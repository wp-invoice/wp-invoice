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

    //** Process action */
    $invoice_ids = array();
    foreach ( (array) $_REQUEST[ 'post_ids' ] as $ID ) {
      //** Perfom action */
      $this_invoice = new WPI_Invoice();
      $this_invoice->load_invoice( "id={$ID}" );
      $invoice_id = $this_invoice->data[ 'invoice_id' ];
      switch ( $action ) {
        case 'trash':
          if ( $this_invoice->trash() ) {
            $invoice_ids[ ] = $invoice_id;
          }
          break;
        case 'delete':
          if ( $this_invoice->delete() ) {
            $invoice_ids[ ] = $invoice_id;
          }
          break;
        case 'untrash':
          if ( $this_invoice->untrash() ) {
            $invoice_ids[ ] = $invoice_id;
          }
          break;
        case 'unarchive':
          if ( $this_invoice->unarchive() ) {
            $invoice_ids[ ] = $invoice_id;
          }
          break;
        case 'archive':
          if ( $this_invoice->archive() ) {
            $invoice_ids[ ] = $invoice_id;
          }
          break;
      }
    }

    if ( $status ) {
      $this->message = 'Successfully ' . $status;
    }

//    try {
//
//      switch( $this->current_action() ) {
//
//        case 'untrash':
//          if( empty( $_REQUEST[ 'post_ids' ] ) || !is_array( $_REQUEST[ 'post_ids' ] ) ) {
//            throw new \Exception( sprintf( __( 'Invalid request: no %s IDs provided.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label() ) );
//          }
//          $post_ids = $_REQUEST[ 'post_ids' ];
//          foreach( $post_ids as $post_id ) {
//            $post_id = (int)$post_id;
//            wp_untrash_post( $post_id );
//          }
//          $this->message = sprintf( __( 'Selected %s have been successfully restored from Trash.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label( 'plural' ) );
//          break;
//
//        case 'delete':
//          if( empty( $_REQUEST[ 'post_ids' ] ) || !is_array( $_REQUEST[ 'post_ids' ] ) ) {
//            throw new \Exception( sprintf( __( 'Invalid request: no %s IDs provided.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label() ) );
//          }
//          $post_ids = $_REQUEST[ 'post_ids' ];
//          $trashed = 0;
//          $deleted = 0;
//          foreach( $post_ids as $post_id ) {
//            $post_id = (int)$post_id;
//            if( get_post_status( $post_id ) == 'trash' ) {
//              $deleted++;
//              wp_delete_post( $post_id );
//            } else {
//              $trashed++;
//              wp_trash_post( $post_id );
//            }
//          }
//          if( $trashed > 0 && $deleted > 0 ) {
//            $this->message = sprintf( __( 'Selected %s have been successfully moved to Trash or deleted.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label( 'plural' ) );
//          } elseif( $trashed > 0 ) {
//            $this->message = sprintf( __( 'Selected %s have been successfully moved to Trash.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label( 'plural' ) );
//          } elseif( $deleted > 0 ) {
//            $this->message = sprintf( __( 'Selected %s have been successfully deleted.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label( 'plural' ) );
//          } else {
//            throw new \Exception( sprintf( __( 'No one %s was deleted.', ud_get_wp_property( 'domain' ) ), \WPP_F::property_label() ) );
//          }
//          break;
//
//        default:
//          //** Any custom action can be processed using action hook */
//          do_action( 'wpp::all_properties::process_bulk_action', $this->current_action() );
//          break;
//
//      }
//
//    } catch ( \Exception $e ) {
//      $this->error = $e->getMessage();
//    }

  }

}