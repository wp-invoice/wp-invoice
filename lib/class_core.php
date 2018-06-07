<?php

/**
 * 
 */
class WPI_Core {

  /**
   * Singleton
   * @var type 
   */
  static private $instance = NULL;
  
  /**
   * User
   * @var type 
   */
  public $User;
  
  /**
   * Settings
   * @var type 
   */
  public $Settings;
  
  /**
   * Functions
   * @var type 
   */
  public $Functions;
  
  /**
   * UI
   * @var type 
   */
  public $UI;
  
  /**
   * Ajax
   * @var type 
   */
  public $Ajax;
  
  /**
   * WPI Version
   * @var type 
   */
  public $version = WP_INVOICE_VERSION_NUM;
  
  /**
   * URI
   * @var type 
   */
  public $uri;
  
  /**
   * The path
   * @var type 
   */
  public $the_path;
  
  /**
   * Front path
   * @var type 
   */
  public $frontend_path;
  
  /**
   * Page link
   * @var type 
   */
  public $page_link;
  
  /**
   * Links
   * @var type 
   */
  public $links;
  
  /**
   * UI path
   * @var type 
   */
  public $ui_path;
  
  /**
   * Current user
   * @var type 
   */
  public $current_user;
  
  /**
   * Options
   * @var type 
   */
  public $options;
  
  /**
   * Plugin base name
   * @var type 
   */
  public $plugin_basename;
  
  /**
   * Path
   * @var type 
   */
  public $path;
  
  /**
   * File
   * @var type 
   */
  public $file;
  
  /**
   * Directory
   * @var type 
   */
  public $directory;
  
  /**
   * User prefs
   * @var type 
   */
  public $user_preferences;

  /**
   * CRM Notification actions
   * @var array 
   */
  static public $crm_notification_actions = array(
      'wpi_send_thank_you_email' => 'WPI: Invoice Paid (Client Receipt)',
      'wpi_cc_thank_you_email' => 'WPI: Invoice Paid (Notify Administrator)',
      'wpi_send_invoice_creator_email' => 'WPI: Invoice Paid (Notify Creator)',
  );

  /**
   * Singleton.
   * @since 3.0
   */
  static function getInstance() {
    return is_null(self::$instance) ? self::$instance = new WPI_Core() : self::$instance;
  }

