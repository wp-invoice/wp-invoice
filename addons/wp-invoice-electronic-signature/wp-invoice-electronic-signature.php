<?php
/**
 * Plugin Name: WP-Invoice: Electronic Signature
 * Plugin URI: https://www.usabilitydynamics.com
 * Description: Electronic Signature for WP-Invoice 4.0+
 * Author: Usability Dynamics, Inc.
 * Version: 1.0.2
 * Text Domain: wpies
 * Author URI: http://usabilitydynamics.com
 * Requires at least: 4.0
 * Tested up to: 4.4.1
 * GitHub Plugin URI: wp-invoice/wp-invoice-electronic-signature
 * GitHub Branch: v1.0
 * Support: https://wordpress.org/support/plugin/wp-invoice
 * UserVoice: http://feedback.usabilitydynamics.com/forums/9692-wp-invoice
 *
 * Copyright 2012 - 2015 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

if( !function_exists( 'ud_get_wp_invoice_electronic_signature' ) ) {

  /**
   * Returns WP-Invoice: Electronic Signature Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_get_wp_invoice_electronic_signature( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPIES\Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_invoice_electronic_signature' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_check_wp_invoice_electronic_signature() {
    global $_ud_wp_invoice_error;
    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wpies' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wpies' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wpies' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPIES\Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wpies' ) );
      }
    } catch( Exception $e ) {
      $_ud_wp_invoice_error = $e->getMessage();
      return false;
    }
    return true;
  }

}


if( ud_check_wp_invoice_electronic_signature() ) {
  //** Initialize. */
  ud_get_wp_invoice_electronic_signature();
}
