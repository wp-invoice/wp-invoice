<?php

/**
 * Class My_WP_List_Table
 *
 * Overview list implementation
 */



class WPI_List_Table extends \UsabilityDynamics\WPLT\WP_List_Table {

  public function __construct( $args = array() ) {

    $this->args = wp_parse_args($args, array(

      'singular' => __( 'Invoice', WPI ),

      'plural' => __( 'Invoices', WPI ),

      'post_type' => 'wpi_object',
      'post_status' => 'all',
      'orderby' => 'ID',
      'order' => 'DESC'
    ));

    parent::__construct($this->args);

  }

  /**
   * Set columns for your table
   */
  public function get_columns() {
    return array(
      'title' => __('Title')
    );
  }

}