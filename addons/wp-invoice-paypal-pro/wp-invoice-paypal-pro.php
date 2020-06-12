<?php
/**
 * Plugin Name: WP-Invoice: Paypal Pro
 * Plugin URI: https://www.usabilitydynamics.com/product/wp-invoice-paypal-pro/
 * Description: PayPal Payment Pro Support for WP-Invoice Plugin
 * Author: Usability Dynamics, Inc.
 * Version: 1.0.2
 * Requires at least: 4.0
 * Tested up to: 4.4.2
 * Text Domain: wpi-ppp
 * Author URI: http://www.usabilitydynamics.com
 * GitHub Plugin URI: wp-invoice/wp-invoice-paypal-pro
 * GitHub Branch: v1.0
 *
 * Copyright 2012 - 2016 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

if( !function_exists( 'ud_get_wp_invoice_paypal_pro' ) ) {

  /**
   * Returns WP-Invoice Paypal Pro Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_get_wp_invoice_paypal_pro( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPI_PPP\Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_invoice_paypal_pro' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_check_wp_invoice_paypal_pro() {
    global $_ud_wp_invoice_error;
    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wpi-ppp' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wpi-ppp' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wpi-ppp' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPI_PPP\Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wpi-ppp' ) );
      }
    } catch( Exception $e ) {
      $_ud_wp_invoice_error = $e->getMessage();
      return false;
    }
    return true;
  }

}

if( ud_check_wp_invoice_paypal_pro() ) {
  //** Initialize. */
  ud_get_wp_invoice_paypal_pro();
}