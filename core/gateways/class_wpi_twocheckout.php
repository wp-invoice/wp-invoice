<?php
/**
Name: 2Checkout
Class: wpi_twocheckout
Internal Slug: wpi_twocheckout
JS Slug: wpi_twocheckout
Version: 1.0
Description: Provides the 2Checkout for payment options
*/

class wpi_twocheckout extends wpi_gateway_base {

	/**
   * Input types
   */
  const TEXT_INPUT_TYPE   = 'text';
  const SELECT_INPUT_TYPE = 'select';

	/**
	 * Payment settings
	 *
	 * @var array
	 */
  var $options = array(
    'name' => '2Checkout',
    'allow' => '',
    'default_option' => '',
    'settings' => array(
      'twocheckout_sid' => array(
        'label' => "2Checkout Seller ID",
        'value' => ''
      ),
      'twocheckout_secret' => array(
        'label' => "2Checkout Secret Word",
        'value' => ''
      ),
      'test_mode' => array(
        'label' => "Demo Mode",
        'description' => "Use 2Checkout Demo Mode",
        'type' => 'select',
        'value' => 'N',
        'data' => array(
          'N' => "No",
          'Y' => "Yes"
        )
      ),
      'direct_checkout' => array(
          'label' => "Direct Checkout",
          'description' => "Use Direct Checkout",
          'type' => 'select',
          'value' => 'Y',
          'data' => array(
              'N' => "No",
              'Y' => "Yes"
          )
      ),
      'button_url' => array(
        'label' => "2Checkout Button URL",
        'value' => "https://www.2checkout.com/images/paymentlogoshorizontal.png"
      ),
      'ipn' => array(
        'label' => "2Checkout Approved URL/INS URL",
        'type' => "readonly",
        'description' => "Set this URL as your Approved URL in your 2Checkout Site Management page and Notification URL under your 2Checkout Notification page."
      )
    )
  );

	/**
   * Fields list for frontend
   */
  var $front_end_fields = array(

    'customer_information' => array(

      'first_name'  => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'first_name',
        'label' => 'First Name'
      ),

      'last_name'   => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'last_name',
        'label' => 'Last Name'
      ),

