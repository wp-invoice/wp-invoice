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
    function recurring_settings( $invoice ) {
    ?>
      <h4><?php _e( 'InterKassa Recurring Billing', WPI ); ?></h4>
      <p><?php _e( 'Currently InterKassa gateway does not support Recurring Billing', WPI ); ?></p>
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
                        <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo!empty($invoice['user_data'][$field_slug]) ? $invoice['user_data'][$field_slug] : ''; ?>" />
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
    function server_callback() {

        if ( empty( $_POST ) ) die(__('Direct access not allowed', WPI));

        $invoice = new WPI_Invoice();
        $invoice->load_invoice("id={$_POST['ik_payment_id']}");

        if ( $_POST['ik_payment_state'] != 'success' ) {
            echo 'Failed';
            return;
        }

        if ( !$this->_hash_verified( $invoice ) ) {
            echo 'Hash or Shop ID is wrong';
            return;
        }

        if ( get_post_meta( $invoice->data['ID'], 'wpi_processed_by_interkassa', 1 ) == 'true' ) {
            echo 'Already processed';
            return;
        }

        update_post_meta( $invoice->data['ID'], 'wpi_processed_by_interkassa', 'true' );

        /** Add payment amount */
        $event_note = sprintf(__('%s paid via InterKassa [%s]', WPI), WPI_Functions::currency_format(abs($_POST['ik_payment_amount'])), $_POST['ik_paysystem_alias']);
        $event_amount = (float)$_POST['ik_payment_amount'];
        $event_type   = 'add_payment';

        //** Log balance changes */
        $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");

        //** Log payer email */
        $trans_id = sprintf(__("Transaction ID: %s", WPI), $_POST['ik_trans_id']);
        $invoice->add_entry("attribute=invoice&note=$trans_id&type=update");
        $invoice->save_invoice();

        //** ... and mark invoice as paid */
        wp_invoice_mark_as_paid( $_POST['ik_payment_id'], $check = true );
        send_notification( $invoice->data );

        echo 'OK';

    }

    /**
     * Hash checker
     * @global type $wpi_settings
     * @param type $invoice
     * @return type
     */
    private function _hash_verified( $invoice ) {

        $sing_hash_str = $_POST['ik_shop_id'].':'.
            $_POST['ik_payment_amount'].':'.
            $_POST['ik_payment_id'].':'.
            $_POST['ik_paysystem_alias'].':'.
            $_POST['ik_baggage_fields'].':'.
            $_POST['ik_payment_state'].':'.
            $_POST['ik_trans_id'].':'.
            $_POST['ik_currency_exch'].':'.
            $_POST['ik_fees_payer'].':'.
            $invoice->data['billing']['wpi_interkassa']['settings']['secret_key']['value'];

        $sign_hash = strtoupper( md5( $sing_hash_str ) );

        $hash_is_good = $_POST['ik_sign_hash'] == $sign_hash;

        $shop_is_good = $_POST['ik_shop_id'] == $invoice->data['billing']['wpi_interkassa']['settings']['ik_shop_id']['value'];

        return $hash_is_good && $shop_is_good;

    }

    /**
     * Payment Processor
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
        update_user_meta($wp_users_id, 'phonenumber', $_REQUEST['phonenumber']);
        update_user_meta($wp_users_id, 'country', $_REQUEST['country']);

        if (!empty($crm_data))
            $this->user_meta_updated($crm_data);

        echo json_encode(
            array('success' => 1)
        );
    }

}
?>
