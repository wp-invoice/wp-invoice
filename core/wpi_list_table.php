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

    add_filter( 'wplt:orderby:is_numeric', array( __CLASS__, 'set_numeric_fields' ), 10, 2 );
    add_filter( 'wplt:orderby:meta_type', array( __CLASS__, 'set_correct_types' ), 10, 2 );
    add_filter( 'post_row_actions', array(__CLASS__, 'add_view_link'), 10, 2);

    parent::__construct($this->args);

  }

  /**
   * @param array $actions
   * @param bool $post
   */
  public static function add_view_link( $actions = array(), $post = false ) {

    if ( $post ) {
      if ( $post->post_status != 'trash' && $post->post_status != 'archived' ) {
        $actions['view'] = '<a target="_blank" href="'.get_invoice_permalink($post->ID).'">'.__( 'View', WPI ).'</a>';
      }
    }

    return $actions;

  }

  /**
   * @param $false
   * @param $slug
   */
  public static function set_numeric_fields( $false, $slug ) {
    if ( $slug == 'total_payments' ) {
      return true;
    }
    return $false;
  }

  public static function set_correct_types( $false, $slug ) {
    if ( $slug == 'date' || $slug == 'modified' ) {
      return 'DATETIME';
    }
    return $false;
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
      'updated'   => __( 'Created', WPI ),
      'created'   => __( 'Updated', WPI ),
      'status'    => __( 'Status', WPI ),
      'type'      => __( 'Type', WPI ),
      'id'        => __( 'ID', WPI )
    );
  }

  /**
   * Sortable columns
   *
   * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
   */
  public function get_sortable_columns() {
    $columns = array(
        'title' => array( 'title', false ),  //true means it's already sorted
        'collected' => array( 'total_payments', false ),
        'recipient' => array( 'user_email', false ),
        'updated'    => array( 'date', false ),
        'created' => array( 'modified', false )
    );

    return $columns;
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
    $post = $this->get_invoice_object( $post );

    return date(get_option('date_format').' \a\t '.get_option('time_format'), strtotime($post->post_date));
  }

  /**
   * @param $post
   * @return string
   */
  public function column_created( $post ) {
    $post = $this->get_invoice_object( $post );

    return date(get_option('date_format').' \a\t '.get_option('time_format'), strtotime($post->post_modified));
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

    $regular_id = false;

    $invoice_id = $post->invoice_id;
    if(!empty($post->custom_id)) {
      $invoice_id = $post->custom_id;
      $regular_id = $post->invoice_id;
    }
    return '<a href="' . get_invoice_permalink($post->invoice_id) . '" target="_blank">'.apply_filters("wpi_attribute_invoice_id", $invoice_id, $post). ($regular_id?(' ('.$regular_id.') '):'') .'</a>';
  }

  /**
   * Add Bulk Actions
   *
   * @return array
   */
  public function get_bulk_actions() {
    $actions = array();

    $actions['untrash'] = __( 'Restore', WPI );
    $actions['archive'] = __( 'Archive', WPI );
    $actions['delete'] = __( 'Delete Permanently', WPI );
    $actions['trash'] = __( 'Move to Trash', WPI );
    $actions['unarchive'] = __( 'Un-Archive', WPI );

    return $actions;
  }

  /**
   * Handle Bulk Action's request
   *
   */
  public function process_bulk_action() {

    $action = $this->current_action();

    $status = false;

    //** Set status */
    switch ( $action ) {
      case 'trash':
        $status = 'trashed';
        break;
      case 'delete':
        $status = 'deleted';
        break;
      case 'untrash':
        $status = 'restored';
        break;
      case 'unarchive':
        $status = 'un-archived';
        break;
      case 'archive':
        $status = 'archived';
        break;
    }

    $invoice_ids = array();
    if ( !empty( $_REQUEST[ 'post_ids' ] ) ) {
      foreach ((array)$_REQUEST['post_ids'] as $ID) {

        $this_invoice = new WPI_Invoice();
        $this_invoice->load_invoice("id={$ID}");
        $invoice_id = $this_invoice->data['invoice_id'];

        switch ($action) {
          case 'trash':
            if ($this_invoice->trash()) {
              $invoice_ids[] = $invoice_id;
            }
            break;
          case 'delete':
            if ($this_invoice->delete()) {
              $invoice_ids[] = $invoice_id;
            }
            break;
          case 'untrash':
            if ($this_invoice->untrash()) {
              $invoice_ids[] = $invoice_id;
            }
            break;
          case 'unarchive':
            if ($this_invoice->unarchive()) {
              $invoice_ids[] = $invoice_id;
            }
            break;
          case 'archive':
            if ($this_invoice->archive()) {
              $invoice_ids[] = $invoice_id;
            }
            break;
        }
      }
    }

    if ( $status ) {
      $this->message = 'Successfully ' . $status;
    }

  }

}