<?php
/**
 * Plugin Name: WP-Invoice
 * Plugin URI: http://usabilitydynamics.com/products/wp-invoice/
 * Description: Send itemized web-invoices directly to your clients. Credit card payments may be accepted via Authorize.net, Stripe, 2Checkout, MerchantPlus NaviGate, or PayPal account. Recurring billing is also available via Authorize.net's ARB. Visit <a href="admin.php?page=wpi_page_settings">WP-Invoice Settings Page</a> to setup.
 * Author: UsabilityDynamics.com
 * Version: 4.0.0
 * Author URI: http://UsabilityDynamics.com/
 * Copyright 2011 - 2014  Usability Dynamics, Inc. (email : info@UsabilityDynamics.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if( !function_exists( 'ud_get_wp_invoice' ) ) {

  if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once ( __DIR__ . '/vendor/autoload.php' );
  }

  //** Define WPI Version */
  if ( !defined( 'WP_INVOICE_VERSION_NUM' ) ) {
    define( 'WP_INVOICE_VERSION_NUM', '4.0.0' );
  }

  //** Define shorthand for transdomain */
  if ( !defined( 'WPI' ) ) {
    define( 'WPI', 'wp-invoice' );
  }

  //** Define WPI directory name - used to identify WPI templates */
  if ( !defined( 'WPI_Dir' ) ) {
    define( 'WPI_Dir', basename( dirname( __FILE__ ) ) );
  }

  //** Path for WPI Directory */
  if ( !defined( 'WPI_Path' ) ) {
    define( 'WPI_Path', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
  }

  //** URL for WPI Directory */
  if ( !defined( 'WPI_URL' ) ) {
    define( 'WPI_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
  }
  
  if ( !defined( 'WPI_STATIC_URL' ) ) {
    define( 'WPI_STATIC_URL', WPI_URL . '/static' );
  }
  
  if ( !defined( 'WPI_STATIC_PATH' ) ) {
    define( 'WPI_STATIC_PATH', WPI_Path . '/static' );
  }

  //** Directory paths */
  if ( !defined( 'WPI_Gateways_Path' ) ) {
    define( 'WPI_Gateways_Path', WPI_Path . '/lib/gateways' );
  }
  
  if ( !defined( 'WPI_Gateways_URL' ) ) {
    define( 'WPI_Gateways_URL', WPI_URL . '/lib/gateways' );
  }
  
  if ( !defined( 'WPI_Templates_Path' ) ) {
    define( 'WPI_Templates_Path', WPI_Path . '/static/template' );
  }
  
  if ( !defined( 'WPI_Templates_URL' ) ) {
    define( 'WPI_Templates_URL', WPI_URL . '/static/template' );
  }
  
  /**
   * Returns WP_Invoice object
   *
   * @author korotkov@UD
   * @since 4.0.0
   */
  function ud_get_wp_invoice( $key = false, $default = null ) {
    if( class_exists( '\UsabilityDynamics\WPI\Bootstrap' ) ) {
      $instance = \UsabilityDynamics\WPI\Bootstrap::get_instance();
      return $key ? $instance->get( $key, $default ) : $instance;
    }
    return false;
  }

}

//** Initialize. */
ud_get_wp_invoice();
