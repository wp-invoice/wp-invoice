<?php
/**
 * Plugin Name: WP-Invoice: PDF
 * Plugin URI: https://www.usabilitydynamics.com/product/wp-invoice-pdf/
 * Description: PDF feature allows you to easily generate PDF versions of your invoices, receipts and quotes. The generated files can be used as e-mail attachments, or displayed as links on the secure invoice pages. The feature is shipped with three unique and attractive PDF layouts.
 * Author: Usability Dynamics, Inc.
 * Version: 2.1.5
 * Requires at least: 4.0
 * Tested up to: 4.9.8
 * Text Domain: wp-invoice-pdf
 * Author URI: https://www.usabilitydynamics.com
 * GitHub Plugin URI: wp-invoice/wp-invoice-pdf
 * GitHub Branch: v2.0
 *
 * Copyright 2012 - 2018 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

if( !function_exists( 'ud_get_wp_invoice_pdf' ) ) {

  /**
   * Returns  Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 2.0.0
   */
  function ud_get_wp_invoice_pdf( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPI\WPI_PDF_Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_invoice_pdf' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 2.0.0
   */
  function ud_check_wp_invoice_pdf() {
    global $_ud_wp_invoice_error;
    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp-invoice-pdf' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp-invoice-pdf' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp-invoice-pdf' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPI\WPI_PDF_Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp-invoice-pdf' ) );
      }
    } catch( Exception $e ) {
      $_ud_wp_invoice_error = $e->getMessage();
      return false;
    }
    return true;
  }

}

if( ud_check_wp_invoice_pdf() ) {
  //** Initialize. */
  ud_get_wp_invoice_pdf();
}