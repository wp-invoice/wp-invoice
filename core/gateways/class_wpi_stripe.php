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
// For each section
      foreach ($this->front_end_fields as $key => $value) {
// If section is not empty
        if (!empty($this->front_end_fields[$key])) {
          $html = '';
          ob_start();
          ?>
          <ul class="wpi_checkout_block">
            <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), WPI); ?></li>
            <?php
            $html = ob_get_clean();
            echo $html;
// For each field
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

    function process_payment() {
      global $invoice;

      echo '<pre>';
      print_r( $pk = $invoice['billing']['wpi_stripe']['settings'][$invoice['billing']['wpi_stripe']['settings']['mode']['value'].'_secret_key']['value'] );
      echo '</pre>';

      // Response
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

      require_once( WPI_Path . '/third-party/stripe/lib/Stripe.php' );

      Stripe::setApiKey($pk);

      /**
       * @todo: ->
       */
      Stripe_Charge::create();

      $response['data'] = $data;
      die(json_encode($response));
    }

  }