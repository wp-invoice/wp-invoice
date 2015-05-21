<?php

/**
 * Class My_WP_List_Table
 *
 * Overview list implementation
 */



class New_WPI_List_Table extends \UsabilityDynamics\WPLT\WP_List_Table {

  public $current_invoice = array();

  /**
   * @param array $args
   */
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
   * @param $object
   * @return object
   */
  public function get_invoice_object( $object ) {

    if ( array_key_exists( $object->ID, $this->current_invoice ) ) {
      return $this->current_invoice[$object->ID];
    }

    $this->current_invoice[$object->ID] = new WPI_Invoice();
    $this->current_invoice[$object->ID]->load_invoice("id={$object->ID}");

    return $this->current_invoice[$object->ID] = (object)$this->current_invoice[$object->ID]->data;

  }

  /**
   * Set columns for your table
   */
  public function get_columns() {
    return array(
      'cb'        => '<input type="checkbox" />',
      'title'     => __( 'Title', WPI ),
      'collected' => __( 'Collected', WPI ),
      'recipient' => __( 'Recipient', WPI ),
      'updated'   => __( 'Updated', WPI ),
      'status'    => __( 'Status', WPI ),
      'type'      => __( 'Type', WPI ),
      'id'        => __( 'ID', WPI )
    );
  }

  /**
   * Collected column
   *
   * @param $post
   * @return string
   */
  public function column_collected( $post ){
    global $wpi_settings;

    $r = '';
    $post = $this->get_invoice_object( $post );

    if ( !empty( $post->subtotal ) ) {
      if ( $post->type == 'single_payment' ) {
        $r .= (!empty($wpi_settings['currency']['symbol'][$post->default_currency_code])?$wpi_settings['currency']['symbol'][$post->default_currency_code]:'$') . wp_invoice_currency_format( !empty( $post->total_payments )?$post->total_payments:0 );
      } elseif ( $post->type == 'recurring' ) {
        $r .= (!empty($wpi_settings['currency']['symbol'][$post->default_currency_code])?$wpi_settings['currency']['symbol'][$post->default_currency_code]:'$') . wp_invoice_currency_format( !empty( $post->total_payments )?$post->total_payments:0 );
      } else {
        $r .= (!empty($wpi_settings['currency']['symbol'][$post->default_currency_code])?$wpi_settings['currency']['symbol'][$post->default_currency_code]:'$') . wp_invoice_currency_format( !empty( $post->adjustments )?abs($post->adjustments):0 )
            ." <span style='color:#aaaaaa;'>" . __('of', WPI) ." ".
            (!empty($wpi_settings['currency']['symbol'][$post->default_currency_code])?$wpi_settings['currency']['symbol'][$post->default_currency_code]:'$') . wp_invoice_currency_format($post->subtotal-(!empty($post->total_discount)?$post->total_discount:0)+(!empty($post->total_tax)?$post->total_tax:0))
            ."</span>";
      }
    } else {
      $r .= (!empty($wpi_settings['currency']['symbol'][$post->default_currency_code])?$wpi_settings['currency']['symbol'][$post->default_currency_code]:'$') . wp_invoice_currency_format(0);
    }

    return $r;

  }

  /**
   * @param $post
   * @return string
   */
  public function column_recipient( $post ) {
    $r = '';
    $post = $this->get_invoice_object( $post );

    if(class_exists('WP_CRM_Core')) {
      $edit_user_url = admin_url("admin.php?page=wp_crm_add_new&user_id={$post->user_data['ID']}");
    } else {
      $edit_user_url =  admin_url("user-edit.php?user_id={$post->user_data['ID']}");
    }

    $r .= '<ul>';
    $r .= '<li><a href="'.$edit_user_url.'">' . $post->user_data['display_name'] . '</a></li>';
    $r .= '<li>' . $post->user_data['user_email'] . '</li>';
    $r .= '</ul>';

    return $r;
  }

  /**
   * @param $post
   * @return string
   */
  public function column_updated( $post ) {
    $r = '';
    $post = $this->get_invoice_object( $post );

    if ( !empty( $post->post_status ) ) {
      if ( $post->post_status == 'paid' ) {
        $r .= get_post_status_object($post->post_status)->label.' '.human_time_diff(strtotime($post->post_modified), (time() + get_option('gmt_offset')*60*60)).__(' ago', WPI);
      } else {
        $r .= human_time_diff(strtotime($post->post_modified), (time() + get_option('gmt_offset')*60*60)).__(' ago', WPI);
      }
    } else {
      $r .= date(get_option('date_format'), strtotime($post->post_date));
    }

    return $r;
  }

  /**
   * @param $post
   * @return mixed
   */
  public function column_status( $post ) {
    return get_post_status_object($post->post_status)->label;
  }

  /**
   * @param $post
   * @return mixed
   */
  public function column_type( $post ) {
    global $wpi_settings;
    return $wpi_settings['types'][$post->type]['label'];
  }

  /**
   * @param $post
   * @return string
   */
  public function column_id( $post ) {
    $post = $this->get_invoice_object( $post );

    $invoice_id = $post->invoice_id;
    if(!empty($post->custom_id)) {
      $invoice_id = $post->custom_id;
    }
    return '<a href="' . get_invoice_permalink($post->invoice_id) . '" target="_blank">'.apply_filters("wpi_attribute_invoice_id", $invoice_id, $post).'</a>';
  }

}