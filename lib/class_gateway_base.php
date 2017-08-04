<?php

/**
 * This class represents the base class for any WPI payment gateway.  All the functions
 * herein should be overridden.  The child classes should be in the 'gateways' folder.
 * @since 3.0
 */
abstract class wpi_gateway_base {

  const TEXT_INPUT_TYPE = 'text';
  const SELECT_INPUT_TYPE = 'select';
  const TEXTAREA_INPUT_TYPE = 'textarea';
  const CHECKBOX_INPUT_TYPE = 'checkbox';
  const RECAPTCHA_INPUT_TYPE = 'recaptcha';
  
  var $options = array();
  var $front_end_fields = array();

  /**
   * This function sets the 'type' variable for us, anything that overrides this should
   * call 'parent::__construct'
   * @since 1.0
   */
  function __construct() {
    //** Set the class name */
    $this->type = get_class($this);
    __('Customer Information', ud_get_wp_invoice()->domain);
    add_filter('sync_billing_update', array('wpi_gateway_base', 'sync_billing_filter'), 10, 3);
    add_filter('wpi_recurring_settings', create_function(' $gateways ', ' $gateways[] = "' . $this->type . '"; return $gateways; '));
    add_action('wpi_recurring_settings_' . $this->type, array($this, 'recurring_settings'));
    add_action('wpi_payment_fields_' . $this->type, array($this, 'wpi_payment_fields'));
  }

  /**
   * Each payment handler must call this function after successful payment fact.
   * @param $invoice
   */
  public static function successful_payment( $invoice ) {
    do_action( 'wpi_successful_payment', $invoice );
  }

  /**
   * Need to call this once webhook or any other payment confirmation received from payment gateway
   * @param $invoice
   */
  public static function successful_payment_webhook( $invoice ) {
    do_action( 'wpi_successful_payment_webhook', $invoice );
  }

  /**
   * This function handles the display of the admin settings for the individual payment gateway
   * It is called on the settings page and on the invoice page
   * @param string $args A URL encoded string that contains all the arguments
   * @since 3.0
   */
  function frontend_display($args = '', $from_ajax = false) {
    global $wpdb, $wpi_settings, $invoice;
    //** Setup defaults, and extract the variables */
    $defaults = array();
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    $process_payment_nonce = wp_create_nonce( "process-payment" );
    //** Include the template file required */
    include('gateways/templates/payment_header.tpl.php');
    include('gateways/templates/' . $this->type . '-frontend.tpl.php');
    include('gateways/templates/payment_footer.tpl.php');
  }

  /**
   * This function handles AJAX payment type changes - it is simply a wrapper for
   * the 'frontend_display' function
   * @since 3.0
   */
  static function change_payment_form_ajax() {
    global $wpdb, $wpi_settings, $invoice;
    //** Pull in the invoice */
    $the_invoice = new WPI_Invoice();
    $invoice = $the_invoice->load_invoice("return=true&id=" . wpi_invoice_id_to_post_id($_REQUEST['invoice_id']));
    //** We have the invoice, call the frontend_display */
    $wpi_settings['installed_gateways'][$_REQUEST['type']]['object']->frontend_display($invoice, true);
    die();
  }

  /**
   * This function handles the processing of the payments - it should be overrideen in child classes
   * @param string $args The args for the fucnction
   * @since 3.0
   */
  static function process_payment() {
    check_ajax_referer( 'process-payment', 'security' );
    global $wpi_settings, $invoice, $wp_crm;

    if(
      !empty($wp_crm[ 'configuration' ][ 'recaptcha_site_key' ]) &&
      isset($wp_crm['data_structure']['attributes']['recaptcha']['wp_invoice']) &&
      $wp_crm['data_structure']['attributes']['recaptcha']['wp_invoice'] == 'true'
    ){
      if(!isset($_REQUEST['cc_data']['recaptcha']) || !WP_CRM_F::reCaptchaVerify($_REQUEST['cc_data']['recaptcha'])){
        //** Response */
        $response = array(
            'success' => false,
            'error' => true,
            'data' => array(
                'messages' => array( __("Captcha validation field.") ),
              )
        );
        die(json_encode($response));
      }
    }

    //** Pull the invoice */
    $the_invoice = new WPI_Invoice();
    $invoice = $the_invoice->load_invoice("return=true&id=" . wpi_invoice_id_to_post_id($_REQUEST['invoice_id']));
    //** Call the child function based on the wpi_type variable sent */
    do_action( 'wpi_before_process_payment', $invoice );
    $wpi_settings['installed_gateways'][$_REQUEST['type']]['object']->process_payment();
    die();
  }

