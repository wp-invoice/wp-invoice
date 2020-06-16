<?php

/**
 * Define default event types
 */
define( 'WPI_EVENT_TYPE_ADD_PAYMENT', 'add_payment' );
define( 'WPI_EVENT_TYPE_ADD_CHARGE', 'add_charge' );
define( 'WPI_EVENT_TYPE_ADD_ADJUSTMENT', 'do_adjustment' );
define( 'WPI_EVENT_TYPE_ADD_REFUND', 'refund' );

/**
 * WP-Invoice AJAX Handler
 */
class WPI_Ajax {

  /**
   * Search user for invoice page metabox
   * @global object $wpdb
   */
  static function search_email() {
    global $wpdb, $blog_id;

    $users_found = $wpdb->get_results( "SELECT `u`.`user_email` as `id`, `u`.`user_email` as `title`
                                       FROM `{$wpdb->users}` as `u` INNER JOIN `{$wpdb->usermeta}` as `m`
                                         ON `u`.`ID` = `m`.`user_id`
                                       WHERE (`u`.`display_name` LIKE '%{$_REQUEST['s']}%'
                                         OR `u`.`user_email` LIKE '%{$_REQUEST['s']}%')
                                         AND `u`.`user_email` != ''
                                         AND `m`.`meta_key` = '{$wpdb->get_blog_prefix( $blog_id )}capabilities'
                                       GROUP BY `u`.`ID`
                                       LIMIT 10" );

    die( json_encode( $users_found ) );
  }

  /**
   * Search users for filter invoice section
   * @global object $wpdb
   */
  static function search_recipient() {
    global $wpdb, $blog_id;

    $users_found = $wpdb->get_results( "SELECT `u`.`ID`, `u`.`user_email` as `id`, CONCAT(`u`.`display_name`, ' (', `u`.`user_email`, ')') as `label`
                                       FROM `{$wpdb->users}` as `u` INNER JOIN `{$wpdb->usermeta}` as `m`
                                         ON `u`.`ID` = `m`.`user_id`
                                       WHERE (`u`.`display_name` LIKE '%{$_REQUEST['q']}%'
                                         OR `u`.`user_email` LIKE '%{$_REQUEST['q']}%')
                                         AND `u`.`user_email` != ''
                                         AND `m`.`meta_key` = '{$wpdb->get_blog_prefix( $blog_id )}capabilities'
                                       GROUP BY `u`.`ID`
                                       LIMIT 10" );

    die( json_encode( $users_found ) );
  }

  /**
   * Return user data in JSON format
   *
   * @todo add hooks to accomodate different user values
   * @since 3.0
   *
   */
  static function get_user_date( $user_email = false ) {

    if ( !$user_email ) {
      return;
    }

    $user_id = email_exists( $user_email );

    if ( !$user_id ) {
      return;
    }

    $user_data[ 'first_name' ] = get_user_meta( $user_id, 'first_name', true );
    $user_data[ 'last_name' ] = get_user_meta( $user_id, 'last_name', true );
    $user_data[ 'company_name' ] = get_user_meta( $user_id, 'company_name', true );
    $user_data[ 'phonenumber' ] = get_user_meta( $user_id, 'phonenumber', true );
    $user_data[ 'streetaddress' ] = get_user_meta( $user_id, 'streetaddress', true );
    $user_data[ 'city' ] = get_user_meta( $user_id, 'city', true );
    $user_data[ 'state' ] = get_user_meta( $user_id, 'state', true );
    $user_data[ 'zip' ] = get_user_meta( $user_id, 'zip', true );
    $user_data[ 'country' ] = get_user_meta( $user_id, 'country', true );

    if ( $user_data ) {
      echo json_encode( array( 'succes' => 'true', 'user_data' => $user_data ) );
    }
  }

  /**
   * Process special invoice-related event
   */
  static function process_manual_event() {
    global $wpi_settings;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      die( json_encode( array( 'success' => 'false', 'message' => __( 'You are not allowed to perform this action.', ud_get_wp_invoice()->domain ) ) ) );
    }
    
    $invoice_id = $_REQUEST[ 'invoice_id' ];
    $event_type = $_REQUEST[ 'event_type' ];
    $event_amount = $_REQUEST[ 'event_amount' ];
    $event_note = $_REQUEST[ 'event_note' ];
    $event_date = $_REQUEST[ 'event_date' ];
    $event_time = $_REQUEST[ 'event_time' ];
    $event_tax = $_REQUEST[ 'event_tax' ];
    $timestamp = strtotime( $event_date . ' ' . $event_time ) - get_option( 'gmt_offset' ) * 60 * 60;

    if ( empty( $event_note ) || empty( $event_amount ) || !is_numeric( $event_amount ) ) {
      die( json_encode( array( 'success' => 'false', 'message' => __( 'Please enter a note and numeric amount.', ud_get_wp_invoice()->domain ) ) ) );
    }

    switch ( $event_type ) {

      case WPI_EVENT_TYPE_ADD_PAYMENT:
        if ( !empty( $event_amount ) ) {
          $event_note = WPI_Functions::currency_format( abs( $event_amount ), $invoice_id ) . " " . __( 'paid in', ud_get_wp_invoice()->domain ) . " - $event_note";
        }
        break;

      case WPI_EVENT_TYPE_ADD_CHARGE:
        if ( !empty( $event_amount ) ) {
          $name = $event_note;
          $event_note = WPI_Functions::currency_format( $event_amount, $invoice_id ) . " " . (!empty($event_tax)?'&#43;'.$event_tax.'%':'') . "  " . __( 'charge added', ud_get_wp_invoice()->domain ) . " - $event_note";
          $core = WPI_Core::getInstance();
          $charge_item = $core->Functions->add_itemized_charge( $invoice_id, $name, $event_amount, $event_tax );
        }
        break;

      case WPI_EVENT_TYPE_ADD_ADJUSTMENT:
        if ( !empty( $event_amount ) ) {
          $event_note = WPI_Functions::currency_format( $event_amount, $invoice_id ) . "  " . __( 'adjusted', ud_get_wp_invoice()->domain ) . " - $event_note";
        }
        break;

      case WPI_EVENT_TYPE_ADD_REFUND:
        if ( !empty( $event_amount ) ) {
          $event_amount = abs( (float) $event_amount );
          $event_note = WPI_Functions::currency_format( $event_amount, $invoice_id ) . "  " . __( 'refunded', ud_get_wp_invoice()->domain ) . " - $event_note";
        }
        break;

      default:
        break;
    }

    $invoice = new WPI_Invoice();
    $invoice->load_invoice( "id=$invoice_id" );
    $insert_id = $invoice->add_entry(array(
        'attribute' => 'balance',
        'note'      => $event_note,
        'amount'    => $event_amount,
        'type'      => $event_type,
        'time'      => $timestamp
    ));

    if ( $insert_id ) {
      $response = array( 'success' => 'true', 'message' => sprintf( __( 'Event Added: %1s.', ud_get_wp_invoice()->domain ), $event_note ) );
    } else {
      $response = array( 'success' => 'false', 'message' => sprintf( __( 'Could not save entry in invoice log. %1s', ud_get_wp_invoice()->domain ), '' ) );
    }

    $invoice->save_invoice();

    if ( !empty( $charge_item ) && $event_type == 'add_charge' ) {
      $response[ 'charge_item' ] = $charge_item;
    }

    die( json_encode( $response ) );
  }

  /**
   * Returns notification email based on pased values
   *
   * @global object $wpdb
   * @global array $wpi_settings
   */
  static function get_notification_email() {

    $template_id = $_REQUEST[ 'template_id' ];
    $invoice_id = intval( $_REQUEST[ 'wpi_invoiceid' ] );

    $template = WPI_Functions::preprocess_notification_template( $template_id, $invoice_id );

    $aryJson = array();
    //** Filter data before using. korotkov@ud */
    $aryJson[ 'wpi_content' ] = apply_filters( 'wpi_notification_content', $template->ary[ 'NotificationContent' ], $template->invoice );
    $aryJson[ 'wpi_subject' ] = apply_filters( 'wpi_notification_subject', $template->ary[ 'NotificationSubject' ], $template->invoice );

    die( json_encode( $aryJson ) );
  }

  /**
   * This function sends our our notifications from the admin screen
   */
  static function send_notification() {
    global $wpi_settings;

    if ( !WPI_Functions::current_user_can_send_notifications() ) {
      die( json_encode( array( 'status' => 403, 'message' => __( 'You are not allowed to perform this action.', ud_get_wp_invoice()->domain ) ) ) );
    }

    //** Start buffering to avoid appearing any errors in response */
    ob_start();

    //** Setup, and send our e-mail */
    $headers = array(
        "From: " . get_bloginfo() . " <" . get_bloginfo( 'admin_email' ) . ">\r\n"
    );
    $message = html_entity_decode( $_REQUEST[ 'body' ], ENT_QUOTES, 'UTF-8' );
    $subject = html_entity_decode( $_REQUEST[ 'subject' ], ENT_QUOTES, 'UTF-8' );
    $to = $_REQUEST[ 'to' ];

    //** Validate for empty fields data */
    if ( empty( $to ) || empty( $subject ) || empty( $message ) ) {
      ob_end_clean();
      die( json_encode( array( "status" => 500, "msg" => __( "The fields should not be empty. Please, check the fields data and try to send notification again.", ud_get_wp_invoice()->domain ) ) ) );
    }

    WPI_Functions::maybe_override_mail_from();

    if ( wp_mail( $to, $subject, apply_filters( 'wpi_notification_message', $message, $to, $subject, absint($_REQUEST[ 'invoice_id' ]) ), apply_filters( 'wpi_notification_headers', $headers, $to, $subject, absint($_REQUEST[ 'invoice_id' ]) ) ) ) {
      $pretty_time = date( get_option( 'time_format' ) . " " . get_option( 'date_format' ), time() + get_option( 'gmt_offset' ) * 60 * 60 );
      $text = __( "Notification Sent", ud_get_wp_invoice()->domain ) . ( isset( $_REQUEST[ 'template' ] ) && !empty( $_REQUEST[ 'template' ] ) ? " (" . $_REQUEST[ 'template' ] . ")" : "" ) . " " . __( 'to', ud_get_wp_invoice()->domain ) . " {$to} " . __( 'at', ud_get_wp_invoice()->domain ) . " {$pretty_time}.";
      WPI_Functions::log_event( wpi_invoice_id_to_post_id( $_REQUEST[ 'invoice_id' ] ), 'invoice', 'notification', '', $text, time() );
      ob_end_clean();
      die( json_encode( array( "status" => 200, "msg" => __( "Successfully sent the invoice notification!", ud_get_wp_invoice()->domain ) ) ) );
    }
    ob_end_clean();
    die( json_encode( array( "status" => 500, "msg" => __( "Unable to send the e-mail. Please, try again later.", ud_get_wp_invoice()->domain ) ) ) );
  }

  /**
   * Save invoice from Ajax
   */
  static function save_invoice() {
    global $wpi_settings;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      die( __( "You are not allowed to perform this action.", ud_get_wp_invoice()->domain ) );
    }

    $invoice_id = WPI_Functions::save_invoice( $_REQUEST[ 'wpi_invoice' ] );
    if ( $invoice_id ) {
      echo sprintf( __( "Saved. <a target='_blank' href='%s'>View Invoice</a>", ud_get_wp_invoice()->domain ), get_invoice_permalink( $invoice_id ) ) . ". " . __( 'Invoice id #', ud_get_wp_invoice()->domain ) . "<span id='new_invoice_id'>".wpi_post_id_to_invoice_id($invoice_id)."</span>.";
    } else {
      echo __( "There was a problem with saving the invoice. Reference the log for troubleshooting.", ud_get_wp_invoice()->domain );
    }
    die();
  }

