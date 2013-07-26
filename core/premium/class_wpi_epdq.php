<?php
/**
Name: ePDQ 
Class: wpi_epdq
Global Variable: wpi_epdq
Internal Slug: wpi_epdq
JS Slug: wpi_epdq
Version: 1.0
Description: WP-Invoice ePDQ Gateway
*/

class wpi_epdq extends wpi_gateway_base {
  
  var $options = array(
    'name' => 'ePDQ',
    'allow' => '',
    'default_option' => '',
    'settings' => array(
      'address' => array(
        'label' => "Address",
        'value' => ''
      )
    )
  );

  function wpi_premium_loaded() {
    global $wpi_settings, $wpi_epdq;
    
    $wpi_settings['installed_gateways']['wpi_epdq'] = $wpi_settings['installed_features']['wpi_epdq'];
    unset( $wpi_settings['installed_gateways']['wpi_epdq']['disabled'] );
    eval("\$wpi_settings['installed_gateways']['wpi_epdq']['object'] = new wpi_epdq();");
    parent::sync_billing_objects();
  }
  
} /** wpi_epdq */

add_action( 'wpi_premium_loaded', array( 'wpi_epdq', 'wpi_premium_loaded' ) );
?>
