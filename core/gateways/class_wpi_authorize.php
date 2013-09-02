<?php

/**
  Name: MerchantPlus.com and other Authorize.net Gateways
  Class: wpi_authorize
  Internal Slug: wpi_authorize
  JS Slug: wpi_authorize
  Version: 1.0
  Description: Uses Authorize.net compatible gateways to accept payments
 */
class wpi_authorize extends wpi_gateway_base {

  /**
   * Construct
   */
  function __construct() {
    parent::__construct();

    /**
     * Opations array for settings page
     */
    $this->options = array(
        'name' => 'Credit Card',
        'public_name' => 'Credit Card',
        'allow' => true,
        'default_option' => '',
        'settings' => array(
            'gateway_username' => array(
                'label' => __( "Gateway Username", WPI ),
                'value' => '',
                'description' => __( "Your credit card processor will provide you with a gateway username.", WPI )
            ),
            'gateway_tran_key' => array(
                'label' => __( "Gateway Transaction Key", WPI ),
                'value' => "",
                'description' => __( "You will be able to generate this in your credit card processor's control panel.", WPI )
            ),
            'gateway_url' => array(
                'label' => __( "Gateway URL", WPI ),
                'value' => "",
                'description' => __( "This is the URL provided to you by your credit card processing company.", WPI ),
                'special' => array(
                    'MerchantPlus' => 'https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi',
                    'Authorize.Net' => 'https://secure.authorize.net/gateway/transact.dll',
                    'Authorize.Net Developer' => 'https://test.authorize.net/gateway/transact.dll',
                )
            ),
            'recurring_gateway_url' => array(
                'label' => __( "Recurring Billing Gateway URL", WPI ),
                'value' => "",
                'description' => __( "Recurring billing gateway URL is most likely different from the Gateway URL, and will almost always be with Authorize.net. Be advised - test credit card numbers will be declined even when in test mode.", WPI ),
                'special' => array(
                    'Authorize.net ARB' => 'https://api.authorize.net/xml/v1/request.api',
                    'Authorize.Net ARB Testing' => 'https://apitest.authorize.net/xml/v1/request.api'
                )
            ),
            'gateway_test_mode' => array(
                'label' => __( "Test / Live Mode", WPI ),
                'type' => "select",
                'data' => array(
                    "TRUE" => __( "Test - Do Not Process Transactions", WPI ),
                    "FALSE" => __( "Live - Process Transactions", WPI )
                )
            ),
            'gateway_delim_char' => array(
                'label' => __( "Delimiter Character", WPI ),
                'value' => ",",
                'description' => __( "Get this from your credit card processor. If the transactions are not going through, this character is most likely wrong.", WPI )
            ),
            'gateway_encap_char' => array(
                'label' => __( "Encapsulation Character", WPI ),
                'value' => "",
                'description' => __( "Authorize.net default is blank. Otherwise, get this from your credit card processor. If the transactions are going through, but getting strange responses, this character is most likely wrong.", WPI )
            ),
            'gateway_email_customer' => array(
                'label' => __( "Email Customer (on success)", WPI ),
                'type' => "select",
                'value' => '',
                'data' => array(
                    "TRUE" => __( "Yes", WPI ),
                    "FALSE" => __( "No", WPI )
                )
            ),
            'gateway_merchant_email' => array(
                'label' => __( "Merchant Email", WPI ),
                'value' => "",
                'description' => __( "Email address to which the merchant’s copy of the customer confirmation email should be sent. If a value is submitted, an email will be sent to this address as well as the address(es) configured in the Merchant Interface.", WPI )
            ),
            'gateway_header_email_receipt' => array(
                'label' => __( "Customer Receipt Email Header", WPI ),
                'value' => ""
            ),
            'gateway_MD5Hash' => array(
                'label' => __( "Security: MD5 Hash", WPI ),
                'value' => ""
            ),
            'gateway_delim_data' => array(
                'label' => __( "Delim Data", WPI ),
                'type' => "select",
                'value' => '',
                'data' => array(
                    "TRUE" => __( "Yes", WPI ),
                    "FALSE" => __( "No", WPI )
                )
            ),
            'silent_post_url' => array(
                'label' => __( "Silent Post URL", WPI ),
                'type' => "readonly",
                'value' => "",
                'description' => __( "Silent Post responses are returned in real-time, meaning as soon as the transaction processes the Silent Post is sent to your specified URL. Go to https://account.authorize.net -> Settings -> Silent Post URL and copy this URL to input field. Required only for Recurring Billing and not for all Merchants.", WPI )
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
          'name'  => 'cc_data[first_name]',
          'label' => __( 'First Name', WPI )
        ),

        'last_name'   => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'cc_data[last_name]',
          'label' => __( 'Last Name', WPI )
        ),

        'user_email'  => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'cc_data[user_email]',
          'label' => __( 'User Email', WPI )
        ),

        'phonenumber' => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'cc_data[phonenumber]',
          'label' => __( 'Phone Number', WPI )
        ),

