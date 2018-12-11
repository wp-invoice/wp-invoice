<?php
/**
 * WP-Invoice XML-RPC API Reference
 *
 * @type Array
 * @author korotkov@ud
 */
global $wpi_xml_rpc_api_reference;

$wpi_xml_rpc_api_reference = array(

  //** Namespace */
  'namespace' => new WPI_XMLRPC_API(),

  //** Methods */
  'methods' => array(

    //** Create Invoice */
    'create_invoice' => array(
      'description' => 'Create new invoice based on information passed.',
      'args' => array(
        'custom_id' => array(
          'description' => 'Previously generated Custom Invoice ID.',
          'required' => false,
          'type' => 'Number'
        ),
        'subject' => array(
          'description' => 'The title of invoice post object.',
          'required' => true,
          'type' => 'String'
        ),
        'description' => array(
          'description' => 'The content of invoice post object.',
          'required' => false,
          'type' => 'String'
        ),
        'type' => array(
          'description' => 'The type of invoice object. One of allowed types can be used. (invoice, quote, single_payment, recurring).',
          'required' => true,
          'type' => 'String'
        ),
        'user_data' => array(
          'description' => 'Recipient data. Conditionaly required. Associative array should always contain "user_email".',
          'required' => true,
          'type' => 'Array'
        ),
        'deposit' => array(
          'description' => 'Minimum amount of Partial Payment allowed. Partial Payments disabled if empty.',
          'required' => false,
          'type' => 'Number'
        ),
        'due_date' => array(
          'description' => 'Invoice Due Date. Associative array of 3 permanent elements: month, year, day (mm, yyyy, dd).',
          'required' => false,
          'type' => 'Array'
        ),
        'currency' => array(
          'description' => 'Currency code of the currency you need to use for current invoice. Format AAA.',
          'required' => false,
          'type' => 'String'
        ),
        'tax' => array(
          'description' => 'Global tax value in percents. Will be applied to every item and charge.',
          'required' => false,
          'type' => 'Number'
        ),
        'tax_method' => array(
          'description' => 'Method of tax counting. After or Before discount. (before_discount, after_discount).',
          'required' => false,
          'type' => 'String'
        ),
        'recurring' => array(
          'description' => 'Recurring billing cycles options.',
          'required' => false,
          'type' => 'String'
        ),
        'status' => array(
          'description' => 'The status of invoice. One of registered statuses is allowed.',
          'required' => true,
          'type' => 'String'
        ),
        'discount' => array(
          'description' => 'Discount for current invoice. Associative array should contain 3 fields: name, type, amount.',
          'required' => false,
          'type' => 'Array'
        ),
        'items' => array(
          'description' => 'Line items of current invoice. Set of associative arrays. Conditionaly required.',
          'required' => true,
          'type' => 'Array'
        ),
        'charges' => array(
          'description' => 'Charges of current invoice. Set of associative arrays. Conditionaly required.',
          'required' => true,
          'type' => 'Array'
        )
      ),
      'return' => 'WPI_Invoice|WP_Error'
    ),

    //** Delete Invoice */
    'delete_invoice' => array(
      'description' => 'Delete invoice by ID.',
      'args' => array(
        'ID' => array(
          'description' => 'Invoice ID.',
          'required' => true,
          'type' => 'Number'
        )
      ),
      'return' => 'Boolean|WP_Error'
    ),

    //** Get Invoice */
    'get_invoice' => array(
      'description' => 'Get invoice by ID',
      'args' => array(
        'ID' => array(
          'description' => 'Invoice ID.',
          'required' => true,
          'type' => 'Number'
        )
      ),
      'return' => 'WPI_Invoice|WP_Error'
    ),

    //** Refund invoice */
    'refund_invoice' => array(
      'description' => 'Refund invoice by ID. Note that it does not do refund on merchant side.',
      'args' => array(
        'ID' => array(
          'description' => 'Invoice ID.',
          'required' => true,
          'type' => 'Number'
        )
      ),
      'return' => 'WPI_Invoice|WP_Error'
    ),

    //** Pay invoice by ID */
    'pay_invoice' => array(
      'description' => 'Pay invoice by ID',
      'args' => array(
        'ID' => array(
          'description' => 'Invoice ID.',
          'required' => true,
          'type' => 'Number'
        ),
        'amount' => array(
          'description' => 'Amount to be paid.',
          'required' => true,
          'type' => 'Number'
        ), //** Add optional detail */
        'detail' => array(
          'description' => 'Note to be added to log.',
          'required' => false,
          'type' => 'String'
        )
      ),
      'return' => 'WPI_Invoice|WP_Error'
    ),

    //** Update invoice */
    'update_invoice' => array(
      'description' => 'Update some of the invoice attributes.',
      'args' => array(
        'ID' => array(
          'description' => 'Invoice ID.',
          'required' => true,
          'type' => 'Number'
        ),
        'subject' => array(
          'description' => 'The title of invoice post object.',
          'required' => true,
          'type' => 'String'
        ),
        'description' => array(
          'description' => 'The content of invoice post object.',
          'required' => false,
          'type' => 'String'
        ),
        'type' => array(
          'description' => 'The type of invoice object. One of allowed types can be used. (invoice, quote, single_payment, recurring).',
          'required' => true,
          'type' => 'String'
        ),
        'deposit' => array(
          'description' => 'Minimum amount of Partial Payment allowed. Partial Payments disabled if empty.',
          'required' => false,
          'type' => 'Number'
        ),
        'due_date' => array(
          'description' => 'Invoice Due Date. Associative array of 3 permanent elements: month, year, day (mm, yyyy, dd).',
          'required' => false,
          'type' => 'Array'
        ),
        'tax' => array(
          'description' => 'Global tax value in percents. Will be applied to every item and charge.',
          'required' => false,
          'type' => 'Number'
        ),
        'tax_method' => array(
          'description' => 'Method of tax counting. After or Before discount. (before_discount, after_discount).',
          'required' => false,
          'type' => 'String'
        ),
        'recurring' => array(
          'description' => 'Recurring billing cycles options.',
          'required' => false,
          'type' => 'String'
        ),
        'discount' => array(
          'description' => 'Discount for current invoice. Associative array should contain 3 fields: name, type, amount.',
          'required' => false,
          'type' => 'Array'
        ),
        'items' => array(
          'description' => 'Line items of current invoice. Set of associative arrays. Conditionaly required.',
          'required' => true,
          'type' => 'Array'
        ),
        'charges' => array(
          'description' => 'Charges of current invoice. Set of associative arrays. Conditionaly required.',
          'required' => true,
          'type' => 'Array'
        )
      )
    )
  )
);

