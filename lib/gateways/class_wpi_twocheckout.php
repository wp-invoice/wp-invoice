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
  
  static $_options = array();

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    
    $this->options = array(
        'name' => __( '2Checkout', ud_get_wp_invoice()->domain ),
        'allow' => '',
        'default_option' => '',
        'settings' => array(
            'twocheckout_sid' => array(
                'label' => __( "2Checkout Seller ID", ud_get_wp_invoice()->domain ),
                'value' => ''
            ),
            'twocheckout_secret' => array(
                'label' => __( "2Checkout Secret Word", ud_get_wp_invoice()->domain ),
                'value' => ''
            ),
            'test_mode' => array(
                'label' => __( "Demo Mode", ud_get_wp_invoice()->domain ),
                'description' => __( "Use 2Checkout Demo Mode", ud_get_wp_invoice()->domain ),
                'type' => 'select',
                'value' => 'N',
                'data' => array(
                    'N' => __( "No", ud_get_wp_invoice()->domain ),
                    'Y' => __( "Yes", ud_get_wp_invoice()->domain )
                )
            ),
            'passback' => array(
                'label' => __( "2Checkout Approved URL/INS URL", ud_get_wp_invoice()->domain ),
                'type' => "readonly",
                'description' => __( "Set this URL as your Approved URL in your 2Checkout Site Management page and Notification URL under your 2Checkout Notification page.", ud_get_wp_invoice()->domain )
            )
        )
    );
    
    //** Fields for front-end. */
    $this->front_end_fields = array(
        'customer_information' => array(
            'first_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'first_name',
                'label' => __('First Name', ud_get_wp_invoice()->domain)
            ),
            'last_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'last_name',
                'label' => __('Last Name', ud_get_wp_invoice()->domain)
            ),
            'user_email' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'email',
                'label' => __('Email Address', ud_get_wp_invoice()->domain)
            ),
            'phonenumber' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'phonenumber',
                'label' => __('Phone', ud_get_wp_invoice()->domain)
            ),
            'streetaddress' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'street_address',
                'label' => __('Address', ud_get_wp_invoice()->domain)
            ),
            'country' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'country',
                'label' => __('Country', ud_get_wp_invoice()->domain)
            ),
            'city' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'city',
                'label' => __('City', ud_get_wp_invoice()->domain)
            ),
            'state' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'state',
                'label' => __('State/Province', ud_get_wp_invoice()->domain)
            ),
            'zip' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'zip',
                'label' => __('Zip/Postal Code', ud_get_wp_invoice()->domain)
            )
        )
    );
    
    $this->options['settings']['passback']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_twocheckout');
  
    self::$_options = $this->options;
  }

  /**
   * Recurring settings UI
   * @param type $this_invoice
   */
  function recurring_settings($this_invoice) {
    ?>
    <h4><?php _e('2Checkout Recurring Billing', ud_get_wp_invoice()->domain); ?></h4>
    <table class="wpi_recurring_bill_settings">
        <tr>
            <th style="cursor:help;" title="<?php _e('Specifies billing frequency.', ud_get_wp_invoice()->domain); ?>"><?php _e('Interval', ud_get_wp_invoice()->domain); ?></th>
            <td>
              <?php echo WPI_UI::input("id=2co_recurrence_interval&name=wpi_invoice[recurring][".$this->type."][recurrence_interval]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['recurrence_interval'] : '') . "&special=size='2' maxlength='4' autocomplete='off'"); ?>
              <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][recurrence_period]&values=" . serialize(apply_filters('wpi_2co_recurrence_period', array("Week" => __("Week", ud_get_wp_invoice()->domain), "Month" => __("Month", ud_get_wp_invoice()->domain), "Year" => __("Year", ud_get_wp_invoice()->domain)))) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['recurrence_period'] : '')); ?>
            </td>
        </tr>
        <tr>
            <th style="cursor:help;" title="<?php _e('Specifies billing duration.', ud_get_wp_invoice()->domain); ?>"><?php _e('Duration', ud_get_wp_invoice()->domain); ?></th>
            <td>
              <?php echo WPI_UI::input("id=2co_duration_interval&name=wpi_invoice[recurring][".$this->type."][duration_interval]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['duration_interval'] : '') . "&special=size='2' maxlength='4' autocomplete='off'"); ?>
              <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][duration_period]&values=" . serialize(apply_filters('wpi_2co_duration_period', array("Week" => __("Week", ud_get_wp_invoice()->domain), "Month" => __("Month", ud_get_wp_invoice()->domain), "Year" => __("Year", ud_get_wp_invoice()->domain)))) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['duration_period'] : '')); ?>
            </td>
        </tr>
    </table>
    <?php
  }
  
  /**
   * Get recurrence
   * @param type $invoice
   */
  public function get_recurrence( $invoice ) {
    return $invoice['recurring']['wpi_twocheckout']['recurrence_interval'].' '.$invoice['recurring']['wpi_twocheckout']['recurrence_period'];
  }
  
  /**
   * Get duration
   * @param type $invoice
   */
  public function get_duration( $invoice ) {
    return $invoice['recurring']['wpi_twocheckout']['duration_interval'].' '.$invoice['recurring']['wpi_twocheckout']['duration_period'];
  }

  /**
   * Overrided payment process for 2Checkout
   *
   * @global type $invoice
   * @global type $wpi_settings
   */
  static function process_payment() {
    global $invoice;

    $wp_users_id = $invoice['user_data']['ID'];

    // update user data
    update_user_meta($wp_users_id, 'last_name', !empty($_REQUEST['last_name'])?$_REQUEST['last_name']:'' );
    update_user_meta($wp_users_id, 'first_name', !empty($_REQUEST['first_name'])?$_REQUEST['first_name']:'' );
    update_user_meta($wp_users_id, 'city', !empty($_REQUEST['city'])?$_REQUEST['city']:'' );
    update_user_meta($wp_users_id, 'state', !empty($_REQUEST['state'])?$_REQUEST['state']:'' );
    update_user_meta($wp_users_id, 'zip', !empty($_REQUEST['zip'])?$_REQUEST['zip']:'' );
    update_user_meta($wp_users_id, 'streetaddress', !empty($_REQUEST['street_address'])?$_REQUEST['street_address']:'' );
    update_user_meta($wp_users_id, 'phonenumber', !empty($_REQUEST['phonenumber'])?$_REQUEST['phonenumber']:'' );
    update_user_meta($wp_users_id, 'country', !empty($_REQUEST['country'])?$_REQUEST['country']:'' );

    if ( !empty( $_REQUEST['crm_data'] ) ) {
      self::user_meta_updated( $_REQUEST['crm_data'] );
    }

    $invoice_obj = new WPI_Invoice();
    $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

    parent::successful_payment($invoice_obj);

    echo json_encode(
      array('success' => 1)
    );
  }

  /**
   * Fields renderer
   * @param type $invoice
   */
  public function wpi_payment_fields($invoice) {

    $this->front_end_fields = apply_filters('wpi_crm_custom_fields', $this->front_end_fields, 'crm_data');

    if (!empty($this->front_end_fields)) {
      //** For each section */
      foreach ($this->front_end_fields as $key => $value) {
        //** If section is not empty */
        if (!empty($this->front_end_fields[$key])) {
          $html = '';
          ob_start();
          ?>
          <ul class="wpi_checkout_block">
            <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), ud_get_wp_invoice()->domain); ?></li>
            <?php
            $html = ob_get_clean();
            echo $html;
            //** For each field */
            foreach ($value as $field_slug => $field_data) {
              //** Change field properties if we need */
              $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_twocheckout');
              $html = '';

              ob_start();

              switch ($field_data['type']) {
                case self::TEXT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
                      <div class="controls">
                        <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo isset($field_data['value'])?$field_data['value']:(!empty($invoice['user_data'][$field_slug])?$invoice['user_data'][$field_slug]:'');?>" />
                      </div>
                    </div>
                  </li>

                  <?php
                  $html = ob_get_clean();

                  break;

                case self::SELECT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <label for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
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
     * Handler for 2Checkout Callback
     * @author Craig Christenson
     * Full callback URL: http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_twocheckout
     */
    static function server_callback() {

      if (empty($_REQUEST)) {
        die(__('Direct access not allowed', ud_get_wp_invoice()->domain));
      }

      $invoice = new WPI_Invoice();
      $invoice->load_invoice("id={$_REQUEST['merchant_order_id']}");

      /** Verify callback request */
      if ( self::_ipn_verified($invoice) ) {
        if ($_REQUEST['key']) {
          $event_note = sprintf(__('%s paid via 2Checkout', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_REQUEST['total']), $_REQUEST['merchant_order_id']));
          $event_amount = (float) $_REQUEST['total'];
          $event_type = 'add_payment';
          /** Log balance changes */
          $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
          /** Log payer email */
          $payer_email = sprintf(__("2Checkout buyer email: %s", ud_get_wp_invoice()->domain), $_REQUEST['email']);
          $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
          $invoice->save_invoice();
          /** ... and mark invoice as paid */
          wp_invoice_mark_as_paid($_REQUEST['invoice_id'], $check = true);
          parent::successful_payment( $invoice );
          send_notification($invoice->data);
          echo '<script type="text/javascript">window.location="' . get_invoice_permalink($invoice->data['ID']) . '";</script>';

          /** Handle INS messages */
        } elseif ($_POST['md5_hash']) {

          switch ($_POST['message_type']) {

            case 'FRAUD_STATUS_CHANGED':
              if ($_POST['fraud_status'] == 'pass') {
                WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Passed 2Checkout fraud review.', ud_get_wp_invoice()->domain));
              } elseif (condition) {
                WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Failed 2Checkout fraud review.', ud_get_wp_invoice()->domain));
                wp_invoice_mark_as_pending($_POST['vendor_order_id']);
              }
              break;

            case 'RECURRING_STOPPED':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring billing stopped.', ud_get_wp_invoice()->domain));
              break;

            case 'RECURRING_INSTALLMENT_FAILED':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring installment failed.', ud_get_wp_invoice()->domain));
              break;

            case 'RECURRING_INSTALLMENT_SUCCESS':
              $event_note = sprintf(__('%1s paid for subscription %2s', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_POST['item_rec_list_amount_1']), $_POST['vendor_order_id']), $_POST['sale_id']);
              $event_amount = (float) $_POST['item_rec_list_amount_1'];
              $event_type = 'add_payment';
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              $invoice->save_invoice();
              send_notification($invoice->data);
              break;

            case 'RECURRING_COMPLETE':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring installments completed.', ud_get_wp_invoice()->domain));
              wp_invoice_mark_as_paid($_POST['invoice'], $check = false);
              break;

            case 'RECURRING_RESTARTED':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring sale restarted.', ud_get_wp_invoice()->domain));
              break;

            default:
              break;
          }
        }
      }
    }
    
    /**
     * Get proper api url
     * @filters wpi_2co_live_url, wpi_2co_demo_url
     * @param type $invoice
     * @return type
     */
    public function get_api_url( $invoice ) {
      return $invoice['billing']['wpi_twocheckout']['settings']['test_mode']['value'] == 'N' ? apply_filters( 'wpi_2co_live_url', 'https://www.2checkout.com/checkout/purchase' ) : apply_filters( 'wpi_2co_demo_url', 'https://sandbox.2checkout.com/checkout/purchase' );
    }
    
    /**
     * Get SID
     * @param type $invoice
     * @return type
     */
    public function get_sid( $invoice ) {
      return $invoice['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value'];
    }
    
    /**
     * 
     * @param type $invoice
     * @return \type
     */
    public function get_callback_url( $invoice ) {
      return $invoice['billing']['wpi_twocheckout']['settings']['passback']['value'];
    }

   /**
    * Verify return/notification and return TRUE or FALSE
    * @author Craig Christenson
    **/
    private static function _ipn_verified($invoice = false) {

      if ($_REQUEST['key']) {
        $transaction_id = $_REQUEST['order_number'];
        
        $compare_string = $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_secret']['value'] .
                $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value'] . $transaction_id .
                $_REQUEST['total'];
        
        $compare_hash1 = strtoupper(md5($compare_string));
        $compare_hash2 = $_REQUEST['key'];

        if ($compare_hash1 != $compare_hash2) {
          die("MD5 HASH Mismatch! Make sure your demo settings are correct.");
        } else {
          return TRUE;
        }
      } elseif ($_POST['md5_hash']) {
        $compare_string = $_POST['sale_id'] . $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value'] .
                $_POST['invoice_id'] . $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_secret']['value'];
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
  