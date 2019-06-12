<?php
/**
 * Plugin Name: WP-Invoice
 * Plugin URI: https://www.usabilitydynamics.com/product/wp-invoice
 * Description: WP-Invoice lets WordPress blog owners send itemized invoices to their clients. Ideal for web developers, SEO consultants, general contractors, or anyone with a WordPress blog and clients to bill.
 * Author: Usability Dynamics, Inc.
 * Version: 4.2.2
 * Requires at least: 4.0
 * Tested up to: 5.1
 * Text Domain: wp-invoice
 * Author URI: http://www.usabilitydynamics.com
 * GitHub Plugin URI: wp-invoice/wp-invoice
 * GitHub Branch: v4.0
 * Support: https://wordpress.org/support/plugin/wp-invoice
 * UserVoice: http://feedback.usabilitydynamics.com/forums/9692-wp-invoice
 *
 * Copyright 2012 - 2018 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

//** Define WPI Version */
if ( !defined( 'WP_INVOICE_VERSION_NUM' ) ) {
  define( 'WP_INVOICE_VERSION_NUM', '4.2.2' );
}

//** Define shorthand for transdomain */
if ( !defined( 'WPI' ) ) {
  define( 'WPI', 'wp-invoice' );
}

if( !function_exists( 'ud_get_wp_invoice' ) ) {

  /**
   * Returns  Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 4.0.0
   */
  function ud_get_wp_invoice( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPI\WPI_Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_invoice' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 4.0.0
   */
  function ud_check_wp_invoice() {
    global $_ud_wp_invoice_error;
    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp-invoice' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp-invoice' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp-invoice' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPI\WPI_Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp-invoice' ) );
      }
    } catch( Exception $e ) {
      $_ud_wp_invoice_error = $e->getMessage();
      return false;
    }
    return true;
  }

}

if( !function_exists( 'ud_my_wp_plugin_message' ) ) {
  /**
   * Renders admin notes in case there are errors on plugin init
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_wp_invoice_message() {
    global $_ud_wp_invoice_error;
    if( !empty( $_ud_wp_invoice_error ) ) {
      $message = sprintf( __( '<p><b>%s</b> can not be initialized. %s</p>', 'wp-invoice' ), 'WP-Invoice', $_ud_wp_invoice_error );
      echo '<div class="error fade" style="padding:11px;">' . $message . '</div>';
    }
  }
  add_action( 'admin_notices', 'ud_wp_invoice_message' );
}

if( ud_check_wp_invoice() ) {
  //** Initialize. */
  ud_get_wp_invoice();
}