  /**
   * Constructor
   * @since 3.0
   */
  function __construct() {
    global $wpdb, $wpi_settings;

    //** Load settings class and options */
    $this->Settings = new WPI_Settings($this);
    $this->options = $this->Settings->options;

    //** Load settings into global variable */
    $wpi_settings = $this->options;

    //** Load other classes */
    $this->UI = new WPI_UI($this);
    $this->Functions = new WPI_Functions($this);
    $this->Ajax = new WPI_Ajax($this);

    //** Set basic variables */
    $this->plugin_basename = plugin_basename(__FILE__);
    $this->path = dirname(__FILE__);
    $this->file = basename(__FILE__);
    $this->directory = basename($this->path);
    $this->uri = WP_PLUGIN_URL . "/" . $this->directory;
    $this->the_path = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__));
    $this->frontend_path = ( $wpi_settings['force_https'] == 'true' ? str_replace('http://', 'https://', $this->the_path) : $this->the_path );

    //** Set additional dynamic settings */
    $wpi_settings['frontend_path'] = $this->frontend_path;
    $wpi_settings['links']['overview_page'] = 'admin.php?page=wpi_main';
    $wpi_settings['links']['settings_page'] = 'admin.php?page=wpi_page_settings';
    $wpi_settings['links']['manage_invoice'] = 'admin.php?page=wpi_page_manage_invoice';

    //** Load Payment gateways */
    $this->Functions->load_gateways();
      //** Preload WPLT */
      new \UsabilityDynamics\WPLT\Bootstrap();

    //** Load the rest at the init level */
    add_action('init', array($this, 'init'), 0);
  }

  /**
   * Loaded at 'init' action
   * @since 3.0
   */
  function init() {
    global $user_ID, $wpi_settings;
    
    $wpi_settings['admin']['ui_path'] = $this->path . "/ui/";

    //** Download backup of configuration BEFORE any additional info added to it by filters */
    if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpi_page_settings' && isset($_REQUEST['wpi_action']) && $_REQUEST['wpi_action'] == 'download-wpi-backup' && wp_verify_nonce($_REQUEST['_wpnonce'], 'download-wpi-backup')
    ) {

      $sitename = sanitize_key(get_bloginfo('name'));
      $filename = $sitename . '-wp-invoice.' . date('Y-m-d') . '.txt';

      header("Cache-Control: public");
      header("Content-Description: File Transfer");
      header("Content-Disposition: attachment; filename=$filename");
      header("Content-Transfer-Encoding: binary");
      header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);

      echo json_encode($wpi_settings);

      die();
    }

    //** Action to use to hook in before init */
    do_action('wpi_pre_init');

    add_action('admin_head', array($this, 'admin_head'));
    //** Generate and display WP-Invoice notices on admin panel */
    add_action('admin_notices', array($this, 'admin_notices'));

    //** update invoices types */
    $wpi_settings['types'] = apply_filters('wpi_object_types', $wpi_settings['types']);

    //** Run Everytime
    $this->Functions->register_post_type();

    add_action("wpi_contextual_help", array('WPI_UI', "wpi_contextual_help"));
    add_action("admin_enqueue_scripts", array('WPI_UI', "admin_enqueue_scripts"), 0);
    add_action("admin_enqueue_scripts", array('WPI_UI', "admin_print_styles"), 0);

    add_action('template_redirect', array($this, 'template_redirect'), 0);
    add_action('profile_update', array('WPI_Functions', 'save_update_profile'), 10, 2);
    add_action('profile_update', array('WPI_Functions', 'protect_user_invoices'), 10, 2);
    add_action('edit_user_profile', array($this->UI, 'display_user_profile_fields'));
    add_action('show_user_profile', array($this->UI, 'display_user_profile_fields'));

    add_action('admin_menu', array('WPI_UI', 'admin_menu'), 9);
    add_action('admin_init', array($this, 'admin_init'));

    if ( isset( $wpi_settings['tos_checkbox'] ) && $wpi_settings['tos_checkbox'] == 'true' ) {
      add_action('wpi_after_payment_fields', array('WPI_UI', 'terms_checkbox'));
      add_action('wpi_before_process_payment', array('wpi_gateway_base', 'handle_terms_acceptance'));
    }

    add_action('wp_ajax_wpi_get_user_date', function(){
      die(WPI_Ajax::get_user_date($_REQUEST["user_email"]));
    });
    add_action('wp_ajax_wpi_process_manual_event', array('WPI_Ajax', 'process_manual_event'));
    add_action('wp_ajax_wpi_get_notification_email', array('WPI_Ajax', 'get_notification_email'));
    add_action('wp_ajax_wpi_save_invoice', array('WPI_Ajax', 'save_invoice'));
    add_action('wp_ajax_wpi_get_status', array($this->Ajax, 'show_invoice_status'));
    add_action('wp_ajax_wpi_get_charges', array($this->Ajax, 'show_invoice_charges'));
    add_action('wp_ajax_wpi_send_notification', array('WPI_Ajax', 'send_notification'));
    add_action('wp_ajax_nopriv_wpi_gateway_process_payment', array('wpi_gateway_base', 'process_payment'));
    add_action('wp_ajax_wpi_gateway_process_payment', array('wpi_gateway_base', 'process_payment'));
    add_action('wp_ajax_nopriv_wpi_front_change_payment_form_ajax', array('wpi_gateway_base', 'change_payment_form_ajax'));
    add_action('wp_ajax_wpi_front_change_payment_form_ajax', array('wpi_gateway_base', 'change_payment_form_ajax'));
    add_action('wp_ajax_nopriv_wpi_gateway_server_callback', array('wpi_gateway_base', 'server_callback'));
    add_action('wp_ajax_wpi_gateway_server_callback', array('wpi_gateway_base', 'server_callback'));
    add_action('wp_ajax_wpi_install_custom_templates', array('WPI_Ajax', 'install_templates'));
    add_action('wp_ajax_wpi_user_autocomplete_handler', array('WPI_Ajax', 'user_autocomplete_handler'));
    add_action('wp_ajax_wpi_template_autocomplete_handler', array('WPI_Ajax', 'template_autocomplete_handler'));
    add_action('wp_ajax_wpi_search_email', array('WPI_Ajax', 'search_email'));
    add_action('wp_ajax_wpi_search_recipient', array('WPI_Ajax', 'search_recipient'));

    //** WP-CRM integration */
    add_action('wpi_integrate_crm_user_panel', array('WPI_UI', 'crm_user_panel'));
    if (class_exists('WP_CRM_Core')) {
      add_action('wp_crm_data_structure_attributes', array('WPI_UI', 'wp_crm_data_structure_attributes'));
      add_filter('wpi_crm_custom_fields', array('WPI_Functions', 'wpi_crm_custom_fields'), 10, 2);

      //** Contextual Help for CRM */
      add_filter('crm_page_wp_crm_settings_help', array('WPI_UI', 'wp_crm_contextual_help'));

      //** Add CRM notification fire action */
      add_filter("wp_crm_notification_actions", array('WPI_Functions', 'wpi_crm_custom_notification'));
      add_filter('wp_crm_entry_type_label', array('WPI_Functions', 'wp_crm_entry_type_label'), 10, 2);
    }

    add_action('the_post', array('WPI_Functions', 'the_post'));

    add_shortcode('wp-invoice-lookup', 'wp_invoice_lookup');
    add_shortcode('wp-invoice-history', 'wp_invoice_history');

    //** Load invoice lookup widget */
    add_action('widgets_init', function(){
      return register_widget("InvoiceLookupWidget");
    });

    //** load user's invoice history widget */
    add_action('widgets_init', function(){
      return register_widget("InvoiceHistoryWidget");
    });

    add_action('wpi_invoice_object_delete', array('WPI_Functions', 'delete_invoice_log'));

    //** Find out if a wpi directory exists in template folder and use that, if not, use default template */
    $wpi_settings['frontend_template_path'] = $this->Functions->template_path();
    $wpi_settings['default_template_path'] = ud_get_wp_invoice()->path( 'static/views/', 'dir' );

    //** has to be set here, WPI_Core is loaded too early */
    $this->current_user = $user_ID;
    $this->user_preferences['manage_page'] = get_user_meta($user_ID, 'wp_invoice_ui_manage_page');
    $this->user_preferences['main'] = get_user_meta($user_ID, 'wp_invoice_ui_main');

    if (!get_user_option("screen_layout_admin_page_wpi_invoice_edit")) {
      update_user_option($user_ID, 'screen_layout_admin_page_wpi_invoice_edit', 2, true);
    }

    if (!get_user_option("wpi_ui_display_global_tax")) {
      update_user_option($user_ID, 'wpi_ui_display_global_tax', 'true', true);
    }

    if (!get_user_option("wpi_ui_display_itemized_tax")) {
      update_user_option($user_ID, 'wpi_ui_display_itemized_tax', 'false', true);
    }

    if (!get_user_option("wpi_ui_payment_method_options")) {
      update_user_option($user_ID, 'wpi_ui_payment_method_options', 'true', true);
    }

    if (!get_user_option("wpi_ui_currency_options")) {
      update_user_option($user_ID, 'wpi_ui_currency_options', 'true', true);
    }
            
    wp_register_script('jquery.bind', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.bind.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.maskedinput', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.maskedinput.js", 'url' ), array('jquery'), '1.4.1', true );
    wp_register_script('jquery.form', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.form.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.validate', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.validate-1.8.1.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.smookie', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.smookie.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.formatCurrency', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.formatCurrency.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.number.format', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.number.format.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.impromptu', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery-impromptu.1.7.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('jquery.field', ud_get_wp_invoice()->path( "static/scripts/vendor/jquery.field.min.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('wpi-gateways', ud_get_wp_invoice()->path( "lib/gateways/js/wpi_gateways.js.php", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('wpi.checkout', ud_get_wp_invoice()->path( "static/scripts/wpi-checkout.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );
    wp_register_script('wpi_select2_js', ud_get_wp_invoice()->path( "lib/third-party/select2/select2.js", 'url' ), array('jquery'), WP_INVOICE_VERSION_NUM, true );

    wp_register_script('jsapi', 'https://www.google.com/jsapi');
    
    wp_register_style('wpi_select2_css', ud_get_wp_invoice()->path( "lib/third-party/select2/select2.css", 'url' ), array());

    //** Masure dependancies are identified in case this script is included in other pages */
    wp_register_script('wp-invoice-events', ud_get_wp_invoice()->path( "static/scripts/wpi-events.js", 'url' ), array(
        'jquery',
        'jquery.formatCurrency',
        'jquery-ui-core'
    ));

    wp_register_script('wp-invoice-functions', ud_get_wp_invoice()->path( "static/scripts/wpi-functions.js", 'url' ), array('wp-invoice-events'));

    //** Find and register theme-specific style if a custom wp_properties.css does not exist in theme */
    if (!$this->Functions->is_true($wpi_settings['do_not_load_theme_specific_css']) && $this->Functions->has_theme_specific_stylesheet()) {
      wp_register_style('wpi-theme-specific', ud_get_wp_invoice()->path( "static/views/theme-specific/".get_option('template').".css", 'url' ), array(), WP_INVOICE_VERSION_NUM);
    }

    if ($this->Functions->is_true($wpi_settings['use_css'])) {
      wp_register_style('wpi-default-style', ud_get_wp_invoice()->path( "static/views/wpi-default-style.css", 'url' ), array(), WP_INVOICE_VERSION_NUM);
    }

    /**
     * Add Publish Later option to publish box
     */
    add_action( 'wpi_publish_options', function($this_invoice) {
      global $wpi_settings;
      $available_notifications = $wpi_settings['notification'];
      ob_start();
      ?>
        <script type="text/javascript">
          jQuery(document).ready(function(){
            jQuery(document).on( 'change', '.wpi_wpi_invoice_publish_later_', function() {
              if ( jQuery(this).is(':checked') ) {
                jQuery('.wpi_publish_later_date_time').show();
                jQuery('.wpi_publish_later_date_time input').attr('required','required');
              } else {
                jQuery('.wpi_publish_later_date_time').hide();
                jQuery('.wpi_publish_later_date_time input').removeAttr('required');
              }
            });
            jQuery('.wpi_wpi_invoice_publish_later_').change();
          });
        </script>
        <li class="wpi_publish_later">
          <?php echo WPI_UI::checkbox("name=wpi_invoice[publish_later]&value=true&label=".__('Publish Later', ud_get_wp_invoice()->domain), !empty($this_invoice['publish_later']) ? $this_invoice['publish_later'] : false); ?>
          <div style="padding-top:5px;" class="wpi_publish_later_date_time hidden">
            <div class="timestampdiv">
              <?php WPI_Functions::select_publish_time( $this_invoice ) ?>
            </div>
            <label>
              <?php _e( 'Client Notification', ud_get_wp_invoice()->domain ) ?>
              <select name="wpi_invoice[publish_later_notification]" class="widefat">
                <option value="0"><?php _e( 'Do not send any', ud_get_wp_invoice()->domain ) ?></option>
                <?php if ( !empty( $available_notifications ) && is_array( $available_notifications ) ): ?>
                    <?php foreach( $available_notifications as $notification_key => $notification ): ?>
                      <option <?php selected( $notification_key, !empty($this_invoice['publish_later_notification'])?$this_invoice['publish_later_notification']:false ) ?> value="<?php echo $notification_key ?>"><?php echo $notification['name'] ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </label>
          </div>
        </li>
      <?php
      echo ob_get_clean();
    },9);

    /**
     * Handle Scheduling an invoice publish date
     */
    add_filter( 'wpi_invoice_pre_save', function( $invoice_object, $invoice_data ) {
      if ( !empty( $invoice_data['publish_later'] ) && $invoice_data['publish_later'] == 'on' ) {
        if ( empty( $invoice_data['publish_date_time'] ) || !is_array( $invoice_data['publish_date_time'] ) ) return $invoice_object;

        foreach( $invoice_data['publish_date_time'] as $date_part => $date_part_value ) {
          if ( !is_numeric( $date_part_value ) ) return $invoice_object;
        }

        $future_date_time = strtotime( $mysql_date_time = sprintf(
            '%3$s-%2$s-%1$s %4$s:%5$s:%6$s',
            $invoice_data['publish_date_time']['jj'],
            $invoice_data['publish_date_time']['mm'],
            $invoice_data['publish_date_time']['aa'],
            $invoice_data['publish_date_time']['hh'],
            $invoice_data['publish_date_time']['mn'],
            $invoice_data['publish_date_time']['ss']
        ) );

        if ( $future_date_time <= current_time( 'timestamp' ) ) return $invoice_object;

        $invoice_object->set(array(
          'post_status' => 'future',
          'publish_later' => 'on',
          'publish_date_time' => $invoice_data['publish_date_time'],
          'post_date' => $mysql_date_time,
          'post_date_gmt' => date( 'Y-m-d H:i:s', $future_date_time - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
          'publish_later_notification' => $invoice_data['publish_later_notification']
        ));

      } else {
        $invoice_object->set(array(
          'post_status' => 'active'
        ));
      }

      return $invoice_object;
    }, 10, 2);

    /**
     * Handle status change for invoice object
     */
    add_action( 'future_to_publish', function( $post ) {
      if ( $post && $post->post_type === 'wpi_object' ) {
        $post->post_status = 'active';
        $updated = wp_update_post( $post );
        if ( $updated && !is_wp_error( $updated ) ) {

          $notification = get_post_meta( $post->ID, 'publish_later_notification', 1 );
          if ( empty( $notification ) ) return false;

          if ( !is_callable( array( '\WPI_Functions', 'preprocess_notification_template' ) ) ) return false;

          $template = WPI_Functions::preprocess_notification_template( $notification, $post->ID );

          if ( !DOING_CRON ) return false;

          //** Setup, and send our e-mail */
          $headers = array( "From: " . get_bloginfo() . " <" . get_bloginfo( 'admin_email' ) . ">" );
          $message = html_entity_decode( $template->ary['NotificationContent'], ENT_QUOTES, 'UTF-8' );
          $subject = html_entity_decode( $template->ary['NotificationSubject'], ENT_QUOTES, 'UTF-8' );
          $to = $template->invoice['user_email'];

          //** Validate for empty fields data */
          if ( empty( $to ) || empty( $subject ) || empty( $message ) ) return false;

          WPI_Functions::maybe_override_mail_from();

          if ( wp_mail( $to, $subject, apply_filters( 'wpi_notification_message', $message, $to, $subject, absint( $template->invoice['invoice_id'] ) ), apply_filters( 'wpi_notification_headers', $headers, $to, $subject, absint( $template->invoice['invoice_id'] ) ) ) ) {
            $pretty_time = date( get_option( 'time_format' ) . " " . get_option( 'date_format' ), current_time( 'timestamp' ) );
            $text = __( "Notification Sent", ud_get_wp_invoice()->domain ) . " (" . $subject . ") " . __( 'to', ud_get_wp_invoice()->domain ) . " {$to} " . __( 'at', ud_get_wp_invoice()->domain ) . " {$pretty_time}.";
            WPI_Functions::log_event( $post->ID, 'invoice', 'notification', '', $text, time() );
          }

        }
      }
    });

    /**
     *
     */
    add_filter( 'wpi_custom_meta', function( $keys ) {
      if ( !is_array( $keys ) ) $keys = array();
      $keys[] = 'publish_later_notification';
      return $keys;
    });
  }

  /**
   * Pre-Header functions
   * Loads after admin_enqueue_scripts, admin_print_styles, and admin_head.
   * Loads before: favorite_actions, screen_meta
   * @since 3.0
   */
  function admin_head() {
    global $current_screen;

    do_action("wpi_pre_header_{$current_screen->id}", $current_screen->id);
  }

  /*
   * Render WPI Admin notices
   *
   * - WPI Notice should be added through 'prepare_admin_notices' filter.
   * - Notice Params:
   * - @param string type. Required. 'updated' or 'error'.
   * - @param string message. Required. Text of notice.
   * - @param string screen_id. Optional. Where the notice should be shown.
   *
   * @author Maxim Peshkov
   * @since 3.02
   */

  function admin_notices() {
    global $current_screen;

    //** Notice will be shown only on WPI admin pages */
    if ($current_screen->parent_base == "wpi_main") {
      $notices = apply_filters('prepare_admin_notices', array());

      foreach ($notices as $notice) {
        //** Determine if notice should be shown on specific WPI page */
        if (!empty($notice['screen_id']) && $current_screen->id != $notice['screen_id']) {
          continue;
        }

        if (!empty($notice['message'])) {
          echo "<div class=\"{$notice['type']}\">{$notice['message']}</div>";
        }
      }
    }
  }

  /**
   * Perform back-end administrative functions
   * @since 3.0
   */
  function admin_init() {
    global $wpi_settings;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      return;
    }

    //** Handle backup */
    if (isset($_FILES['wpi_settings']['tmp_name']['settings_from_backup']) && $backup_file = $_FILES['wpi_settings']['tmp_name']['settings_from_backup']) {
      $backup_contents = file_get_contents($backup_file);

      if (!empty($backup_contents))
        $decoded_settings = json_decode($backup_contents, true);

      if (!empty($decoded_settings))
        $_REQUEST['wpi_settings'] = $decoded_settings;
    }

    if (!empty($_REQUEST['wpi_settings']) && is_array($_REQUEST['wpi_settings'])) {
      $this->Settings->SaveSettings($_REQUEST['wpi_settings']);
      WPI_Functions::settings_action();
    }

    //** Add metaboxes */
    if (isset($wpi_settings['pages']) && is_array($wpi_settings['pages'])) {
      $this->add_metaboxes($wpi_settings['pages']);
    }

    //** Check for updates */
    WPI_Functions::manual_activation();
  }

  /**
   * Add metaboxes to WPI pages
   * @param type $screens
   */
  function add_metaboxes($screens) {
    foreach ($screens as $screen) {
      if (!class_exists($screen)) {
        continue;
      }

      $location_prefixes = array('side_', 'normal_', 'advanced_');

      foreach (get_class_methods($screen) as $box) {
        //** Set context and priority if specified for box */
        $context = 'normal';

        if (strpos($box, "side_") === 0) {
          $context = 'side';
        }
        if (strpos($box, "advanced_") === 0) {
          $context = 'advanced';
        }

        //** Get name from slug */
        $label = WPI_Functions::slug_to_label(str_replace($location_prefixes, '', $box));

        add_meta_box($box, $label, array($screen, $box), $screen, $context, 'high');
      }
    }
  }

  /**
   * Define types that are viewable
   * @return array
   * @filter wpi_viewable_types
   */
  function viewable_types() {
    return apply_filters( 'wpi_viewable_invoice_types', array('paid', 'active', 'pending', 'refund') );
  }

  /**
   * Handles validation when somebody is attempting to view an invoice.
   * If validation is passsed, we add the necessary
   * filters to display the invoice header and page content;
   * Global $invoice_id variable set by WPI_Functions::validate_page_hash();
   */
  function template_redirect() {
    global $invoice_id, $wpi_settings, $wpi_invoice_object, $post, $current_user;

    //** Alwys load styles without checking if given page has an invoice */
    wp_enqueue_style('wpi-theme-specific');
    wp_enqueue_style('wpi-default-style');

    //** Determine if the current page is invoice's page */
    if (empty($post->ID) || $wpi_settings['web_invoice_page'] != $post->ID) {
      return;
    }

    //** If invoice_id is passed, run validate_page_hash  to make sure this is the right page and invoice_id exists */
    if (isset($_GET['invoice_id'])) {

      if (WPI_Functions::validate_page_hash(esc_sql($_GET['invoice_id']))) {

        //** load global invoice object */
        $post_id = wpi_invoice_id_to_post_id($invoice_id);

        $wpi_invoice_object = new WPI_Invoice();
        $wpi_invoice_object->load_invoice("id=$post_id");

        add_filter('viewable_invoice_types', array($this, 'viewable_types'));

        //** Determine if current invoice object is "viewable" */
        if (!in_array($wpi_invoice_object->data['post_status'], apply_filters('viewable_invoice_types', array('active')))) {
          return;
        }

        if (isset($wpi_settings['logged_in_only']) && $wpi_settings['logged_in_only'] == 'true') {
          if (!current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) && !WPI_Functions::user_is_invoice_recipient($wpi_invoice_object)) {
            //** Show 404 when invoice doesn't exist */
            $not_found = get_query_template('404');
            if ( empty( $not_found ) ) {
              $not_found = get_query_template('index');
            }
            require_once $not_found;
            die();
          }
        }

        do_action('wpi_template_redirect', $wpi_invoice_object);

        //** Load front end scripts */
        wp_enqueue_script('jquery.validate');
        wp_enqueue_script('wpi-gateways');
        wp_enqueue_script('jquery.maskedinput');
        wp_enqueue_script('wpi-frontend-scripts');

        if (!empty($wpi_settings['ga_event_tracking']) && $wpi_settings['ga_event_tracking']['enabled'] == 'true') {
          wp_enqueue_script('wpi-ga-tracking', ud_get_wp_invoice()->path( "static/scripts/wpi.ga.tracking.js", 'url' ), array('jquery'));
        }

        //** Apply Filters to the invoice description */
        add_action('wpi_description', 'wpautop');
        add_action('wpi_description', 'wptexturize');
        add_action('wpi_description', 'shortcode_unautop');
        add_action('wpi_description', 'convert_chars');
        add_action('wpi_description', 'capital_P_dangit');

        //** Declare the variable that will hold our AJAX url for JavaScript purposes */
        wp_localize_script('wpi-gateways', 'wpi_ajax', array('url' => admin_url('admin-ajax.php')));

        add_action('wp_head', array('WPI_UI', 'frontend_header'));

        if ($wpi_settings['replace_page_title_with_subject'] == 'true' || $wpi_settings['hide_page_title'] == 'true') {
          add_action('wp_title', array('WPI_UI', 'wp_title'), 0, 3);
        }

        if ($wpi_settings['replace_page_heading_with_subject'] == 'true' || $wpi_settings['hide_page_title'] == 'true') {
          add_action('the_title', array('WPI_UI', 'the_title'), 0, 2);
        }

        add_action('the_content', array('WPI_UI', 'the_content'), 20);
      } else {
        //** Show 404 when invoice doesn't exist */
        $not_found = get_query_template('404');
        require_once $not_found;
        die();
      }
    }

    //** Fixed WordPress filters if page is being opened in HTTPS mode */
    if ( is_ssl() && function_exists('force_ssl')) {
      add_filter('option_siteurl', 'force_ssl');
      add_filter('option_home', 'force_ssl');
      add_filter('option_url', 'force_ssl');
      add_filter('option_wpurl', 'force_ssl');
      add_filter('option_stylesheet_url', 'force_ssl');
      add_filter('option_template_url', 'force_ssl');
      add_filter('script_loader_src', 'force_ssl');
    }

    //** Lookup functionality */
    if (isset($_POST['wp_invoice_lookup_input'])) {

      if (!empty($current_user->ID)) {
        $id = get_invoice_id($_POST['wp_invoice_lookup_input']);

        if (empty($id)) {
          //** Show 404 when invoice doesn't exist */
          $not_found = get_query_template('404');
          require_once $not_found;
          die();
        }
        $invoice = get_invoice($id);

        if (current_user_can('level_10') || $current_user->data->user_email == $invoice['user_email']) {

          header("location:" . get_invoice_permalink($_POST['wp_invoice_lookup_input']));
          die();
        } else {
          //** Show 404 when invoice doesn't exist */
          $not_found = get_query_template('404');
          require_once $not_found;
          die();
        }
      } else {
        //** Show 404 when invoice doesn't exist */
        $not_found = get_query_template('404');
        require_once $not_found;
        die();
      }
    }
  }
}