  /**
   * This function listens for any server call backs for the payment gateway (i.e. PayPal IPN)
   * It gets called by the ajax action:
   * http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=typeofpaymentobject
   * It should be overridden
   * @since 3.0
   */
  static function server_callback() {
    global $wpi_settings;
    //** Call the actual function that does the processing on the type of object we have */
    $wpi_settings['installed_gateways'][$_REQUEST['type']]['object']->server_callback();
    die();
  }

  /**
   * This function syncs our options table with our actual object
   * @since 3.0
   */
  static function sync_billing_objects() {
    global $wpi_settings;

    if (!isset($wpi_settings['billing']) || !is_array($wpi_settings['billing'])) {
      $wpi_settings['billing'] = array();
    }

    $g = array();
    //** Handle Merging of arrays to custom variable */
    foreach ($wpi_settings['installed_gateways'] as $slug => $gateway) {

      if (!empty($gateway['object']->options)) {
        foreach ($gateway['object']->options as $option_key => $option) {

          switch ($option_key) {
            //** Handle Settings element. */
            case 'settings':
              if (is_array($option)) {
                foreach ($option as $k => $v) {
                  if (!isset($wpi_settings['billing'][$slug][$option_key][$k])) {
                    $g[$slug][$option_key][$k] = $v;
                  } else {
                    if (is_array($v)) {
                      $g[$slug][$option_key][$k] = apply_filters('sync_billing_update', $k, $v, wp_parse_args($wpi_settings['billing'][$slug][$option_key][$k], $v));
                    } else {
                      $g[$slug][$option_key][$k] = !empty($wpi_settings['billing'][$slug][$option_key][$k]) ? $wpi_settings['billing'][$slug][$option_key][$k] : $v;
                    }
                  }
                }
              } else {
                $g[$slug][$option_key] = !empty($wpi_settings['billing'][$slug][$option_key]) ? $wpi_settings['billing'][$slug][$option_key] : $option;
              }
              break;

            default:
              if (!isset($wpi_settings['billing'][$slug][$option_key])) {
                $g[$slug][$option_key] = $option;
              } else {
                if (!is_array($option)) {
                  $g[$slug][$option_key] = !empty($wpi_settings['billing'][$slug][$option_key]) ? $wpi_settings['billing'][$slug][$option_key] : $option;
                } else {
                  $g[$slug][$option_key] = wp_parse_args($wpi_settings['billing'][$slug][$option_key], $option);
                }
              }
              break;
          }
        }
      }

      //** Do it recursively, so both items have the same values */
      if (!empty($wpi_settings['installed_gateways'][$slug]['object']->options)) {
        $wpi_settings['installed_gateways'][$slug]['object']->options = $g[$slug];
      }
      $wpi_settings['billing'][$slug] = !empty($g[$slug]) ? $g[$slug] : array();
    }
  }

  /**
   * Sync billing
   * @param type $setting_slug
   * @param type $new_setting_array
   * @param type $def_setting_array
   * @return type
   */
  public static function sync_billing_filter($setting_slug, $new_setting_array, $def_setting_array) {

    if ($setting_slug == 'ipn' || $setting_slug == 'silent_post_url') {
      return $new_setting_array;
    }

    return $def_setting_array;
  }

