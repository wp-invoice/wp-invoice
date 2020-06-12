<?php
/**
  Name: USA ePay
  Class: wpi_usa_epay
  Internal Slug: wpi_usa_epay
  JS Slug: wpi_usa_epay
  Version: 1.0
  Description: Provides USA ePay Gateway for WP-Invoice
 */

namespace UsabilityDynamics\WPI_USA_EPAY {

  if (!class_exists('UsabilityDynamics\WPI_USA_EPAY\Gateway') && class_exists('\wpi_gateway_base')) {

    class Gateway extends \wpi_gateway_base {

      const SELECT_COUNTRIES_TYPE = 'countries';
      const SELECT_STATES_TYPE = 'states';

      /**
       * Construct
       */
      function __construct() {

        //** we do not call __construct here to prevent wrong $type to be generated */
        $this->type = 'wpi_usa_epay';

        //** Thats why we need to replicate all that was done in parent::__construct but properly for current case
        __('Customer Information', ud_get_wp_invoice_usa_epay()->domain);
        add_filter('sync_billing_update', array('wpi_gateway_base', 'sync_billing_filter'), 10, 3);
        add_filter('wpi_recurring_settings', create_function(' $gateways ', ' $gateways[] = "' . $this->type . '"; return $gateways; '));
        add_action('wpi_recurring_settings_' . $this->type, array($this, 'recurring_settings'));
        add_action('wpi_payment_fields_' . $this->type, array($this, 'wpi_payment_fields'));

        //** Fields for front-end. */
        $this->front_end_fields = array(
          'customer_information' => array(
            'first_name' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'first_name',
              'label' => __('First Name', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'last_name' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'last_name',
              'label' => __('Last Name', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'user_email' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'email',
              'label' => __('Email Address', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'phonenumber' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'phone',
              'label' => __('Phone', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'streetaddress' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'street',
              'label' => __('Street', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'city' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'city',
              'label' => __('City', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'zip' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'zip',
              'label' => __('Zip/Postal Code', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'country' => array(
              'type' => 'countries',
              'class' => 'text-input',
              'name' => 'countrycode',
              'label' => __('Country', ud_get_wp_invoice_usa_epay()->domain)
            ),
            'state' => array(
              'type' => 'text',
              'class' => 'text-input',
              'name' => 'state',
              'label' => __('State/Province', ud_get_wp_invoice_usa_epay()->domain)
            )
          )
        );

        $this->options = array(
          'name' => 'USA ePay',
          'allow' => '',
          'default_option' => '',
          'settings' => array(
            'key' => array(
              'label' => __("Source Key", ud_get_wp_invoice_usa_epay()->domain),
              'value' => ''
            ),
            'pin' => array(
              'label' => __("PIN", ud_get_wp_invoice_usa_epay()->domain),
              'value' => ''
            ),
            'usesandbox' => array(
              'label' => __("Use in Test Mode", ud_get_wp_invoice_usa_epay()->domain),
              'description' => __("Sandbox", ud_get_wp_invoice_usa_epay()->domain),
              'type' => 'select',
              'value' => 'N',
              'data' => array(
                'N' => __("No", ud_get_wp_invoice_usa_epay()->domain),
                'Y' => __("Yes", ud_get_wp_invoice_usa_epay()->domain)
              )
            )
          )
        );
      }
      
      /**
       * Dodify inputs if needed
       * @param type $attrs
       * @param type $slug
       * @return type
       */
      static function input_attributes( $attrs, $slug ) {
        
        switch( $slug ) {
          
          case 'cc_expiration':
            $attrs[] = 'placeholder="MMYYYY"';
            break;
          
          default: break;
        }
        
        return $attrs;
      }

      /**
       * Override function
       * @global type $wpdb
       * @global type $wpi_settings
       * @global type $invoice
       * @param type $args
       * @param type $from_ajax
       */
      function frontend_display($args = '', $from_ajax = false) {
        global $wpdb, $wpi_settings, $invoice;
        //** Setup defaults, and extract the variables */
        $defaults = array();
        extract(wp_parse_args($args, $defaults), EXTR_SKIP);
        //** Include the template file required */
        $process_payment_nonce = wp_create_nonce( "process-payment" );

        include( ud_get_wp_invoice()->path('lib/gateways/templates/', 'dir') . 'payment_header.tpl.php' );
        include( ud_get_wp_invoice_usa_epay()->path('static/views/', 'dir') . 'usa_epay_frontend.php' );
        include( ud_get_wp_invoice()->path('lib/gateways/templates/', 'dir') . 'payment_footer.tpl.php');
      }

      /**
       * Show settings for RB. Nothing in case of InterKassa
       * @param type $invoice
       */
      function recurring_settings($invoice) {
        ?>
        <h4><?php _e('USA ePay Recurring Billing', ud_get_wp_invoice_usa_epay()->domain); ?></h4>
        <p><?php _e('Currently gateway does not support Recurring Billing', ud_get_wp_invoice_usa_epay()->domain); ?></p>
        <?php
      }

      /**
       * Fields renderer for PPP
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
                <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), ud_get_wp_invoice_usa_epay()->domain); ?></li>
                <?php
                $html = ob_get_clean();
                echo $html;

                //** For each field */
                foreach ($value as $field_slug => $field_data) {

                  //** Change field properties if we need */
                  $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_usa_epay');
                  $html = '';

                  ob_start();

                  switch ($field_data['type']) {
                    case self::TEXT_INPUT_TYPE:
                      ?>

                      <li class="wpi_checkout_row">
                        <div class="control-group">
                          <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice_usa_epay()->domain); ?></label>
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
                        <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice_usa_epay()->domain); ?></label>
                        <?php echo \WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                      </li>

                      <?php
                      $html = ob_get_clean();

                      break;

                    case self::SELECT_COUNTRIES_TYPE:
                      ?>

                      <li class="wpi_checkout_row">
                        <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice_usa_epay()->domain); ?></label>
                        <?php echo \WPI_UI::select("name={$field_data['name']}&values=countries&id={$field_slug}&class={$field_data['class']}"); ?>
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
         * Process Payment
         */
        static function process_payment() {
          global $invoice;

          //** Response */
          $response = array(
            'success' => false,
            'error' => false,
            'data' => null
          );

          $data = array();

          $gateway_settings = $invoice['billing']['wpi_usa_epay']['settings'];
          $invoice_id = $invoice['invoice_id'];

          try {

            $request = new umTransaction();
            
            $request->key    = $gateway_settings['key']['value'];
            $request->pin    = $gateway_settings['pin']['value'];
            $request->usesandbox = $gateway_settings['usesandbox']['value'] == 'Y';
            $request->ip     = $_SERVER['REMOTE_ADDR'];
            $request->testmode = 0;
            $request->command = "cc:sale";
            
            $request->amount = $_REQUEST['amount'];
            $request->card   = $_REQUEST['acct'];
            $request->exp    = $_REQUEST['exp_m'] . $_REQUEST['exp_y'];
            $request->cvv2   = $_REQUEST['cvv2'];
            $request->billfname = $_REQUEST['first_name'];
            $request->billlname = $_REQUEST['last_name'];
            $request->billstreet = $_REQUEST['street'];
            $request->billcity = $_REQUEST['city'];
            $request->billstate = $_REQUEST['state'];
            $request->billzip = $_REQUEST['zip'];
            $request->billcountry = $_REQUEST['countrycode'];
            $request->email = $_REQUEST['email'];
            $request->currency = $_REQUEST['currency_code'];
            $request->description = $invoice['post_title'];
            $request->invoice = $invoice_id;
            
            $request->Process();
            
            $response['gateway_response'] = $request;

            if ($request->result === 'Approved') {

              $wp_users_id = $invoice['user_data']['ID'];

              //** update user data */
              update_user_meta($wp_users_id, 'last_name', !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : '' );
              update_user_meta($wp_users_id, 'first_name', !empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : '' );
              update_user_meta($wp_users_id, 'city', !empty($_REQUEST['city']) ? $_REQUEST['city'] : '' );
              update_user_meta($wp_users_id, 'state', !empty($_REQUEST['state']) ? $_REQUEST['state'] : '' );
              update_user_meta($wp_users_id, 'zip', !empty($_REQUEST['zip']) ? $_REQUEST['zip'] : '' );
              update_user_meta($wp_users_id, 'streetaddress', !empty($_REQUEST['street']) ? $_REQUEST['street'] : '' );
              update_user_meta($wp_users_id, 'phonenumber', !empty($_REQUEST['phone']) ? $_REQUEST['phone'] : '' );
              update_user_meta($wp_users_id, 'country', !empty($_REQUEST['countrycode']) ? $_REQUEST['countrycode'] : '' );
              
              if ( !empty( $_REQUEST['crm_data'] ) ) {
                self::user_meta_updated( $_REQUEST['crm_data'] );
              }

              $invoice_obj = new \WPI_Invoice();
              $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

              $amount = (float)$request->authamount;
              
              //** Add payment amount */
              $event_note = \WPI_Functions::currency_format($amount, $invoice['invoice_id']) . __(" paid via USA ePay", ud_get_wp_invoice_usa_epay()->domain);
              $event_amount = $amount;
              $event_type = 'add_payment';

              $event_note = urlencode($event_note);
              
              //** Log balance changes */
              $invoice_obj->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              
              //** Log client IP */
              $success = __("Successfully processed by ", ud_get_wp_invoice_usa_epay()->domain).$_SERVER['REMOTE_ADDR'];
              $invoice_obj->add_entry("attribute=invoice&note=$success&type=update");
              
              //** Log payer */
              $payer_card = __("USA ePay Reference Number: ", ud_get_wp_invoice_usa_epay()->domain).$request->refnum;
              $invoice_obj->add_entry("attribute=invoice&note=$payer_card&type=update");

              $invoice_obj->save_invoice();
              
              //** Mark invoice as paid */
              wp_invoice_mark_as_paid($invoice_id, $check = true);

              parent::successful_payment( $invoice_obj );

              send_notification( $invoice );
              
              $data['messages'][] = __( 'Successfully paid. Thank you.', ud_get_wp_invoice_usa_epay()->domain );
              $response['success'] = true;
              $response['error'] = false;
              
            } else {
              
              $data['messages'][] = $request->error;
              $response['success'] = false;
              $response['error'] = true;
              
            }
          } catch (\Exception $e) {

            $response['error'] = true;
            $data['messages'][] = $e->getMessage();
          }

          $response['data'] = $data;
          die(json_encode($response));
        }
        
        /**
         * Server Callback
         */
        public static function server_callback() {
          die('Nothing to do here.');
        }

      }

    }
  }
