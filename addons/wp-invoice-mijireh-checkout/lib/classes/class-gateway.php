<?php
/**
  Name: Mijireh Checkout
  Class: wpi_mijireh_checkout
  Internal Slug: wpi_mijireh_checkout
  JS Slug: wpi_mijireh_checkout
  Version: 1.0
  Description: Provides Mijireh Checkout gateway
 */

namespace UsabilityDynamics\WPI_MC {

  use UsabilityDynamics\MijirehClient\Address;
  use UsabilityDynamics\MijirehClient\Exception;
  use UsabilityDynamics\MijirehClient\Mijireh;
  use UsabilityDynamics\MijirehClient\Order;

  if (!class_exists('UsabilityDynamics\WPI_MC\Gateway') && class_exists('\wpi_gateway_base')) {
    class Gateway extends \wpi_gateway_base {

      /**
       * Construct
       */
      function __construct() {

        //** we do not call __construct here to prevent wrong $type to be generated */
        $this->type = 'wpi_mijireh_checkout';

        //** Thats why we need to replicate all that was done in parent::__construct but properly for current case
        __('Customer Information', ud_get_wp_invoice()->domain);
        add_filter('sync_billing_update', array('wpi_gateway_base', 'sync_billing_filter'), 10, 3);
        add_filter('wpi_recurring_settings', create_function(' $gateways ', ' $gateways[] = "' . $this->type . '"; return $gateways; '));
        add_action('wpi_recurring_settings_' . $this->type, array($this, 'recurring_settings'));
        add_action('wpi_payment_fields_' . $this->type, array($this, 'wpi_payment_fields'));

        //** Fields for front-end. */
        $this->front_end_fields = array(
          'customer_information' => array(
            'first_name' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'first_name',
              'label' => __('First Name', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'last_name' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'last_name',
              'label' => __('Last Name', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'user_email' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'user_email',
              'label' => __('Email Address', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'phonenumber' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'phonenumber',
              'label' => __('Phone', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'streetaddress' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'streetaddress',
              'label' => __('Street', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'city' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'city',
              'label' => __('City', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'zip' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'zip',
              'label' => __('Zip/Postal Code', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'country' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'country',
              'label' => __('Country', ud_get_wp_invoice_mijireh_checkout()->domain)
            ),
            'state' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'state',
              'label' => __('State/Province', ud_get_wp_invoice_mijireh_checkout()->domain)
            )
          )
        );

        $this->options = array(
          'name' => 'Mijireh Checkout',
          'allow' => '',
          'default_option' => '',
          'settings' => array(
            'access_key' => array(
              'label' => __("Access Key", ud_get_wp_invoice_mijireh_checkout()->domain),
              'value' => ''
            )
          )
        );
      }

      /**
       * Override function
       * @global type $wpdb
       * @global type $wpi_settings
       * @global type $invoice
       * @param type $args
       * @param type $from_ajax
       */
      function frontend_display($args = '', $from_ajax = false) {
        global $wpdb, $wpi_settings, $invoice;
        //** Setup defaults, and extract the variables */
        $defaults = array();
        extract(wp_parse_args($args, $defaults), EXTR_SKIP);
        require_once( ud_get_wp_invoice()->path( 'lib/class_template_functions.php', 'dir' ) );
        //** Include the template file required */
        $process_payment_nonce = wp_create_nonce( "process-payment" );

        include( ud_get_wp_invoice()->path('lib/gateways/templates/', 'dir') . 'payment_header.tpl.php' );
        include( ud_get_wp_invoice_mijireh_checkout()->path('static/views/', 'dir') . 'mijireh_checkout_frontend.php' );
        include( ud_get_wp_invoice()->path('lib/gateways/templates/', 'dir') . 'payment_footer.tpl.php');
      }

      /**
       * Show settings for RB. Nothing in case of InterKassa
       * @param type $invoice
       */
      function recurring_settings($invoice) {
        ?>
        <h4><?php _e('Mijireh Checkout Recurring Billing', ud_get_wp_invoice_mijireh_checkout()->domain); ?></h4>
        <p><?php _e('Currently Mijireh Checkout gateway does not support Recurring Billing', ud_get_wp_invoice_mijireh_checkout()->domain); ?></p>
        <?php
      }

      /**
       * Fields renderer for PPP
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
                <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), ud_get_wp_invoice_mijireh_checkout()->domain); ?></li>
                <?php
                $html = ob_get_clean();
                echo $html;

                //** For each field */
                foreach ($value as $field_slug => $field_data) {

                  //** Change field properties if we need */
                  $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_mijireh_checkout');
                  $html = '';

                  ob_start();

                  switch ($field_data['type']) {
                    case self::TEXT_INPUT_TYPE:
                      ?>

                      <li class="wpi_checkout_row">
                        <div class="control-group">
                          <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice_mijireh_checkout()->domain); ?></label>
                          <div class="controls">
                            <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo isset($field_data['value']) ? $field_data['value'] : (!empty($invoice['user_data'][$field_slug]) ? $invoice['user_data'][$field_slug] : ''); ?>" />
                          </div>
                        </div>
                      </li>

                      <?php
                      $html = ob_get_clean();

                      break;

                    case self::SELECT_INPUT_TYPE:
                      ?>

                      <li class="wpi_checkout_row">
                        <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice_mijireh_checkout()->domain); ?></label>
                        <?php echo \WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
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
         * Process Payment
         */
        static function process_payment() {

          global $invoice;

          //** Response */
          $response = array(
            'success' => false,
            'error' => false,
            'data' => null
          );

          $data = array();

          $access_key = $invoice['billing']['wpi_mijireh_checkout']['settings']['access_key']['value'];
          $invoice_id = $invoice['invoice_id'];

          try {

            Mijireh::$access_key = $access_key;

            $order = new Order();

                      //** Support partial payments */
            if ($invoice['deposit_amount'] > 0) {
              $amount = (float) $_REQUEST['amount'];
              if (((float) $_REQUEST['amount']) > $invoice['net']) {
                $amount = $invoice['net'];
              }
              if (((float) $_REQUEST['amount']) < $invoice['deposit_amount']) {
                $amount = $invoice['deposit_amount'];
              }

              $order->add_item( __('Partial Payment', ud_get_wp_invoice_mijireh_checkout()->domain), $amount );

              $order->total = number_format( (float)$amount, 2, '.', '' );
            } else {

              if ( !empty( $invoice['itemized_list'] ) && is_array( $invoice['itemized_list'] ) ) {
                foreach( $invoice['itemized_list'] as $_item ) {
                  $order->add_item( $_item['name'], $_item['price'], $_item['quantity'] );
                }
              }

              if ( !empty( $invoice['itemized_charges'] ) && is_array( $invoice['itemized_charges'] ) ) {
                foreach( $invoice['itemized_charges'] as $_item ) {
                  $order->add_item( $_item['name'], $_item['amount'] );
                }
              }

              if ( !empty( $invoice['adjustments'] ) ) {
                if ( (float)$invoice['adjustments'] < 0 ) {
                  $invoice['total_discount'] += abs( (float)$invoice['adjustments'] );
                } else {
                  $invoice['total_tax'] += abs( (float)$invoice['adjustments'] );
                }
              }

              $order->tax = number_format( $invoice['total_tax'], 2, '.', '' );
              $order->discount = number_format( $invoice['total_discount'], 2, '.', '' );
              $order->total = number_format( (float)$invoice['net'], 2, '.', '' );
            }

            $order->return_url = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_mijireh_checkout&i='.$invoice_id.'&t='.$order->total);
            $order->shipping = 0;

            $order->email = $_REQUEST['user_email'];
            $order->first_name = $_REQUEST['first_name'];
            $order->last_name = $_REQUEST['last_name'];

            $address = new Address();
            $address->street = $_REQUEST['streetaddress'];
            $address->city = $_REQUEST['city'];
            $address->state_province = $_REQUEST['state'];
            $address->zip_code = $_REQUEST['zip'];
            $address->country = $_REQUEST['country'];
            $address->phone = $_REQUEST['phonenumber'];

            $order->set_shipping_address($address);
            $order->set_billing_address($address);

            $order->add_meta_data( 'invoice_id', $invoice_id );

            $order = apply_filters( 'wpi_mc_pre_payment_order', $order );

            $order->create();

            $wp_users_id = $invoice['user_data']['ID'];

            update_user_meta($wp_users_id, 'last_name', !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : '' );
            update_user_meta($wp_users_id, 'first_name', !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : '' );
            update_user_meta($wp_users_id, 'city', !empty($_REQUEST['city']) ? $_REQUEST['city'] : '' );
            update_user_meta($wp_users_id, 'state', !empty($_REQUEST['state']) ? $_REQUEST['state'] : '' );
            update_user_meta($wp_users_id, 'zip', !empty($_REQUEST['zip']) ? $_REQUEST['zip'] : '' );
            update_user_meta($wp_users_id, 'streetaddress', !empty($_REQUEST['streetaddress']) ? $_REQUEST['streetaddress'] : '' );
            update_user_meta($wp_users_id, 'phonenumber', !empty($_REQUEST['phonenumber']) ? $_REQUEST['phonenumber'] : '' );
            update_user_meta($wp_users_id, 'country', !empty($_REQUEST['country']) ? $_REQUEST['country'] : '' );

            if ( !empty( $_REQUEST['crm_data'] ) ) {
              self::user_meta_updated( $_REQUEST['crm_data'] );
            }

            $invoice_obj = new \WPI_Invoice();
            $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

            parent::successful_payment($invoice_obj);

            $data['redirect'] = $order->checkout_url;
            $response['success'] = true;
            $response['error'] = false;

          } catch ( Exception $e ) {
            $response['error'] = true;
            $data['messages'][] = $e->getMessage();
          }

          $response['data'] = $data;
          die(json_encode($response));
        }

        /**
         * Server Callback
         */
        public static function server_callback() {

          if ( !empty( $_GET['order_number'] ) && !empty( $_GET['i'] ) ) {

            $invoice = new \WPI_Invoice();
            $invoice->load_invoice(array('id' => $_GET['i']));

            $access_key = $invoice->data['billing']['wpi_mijireh_checkout']['settings']['access_key']['value'];

            try {

              Mijireh::$access_key = $access_key;

              $order = new Order( esc_attr( $_GET['order_number'] ) );
              $order_invoice_id = $order->get_meta_value( 'invoice_id' );

              if ( $order_invoice_id != $invoice->data['invoice_id'] ) {
                die('Wrong arguments set');
              }

              $event_note = sprintf( __('%s paid via Mijireh Checkout', ud_get_wp_invoice_mijireh_checkout()->domain), \WPI_Functions::currency_format( abs( $_GET['t'] ) ) );
              $event_amount = (float) $_GET['t'];
              $event_type = 'add_payment';
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              $invoice->save_invoice();

              wp_invoice_mark_as_paid( $_GET['i'], $check = true );
              parent::successful_payment( $invoice );
              send_notification($invoice->data);
              echo '<script type="text/javascript">window.location="' . get_invoice_permalink($invoice->data['ID']) . '";</script>';

            } catch ( Exception $e ) {
               die( $e->getMessage() );
            }
          }

          if ( !empty( $_GET['m'] ) && $_GET['m'] == 'checkout' && !empty( $_GET['order_number'] ) ) {
            global $wpdb;
            $invoice_id = $wpdb->get_var( $wpdb->prepare( "select post_id from {$wpdb->postmeta} where meta_key = 'order_number' and meta_value = '%s'", array( $_GET['order_number'] ) ) );

            if ( !empty( $invoice_id ) ) {
              global $wpi_settings;

              $gateway_settings = $wpi_settings['billing']['wpi_mijireh_checkout']['settings'];
              $access_key = $gateway_settings['access_key']['value'];

              Mijireh::$access_key = $access_key;

              $order = new Order( esc_attr( $_GET['order_number'] ) );
              $order_trans_id = $order->get_meta_value( 'trans_id' );

              $trans_id = get_post_meta( $invoice_id, 'trans_id', 1 );

              if ( $trans_id != $order_trans_id ) {
                die('Wrong arguments set');
              }

              $invoice = new \WPI_Invoice();
              $invoice->load_invoice(array('id' => $invoice_id));

              $event_note = sprintf(__('Paid via Mijireh Checkout', ud_get_wp_invoice()->domain));
              $event_amount = (float) $invoice->data['net'];
              $event_type = 'add_payment';
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              $invoice->save_invoice();
              wp_invoice_mark_as_paid($invoice_id, $check = true);
              parent::successful_payment( $invoice );
              send_notification($invoice->data);
              echo '<script type="text/javascript">window.location="' . get_invoice_permalink($invoice->data['ID']) . '";</script>';
            }
          }

          die('Direct access not allowed');

        }

      }

    }
  }
