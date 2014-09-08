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
                'label' => __("Mode", WPI),
                'description' => "Stripe payment mode",
                'type' => 'select',
                'value' => 'test',
                'data' => array(
                    'test' => "Test",
                    'live' => "Live"
                )
            ),
            'webhook_url' => array(
                'label' => __( "Webhook URL", WPI ),
                'type' => "readonly",
                'value' => admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_stripe'),
                'description' => __( "Use webhooks to be notified about events that happen in your Stripe account.", WPI )
            )
        )
    );

  }

  /**
   * Settings for recurring billing
   * @param type $this_invoice
   */
  function recurring_settings($this_invoice) {
      ?>
      <h4><?php _e('Stripe Subscriptions', WPI); ?></h4>
      <table class="wpi_recurring_bill_settings">
          <tr>
              <th style="cursor:help;" title="<?php _e('Specifies billing frequency.', WPI); ?>"><?php _e('Interval', WPI); ?></th>
              <td>
                  <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][interval]&values=" . serialize(apply_filters('wpi_stripe_interval', array("week" => __("Week", WPI), "month" => __("Month", WPI), "year" => __("Year", WPI)))) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['interval'] : '')); ?>
              </td>
          </tr>

          <tr>
              <th style="cursor:help;" title="<?php _e('The number of the unit specified in the interval parameter. For example, you could specify an interval_count of 3 and an interval of "month" for quarterly billing (every 3 months).', WPI); ?>"><?php _e('Interval Count', WPI); ?></th>
              <td>
                  <?php echo WPI_UI::input("id=stripe_interval_count&name=wpi_invoice[recurring][".$this->type."][interval_count]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['interval_count'] : '') . "&special=size='2' maxlength='4' autocomplete='off'"); ?>
              </td>
          </tr>
      </table>
      <?php
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
              $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_stripe');
              $html = '';

              ob_start();

              switch ($field_data['type']) {
                case self::TEXT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], WPI); ?></label>
                      <div class="controls">
                        <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo isset($field_data['value'])?$field_data['value']:(!empty($invoice['user_data'][$field_slug])?$invoice['user_data'][$field_slug]:'');?>" />
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
     * Process STRIPE payment
     * @global type $invoice
     */
    static function process_payment() {
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

        if ( !class_exists('Stripe') ) {
          require_once( WPI_Path . '/third-party/stripe/lib/Stripe.php' );
        }
        $pk = trim($invoice['billing']['wpi_stripe']['settings'][$invoice['billing']['wpi_stripe']['settings']['mode']['value'].'_secret_key']['value']);

        Stripe::setApiKey($pk);

        switch( $invoice['type'] == 'recurring' ) {

          //** If recurring */
          case true:

            $plan = Stripe_Plan::create(array(
              "amount" => (float)$invoice['net']*100,
              "interval" => $invoice['recurring']['wpi_stripe']['interval'],
              "interval_count" => $invoice['recurring']['wpi_stripe']['interval_count'],
              "name" => $invoice['post_title'],
              "currency" => strtolower($invoice['default_currency_code']),
              "id" => $invoice['invoice_id'])
            );

            $customer = Stripe_Customer::create(array(
              "card" => $token,
              "plan" => $invoice['invoice_id'],
              "email" => $invoice['user_email'])
            );

            if ( !empty( $plan->id ) && !empty( $plan->amount ) && !empty( $customer->id ) ) {

              $invoice_obj = new WPI_Invoice();
              $invoice_obj->load_invoice("id={$invoice['invoice_id']}");
              $log = sprintf( __( "Subscription has been initiated. Plan: %s, Customer: %s", WPI ), $plan->id, $customer->id );
              $invoice_obj->add_entry("attribute=invoice&note=$log&type=update");
              $invoice_obj->save_invoice();

              update_post_meta( wpi_invoice_id_to_post_id( $invoice['invoice_id'] ), '_stripe_customer_id', $customer->id );

              $data['messages'][] = __( 'Stripe Subscription has been initiated. Do not pay this invoice again. Thank you.', WPI );
              $response['success'] = true;
              $response['error'] = false;
            } else {

              $data['messages'][] = __( 'Could not initiate Stripe Subscription. Contact site Administrator please.', WPI );
              $response['success'] = false;
              $response['error'] = true;
            }

            break;

          //** If regular payment */
          case false:

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

              $invoice_id  = $invoice['invoice_id'];
              $wp_users_id = $invoice['user_data']['ID'];

              //** update user data */
              update_user_meta($wp_users_id, 'last_name', !empty($_REQUEST['last_name'])?$_REQUEST['last_name']:'' );
              update_user_meta($wp_users_id, 'first_name', !empty($_REQUEST['first_name'])?$_REQUEST['first_name']:'' );
              update_user_meta($wp_users_id, 'city', !empty($_REQUEST['city'])?$_REQUEST['city']:'' );
              update_user_meta($wp_users_id, 'state', !empty($_REQUEST['state'])?$_REQUEST['state']:'' );
              update_user_meta($wp_users_id, 'zip', !empty($_REQUEST['zip'])?$_REQUEST['zip']:'' );
              update_user_meta($wp_users_id, 'streetaddress', !empty($_REQUEST['address1'])?$_REQUEST['address1']:'' );
              update_user_meta($wp_users_id, 'phonenumber', !empty($_REQUEST['phonenumber'])?$_REQUEST['phonenumber']:'' );
              update_user_meta($wp_users_id, 'country', !empty($_REQUEST['country'])?$_REQUEST['country']:'' );

              if ( !empty( $_REQUEST['crm_data'] ) ) {
                self::user_meta_updated( $_REQUEST['crm_data'] );
              }

              $invoice_obj = new WPI_Invoice();
              $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

              $amount = (float)($charge->amount/100);
              //** Add payment amount */
              $event_note = WPI_Functions::currency_format($amount, $invoice['invoice_id']) . __(" paid via STRIPE", WPI);
              $event_amount = $amount;
              $event_type = 'add_payment';

              $event_note = urlencode($event_note);
              //** Log balance changes */
              $invoice_obj->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              //** Log client IP */
              $success = __("Successfully processed by ", WPI).$_SERVER['REMOTE_ADDR'];
              $invoice_obj->add_entry("attribute=invoice&note=$success&type=update");
              //** Log payer */
              $payer_card = __("STRIPE Card ID: ", WPI).$charge->card->id;
              $invoice_obj->add_entry("attribute=invoice&note=$payer_card&type=update");

              $invoice_obj->save_invoice();
              //** Mark invoice as paid */
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

            break;

          //** Other cases */
          default: break;
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
      } catch (Exception $e) {

        $response['error'] = true;
        $data['messages'][] = $e->getMessage();
      }

      $response['data'] = $data;
      die(json_encode($response));
    }

    /**
     *
     */
    static function server_callback() {
      global $wpdb;

      //** Get request body */
      $body = @file_get_contents('php://input');
      $event_object = json_decode($body);

      switch ($event_object->type) {

        //** Used only for subscriptions since single payments processed without Webhook */
        case 'charge.succeeded':

          $post_id = $wpdb->get_col("SELECT post_id
          FROM {$wpdb->postmeta}
          WHERE meta_key = '_stripe_customer_id'
            AND meta_value = '{$event_object->data->object->customer}'");

          $invoice_object = new WPI_Invoice();
          $invoice_object->load_invoice("id=" . $post_id[0]);

          if (empty($invoice_object->data['ID'])) {
            die("Can't load invoice");
          }

          if ( !class_exists('Stripe') ) {
            require_once( WPI_Path . '/third-party/stripe/lib/Stripe.php' );
          }
          $pk = trim($invoice_object->data['billing']['wpi_stripe']['settings'][$invoice_object->data['billing']['wpi_stripe']['settings']['mode']['value'] . '_secret_key']['value']);

          Stripe::setApiKey($pk);

          $event = Stripe_Event::retrieve($event_object->id);


          if ($event->data->object->paid == 1) {
            $event_amount = (float) ($event->data->object->amount / 100);
            $event_note = WPI_Functions::currency_format(abs($event_amount), $invoice_object->data['invoice_id']) . ' ' . __('Stripe Subscription Payment', WPI);
            $event_type = 'add_payment';

            $invoice_object->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
            $invoice_object->save_invoice();
          }
          break;

        case 'customer.subscription.deleted':

          $post_id = $wpdb->get_col("SELECT post_id
          FROM {$wpdb->postmeta}
          WHERE meta_key = '_stripe_customer_id'
            AND meta_value = '{$event_object->data->object->customer}'");

          $invoice_object = new WPI_Invoice();
          $invoice_object->load_invoice("id=" . $post_id[0]);

          if (empty($invoice_object->data['ID'])) {
            die("Can't load invoice");
          }

          if ( !class_exists('Stripe') ) {
            require_once( WPI_Path . '/third-party/stripe/lib/Stripe.php' );
          }
          $pk = trim($invoice_object->data['billing']['wpi_stripe']['settings'][$invoice_object->data['billing']['wpi_stripe']['settings']['mode']['value'] . '_secret_key']['value']);

          Stripe::setApiKey($pk);

          $event = Stripe_Event::retrieve($event_object->id);

          if ( $event->data->object->status == 'canceled' ) {
            $invoice_object->add_entry("attribute=invoice&note=".__('Stripe Subscription has been canceled', WPI)."&type=update");
            $invoice_object->save_invoice();
            wp_invoice_mark_as_paid($invoice_object->data['invoice_id']);
          }

          break;

        default: break;
      }

    }

  }