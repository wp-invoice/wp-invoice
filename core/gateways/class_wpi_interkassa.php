<?php

/**
  Name: InterKassa
  Class: wpi_interkassa
  Internal Slug: wpi_interkassa
  JS Slug: wpi_interkassa
  Version: 1.0
  Description: Provides InterKassa gateway. No recurring payments support.
 */
class wpi_interkassa extends wpi_gateway_base {

  /**
   * Construct
   */
  public function __construct() {
    parent::__construct();

    $this->options = array(
        'name' => 'InterKassa',
        'allow' => '',
        'default_option' => '',
        'settings' => array(
            'ik_shop_id' => array(
                'label' => __("Shop ID", WPI),
                'value' => ''
            ),
            'secret_key' => array(
                'label' => __("Secret Key", WPI),
                'value' => ''
            ),
            'test_key' => array(
                'label' => __("Test Key", WPI),
                'value' => ''
            ),
            'ipn' => array(
                'label' => __("Status URL", WPI),
                'type' => "readonly",
                'description' => __("Use this URL as Status URL in Merchant settings to get notified once payments made.", WPI)
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
                'label' => __('First Name', WPI)
            ),
            'last_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'last_name',
                'label' => __('Last Name', WPI)
            ),
            'user_email' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'email_address',
                'label' => __('Email Address', WPI)
            ),
            'phonenumber' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'phonenumber',
                'label' => __('Phone', WPI)
            ),
            'streetaddress' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'address1',
                'label' => __('Address', WPI)
            ),
            'city' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'city',
                'label' => __('City', WPI)
            ),
            'state' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'state',
                'label' => __('State/Province', WPI)
            ),
            'zip' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'zip',
                'label' => __('Zip/Postal Code', WPI)
            )
        )
    );

    $this->options['settings']['ipn']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_interkassa');
  }

  /**
   * Show settings for RB. Nothing in case of InterKassa
   * @param type $invoice
   */
  function recurring_settings($invoice) {
    ?>
    <h4><?php _e('InterKassa Recurring Billing', WPI); ?></h4>
    <p><?php _e('Currently InterKassa gateway does not support Recurring Billing', WPI); ?></p>
    <?php
  }

  /**
   * Fields renderer
   * @param type $invoice
   */
  function wpi_payment_fields($invoice) {

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
            <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), WPI); ?></li>
            <?php
            $html = ob_get_clean();
            echo $html;
            //** For each field */
            foreach ($value as $field_slug => $field_data) {
              //** Change field properties if we need */
              $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_interkassa');
              $html = '';

              ob_start();

              switch ($field_data['type']) {
                case self::TEXT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], WPI); ?></label>
                      <div class="controls">
                        <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo isset($field_data['value']) ? $field_data['value'] : (!empty($invoice['user_data'][$field_slug]) ? $invoice['user_data'][$field_slug] : ''); ?>" />
                      </div>
                    </div>
                  </li>

                  <?php
                  $html = ob_get_clean();

                  break;

                case self::SELECT_INPUT_TYPE:
                  ?>

                  <li class="wpi_checkout_row">
                    <label for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], WPI); ?></label>
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
   * Merchant CB handler
   */
  static function server_callback() {

    if (empty($_POST))
      die(__('Direct access not allowed', WPI));

    $invoice = new WPI_Invoice();
    $invoice->load_invoice("id={$_POST['ik_pm_no']}");

    if ($_POST['ik_inv_st'] != 'success') {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error [Cannot process payment]', true, 500);
      return;
    }

    if (!self::_hash_verified($invoice)) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error [Hash or Shop ID is wrong]', true, 500);
      return;
    }

    if (get_post_meta($invoice->data['ID'], 'wpi_processed_by_interkassa', 1) == 'true') {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error [Already processed]', true, 500);
      return;
    }

    update_post_meta($invoice->data['ID'], 'wpi_processed_by_interkassa', 'true');

    /** Add payment amount */
    $event_note = sprintf(__('%s paid via InterKassa [%s]', WPI), WPI_Functions::currency_format(abs($_POST['ik_am']), $_POST['ik_pm_no']), $_POST['ik_pw_via']);
    $event_amount = (float) $_POST['ik_am'];
    $event_type = 'add_payment';

    //** Log balance changes */
    $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");

    //** Log payer email */
    $trans_id = sprintf(__("Transaction ID: %s", WPI), $_POST['ik_trn_id']);
    $invoice->add_entry("attribute=invoice&note=$trans_id&type=update");
    $invoice->save_invoice();

    //** ... and mark invoice as paid */
    wp_invoice_mark_as_paid($_POST['ik_pm_no'], $check = true);
    send_notification($invoice->data);

    echo 'OK';
  }

  /**
   * Hash checker
   * @global type $wpi_settings
   * @param type $invoice
   * @return type
   */
  private static function _hash_verified($invoice) {

    if ($_POST['ik_pw_via'] == 'test_interkassa_test_xts') {
      $secret_key = $invoice->data['billing']['wpi_interkassa']['settings']['test_key']['value'];
    } else {
      $secret_key = $invoice->data['billing']['wpi_interkassa']['settings']['secret_key']['value'];
    }

    $array = array();
    foreach ($_POST as $key => $value) {
      if (substr($key, 0, 3) == 'ik_' && $key != 'ik_sign') {
        $array[$key] = $value;
      }
    }
    ksort($array, SORT_STRING);
    array_push($array, $secret_key);
    $signString = implode(':', $array);
    $sign_hash = base64_encode(md5($signString, true));

    $hash_is_good = $_POST['ik_sign'] == $sign_hash;

    $shop_is_good = $_POST['ik_co_id'] == $invoice->data['billing']['wpi_interkassa']['settings']['ik_shop_id']['value'];

    return $hash_is_good && $shop_is_good;
  }

  /**
   * Payment Processor
   */
  static function process_payment() {
    global $invoice;

    $wp_users_id = $invoice['user_data']['ID'];

    //** update user data */
    update_user_meta($wp_users_id, 'last_name', !empty($_REQUEST['last_name'])?$_REQUEST['last_name']:'' );
    update_user_meta($wp_users_id, 'first_name', !empty($_REQUEST['first_name'])?$_REQUEST['first_name']:'' );
    update_user_meta($wp_users_id, 'city', !empty($_REQUEST['city'])?$_REQUEST['city']:'' );
    update_user_meta($wp_users_id, 'state', !empty($_REQUEST['state'])?$_REQUEST['state']:'' );
    update_user_meta($wp_users_id, 'zip', !empty($_REQUEST['zip'])?$_REQUEST['zip']:'' );
    update_user_meta($wp_users_id, 'streetaddress', !empty($_REQUEST['address1'])?$_REQUEST['address1']:'' );
    update_user_meta($wp_users_id, 'phonenumber', !empty($_REQUEST['phonenumber'])?$_REQUEST['phonenumber']:'' );
    update_user_meta($wp_users_id, 'country', !empty($_REQUEST['country'])?$_REQUEST['country']:'' );

    if (!empty($_REQUEST['crm_data'])) {
      self::user_meta_updated($_REQUEST['crm_data']);
    }

    echo json_encode(
      array('success' => 1)
    );
  }
}
?>