  /**
   * CRM user_meta updating on payment done
   *
   * @global type $invoice
   * @param type $data
   * @return type
   */
  function user_meta_updated($data) {
    global $invoice;

    //** CRM data updating */
    if (!class_exists('WP_CRM_Core'))
      return;

    $crm_attributes = WPI_Functions::get_wpi_crm_attributes();
    if (empty($crm_attributes))
      return;

    $wp_users_id = $invoice['user_data']['ID'];

    foreach ($data as $key => $value) {
      if (key_exists($key, $crm_attributes)) {
        switch ($crm_attributes[$key]['input_type']) {
          case 'dropdown':

            foreach ($crm_attributes[$key]['option_keys'] as $_to_rm) {
              delete_user_meta($wp_users_id, $_to_rm);
            }

            update_user_meta($wp_users_id, $value, 'on');
            break;

          default:
            update_user_meta($wp_users_id, $key, $value);
            break;
        }
      }
    }
  }

  /**
   * @param $invoice
   */
  public static function handle_terms_acceptance( $invoice ) {
    if ( empty( $_POST['accept_terms'] ) || $_POST['accept_terms'] != 'true' ) {
      wpi_send_json_error(array('messages'=>array(__('Terms must be accepted. Aborting payment.', ud_get_wp_invoice()->domain)) ));
    }
  }

  /**
   * Display recaptcha field.
   * @author alim@udx
   * @param $field_data
   */
  public function display_recaptcha($field_data){
    global $wp_crm;

    if(!empty($wp_crm[ 'configuration' ][ 'recaptcha_site_key' ])){
      $site_key = $wp_crm[ 'configuration' ][ 'recaptcha_site_key' ];
    }
    else{
      echo "<script>console.error('" . __("To enable chaptcha please set reCAPTCHA keys in CRM settings page.") . "');</script>";
      return;
    }

    WP_CRM_F::force_script_inclusion( 'recaptcha' );

    ?>
    <li class="wpi_checkout_row wp_crm_recaptcha_container">
      <div class="control-group wp_crm_recaptcha_div">
        <label class="control-label"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
        <input class="crm-g-captcha-input" type="hidden" name="<?php echo esc_attr( $field_data['name'] ); ?>">
        <div class='crm-g-recaptcha crm-clearfix controls clearfix' data-sitekey='<?php echo $site_key;?>'></div>
        <span class="help-inline wp_crm_error_messages crm-clearfix"></span>
      </div>
    </li>
    <style type="text/css">
      .wp_crm_recaptcha_container .control-label{
        margin-bottom: 10px;
      }
    </style>
    <script type="text/javascript">
      var type = jQuery("#wpi_form_type").val();
      if ( typeof type != 'undefined' ){
        var type_messages = window[type + '_messages'];
        var type_rules = window[type + '_rules'];

        type_rules["<?php echo esc_attr( $field_data['name'] ); ?>"] = {
            required: true
          };

        type_messages["<?php echo esc_attr( $field_data['name'] ); ?>"] = {
            required: "Are you human? Please verify the captcha."
          };

        function crm_recaptcha_onload(argument) {
          jQuery('.crm-g-recaptcha').each(function(argument) {
            var container = jQuery(this);
            var formID = container.parents('form').attr('id');
            var parameters = {
              sitekey: container.data('sitekey'),
              callback: function(response){
                var input = jQuery('.crm-g-captcha-input', '#' + formID);
                input.val(response);
              },
              'expired-callback': function(){
                var input = jQuery('.crm-g-captcha-input', '#' + formID);
                input.val('');
              },
            };
            window[formID + '_recaptcha'] = grecaptcha.render(
              this,
              parameters
            );

          });
        }
      }
    </script>
    <?php
  }

  /**
   * Redecrale this in child class
   */
  abstract function wpi_payment_fields($invoice);

  abstract function recurring_settings($invoice);
}