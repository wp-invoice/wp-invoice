<?php

/**
  Name: PayPal
  Class: wpi_paypal
  Internal Slug: wpi_paypal
  JS Slug: wpi_paypal
  Version: 1.0
  Description: Provides the PayPal for payment options
 */
class wpi_paypal extends wpi_gateway_base {

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();

    /**
     * Payment settings
     *
     * @var array
     */
    $this->options = array(
        'name' => 'PayPal',
        'allow' => '',
        'default_option' => '',
        'settings' => array(
            'paypal_address' => array(
                'label' => __("PayPal Username", ud_get_wp_invoice()->domain),
                'value' => ''
            ),
            'test_mode' => array(
                'label' => __("Use in Test Mode", ud_get_wp_invoice()->domain),
                'description' => __("Use PayPal SandBox for test mode", ud_get_wp_invoice()->domain),
                'type' => 'select',
                'value' => 'N',
                'data' => array(
                    'N' => __("No", ud_get_wp_invoice()->domain),
                    'Y' => __("Yes", ud_get_wp_invoice()->domain)
                )
            ),
            'ipn' => array(
                'label' => __("PayPal IPN URL", ud_get_wp_invoice()->domain),
                'type' => "readonly",
                'description' => __("Once IPN is integrated, sellers can automate their back office so they don't have to wait for payments to come in to trigger order fulfillment. Setup this URL into your PayPal Merchant Account Settings.", ud_get_wp_invoice()->domain)
            ),
            'send_notify_url' => array(
                'label' => __("Send IPN URL with payment request?", ud_get_wp_invoice()->domain),
                'description' => __('Use this option if you did not set IPN in your PayPal account.', ud_get_wp_invoice()->domain),
                'type' => "select",
                'value' => '',
                'data' => array(
                    "1" => __("Yes", ud_get_wp_invoice()->domain),
                    "0" => __("No", ud_get_wp_invoice()->domain)
                )
            )
        )
    );

