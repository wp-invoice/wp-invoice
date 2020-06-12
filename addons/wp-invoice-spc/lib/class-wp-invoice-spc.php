<?php
/**
 * Name: Single Page Checkout
 * Class: wpi_spc
 * Global Variable: wpi_spc
 * Internal Slug: wpi_spc
 * JS Slug: wpi_spc
 * Version: 2.0.0
 * Minimum Core Version: 4.0.0
 * Feature ID: 10
 * Description: Create single page checkout forms.
 */


class wpi_spc {

  /**
   * Array of gateways that SPC supports.
   *
   * @var array
   */
  static $accept_gateways = array('wpi_authorize', 'wpi_paypal');

	/**
   * Primary SPC function called after premium features have been loaded.
   * Adds feature functionality by actions and filters
   * @since 1.0
   */
  static function init_feature() {
    global $wpi_settings, $wpi_checkout, $wp_version;

    //** Front-end stuff */
    add_action('init',                                array(__CLASS__, 'init'));
    add_action('template_redirect',                   array(__CLASS__, 'template_redirect'));

    //** Ajax handler for processing transactions */
    add_action('wp_ajax_wpi_checkout_process',        array(__CLASS__, 'wpi_checkout_process'));
    add_action('wp_ajax_nopriv_wpi_checkout_process', array(__CLASS__, 'wpi_checkout_process'));

    //** Add new Invoice type */
    add_filter('wpi_object_types',                    array(__CLASS__, 'wpi_object_types'));

    //** Add Checkout Tab */
    add_filter('wpi_settings_page_basic_settings',    array(__CLASS__, 'settings_page_content'));
    add_filter('wpi_object_paid_message',             array(__CLASS__, 'paid_message'), 10, 2);

    //** Contextual Help */
    add_action("wpi_settings_page_help",              array(__CLASS__, 'contextual_help'));
    add_action('wpi_paypal_complete_ipn',             array(__CLASS__, 'paypal_complete_ipn'));

    //** Add hidden attributes */
    add_action('wpi::spc::payment_form_top',          array(__CLASS__, 'hidden_attributes'));
    add_action('wpi::spc::saved',                     array(__CLASS__, 'after_save_action'));

    if ( !empty( $wpi_settings['ga_event_tracking'] ) && $wpi_settings['ga_event_tracking']['enabled'] == 'true' ) {
      wp_enqueue_script('wpi-ga-tracking', ud_get_wp_invoice()->path( 'static/scripts/wpi.ga.tracking.js', 'url' ), array('jquery'));
    }

    /* Add Shortcode */
    add_shortcode('wpi_checkout', array('wpi_spc', 'shortcode_wpi_checkout'));

    $wpi_checkout['info_block']['customer_information'] = array(
      'first_name' => array(
        'label' => __("First Name", ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'last_name' => array(
        'label' => __("Last Name", ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'phonenumber' => array(
        'label' => __("Phone", ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'user_email' => array(
        'label' => __("Email", ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      )
    );

    $wpi_checkout['info_block']['billing_information'] = array(
      'cc_number' => array(
        'label' => __('Credit Card', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'cc_expiration' => array(
        'label' => __('Expiration Date', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'cc_code' => array(
        'label' => __('Card Code', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      )
    );

    $wpi_checkout['info_block']['billing_address'] = array(
      'streetaddress' => array(
        'label' => __('Street Address', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'city' => array(
        'label' => __('City', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'state' => array(
        'label' => __('State/Province', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'zip' => array(
        'label' => __('Zip/Postal Code', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      ),
      'country' => array(
        'label' => __('Country', ud_get_wp_invoice_spc()->domain),
        'type' => 'input'
      )
    );

    if(!isset($wpi_settings['spc_checkout']['create_user_accounts'])) {
      $wpi_settings['spc_checkout']['create_user_accounts'] = 'true';
    }
    if(!isset($wpi_settings['spc_checkout']['enforce_ssl'])) {
      $wpi_settings['spc_checkout']['enforce_ssl'] = 'false';
    }

    self::schedule_events();
    
    self::$accept_gateways = apply_filters( 'wpi::spc::accepted_gateways', self::$accept_gateways );

  }  /* end: wpi_premium_loaded(); */

  /**
   * Template redirect for SPC
   * @global array $wpi_settings
   * @global type $post
   * @author korotkov@ud
   */
  static function template_redirect() {
    global $wpi_settings, $post;

    if ( empty( $post->post_content ) ) return;

    if ( $wpi_settings['spc_checkout']['enforce_ssl'] == 'true' && !is_ssl() ) {
      if (preg_match('/\[wpi_checkout.*?\]/', $post->post_content)) {
        header("Location: https://" . (  $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ) . $_SERVER['REQUEST_URI']);
        exit;
      }
    }

    if ( $wpi_settings['spc_checkout']['enforce_ssl'] == 'false' && is_ssl() ) {
      if (preg_match('/\[wpi_checkout.*?\]/', $post->post_content)) {
        header("Location: http://" . (  $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ) . $_SERVER['REQUEST_URI']);
        exit;
      }
    }
  }

  /**
   * Handle IPN request to process callback
   *
   * @param type $post
   */
  static function paypal_complete_ipn( $post ) {
    if ( $post['txn_type'] == 'cart' && $post['payment_status'] == 'Completed' ) {
      if ( !empty( $post['custom'] ) ) {
        $customs = explode('|', $post['custom']);
        $custom_params = array();
        foreach ($customs as $custom) {
          $custom_params[] = explode(':', $custom);
        }
        if ( !empty( $custom_params ) && is_array( $custom_params ) ) {
          foreach ($custom_params as $param) {
            if ( $param[0] == 'cbf' && function_exists( $param[1] ) ) {
              $post_id = wpi_invoice_id_to_post_id( $post['invoice'] );

              $domain_name = get_post_meta( $post_id, 'customer_information', 1);
              $user_email  = get_post_meta( $post_id, 'user_email', 1);
              $items       = get_post_meta( $post_id, 'itemized_list', 1 );
              $user        = get_user_by( 'email', $user_email );
              $user_meta   = get_user_meta( $user->ID );

              $post_data   = get_post_meta( $post_id );

              if ( !empty( $user_meta['temp_pp_ipn_data'][0] ) ) {
                $new_user = true;
                $new_user_password = unserialize( $user_meta['temp_pp_ipn_data'][0] );
                delete_user_meta( $user->ID, 'temp_pp_ipn_data' );
              } else {
                $new_user = false;
                $new_user_password = '';
              }

              $transaction_data = array(
                'new_user' => $new_user,
                'new_user_password' => $new_user_password['pass'],
                'transaction' => array(
                  'payment_status' => 'Complete'
                ),
                'billing' => array(
                  'customer_information_domain_name' => $domain_name['domain_name'],
                  'user_email' => $user_email
                ),
                'user_data' => array(
                  'ID'         => $user->ID,
                  'first_name' => !empty( $user_meta['first_name'][0] ) ? $user_meta['first_name'][0] : 'User',
                  'last_name'  => !empty( $user_meta['last_name'][0] ) ? $user_meta['last_name'][0] : 'User',
                  'user_email' => $user_email,
                  'user_login' => $user_email
                ),
                'items' => $items,
                'other_meta' => array(
                  'charge_amount' => $post['mc_gross'],
                  'cc_number'     => $post['payer_email'],
                  'transaction_id'=> $post['txn_id']
                ),
                'post_data' => $post_data
              );
              call_user_func( $param[1] , $transaction_data );
            }
          }
        }
      }
    }
  }

  /**
   * Init schedule events
   */
  static function schedule_events() {

    add_action( 'wpi_spc_remove_abandoned_transactions', array( 'wpi_spc', 'remove_abandoned_transactions' ) );
    add_filter( 'cron_schedules', array('wpi_spc', 'cron_schedules'));

    if ( !wp_next_scheduled( 'wpi_spc_remove_abandoned_transactions' ) ) {
      wp_schedule_event( time(), 'ten_minutes', 'wpi_spc_remove_abandoned_transactions' );
    }
  }

  /**
   * Get available gateways for SPC forms
   *
   * @global array $wpi_settings
   * @return array
   * @author korotkov@ud
   */
  static function available_gateways() {
      global $wpi_settings;

      $gateways = array();
      foreach ( $wpi_settings['billing'] as $key => $value ) {
          if ( isset($value['allow']) && $value['allow'] == 'true' && in_array( $key, self::$accept_gateways ) ) {
              $gateways[$key]['name'] = $value['name'];
              if ( !empty($value['default_option']) ) {
                  $gateways[$key]['default_option'] = $value['default_option'];
              } else {
                  $gateways[$key]['default_option'] = 'false';
              }
          }
      }
      return $gateways;
  }

	/**
	 * Remove abandoned PayPal transactions every hour
	 *
	 * @author korotkov@ud
	 */
	static function remove_abandoned_transactions() {

		$args = array(
			'numberposts' => 0,
			'meta_key'    => 'processed_by_ipn',
			'meta_value'  => 'false',
			'post_type'   => 'wpi_object',
			'post_status' => 'pending'
		);

		$seconds_old = 60*60;

		$invoices_array = get_posts( $args );

		foreach( $invoices_array as $invoice ) {
			if ( ( strtotime( $invoice->post_date ) + $seconds_old ) < (time() + get_option('gmt_offset')*60*60) ) {
				wp_delete_post( $invoice->ID );
			}
		}
	}

	/**
	 * Add new pariod to cron schedules
	 *
	 * @param array $schedules
	 * @return array
	 * @author korotkov@ud
	 */
	static function cron_schedules( $schedules ) {
		// add a 'weekly' schedule to the existing set
		$schedules['ten_minutes'] = array(
			'interval' => 600,
			'display' => __('Every 10 Minutes', ud_get_wp_invoice_spc()->domain)
		);
		return $schedules;
	}

  /**
   * Get gateways slug list
   *
   * @global array $wpi_settings
   * @return CSV string
   * @author korotkov@ud
   */
  static function gateways_values() {
    global $wpi_settings;

    $keys = array();
    foreach ( $wpi_settings['billing'] as $key => $value ) {
      if ( in_array( $key, self::$accept_gateways ) ) $keys[] = "'".$key."'";
    }
    
    return implode(', ', $keys);
  }

  /**
   * Show properly paid message for SPC complete transaction.
   *
   * @param string $current_message
   * @param object $invoice
   * @author korotkov@ud
   * @return string
   */
  static function paid_message( $current_message, $invoice ) {
    if ( $invoice->data['type'] == 'single_payment' ) return __('Transaction complete.', ud_get_wp_invoice_spc()->domain);
    return $current_message;
  }

  /**
   * Filter handler for wpi contextual help
   *
   * @param array $current
   * @return array
   * @author korotkov@ud
   */
  static function contextual_help( $data ) {

    $data['Main'][] = "<p>".__('<b>Single Page Checkout</b><br /> Main settings for Single Page Checkout Premium Feature. <a target="_blank" href="https://www.usabilitydynamics.com/product/wp-invoice-single-page-checkout">More...</a>', ud_get_wp_invoice_spc()->domain)."</p>";

    $data['Single Page Checkout'][] = "<h3>".__('Single Page Checkout', ud_get_wp_invoice_spc()->domain)."</h3>";
    $data['Single Page Checkout'][] = "<p>".__('Create single page checkout forms that are then visible in the "Invoice" overview.', ud_get_wp_invoice_spc()->domain)."</p>";
    $data['Single Page Checkout'][] = "<p>".__('You can create Single Checkout Page by using shortcode <b>[wpi_checkout]</b>. Options:', ud_get_wp_invoice_spc()->domain)."</p>";

    $options['title']                = __('The title that will be displayed in control panel overview.', ud_get_wp_invoice_spc()->domain);
    $options['terms_page_id']        = __('ID of page that has sales terms, inserts a link into SPC to the page.', ud_get_wp_invoice_spc()->domain);
    $options['item']                 = __('A comma separated list of items that the SPC is for.', ud_get_wp_invoice_spc()->domain);
    $options['callback_function']    = __('PHP function to be executed when transaction is complete.', ud_get_wp_invoice_spc()->domain);
    $options['customer_information'] = __('A CSV list of Title of any custom attributes for the checkout.', ud_get_wp_invoice_spc()->domain);
    $options['custom_amount']        = __('`true/false` value. If true then custom amount field will appear on SPC. Do not use <u>fee</u> and <u>items</u> in case of custom_amount is true.', ud_get_wp_invoice_spc()->domain);
    $options['uncheck_items']        = __('Disabled the checkout items, only use this if you are using some sort of JS script to enable them back on.', ud_get_wp_invoice_spc()->domain);
    $options['fee']                  = __('Percent value of fee applied to items amounts (ex. fee=2 or fee="2%" )', ud_get_wp_invoice_spc()->domain);
    $options['gateways']             = __('CSV list of gateways available for payment.', ud_get_wp_invoice_spc()->domain).__(' Available values: ', ud_get_wp_invoice_spc()->domain).'<b>'.self::gateways_values().'</b>';

    if(is_array($options)) {
      $data['Single Page Checkout'][] = '<ul>';
      foreach($options as $var => $title) {
        $data['Single Page Checkout'][] =  '<li><b>' . $var . '</b> - ' . $title . '</li>';
      }
      $data['Single Page Checkout'][] = '</ul>';
    }
    $data['Single Page Checkout'][] = "<p>".__('Example: <b>[wpi_checkout title="Water Bill Payment" custom_amount="true" fee="5%"]</b>', ud_get_wp_invoice_spc()->domain)."</p>";

    return $data;
  }

  /**
   * Save custom fields data
   *
   * @param array $args
   * @return mixed
   */
  static function custom_field_save( $args ) {

    $defaults = array(
       'field'      => '',
       'data'       => '',
       'invoice_id' => '',
       'user_data'  => array()
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    if ( empty( $field ) || empty( $data ) || empty( $invoice_id ) ) return;

    if ( strstr( $field, 'customer_information_' ) ) {

      $real_field_name = str_replace('customer_information_', '', $field);

      $customer_information = get_post_meta( $invoice_id, 'customer_information', true );
      $customer_information[ $real_field_name ] = $data;

      update_post_meta($invoice_id, 'customer_information', $customer_information);

      if ( !empty( $user_data ) ) {
        $user_data[$real_field_name] = $data;
        WPI_Functions::update_user($user_data);
      }

    }

  }

  /**
   * Process custom amount
   *
   * @param number $total
   * @param array $wpi_checkout
   * @author korotkov@ud
   * @return number
   */
  static function process_custom_amount( $total, $wpi_checkout ) {

    if ( !empty($wpi_checkout['billing']['amount']) ) {
      if ( !empty($wpi_checkout['fee']) ) {
        return (float)$wpi_checkout['billing']['amount'] + (float)$wpi_checkout['billing']['amount']/100*$wpi_checkout['fee'];
      }
      return (float)$wpi_checkout['billing']['amount'];
    }
    return $total;

  }

  /**
   * AJAX function for handling transactions
   *
   * @since 1.0
   *
   */
  static function wpi_checkout_process( $data = false ) {
    global $post, $wpi_settings, $wp_version;

    add_filter( 'wpi_spc_total_filter', array( __CLASS__, 'process_inline_fee' ), 10, 2 );
    add_action( 'wpi_spc::field_save', array( __CLASS__, 'custom_field_save' ) );

    if( !$data || !is_array( $data ) ){
      $data = array();
      parse_str($_REQUEST['data'], $data);
    }
    $billing = $data['wpi_checkout']['billing'];

    add_filter( 'wpi_spc_total_filter', array( 'wpi_spc', 'process_custom_amount' ), 10, 2 );

    $title     = null;
    $fee_value = null;
    if ( !empty( $data['wpi_checkout']['spc_title'] ) ) {
      $title = stripslashes(trim($data['wpi_checkout']['spc_title']));
    }
    if ( !empty( $data['wpi_checkout']['fee'] ) ) {
      $fee_value = $data['wpi_checkout']['fee'];
    }

    $return   = array();
    $new_user = false;

    //** Check for hacking */
    if ( $data['wpi_checkout']['security_hash'] != self::generate_security_hash( !empty( $data['wpi_checkout']['fee'] )?$data['wpi_checkout']['fee']:'', $data['wpi_checkout']['default_price'] ) ) {
      $return['payment_status'] = 'hacking_attempt';
    }

    //** Validate Request */
    if( !empty($data['wpi_checkout']['terms']) && $data['wpi_checkout']['terms'] == 'false' ) {
      $return['payment_status'] = 'validation_fail';
      $return['missing_data']['terms'] = __('Terms must be accepted.', ud_get_wp_invoice_spc()->domain);
    }

    if(empty($billing['user_email'])) {
      $return['payment_status'] = 'validation_fail';
      $return['missing_data']['user_email'] = __('An e-mail address is required.', ud_get_wp_invoice_spc()->domain);
    }
    if(empty($billing['first_name'])) {
      $return['payment_status'] = 'validation_fail';
      $return['missing_data']['first_name'] = __('You must enter a name.', ud_get_wp_invoice_spc()->domain);
    }
    if(empty($billing['cc_number']) && $data['wpi_checkout']['payment_method'] == 'wpi_authorize') {
      $return['payment_status'] = 'validation_fail';
      $return['missing_data']['cc_number'] = __('You must enter a valid credit card number.', ud_get_wp_invoice_spc()->domain);
    }
    if(empty($billing['cc_expiration']) && $data['wpi_checkout']['payment_method'] == 'wpi_authorize') {
      $return['payment_status'] = 'validation_fail';
      $return['missing_data']['cc_expiration'] = __('You must enter an expiration date.', ud_get_wp_invoice_spc()->domain);
    }
		
		/* Custom Checkout Process Validation */
		$return = apply_filters( 'wpi_checkout_process_validation', $return, $data );

    if(!empty($return['payment_status'])) {
      echo json_encode($return);
      die();
    }

    ob_start();

    /* Create item list */
    $item_names[] = __('No items', ud_get_wp_invoice_spc()->domain);
    $totals[]     = 0;
    if ( !empty( $data['wpi_checkout']['items'] ) ) {
      $item_names = array();
      $totals     = array();
      foreach($wpi_settings['predefined_services'] as $count => $item) {
        if(in_array($item['name'], array_keys($data['wpi_checkout']['items']))) {
          $items[$count] = $item;
          $item_names[] = $item['name'];
          $totals[] = $item['price']*$item['quantity'] + ($item['price']*$item['quantity']/100*$item['tax']);
        }
      }
      $totals_before_fee = array_sum($totals);
    } else {
      if ( !empty( $data['wpi_checkout']['billing']['amount'] ) ) {
        $totals_before_fee = $data['wpi_checkout']['billing']['amount'];
      } else {
        $totals_before_fee = 0;
      }
    }

    $totals = apply_filters( 'wpi_spc_total_filter', array_sum($totals), $data['wpi_checkout'] );

    /* Do this early so customer ID can be passed to merchant if it exists */
    $user_id = email_exists($billing['user_email']);

    $process_data = array(
      'venue' => $data['wpi_checkout']['payment_method'],
      'amount' => $totals,
      'description' => implode(',', $item_names),
      'trans_id' => time(),
      'customer_ip' => $_SERVER['REMOTE_ADDR'],
      'payer_first_name' => !empty($billing['first_name'])?$billing['first_name']:'',
      'payer_last_name' => !empty($billing['last_name'])?$billing['last_name']:'',
      'payer_email' => !empty($billing['user_email'])?$billing['user_email']:'',
      'cc_number' => !empty($billing['cc_number'])?$billing['cc_number']:'',
      'cc_expiration' => !empty($billing['cc_expiration'])?$billing['cc_expiration']:'',
      'cc_code' => !empty($billing['cc_code'])?$billing['cc_code']:'',
      'address' => !empty($billing['streetaddress'])?$billing['streetaddress']:'',
      'state' => !empty($billing['state'])?$billing['state']:'',
      'city' => !empty($billing['city'])?$billing['city']:'',
      'phone_number' => !empty($billing['phonenumber'])?$billing['phonenumber']:'',
      'country' => !empty($billing['country'])?$billing['country']:'',
      'zip' => !empty($billing['zip'])?$billing['zip']:'',
      'currency_code' => !empty($data['wpi_checkout']['currency_code'])?$data['wpi_checkout']['currency_code']:''
    );

    if($user_id) {
      $process_data['customer_id'] = $user_id;
    } else {
      $new_user = true;
    }

    /**
     * Run our final data through a filter
     */
    $process_data = apply_filters( 'wpi_spc_pre_process_data', $process_data, $data );

    /**
     * Process transaction
     */
    $result = wpi_process_transaction($process_data);

    /* Debug */
    //$result = array('payment_status' => 'Complete', 'receiver_email' => 'user@example.com', 'transaction_id' => rand(10000, 99999));

    //** Check if Payment Succesful */
    if($result['payment_status'] == 'Complete') {
      /* Create (Update) User Account */
      //if($wpi_settings['spc_checkout']['create_user_accounts'] == 'true' || $user_id) {
        $user_data = array(
          "user_email" => $billing['user_email'],
          "first_name" => $billing['first_name'],
          "last_name" => $billing['last_name'],
          "phonenumber" => $billing['phonenumber'],
          "streetaddress" => $billing['streetaddress'],
          "city" => $billing['city'],
          "state" => $billing['state'],
          "zip" => $billing['zip'],
          "country" => $billing['country']
        );

        if( $new_user ) {
          $new_password = wp_generate_password( 12, false);
          $user_data['user_pass'] = $new_password;
          $transaction_data['new_user_password'] = $new_password;
        }
        $user_id = WPI_Functions::update_user($user_data);
        //** If new user has been successfully created */
        if ( $new_user && $user_id ) {
          //** Send user password to user */
          do_action('wpi_spc_user_created', $user_id, $new_password);
          //** Remember password temporary fo PayPal IPN. Will be removed after IPN callback */
          update_user_meta($user_id, 'temp_pp_ipn_data', array('pass'=>$new_password));
        }
      //}

      //** Load user data if we can */
      if($user_id && !is_wp_error($user_id)) {
        $user_data =(array) get_userdata($user_id);
        /** WP 3.3 compatibility */
        if ( version_compare($wp_version, '3.3', '>=') ) {
          $meta = get_metadata('user', $user_id, '', 1);
          $user_data = array(
            "ID" => $user_id,
            "user_email" => $user_data['data']->user_email,
            "user_login" => $user_data['data']->user_email,
            "first_name" => $meta['first_name'][0],
            "last_name" => $meta['last_name'][0],
            "phonenumber" => $meta['phonenumber'][0],
            "streetaddress" => $meta['streetaddress'][0],
            "city" => $meta['city'][0],
            "state" => $meta['state'][0],
            "zip" => $meta['zip'][0],
            "country" => $meta['country'][0]
          );
        }

      }

      if ( !empty( $items ) ) {
        $transaction_data['items'] = $items;
      }

      if ( !empty( $billing['amount'] ) ) {
        $transaction_data['amount'] = (float)$billing['amount'];
      }
      $transaction_data['user_data'] = $user_data;
      $transaction_data['transaction'] = $result;
      $transaction_data['billing'] = $billing;

      if ( !is_null( $title ) ) {
        $transaction_data['title'] = $title;
      }

      if ( !is_null( $fee_value ) ) {
        $transaction_data['fee'] = ($totals_before_fee/100)*$fee_value;
      }

      if($new_user) {
        $transaction_data['new_user']['password'] = $new_password;
      }

      $transaction_data['other_meta']['charge_amount'] = $process_data['amount'];
      $transaction_data['other_meta']['transaction_id'] = $process_data['trans_id'];
      $transaction_data['other_meta']['cc_number'] = "XXXX-XXXX-XXXX-" . substr($process_data['cc_number'],-4,4);

      if($invoice_id = self::save_invoice($transaction_data)) {
        $return['payment_status'] = 'success';
        $return['message'] = apply_filters("wpi_checkout_payment_success_message",  __('Thank you. Your payment has been processed.', ud_get_wp_invoice_spc()->domain), !empty($items)?$items:array(), $result);
				$return['invoice_id'] = $invoice_id;

        $post_data = get_post_meta( $invoice_id );
        $transaction_data['post_data'] = $post_data;

        if(!empty($data['wpi_checkout']['callback_action']) && function_exists($data['wpi_checkout']['callback_action'] )) {
          $return['callback'] = call_user_func($data['wpi_checkout']['callback_action'] , $transaction_data);
        }
      }

    } else {
      //** Payment failed. Return error code. */
      $return['payment_status'] = 'processing_failure';
      $return['message'] = !empty( $result['error_message'] ) ? __('Transaction Error: ', ud_get_wp_invoice_spc()->domain) . $result['error_message'] :  __('There was an error with the transaction. Please select another payment venue, or contact support.', ud_get_wp_invoice_spc()->domain);
    }

    $invoice_items = array();

    /** It is for adding SKU (unique) field to items list */
    if ( !empty($transaction_data['amount']) ){
      $invoice_items[0]['name'] = $transaction_data['title'];
      $invoice_items[0]['price'] = $transaction_data['amount'];
      $invoice_items[0]['quantity'] = 1;
      $invoice_items[0]['id'] = str_replace('-', '_', sanitize_title( $transaction_data['title'] ));
      $return['wpi_invoice']['invoice_amount'] = $transaction_data['amount'];
    }else{
      $return['wpi_invoice']['invoice_amount'] = 0;
      foreach ((array)$transaction_data['items'] as $key =>$item) {
        $invoice_items[$key]['name'] = $item['name'];
        $invoice_items[$key]['price'] = $item['price'];
        $invoice_items[$key]['quantity'] = $item['quantity'];
        $invoice_items[$key]['id'] = str_replace('-', '_', sanitize_title( $item['name'] ));
        $return['wpi_invoice']['invoice_amount'] += $item['price'];
      }
    }
    $return['wpi_invoice']['user_data'] = array('city'=>$transaction_data['user_data']['city'],'state'=>$transaction_data['user_data']['state'],'country'=>$transaction_data['user_data']['country']);
    $return['wpi_invoice']['business_name']  = $wpi_settings['business_name'];
    $return['wpi_invoice']['invoice_title']  = $transaction_data['title'];
    $return['wpi_invoice']['invoice_id'] = $invoice_id;
    $return['wpi_invoice']['invoice_items'] = $invoice_items;

    /** Silence Warnings */
    $errors = ob_get_contents();
    ob_end_clean();

    if( !empty( $errors ) ) {
      $return['errors'] = $errors;
    }

    $return = apply_filters( 'wpi::spc::checkout_process_return', $return );

    die(json_encode($return));
  } /* end: wpi_checkout_process(); */

  /**
   * Save Transaction as Invoice object
   * @author Maxim Peshkov
   */
  static function save_invoice($transaction_data) {
    global $wpi_checkout;

    /** Set transaction items (Line Item) names to string */
    $items = array();
    if ( !empty( $transaction_data['items'] ) ) {
      foreach ($transaction_data['items'] as $line_item) {
        $items[] = $line_item['name'];
      }
    }
    $items = implode(',', $items);

		/** Generate proper description */
		if ( empty( $items ) && !empty( $transaction_data['amount'] ) ) {
			$description = __('Custom Amount: ' . wp_invoice_currency_format( (float)$transaction_data['amount'] ), ud_get_wp_invoice_spc()->domain);
		} else {
			$description = __("Items: ", ud_get_wp_invoice_spc()->domain) . $items;
		}
		if ( !empty( $transaction_data['fee'] ) ) {
			$description .= " + " . wp_invoice_currency_format( (float)$transaction_data['fee'] ) . __(" of fee.", ud_get_wp_invoice_spc()->domain);
		}

    $subject = __("Transaction #", ud_get_wp_invoice_spc()->domain) . $transaction_data['other_meta']['transaction_id'] . " (". $transaction_data['other_meta']['cc_number'] .")";
    if ( !empty( $transaction_data['title'] ) ) {
      $subject = $transaction_data['title']." (".__('Transaction #', ud_get_wp_invoice_spc()->domain) . $transaction_data['other_meta']['transaction_id'] . ")";
    }

    /* Create new Invoice object */
    $invoice = new WPI_Invoice();
    $invoice->create_new_invoice(array(
      'subject' => $subject,
      'description' => $description
    ));

    $invoice->set(array(
      "type" => "single_payment",
      "user_email" => trim($transaction_data['user_data']['user_email'])
    ));

    if ( !empty( $transaction_data['title'] ) ) {
      $invoice->set(array(
        "created_by" => $transaction_data['title']
      ));
    } else {
      $invoice->set(array(
        "created_by" => __("Default Checkout Form", ud_get_wp_invoice_spc()->domain)
      ));
    }

    if ( !empty( $transaction_data['fee'] ) ) {
      $invoice->line_charge(array (
        'name' => 'Fee',
        'amount' => number_format( (float)$transaction_data['fee'], 2, '.', '' ),
        'tax' => 0
      ));
    }

    /* Add line items */
    if ( !empty( $transaction_data['items'] ) ) {
      foreach ($transaction_data['items'] as $line_item) {
        $invoice->line_item(array(
          "name" => $line_item['name'],
          "description" => $line_item['description'],
          "quantity" => $line_item['quantity'],
          "price" => number_format( (float)$line_item['price'], 2, '.', '' ),
          "tax_rate" => $line_item['tax']
        ));
      }
    }

    if ( !empty( $transaction_data['amount'] ) ) {
      $invoice->line_item(array(
          "name" => __('Amount', ud_get_wp_invoice_spc()->domain),
          "description" => __('Custom amount from SPC', ud_get_wp_invoice_spc()->domain),
          "quantity" => 1,
          "price" => number_format( (float)$transaction_data['amount'], 2, '.', '' ),
          "tax_rate" => 0
        ));
    }

    if ( in_array( $transaction_data['transaction']['payment_method'], apply_filters( 'wpi::spc::pending_invoice_gateways', array( 'wpi_paypal' ) ) ) ) {
      $invoice->set(array(
          "post_status" => "pending"
      ));
    }

    /* Try to save transaction data as Invoice object */
    $invoice_id = $invoice->save_invoice();

    /** Save custom */
    foreach( $transaction_data['billing'] as $data_key => $data_value ) {
      do_action( 'wpi_spc::field_save', array(
          'field'      => $data_key,
          'data'       => $data_value,
          'invoice_id' => $invoice_id,
          'user_data'  => $transaction_data['user_data']
      ));
    }

    do_action( 'wpi::spc::saved', $invoice_id );

    /* If transaction is completed and invoice was successfully saved
     * We add transaction log and update status
     */
    if ( in_array( $transaction_data['transaction']['payment_method'], apply_filters( 'wpi::spc::pending_invoice_gateways', array( 'wpi_paypal' ) ) ) ) {
			update_post_meta($invoice_id, 'processed_by_ipn', 'false');
      $paypal_invoice = new WPI_Invoice();
      $paypal_invoice->load_invoice("id=$invoice_id");
      do_action( 'wpi_successful_payment', $paypal_invoice );
			return wpi_post_id_to_invoice_id($invoice_id);
		}
    if(strtolower($transaction_data['transaction']['payment_status']) == 'complete') {

      if (!empty($invoice_id)) {
        /* Re-Init $invoice */
        $invoice = new WPI_Invoice();
        $invoice->load_invoice("id=$invoice_id");
        /* Add payment */
        $note = sprintf(__("Transaction #%s was successfully completed.", ud_get_wp_invoice_spc()->domain), $transaction_data['transaction']['transaction_id']);
        $event_type = 'add_payment';
        $event_amount = $transaction_data['other_meta']['charge_amount'];
        $event_note = WPI_Functions::currency_format(abs($event_amount), $invoice_id)." ".__('paid in', ud_get_wp_invoice_spc()->domain)." - $note";
        $timestamp = time();

        $invoice->add_entry(array(
          "attribute" => "balance",
          "note" => $event_note,
          "amount" => $event_amount,
          "type" => $event_type,
          "time" => $timestamp
        ));
        /* Update invoice */
        $invoice->save_invoice();

        do_action( 'wpi_successful_payment', $invoice );

        return $invoice_id;

      } else {
        return false;
      }
    }
    return false;
  }

  /**
   * Front-end actions (called on init level)
   *
   * Detects wpi_checkout shortcode
   *
   * @todo Decide if IPN is done here or handled by another WPI function
   * @todo Run the WPI SSL enforecement check
   * @since 1.0
   *
   */
  static function init() {
    global $post;

    if(is_admin()) {
      return;
    }

    wp_enqueue_script('jquery.number.format');
  } /* end: init(); */

  /**
   * Renders payment shortcode.
   *
   * Attempts to load a custom checkout page template, eventually defaults to built-in template
   *
   * @since 1.0
   *
   */
  static function shortcode_wpi_checkout( $atts = "",  $content = null, $code = "") {
    global $wpi_settings, $wpi_checkout, $current_user;
    
    $result = '';

    //** STEP 1. Init general atts. */

    //** Loaded needed scripts - will not work on pre WP 3.3 */
    wp_enqueue_script( 'jquery.number.format' );
    wp_enqueue_script( 'wpi.checkout' );

    //** Set the current user, if the current user is not set */
    wp_get_current_user();

    // Load defaults
    $atts = wp_parse_args( $atts, array(
      'terms_page_id'     => '',
      'terms_text'        => __( 'I accept the <a href="%s" target="_blank">terms and conditions</a>.', ud_get_wp_invoice_spc()->domain ),
      'return'            => 'true',
      'uncheck_items'     => 'false',
      'hidden_attributes' => '',
      'template'          => '',
      'user_email'        => $current_user->get('user_email')
    ) );

    /** Custom fields */
    if ( !empty( $atts['customer_information'] ) ) {
      // Create array from passed customer_information
      if(strpos($atts['customer_information'],',')) {
        $customer_information_names = explode(',' ,$atts['customer_information']);
      } else {
        $customer_information_names = array($atts['customer_information']);
      }
      // Add custom fields to block
      if ( !empty( $customer_information_names ) ) {
        foreach( $customer_information_names as $field_title ) {
          $new_slug = str_replace('-', '_', sanitize_title( $field_title ));
          if ( empty( $wpi_checkout['info_block']['customer_information'][$new_slug] ) ) {
            $wpi_checkout['info_block']['customer_information']['customer_information_'.$new_slug] = array(
              'label'  => __($field_title, ud_get_wp_invoice_spc()->domain),
              'type'   => 'input'
            );
          }
        }
      }
    }

    /** Detect inline fee */
    if ( !empty( $atts['fee'] ) ) {
      add_filter( 'wpi_spc_total_filter', array( 'wpi_spc', 'process_inline_fee' ), 10, 2 );
    }

    if ( !empty( $atts['custom_amount'] ) && empty( $atts['item'] ) ) {
      add_filter( 'spc_custom_billing_information', array( 'wpi_spc', 'add_custom_amount_field' ) );
    }

    if ( !empty( $atts['user_email'] ) ) {
      add_filter('wpi_spc::input_value', array( 'wpi_spc', 'set_fixed_user_email' ), 10, 3);
      add_filter('wpi_spc::input_attributes', array( 'wpi_spc', 'set_user_email_readonly' ), 10, 2);
    }

    if(is_numeric($atts['terms_page_id']) && $terms_url = get_permalink($atts['terms_page_id'])) {
      $atts['terms'] = sprintf($atts['terms_text'], $terms_url);
    }

    // Create array from passed items
    if( !empty($atts['item']) && strpos($atts['item'],',') ) {
      $item_names = explode(',' ,$atts['item']);
    } elseif ( !empty($atts['item']) ) {
      $item_names = array($atts['item']);
    } else {
      $item_names = array();
    }

    // Create item list
    foreach((array)$wpi_settings['predefined_services'] as $count => $item) {
      if( in_array($item['name'], $item_names) && !empty($item['name']) ) {
        $item_found = true;
        $atts['items'][$count] = $item;
      }
    }

    //** STEP 2. Set template's variables. */

    $atts[ 'env_vars' ] = array(
      'id' => 'wpi_spc_' . md5( rand( 100,9999999 ) ),
      'template' => $atts['template'],
    );

    /** Check available gateways */
    $atts[ 'env_vars' ][ 'available_gateways' ] = array();
    $atts[ 'env_vars' ][ 'system_gateways' ]    = self::available_gateways();
    
    $gateways = array();

    $atts[ 'env_vars' ][ 'gateways' ] = array();
    if ( !empty( $atts['gateways'] ) ) {
      $gateways = explode(',', $atts['gateways']);
    }

    if( empty( $gateways ) ) {
      foreach( $atts[ 'env_vars' ][ 'system_gateways' ] as $key => $gateway ) {
        $gateways[] = $key;
      }
    }

    foreach( $gateways as $gateway ) {
      if ( array_key_exists( $gateway, $atts[ 'env_vars' ][ 'system_gateways' ] ) && is_array( $atts[ 'env_vars' ][ 'system_gateways' ][$gateway] ) ) {
        $atts[ 'env_vars' ][ 'available_gateways' ][$gateway] = $atts[ 'env_vars' ][ 'system_gateways' ][$gateway];
        $atts[ 'env_vars' ][ 'available_gateways' ][$gateway][ 'data' ] = apply_filters( "wpi::checkout::{$gateway}::script", array(
          'time' => time(),
          'ajaxurl' => admin_url('admin-ajax.php'),
          'strings' => array(
            'processing' => __( 'Processing...', ud_get_wp_invoice_spc()->domain ),
            'process_payment' => __( 'Process Payment', ud_get_wp_invoice_spc()->domain ),
            'hacking' => __( 'Hacking?', ud_get_wp_invoice_spc()->domain ),
            'redirecting_to_paypal' => __( 'Redirecting to PayPal. Wait please...', ud_get_wp_invoice_spc()->domain ),
          )
        ) );
      }
    }

    $atts[ 'env_vars' ][ 'total' ] = array( 0 );
    if ( !empty( $atts['items'] ) ) {
      foreach( $atts['items'] as $item ) {
        $item['tax'] = isset( $item['tax'] ) ? $item['tax'] : 0;
        $atts[ 'env_vars' ][ 'total' ][] = number_format( (float)($item['price']*$item['quantity'] + ($item['price']*$item['quantity']/100*$item['tax'])), 2, '.', '');
      }
    }

    $atts[ 'env_vars' ][ 'total_before_filters' ] = array_sum( $atts[ 'env_vars' ][ 'total' ] );
    $atts[ 'env_vars' ][ 'total' ] = apply_filters( 'wpi_spc_total_filter', array_sum( $atts[ 'env_vars' ][ 'total' ] ), $atts );

    $atts[ 'env_vars' ][ 'ga_event_tracking' ] = isset( $wpi_settings['ga_event_tracking'] ) ? $wpi_settings['ga_event_tracking'] : '';

    $wpi_checkout['info_block']['billing_information'] = apply_filters('spc_custom_billing_information', $wpi_checkout['info_block']['billing_information']);

    //** STEP 3. Render template(s) */

    $template_found = UsabilityDynamics\Utility::get_template_part( array(
      "wpi_checkout-{$atts['template']}",
      "wpi_checkout"
    ), apply_filters( 'wpi::spc::template', array( ud_get_wp_invoice_spc()->path('static/views', 'dir') ) ) );

    if( !$template_found ) {
      return false;
    }

    extract( $atts[ 'env_vars' ] );

    //** Enqueue gateways scripts */
    if( !empty( $available_gateways ) && is_array( $available_gateways ) ) {
      foreach( $available_gateways as $k => $d ) {
        wp_enqueue_script( "wpi.checkout.{$k}", apply_filters( 'wpi::spc::gateway_script_url', ud_get_wp_invoice()->path('lib/gateways', 'url') . "/js/{$k}_checkout.js", $k ), array( 'wpi.checkout' ) );
      }
    }

    //** Preare params for using them in json object */
    $jopts = $atts[ 'env_vars' ];
    array_walk_recursive( $jopts, create_function('&$value', '$value = addslashes($value);') );
    $jopts = json_encode( $jopts );

    ob_start();

    include $template_found;

    ?>
    <script type="text/javascript">
      jQuery( document ).ready( function() { wpi_spc( '<?php echo $jopts; ?>' ); } );
    </script>
    <?php

    $result .= ob_get_contents();
    ob_end_clean();

    return "<div id=\"{$atts[ 'env_vars' ][ 'id' ]}\" class=\"wpi_checkout\">{$result}</div>";
  } /* end: shortcode_wpi_checkout(); */


  /**
   * Filter for adding amount field
   *
   * @param array $current
   * @author korotkov@ud
   * @return array
   */
  static function add_custom_amount_field( $current ) {
    $current['amount'] = array(
      'label' => __('Amount', ud_get_wp_invoice_spc()->domain),
      'type' => 'input'
    );
    return $current;
  }

  /**
   * Handles inline fee attr
   *
   * @param float $total
   * @param array $atts
   * @return float
   *
   * @author korotkov@ud
   */
  static function process_inline_fee( $total, $atts ) {
    $fee = !empty($atts['fee']) ? (int)$atts['fee'] : false;

    // if fee is number value i.e. not percents
    if ( is_numeric( $fee ) ) {
      $total += (float)number_format( (float)($total/100*$fee), 2, '.', '');
    }

    return $total;
  }

  /**
   * Generate secutity hash to prevent inline fee hack
   *
   * @param number $fee
   * @param number $total
   * @return md5 hash
   */
  static function generate_security_hash( $fee, $total ) {
    return md5( ((int)$fee).'hash'.$total );
  }


  /**
   * Add object type "Single Payment"
   * Used by WPI_Object_List_Table class
   *
   * @param $types. Array of object types
   * @return array
   * @since 1.0
   * @author Maxim Peshkov
   */
  static function wpi_object_types($types) {
    $types['single_payment'] = array('label' => __('Single Payment', ud_get_wp_invoice_spc()->domain));
    return $types;
  }

  /**
   * Content to display inside the Checkout tab on the Settings page
   *
   * @since 1.0
   *
   */
  static function settings_page_content($wpi_settings) {
    ?>
      <tr>
        <th><?php _e('Single Page Checkout', ud_get_wp_invoice_spc()->domain);?></th>
        <td>
          <ul>
            <li>
              <?php echo WPI_UI::checkbox("&name=wpi_settings[spc_checkout][enforce_ssl]&value=true&label=" . __('Enforce SSL on checkout pages.', ud_get_wp_invoice_spc()->domain),$wpi_settings['spc_checkout']['enforce_ssl'])?>
              <div class="description"><?php _e('As for regular invoice pages, HTTPS should be enforced on pages that have a checkout form.', ud_get_wp_invoice_spc()->domain);?></div>
            </li>
          </ul>
        </td>
      </tr>
    <?php
  } /* end: settings_page_tab_content() */

  /**
   * Draw hidden attributes
   * @param type $attrs
   */
  static function hidden_attributes( $attrs ) {
    if ( !empty( $attrs['hidden_attributes'] ) ) {
      $pairs = explode(',', $attrs['hidden_attributes']);
      if ( is_array( $pairs ) ) {
        foreach ( $pairs as $pair ) {
          $value_pair = explode('=', $pair);
          if ( is_array( $value_pair ) ): ?>
            <input type="hidden" name="wpi_checkout[hidden][<?php echo $value_pair[0] ?>]" value="<?php echo $value_pair[1] ?>" />
          <?php endif;
        }
      }
    }
  }

  /**
   * Do something after invoice has been saved
   * @param type $post_id
   */
  static function after_save_action( $post_id ) {
    $data = array();
    parse_str($_REQUEST['data'], $data);

    //** Save hidden attributes */
    if ( !empty( $data['wpi_checkout']['hidden'] ) && is_array( $data['wpi_checkout']['hidden'] ) ) {

      //** Add meta data to invoice meta just in case */
      foreach( $data['wpi_checkout']['hidden'] as $hidden_key => $hidden_value ) {
        update_post_meta( $post_id, $hidden_key, $hidden_value );
      }

      //**  */
      global $wp_properties;

      $property_id = !empty( $data['wpi_checkout']['hidden']['wpp::feps::property_id'] ) ? $data['wpi_checkout']['hidden']['wpp::feps::property_id'] : false;
      $form_id     = get_post_meta( $property_id, 'wpp::feps::form_id', 1 );
      $subscription_plan_data = $wp_properties['configuration']['feature_settings']['feps']['forms'][$form_id]['subscription_plans'][$data['wpi_checkout']['hidden']['wpp::feps::subscription_plan']];

      update_post_meta( $property_id, 'wpp::feps::subscription_plan', $subscription_plan_data );
    }
  }

  /**
   * Process user_email shortcode attribute
   *
   * @param type $current
   * @param type $slug
   * @param type $attrs
   * @return type
   */
  static function set_fixed_user_email( $current, $slug, $attrs ) {
    if ( $slug == 'user_email' ) {
      return $attrs[$slug];
    }
    return $current;
  }

  /**
   * Set fix e-mail read only
   *
   * @param string $input_attributes
   * @param type $slug
   * @return string
   */
  static function set_user_email_readonly( $input_attributes, $slug ) {
    if ( $slug == 'user_email' ) {
      $input_attributes[] = 'readonly="readonly"';
      return $input_attributes;
    }
    return $input_attributes;
  }

} /* end class: wpi_spc */
