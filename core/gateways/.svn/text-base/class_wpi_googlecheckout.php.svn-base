<?php
/**
Name: Google Checkout
Class: wpi_googlecheckout
Internal Slug: wpi_googlecheckout
JS Slug: wpi_googlecheckout
Version: 1.0
Description: Provides the Google Checkout for payment options
*/

class wpi_googlecheckout extends wpi_gateway_base {

  /**
   * Input types
   */
  const TEXT_INPUT_TYPE   = 'text';
  const SELECT_INPUT_TYPE = 'select';

  /**
   * Properties of class
   * @var array
   */
  var $options = array();
  var $front_end_fields = array();

	/**
	 * Constructor
	 */
  function __construct() {
    parent::__construct();

    //** Options if current payment venue */
    $this->options = array(
      'name' => __('Google Checkout', WPI),
      'allow' => '',
      'default_option' => '',
      'settings' => array(
        'merchant_id' => array(
          'label' => __('Merchant ID', WPI),
          'value' => ''
        ),
        'test_mode' => array(
          'label' => __('Use in Test Mode', WPI),
          'description' => __('Use Google SandBox for test mode', WPI),
          'type' => 'select',
          'value' => 'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/',
          'data' => array(
            'https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/' => __( 'No', WPI ),
            'https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/' => __( 'Yes', WPI )
          )
        ),
        'ipn' => array(
          'label' => __( 'API callback URL', WPI ),
          'type' => "readonly",
          'description' => __( 'Specify a URL for Google to notify you of new orders and changes in order state.', WPI ),
          'value' => admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_googlecheckout')
        )
      )
    );

    //** Fields for front-end. */
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

        'phonenumber'  => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'phonenumber',
          'label' => __( 'Phone', WPI )
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

		add_action( 'wpi_payment_fields_googlecheckout', array( $this, 'wpi_payment_fields' ) );
    add_action( 'wpi_recurring_after_bill_every',    array( $this, 'billing_periods' ) );
    add_action( 'wpi_recurring_after_date',          array( $this, 'no_charge_after' ) );
    add_filter( 'wpi_create_schedule_recurring',     array( $this, 'create_schedule_recurring' ) );
	}

  /**
   * Process saving of Google Checkout specific options
   *
   * @param array $recurring
   * @return type
   */
  function create_schedule_recurring( $recurring ) {
    $recurring['google_billing_period'] = !empty( $_REQUEST['wpi_invoice']['recurring']['google_billing_period'] ) ? $_REQUEST['wpi_invoice']['recurring']['google_billing_period'] : 'DAILY';
    $recurring['google_no_charge_after'] = !empty( $_REQUEST['wpi_invoice']['recurring']['google_no_charge_after'] ) ? $_REQUEST['wpi_invoice']['recurring']['google_no_charge_after'] : array();
    return $recurring;
  }

  /**
   * Render No Charge After options.
   *
   * @param type $this_invoice
   */
  function no_charge_after( $this_invoice ) {
    ?>
    <tr>
      <th style="cursor:help;font-weight:bold;" title="<?php _e('This option specifies the latest date that you can charge the customer for the subscription. This element can help you to ensure that you do not overcharge your customers.', WPI); ?>"><?php _e('No Charge After', WPI); ?>:</th>
      <td>
        <div>
          <?php echo WPI_UI::select("id=r_no_charge_after_mm&name=wpi_invoice[recurring][google_no_charge_after][month]&values=months&current_value=" . (!empty($this_invoice['recurring']) ? $this_invoice['recurring']['google_no_charge_after']['month'] : '')); ?>
          <?php echo WPI_UI::input("id=r_no_charge_after_jj&name=wpi_invoice[recurring][google_no_charge_after][day]&value=" . (!empty($this_invoice['recurring']) ? $this_invoice['recurring']['google_no_charge_after']['day'] : '') . "&special=size='2' maxlength='2' autocomplete='off'") ?>
          <?php echo WPI_UI::input("id=r_no_charge_after_aa&name=wpi_invoice[recurring][google_no_charge_after][year]&value=" . (!empty($this_invoice['recurring']) ? $this_invoice['recurring']['google_no_charge_after']['year'] : '') . "&special=size='2' maxlength='4' autocomplete='off'") ?>
        </div>
        <small><?php _e('Applicable only for Google Checkout', WPI); ?></small>
      </td>
    </tr>
    <?php
  }

