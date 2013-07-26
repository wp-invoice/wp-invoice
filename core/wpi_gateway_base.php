<?php

/**
 * This class represents the base class for any WPI payment gateway.  All the functions
 * herein should be overridden.  The child classes should be in the 'gateways' folder.
 * @since 3.0
 */
abstract class wpi_gateway_base {
  /**
   * This function sets the 'type' variable for us, anything that overrides this should
   * call 'parent::__construct'
   * @since 1.0
   */
  function __construct() {
    /** Set the class name */
    $this->type = get_class($this);
    add_filter( 'sync_billing_update', array( 'wpi_gateway_base', 'sync_billing_filter' ), 10, 3 );
  }
  
  /**
   * This function handles the display of the admin settings for the individual payment gateway 
   * It is called on the settings page and on the invoice page 
   * @param string $args A URL encoded string that contains all the arguments
   * @since 3.0
   */  
  function frontend_display($args = '', $from_ajax = false){
    global $wpdb, $wpi_settings, $invoice;
        /** Setup defaults, and extract the variables */
    $defaults = array();
        extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    /** Include the template file required */
    include('gateways/templates/payment_header.tpl.php');
    /** Why eval here? korotkov@ud */
    //eval("include('gateways/templates/".$this->type."-frontend.tpl.php');");
    include('gateways/templates/'.$this->type.'-frontend.tpl.php');
    include('gateways/templates/payment_footer.tpl.php');
  }
  
  /**
   * This function handles AJAX payment type changes - it is simply a wrapper for
   * the 'frontend_display' function
   * @since 3.0
   */
  function change_payment_form_ajax(){
    global $wpdb, $wpi_settings, $invoice;
    /** Pull in the invoice */
    $the_invoice = new WPI_Invoice();
    $invoice = $the_invoice->load_invoice("return=true&id=".wpi_invoice_id_to_post_id($_REQUEST['invoice_id']));
    /** We have the invoice, call the frontend_display */
    $wpi_settings['installed_gateways'][$_REQUEST['type']]['object']->frontend_display($invoice, true);
    die();
  }
  
  /**
   * This function handles the processing of the payments - it should be overrideen in child classes
   * @param string $args The args for the fucnction
   * @since 3.0
   */
  function process_payment(){
    global $wpi_settings, $invoice;
    /** Pull the invoice */
    $the_invoice = new WPI_Invoice();
    $invoice = $the_invoice->load_invoice("return=true&id=".wpi_invoice_id_to_post_id($_REQUEST['invoice_id']));
    /** Call the child function based on the wpi_type variable sent */
    $wpi_settings['installed_gateways'][$_REQUEST['type']]['object']->process_payment();
    die();
  }

  /**
   * This function listens for any server call backs for the payment gateway (i.e. PayPal IPN)
   * It gets called by the ajax action: 
   * http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=typeofpaymentobject
   * It should be overridden
   * @since 3.0
   */  
  function server_callback(){
    global $wpi_settings;
    /** Call the actual function that does the processing on the type of object we have */
    $wpi_settings['installed_gateways'][$_REQUEST['type']]['object']->server_callback();
    die();
  }
  
  /**
   * This function syncs our options table with our actual object
   * @since 3.0
   */
  function sync_billing_objects(){
    global $wpi_settings;
    
    if(!isset($wpi_settings['billing']) || !is_array($wpi_settings['billing'])) {
      $wpi_settings['billing'] = array();
    }
    
    $g = array();
    /* Handle Merging of arrays to custom variable */
    foreach($wpi_settings['installed_gateways'] as $slug => $gateway){
      
      if ( !empty( $gateway['object']->options ) )
        foreach ($gateway['object']->options as $option_key => $option) {

          switch ($option_key) {
            /* Handle Settings element. */
            case 'settings':
              if(is_array($option)) {
                foreach($option as $k => $v) {
                  if (!isset($wpi_settings['billing'][$slug][$option_key][$k])) {
                    $g[$slug][$option_key][$k] = $v;
                  } else {
                    if(is_array($v)) {
                      $g[$slug][$option_key][$k] = apply_filters( 'sync_billing_update', $k, $v, wp_parse_args($wpi_settings['billing'][$slug][$option_key][$k], $v) );
                    } else {
                      $g[$slug][$option_key][$k] = !empty($wpi_settings['billing'][$slug][$option_key][$k]) ? $wpi_settings['billing'][$slug][$option_key][$k] : $v;
                    }
                  }

                }
              } else {
                $g[$slug][$option_key] = !empty($wpi_settings['billing'][$slug][$option_key]) ? $wpi_settings['billing'][$slug][$option_key] : $option;
              }
              break;

            default:
              if(!isset($wpi_settings['billing'][$slug][$option_key])) {
                $g[$slug][$option_key] = $option;
              } else {
                if(!is_array($option)) {
                  $g[$slug][$option_key] = !empty($wpi_settings['billing'][$slug][$option_key]) ? $wpi_settings['billing'][$slug][$option_key] : $option;
                } else {
                  $g[$slug][$option_key] = wp_parse_args($wpi_settings['billing'][$slug][$option_key], $option);
                }
              }
              break;
          }

        }
      
      /** Do it recursively, so both items have the same values */
      if ( !empty( $wpi_settings['installed_gateways'][$slug]['object']->options ) )
        $wpi_settings['installed_gateways'][$slug]['object']->options = $g[$slug];
      $wpi_settings['billing'][$slug] = $g[$slug];
      
    }
    
  }
  
  public function sync_billing_filter( $setting_slug, $new_setting_array, $def_setting_array ) {
    
    if ( $setting_slug == 'ipn' || $setting_slug == 'silent_post_url' ) {
      return $new_setting_array;
    }
    
    return $def_setting_array;
    
  }
	
	/**
	 * CRM user_meta updating on payment done
	 * 
	 * @global type $invoice
	 * @param type $data
	 * @return type 
	 */
  function user_meta_updated( $data ) {
    global $invoice;
    // CRM data updating
    if ( !class_exists('WP_CRM_Core') ) return;
    
    $crm_attributes = WPI_Functions::get_wpi_crm_attributes();
    if ( empty( $crm_attributes ) ) return;
    
    $wp_users_id = $invoice['user_data']['ID'];
    
    foreach ( $data as $key => $value ) {
      if ( key_exists( $key, $crm_attributes ) ) {
        update_user_meta($wp_users_id, $key, $value);
      }
    }
    
  }
}