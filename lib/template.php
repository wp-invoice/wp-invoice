<?php

/**
 * Returns an invoice object as an array.
 * @param type $args
 */
function get_invoice( $args ) {
  if ( is_numeric( $args ) ) {
    $invoice_id = $args;
  } else {
    $defaults = array( 'invoice_id' => '', 'return_class' => false );
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
  }
  $invoice = new WPI_Invoice();
  $invoice->load_invoice( "id=$invoice_id" );

  if ( !empty( $invoice->error ) && $invoice->error ) {
    return sprintf( __( "Invoice %s not found.", ud_get_wp_invoice()->domain ), $invoice_id );
  }

  if ( !empty( $return_class ) && $return_class ) {
    return $invoice;
  }

  return $invoice->data;
}

/**
 * Invoice lookup function
 * If return is passed as true, function is returned.
 *
 * @global type $wpi_settings
 *
 * @param type $args
 *
 * @return type
 */
function wp_invoice_lookup( $args = '' ) {
  global $wpi_settings, $current_user;
  
  $result = '';

  $defaults = array(
    'message' => __( 'Enter Invoice ID', ud_get_wp_invoice()->domain ),
    'button' => __( 'Lookup', ud_get_wp_invoice()->domain ),
    'return' => true
  );
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

  if ( !$current_user->ID ) {
    return;
  }

  ob_start();
  if ( WPI_Functions::wpi_use_custom_template( 'invoice_lookup.php' ) ) {
    include( $wpi_settings[ 'frontend_template_path' ] . 'invoice_lookup.php' );
  } else {
    include( $wpi_settings[ 'default_template_path' ] . 'invoice_lookup.php' );
  }
  $result .= ob_get_clean();

  if ( $return ) { 
    return $result;
  }
  echo $result;
}

/**
 * TO keep wpi naming structure
 *
 * @param type $args
 *
 * @return type
 */
function wpi_invoice_lookup( $args = '' ) {
  return wp_invoice_lookup( $args );
}

/**
 * Draw widget by shortcode
 *
 * @param array $args
 */
function wp_invoice_history( $args = '' ) {
  ob_start();
  echo the_widget( 'InvoiceHistoryWidget', $args );
  return ob_get_clean();
}