    /**
     * Fields list for frontend
     */
    $this->front_end_fields = array(
        'customer_information' => array(
            'first_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'first_name',
                'label' => __('First Name', ud_get_wp_invoice()->domain)
            ),
            'last_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'last_name',
                'label' => __('Last Name', ud_get_wp_invoice()->domain)
            ),
            'user_email' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'email_address',
                'label' => __('Email Address', ud_get_wp_invoice()->domain)
            ),
            'phonenumber' => array(
                array(
                    'type' => 'text',
                    'class' => 'text-input small',
                    'name' => 'night_phone_a'
                ),
                array(
                    'type' => 'text',
                    'class' => 'text-input small',
                    'name' => 'night_phone_b'
                ),
                array(
                    'type' => 'text',
                    'class' => 'text-input small',
                    'name' => 'night_phone_c'
                )
            ),
            'streetaddress' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'address1',
                'label' => __('Address', ud_get_wp_invoice()->domain)
            ),
            'city' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'city',
                'label' => __('City', ud_get_wp_invoice()->domain)
            ),
            'state' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'state',
                'label' => __('State/Province', ud_get_wp_invoice()->domain)
            ),
            'zip' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'zip',
                'label' => __('Zip/Postal Code', ud_get_wp_invoice()->domain)
            ),
            'country' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'country',
                'label' => __('Country', ud_get_wp_invoice()->domain)
            )
        )
    );

    $this->options['settings']['ipn']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_paypal');
  }

  /**
   *
   * @param type $this_invoice
   */
  function recurring_settings($this_invoice) {
    ?>
    <h4><?php _e('PayPal Subscriptions', ud_get_wp_invoice()->domain); ?></h4>
    <table class="wpi_recurring_bill_settings">
      <tr>
        <th><?php _e('Bill Every', ud_get_wp_invoice()->domain); ?></th>
        <td>
    <?php echo WPI_UI::input("name=wpi_invoice[recurring][" . $this->type . "][length]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['length'] : '') . "&class=wpi_small wpi_bill_every_length"); ?>
    <?php echo WPI_UI::select("name=wpi_invoice[recurring][" . $this->type . "][unit]&values=" . serialize(array("days" => __("Day(s)", ud_get_wp_invoice()->domain), "weeks" => __("Week(s)", ud_get_wp_invoice()->domain), "months" => __("Month(s)", ud_get_wp_invoice()->domain), "years" => __("Year(s)", ud_get_wp_invoice()->domain))) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['unit'] : '')); ?>
        </td>
      </tr>
      <tr>
        <th><?php _e('Billing Cycles', ud_get_wp_invoice()->domain); ?></th>
        <td><?php echo WPI_UI::input("id=wpi_meta_recuring_cycles&name=wpi_invoice[recurring][" . $this->type . "][cycles]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['cycles'] : '') . "&class=wpi_small"); ?></td>
      </tr>
    </table>
    <?php
  }
  
  /**
   * Get proper api url
   * @filters wpi_paypal_live_url, wpi_paypal_demo_url
   * @param type $invoice
   * @return type
   */
  static public function get_api_url( $invoice ) {
    return 
      (!empty( $invoice['billing']['wpi_paypal']['settings']['test_mode']['value'] ) && $invoice['billing']['wpi_paypal']['settings']['test_mode']['value'] == 'Y') 
      ? apply_filters( 'wpi_paypal_demo_url', 'https://www.sandbox.paypal.com/cgi-bin/webscr' )
      : ( strlen($invoice['billing']['wpi_paypal']['settings']['test_mode']['value'])>1 ? $invoice['billing']['wpi_paypal']['settings']['test_mode']['value'] : apply_filters( 'wpi_paypal_live_url', 'https://www.paypal.com/cgi-bin/webscr' ) );
  }
  
  /**
   * 
   * @param type $invoice
   * @return type
   */
  static public function get_business( $invoice ) {
    return !empty( $invoice['billing']['wpi_paypal']['settings']['paypal_address']['value'] ) ? $invoice['billing']['wpi_paypal']['settings']['paypal_address']['value'] : '';
  }
  
  /**
   * 
   * @param type $invoice
   * @return type
   */
  static public function do_send_notify_url( $invoice ) {
    return (!empty( $invoice['billing']['wpi_paypal']['settings']['send_notify_url']['value'] ) && $invoice['billing']['wpi_paypal']['settings']['send_notify_url']['value'] == '1') ? true : false;
  }

  /**
   * Overrided payment process for paypal
   *
   * @global type $invoice
   * @global type $wpi_settings
   */
  static function process_payment() {
    global $invoice;

    $wp_users_id = $invoice['user_data']['ID'];

    //** update user data */
    update_user_meta($wp_users_id, 'last_name', $_REQUEST['last_name']);
    update_user_meta($wp_users_id, 'first_name', $_REQUEST['first_name']);
    update_user_meta($wp_users_id, 'city', $_REQUEST['city']);
    update_user_meta($wp_users_id, 'state', $_REQUEST['state']);
    update_user_meta($wp_users_id, 'zip', $_REQUEST['zip']);
    update_user_meta($wp_users_id, 'streetaddress', $_REQUEST['address1']);
    update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['night_phone_a'] . '-' . $_REQUEST['night_phone_b'] . '-' . $_REQUEST['night_phone_c']);
    update_user_meta($wp_users_id, 'country', $_REQUEST['country']);
    
    if ( !empty( $_REQUEST['crm_data'] ) ) {
      self::user_meta_updated( $_REQUEST['crm_data'] );
    }

    $invoice_obj = new WPI_Invoice();
    $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

    parent::successful_payment($invoice_obj);

    echo json_encode(
      array('success' => 1)
    );
  }

  /**
   * Render fields
   *
   * @param array $invoice
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
            <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), ud_get_wp_invoice()->domain); ?></li>
          <?php
          $html = ob_get_contents();
          ob_end_clean();
          echo $html;
          // For each field
          foreach ($value as $field_slug => $field_data) {

            // If field is set of 3 fields for paypal phone number
            if ($field_slug == 'phonenumber') {

              echo '<li class="wpi_checkout_row"><div class="control-group"><label class="control-label">' . __('Phone Number', ud_get_wp_invoice()->domain) . '</label><div class="controls">';

              $phonenumber = !empty($invoice['user_data']['phonenumber']) ? $invoice['user_data']['phonenumber'] : "---";
              $phone_array = preg_split('/-/', $phonenumber);

              foreach ($field_data as $field) {
                //** Change field properties if we need */
                $field = apply_filters('wpi_payment_form_styles', $field, $field_slug, 'wpi_paypal');
                ob_start();
                ?>
                  <input type="<?php echo esc_attr($field['type']); ?>" class="<?php echo esc_attr($field['class']); ?>"  name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr($phone_array[key($phone_array)]);
                next($phone_array); ?>" />
                  <?php
                  $html = ob_get_contents();
                  ob_end_clean();
                  echo $html;
                }

                echo '</div></div></li>';
              }
              //** Change field properties if we need */
              $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_paypal');

              $html = '';
              if ( !empty( $field_data['type'] ) ) {
                switch ($field_data['type']) {
                  case self::TEXT_INPUT_TYPE:

                    ob_start();
                    ?>

                    <li class="wpi_checkout_row">
                      <div class="control-group">
                        <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
                        <div class="controls">
                          <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo isset($field_data['value']) ? $field_data['value'] : (!empty($invoice['user_data'][$field_slug]) ? $invoice['user_data'][$field_slug] : ''); ?>" />
                        </div>
                      </div>
                    </li>

                    <?php
                    $html = ob_get_contents();
                    ob_end_clean();

                    break;

                  case self::SELECT_INPUT_TYPE:

                    ob_start();
                    ?>

                    <li class="wpi_checkout_row">
                      <label for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
                  <?php echo WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                    </li>

                    <?php
                    $html = ob_get_contents();
                    ob_clean();

                    break;

                  case self::RECAPTCHA_INPUT_TYPE:
                    $this->display_recaptcha($field_data);
                    
                    break;

                  default:
                    break;
                }
              }

              echo $html;
            }
            echo '</ul>';
          }
        }
      }
    }

    /**
     * Handler for PayPal IPN queries
     * @author korotkov@ud
     * Full callback URL: http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_paypal
     */
    static function server_callback() {

      if (empty($_POST))
        die(__('Direct access not allowed', ud_get_wp_invoice()->domain));

      $invoice = new WPI_Invoice();
      $invoice->load_invoice("id={$_POST['invoice']}");

      /** Verify callback request */
      if (self::_ipn_verified($invoice)) {

        switch ($_POST['txn_type']) {
          /** New PayPal Subscription */
          case 'subscr_signup':
            /** PayPal Subscription created */
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription created', ud_get_wp_invoice()->domain));
            wp_invoice_mark_as_pending($_POST['invoice']);
            do_action('wpi_paypal_subscr_signup_ipn', $_POST);
            break;

          case 'subscr_cancel':
            /** PayPal Subscription cancelled */
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription cancelled', ud_get_wp_invoice()->domain));
            do_action('wpi_paypal_subscr_cancel_ipn', $_POST);
            break;

          case 'subscr_failed':
            /** PayPal Subscription failed */
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription payment failed', ud_get_wp_invoice()->domain));
            do_action('wpi_paypal_subscr_failed_ipn', $_POST);
            break;

          case 'subscr_payment':
            /** Payment of Subscription */
            switch ($_POST['payment_status']) {
              case 'Completed':
                /** Add payment amount */
                $event_note = sprintf(__('%1s paid for subscription %2s', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_POST['mc_gross']), $_POST['invoice']), $_POST['subscr_id']);
                $event_amount = (float) $_POST['mc_gross'];
                $event_type = 'add_payment';
                /** Log balance changes */
                $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
                $invoice->save_invoice();
                send_notification($invoice->data);
                break;

              default:
                break;
            }
            do_action('wpi_paypal_subscr_payment_ipn', $_POST);
            break;

          case 'subscr_eot':
            /** PayPal Subscription end of term */
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription term is finished', ud_get_wp_invoice()->domain));
            wp_invoice_mark_as_paid($_POST['invoice'], $check = false);
            do_action('wpi_paypal_subscr_eot_ipn', $_POST);
            break;

          case 'subscr_modify':
            /** PayPal Subscription modified */
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription modified', ud_get_wp_invoice()->domain));
            do_action('wpi_paypal_subscr_modify_ipn', $_POST);
            break;

          case 'web_accept':
            /** PayPal simple button */
            switch ($_POST['payment_status']) {

              case 'Pending':
                /** Mark invoice as Pending */
                wp_invoice_mark_as_pending($_POST['invoice']);
                do_action('wpi_paypal_pending_ipn', $_POST);
                break;

              case 'Completed':
                /** Add payment amount */
                $event_note = sprintf(__('%s paid via PayPal', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_POST['mc_gross']), $_POST['invoice']));
                $event_amount = (float) $_POST['mc_gross'];
                $event_type = 'add_payment';
                /** Log balance changes */
                $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
                /** Log payer email */
                $payer_email = sprintf(__("PayPal Payer email: %s", ud_get_wp_invoice()->domain), $_POST['payer_email']);
                $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
                $invoice->save_invoice();
                /** ... and mark invoice as paid */
                wp_invoice_mark_as_paid($_POST['invoice'], $check = true);
                send_notification($invoice->data);
                do_action('wpi_paypal_complete_ipn', $_POST);
                parent::successful_payment_webhook( $invoice );
                break;

              default: break;
            }
            break;

          case 'cart':
            /** PayPal Cart. Used for SPC */
            switch ($_POST['payment_status']) {
              case 'Pending':
                /** Mark invoice as Pending */
                wp_invoice_mark_as_pending($_POST['invoice']);
                do_action('wpi_paypal_pending_ipn', $_POST);
                break;
              case 'Completed':
                /** Add payment amount */
                $event_note = sprintf(__('%s paid via PayPal', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_POST['mc_gross']), $_POST['invoice']));
                $event_amount = (float) $_POST['mc_gross'];
                $event_type = 'add_payment';
                /** Log balance changes */
                $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
                /** Log payer email */
                $payer_email = sprintf(__("PayPal Payer email: %s", ud_get_wp_invoice()->domain), $_POST['payer_email']);
                $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
                $invoice->save_invoice();
                /** ... and mark invoice as paid */
                wp_invoice_mark_as_paid($_POST['invoice'], $check = true);
                send_notification($invoice->data);
                do_action('wpi_paypal_complete_ipn', $_POST);
                break;

              default: break;
            }
            break;

          default:
            break;
        }
        echo ' ';
      }
    }

    /**
     * Verify IPN and returns TRUE or FALSE
     * @author korotkov@ud
     * */
    private static function _ipn_verified($invoice = false) {

      if ($invoice) {
        $request = self::get_api_url( $invoice->data );
      } else {
        global $wpi_settings;
        $request = self::get_api_url( $wpi_settings );
      }

      $_POST['cmd'] = '_notify-validate'; // set `cmd` param for post request

// Commented because we need POST request for paypal IPN
//      foreach ($_POST as $key => $value) {
//        $value = urlencode(stripslashes($value));
//        $request .= "&$key=$value";
//      }

      $response = wp_remote_post( $request, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => $_POST,
        'cookies' => array()
            )
      );

      if ( ! is_wp_error( $response ) ) {
         return strstr($response['body'], 'VERIFIED') ? TRUE : FALSE;
      } else {
         return false;
      }

    }

  }
  