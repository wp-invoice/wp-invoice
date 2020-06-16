<?php
/**
 * Helper Functions for invoice template
 */

if ( !function_exists( 'wpi_business_name' ) ) {
  /**
   * Display business name
   */
  function wpi_business_name() {
    $core = WPI_Core::getInstance();
    echo apply_filters('wpi_business_name', $core->Settings->options['business_name']);
  }
}

if ( !function_exists( 'wpi_business_address' ) ) {
  /**
   * Display business address
   */
  function wpi_business_address() {
    $core = WPI_Core::getInstance();
    echo apply_filters('wpi_business_address', $core->Settings->options['business_address']);
  }
}

if ( !function_exists( 'wpi_business_phone' ) ) {
  /**
   * Display business phone
   */
  function wpi_business_phone() {
    $core = WPI_Core::getInstance();
    echo apply_filters('wpi_business_phone', $core->Settings->options['business_phone']);
  }
}