<?php
/**
Name: PayPal
Class: wpi_paypal
Internal Slug: wpi_paypal
JS Slug: wpi_paypal
Version: 1.0
Description: Provides the PayPal for payment options
*/

class wpi_paypal extends wpi_gateway_base {


    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        /**
        * Payment settings
        *
        * @var array
        */
        $this->options = array(
         'name' => 'PayPal',
         'allow' => '',
         'default_option' => '',
         'settings' => array(
           'paypal_address' => array(
             'label' => __( "PayPal Username", WPI ),
             'value' => ''
           ),
           'test_mode' => array(
             'label' => __( "Use in Test Mode", WPI ),
             'description' => __( "Use PayPal SandBox for test mode", WPI ),
             'type' => 'select',
             'value' => 'https://www.paypal.com/cgi-bin/webscr',
             'data' => array(
               'https://www.paypal.com/cgi-bin/webscr' => __( "No", WPI ),
               'https://www.sandbox.paypal.com/cgi-bin/webscr' => __( "Yes", WPI )
             )
           ),
           'ipn' => array(
             'label' => __( "PayPal IPN URL", WPI ),
             'type' => "readonly",
             'description' => __( "Once IPN is integrated, sellers can automate their back office so they don’t have to wait for payments to come in to trigger order fulfillment. Setup this URL into your PayPal Merchant Account Settings.", WPI )
           ),
           'send_notify_url' => array(
                'label' => __( "Send IPN URL with payment request?", WPI ),
                'description' => __( 'Use this option if you did not set IPN in your PayPal account.', WPI ),
                'type' => "select",
                'value' => '',
                'data' => array(
                    "1" => __( "Yes", WPI ),
                    "0" => __( "No", WPI )
                )
            )
         )
        );

        /**
        * Fields list for frontend
        */
        $this->front_end_fields = array(

         'customer_information' => array(

           'first_name'  => array(
             'type'  => 'text',
             'class' => 'text-input',
             'name'  => 'first_name',
             'label' => __( 'First Name', WPI )
           ),

           'last_name'   => array(
             'type'  => 'text',
             'class' => 'text-input',
             'name'  => 'last_name',
             'label' => __( 'Last Name', WPI )
           ),

           'user_email'  => array(
             'type'  => 'text',
             'class' => 'text-input',
             'name'  => 'email_address',
             'label' => __( 'Email Address', WPI )
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
             'label' => __( 'Address', WPI )
           ),

           'city'        => array(
             'type'  => 'text',
             'class' => 'text-input',
             'name'  => 'city',
             'label' => __( 'City', WPI )
           ),

           'state'       => array(
             'type'   => 'text',
             'class'  => 'text-input',
             'name'   => 'state',
             'label'  => __( 'State/Province', WPI )
           ),

           'zip'         => array(
             'type'  => 'text',
             'class' => 'text-input',
             'name'  => 'zip',
             'label' => __( 'Zip/Postal Code', WPI )
           ),

           'country'     => array(
             'type'   => 'text',
             'class'  => 'text-input',
             'name'   => 'country',
             'label'  => __( 'Country', WPI )
           )

         )

        );

