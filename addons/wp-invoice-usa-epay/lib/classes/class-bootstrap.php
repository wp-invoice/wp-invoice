<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPI_USA_EPAY {

  if( !class_exists( 'UsabilityDynamics\WPI_USA_EPAY\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI_USA_EPAY\Bootstrap object
       */
      protected static $instance = null;
      
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
        
      }
      
      /**
       * Add for SPC support
       */
      public function accept_for_spc( $gateways ) {
        $gateways[] = 'wpi_usa_epay';
        return $gateways;
      }
      
      /**
       * Correct script URL
       */
      public function correct_script_url( $url, $gateway ) {
        if ( $gateway === 'wpi_usa_epay' ) {
          $url = ud_get_wp_invoice_usa_epay()->path('static/scripts/', 'url') . "usa_epay_checkout.js";
        }
        return $url;
      }
      
      /**
       * Correct form template
       */
      public function checkout_form_template( $path, $gateway ) {
        if ( $gateway === 'wpi_usa_epay' ) {
          $path = ud_get_wp_invoice_usa_epay()->path('static/views/', 'dir') . "usa_epay_checkout.tpl.php";
        }
        return $path;
      }
      
      /**
       * Add method to API
       */
      public function add_api_method( $methods ) {
        
        $methods['wpi_usa_epay'] = array(true);
        
        return $methods;
      }
      
      /**
       * API method implementation
       */
      public function api_method_implementation( $_response, $args ) {

        if ( $args['venue'] != 'wpi_usa_epay' ) return $_response;

        global $wpi_settings;
        
        $gateway_settings = $wpi_settings['billing']['wpi_usa_epay']['settings'];
        
        try {
          
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
          if(empty($args['cc_number'])) {
            $return['payment_status'] = 'validation_fail';
            $return['missing_data']['cc_number'] = __('You must enter a valid credit card number.', ud_get_wp_invoice_spc()->domain);
          }
          if(empty($args['cc_expiration'])) {
            $return['payment_status'] = 'validation_fail';
            $return['missing_data']['cc_expiration'] = __('You must enter an expiration date.', ud_get_wp_invoice_spc()->domain);
          }
          if(empty($args['cc_code'])) {
            $return['payment_status'] = 'validation_fail';
            $return['missing_data']['cc_code'] = __('You must enter a card code.', ud_get_wp_invoice_spc()->domain);
          }
          
          if(!empty($return['payment_status'])) {
            echo json_encode($return);
            die();
          }
          
          $request = new umTransaction();
            
          $request->key    = $gateway_settings['key']['value'];
          $request->pin    = $gateway_settings['pin']['value'];
          $request->usesandbox = $gateway_settings['usesandbox']['value'] == 'Y';
          $request->ip     = $_SERVER['REMOTE_ADDR'];
          $request->testmode = 0;
          $request->command = "cc:sale";

          $request->amount = $args['amount'];
          $request->card   = $args['cc_number'];
          $request->exp    = $args['cc_expiration'];
          $request->cvv2   = $args['cc_code'];
          $request->billfname = $args['payer_first_name'];
          $request->billlname = $args['payer_last_name'];
          $request->billstreet = $args['address'];
          $request->billcity = $args['city'];
          $request->billstate = $args['state'];
          $request->billzip = $args['zip'];
          $request->billcountry = $args['country'];
          $request->email = $args['payer_email'];
          $request->currency = $args['currency_code'];
          $request->description = $args['description'];
          $request->invoice = $args['trans_id'];

          $request->Process();

          if ($request->result === 'Approved') {
            $_response['payment_status'] = \WPI_Payment_Api::WPI_METHOD_STATUS_COMPLETE;
            $_response['receiver_email'] = !empty( $args['payer_email'] ) ? $args['payer_email'] : '';
            $_response['payment_method'] = 'wpi_usa_epay';
            $_response['transaction_id'] = $request->refnum;
          } else {
            $_response['error_message'] = $request->error;
          }
          
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
        
        $slug = 'wpi_usa_epay';
        
        $file = ud_get_wp_invoice_usa_epay()->path('lib/classes/', 'dir') . 'class-gateway.php';

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
        eval("\$wpi_settings['installed_gateways']['" . $slug . "']['object'] = new UsabilityDynamics\WPI_USA_EPAY\Gateway();");

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
