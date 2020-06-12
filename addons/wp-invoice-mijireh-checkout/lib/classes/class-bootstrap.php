<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPI_MC {

  use UsabilityDynamics\MijirehClient\Address;
  use UsabilityDynamics\MijirehClient\Mijireh;
  use UsabilityDynamics\MijirehClient\Order;

  if( !class_exists( 'UsabilityDynamics\WPI_MC\bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPMC\bootstrap object
       */
      protected static $instance = null;

      /**
       * @var
       */
      private $order;
      
      /**
       * Instantaite class.
       */
      public function init() {

        //** Here is we go. */
        add_action('wpi_pre_init', array($this, 'load_gateway'));

        //** Add gateway to the list of acceptable */
        add_filter('wpi::spc::accepted_gateways', array($this, 'accept_for_spc'));

        //** Set correct script URL */
        add_filter('wpi::spc::gateway_script_url', array($this, 'correct_script_url'), 10, 2);

        //** Correct template */
        add_filter('wpi::spc::checkout_form_template', array($this, 'checkout_form_template'), 10, 2);

        //** Add method to WP-Invoice Payment API */
        add_filter('wpi::payment_api::defaults', array($this, 'add_api_method'));

        //** Implement Payment API method */
        add_action('wpi::payment_api::custom_venue', array($this, 'api_method_implementation'), 10, 2);

        //** Add this gateway to the list of gateways that create PENDING invoices */
        add_filter('wpi::spc::pending_invoice_gateways', array($this, 'add_pending_invoice_gateway'));
        
      }

      /**
       * @param $current
       * @return array
       */
      public function add_pending_invoice_gateway( $current ) {
        $current[] = 'wpi_mijireh_checkout';
        return $current;
      }

      /**
       * Allow for SPC
       */
      public function accept_for_spc( $gateways ) {
        $gateways[] = 'wpi_mijireh_checkout';
        return $gateways;
      }

      /**
       * Correct script URL
       */
      public function correct_script_url( $url, $gateway ) {
        if ( $gateway === 'wpi_mijireh_checkout' ) {
          $url = ud_get_wp_invoice_mijireh_checkout()->path('static/scripts/', 'url') . "checkout.js";
        }
        return $url;
      }

      /**
       * Correct form template
       */
      public function checkout_form_template( $path, $gateway ) {
        if ( $gateway === 'wpi_mijireh_checkout' ) {
          $path = ud_get_wp_invoice_mijireh_checkout()->path('static/views/', 'dir') . "checkout.tpl.php";
        }
        return $path;
      }

      /**
       * Add method to API
       */
      public function add_api_method( $methods ) {
        $methods['wpi_mijireh_checkout'] = array(true);
        return $methods;
      }

      /**
       * @param $data
       * @return mixed
       */
      public function checkout_process_return( $data ) {
        if ( !empty( $this->order->checkout_url ) ) {
          $data['message'] = __( 'Redirecting to gateway... Please wait...', ud_get_wp_invoice_mijireh_checkout()->domain );
          $data['checkout_url'] = $this->order->checkout_url;
        }
        return $data;
      }

      /**
       * @param $invoice_obj
       */
      public function on_successful_invoice_creation( $invoice_obj ) {
        if ( !empty( $this->order->order_number ) ) {
          update_post_meta( $invoice_obj->data['ID'], 'order_number', $this->order->order_number );
          update_post_meta( $invoice_obj->data['ID'], 'trans_id', $this->order->get_meta_value( 'trans_id' ) );
        }
      }

      /**
       * @param $_response
       * @param $args
       * @return mixed
       */
      public function api_method_implementation( $_response, $args ) {

        $_response['args'] = $args;

        if ( $args['venue'] != 'wpi_mijireh_checkout' ) return $_response;

        global $wpi_settings;

        $gateway_settings = $wpi_settings['billing']['wpi_mijireh_checkout']['settings'];

        if(empty($args['payer_email'])) {
          $return['payment_status'] = 'validation_fail';
          $return['missing_data']['user_email'] = __('An e-mail address is required.', ud_get_wp_invoice_spc()->domain);
        }

        if(empty($args['payer_first_name'])) {
          $return['payment_status'] = 'validation_fail';
          $return['missing_data']['first_name'] = __('You must enter a first name.', ud_get_wp_invoice_spc()->domain);
        }

        if(empty($args['payer_last_name'])) {
          $return['payment_status'] = 'validation_fail';
          $return['missing_data']['last_name'] = __('You must enter a last name.', ud_get_wp_invoice_spc()->domain);
        }

        if(empty($args['state'])) {
          $return['payment_status'] = 'validation_fail';
          $return['missing_data']['state'] = __('State is required.', ud_get_wp_invoice_spc()->domain);
        }

        if(empty($args['city'])) {
          $return['payment_status'] = 'validation_fail';
          $return['missing_data']['city'] = __('City is required.', ud_get_wp_invoice_spc()->domain);
        }

        if(empty($args['zip'])) {
          $return['payment_status'] = 'validation_fail';
          $return['missing_data']['zip'] = __('Zip is required.', ud_get_wp_invoice_spc()->domain);
        }

        if(!empty($return['payment_status'])) {
          echo json_encode($return);
          die();
        }

        try {

          $access_key = $gateway_settings['access_key']['value'];

          Mijireh::$access_key = $access_key;

          $this->order = new Order();

          $this->order->add_item( __('Invoice '.$args['trans_id'].' - '.$args['description'], ud_get_wp_invoice_mijireh_checkout()->domain), number_format( (float)$args['amount'], 2, '.', '' ) );

          $this->order->total = number_format( (float)$args['amount'], 2, '.', '' );

          $this->order->return_url = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_mijireh_checkout&m=checkout');
          $this->order->shipping = 0;

          $this->order->email = $args['payer_email'];
          $this->order->first_name = $args['payer_first_name'];
          $this->order->last_name = $args['payer_last_name'];

          $address = new Address();
          $address->street = $args['address'];
          $address->city = $args['city'];
          $address->state_province = $args['state'];
          $address->zip_code = $args['zip'];
          $address->country = $args['country'];
          $address->phone = $args['phone_number'];

          $this->order->set_shipping_address($address);
          $this->order->set_billing_address($address);

          $this->order->add_meta_data( 'trans_id', $args['trans_id'] );

          $this->order = apply_filters( 'wpi::spc::mc_checkout_payment_order', $this->order );

          $this->order->create();

          /**
           * Change some data while creating invoice for this transaction
           */
          add_filter( 'wpi::spc::checkout_process_return', array( $this, 'checkout_process_return' ) );
          add_action( 'wpi_successful_payment', array( $this, 'on_successful_invoice_creation' ) );

          $_response['payment_status'] = \WPI_Payment_Api::WPI_METHOD_STATUS_COMPLETE;
          $_response['receiver_email'] = !empty( $args['payer_email'] ) ? $args['payer_email'] : '';
          $_response['payment_method'] = 'wpi_mijireh_checkout';
          $_response['transaction_id'] = $this->order->order_number;

        } catch (\Exception $e) {
          $_response['error_message'] = $e->getMessage();
        }

        return $_response;

      }

      /**
       * Do load gateway
       */
      public function load_gateway() {
        global $wpi_settings;

        $default_headers = array(
            'Name' => 'Name',
            'Version' => 'Version',
            'Description' => 'Description'
        );

        $slug = 'wpi_mijireh_checkout';

        if ( !file_exists( ud_get_wp_invoice_mijireh_checkout()->path('lib/classes/', 'dir') . 'class-gateway.php' ) ) {
          return;
        }

        $file = ud_get_wp_invoice_mijireh_checkout()->path('lib/classes/', 'dir') . 'class-gateway.php';

        $plugin_data = get_file_data( $file, $default_headers, 'plugin' );
        $wpi_settings['installed_gateways'][$slug]['name'] = $plugin_data['Name'];
        $wpi_settings['installed_gateways'][$slug]['version'] = $plugin_data['Version'];
        $wpi_settings['installed_gateways'][$slug]['description'] = $plugin_data['Description'];

        if (WP_DEBUG) {
          include_once( $file );
        } else {
          @include_once( $file );
        }

        //** Initialize the object, then update the billing permissions to show whats in the object */
        eval("\$wpi_settings['installed_gateways']['" . $slug . "']['object'] = new UsabilityDynamics\WPI_MC\Gateway();");

        //** Sync our options */
        \wpi_gateway_base::sync_billing_objects();
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