      'user_email'  => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'email_address',
        'label' => 'Email Address'
      ),

			'phonenumber' => array(
				array(
					'type'  => 'text',
					'class' => 'text-input small',
					'name'  => 'night_phone_a'
				),
				array(
					'type'  => 'text',
					'class' => 'text-input small',
					'name'  => 'night_phone_b'
				),
				array(
					'type'  => 'text',
					'class' => 'text-input small',
					'name'  => 'night_phone_c'
				)
			),

      'streetaddress'     => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'address1',
        'label' => 'Address'
      ),

      'city'        => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'city',
        'label' => 'City'
      ),

      'state'       => array(
        'type'   => 'text',
        'class'  => 'text-input',
        'name'   => 'state',
        'label'  => 'State/Province'
      ),

      'zip'         => array(
        'type'  => 'text',
        'class' => 'text-input',
        'name'  => 'zip',
        'label' => 'Zip/Postal Code'
      ),

      'country'     => array(
        'type'   => 'text',
        'class'  => 'text-input',
        'name'   => 'country',
        'label'  => 'Country'
      )

    )

  );

	/**
	 * Constructor
	 */
  function __construct() {
    parent::__construct();
    $this->options['settings']['ipn']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_twocheckout');

		add_action( 'wpi_payment_fields_twocheckout', array( $this, 'wpi_payment_fields' ) );
	}

	/**
	 * Overrided payment process for 2Checkout
	 *
	 * @global type $invoice
	 * @global type $wpi_settings
	 */
	function process_payment() {
		global $invoice, $wpi_settings;

		$crm_data    = $_REQUEST['crm_data'];
    $invoice_id  = $invoice['invoice_id'];
    $wp_users_id = $invoice['user_data']['ID'];
    $post_id     = wpi_invoice_id_to_post_id($invoice_id);

		// update user data
		update_user_meta($wp_users_id, 'last_name', $_REQUEST['last_name']);
		update_user_meta($wp_users_id, 'first_name', $_REQUEST['first_name']);
		update_user_meta($wp_users_id, 'city', $_REQUEST['city']);
		update_user_meta($wp_users_id, 'state', $_REQUEST['state']);
		update_user_meta($wp_users_id, 'zip', $_REQUEST['zip']);
		update_user_meta($wp_users_id, 'streetaddress', $_REQUEST['address1']);
		update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['night_phone_a'].'-'.$_REQUEST['night_phone_b'].'-'.$_REQUEST['night_phone_c']);
		update_user_meta($wp_users_id, 'country', $_REQUEST['country']);

		if ( !empty( $crm_data ) ) $this->user_meta_updated( $crm_data );

		echo json_encode(
		  array( 'success' => 1 )
		);

	}

	/**
   * Render fields
   *
   * @param array $invoice
   */
  function wpi_payment_fields( $invoice ) {

    $this->front_end_fields = apply_filters( 'wpi_crm_custom_fields', $this->front_end_fields, 'crm_data' );

    if ( !empty( $this->front_end_fields ) ) {
      // For each section
      foreach( $this->front_end_fields as $key => $value ) {
        // If section is not empty
        if ( !empty( $this->front_end_fields[ $key ] ) ) {
					$html = '';
					ob_start();

					?>
					<ul class="wpi_checkout_block">
						<li class="section_title"><?php _e( ucwords( str_replace('_', ' ', $key) ), WPI); ?></li>
					<?php
					$html = ob_get_contents();
					ob_end_clean();
					echo $html;
          // For each field
          foreach( $value as $field_slug => $field_data ) {

						// If field is set of 3 fields for 2Checkout phone number
						if ( $field_slug == 'phonenumber' ) {

							echo '<li class="wpi_checkout_row"><div class="control-group"><label class="control-label">'.__('Phone Number', WPI).'</label><div class="controls">';

							$phonenumber = !empty($invoice['user_data']['phonenumber']) ? $invoice['user_data']['phonenumber'] : "---";
							$phone_array = split('[/.-]', $phonenumber);

							foreach( $field_data as $field ) {
                //** Change field properties if we need */
                $field = apply_filters('wpi_payment_form_styles', $field, $field_slug, 'wpi_twocheckout');
								ob_start();
                ?>
                  <input type="<?php echo esc_attr( $field['type'] ); ?>" class="<?php echo esc_attr( $field['class'] ); ?>"  name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $phone_array[key($phone_array)] ); next($phone_array); ?>" />
                <?php
                $html = ob_get_contents();
                ob_end_clean();
								echo $html;
							}

							echo '</div></div></li>';

						}
            //** Change field properties if we need */
            $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_twocheckout');

            $html = '';
            switch ( $field_data['type'] ) {
              case self::TEXT_INPUT_TYPE:

                ob_start();

                ?>

                <li class="wpi_checkout_row">
                  <div class="control-group">
                    <label class="control-label" for="<?php echo esc_attr( $field_slug ); ?>"><?php _e($field_data['label'], WPI); ?></label>
                    <div class="controls">
                      <input type="<?php echo esc_attr( $field_data['type'] ); ?>" class="<?php echo esc_attr( $field_data['class'] ); ?>"  name="<?php echo esc_attr( $field_data['name'] ); ?>" value="<?php echo !empty($invoice['user_data'][$field_slug])?$invoice['user_data'][$field_slug]:'';?>" />
                    </div>
                  </div>
                </li>

                <?php

                $html = ob_get_contents();
                ob_end_clean();

                break;

              case self::SELECT_INPUT_TYPE:

                ob_start();

                ?>

                <li class="wpi_checkout_row">
                  <label for="<?php echo esc_attr( $field_slug ); ?>"><?php _e($field_data['label'], WPI); ?></label>
                  <?php echo WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                </li>

                <?php

                $html = ob_get_contents();
                ob_clean();

                break;

              default:
                break;
            }

            echo $html;

          }
					echo '</ul>';
        }
      }

    }

  }

  /**
   * Handler for 2Checkout Callback
   * @author Craig Christenson
   * Full callback URL: http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_twocheckout
   */
  function server_callback(){

    if ( empty( $_REQUEST ) ) die(__('Direct access not allowed', WPI));

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id={$_REQUEST['merchant_order_id']}");

    /** Verify callback request */
    if ( $this->_ipn_verified( $invoice ) ) {
      if ($_REQUEST['key']) {
        $event_note = sprintf(__('%s paid via 2Checkout', WPI), WPI_Functions::currency_format(abs($_REQUEST['total']), $_REQUEST['merchant_order_id']));
        $event_amount = (float)$_REQUEST['total'];
        $event_type   = 'add_payment';
        /** Log balance changes */
        $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
        /** Log payer email */
        $payer_email = sprintf(__("2Checkout buyer email: %s", WPI), $_REQUEST['email']);
        $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
        $invoice->save_invoice();
        /** ... and mark invoice as paid */
        wp_invoice_mark_as_paid( $_REQUEST['invoice'], $check = true );
        send_notification( $invoice->data );
        echo '<script type="text/javascript">window.location="'.$_REQUEST['x_receipt_link_url'].'";</script>';

      /** Handle INS messages */
      } elseif ($_POST['md5_hash']) {

        switch ( $_POST['message_type'] ) {

          case 'FRAUD_STATUS_CHANGED':
            if ($_POST['fraud_status'] == 'pass') {
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Passed 2Checkout fraud review.', WPI));
            } elseif (condition) {
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Failed 2Checkout fraud review.', WPI));
              wp_invoice_mark_as_pending( $_POST['vendor_order_id'] );
            }
            break;

          case 'RECURRING_STOPPED':
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring billing stopped.', WPI));
            break;

          case 'RECURRING_INSTALLMENT_FAILED':
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring installment failed.', WPI));
            break;

          case 'RECURRING_INSTALLMENT_SUCCESS':
            $event_note = sprintf(__('%1s paid for subscription %2s', WPI), WPI_Functions::currency_format(abs($_POST['item_rec_list_amount_1']), $_POST['vendor_order_id']), $_POST['sale_id']);
            $event_amount = (float)$_POST['item_rec_list_amount_1'];
            $event_type   = 'add_payment';
            $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
            $invoice->save_invoice();
            send_notification( $invoice->data );
            break;

          case 'RECURRING_COMPLETE':
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring installments completed.', WPI));
            wp_invoice_mark_as_paid( $_POST['invoice'], $check = false );
            break;

          case 'RECURRING_RESTARTED':
            WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring sale restarted.', WPI));
            break;

          default:
            break;
        }
      }
    }
  }