/**
 * WP-Invoice XML-RPC API Class
 *
 * @since 3.08.7
 * @author korotkov@ud
 */
class WPI_XMLRPC_API {

  /**
   * API Name
   *
   * @var string
   */
  public $name = 'WPI_XMLRPC_API';

  /**
   * API Description
   *
   * @var string
   */
  public $description = 'WP-Invoice XML-RPC API Handler. Can be used by authorized users by calling single method "wp.invoice" with arguments described in API Reference.';

  /**
   * Initialize
   */
  function __construct() {
    //** Extend standard XML-RPC */
    add_filter( 'xmlrpc_methods', array( __CLASS__, 'r__register' ) );
    add_action( 'wpi_settings_before_help', 'wpi_help_api_reference' );
  }

  /**
   * Register method-hook
   *
   * @param array $methods
   *
   * @return array
   */
  static function r__register( $methods ) {
    $methods[ 'wp.invoice' ] = 'wpi_xmlrpc_request';
    return $methods;
  }

  /**
   * Create new invoice
   *
   * @param array $args
   *
   * @return WPI_Invoice
   * @see WPI_Invoice
   * @uses Internal API of plugin
   */
  function create_invoice( $args = array() ) {
    global $wpi_settings;

    //** Default arguments */
    $defaults = array(
      'custom_id' => false,
      'subject' => false,
      'description' => false,
      'type' => false,
      'user_data' => array(
        'user_email' => false,
        'first_name' => false,
        'last_name' => false,
        'phonenumber' => false,
        'streetaddress' => false,
        'city' => false,
        'state' => false,
        'zip' => false,
        'country' => false
      ),
      'deposit' => false,
      'due_date' => array(
        'year' => false,
        'month' => false,
        'day' => false
      ),
      'currency' => false,
      'tax' => false,
      'tax_method' => false,
      'recurring' => array(
        'unit' => false,
        'length' => false,
        'cycles' => false,
        'send_invoice_automatically' => false,
        'start_date' => array(
          'month' => false,
          'day' => false,
          'year' => false
        )
      ),
      'status' => false,
      'discount' => array(
        'name' => false,
        'type' => false,
        'amount' => false
      ),
      'items' => array(),
      'charges' => array()
    );

    //** Parse arguments */
    extract( $args = wp_parse_args( $args, $defaults ) );

    //** If empty subject - return error */
    if ( !$subject ) return new WP_Error( 'wp.invoice', __( 'Method requires "subject" argument to be passed.', ud_get_wp_invoice()->domain ), $args );

    //** If empty user_email - return error */
    if ( !$user_data[ 'user_email' ] ) return new WP_Error( 'wp.invoice', __( 'Method requires "user_email" in "user_data" argument to be passed.', ud_get_wp_invoice()->domain ), $args );
    if ( !filter_var( $user_data[ 'user_email' ], FILTER_VALIDATE_EMAIL ) ) return new WP_Error( 'wp.invoice', __( 'User Email is malformed.', ud_get_wp_invoice()->domain ), $args );

    //** Items/Charges check */
    if ( empty( $items ) && empty( $charges ) ) return new WP_Error( 'wp.invoice', __( 'Method requires "items" or "charges" argument to be passed.', ud_get_wp_invoice()->domain ), $args );

    //** If type is registered */
    if ( !array_key_exists( $type, $wpi_settings[ 'types' ] ) ) return new WP_Error( 'wp.invoice', __( 'Unknown invoice type.', ud_get_wp_invoice()->domain ), $args );

    //** If recurring */
    if ( $type == 'recurring' ) {
      $recurring = array_filter( $recurring );
      if ( empty( $recurring[ 'unit' ] ) || empty( $recurring[ 'cycles' ] ) ) return new WP_Error( 'wp.invoice', __( 'Method requires correct "recurring" argument if "type" is recurring.', ud_get_wp_invoice()->domain ), $args );
      if ( !empty( $deposit ) ) return new WP_Error( 'wp.invoice', __( 'Cannot use "deposit" with "recurring" type.', ud_get_wp_invoice()->domain ), $args );
    }

    //** If quote */
    if ( $type == 'quote' ) {
      if ( !empty( $deposit ) ) return new WP_Error( 'wp.invoice', __( 'Cannot use "deposit" with "quote" type.', ud_get_wp_invoice()->domain ), $args );
    }

    //** Check status */
    if ( !$status ) return new WP_Error( 'wp.invoice', __( 'Method requires "status" argument to be passed.', ud_get_wp_invoice()->domain ), $args );
    if ( !array_key_exists( $status, $wpi_settings[ 'invoice_statuses' ] ) ) return new WP_Error( 'wp.invoice', __( 'Unknown invoice status.', ud_get_wp_invoice()->domain ), $args );

    //** New Invoice object */
    $invoice = new WPI_Invoice();

    //** Load invoice by ID */
    $invoice->create_new_invoice( $args );

    //** Set type */
    $invoice->set( array(
      'type' => $type
    ) );

    //** If quote */
    if ( $type == 'quote' ) {
      $invoice->set( array( 'status' => $type ) );
      $invoice->set( array( 'is_quote' => 'true' ) );
    }

    //** Recurring */
    if ( $type == 'recurring' ) {
      $invoice->create_schedule( $recurring );
    }

    //** Try loading user by email */
    $invoice->load_user( array(
      'email' => $user_data[ 'user_email' ]
    ) );

    //** If new user - add data to his object */
    if ( empty( $invoice->data[ 'user_data' ] ) ) {
      $invoice->data[ 'user_data' ] = $user_data;
    }

    //** Create/Update user if need */
    WPI_Functions::update_user( $user_data );

    //** Try loading user by email again */
    $invoice->load_user( array(
      'email' => $user_data[ 'user_email' ]
    ) );

    //** Partial payments */
    if ( $deposit ) {
      $invoice->set( array( 'deposit_amount' => $deposit ) );
    } else {
      $invoice->set( array( 'deposit_amount' => 0 ) );
    }

    //** Due date */
    $invoice->set( array( 'due_date_year' => $due_date[ 'year' ] ) );
    $invoice->set( array( 'due_date_month' => $due_date[ 'month' ] ) );
    $invoice->set( array( 'due_date_day' => $due_date[ 'day' ] ) );

    //** Currency */
    $invoice->set( array( 'default_currency_code' => $currency ) );

    //** Tax */
    $invoice->set( array( 'tax' => $tax ) );

    //** Status */
    $invoice->set( array( 'post_status' => $status ) );

    //** Discount */
    $discount = array_filter( $discount );
    if ( !empty( $discount ) ) {
      if ( empty( $discount[ 'name' ] ) ) return new WP_Error( 'wp.invoice', __( 'Discount name is required.', ud_get_wp_invoice()->domain ), $args );
      if ( empty( $discount[ 'type' ] ) ) return new WP_Error( 'wp.invoice', __( 'Discount type is required. ("amount" or "percent").', ud_get_wp_invoice()->domain ), $args );
      if ( empty( $discount[ 'amount' ] ) ) return new WP_Error( 'wp.invoice', __( 'Discount amount is required.', ud_get_wp_invoice()->domain ), $args );
      $invoice->add_discount( $discount );
    }

    //** Items */
    foreach ( $items as $item ) {
      //** Do not allow to save melformed items */
      if ( empty( $item[ 'name' ] ) ||
        empty( $item[ 'quantity' ] ) ||
        empty( $item[ 'price' ] )
      ) {
        return new WP_Error( 'wp.invoice', __( 'One or more "items" have malformed structure. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      }

      //** Global tax has higher priority */
      if ( !empty( $tax ) ) $item[ 'tax_rate' ] = $tax;

      //** Check types */
      if ( !is_numeric( $item[ 'quantity' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "items" have wrong "quantity" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      if ( !is_numeric( $item[ 'price' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "items" have wrong "price" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      if ( !empty( $item[ 'tax_rate' ] ) ) {
        if ( !is_numeric( $item[ 'tax_rate' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "items" have wrong "tax_rate" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      }

      //** If passed validation - save item */
      $invoice->line_item( $item );
    }

    //** Charges */
    foreach ( $charges as $charge ) {
      //** Do not allow to save melformed items */
      if ( empty( $charge[ 'name' ] ) ||
        empty( $charge[ 'amount' ] )
      ) {
        return new WP_Error( 'wp.invoice', __( 'One or more "charges" have malformed structure. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      }

      //** Global tax has higher priority */
      if ( !empty( $tax ) ) $charge[ 'tax' ] = $tax;

      //** Check types */
      if ( !is_numeric( $charge[ 'amount' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "charges" have wrong "amount" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      if ( !empty( $charge[ 'tax' ] ) ) {
        if ( !is_numeric( $charge[ 'tax' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "charges" have wrong "tax" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
      }

      //** If passed validation - save item */
      $invoice->line_charge( $charge );
    }

    //** Set tax method */
    if ( !empty( $tax_method ) ) {
      if ( $tax_method != 'before_discount' && $tax_method != 'after_discount' ) {
        return new WP_Error( 'wp.invoice', __( 'Unknown "tax_method".', ud_get_wp_invoice()->domain ), $args );
      }
    }
    $invoice->set( array( 'tax_method' => $tax_method ) );

    $invoice->data['new_invoice'] = false;

    //** Save */
    $invoice->save_invoice();

    //** Return saved object */
    return $invoice;
  }

  /**
   * Refund invoice by ID
   *
   * @param type $args
   *
   * @return WP_Error|WPI_Invoice
   */
  function refund_invoice( $args = array() ) {
    //** Defaults  */
    $defaults = array(
      'ID' => false
    );

    //** Parse arguments */
    extract( wp_parse_args( $args, $defaults ) );

    //** Check */
    if ( !$ID ) return new WP_Error( 'wp.invoice', __( 'Argument "ID" is required.', ud_get_wp_invoice()->domain ), $args );

    //** New Invoice object */
    $invoice = new WPI_Invoice();

    //** Load invoice by ID */
    $invoice->load_invoice( array( 'id' => $ID ) );

    //** Check */
    if ( !empty( $invoice->error ) ) return new WP_Error( 'wp.invoice', __( 'Invoice not found', ud_get_wp_invoice()->domain ), $args );

    //** Do refund if it has payments */
    if ( empty( $invoice->data[ 'total_payments' ] ) ) return new WP_Error( 'wp.invoice', __( 'Cannot be refunded. No payments found.', ud_get_wp_invoice()->domain ), $args );

    $insert_id = $invoice->add_entry( array(
      'attribute' => 'balance',
      'note' => 'Refunded via XML-RPC',
      'amount' => (float) $invoice->data[ 'total_payments' ],
      'type' => 'refund'
    ) );
    if ( !$insert_id ) return new WP_Error( 'wp.invoice', __( 'Could not refund due to unknown error.', ud_get_wp_invoice()->domain ), $args );

    $invoice->save_invoice();

    //** Load again to get changes */
    $invoice = new WPI_Invoice();
    $invoice->load_invoice( array( 'id' => $ID ) );

    return $invoice;
  }

  /**
   * Pay invoice by ID
   *
   * @param type $args
   *
   * @return WP_Error|WPI_Invoice
   */
  function pay_invoice( $args = array() ) {
    //** Default arguments */
    $defaults = array(
      'ID' => false,
      'amount' => false,
      'detail' => false
    );

    //** Parse arguments */
    extract( wp_parse_args( $args, $defaults ) );

    //** Check */
    if ( !$ID ) return new WP_Error( 'wp.invoice', __( 'Argument "ID" is required.', ud_get_wp_invoice()->domain ), $args );
    if ( !$amount ) return new WP_Error( 'wp.invoice', __( 'Argument "amount" is required.', ud_get_wp_invoice()->domain ), $args );
    if ( !is_numeric( $amount ) ) return new WP_Error( 'wp.invoice', __( 'Argument "amount" is malformed.', ud_get_wp_invoice()->domain ), $args );

    //** New Invoice object */
    $invoice = new WPI_Invoice();

    //** Load invoice by ID */
    $invoice->load_invoice( array( 'id' => $ID ) );

    //** Check */
    if ( !empty( $invoice->error ) ) return new WP_Error( 'wp.invoice', __( 'Invoice not found', ud_get_wp_invoice()->domain ), $args );

    //** Pay only if status if not paid */
    if ( $invoice->data[ 'post_status' ] == 'paid' ) return new WP_Error( 'wp.invoice', __( 'Invoice is completely paid. Payments are not acceptable anymore.', ud_get_wp_invoice()->domain ), $args );

    //** Check amount */
    if ( (float) $invoice->data[ 'net' ] < (float) $amount ) return new WP_Error( 'wp.invoice', __( 'Cannot pay more that the balance is. Maximum is ' . $invoice->data[ 'net' ], ud_get_wp_invoice()->domain ), $args );

    //** Handle partial */
    if ( (float) $invoice->data[ 'net' ] > (float) $amount ) {
      if ( empty( $invoice->data[ 'deposit_amount' ] ) ) return new WP_Error( 'wp.invoice', __( 'Partial payments are not allowed. Pay minimum is ' . $invoice->data[ 'net' ], ud_get_wp_invoice()->domain ), $args );
      if ( (float) $amount < (float) $invoice->data[ 'deposit_amount' ] ) {
        return new WP_Error( 'wp.invoice', __( 'Minimum allowed payment is ' . $invoice->data[ 'deposit_amount' ], ud_get_wp_invoice()->domain ), $args );
      }
    }

    //** Add payment item */
    if($detail === FALSE) {
        $note = 'Paid ' . ( (float) $amount ) . ' ' . $invoice->data[ 'default_currency_code' ] . ' via XML-RPC API';   
    } else {
        $note = 'Paid ' . ( (float) $amount ) . ' ' . $invoice->data[ 'default_currency_code' ] . ' via XML-RPC API - ' . $detail;
    }
    $invoice->add_entry( array(
      'attribute' => 'balance',
      'note' => $note,
      'amount' => (float) $amount,
      'type' => 'add_payment'
    ) );

    //** Save to be sure totals recalculated */
    $invoice->save_invoice();

    //** Load again to get changes */
    $invoice = new WPI_Invoice();
    $invoice->load_invoice( array( 'id' => $ID ) );

    return $invoice;
  }

  /**
   * Delete Invoice by ID
   *
   * @param array $args
   *
   * @return bool
   */
  function delete_invoice( $args = array() ) {
    //** Default arguments */
    $defaults = array( 'ID' => false );

    //** Parse arguments */
    extract( wp_parse_args( $args, $defaults ) );

    //** Check */
    if ( !$ID ) return new WP_Error( 'wp.invoice', __( 'Argument "ID" is required.', ud_get_wp_invoice()->domain ), $args );

    //** New Invoice object */
    $invoice = new WPI_Invoice();

    //** Load invoice by ID */
    $invoice->load_invoice( array( 'id' => $ID ) );

    //** Return result of delete method */
    return $invoice->delete();
  }

  /**
   * Returns invoice object requested by ID
   *
   * @param array $args
   *
   * @return WPI_Invoice
   */
  function get_invoice( $args = array() ) {
    //** Default arguments */
    $defaults = array( 'ID' => false );

    //** Parse arguments */
    extract( wp_parse_args( $args, $defaults ) );

    //** Check */
    if ( !$ID ) return new WP_Error( 'wp.invoice', __( 'Argument "ID" is required.', ud_get_wp_invoice()->domain ), $args );

    //** New Invoice object */
    $invoice = new WPI_Invoice();

    //** Load invoice by ID */
    $invoice->load_invoice( array( 'id' => $ID ) );

    //** Return ready object */
    return empty( $invoice->error ) ? $invoice : new WP_Error( 'wp.invoice', __( 'Invoice not found', ud_get_wp_invoice()->domain ), $args );
  }

  /**
   * Update invoice by ID
   *
   * @global Array $wpi_settings
   *
   * @param Array $args
   *
   * @return WP_Error|WPI_Invoice
   */
  function update_invoice( $args = array() ) {
    global $wpi_settings;

    //** Default arguments */
    $defaults = array(
      'ID' => false,
      'subject' => false,
      'description' => false,
      'type' => false,
      'deposit' => false,
      'due_date' => false,
      'tax' => false,
      'tax_method' => false,
      'recurring' => false,
      'discount' => false,
      'items' => array(),
      'charges' => array()
    );

    //** Parse arguments */
    extract( $args = wp_parse_args( $args, $defaults ) );

    //** Check */
    if ( !$ID ) return new WP_Error( 'wp.invoice', __( 'Argument "ID" is required.', ud_get_wp_invoice()->domain ), $args );

    //** New Invoice object */
    $invoice = new WPI_Invoice();

    //** Load invoice by ID */
    $invoice->load_invoice( array( 'id' => $ID ) );

    $set = array();

    //** Subject */
    if ( $subject ) {
      $subject = trim( $subject );
      if ( !empty( $subject ) ) {
        $set[ 'subject' ] = $subject;
        $set[ 'post_title' ] = $subject;
      }
    }

    //** Description */
    if ( $description ) {
      $description = trim( $description );
      if ( !empty( $description ) ) {
        $set[ 'description' ] = $description;
      }
    }

    if ( $type ) {
      //** If type is registered */
      if ( !array_key_exists( $type, $wpi_settings[ 'types' ] ) ) return new WP_Error( 'wp.invoice', __( 'Unknown invoice type.', ud_get_wp_invoice()->domain ), $args );

      //** If recurring */
      if ( $type == 'recurring' ) {
        $recurring = array_filter( $recurring );
        if ( empty( $recurring[ 'unit' ] ) || empty( $recurring[ 'cycles' ] ) ) return new WP_Error( 'wp.invoice', __( 'Method requires correct "recurring" argument if "type" is recurring.', ud_get_wp_invoice()->domain ), $args );
        if ( !empty( $deposit ) ) return new WP_Error( 'wp.invoice', __( 'Cannot use "deposit" with "recurring" type.', ud_get_wp_invoice()->domain ), $args );
      }

      //** If quote */
      if ( $type == 'quote' ) {
        if ( !empty( $deposit ) ) return new WP_Error( 'wp.invoice', __( 'Cannot use "deposit" with "quote" type.', ud_get_wp_invoice()->domain ), $args );
      }

      $set[ 'type' ] = $type;

      //** If quote */
      if ( $type == 'quote' ) {
        $set[ 'status' ] = $type;
        $set[ 'is_quote' ] = 'true';
      }

      //** Recurring */
      if ( $type == 'recurring' ) {
        $invoice->create_schedule( $recurring );
      }
    }

    //** Partial payments */
    if ( $deposit ) {
      $set[ 'deposit_amount' ] = (float) $deposit;
    }

    if ( $due_date ) {
      $set[ 'due_date_year' ] = $due_date[ 'year' ];
      $set[ 'due_date_month' ] = $due_date[ 'month' ];
      $set[ 'due_date_day' ] = $due_date[ 'day' ];
    }

    if ( $tax ) {
      $set[ 'tax' ] = $tax;
    }

    if ( $tax_method ) {
      if ( $tax_method != 'before_discount' && $tax_method != 'after_discount' ) {
        return new WP_Error( 'wp.invoice', __( 'Unknown "tax_method".', ud_get_wp_invoice()->domain ), $args );
      }
      $set[ 'tax_method' ] = $tax_method;
    }

    if ( $discount ) {
      if ( empty( $discount[ 'name' ] ) ) return new WP_Error( 'wp.invoice', __( 'Discount name is required.', ud_get_wp_invoice()->domain ), $args );
      if ( empty( $discount[ 'type' ] ) ) return new WP_Error( 'wp.invoice', __( 'Discount type is required. ("amount" or "percent").', ud_get_wp_invoice()->domain ), $args );
      if ( empty( $discount[ 'amount' ] ) ) return new WP_Error( 'wp.invoice', __( 'Discount amount is required.', ud_get_wp_invoice()->domain ), $args );
      $invoice->data[ 'discount' ] = array();
      $invoice->add_discount( $discount );
    }

    if ( $items ) {
      //** Items */
      foreach ( $items as $item ) {
        //** Do not allow to save melformed items */
        if ( empty( $item[ 'name' ] ) ||
          empty( $item[ 'quantity' ] ) ||
          empty( $item[ 'price' ] )
        ) {
          return new WP_Error( 'wp.invoice', __( 'One or more "items" have malformed structure. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        }

        //** Global tax has higher priority */
        if ( !empty( $tax ) ) $item[ 'tax_rate' ] = $tax;

        //** Check types */
        if ( !is_numeric( $item[ 'quantity' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "items" have wrong "quantity" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        if ( !is_numeric( $item[ 'price' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "items" have wrong "price" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        if ( !empty( $item[ 'tax_rate' ] ) ) {
          if ( !is_numeric( $item[ 'tax_rate' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "items" have wrong "tax_rate" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        }
      }
    }

    if ( $charges ) {
      //** Charges */
      foreach ( $charges as $charge ) {
        //** Do not allow to save melformed items */
        if ( empty( $charge[ 'name' ] ) ||
          empty( $charge[ 'amount' ] )
        ) {
          return new WP_Error( 'wp.invoice', __( 'One or more "charges" have malformed structure. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        }

        //** Global tax has higher priority */
        if ( !empty( $tax ) ) $charge[ 'tax' ] = $tax;

        //** Check types */
        if ( !is_numeric( $charge[ 'amount' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "charges" have wrong "amount" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        if ( !empty( $charge[ 'tax' ] ) ) {
          if ( !is_numeric( $charge[ 'tax' ] ) ) return new WP_Error( 'wp.invoice', __( 'One or more "charges" have wrong "tax" value. Cannot create Invoice.', ud_get_wp_invoice()->domain ), $args );
        }
      }
    }

    //** If passed validation - save item */
    if ( $charges ) {
      $invoice->data[ 'itemized_charges' ] = array();
      foreach ( $charges as $charge ) {
        $invoice->line_charge( $charge );
      }
    }
    if ( $items ) {
      $invoice->data[ 'itemized_list' ] = array();
      foreach ( $items as $item ) {
        $invoice->line_item( $item );
      }
    }

    $invoice->set( $set );

    $invoice->save_invoice();

    $invoice = new WPI_Invoice();
    //** Load invoice by ID */
    $invoice->load_invoice( array( 'id' => $ID ) );

    return $invoice;
  }

}

/**
 * Fires API methods in order to arguments passed
 *
 * @param array $args
 */
if ( !function_exists( 'wpi_xmlrpc_request' ) ) {
  function wpi_xmlrpc_request( $args ) {
    global $wp_xmlrpc_server, $wpi_xml_rpc_api_reference;

    //** Escape args */
    $wp_xmlrpc_server->escape( $args );

    //** Sort args */
    $method = $args[ 0 ];
    $credentials = $args[ 1 ];
    $args = $args[ 2 ];
    $blog = isset( $args[ 3 ] ) ? $args[ 3 ] : 0;

    //** Check credentials */
    if ( !$user = $wp_xmlrpc_server->login( $credentials[ 0 ], $credentials[ 1 ] ) )
      return $wp_xmlrpc_server->error;

    if ( !current_user_can_for_blog( $blog, 'manage_options' ) ) return new WP_Error( 'wp.invoice', __( 'Access denied. Do not have rights.', ud_get_wp_invoice()->domain ), $args );

    //** Check for reference */
    if ( !array_key_exists( $method, $wpi_xml_rpc_api_reference[ 'methods' ] ) ) return new WP_Error( 'wp.invoice', __( 'Requested method is absent in API Reference', ud_get_wp_invoice()->domain ), $args );

    //** Return result of calling requested method */
    return is_callable( array( $wpi_xml_rpc_api_reference[ 'namespace' ], $method ) )
      ? call_user_func( array( $wpi_xml_rpc_api_reference[ 'namespace' ], $method ), $args )
      : new WP_Error( 'wp.invoice', __( 'Unknown method', ud_get_wp_invoice()->domain ), $method );
  }
}

/**
 * Render API help
 */
if ( !function_exists( 'wpi_help_api_reference' ) ) {
  function wpi_help_api_reference() {
    global $wpi_xml_rpc_api_reference;
    ?>
    <div class="wpi_settings_block">
        <?php _e( 'WP-Invoice XML-RPC API Reference', ud_get_wp_invoice()->domain ); ?>
      <input type="button" class="wpi_settings_view button-primary" value="<?php esc_attr( _e( 'Toggle', ud_get_wp_invoice()->domain ) ); ?>">
        <div class="wpi_settings_row hidden">
          <div class="wpi_scrollable_content">
            <h2>
              <?php _e( 'WP-Invoice XML-RPC API Reference', ud_get_wp_invoice()->domain ); ?>
            </h2>
            <p>
              <?php echo $wpi_xml_rpc_api_reference[ 'namespace' ]->description; ?>
            </p>
            <h2>
              <?php _e( 'Examples', ud_get_wp_invoice()->domain ); ?>
            </h2>
            <p>
              <a target="_blank" href="https://github.com/UsabilityDynamics/wp-invoice/wiki/API#examples">https://github.com/UsabilityDynamics/wp-invoice/wiki/API</a>
            </p>
            <p>
              <?php _e( 'Below is a list of available functions that current API supports.', ud_get_wp_invoice()->domain ); ?>
            </p>

            <ul>
            <?php foreach ( $wpi_xml_rpc_api_reference[ 'methods' ] as $method => $info ): ?>
              <li class="wpi_api_method_wrapper">
                <code><?php echo $method; ?></code>
                <p><?php echo $info[ 'description' ]; ?></p>
                <h4><?php _e( 'Arguments:', ud_get_wp_invoice()->domain ); ?></h4>
                <ol>
                  <?php foreach ( $info[ 'args' ] as $arg => $arg_info ): ?>
                    <li><b><?php echo $arg; ?></b>:<?php echo $arg_info[ 'type' ]; ?><?php echo $arg_info[ 'required' ] ? '<sup>*</sup>' : ''; ?>
                      - <?php echo $arg_info[ 'description' ] ?></li>
                  <?php endforeach; ?>
                </ol>
              </li>
            <?php endforeach; ?>
            </ul>

          </div>
        </div>
      </div>
  <?php
  }
}