  /**
   * Render Google Checkout specific options
   *
   * @param array $invoice
   */
  function billing_periods( $invoice ) {
    ?>
      <tr>
        <th style="cursor:help;font-weight:bold;" title="<?php _e('If you use Google Checkout for subscriptions then these options will be used to determine billing period.', WPI); ?>"><?php _e('Google Checkout Billing Period', WPI); ?></th>
        <td>
           <?php echo WPI_UI::select("name=wpi_invoice[recurring][google_billing_period]&values=" . serialize(apply_filters('wpi_google_billing_period', array( "DAILY" => __("Daily", WPI), "WEEKLY" => __("Weekly", WPI), "SEMI_MONTHLY" => __("Semi Monthly", WPI), "MONTHLY" => __("Monthly", WPI), "EVERY_TWO_MONTHS" => __("Every Two Months", WPI), "QUARTERLY" => __("Quarterly", WPI), "YEARLY" => __("Yearly", WPI)))) . "&current_value=" . (!empty($invoice['recurring']) ? $invoice['recurring']['google_billing_period'] : '')); ?>
        </td>
      </tr>
    <?php
  }

  /**
   * Render fields
   *
   * @param type $invoice
   */
  function wpi_payment_fields( $invoice ) {

    $this->front_end_fields = apply_filters( 'wpi_crm_custom_fields', $this->front_end_fields, 'cc_data' );

    if ( !empty( $this->front_end_fields ) ) {
      //** For each section */
      foreach( $this->front_end_fields as $key => $value ) {
        //** If section is not empty */
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
          //** For each field */
          foreach( $value as $field_slug => $field_data ) {
            //** Change field properties if we need */
            $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_googlecheckout');
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
                <?
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
   * Process payment
   *
   * @global array $invoice
   * @global array $wpi_settings
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
		update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['phonenumber']);
		update_user_meta($wp_users_id, 'country', $_REQUEST['country']);

		if ( !empty( $crm_data ) ) $this->user_meta_updated( $crm_data );

		echo json_encode(
		  array( 'success' => 1 )
		);

	}

  /**
   * IPN handler for Google Checkout
   */
  function server_callback(){

    if ( empty( $_POST ) ) die(__('Direct access not allowed', WPI));

    $invoice_id          = $_POST['order-summary_shopping-cart_buyer-messages_special-instructions-1'];
    $google_order_number = $_POST['google-order-number'];

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id={$invoice_id}");

    //** Process different types */
    switch( $_POST['_type'] ) {

      //** New order */
      case 'new-order-notification':

        //** Process different statuses */
        switch ( $_POST['financial-order-state'] ) {

          case 'REVIEWING':
            wp_invoice_mark_as_pending( $invoice_id );
            update_post_meta( wpi_invoice_id_to_post_id($invoice_id) , 'google-order-number', $google_order_number );
            do_action( 'wpi_googlecheckout_reviewing_ipn', $_POST );
            break;

          default: break;
        }

        break;

      //** Change state */
      case 'charge-amount-notification':
          /** Add payment amount */
          $event_note = sprintf(__('%s paid via Google Checkout', WPI), WPI_Functions::currency_format(abs($_POST['total-charge-amount']), $invoice_id));
          $event_amount = (float)$_POST['total-charge-amount'];
          $event_type   = 'add_payment';
          /** Log balance changes */
          $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
          /** Log payer email */
          $payer_data = sprintf(__("Google Buyer ID: %s", WPI), $_POST['order-summary_buyer-id']);
          $invoice->add_entry("attribute=invoice&note=$payer_data&type=update");
          $invoice->save_invoice();
          /** ... and mark invoice as paid */
          wp_invoice_mark_as_paid( $invoice_id, $check = true );
          send_notification( $invoice->data );
          do_action( 'wpi_googlecheckout_charged_ipn', $_POST );
        break;

      default: break;
    }

    die( '<notification-acknowledgment xmlns="http://checkout.google.com/schema/2" serial-number="'.$_POST['serial-number'].'"/>' );
  }

}