//   /**
//    * Verify return/notification and return TRUE or FALSE
//    * @author Craig Christenson
//    **/
  private function _ipn_verified( $invoice = false ) {

  	if ($_REQUEST['key']) {
      if ($this->options['settings']['test_mode']['value'] == 'Y') {
        $transaction_id = 1;
      } else {
        $transaction_id = $_REQUEST['order_number'];
      }
      $compare_string = $this->options['settings']['twocheckout_secret']['value'] .
      $this->options['settings']['twocheckout_sid']['value']. $transaction_id .
      $_REQUEST['total'];
      $compare_hash1 = strtoupper(md5($compare_string));
      $compare_hash2 = $_REQUEST['key'];
      if ($compare_hash1 != $compare_hash2) {
          die("MD5 HASH Mismatch! Make sure your demo settings are correct.");
      } else {
          return TRUE;
      }
    } elseif ($_POST['md5_hash']) {
      $compare_string = $_POST['sale_id'] . $this->options['settings']['twocheckout_sid']['value'] . 
      $_POST['invoice_id'] . $this->options['settings']['twocheckout_secret']['value'];
      $compare_hash1 = strtoupper(md5($compare_string));
      $compare_hash2 = $_POST['md5_hash'];
      if ($compare_hash1 != $compare_hash2) {
          die("MD5 HASH Mismatch!");
      } else {
          return TRUE;
      }
    }
  }

}