        'streetaddress'     => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'cc_data[streetaddress]',
          'label' => __( 'Address', WPI )
        ),

        'city'        => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'cc_data[city]',
          'label' => __( 'City', WPI )
        ),

        'state'       => array(
          'type'   => 'text',
          'class'  => 'text-input',
          'name'   => 'cc_data[state]',
          'label'  => __( 'State', WPI )
        ),

        'zip'         => array(
          'type'  => 'text',
          'class' => 'text-input',
          'name'  => 'cc_data[zip]',
          'label' => __( 'Zip', WPI )
        ),

        'country'     => array(
          'type'   => 'text',
          'class'  => 'text-input',
          'name'   => 'cc_data[country]',
          'label'  => __( 'Country', WPI )
        )

      ),

      'billing_information' => array(

        'card_num'    => array(
          'type'   => 'text',
          'class'  => 'credit_card_number input_field text-input',
          'name'   => 'cc_data[card_num]',
          'label'  => __( 'Card Number', WPI )
        ),

        'exp_month'   => array(
          'type'   => 'text',
          'class'  => 'text-input exp_month',
          'name'   => 'cc_data[exp_month]',
          'label'  => __( 'Expiration Month', WPI )
        ),

        'exp_year'    => array(
          'type'   => 'text',
          'class'  => 'text-input exp_year',
          'name'   => 'cc_data[exp_year]',
          'label'  => __( 'Expiration Year', WPI )
        ),

        'card_code'   => array(
          'type'   => 'text',
          'class'  => 'text-input',
          'name'   => 'cc_data[card_code]',
          'label'  => __( 'Card Code', WPI )
        )

      )

    );

    $this->options['settings']['silent_post_url']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_authorize');

    add_action( 'wpi_authorize_user_meta_updated', array( $this, 'user_meta_updated' ) );
  }

  /**
   *
   * @param type $this_invoice
   */
  function recurring_settings( $this_invoice ) {
    ?>
    <h4><?php _e( 'Authorize.net ARB', WPI ); ?></h4>
    <table class="wpi_recurring_bill_settings">
      <tr>
        <th><?php _e( 'Bill Every', WPI ); ?></th>
        <td>
          <?php echo WPI_UI::input("name=wpi_invoice[recurring][".$this->type."][length]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['length'] : '') . "&class=wpi_small wpi_bill_every_length"); ?>
          <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][unit]&values=" . serialize(array( "days" => __("Day(s)", WPI), "months" => __("Month(s)", WPI) )) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['unit'] : '')); ?>
        </td>
      </tr>
      <tr>
        <th><?php _e( 'Billing Cycles', WPI ); ?></th>
        <td><?php echo WPI_UI::input("id=wpi_meta_recuring_cycles&name=wpi_invoice[recurring][".$this->type."][cycles]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['cycles'] : '') . "&class=wpi_small"); ?></td>
      </tr>
      <tr>
        <th><?php _e( 'Send Invoice', WPI ); ?></th>
        <td>
          <script type="text/javascript">
            var recurring_send_invoice_automatically_<?php echo $this->type; ?> = '<?php echo !empty($this_invoice['recurring'][$this->type]['send_invoice_automatically']) ? $this_invoice['recurring'][$this->type]['send_invoice_automatically'] : 'on'; ?>';
            jQuery( document ).bind('wpi_enable_recurring', function(){
              if ( recurring_send_invoice_automatically_<?php echo $this->type; ?> == 'on' ) {
                wpi_disable_recurring_start_date( '<?php echo $this->type; ?>' );
              } else {
                wpi_enable_recurring_start_date( '<?php echo $this->type; ?>' );
              }
            });
          </script>
          <?php echo WPI_UI::checkbox("special=data-type='{$this->type}'&id=wpi_wpi_invoice_recurring_send_invoice_automatically_{$this->type}&class=wpi_wpi_invoice_recurring_send_invoice_automatically {$this->type}&name=wpi_invoice[recurring][".$this->type."][send_invoice_automatically]&value=true&label=".__('Automatically.', WPI), !empty($this_invoice['recurring'][$this->type]['send_invoice_automatically']) ? $this_invoice['recurring'][$this->type]['send_invoice_automatically'] : 'on'); ?>
        </td>
      </tr>
      <tr class="wpi_recurring_start_date <?php echo $this->type; ?>" style="display:<?php echo !empty($this_invoice['recurring'][$this->type]) && $this_invoice['recurring'][$this->type]['send_invoice_automatically'] == 'on' ? 'none;' : ''; ?>">
        <th><?php _e( 'Date', WPI ); ?></th>
        <td>
          <div>
            <?php echo WPI_UI::select("id=r_start_date_mm&name=wpi_invoice[recurring][".$this->type."][start_date][month]&values=months&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['start_date']['month'] : '')); ?>
            <?php echo WPI_UI::input("id=r_start_date_jj&name=wpi_invoice[recurring][".$this->type."][start_date][day]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['start_date']['day'] : '') . "&special=size='2' maxlength='2' autocomplete='off'") ?>
            <?php echo WPI_UI::input("id=r_start_date_aa&name=wpi_invoice[recurring][".$this->type."][start_date][year]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['start_date']['year'] : '') . "&special=size='2' maxlength='4' autocomplete='off'") ?><br />
            <span onclick="wp_invoice_add_time('r_start_date', 7);" class="wp_invoice_click_me"><?php _e('In One Week', WPI); ?></span> | <span onclick="wp_invoice_add_time('r_start_date', 30);" class="wp_invoice_click_me"><?php _e('In 30 Days', WPI); ?></span> | <span onclick="wp_invoice_add_time('r_start_date', 'clear');" class="wp_invoice_click_me"><?php _e('Clear', WPI); ?></span>
          </div>
        </td>
      </tr>
    </table>
    <?php
  }

  /**
   * Render fields
   *
   * @param array $invoice
   */
  function wpi_payment_fields( $invoice ) {

    $this->front_end_fields = apply_filters( 'wpi_crm_custom_fields', $this->front_end_fields, 'cc_data' );

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
					$html = ob_get_clean();
					echo $html;
          // For each field
          foreach( $value as $field_slug => $field_data ) {
            //** Change field properties if we need */
            $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_authorize');
            $html = '';

            ob_start();

            switch ( $field_data['type'] ) {
              case self::TEXT_INPUT_TYPE:

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

                $html = ob_get_clean();

                break;

              case self::SELECT_INPUT_TYPE:

                ?>

                <li class="wpi_checkout_row">
                  <label for="<?php echo esc_attr( $field_slug ); ?>"><?php _e($field_data['label'], WPI); ?></label>
                  <?php echo WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                </li>

                <?php

                $html = ob_get_clean();

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
    * Overrided process payment for Authorize.net
    *
    * @global object $invoice
    * @global array $wpi_settings
    * @param array $data
    */
  function process_payment($data=null) {
    global $invoice, $wpi_settings;

    //** Require our external libraries */
    require_once( WPI_Path . '/third-party/authorize.net/authnet.class.php' );
    require_once( WPI_Path . '/third-party/authorize.net/authnetARB.class.php' );

    // Pull in the CCard data from the request, and other variables we'll use
    // If data passed then use it. Otherwise use data from request.
    // It used to make available to do payment processes by WPI_Payment_Api
    $cc_data = is_null($data) ? $_REQUEST['cc_data'] : $data;
    $invoice_id = $invoice['invoice_id'];
    $wp_users_id = $invoice['user_data']['ID'];
    $post_id = wpi_invoice_id_to_post_id($invoice_id);

    //** Recurring */
    $recurring = $invoice['type'] == 'recurring' ? true : false;

    //** Response */
    $response = array(
        'success' => false,
        'error' => false,
        'data' => null
    );

    //** Invoice custom id which is sending to authorize.net */
    $cc_data['invoice_id'] = $invoice_id;

    $invoice_obj = new WPI_Invoice();
    $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

    if ($invoice['deposit_amount'] > 0) {
      $amount = (float) $cc_data['amount'];
      if (((float) $cc_data['amount']) > $invoice['net']) {
        $amount = $invoice['net'];
      }
      if (((float) $cc_data['amount']) < $invoice['deposit_amount']) {
        $amount = $invoice['deposit_amount'];
      }
    } else {
      $amount = $invoice['net'];
    }

    //** We assume that all data is good to go, considering we are valadating with JavaScript */
    $payment = new WP_Invoice_Authnet();
    $payment->transaction($cc_data['card_num']);

    //** Billing Info */
    $payment->setParameter("x_card_code", $cc_data['card_code']);
    $payment->setParameter("x_exp_date ", $cc_data['exp_month'] . $cc_data['exp_year']);
    $payment->setParameter("x_amount", $amount);
    $payment->setParameter("x_currency_code", $cc_data['currency_code']);

    if ($recurring) {
      $payment->setParameter("x_recurring_billing", true);
    }

    //** Order Info */
    $payment->setParameter("x_description", $invoice['post_title']);
    $payment->setParameter("x_invoice_id", $invoice['invoice_id']);
    $payment->setParameter("x_duplicate_window", 30);

    //** Customer Info */
    $payment->setParameter("x_first_name", $cc_data['first_name']);
    $payment->setParameter("x_last_name", $cc_data['last_name']);
    $payment->setParameter("x_address", $cc_data['streetaddress']);
    $payment->setParameter("x_city", $cc_data['city']);
    $payment->setParameter("x_state", $cc_data['state']);
    $payment->setParameter("x_country", $cc_data['country']);
    $payment->setParameter("x_zip", $cc_data['zip']);
    $payment->setParameter("x_phone", $cc_data['phonenumber']);
    $payment->setParameter("x_email", $cc_data['user_email']);
    $payment->setParameter("x_cust_id", "WP User - " . $wp_users_id);
    $payment->setParameter("x_customer_ip ", $_SERVER['REMOTE_ADDR']);

    //** Process */
    $payment->process();

    //** Process results */
    if ($payment->isApproved()) {
      update_user_meta($wp_users_id, 'last_name', $cc_data['last_name']);
      update_user_meta($wp_users_id, 'first_name', $cc_data['first_name']);
      update_user_meta($wp_users_id, 'city', $cc_data['city']);
      update_user_meta($wp_users_id, 'state', $cc_data['state']);
      update_user_meta($wp_users_id, 'zip', $cc_data['zip']);
      update_user_meta($wp_users_id, 'streetaddress', $cc_data['streetaddress']);
      update_user_meta($wp_users_id, 'phonenumber', $cc_data['phonenumber']);
      update_user_meta($wp_users_id, 'country', $cc_data['country']);

      do_action( 'wpi_authorize_user_meta_updated', $cc_data );

      //** Add payment amount */
      $event_note = WPI_Functions::currency_format($amount, $invoice['invoice_id']) . " paid via Authorize.net";
      $event_amount = $amount;
      $event_type = 'add_payment';

      $event_note = urlencode($event_note);
      //** Log balance changes */
      $invoice_obj->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
      //** Log client IP */
      $success = "Successfully processed by {$_SERVER['REMOTE_ADDR']}";
      $invoice_obj->add_entry("attribute=invoice&note=$success&type=update");
      //** Log payer email */
      $payer_email = "Authorize.net Payer email: {$cc_data['user_email']}";
      $invoice_obj->add_entry("attribute=invoice&note=$payer_email&type=update");

      $invoice_obj->save_invoice();
      //** Mark invoice as paid */
      wp_invoice_mark_as_paid($invoice_id, $check = true);

      send_notification( $invoice );

      $data['messages'][] = $payment->getResponseText();
      $response['success'] = true;
      $response['error'] = false;

      if ($recurring) {
        $arb = new WP_Invoice_AuthnetARB($invoice);
        //** Customer Info */
        $arb->setParameter('customerId', "WP User - " . $invoice['user_data']['ID']);
        $arb->setParameter('firstName', !empty( $cc_data['first_name'] )?$cc_data['first_name']:'-');
        $arb->setParameter('lastName', !empty( $cc_data['last_name'] )?$cc_data['last_name']:'-');
        $arb->setParameter('address', !empty( $cc_data['streetaddress'] )?$cc_data['streetaddress']:'-');
        $arb->setParameter('city', !empty( $cc_data['city'] )?$cc_data['city']:'-');
        $arb->setParameter('state', !empty( $cc_data['state'] )?$cc_data['state']:'-');
        $arb->setParameter('zip', !empty( $cc_data['zip'] )?$cc_data['zip']:'-');
        $arb->setParameter('country', !empty( $cc_data['country'] )?$cc_data['country']:'-');
        $arb->setParameter('customerEmail', !empty( $cc_data['user_email'] )?$cc_data['user_email']:'-');
        $arb->setParameter('customerPhoneNumber', !empty( $cc_data['phonenumber'] )?$cc_data['phonenumber']:'-');

        //** Billing Info */
        $arb->setParameter('amount', $invoice['net']);
        $arb->setParameter('cardNumber', $cc_data['card_num']);
        $arb->setParameter('expirationDate', $cc_data['exp_month'] . $cc_data['exp_year']);

        //** Subscription Info */
        $arb->setParameter('refID', $invoice['invoice_id']);
        $arb->setParameter('subscrName', $invoice['post_title']);

        $arb->setParameter('interval_length', $invoice['recurring']['wpi_authorize']['length']);
        $arb->setParameter('interval_unit', $invoice['recurring']['wpi_authorize']['unit']);

        //** format: yyyy-mm-dd */
        if ($invoice['recurring']['wpi_authorize']['send_invoice_automatically'] == 'on') {
          $arb->setParameter('startDate', date("Y-m-d", time()));
        } else {
          $arb->setParameter('startDate', $invoice['recurring']['wpi_authorize']['start_date']['year'] . '-' . $invoice['recurring']['wpi_authorize']['start_date']['month'] . '-' . $invoice['recurring']['wpi_authorize']['start_date']['day']);
        }

        $arb->setParameter('totalOccurrences', $invoice['recurring']['wpi_authorize']['cycles']);

        $arb->setParameter('trialOccurrences', 1);
        $arb->setParameter('trialAmount', '0.00');

        $arb->setParameter('orderInvoiceNumber', $invoice['invoice_id']);
        $arb->setParameter('orderDescription', $invoice['post_title']);

        $arb->createAccount();

        if ($arb->isSuccessful()) {
          update_post_meta($post_id, 'subscription_id', $arb->getSubscriberID());
          WPI_Functions::log_event($post_id, 'invoice', 'update', '', __('Subscription initiated, Subcription ID', WPI).' - ' . $arb->getSubscriberID());
          $data['messages'][] = "Recurring Billing Subscription initiated";
          $response['success'] = true;
          $response['error'] = false;
        }

        if ($arb->isError()) {
          $data['messages'][] = __('One-time credit card payment is processed successfully. However, recurring billing setup failed. ', WPI) . $arb->getResponse();
          $response['success'] = false;
          $response['error'] = true;
          WPI_Functions::log_event($post_id, 'invoice', 'update', '', __('Response Code: ', WPI) . $arb->getResponseCode() . ' | '.__('Subscription error', WPI).' - ' . $arb->getResponse());
        }
      }
    } else {
      $response['success'] = false;
      $response['error'] = true;
      $data['messages'][] = $payment->getResponseText();
    }

    $response['data'] = $data;

    // Uncomment these to troubleshoot.  You will need FireBug to view the response of the AJAX post.
    //echo $arb->xml;
    //echo $arb->response;
    //echo $arb->getResponse();
    //print_r( $payment->getResults() );
    //echo $payment->getResponseText();
    //echo $payment->getTransactionID();
    //echo $payment->getAVSResponse();
    //echo $payment->getAuthCode();

    die(json_encode($response));
  }

  /**
   * Handler for Silent Post Url
   */
  function server_callback() {
    $arb = false;
    $fields = array();

    foreach ($_REQUEST as $name => $value) {
      $fields[$name] = $value;
      if ($name == 'x_subscription_id') {
        $arb = true;
      }
    }

    // Handle recurring billing payments
    if ($arb == true && $fields['x_response_code'] == 1) {

      $paynum = $fields['x_subscription_paynum'];
      $subscription_id = $fields['x_subscription_id'];
      $amount = $fields['x_amount'];
      $invoice_id = wpi_post_id_to_invoice_id(wpi_subscription_id_to_post_id($subscription_id));

      $invoice_obj = new WPI_Invoice();
      $invoice_obj->load_invoice("id=$invoice_id");

      // Add payment amount
      $event_note = WPI_Functions::currency_format(abs($amount), $invoice_id) . ". ARB payment $paynum of {$invoice_obj->data['recurring']['wpi_authorize']['cycles']}";
      $event_amount = $amount;
      $event_type = 'add_payment';

      $invoice_obj->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");

      // Complete subscription if last payment done
      if ($invoice_obj->data['recurring']['wpi_authorize']['cycles'] <= $paynum) {
        WPI_Functions::log_event(wpi_invoice_id_to_post_id($invoice_id), 'invoice', 'update', '', __('Subscription completely paid', WPI));
        wp_invoice_mark_as_paid($invoice_id);
      }

      $invoice_obj->save_invoice();
    }

  }

}