        $this->options['settings']['ipn']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_paypal');

    }

    /**
     *
     * @param type $this_invoice
     */
    function recurring_settings( $this_invoice ) {
      ?>
      <h4><?php _e( 'PayPal Subscriptions', WPI ); ?></h4>
      <table class="wpi_recurring_bill_settings">
        <tr>
          <th><?php _e( 'Bill Every', WPI ); ?></th>
          <td>
            <?php echo WPI_UI::input("name=wpi_invoice[recurring][".$this->type."][length]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['length'] : '') . "&class=wpi_small wpi_bill_every_length"); ?>
            <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][unit]&values=" . serialize(array( "days" => __("Day(s)", WPI), "weeks" => __("Week(s)", WPI), "months" => __("Month(s)", WPI), "years" => __("Year(s)", WPI) )) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['unit'] : '')); ?>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Billing Cycles', WPI ); ?></th>
          <td><?php echo WPI_UI::input("id=wpi_meta_recuring_cycles&name=wpi_invoice[recurring][".$this->type."][cycles]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['cycles'] : '') . "&class=wpi_small"); ?></td>
        </tr>
      </table>
      <?php
    }

    /**
     * Overrided payment process for paypal
     *
     * @global type $invoice
     * @global type $wpi_settings
     */
    function process_payment() {
        global $invoice;

        $crm_data = $_REQUEST['crm_data'];
        $wp_users_id = $invoice['user_data']['ID'];

        //** update user data */
        update_user_meta($wp_users_id, 'last_name', $_REQUEST['last_name']);
        update_user_meta($wp_users_id, 'first_name', $_REQUEST['first_name']);
        update_user_meta($wp_users_id, 'city', $_REQUEST['city']);
        update_user_meta($wp_users_id, 'state', $_REQUEST['state']);
        update_user_meta($wp_users_id, 'zip', $_REQUEST['zip']);
        update_user_meta($wp_users_id, 'streetaddress', $_REQUEST['address1']);
        update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['night_phone_a'] . '-' . $_REQUEST['night_phone_b'] . '-' . $_REQUEST['night_phone_c']);
        update_user_meta($wp_users_id, 'country', $_REQUEST['country']);

        if (!empty($crm_data))
            $this->user_meta_updated($crm_data);

        echo json_encode(
                array('success' => 1)
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

						// If field is set of 3 fields for paypal phone number
						if ( $field_slug == 'phonenumber' ) {

							echo '<li class="wpi_checkout_row"><div class="control-group"><label class="control-label">'.__('Phone Number', WPI).'</label><div class="controls">';

							$phonenumber = !empty($invoice['user_data']['phonenumber']) ? $invoice['user_data']['phonenumber'] : "---";
							$phone_array = split('[/.-]', $phonenumber);

							foreach( $field_data as $field ) {
                //** Change field properties if we need */
                $field = apply_filters('wpi_payment_form_styles', $field, $field_slug, 'wpi_paypal');
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
            $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_paypal');

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
   * Handler for PayPal IPN queries
   * @author korotkov@ud
   * Full callback URL: http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_paypal
   */
  function server_callback(){

    if ( empty( $_POST ) ) die(__('Direct access not allowed', WPI));

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id={$_POST['invoice']}");

    /** Verify callback request */
    if ( $this->_ipn_verified( $invoice ) ) {

      switch ( $_POST['txn_type'] ) {
        /** New PayPal Subscription */
        case 'subscr_signup':
          /** PayPal Subscription created */
          WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription created', WPI));
          wp_invoice_mark_as_pending( $_POST['invoice'] );
          do_action( 'wpi_paypal_subscr_signup_ipn', $_POST );
          break;

        case 'subscr_cancel':
          /** PayPal Subscription cancelled */
          WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription cancelled', WPI));
          do_action( 'wpi_paypal_subscr_cancel_ipn', $_POST );
          break;

        case 'subscr_failed':
          /** PayPal Subscription failed */
          WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription payment failed', WPI));
          do_action( 'wpi_paypal_subscr_failed_ipn', $_POST );
          break;

        case 'subscr_payment':
          /** Payment of Subscription */
          switch ( $_POST['payment_status'] ) {
            case 'Completed':
              /** Add payment amount */
              $event_note = sprintf(__('%1s paid for subscription %2s', WPI), WPI_Functions::currency_format(abs($_POST['mc_gross']), $_POST['invoice']), $_POST['subscr_id']);
              $event_amount = (float)$_POST['mc_gross'];
              $event_type   = 'add_payment';
              /** Log balance changes */
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              $invoice->save_invoice();
              send_notification( $invoice->data );
              break;

            default:
              break;
          }
          do_action( 'wpi_paypal_subscr_payment_ipn', $_POST );
          break;

        case 'subscr_eot':
          /** PayPal Subscription end of term */
          WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription term is finished', WPI));
          wp_invoice_mark_as_paid( $_POST['invoice'], $check = false );
          do_action( 'wpi_paypal_subscr_eot_ipn', $_POST );
          break;

        case 'subscr_modify':
          /** PayPal Subscription modified */
          WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['invoice']), 'invoice', 'update', '', __('PayPal Subscription modified', WPI));
          do_action( 'wpi_paypal_subscr_modify_ipn', $_POST );
          break;

        case 'web_accept':
          /** PayPal simple button */
          switch( $_POST['payment_status'] ) {

            case 'Pending':
              /** Mark invoice as Pending */
              wp_invoice_mark_as_pending( $_POST['invoice'] );
              do_action( 'wpi_paypal_pending_ipn', $_POST );
              break;

            case 'Completed':
              /** Add payment amount */
              $event_note = sprintf(__('%s paid via PayPal', WPI), WPI_Functions::currency_format(abs($_POST['mc_gross']), $_POST['invoice']));
              $event_amount = (float)$_POST['mc_gross'];
              $event_type   = 'add_payment';
              /** Log balance changes */
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              /** Log payer email */
              $payer_email = sprintf(__("PayPal Payer email: %s", WPI), $_POST['payer_email']);
              $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
              $invoice->save_invoice();
              /** ... and mark invoice as paid */
              wp_invoice_mark_as_paid( $_POST['invoice'], $check = true );
              send_notification( $invoice->data );
              do_action( 'wpi_paypal_complete_ipn', $_POST );
              break;

            default: break;

          }
          break;

        case 'cart':
          /** PayPal Cart. Used for SPC */
          switch( $_POST['payment_status'] ) {
            case 'Pending':
              /** Mark invoice as Pending */
              wp_invoice_mark_as_pending( $_POST['invoice'] );
              do_action( 'wpi_paypal_pending_ipn', $_POST );
              break;
            case 'Completed':
              /** Add payment amount */
              $event_note = sprintf(__('%s paid via PayPal', WPI), WPI_Functions::currency_format(abs($_POST['mc_gross']), $_POST['invoice']));
              $event_amount = (float)$_POST['mc_gross'];
              $event_type   = 'add_payment';
              /** Log balance changes */
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              /** Log payer email */
              $payer_email = sprintf(__("PayPal Payer email: %s", WPI), $_POST['payer_email']);
              $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
              $invoice->save_invoice();
              /** ... and mark invoice as paid */
              wp_invoice_mark_as_paid( $_POST['invoice'], $check = true );
              send_notification( $invoice->data );
              do_action( 'wpi_paypal_complete_ipn', $_POST );
              break;

            default: break;

          }
          break;

        default:
          break;
      }

    }

  }

  /**
   * Verify IPN and returns TRUE or FALSE
   * @author korotkov@ud
   **/
  private function _ipn_verified( $invoice = false ) {

		if ( $invoice ) {
			$request = $invoice->data['billing']['wpi_paypal']['settings']['test_mode']['value'].'?cmd=_notify-validate';
		} else {
			global $wpi_settings;
			$request = $wpi_settings['billing']['wpi_paypal']['settings']['test_mode']['value'].'?cmd=_notify-validate';
		}

    foreach ( $_POST as $key => $value ) {
      $value = urlencode( stripslashes( $value ) );
      $request .= "&$key=$value";
    }

    return strstr( file_get_contents( $request ), 'VERIFIED' ) ? TRUE : FALSE;

  }

}