  /**
   * Returns invoice status using the get_status function, then dies.
   */
  static function show_invoice_status() {
    $invoice_id = intval( $_REQUEST[ 'invoice_id' ] );
    WPI_Functions::get_status( wpi_invoice_id_to_post_id( $invoice_id ) );
    die();
  }

  /**
   * Invoice charges
   */
  static function show_invoice_charges() {
    $invoice_id = intval( $_REQUEST[ 'invoice_id' ] );
    WPI_Functions::get_charges( wpi_invoice_id_to_post_id( $invoice_id ) );
    die();
  }

  /**
   * Install templates for WPI
   */
  static function install_templates() {
    global $wpi_settings;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      die( __( "You are not allowed to perform this action.", ud_get_wp_invoice()->domain ) );
    }

    $errors = array();
    $custom_template_path = STYLESHEETPATH . "/wpi";
    $original_template_path = dirname( __FILE__ ) . "/../static/views";

    if ( !is_dir( $custom_template_path ) ) {
      if ( !@mkdir( $custom_template_path ) ) {
        $errors[ ] = __( "Unable to create 'wpi' folder in template folder. ", ud_get_wp_invoice()->domain );
        die( json_encode( $errors ) );
      }
    }

    $files_copied = 0;
    if ( $dir = @opendir( $original_template_path ) ) {
      while ( ( $file = readdir( $dir ) ) !== false ) {
        unset( $info );
        $info = pathinfo( $file );
        if ( !empty($info[ 'extension' ]) && $info[ 'extension' ] == 'php' ) {
          if ( @copy( $original_template_path . "/" . $file, "$custom_template_path/$file" ) )
            $files_copied++;
        }
      }
      closedir( $dir );
    } else {
      $errors[ ] = __( "Unable to open 'wpi' folder in template folder.", ud_get_wp_invoice()->domain );
      die( json_encode( $errors ) );
    }

