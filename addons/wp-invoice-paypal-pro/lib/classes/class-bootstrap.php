<?php

/**
 * Bootstrap
 *
 * @since 1.0.0
 */

namespace UsabilityDynamics\WPI_PPP {

  if (!class_exists('UsabilityDynamics\WPI_PPP\Bootstrap')) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {

      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI_PPP\Bootstrap object
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
        
        add_filter('wpi_spc_pre_process_data', array($this, 'pre_process_data'), 10, 2);
      }
      
      /**
       * 
       * @param type $data
       * @param type $post
       * @return type
       */
      public function pre_process_data( $data, $post ) {
        
        switch( $data['venue'] ) {
          
          case 'wpi_paypal_pro':
            $data['credit_card_type'] = $post['wpi_checkout']['billing']['credit_card_type'];
            break;
          
          default: break;
        }
        
        return $data;
      }
      
      /**
       * 
       * @param type $methods
       * @return type
       */
      public function add_api_method( $methods ) {
        
        $methods['wpi_paypal_pro'] = array(true);
        
        return $methods;
      }
      
      /**
       * 
       * @param type $_response
       * @param type $args
       * @return type
       */
      public function api_method_implementation( $_response, $args ) {

        if ( $args['venue'] != 'wpi_paypal_pro' ) return $_response;

        global $wpi_settings;
        
        $_paypal_pro_billing = $wpi_settings['billing']['wpi_paypal_pro']['settings'];
        
        try {
          
          $request = new PayPalDirectPayment(
            $_paypal_pro_billing['api_username']['value'], 
            $_paypal_pro_billing['api_password']['value'], 
            $_paypal_pro_billing['api_signature']['value'], 
            $_paypal_pro_billing['test_mode']['value'] === 'Y' ? true : false
          );
          
          $request->Amount         = $args['amount'];
          $request->CardType       = $args['credit_card_type'];
          $request->CardNumber     = $args['cc_number'];
          $request->CardExpiration = $args['cc_expiration'];
          $request->CardCVV2       = $args['cc_code'];
          
          $request->FirstName      = $args['payer_first_name'];
          $request->LastName       = $args['payer_last_name'];
          $request->Address        = $args['address'];
          $request->City           = $args['city'];
          $request->State          = $args['state'];
          $request->Zip            = $args['zip'];
          $request->Country        = $args['country'];
          $request->Email          = $args['payer_email'];
          $request->Currency       = $args['currency_code'];
          $request->Description    = $args['description'];
          $request->InvoiceNumber  = $args['trans_id'];
        
          $request->Send();
          
          if ($request->Response['ACK'] === 'Success') {
            $_response['payment_status'] = \WPI_Payment_Api::WPI_METHOD_STATUS_COMPLETE;
            $_response['receiver_email'] = !empty( $args['payer_email'] ) ? $args['payer_email'] : '';
            $_response['payment_method'] = 'wpi_paypal_pro';
            $_response['transaction_id'] = $request->Response['TRANSACTIONID'];
          } else {
            $_response['error_message'] = $request->Response['L_LONGMESSAGE0'];
          }
          
        } catch (\Exception $e) {

          $_response['error_message'] = $e->getMessage();
        } catch (PayPalInvalidValueException $e) {

          $_response['error_message'] = $e->getMessage();
        } catch (PayPalUndefinedMethodException $e) {

          $_response['error_message'] = $e->getMessage();
        }
        
        return $_response;
        
      }

      /**
       * 
       * @param string $path
       * @param type $gateway
       * @return string
       */
      public function checkout_form_template( $path, $gateway ) {
        if ( $gateway === 'wpi_paypal_pro' ) {
          $path = ud_get_wp_invoice_paypal_pro()->path('static/views/', 'dir') . "paypal_pro_checkout.tpl.php";
        }
        return $path;
      }
      
      /**
       * 
       */
      public function accept_for_spc( $gateways ) {
        $gateways[] = 'wpi_paypal_pro';
        return $gateways;
      }
      
      /**
       * 
       */
      public function correct_script_url( $url, $gateway ) {
        if ( $gateway === 'wpi_paypal_pro' ) {
          $url = ud_get_wp_invoice_paypal_pro()->path('static/scripts/', 'url') . "paypal_pro_checkout.js";
        }
        return $url;
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
        
        $slug = 'wpi_paypal_pro';
        
        if ( !file_exists( ud_get_wp_invoice_paypal_pro()->path('lib/classes/', 'dir') . 'class-gateway.php' ) ) {
          return;
        }
        
        $file = ud_get_wp_invoice_paypal_pro()->path('lib/classes/', 'dir') . 'class-gateway.php';

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
        eval("\$wpi_settings['installed_gateways']['" . $slug . "']['object'] = new UsabilityDynamics\WPI_PPP\Gateway();");

        //** Sync our options */
        \wpi_gateway_base::sync_billing_objects();
      }

      /**
       * Plugin Activation
       *
       */
      public function activate() {
        
      }

      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {
        
      }

    }

  }
}