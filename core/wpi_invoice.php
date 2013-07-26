<?php
/**
Class used to create new, update, and query invoices.
Example:
$new_invoice = new WPI_Invoice();
$invoice_data = $new_invoice->load_invoice("id=12345");
$invoice_data = $invoice_data->data[subject]
*/
class WPI_Invoice {
  var $data;
  var $has_discount;

  /**
   * Sets variables for the invoice class;
   * Example:
   * $invoce_class->set("subject=Your House Cleaning Bill");
   * $invoce_class->set("description=For cleaning your house on September 19th.");
   * $invoce_class->set("default_payment_method=paypal");
   * Any validation should happen here, and return false is setup is incorrect.
   * Save_invoice() does not validate, only save
   */
  function set($args) {
    global $wpdb;
    $data =wp_parse_args($args, array());

    foreach($data as $meta_key => $meta_value) {
      $this->data[$meta_key] = $meta_value;
    }
  }

  /**
      Determine if new or old invoice
  */
  function existing_invoice() {
    global $wpdb;
    $ID = $this->data['ID'];

    if(empty($ID)) {
      $ID = wpi_invoice_id_to_post_id($this->data['invoice_id']);
    }

    if(!$ID)
      return false;

    $old_invoice = ($wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE ID = '{$ID}'") ? true : false);

    $this->existing_invoice = $old_invoice;
    return $old_invoice;
  }

  /**
      Performs certain administrative functions
  */
  function admin($args) {
    global $wpdb;
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    if($clear_log == 'true') {
      unset($this->data['log']);
      $this->add_entry("note=Log cleared.");
    }
  }

  /**
      Loads defaults from global settings
  */
  function load_defaults() {
    global $wpi_settings;
    // load globals
    $this->data['meta'] = $wpi_settings[globals];
    // currencies
    $this->data['currencies'] = $wpi_settings[currency][types];
    // load billing
    $this->data['billing'] = $wpi_settings[billing];
  }

  /**
   * Load user information from DB into invoice class
   *
   * Fixed to conditionally check for most appropriate name to display and override the 'display_name'
   *
   */
  function load_user($args = '') {
    global $wpi_settings, $wp_version;

    extract(wp_parse_args($args, array (
      'email' => false,
      'user_id' => false
    )), EXTR_SKIP);

    $email = trim($email);

    //** If e-mail exists, but no user_id is passed we get ID from email */
    //**
    // Fix for different versions of wordpress.
    // 3.3 has 'data' object in result of get_user_by()
    //*/
    if ( version_compare($wp_version, '3.3', '>=') ) {

      if(!empty($user_id) && $user_id = get_user_by('ID', $user_id)->data->ID) {
        WPI_Functions::console_log(__('Loaded user from passed ID.', WPI));
        $new_user = false;

      } elseif (!empty($email) && $user_id = get_user_by('email', $email)->data->ID) {
        WPI_Functions::console_log(__('Loaded user from e-mail.', WPI));
        $new_user = false;

      } else {
        WPI_Functions::console_log(__('User info not, found - assuming new user.', WPI));
        $new_user = true;

      }

      //** If new user, we create user_data array, and bail */
      if($new_user) {
        $this->data['user_data'] = array();
        return;
      }

      //** Get basic user data */
      $this->data['user_data'] = (array) get_userdata($user_id)->data;

    } else {

      if($user_id = get_user_by('ID', $user_id)->ID) {
        WPI_Functions::console_log(__('Loaded user from passed ID.', WPI));
        $new_user = false;

      } elseif (!empty($email) && $user_id = get_user_by('email', $email)->ID) {
        WPI_Functions::console_log(__('Loaded user from e-mail.', WPI));
        $new_user = false;

      } else {
        WPI_Functions::console_log(__('User info not, found - assuming new user.', WPI));
        $new_user = true;

      }

      //** If new user, we create user_data array, and bail */
      if($new_user) {
        $this->data['user_data'] = array();
        return;
      }

      //** Get basic user data */
      $this->data['user_data'] = (array) get_userdata($user_id);

    }

    //** Get required user fields */
    $required_fields = (array) $wpi_settings['user_meta']['required'];

    //** Get non essential user information */
    $custom_fields =  (array) apply_filters('wpi_user_information', $wpi_settings['user_meta']['custom']);

    //** Merge required and non-required user fields */
    $user_information = array_merge($required_fields, $custom_fields);

    //** Load all user information, if it exists, into invoice object */
    foreach($user_information as $meta_key => $meta_label) {
      if($meta_value = trim(get_user_meta($user_id, $meta_key, true))) {
        $this->data['user_data'][$meta_key] = $meta_value;
      }
    }

    //** Determine if display_name is custom */
    if(!empty($display_name) && $display_name != $user_email) {
      $recipient = $display_name;
    }

    //** If either first or last name exist, use them */
    if(empty($recipient)) {
      if(!empty($this->data['user_data']['first_name']) || !empty($this->data['user_data']['last_name'])) {
        $recipient = trim(trim($this->data['user_data']['first_name']) . ' ' . trim($this->data['user_data']['last_name']));
      }
    }

    //** Check if company name is set (i.e. corporate client) */
    if(empty($recipient) && !empty($this->data['user_data']['company_name'])) {
      $recipient = $this->data['user_data']['company_name'];
    }

    //** If still empty, just default to email */
    if(empty($recipient)) {
      $recipient = $user_email;
    }

    //** Select Display Name */
    $this->data['user_data']['display_name'] = $recipient;

    //** For quick access (not sure if this is used, but just in case */
    $this->data['user_email'] = $email;

  }

  /**
   * Can be used to setup invoives.
   * Otherwise, set() function can also be used to set this information
   */
  function create_new_invoice($args = '') {
    global $wpi_settings;
    $this->data['new_invoice'] = true;

    // Include global tax if option turned on
    if ( !empty( $wpi_settings['use_global_tax'] ) && $wpi_settings['use_global_tax'] == 'true' && !empty( $wpi_settings['global_tax'] ) ) {
      $this->data['tax'] = (int)$wpi_settings['global_tax'];
    }

    $defaults = array (
      'invoice_id' => '',
      'custom_id' => '',
      'subject' => '',
      'description' => ''
    );
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    // Set Random Invoice Id
    $this->data['invoice_id'] = ($invoice_id ? $invoice_id : rand(10000000, 99999999));

    if (!empty ($subject)) {
      $this->data['subject'] = $subject;
    }
    if (!empty ($description)) {
      $this->data['description'] = $description;
    }
    if (!empty ($custom_id)) {
      $this->data['meta']['custom_id'] = $custom_id;
    }

    // Load Globals
    $this->data = array_merge($this->data, $wpi_settings['globals']);
    // Default Currency
    $this->data['default_currency_code'] = $wpi_settings['currency']['default_currency_code'];
    // Default payment method
		$dpm = '';
    foreach ( $wpi_settings['billing'] as $key => $value) {
      if ( WPI_Functions::is_true( $value['allow'] ) && $value['default_option'] == 'true' ) {
        $dpm = $key;
      }
    }
		$this->data['default_payment_method'] = $dpm;
    // Default Billings
    // Merge billings to get available billings - A.K.
    WPI_Functions::merge_billings( $wpi_settings['billing'], &$this->data['billing'] );
    //$this->data['billing'] = $wpi_settings['billing'];
  }


    /**
    Much like load_invoice, but only loads information that should be copied to a new invoice
    For example, user-specific informaiton is excluded
    */
    function load_template($args = '') {
      $defaults = array (
          'id' => ''
      );
      extract(wp_parse_args($args, $defaults), EXTR_SKIP);

      $this->load_invoice("id=".$id);

      $this->data['invoice_id'] = (!empty($invoice_id) ? $invoice_id : rand(10000000, 99999999));
      $this->data['post_status'] = 'active';
      $this->data['adjustments'] = 0;
      $this->data['total_payments'] = 0;
      unset($this->data['ID']);
    }

  /**
   * Loads invoice information
   * Overwrites globals
   */
  function load_invoice($args = '') {
    global $wpdb, $wpi_settings, $blog_id;

    extract(wp_parse_args($args, array(
      'id' => '',
      'return' => false
    )), EXTR_SKIP);

    $id = wpi_invoice_id_to_post_id($id);

    $new_invoice = is_numeric($id) ? false : true;

    $invoice_data = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID = '$id'", ARRAY_A);

    if($new_invoice || count($invoice_data) < 1) {
      $this->error = true;
      $this->new_invoice = true;
      WPI_Functions::console_log('WPI_Invoice::load_invoice() '.__('function executed, no invoice ID found, assuming new invoice.', WPI));
      return;
    }

    WPI_Functions::console_log('WPI_Invoice::load_invoice() '.__('function executed, invoice_id: ', WPI) . $id);

    $object_meta = get_post_custom($id);


    if(is_array($object_meta)) {
      foreach($object_meta as $meta_key => $meta_value) {
        if(is_array($meta_value)) {
          $meta_value = $meta_value[key($meta_value)];
        }

        if ( is_serialized($meta_value) ) {
          $tmp_meta_value = unserialize($meta_value);
        } else {
          $tmp_meta_value = $meta_value;
        }
        $invoice_data[$meta_key] = (empty($tmp_meta_value) || !is_array($tmp_meta_value)) ? $meta_value : $tmp_meta_value;
      }
    }

    WPI_Functions::merge_billings( $wpi_settings['billing'], &$invoice_data['billing'] );

    //** Add support for MS and for old invoice histories which will have a blog_id of 0 after upgrade */
    if($blog_id == 1) {
      $ms_blog_query = " AND ( blog_id = {$blog_id} OR blog_id = 0) ";
    } else {
      $ms_blog_query = " AND blog_id = {$blog_id} ";
    }

    $object_log = $wpdb->get_results("SELECT * FROM {$wpdb->base_prefix}wpi_object_log WHERE object_id = '{$id}' $ms_blog_query", ARRAY_A);

    if(!empty($object_log)) {
      $invoice_data['log'] = $object_log;
    }

    $invoice_data = apply_filters('wpi_load_invoice', $invoice_data);

    if(!empty($invoice_data['user_email'])) {
      $this->load_user("email={$invoice_data['user_email']}");
    }

    if(empty($this->data['user_data'])) {
      WPI_Functions::console_log(__('Warning: no user information loaded for this invoice.', WPI));
    }

    if(!is_array($this->data)) {
     $this->data = array();
    }

    $this->data = array_merge($invoice_data, $this->data);

    if( $return ) {
      return $this->data;
    }
  }

  /**
   * @TODO: Update it
   * Figure out status of invoice
  */
    function run_reports() {
        if($this->data['amount'] == '0')
            $reports['status'] = 'paid';
        if($this->data['amount'] > '0')
            $reports['status'] =  'balance_due';
        if($this->data['amount'] < '0')
            $reports['status'] =  'negative_balance';
        if($this->data['is_recurring']) {
            // How many cycles have we passed since start?
            $start_date = $this->data['meta']['invoice_date'];
            $current_date['year']     = date('Y');
            $current_date['month']     = date('n');
            $current_date['day']         = date('d');
            switch ($this->data['meta']['recurring']['unit']) {
                case '1-month':
                    // How many month cycles.  The last part figures out if we need add another cycle based on the dates.
                    $reports['cycles'] = (($current_date['year'] - $start_date['year']) * 12) - $start_date['month'] + $current_date['month'] + (($current_date['day'] - $start_date['day'] > 0) ? 1 : 0);
                break;
                case '1-week':
                    $reports['cycles'] = round((WPI_Functions::days_since($start_date['year'] . $start_date['month'] . $start_date['day'], true) / 7));
                break;
                case '2-week':
                    $reports['cycles'] = round((WPI_Functions::days_since($start_date['year'] . $start_date['month'] . $start_date['day'], true) / 14));
                break;
            }
            $reports['lifetime_total'] = $reports['cycles'] * $this->data['amount'];
            $reports['total_paid_in'] = $this->query_log("paid_in=true");
        }
        return $reports;
    }
/**
    Queries invoice logs
*/
    function query_log($args = "" ) {
        $defaults = array ('paid_in' => false);
        extract(wp_parse_args($args, $defaults), EXTR_SKIP);
        if(!is_array($this->data['log']))
            return;
        foreach($this->data['log'] as $event) {
            if($event['event_type'] == 'add_payment') {
                 $total_paid_in = intval($total_paid_in) + intval($event['event_amount']);
            }
        }
        if($paid_in)
            return abs($total_paid_in);
    }
    /**
    Calculates how much has been paid, how much is owed, etc. based off logs
    */
    function calculate_status() {
    }

  /**
   * This is a cruicial function.
   * In addition to basic events it also tracks amounts paid, reimburshed, etc
   *
   * Structure:
   * - timestamp
   * - event_type
   * - amount_paid (sum of these is used to calculate if money is still owed)
   * - basic_event
   * - event_note
   */
  function add_entry($args = '') {
    global $wpdb;

    if(!empty($this->data['ID'])) {
      $ID = $this->data['ID'];
    } else if(!empty($this->data['invoice_id'])) {
      $ID = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = '{$this->data['invoice_id']}'");
    }

    if(empty($ID)) {
      return false;
    }

    extract(wp_parse_args($args, array(
      'type' => 'update',
      'attribute' => 'invoice',
      'amount' => '',
      'note' => '',
      'time' => time()
    )), EXTR_SKIP);

    return WPI_Functions::log_event($ID, $attribute, $type, $amount, $note, $time);

  }

    /**
    Adds line items to an invoice
    */
    function line_item($args = '') {

      extract(wp_parse_args($args, array(
          'name' => '',
          'description' => '',
          'quantity' => 1,
          'price' => '',
          'tax_rate' => ''
      )), EXTR_SKIP);


      if (empty ($name) || empty ($price))
          return false;

      $tax_rate = (!empty( $tax_rate ) && $tax_rate > 0) ? $tax_rate : 0 ;

      if ( !empty( $this->data['itemized_list'] ) ) {
        $items_in_list = count($this->data['itemized_list']) + 1;
      } else {
        $items_in_list = 1;
      }
      // Counted number of items in itemized list, and added another one
      $this->data['itemized_list'][$items_in_list]['name'] = $name;
      $this->data['itemized_list'][$items_in_list]['description'] = $description;
      $this->data['itemized_list'][$items_in_list]['quantity'] = $quantity;
      $this->data['itemized_list'][$items_in_list]['price'] = $price;
      $this->data['itemized_list'][$items_in_list]['tax_rate'] = $tax_rate;

      // Calculate line totals
      $this->data['itemized_list'][$items_in_list]['line_total_tax'] = $quantity * $price * ($tax_rate / 100);
      $this->data['itemized_list'][$items_in_list]['line_total_before_tax'] = $quantity * $price;
      $this->data['itemized_list'][$items_in_list]['line_total_after_tax'] = $quantity * $price * (1 + ($tax_rate / 100));

    }


    /**
    Adds line charges to an invoice
    */
    function line_charge($args = '') {

      $defaults = array (
          'name' => '',
          'amount' => '',
          'tax' => 0
      );

      extract(wp_parse_args($args, $defaults), EXTR_SKIP);
      if (empty ($name) || empty ($amount))
        return false;

      $items_in_list = count( empty( $this->data['itemized_charges'] ) ? null : $this->data['itemized_charges'] ) + 1;
      // Counted number of items in itemized list, and added another one
      $this->data['itemized_charges'][$items_in_list]['name'] = $name;
      $this->data['itemized_charges'][$items_in_list]['amount'] = $amount;
      $this->data['itemized_charges'][$items_in_list]['tax'] = $tax;
      // Calculate line totals
      $this->data['itemized_charges'][$items_in_list]['tax_amount'] = $amount / 100 * $tax;
      $this->data['itemized_charges'][$items_in_list]['after_tax'] = $amount + ( $amount / 100 * $tax );
      $this->data['itemized_charges'][$items_in_list]['before_tax'] = $amount;

    }


    /**
    Adds discounts to an invoice
    */
    function add_discount($args = '') {

      $defaults = array (
          'name' => '',
          'description' => '',
          'amount' => '',
          'type' => ''
      );
      extract(wp_parse_args($args, $defaults), EXTR_SKIP);
      if(!isset($this->data['discount'])) {
        $this->data['discount'] = array();
      }
      $items_in_list = count($this->data['discount']) + 1;
      $this->data['discount'][$items_in_list]['name'] = $name;
      $this->data['discount'][$items_in_list]['amount'] = $amount;
      $this->data['discount'][$items_in_list]['type'] = $type;

    }

/**
Creates an invoice schedule.
There can only be one, so deletes any other schedule.
*/
    function create_schedule($args = '') {

      $defaults = array (
          'unit' => false,
          'length' => false,
          'cycles' => false,
          'send_invoice_automatically' => false,
          'start_date' => array()
      );
      extract(wp_parse_args($args, $defaults), EXTR_SKIP);
      if(!isset($unit))
          return false;
      if(!isset($cycles))
          return false;

      $units  = array( 'months', 'days' );

      $this->is_recurring = true;
      $this->data['recurring']['active'] = 'on';
      $this->data['recurring']['unit'] = in_array( $unit, $units ) ? $unit : 'months';
      $this->data['recurring']['length'] = (int)$length;
      $this->data['recurring']['cycles'] = (int)$cycles;
      $this->data['recurring']['send_invoice_automatically'] =
        ( $send_invoice_automatically != 'on' && $send_invoice_automatically != 'off' )
        ? 'on'
        : $send_invoice_automatically;
      $this->data['recurring']['start_date']['month'] = (int)$start_date['month'];
      $this->data['recurring']['start_date']['day'] = (int)$start_date['day'];
      $this->data['recurring']['start_date']['year'] = (int)$start_date['year'];
      $this->data['recurring'] = apply_filters('wpi_create_schedule_recurring', $this->data['recurring']);
    }

    /**
     * Calculate amounts on each update
     *
     * @global type $wpdb
     * @global type $blog_id
     */
    function calculate_totals() {
      global $wpdb, $blog_id;

      //** Flush vars */
      $taxable_subtotal             = 0;
      $non_taxable_subtotal         = 0;
      $tax_percents                 = array();
      $total_charges                = 0;
      $total                        = 0;
      $this->data['subtotal']       = 0;
      $this->data['total_tax']      = 0;
      $this->data['total_discount'] = 0;

      //** Services itemized list */
      if(isset($this->data['itemized_list']) && is_array($this->data['itemized_list'])) {
        foreach ($this->data['itemized_list'] as $key => $value) {
          if ( $value['line_total_tax'] > 0 ) {
            $taxable_subtotal     += $value['line_total_before_tax'];
            $tax_percents[]       =  array(
								'tax' => $value['tax_rate'],
								'qty' => $value['quantity'],
								'prc' => $value['price']
						);
          } else {
            $non_taxable_subtotal += $value['line_total_before_tax'];
          }
        }
      }

      //** The same is for Charges itemized list */
      if(!empty($this->data['itemized_charges']) && is_array($this->data['itemized_charges'])) {
        foreach ($this->data['itemized_charges'] as $key => $value) {
          if ( !empty($value['tax_amount']) && $value['tax_amount'] > 0 ) {
            $taxable_subtotal     += $value['amount'];
            $tax_percents[]       =  array(
								'tax' => $value['tax'],
								'qty' => 1,
								'prc' => $value['amount']
						);
            $total_charges        += $value['amount'];
          } else {
            $non_taxable_subtotal += $value['amount'];
          }
        }
      }

      $avg_tax = 0;
			$sum = 0;
      if ( !empty( $tax_percents ) ) {
				foreach( $tax_percents as $tax_item ) {
					$sum += $tax_item['tax'];
				}
        $avg_tax = $sum / count( $tax_percents );
      }

      $this->data['subtotal'] = $taxable_subtotal + $non_taxable_subtotal;

      //** Get discount */
      if (!empty($this->data['discount']) && is_array($this->data['discount'])) {
        $highest_percent = 0;
        foreach ($this->data['discount'] as $key => $value) {
          if ($value['type'] == 'percent') {
            //** if a percentage is found, we make a note of it, and build a percentage array, which will later be used to calculate the highest */
            $percentage_found = true;
            if ((int) $highest_percent < (int) $value['amount']) {
              $highest_percent = $value['amount'];
            }
          } else {
            //** if non percentage, simply calculate the sum of all the discounts */
            $this->data['total_discount'] = $this->data['total_discount'] + $value['amount'];
          }
        }
        if (isset($percentage_found) && $percentage_found == true) {
          //** Only do this if a percentage was found.  figure out highest percentage, and overwrite total_discount */
          $this->data['total_discount'] = $this->data['subtotal'] * ($highest_percent / 100);
        }
      }

      //** Handle Tax Method */
      if ( !empty( $this->data['tax_method'] ) ) {
        switch ( $this->data['tax_method'] ) {

          case 'before_discount':

						foreach( $tax_percents as $tax_item ) {
							$this->data['total_tax'] += $tax_item['prc'] / 100 * $tax_item['tax'] * $tax_item['qty'];
						}

            break;

          case 'after_discount':
            $subtotal_with_discount  = $this->data['subtotal'] - $this->data['total_discount'];

            if ($this->data['subtotal'] > 0) {
              $taxable_amount = $taxable_subtotal / $this->data['subtotal'] * $subtotal_with_discount;
            } else {
              $taxable_amount = 0;
            }

            $this->data['total_tax'] = $taxable_amount * $avg_tax / 100;
            break;

          default:
						foreach( $tax_percents as $tax_item ) {
							$this->data['total_tax'] += $tax_item['prc'] / 100 * $tax_item['tax'] * $tax_item['qty'];
						}
            break;
        }
      } else {
        $this->data['tax_method'] = 'before_discount';
				foreach( $tax_percents as $tax_item ) {
					$this->data['total_tax'] += $tax_item['prc'] / 100 * $tax_item['tax'] * $tax_item['qty'];
				}
      }

      $total = number_format( (float)($this->data['subtotal'] - $this->data['total_discount'] + $this->data['total_tax']), 2, '.', '' );

      $total_payments = 0;
      $total_admin_adjustment = 0;
      $refunds = 0;

      $invoice_id = $this->data['invoice_id'];

      //** Add support for MS and for old invoice histories which will have a blog_id of 0 after upgrade */
      if($blog_id == 1) {
        $ms_blog_query = " AND ( blog_id = {$blog_id} OR blog_id = 0 ) ";
      } else {
        $ms_blog_query = " AND blog_id = {$blog_id} ";
      }

      $this->data['log'] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpi_object_log WHERE object_id = '".wpi_invoice_id_to_post_id($invoice_id)."' {$ms_blog_query}  ", ARRAY_A);

      //** Calculate adjustments and refunds */
      if(is_array($this->data['log'])) {

        //** Loop log items */
        foreach($this->data['log'] as $log_event) {

          //** If log item is add_payment */
          if($log_event['action'] == 'add_payment') {
            $total_payments += $log_event['value'];
          }

          //** If log item is do_adjustment */
          if($log_event['action'] == 'do_adjustment') {
            $total_admin_adjustment += $log_event['value'];
          }

          //** If log item is refund */
          if($log_event['action'] == 'refund') {
            $refunds += $log_event['value'];
          }
        }
      }

      $this->data['total_payments'] = $total_payments - $refunds;
      $this->data['adjustments']    = - ($total_payments + $total_admin_adjustment - $refunds);

      $this->data['net'] = number_format( (float)($total + $this->data['adjustments']), 2, '.', '' );

      //** Fixes calculations for recurring invoices - should be last to overwrite incorrect values. */
      if( $this->data['type'] == 'recurring' ) {
        $this->data['total_tax'] = number_format( (float)($this->data['subtotal'] * $avg_tax / 100), 2, '.', '' );
        $this->data['net'] = number_format( (float)($this->data['subtotal'] - $this->data['total_discount'] + $this->data['total_tax']), 2, '.', '' );
        unset($this->data['adjustments']);
      }

      if ( $refunds > 0 && $this->data['total_payments'] <= 0 ) {
        $this->data['post_status'] = 'refund';
      }
    }


  /**
   * Saves invoice to DB.
   *
   * itemized_list, billing are stored as serialized arrays
   * all else as flat meta data
   *
   * @uses $wpdb
    * @since 3.0
   *
   */
  function save_invoice() {
    global $wpdb;

    $this->calculate_totals();

    $non_meta_values = array(
      'ID',
      'subject',
      'description',
      'post_status',
      'post_content',
      'post_date',
      'log'
    );

    if(!empty($this->data['ID']) && (int)$this->data['ID'] > 0) {
      $data['ID'] = $this->data['ID'];
    }

    if(!empty($this->data['invoice_id']) && empty($data['ID'])) {
      $object_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = '{$this->data['invoice_id']}'");
      if(!empty($object_id)) {
        $data['ID'] = $object_id;
      }
    }

    $this->data['post_content'] = !empty($this->data['post_content'])?$this->data['post_content']:'';
    $data['post_title'] = !empty( $this->data['subject'] )?$this->data['subject']:$this->data['post_title'];
    $data['post_content'] = !empty( $this->data['description'] )?$this->data['description']:$this->data['post_content'];
    $data['post_type'] = 'wpi_object';

    /*
     * Determine if Amount to pay (subtotal) is not 0 and Balance (net) <= 0,
     * We set status as 'Paid'.
     */
    if (isset($this->data['net']) &&
        isset($this->data['subtotal']) &&
        $this->data['subtotal'] > 0 &&
        $this->data['net'] <= 0) {
          $data['post_status'] = 'paid';
          $this->add_entry("type=update&amount=paid&note=".__("Status of invoice was changed to 'Paid'.", WPI));
    } else {
      $data['post_status'] = (!empty($this->data['post_status']) ? $this->data['post_status'] : 'active');
    }

    if( !empty( $this->data['post_date'] ) ) {
      $data['post_date'] = $this->data['post_date'];
    }

    if(empty($data['post_title'])) {
      wpi_log_event(__("Error saving invoice. Subject (Title) can not be empty.", WPI));
      return false;
    }

    // WP figures out if we're saving or updating
    if(empty($data['ID'])) {
      $creator = '';
      if ( !empty( $this->data['created_by'] ) ) {
        $creator = __("Created from ", WPI).$this->data['created_by'];
      } else {
        $current_user = wp_get_current_user();
        $creator = __("Created by ", WPI).$current_user->display_name;
      }
      $this->data['ID'] = wp_insert_post($data);
      $this->add_entry("type=create&note=".$creator);
    } else {
      $this->data['ID'] = wp_update_post($data);
      if (!empty($this->is_recurring) && $this->is_recurring) {
        $this->add_entry("attribute=invoice&type=update&note=".__("Recurring invoice updated.", WPI));
      } else if (!empty($this->is_quote) && $this->is_quote) {
        $this->add_entry("attribute=quote&type=update&note=".__("Quote updated.", WPI));
      } else {
        if ( $this->data['type'] == 'single_payment' ) {

        } else {
          $this->add_entry("type=update&note=".__("Updated.", WPI));
        }
      }
    }

    if(empty($this->data['ID']) || $this->data['ID'] == 0) {
      wpi_log_event(__("Error saving invoice. Query used: ", WPI).$wpdb->last_query);
      return false;
    }

    /*
     * We need to determine hash to avoid confusing with invoice URL in future
     * It's need for debug in the most cases.
     * The general reason is three (3) different invoice IDs which we use:
     * ID (post)
     * invoice_id (meta)
     * custom_id (meta)
     * But we always need only invoice_id
     */
    $this->data['hash'] = md5($this->data['invoice_id']);

    $meta_keys = array();
    // now add the rest of the array
    foreach($this->data as $meta_key => $meta_value) {
      do_action('wpi_save_meta_' . $meta_key, $meta_value, $this->data);

      if(in_array($meta_key, $non_meta_values)) {
        continue;
      }
      $meta_keys[] = $meta_key;

      update_post_meta($this->data['ID'], $meta_key, $meta_value);
    }

    // Remove old postmeta data which is not used anymore
    $meta_keys = apply_filters('wpi_custom_meta', $meta_keys);
    if(!empty($meta_keys)) {
      $wpdb->query("
        DELETE FROM {$wpdb->postmeta}
        WHERE post_id = '{$this->data['ID']}'
        AND meta_key NOT IN('" . implode( "','", $meta_keys ) . "')");
    }

    return $this->data['ID'];
  }

  /**
   * Delete Invoice object
   *
   * @since 3.0
   *
   */
  function delete() {
    $ID = $this->data['ID'];
    if( $ID ) {
      if( 'trash' == $this->data['post_status'] ) {
        if(wp_delete_post($ID)) {
          return true;
        }
      } else {
        return $this->trash();
      }

    }
    return false;
  }

  /**
   * Trash Invoice object
   *
   * @since 3.0
   *
   */
  function trash() {
    $ID = $this->data['ID'];
    if($ID && 'pending' != $this->data['post_status']) {
      if(wp_trash_post($ID)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Restore (Untrash) Invoice object
   *
   * @since 3.0
   *
   */
  function untrash() {
    $ID = $this->data['ID'];
    if($ID) {
      if(wp_untrash_post($ID)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Set Archive Status
   *
   * @since 3.0
   *
   */
  function archive() {
    $ID = $this->data['ID'];
    if($ID) {
      if ( 'trash' != $this->data['post_status'] && 'pending' != $this->data['post_status']) {
        /* Update post status */
        $ID = wp_update_post(array(
          'ID' => $this->data['ID'],
          'post_status' => 'archived'
        ));
        /* Determine if post was successfully updated */
        if((int)$ID > 0) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Un-Archive Invoice
   * Set Active Status
   *
   * @uses $wpdb
   * @since 3.0
   *
   */
  function unarchive() {
    global $wpdb;
    $ID = $this->data['ID'];
    if($ID) {
      if ( 'archived' == $this->data['post_status']) {
        /* Update post status */
        $ID = wp_update_post(array(
          'ID' => $this->data['ID'],
          'post_status' => 'active'
        ));
        /* Determine if post was successfully updated */
        if((int)$ID > 0) {
          return true;
        }
      }
    }
    return false;
  }

/**
    Figures out when next payment is due. Mostly for recurring cycles.
*/
    function payment_due($args = '') {
        global $wpi_settings;
        $defaults = array ('invoice_id' => false);
        extract(wp_parse_args($args, $defaults), EXTR_SKIP);
        // Figure out if this is a recurring bill
        return WPI_Functions::days_since($this->data['meta'][due_date]);
    }
}
?>
