<?php

/**
  Name: Stripe
  Class: wpi_stripe
  Internal Slug: wpi_stripe
  JS Slug: wpi_stripe
  Version: 1.0
  Description: Provides Stripe gateway
 */
class wpi_stripe extends wpi_gateway_base {

  /**
   * Properties of class
   * @var array
   */
  var $options = array();
  var $front_end_fields = array();

  /**
   * Construct
   */
  function __construct() {
    parent::__construct();

//** Fields for front-end. */
    $this->front_end_fields = array(
        'customer_information' => array(
            'first_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'first_name',
                'label' => __('First Name', WPI)
            ),
            'last_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'last_name',
                'label' => __('Last Name', WPI)
            ),
            'user_email' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'email_address',
                'label' => __('Email Address', WPI)
            ),
            'phonenumber' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'phonenumber',
                'label' => __('Phone', WPI)
            ),
            'streetaddress' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'address1',
                'label' => __('Address', WPI)
            ),
            'city' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'city',
                'label' => __('City', WPI)
            ),
            'state' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'state',
                'label' => __('State/Province', WPI)
            ),
            'zip' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'zip',
                'label' => __('Zip/Postal Code', WPI)
            )
        )
    );

    $this->options = array(
        'name' => 'Stripe',
        'allow' => '',
        'default_option' => '',
        'settings' => array(
            'test_secret_key' => array(
                'label' => "Test Secret Key",
                'value' => ''
            ),
            'test_publishable_key' => array(
                'label' => "Test Publishable Key",
                'value' => ''
            ),
            'live_secret_key' => array(
                'label' => "Live Secret Key",
                'value' => ''
            ),
            'live_publishable_key' => array(
                'label' => "Live Publishable Key",
                'value' => ''
            ),
            'mode' => array(
                'label' => "Mode",
                'description' => "Stripe payment mode",
                'type' => 'select',
                'value' => 'test',
                'data' => array(
                    'test' => "Test",
                    'live' => "Live"
                )
            )
        )
    );

    add_action('wpi_payment_fields_stripe', array($this, 'wpi_payment_fields'));
  }

  /**
   * Fields renderer for STRIPE
   * @param type $invoice
   */
  function wpi_payment_fields($invoice) {

    $this->front_end_fields = apply_filters('wpi_crm_custom_fields', $this->front_end_fields, 'crm_data');

    if (!empty($this->front_end_fields)) {
      //** For each section */
      foreach ($this->front_end_fields as $key => $value) {
        //** If section is not empty */
        if (!empty($this->front_end_fields[$key])) {
          $html = '';
          ob_start();
          ?>
          <ul class="wpi_checkout_block">
            <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), WPI); ?></li>
            <?php
            $html = ob_get_clean();
            echo $html;
            //** For each field */
            foreach ($value as $field_slug => $field_data) {
              //** Change field properties if we need */
              $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_authorize');
              $html = '';

              ob_start();

              switch ($field_data['type']) {
                case self::TEXT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], WPI); ?></label>
                      <div class="controls">
                        <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo!empty($invoice['user_data'][$field_slug]) ? $invoice['user_data'][$field_slug] : ''; ?>" />
                      </div>
                    </div>
                  </li>

                  <?php
                  $html = ob_get_clean();

                  break;

                case self::SELECT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <label for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], WPI); ?></label>
                    <?php echo WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                  </li>

                  <?php
                  $html = ob_get_clean();

                  break;

                default:
                  break;
              }

              echo $html;
            }
            echo '</ul>';
          }
        }
      }
    }

    /**
     *
     * @global type $invoice
     */
    function process_payment() {
      global $invoice;

      //** Response */
      $response = array(
        'success' => false,
        'error' => false,
        'data' => null
      );

      if (isset($_POST['stripeToken'])) {
        $token = $_POST['stripeToken'];
      } else {
        $response['error'] = true;
        $data['messages'][] = __('The order cannot be processed. You have not been charged. Please confirm that you have JavaScript enabled and try again.', WPI);

        $response['data'] = $data;
        die(json_encode($response));
      }

      try {

        require_once( WPI_Path . '/third-party/stripe/lib/Stripe.php' );
        $pk = $invoice['billing']['wpi_stripe']['settings'][$invoice['billing']['wpi_stripe']['settings']['mode']['value'].'_secret_key']['value'];

        Stripe::setApiKey($pk);

        //** Support partial payments */
        if ($invoice['deposit_amount'] > 0) {
          $amount = (float) $_REQUEST['amount'];
          if (((float) $_REQUEST['amount']) > $invoice['net']) {
            $amount = $invoice['net'];
          }
          if (((float) $_REQUEST['amount']) < $invoice['deposit_amount']) {
            $amount = $invoice['deposit_amount'];
          }
        } else {
          $amount = $invoice['net'];
        }

        $charge = Stripe_Charge::create(array(
          "amount" => (float)$amount*100,
          "currency" => strtolower( $invoice['default_currency_code'] ),
          "card" => $token,
          "description" => $invoice['invoice_id'].' ['.$invoice['post_title'].' / '.get_bloginfo('url').' / '.$invoice['user_email'].']'
        ));

        if ( $charge->paid ) {

          $crm_data    = $_REQUEST['crm_data'];
          $invoice_id  = $invoice['invoice_id'];
          $wp_users_id = $invoice['user_data']['ID'];
          $post_id     = wpi_invoice_id_to_post_id($invoice_id);

          // update user data
          update_user_meta($wp_users_id, 'last_name', $_REQUEST['last_name']);
          update_user_meta($wp_users_id, 'first_name', $_REQUEST['first_name']);
          update_user_meta($wp_users_id, 'city', $_REQUEST['city']);
          update_user_meta($wp_users_id, 'state', $_REQUEST['state']);
          update_user_meta($wp_users_id, 'zip', $_REQUEST['zip']);
          update_user_meta($wp_users_id, 'streetaddress', $_REQUEST['address1']);
          update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['phonenumber']);
          update_user_meta($wp_users_id, 'country', $_REQUEST['country']);

          if ( !empty( $crm_data ) ) $this->user_meta_updated( $crm_data );

          $invoice_obj = new WPI_Invoice();
          $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

          $amount = (float)($charge->amount/100);
          //** Add payment amount */
          $event_note = WPI_Functions::currency_format($amount, $invoice['invoice_id']) . " paid via STRIPE";
          $event_amount = $amount;
          $event_type = 'add_payment';

          $event_note = urlencode($event_note);
          // Log balance changes
          $invoice_obj->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
          // Log client IP
          $success = "Successfully processed by {$_SERVER['REMOTE_ADDR']}";
          $invoice_obj->add_entry("attribute=invoice&note=$success&type=update");
          // Log payer email
          $payer_card = "STRIPE Card ID: {$charge->card->id}";
          $invoice_obj->add_entry("attribute=invoice&note=$payer_card&type=update");

          $invoice_obj->save_invoice();
          //Mark invoice as paid
          wp_invoice_mark_as_paid($invoice_id, $check = true);

          send_notification( $invoice );

          $data['messages'][] = __( 'Successfully paid. Thank you.', WPI );
          $response['success'] = true;
          $response['error'] = false;
        } else {
          $data['messages'][] = $charge->failure_message;
          $response['success'] = false;
          $response['error'] = true;
        }

        $response['data'] = $data;
        die(json_encode($response));

      } catch (Stripe_CardError $e) {

        $e_json = $e->getJsonBody();
        $err = $e_json['error'];
        $response['error'] = true;
        $data['messages'][] = $err['message'];
      } catch (Stripe_ApiConnectionError $e) {

        $response['error'] = true;
        $data['messages'][] = __( 'Service is currently unavailable. Please try again later.', WPI );
      } catch (Stripe_InvalidRequestError $e) {

        $response['error'] = true;
        $data['messages'][] = __( 'Unknown error occured. Please contact site administrator.', WPI );
      } catch (Stripe_ApiError $e) {

        $response['error'] = true;
        $data['messages'][] = __( 'Stripe server is down! Try again later.', WPI );
      }

      $response['data'] = $data;
      die(json_encode($response));
    }

  }