    if ( ( intval( $files_copied ) ) != 0 ) {
      $errors[ ] = sprintf( __( "Success, (%s) template file(s) copied.", ud_get_wp_invoice()->domain ), $files_copied );
      die( json_encode( $errors ) );
    } else {
      $errors[ ] = __( "No template files copied.", ud_get_wp_invoice()->domain );
      die( json_encode( $errors ) );
    }
  }

  /**
   * Handler for AJAX user search for Add new invoice page
   *
   * @global object $wpdb
   * @author korotkov@ud
   */
  static function user_autocomplete_handler() {
    global $wpdb, $blog_id, $wpi_settings;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      die( __( "You are not allowed to perform this action.", ud_get_wp_invoice()->domain ) );
    }

    $users_found = $wpdb->get_results( "SELECT `u`.`ID`, CONCAT(`u`.`display_name`,' - ',`u`.`user_email`) as `label`, `user_email` as `value`
                                       FROM `{$wpdb->users}` as `u` INNER JOIN `{$wpdb->usermeta}` as `m`
                                         ON `u`.`ID` = `m`.`user_id`
                                       WHERE (`u`.`display_name` LIKE '%{$_REQUEST['term']}%'
                                         OR `u`.`user_email` LIKE '%{$_REQUEST['term']}%')
                                         AND `u`.`user_email` != ''
                                         AND `m`.`meta_key` = '{$wpdb->get_blog_prefix( $blog_id )}capabilities'
                                       GROUP BY `u`.`ID`
                                       LIMIT 10" );

    die( json_encode( $users_found ) );
  }

  /**
   * Handler for AJAX template search
   *
   * @global object $wpdb
   * @author korotkov@ud
   */
  static function template_autocomplete_handler() {
    global $wpdb, $wpi_settings;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      die( __( "You are not allowed to perform this action.", ud_get_wp_invoice()->domain ) );
    }

    $invoices_found = $wpdb->get_results( "SELECT `post_title` as `label`,`ID` as `value`
                                          FROM `{$wpdb->posts}`
                                          WHERE `post_title` LIKE '%{$_REQUEST['term']}%'
                                            AND `post_type` = 'wpi_object'
                                          LIMIT 10" );

    $invoices_found = apply_filters( 'wpi_after_template_autocomplete_handler', $invoices_found, $_REQUEST['term'] );

    die( json_encode( $invoices_found ) );
  }

}
