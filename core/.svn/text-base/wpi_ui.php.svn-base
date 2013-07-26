<?php

/**
  Handles functions that are related to the user interface
 */
class WPI_UI {

  /**
   * Sets up plugin pages and loads their scripts
   *
   * @since 3.0
   *
   */
  function admin_menu() {
    global $wpi_settings, $submenu, $wp_version;

    //unset($submenu['edit.php?post_type=wpi_object'][10]);

    /* Get capability required for this plugin's menu to be displayed to the user */
    $capability = self::get_capability_by_level($wpi_settings['user_level']);

    $wpi_settings['pages']['main'] = add_object_page(__('Invoice', WPI), 'Invoice', $capability, 'wpi_main', array('WPI_UI', 'page_loader'), WPI_URL . "/core/css/images/wp_invoice.png");
    $wpi_settings['pages']['main'] = add_submenu_page('wpi_main', __('View All', WPI), __('View All', WPI), $capability, 'wpi_main', array('WPI_UI', 'page_loader'));
    $wpi_settings['pages']['edit'] = add_submenu_page('wpi_main', __('Add New', WPI), __('Add New', WPI), $capability, 'wpi_page_manage_invoice', array('WPI_UI', 'page_loader'));
    $wpi_settings['pages']['reports'] = add_submenu_page('wpi_main', __('Reports', WPI), __('Reports', WPI), $capability, 'wpi_page_reports', array('WPI_UI', 'page_loader'));

    $wpi_settings['pages'] = apply_filters('wpi_pages', $wpi_settings['pages']);

    $wpi_settings['pages']['settings'] = add_submenu_page('wpi_main', __('Settings', WPI), __('Settings', WPI), $capability, 'wpi_page_settings', array('WPI_UI', 'page_loader'));

    /* Update screens information */
    WPI_Settings::setOption('pages', $wpi_settings['pages']);

    // Add Actions
    add_action('load-' . $wpi_settings['pages']['main'], array('WPI_UI', 'pre_load_main_page'));
    add_action('load-' . $wpi_settings['pages']['edit'], array('WPI_UI', 'pre_load_edit_page'));
    add_action('load-' . $wpi_settings['pages']['reports'], array('WPI_UI', 'pre_load_reports_page'));
    add_action('load-' . $wpi_settings['pages']['settings'], array('WPI_UI', 'pre_load_settings_page'));

    //* Load common actions on all WPI pages */
    foreach ($wpi_settings['pages'] as $page_slug) {
      add_action('load-' . $page_slug, array('WPI_UI', 'common_pre_header'));
    }

    // Add Filters
    add_filter('wpi_page_loader_path', array('WPI_UI', "wpi_display_user_selection"), 0, 3);
    add_filter('wpi_pre_header_invoice_page_wpi_page_manage_invoice', array('WPI_UI', "page_manage_invoice_preprocess"));
  }

  /**
   * Get capability required for this plugin's menu to be displayed to the user.
   * It's used for setting this plugin's menu Capability.
   *
   * For more capability details: http://codex.wordpress.org/Roles_and_Capabilities
   *
   * @param int/string $level. Role's level number
   * @retun string. Unique User Level's capability
   * @since 3.0
   * @author Maxim Peshkov
   */
  function get_capability_by_level($level) {
    $capability = '';
    switch ($level) {
      /* Subscriber */
      case '0':
        $capability = 'read';
        break;
      /* Contributor */
      case '1':
        $capability = 'edit_posts';
        break;
      /* Author */
      case '2':
        $capability = 'publish_posts';
        break;
      /* Editor */
      case '5':
        $capability = 'edit_pages';
        break;
      /* Administrator */
      case '8':
      default:
        $capability = 'manage_options';
        break;
    }
    return $capability;
  }

  /**
   * Displays a dropdown of predefined items.
   *
   * @since 3.0
   */
  function get_predefined_item_dropdown($args = '') {
    global $wpi_settings;

    if (empty($wpi_settings['predefined_services'])) {
      return;
    }


    //** Extract passed args and load defaults */
    extract(wp_parse_args($args, array(
                'input_name' => 'wpi[itemized_item][]',
                'input_class' => 'wpi_itemized_item',
                'input_id' => 'wpi_itemized_item',
                'input_style' => ''
            )), EXTR_SKIP);


    $return[] = "<select name='{$input_name}'  class='{$input_class}'  id='{$input_id}' style='{$input_style}' >";
    $return[] = '<option value=""></option>';

    foreach ($wpi_settings['predefined_services'] as $itemized_item) {


      if (empty($itemized_item['name'])) {
        $empty_rows[] = true;
        continue;
      }

      $return[] = "<option value='" . esc_attr($itemized_item['name']) . "' tax='{$itemized_item['tax']}' price='{$itemized_item['price']}'>{$itemized_item['name']}</option>";
    }
    $return[] = '</select>';


    if (count($empty_rows) == count($wpi_settings['predefined_services'])) {
      return false;
    }

    return implode('', $return);
  }

  /**
   * Displays a field for user selection, includes user array in json format, and the jQuery autocomplete() function.
   *
   * @since 3.0
   */
  function draw_user_auto_complete_field($args = '') {
    global $wpi_settings, $wpdb, $wp_scripts;
    wp_enqueue_script('jquery-ui-autocomplete');

    //** Extract passed args and load defaults */
    extract(wp_parse_args($args, array(
                'input_name' => 'wpi[new_invoice][user_email]',
                'input_class' => 'input_field',
                'input_id' => 'wp_invoice_userlookup',
                'input_style' => ''
            )), EXTR_SKIP);
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        jQuery("#<?php echo $input_id; ?>").autocomplete({
          source:ajaxurl+'?action=wpi_user_autocomplete_handler',
          minLength: 3
        });
        jQuery("#<?php echo $input_id; ?>").focus();
      });
    </script>
    <input type="text" name="<?php echo $input_name; ?>" class="<?php echo $input_class; ?>" id="<?php echo $input_id; ?>"  style="<?php echo $input_style; ?>" />
    <?php
  }

  /**
   * Common pre-header loader function for all WPI pages added in admin_menu()
   *
   * All back-end pages call this function, which then determines that UI to load below the headers.
   *
   * @since 3.0
   */
  function common_pre_header() {
    global $current_screen;

    $browser = WPI_Functions::browser();
    $screen_id = $current_screen->id;

    if (!$screen_id) {
      return;
    }

    //* Load Global Script and CSS Files */
    if (file_exists(WPI_Path . '/core/css/jquery-ui-1.8.21.custom.css')) {
      wp_register_style('wpi-custom-jquery-ui', WPI_URL . '/core/css/jquery-ui-1.8.21.custom.css');
    }

    if (file_exists(WPI_Path . '/core/css/wpi-admin.css')) {
      wp_register_style('wpi-admin-css', WPI_URL . '/core/css/wpi-admin.css', array(), WP_INVOICE_VERSION_NUM);
    }

    //* Load Page Conditional Script and CSS Files if they exist*/
    if (file_exists(WPI_Path . "/core/css/{$screen_id}.css")) {
      wp_register_style('wpi-this-page-css', WPI_URL . "/core/css/{$screen_id}.css", array('wpi-admin-css'), WP_INVOICE_VERSION_NUM);
    }

    //* Load IE 7 fix styles */
    if (file_exists(WPI_Path . "/core/css/ie7.css") && $browser['name'] == 'ie' && $browser['version'] == 7) {
      wp_register_style('wpi-ie7', WPI_URL . "/core/css/ie7.css", array('wpi-admin-css'), WP_INVOICE_VERSION_NUM);
    }

    //* Load Page Conditional Script and CSS Files if they exist*/
    if (file_exists(WPI_Path . "/core/js/{$screen_id}.js")) {
      wp_register_script('wpi-this-page-js', WPI_URL . "/core/js/{$screen_id}.js", array('wp-invoice-events'), WP_INVOICE_VERSION_NUM);
    }

    //* Load Conditional Metabox Files */
    if (file_exists(WPI_Path . "/core/ui/metabox/{$screen_id}.php")) {
      include_once WPI_Path . "/core/ui/metabox/{$screen_id}.php";
    }
  }

  /**
   * Used for loading back-end UI
   * All back-end pages call this function, which then determines that UI to load below the headers.
   * @since 3.0
   */
  function page_loader() {
    global $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID, $this_invoice, $wpi_settings, $wpi;

    $screen_id = $current_screen->id;

    /**
     * If plugin just installed
     */
    if ($wpi_settings['first_time_setup_ran'] == 'false') {
      $file_path = apply_filters('wpi_page_loader_path', WPI_Path . "/core/ui/first_time_setup.php", 'first_time_setup', WPI_Path . "/core/ui/");
    } else {
      /**
       * Check if 'web_invoice_page' exists
       * and show warning message if not.
       * and also check that the web_invoice_page is a real page
       */
      if (empty($wpi_settings['web_invoice_page'])) {
        echo '<div class="error"><p>' . sprintf(__('Invoice page not selected. Visit <a href="%s">settings page</a> to configure.', WPI), 'admin.php?page=wpi_page_settings') . '</p></div>';
      } else {
        if (!$wpdb->get_var("SELECT post_name FROM {$wpdb->posts} WHERE ID = {$wpi_settings['web_invoice_page'] }")) {
          echo '<div class="error"><p>' . sprintf(__('Selected invoice page does not exist. Visit <a href="%s">settings page</a> to configure.', WPI), 'admin.php?page=wpi_page_settings') . '</p></div>';
        }
      }
      $file_path = apply_filters('wpi_page_loader_path', WPI_Path . "/core/ui/{$current_screen->base}.php", $current_screen->base, WPI_Path . "/core/ui/");
    }

    if (file_exists($file_path))
      include $file_path;
    else
      echo "<div class='wrap'><h2>" . __('Error', WPI) . "</h2><p>" . __('Template not found:', WPI) . $file_path . "</p></div>";
  }

  /**
   * Hook.
   * Check Request before Manage Page will be loaded.
   *
   * @since 3.0
   */
  function pre_load_edit_page() {
    global $wpi_settings;

    if (!empty($_REQUEST['wpi']) && !empty($_REQUEST['wpi']['existing_invoice'])) {
      $id = (int) $_REQUEST['wpi']['existing_invoice']['invoice_id'];
      if (!empty($id) && !empty($_REQUEST['action'])) {
        self::process_invoice_actions($_REQUEST['action'], $id);
      }
    }

    /** Screen Options */
    if (function_exists('add_screen_option')) {
      add_screen_option('layout_columns', array('max' => 2, 'default' => 2));
    }

    //** Default Help items */
    $contextual_help['Creating New Invoice'][] = '<h3>' . __('Creating New Invoice', WPI) . '</h3>';
    $contextual_help['Creating New Invoice'][] = '<p>' . __("Begin typing the recipient's email into the input box, or double-click to view list of possible options.", WPI) . '</p>';
    $contextual_help['Creating New Invoice'][] = '<p>' . __("For new prospects, type in a new email address.", WPI) . '</p>';

    //** Hook this action is you want to add info */
    $contextual_help = apply_filters('wpi_edit_page_help', $contextual_help);

    do_action('wpi_contextual_help', array('contextual_help' => $contextual_help));
  }

  /**
   * Hook.
   * Check Request before Main (Overview) Page will be loaded.
   *
   * @since 3.0
   */
  function pre_load_main_page() {
    global $wpi_settings, $wpdb;

    /* Process Bulk Actions */
    if (!empty($_REQUEST['post']) && !empty($_REQUEST['action'])) {
      self::process_invoice_actions($_REQUEST['action'], $_REQUEST['post']);
    } else if (!empty($_REQUEST['delete_all']) && $_REQUEST['post_status'] == 'trash') {
      /* Get all trashed invoices */
      $ids = $wpdb->get_col("
        SELECT `ID`
        FROM `{$wpdb->posts}`
        WHERE `post_type` = 'wpi_object'
        AND `post_status` = 'trash'
      ");

      /* Determine if trashed invoices exist we remove them */
      if (!empty($ids)) {
        self::process_invoice_actions('delete', $ids);
      }
    }

    //** Action Messages */
    if (!empty($_REQUEST['invoice_id'])) {
      $invoice_ids = str_replace(',', ', ', $_REQUEST['invoice_id']);
      //** Add Messages */
      if (isset($_REQUEST['trashed'])) {
        WPI_Functions::add_message(sprintf(__('"Invoice(s) %s trashed."', WPI), $invoice_ids));
      } elseif (isset($_REQUEST['untrashed'])) {
        WPI_Functions::add_message(sprintf(__('"Invoice(s) %s untrashed."', WPI), $invoice_ids));
      } elseif (isset($_REQUEST['deleted'])) {
        WPI_Functions::add_message(sprintf(__('"Invoice(s) %s deleted."', WPI), $invoice_ids));
      } elseif (isset($_REQUEST['unarchived'])) {
        WPI_Functions::add_message(sprintf(__('"Invoice(s) %s unarchived."', WPI), $invoice_ids));
      } elseif (isset($_REQUEST['archived'])) {
        WPI_Functions::add_message(sprintf(__('"Invoice(s) %s archived."', WPI), $invoice_ids));
      }
    }

    /** Screen Options */
    if (function_exists('add_screen_option')) {
      add_screen_option('layout_columns', array('max' => 2, 'default' => 2));
    }

    //** Default Help items */
    $contextual_help['General Help'][] = '<h3>' . __('General Information', WPI) . '</h3>';
    $contextual_help['General Help'][] = '<p>' . __('You are on the page which lists your invoices and other item types that you are using.', WPI) . '</p>';
    $contextual_help['General Help'][] = '<p>' . __('Use filter box to find items you need.', WPI) . '</p>';

    //** Hook this action is you want to add info */
    $contextual_help = apply_filters('wpi_main_page_help', $contextual_help);

    do_action('wpi_contextual_help', array('contextual_help' => $contextual_help));
  }

  /**
   * Reports Page load handler
   * @author korotkov@UD
   */
  function pre_load_reports_page() {

    //** Default Help items */
    $contextual_help['General Help'][] = '<h3>' . __('Reports', WPI) . '</h3>';
    $contextual_help['General Help'][] = '<p>' . __('This page allows you to manage your sales statistics.', WPI) . '</p>';

    //** Hook this action is you want to add info */
    $contextual_help = apply_filters('wpi_reports_page_help', $contextual_help);

    do_action('wpi_contextual_help', array('contextual_help' => $contextual_help));
  }

  /**
   * Settings Page load handler
   * @author korotkov@UD
   */
  function pre_load_settings_page() {

    //** Default Help items */
    $contextual_help['Main'][] = '<h3>' . __('Main', WPI) . '</h3>';
    $contextual_help['Main'][] = '<p>' . __('<b>Business Name</b><br /> Enter your business name here. This field defaults to the blog name you chose during WordPress installation.', WPI) . '</p>';
    $contextual_help['Main'][] = '<p>' . __('<b>Business Address</b><br /> Enter your business address here. It will appear on the invoices and quotes you send.', WPI) . '</p>';
    $contextual_help['Main'][] = '<p>' . __('<b>Business Phone</b><br /> Enter your business phone here. It will appear on the invoices and quotes you send.', WPI) . '</p>';
    $contextual_help['Main'][] = '<p>' . __('<b>Email Address</b><br /> Enter your email address here. It will appear on the invoices and quotes you send.', WPI) . '</p>';
    $contextual_help['Main'][] = '<p>' . __('<b>Display Styles</b><br /> Here you can set, enable or disable the WP-Invoice default style settings. Change the default values only if you are an advanced user who understands CSS styles and is able to create their own stylesheets. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-main/">More...</a>', WPI) . '</p>';
    $contextual_help['Main'][] = '<p>' . __('<b>Tax Handling</b><br /> Here you can set when tax calculation is done (depends on your country\'s fiscal system) and you can define a global default tax. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-main/">More...</a>', WPI) . '</p>';
    $contextual_help['Main'][] = '<p>' . __('<b>Advanced Settings</b><br /> These settings control advanced features that have to do with billing, installation features, design issues and general actions for administrators and developers. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-main/">More...</a>', WPI) . '</p>';

    $contextual_help['Business Process'][] = '<h3>' . __('Business Process', WPI) . '</h3>';
    $contextual_help['Business Process'][] = '<p>' . __('<b>When creating an invoice</b><br />Options for managing invoice creating process. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-business-process/">More...</a>', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<b>When viewing an invoice</b><br />Options for managing invoice view. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-business-process/">More...</a>', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<b>How to insert invoice</b><br />Here you have four choices that will define the way an invoice will appear on the invoice display page you have set before. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-business-process/">More...</a>', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<b>After a payment has been completed</b><br />Here we have options that will create automatic email notifications on successful payment of an invoice (partial or complete). <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-business-process/">More...</a>', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<b>Mail From options</b><br />This options allow you to change the default email address that WordPress sends it\'s mail from, and the name of the sender that the email is from.') . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<b>Google Analytics Events Tracking</b><br />If you are using <a target="_blank" href="http://code.google.com/intl/en/apis/analytics/docs/tracking/asyncTracking.html">Google Analytics code snippet</a> for tracking site activity then you can do it better with WP-Invoice Event Tracking feature. Tick events you want to track in your Google Analytics account and see where/when/what people do. To view Events activity go to Content -> Events in your Google Analytics account.', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<u>Attempting to pay Invoices</u> - event is triggered on clicking "Process Payment" button on Invoice page.', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<u>View Invoices</u> - event is triggered when Invoice was viewed by the customer.', WPI) . '</p>';
    $contextual_help['Business Process'][] = '<p>' . __('<i>More Events soon!</i>', WPI) . '</p>';

    $contextual_help['Payment'][] = '<h3>' . __('Payment', WPI) . '</h3>';
    $contextual_help['Payment'][] = '<p>' . __('<b>Default Currency</b><br />Sets the default currency you will use in your invoices. Default value is U.S. Dollars.', WPI) . '</p>';
    $contextual_help['Payment'][] = '<p>' . __('<b>Currency list</b><br>This expandable area allows you to manage the list of currencies you have. You can add new currencies or remove existing ones.<br>Be aware, if you add a new currency please make sure that it corresponds to ISO 4217 and the currency code can be accepted by the payment services / gateways you are using. Here\'s a <a href="http://en.wikipedia.org/wiki/List_of_circulating_currencies">list of currencies</a> with ISO codes and currency symbols.<br>Note that you cannot delete a currency which has already been used in an existing invoice or that is currently selected as default. To do so, delete any invoices using that currency first.', WPI) . '</p>';

    $contextual_help['Payment'][] = '<p>' . __('<b>Default Payment Method</b><br />Here you can choose what default payment method you want to use for invoice payments.', WPI) . '</p>';
    $contextual_help['Payment'][] = '<p>' . __('<b>Payment Gateways</b><br />Here you can specify Gateways which you want to use for your invoices by default. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-payment/">More...</a>', WPI) . '</p>';
    $contextual_help['Payment'][] = '<p>' . __('<b>Manual Payment Information</b><br />If you don\'t want to use payment gateways but offline payments, or if an invoice has no payment gateways enabled, the text in this field will appear as a message to the customer, offering guidance on how to pay you. Write a short text with your bank account number or any other way you want to accept the offline payment. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-payment/">More...</a>', WPI) . '</p>';

    $contextual_help['E-Mail Templates'][] = '<h3>' . __('E-Mail Templates', WPI) . '</h3>';
    $contextual_help['E-Mail Templates'][] = '<p>' . __('You can create as many e-mailed templates as needed, they can later be used to quickly create invoice notifications and reminders, and being sent directly from an invoice page. The following variables can be used within the Subject or the Content of the e-mail templates:', WPI) . '</p>';

    $email_vars['invoice_id'] = __('Invoice ID', WPI);
    $email_vars['link'] = __('URL of invoice', WPI);
    $email_vars['recipient'] = __('Name or business name of receipient', WPI);
    $email_vars['amount'] = __('Due BalanceID', WPI);
    $email_vars['subject'] = __('Invoice title', WPI);
    $email_vars['description'] = __('Description of Invoice', WPI);
    $email_vars['business_name'] = __('Business Name', WPI);
    $email_vars['business_email'] = __('Business Email Address', WPI);
    $email_vars['creator_name'] = __('Name of user who has created invoice', WPI);
    $email_vars['creator_email'] = __('Email of user who has created invoice', WPI);
    $email_vars['due_date'] = __('Invoice due date (if presented)', WPI);

    $email_vars = apply_filters('wpi_email_template_vars', $email_vars);

    if (is_array($email_vars)) {
      $contextual_help['E-Mail Templates'][] = '<ul>';
      foreach ($email_vars as $var => $title) {
        $contextual_help['E-Mail Templates'][] = '<li><b>%' . $var . '%</b> - ' . $title . '</li>';
      }
      $contextual_help['E-Mail Templates'][] = '</ul>';
    }

    $contextual_help['E-Mail Templates'][] = '<p><a href="https://usabilitydynamics.com/tutorials/wp-invoice/email-templates/" target="_blank">' . __('More...', WPI) . '</a></p>';

    $contextual_help['Line Items'][] = '<h3>' . __('Line Items', WPI) . '</h3>';
    $contextual_help['Line Items'][] = '<p>' . __('Predefined Line Items are common services and/or products that you can create once and use in your invoices. For example, if you are a Web professional and your usual invoice has at least an hour of Web Design or / and Web Development services, you can create these item entries to save yourself from typing it every time. When you create a new invoice or quote (with the Quotes Premium Feature), or edit an existing one, you will be able to select these items from a list and if you want, edit the name, description, quantity, price and tax. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-predefined-line-items/">More...</a>', WPI) . '</p>';

    $contextual_help['Premium Features'][] = '<h3>' . __('Premium Features', WPI) . '</h3>';
    $contextual_help['Premium Features'][] = '<p>' . __('This tab show a list of the premium features which are available for purchase and those which you have purchased for WP-Invoice. You can see a list of features available for purchase in the WP-Invoice Premium Features section of our website. <a target="_blank" href="https://usabilitydynamics.com/tutorials/wp-invoice/wp-invoice-settings-premium-features/">More...</a>', WPI) . '</p>';

    $contextual_help['Help'][] = '<h3>' . __('Help', WPI) . '</h3>';
    $contextual_help['Help'][] = '<p>' . __('This tab will help you troubleshoot your plugin and check for updates for Premium Features', WPI) . '</p>';

    $contextual_help['Shortcodes'][] = '<h3>' . __('Shortcodes', WPI) . '</h3>';
    $contextual_help['Shortcodes'][] = '<p><b>' . __('Invoice History', WPI) . '</b></p>';
    $contextual_help['Shortcodes'][] = '<p>' . __('Shortcode:', WPI) . ' <code>[wp-invoice-history title="Your Title"]</code></p>';
    $contextual_help['Shortcodes'][] = '<p>' . __("Works the same way as 'Invoice History' widget. Shows invoice list for currently logged in users.", WPI) . '</p>';

    $contextual_help['Shortcodes'][] = '<p><b>' . __('Invoice Lookup', WPI) . '</b></p>';
    $contextual_help['Shortcodes'][] = '<p>' . __('Shortcode:', WPI) . ' <code>[wp-invoice-lookup message="Your Message" button="Your Button"]</code></p>';
    $contextual_help['Shortcodes'][] = '<p>' . __("Works the same way as 'Invoice Lookup' widget. Allows you to search your invoices by invoice numbers.", WPI) . '</p>';
    //** Hook this action is you want to add info */
    $contextual_help = apply_filters('wpi_settings_page_help', $contextual_help);

    do_action('wpi_contextual_help', array('contextual_help' => $contextual_help));
  }

  /**
   * Process actions from Main Page (List of invoices)
   *
   * @since 3.0
   */
  function process_invoice_actions($action, $ids) {
    global $wpi_settings;

    // Set status
    switch ($action) {
      case 'trash':
        $status = 'trashed';
        break;
      case 'delete':
        $status = 'deleted';
        break;
      case 'untrash':
        $status = 'untrashed';
        break;
      case 'unarchive':
        $status = 'un-archived';
        break;
      case 'archive':
        $status = 'archived';
        break;
    }

    if (!is_array($ids)) {
      $ids = explode(',', $ids);
    }

    // Process action
    $invoice_ids = array();
    foreach ((array) $ids as $ID) {
      // Perfom action
      $this_invoice = new WPI_Invoice();
      $this_invoice->load_invoice("id={$ID}");
      $invoice_id = $this_invoice->data['invoice_id'];
      switch ($action) {
        case 'trash':
          if ($this_invoice->trash()) {
            $invoice_ids[] = $invoice_id;
          }
          break;
        case 'delete':
          if ($this_invoice->delete()) {
            $invoice_ids[] = $invoice_id;
          }
          break;
        case 'untrash':
          if ($this_invoice->untrash()) {
            $invoice_ids[] = $invoice_id;
          }
          break;
        case 'unarchive':
          if ($this_invoice->unarchive()) {
            $invoice_ids[] = $invoice_id;
          }
          break;
        case 'archive':
          if ($this_invoice->archive()) {
            $invoice_ids[] = $invoice_id;
          }
          break;
      }
    }
    if (!empty($status) && $status) {
      // Get Referer and clean it up
      $sendback = wp_get_referer();
      $sendback = remove_query_arg(array('trashed', 'untrashed', 'deleted', 'invoice_id, unarchived, archived'), $sendback);
      // Determine if reffer is not main page, we set it ( anyway, will do redirect to main page )
      if (!strpos($sendback, $wpi_settings['links']['overview_page'])) {
        $sendback = $wpi_settings['links']['overview_page'];
      }
      wp_redirect(add_query_arg(array($status => 1, 'invoice_id' => implode(',', $invoice_ids)), $sendback));
      die();
    }
  }

  /**
   * Can enqueue scripts on specific pages, and print content into head
   *
   * @uses $current_screen global variable
   * @since 3.0
   *
   */
  function admin_enqueue_scripts() {
    global $current_screen;

    /** Include on all pages */
    wp_enqueue_script('jquery-ui-accordion');
    wp_enqueue_script('jquery-ui-datepicker');


    /** Includes page-specific JS if it exists */
    wp_enqueue_script('wpi-this-page-js');

    /** Load scripts on specific pages */
    switch ($current_screen->id) {

      /** Reports page */
      case 'invoice_page_wpi_page_reports':
        wp_enqueue_script('jsapi');
        wp_enqueue_script('wp-invoice-events');
        wp_enqueue_script('wp-invoice-functions');
        break;


      case 'toplevel_page_wpi_main':
        wp_enqueue_script('post');
        wp_enqueue_script('postbox');
      case 'invoice_page_wpi_page_settings':
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('wp-invoice-functions');
        wp_enqueue_script('jquery.smookie');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('wp-invoice-events');
        wp_enqueue_script('jquery.formatCurrency');
        wp_enqueue_script('jquery-data-tables');
        wp_enqueue_style('wpi-jquery-data-tables');
        break;

      case 'invoice_page_wpi_page_manage_invoice':
        wp_enqueue_script('postbox');
        wp_enqueue_script('wp-invoice-functions');
        wp_enqueue_script('wp-invoice-events');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('jquery.formatCurrency');
        wp_enqueue_script('jquery.delegate');
        wp_enqueue_script('jquery.field');
        wp_enqueue_script('jquery.bind');
        wp_enqueue_script('jquery.form');
        wp_enqueue_script('jquery.smookie');

        /** Add scripts and styles for Tiny MCE Editor (default WP Editor) */
        wp_enqueue_script(array('editor', 'thickbox', 'media-upload'));
        wp_enqueue_style('thickbox');

        do_action('wpi_ui_admin_scripts_invoice_editor');

        break;
    }
  }

  /**
   * Add or remove taxonomy columns
   * @since 3.0
   */
  function overview_columns($columns) {

    $overview_columns = apply_filters('wpi_overview_columns', array(
        'cb' => '',
        'post_title' => __('Title', WPI),
        'total' => __('Total Collected', WPI),
        'user_email' => __('Recipient', WPI),
        'post_modified' => __('Date', WPI),
        'post_status' => __('Status', WPI),
        'type' => __('Type', WPI),
        'invoice_id' => __('Invoice ID', WPI)
            ));

    /* We need to grab the columns from the class itself, so we instantiate a new temp object */
    foreach ($overview_columns as $column => $title) {
      $columns[$column] = $title;
    }

    return $columns;
  }

  /**
   * Displays users selection screen when viewing the edit invoice page, and no invoice ID is passed
   *
   * @todo Better check to see if import has already been done
   * @since 3.0
   */
  function wpi_display_user_selection($file_path, $screen, $path) {
    global $wpdb;

    if ($screen != 'invoice_page_wpi_page_manage_invoice') {
      return $file_path;
    }
    if (empty($_REQUEST['wpi'])) {
      return $path . '/user_selection_form.php';
    }
    if (WPI_UD_F::is_older_wp_version('3.4')) {
      return $path = $path . '/invoice_page_wpi_page_manage_invoice_legacy.php';
    }

    return $file_path;
  }

  /**
   * Main invoice page.  Displayes either the first_time_setup, or a list of invoices
   *
   * DOTO: Seems deprecated. - Anton Korotkov
   *
   */
  /* function page_overview() {
    global $wpi_settings;
    WPI_Functions::check_tables();
    // determine if user has compelted setup
    if ($wpi_settings['first_time_setup_ran'] == 'false') {
    include($wpi_settings['admin']['ui_path'] . '/first_time_setup.php');
    } else {
    include($wpi_settings['admin']['ui_path'] . '/overview.php');
    }
    } */

  /**
    Page for adding/editing invoices.  When first opened, displays the user selection form
    Also checks that all proper tables and settings are stup.
   */
  function page_manage_invoice() {
    global $wpi_settings;
    WPI_Functions::check_tables();
    WPI_Functions::check_settings();
    if (isset($_REQUEST['wpi']['new_invoice']) || isset($_REQUEST['wpi']['existing_invoice'])) {
      include($wpi_settings['admin']['ui_path'] . '/manage_invoice.php');
    } else {
      include($wpi_settings['admin']['ui_path'] . '/blocks/postbox_user_selection_form.php');
    }
  }

  /**
   * Does our preprocessing for the manage invoice page, adds our meta boxes, and checks invoice data
   * @since 3.0
   */
  function page_manage_invoice_preprocess($screen_id) {
    global $wpi_settings, $this_invoice, $wpdb;

    //add_screen_option( 'screen_option', array('label' => "Default Screen Option", 'default' => 7, 'option' => 'screen_option') );
    //add_contextual_help($screen_id, 'test');
    // Check if invoice_id already exists
    $invoice_id_exists = false;
    if (!empty($_REQUEST['wpi'])) {
      if (!empty($_REQUEST['wpi']['new_invoice'])) {
        if (wpi_check_invoice($_REQUEST['wpi']['new_invoice']['invoice_id'])) {
          $invoice_id_exists = true;
        }
      }
      if (!empty($_REQUEST['wpi']['existing_invoice'])) {
        if (wpi_check_invoice($_REQUEST['wpi']['existing_invoice']['invoice_id'])) {
          $invoice_id_exists = true;
        }
      }
    }

    if ($invoice_id_exists) {
      // Select status of invoice from DB
      $status = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = '{$_REQUEST['wpi']['existing_invoice']['invoice_id']}' AND meta_key = 'status'");
    }

    // New Invoice
    if (isset($_REQUEST['wpi']['new_invoice']) && empty($invoice_id_exists)) {
      $this_invoice = new WPI_Invoice();
      $this_invoice->create_new_invoice("invoice_id={$_REQUEST['wpi']['new_invoice']['invoice_id']}");

      // If we are copying from a template
      if (!empty($_REQUEST['wpi']['new_invoice']['template_copy'])) {
        $this_invoice->load_template("id={$_REQUEST['wpi']['new_invoice']['template_copy']}");
      }

      // Set user and determine type
      $this_invoice->load_user("email={$_REQUEST['wpi']['new_invoice']['user_email']}");

      // Add custom data if user doesn't exist.
      if (empty($this_invoice->data['user_data'])) {
        $this_invoice->data['user_data'] = array('user_email' => $_REQUEST['wpi']['new_invoice']['user_email']);
      }

      $new_invoice = true;

      // Enter in GET values
      if (isset($_GET['prefill']['subject'])) {
        $this_invoice->data['subject'] = $_GET['prefill']['subject'];
      }

      if (!empty($_GET['prefill']['is_quote']) && $_GET['prefill']['is_quote'] == 'true') {
        $this_invoice->data['is_quote'] = true;
        $this_invoice->data['status'] = "quote";
      }
    } else if (!empty($invoice_id_exists)) {
      // Existing Invoice
      $this_invoice = new WPI_Invoice();

      if (isset($_REQUEST['wpi']['existing_invoice']['invoice_id'])) {
        $ID = $_REQUEST['wpi']['existing_invoice']['invoice_id'];
      } else if (isset($_REQUEST['wpi']['new_invoice']['invoice_id'])) {
        $ID = $_REQUEST['wpi']['new_invoice']['invoice_id'];
      }

      $this_invoice->load_invoice("id={$ID}");
    }

    add_meta_box('postbox_payment_methods', __('Payment Settings', WPI), 'postbox_payment_methods', $screen_id, 'normal', 'high');

    //  add_meta_box('postbox_settings',  __('Settings',WPI), 'postbox_settings', 'admin_page_wpi_invoice_edit', 'side', 'low');
    if (is_object($this_invoice) && isset($this_invoice->data['type']) && $this_invoice->data['type'] == 'single_payment') {
      add_meta_box('postbox_overview', __('Overview', WPI), 'postbox_overview', $screen_id, 'side', 'high');
    } else {
      add_meta_box('postbox_publish', __('Publish', WPI), 'postbox_publish', $screen_id, 'side', 'high');
    }
    //add_meta_box('recurring_billing_box', __('Publish',WPI), 'recurring_billing_box', 'admin_page_wpi_invoice_edit', 'middle', 'low');
    add_meta_box('postbox_user_existing', __('User Information', WPI), 'postbox_user_existing', $screen_id, 'side', 'low');
  }

  /**
    Settings page
   */
  function page_settings() {
    global $wpdb, $wpi_settings;
    WPI_Functions::check_tables();
    include($wpi_settings['admin']['ui_path'] . '/settings_page.php');
  }

  // Displays messages. Can be outputted anywhere, WP JavaScript automatically moves it to the top of the page
  function show_message($content, $type="updated fade") {
    if ($content)
      echo "<div id=\"message\" class='$type' ><p>" . $content . "</p></div>";
  }

  // Displays error messages. Can be outputted anyways, WP JavaScript automatically moves it to the top of the page
  function error_message($message, $return = false) {
    $content = "<div id=\"message\" class='error' ><p>$message</p></div>";
    if ($message != "") {
      if ($return)
        return $content;
      echo $content;
    }
  }

  // Displays the extra profile input fields (such as billing address) in the WP User
  // Called by 'edit_user_profile' and 'show_user_profile'
  function display_user_profile_fields() {
    global $wpdb, $user_id, $wpi_settings;
    $profileuser = get_user_to_edit($user_id);

    include($wpi_settings['admin']['ui_path'] . '/profile_page_content.php');
  }

  /**
   *  Mostly for printing out pre-loaded styles.
   *
   * @since 3.0
   */
  function admin_print_styles() {
    global $wpi_settings, $current_screen;

    wp_enqueue_style('wpi-custom-jquery-ui');
    wp_enqueue_style('wpi-admin-css');

    //** Prints styles specific for this page */
    wp_enqueue_style('wpi-this-page-css');
    wp_enqueue_style('wpi-ie7');
  }

  /**
   * WP-Invoice Contextual Help
   * @global object $current_screen
   * @param array $args
   * @author korotkov@ud
   */
  function wpi_contextual_help($args=array()) {

    $defaults = array(
        'contextual_help' => array()
    );

    extract(wp_parse_args($args, $defaults));

    //** If method exists add_help_tab in WP_Screen */
    if (is_callable(array('WP_Screen', 'add_help_tab'))) {

      //** Loop through help items and build tabs */
      foreach ((array) $contextual_help as $help_tab_title => $help) {

        //** Add tab with current info */
        get_current_screen()->add_help_tab(
                array(
                    'id' => sanitize_title($help_tab_title),
                    'title' => __($help_tab_title, 'wp_crm'),
                    'content' => implode("\n", (array) $contextual_help[$help_tab_title]),
                )
        );
      }

      if (is_callable(array('WP_Screen', 'set_help_sidebar'))) {
        //** Add help sidebar with More Links */
        get_current_screen()->set_help_sidebar(
                '<p><strong>' . __('For more information:', WPI) . '</strong></p>' .
                '<p>' . __('<a href="https://usabilitydynamics.com/products/wp-invoice/" target="_blank">WP-Invoice Product Page</a>', WPI) . '</p>' .
                '<p>' . __('<a href="https://usabilitydynamics.com/products/wp-invoice/forum/" target="_blank">WP-Invoice Forums</a>', WPI) . '</p>'
        );
      }
    } else {
      //** If WP is out of date */
      global $current_screen;
      add_contextual_help($current_screen->id, '<p>' . __('Please upgrade Wordpress to the latest version for detailed help.', WPI) . '</p><p>' . __('Or visit <a href="https://usabilitydynamics.com/products/wp-invoice/" target="_blank">WP-Invoice Help Page</a> on UsabilityDynamics.com', WPI) . '</p>');
    }
  }

  /**
   * Can overwite page title (heading)
   */
  function wp_title($title, $sep, $seplocation) {
    global $invoice_id, $wpdb;

    $post_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = '{$invoice_id}'");
    if (empty($post_id)) {
      return $title;
    }
    $post_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->posts} WHERE ID = '{$post_id}'");
    if (empty($post_title)) {
      return $title;
    }
    return $post_title . ' ' . $sep . ' ';
  }

  /**
   * Can overwite page title (heading)
   */
  function the_title($title = '', $post_id = '') {
    global $wpi_settings, $invoice_id, $wpdb;
    if ($post_id == $wpi_settings['web_invoice_page']) {
      if ($wpi_settings['hide_page_title'] == 'true') {
        return;
      }
      $post_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = '{$invoice_id}'");
      if (empty($post_id)) {
        return $title;
      }
      $post_title = $wpdb->get_var("SELECT post_title FROM {$wpdb->posts} WHERE ID = '{$post_id}'");
      if (empty($post_title)) {
        return $title;
      }
      return $post_title;
    }
    return $title;
  }

  /**
   * Renders invoice in the content.
   *
   * Invoice object already loaded into $wpi_invoice_object at template_redirect()
   *
   */
  function the_content($content) {
    global $post, $invoice, $invoice_id, $wpi_settings, $wpi_invoice_object;

    $invoice = $wpi_invoice_object->data;

    /** Mark invoice as viewed if not by admin */
    if (!current_user_can('manage_options')) {

      /** Prevent duplicating of 'viewed' item. */
      /** 1 time per $hours */
      $hours = 12;

      $viewed_today_from_cur_ip = false;

      foreach ($invoice['log'] as $key => $value) {
        if ($value['user_id'] == '0') {
          if (strstr(strtolower($value['text']), "viewed by {$_SERVER['REMOTE_ADDR']}")) {
            $time_dif = time() - $value['time'];
            if ($time_dif < $hours * 60 * 60) {
              $viewed_today_from_cur_ip = true;
            }
          }
        }
      }

      if (!$viewed_today_from_cur_ip) {
        $wpi_invoice_object->add_entry("note=Viewed by {$_SERVER['REMOTE_ADDR']}");
      }
    }

    /** Include our template functions */
    include_once('wpi_template_functions.php');

    ob_start();
    if ($invoice['post_status'] == 'paid') {
      if (WPI_Functions::wpi_use_custom_template('receipt_page.php')) {
        include($wpi_settings['frontend_template_path'] . 'receipt_page.php');
      } else {
        include($wpi_settings['default_template_path'] . 'receipt_page.php');
      }
    } elseif ($invoice['post_status'] == 'refund') {
      if (WPI_Functions::wpi_use_custom_template('receipt_page.php')) {
        include($wpi_settings['frontend_template_path'] . 'receipt_page.php');
      } else {
        include($wpi_settings['default_template_path'] . 'receipt_page.php');
      }
    } else {
      if (WPI_Functions::wpi_use_custom_template('invoice_page.php')) {
        include($wpi_settings['frontend_template_path'] . 'invoice_page.php');
      } else {
        include($wpi_settings['default_template_path'] . 'invoice_page.php');
      }
    }
    $result .= ob_get_contents();
    ob_end_clean();

    switch ($wpi_settings['where_to_display']) {
      case 'overwrite':
        return $result;
        break;
      case 'below_content':
        return $content . $result;
        break;
      case 'above_content':
        return $result . $content;
        break;
      default:
        return $content;
        break;
    }
  }

  function the_content_shortcode() {
    global $post, $invoice, $invoice_id, $wpi_settings, $wpi_invoice_object;

    $invoice = $wpi_invoice_object->data;

    include_once('wpi_template_functions.php');

    ob_start();
    if (WPI_Functions::wpi_use_custom_template('invoice_page.php')) {
      include($wpi_settings['frontend_template_path'] . 'invoice_page.php');
    } else {
      include($wpi_settings['default_template_path'] . 'invoice_page.php');
    }

    $result .= ob_get_contents();
    ob_end_clean();
    return $result;
  }

  /**
   * Header action
   *
   * @global array $wpi_settings
   */
  function frontend_header() {
    global $wpi_settings, $wpi_invoice_object;
    $invoice_items = array();

    /** It is for adding SKU (unique) field to items list */
    foreach ((array) $wpi_invoice_object->data['itemized_list'] as $key => $value) {
      $invoice_items[$key] = $value;
      $invoice_items[$key]['id'] = str_replace('-', '_', sanitize_title($invoice_items[$key]['name']));
    }
    ?>

    <script type="text/javascript">
      var site_url = '<?php echo WPI_Functions::current_page(); ?>';
      jQuery(document).ready(function(){
    <?php if (!empty($wpi_settings['ga_event_tracking']) && $wpi_settings['ga_event_tracking']['enabled'] == 'true'): ?>

              wpi = wpi || {};
              wpi.invoice_title = '<?php echo addslashes($wpi_invoice_object->data['post_title']); ?>';
              wpi.invoice_amount = <?php echo $wpi_invoice_object->data['net']; ?>;
              wpi.invoice_id = '<?php echo ($wpi_invoice_object->data['custom_id']) ? $wpi_invoice_object->data['custom_id'] : $wpi_invoice_object->data['ID']; ?>';
              wpi.tax = '<?php echo $wpi_invoice_object->data['tax']; ?>';
              wpi.business_name = '<?php echo ($wpi_settings['business_name']); ?>';
              wpi.user_data = {city:'<?php echo ($wpi_settings['user_data']['city']) ? $wpi_settings['user_data']['city'] : ''; ?>',state:'<?php echo ($wpi_settings['user_data']['state']) ? $wpi_settings['user_data']['state'] : ''; ?>',country:'<?php echo ($wpi_settings['user_data']['country']) ? $wpi_settings['user_data']['country'] : ''; ?>'}
              wpi.invoice_items = jQuery.parseJSON('<?php echo json_encode($invoice_items); ?>');

              if ( typeof window._gaq != 'undefined' ) wpi.ga.tracking.init( <?php echo!empty($wpi_settings['ga_event_tracking']['events']['invoices']) ? json_encode($wpi_settings['ga_event_tracking']['events']['invoices']) : '{}'; ?> );

    <?php endif; ?>
          });
    </script>
    <meta name="robots" content="noindex, nofollow" />
    <?php
  }

  /**
    Shorthand function for drawing input fields
   */
  function input($args = '') {
    $defaults = array('id' => '', 'class_from_name' => '', 'title' => '', 'class' => '', 'pattern' => '', 'name' => '', 'group' => '', 'special' => '', 'value' => '', 'type' => '', 'hidden' => false, 'style' => false, 'readonly' => false, 'label' => false);
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    // if [ character is present, we do not use the name in class and id field

    $return = '';
    if (!strpos("$name", '[')) {
      $id = $name;
      $class_from_name = $name;
    }
    if ($label)
      $return .= "<label for='$id'>";
    $return .= "<input " . ($type ? "type=\"$type\" " : '') . " " . ($style ? "style=\"$style\" " : '') . " id=\"$id\" class=\"" . ($type ? "" : "input_field") . " $class_from_name $class " . ($hidden ? " hidden " : '') . "" . ($group ? "group_$group" : '') . " \"    name=\"" . ($group ? $group . "[" . $name . "]" : $name) . "\"  value=\"" . stripslashes($value) . "\"  title=\"$title\" $special " . ($type == 'forget' ? " autocomplete='off'" : '') . " " . ($readonly ? " readonly=\"readonly\" " : "") . " " . (isset($required) && $required ? " required=\"{$required}\" " : "") . ($pattern ? " pattern=\"{$pattern}\" " : "") . "/>";
    if ($label)
      $return .= "$label </label>";
    return $return;
  }

  /**
    Shorthand function for drawing checkbox fields
   */
  function checkbox($args = '', $checked = false) {
    $defaults = array('name' => '', 'id' => false, 'class' => false, 'group' => '', 'special' => '', 'value' => '', 'label' => false, 'maxlength' => false);
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    $return = '';
    // Get rid of all brackets
    if (strpos("$name", '[') || strpos("$name", ']')) {
      $replace_variables = array('][', ']', '[');
      $class_from_name = $name;
      $class_from_name = "wpi_" . str_replace($replace_variables, '_', $class_from_name);
    } else {
      $class_from_name = "wpi_" . $name;
    }
    // Setup Group
    $group_string = '';
    if ($group) {
      if (strpos($group, '|')) {
        $group_array = explode("|", $group);
        $count = 0;
        foreach ($group_array as $group_member) {
          $count++;
          if ($count == 1) {
            $group_string .= "$group_member";
          } else {
            $group_string .= "[$group_member]";
          }
        }
      } else {
        $group_string = "$group";
      }
    }
    // Use $checked to determine if we should check the box
    $checked = strtolower($checked);
    if ($checked == 'yes' ||
            $checked == 'on' ||
            $checked == 'true' ||
            ($checked == true && $checked != 'false' && $checked != '0')) {
      $checked = true;
    } else {
      $checked = false;
    }
    $id = ($id ? $id : $class_from_name);
    $insert_id = ($id ? " id='$id' " : " id='$class_from_name' ");
    $insert_name = ($group_string ? " name='" . $group_string . "[$name]' " : " name='$name' ");
    $insert_checked = ($checked ? " checked='checked' " : " ");
    $insert_value = " value=\"$value\" ";
    $insert_class = " class='$class_from_name $class wpi_checkbox' ";
    $insert_maxlength = ($maxlength ? " maxlength='$maxlength' " : " ");
    // Determine oppositve value
    switch ($value) {
      case 'yes':
        $opposite_value = 'no';
        break;
      case 'true':
        $opposite_value = 'false';
        break;
    }
    // Print label if one is set
    if ($label)
      $return .= "<label for='$id'>";
    // Print hidden checkbox
    $return .= "<input type='hidden' value='$opposite_value' $insert_name />";
    // Print checkbox
    $return .= "<input type='checkbox' $insert_name $insert_id $insert_class $insert_checked $insert_maxlength  $insert_value $special />";
    if ($label)
      $return .= " $label</label>";
    return $return;
  }

  function textarea($args = '') {
    $defaults = array('title' => '', 'class' => '', 'name' => '', 'group' => '', 'special' => '', 'value' => '', 'type' => '');
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    return "<textarea id='$name' class='input_field $name $class " . ($group ? "group_$group" : '') . "'  name='" . ($group ? $group . "[" . $name . "]" : $name) . "' title='$title' $special >" . stripslashes($value) . "</textarea>";
  }

  function select($args = '') {
    $defaults = array('id' => '', 'class' => '', 'name' => '', 'group' => '', 'special' => '', 'values' => '', 'current_value' => '');
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    global $wpi_settings;
    // Get rid of all brackets
    if (strpos("$name", '[') || strpos("$name", ']')) {
      $replace_variables = array('][', ']', '[');
      $class_from_name = $name;
      $class_from_name = "wpi_" . str_replace($replace_variables, '_', $class_from_name);
    } else {
      $class_from_name = "wpi_" . $name;
    }
    // Overwrite class_from_name if class is set
    if ($class)
      $class_from_name = $class;
    $values_array = is_serialized($values) ? unserialize($values) : $values;
    if ($values == 'yon') {
      $values_array = array("yes" => __("Yes", WPI), "no" => __("No", WPI));
    }
    if ($values == 'us_states') {
      $values_array = array('0' => '--' . __('Select') . '--');
      $values_array = array_merge($values_array, $wpi_settings['states']);
    }
    if ($values == 'countries') {
      $values_array = $wpi_settings['countries'];
    }
    if ($values == 'years') {
      // Create year array
      $current_year = intval(date('y'));
      $values_array = array();
      $counter = 0;
      while ($counter < 7) {
        $values_array[$current_year] = "20" . $current_year;
        $current_year++;
        $counter++;
      }
    }
    if ($values == 'months') {
      $values_array = array("" => "", "01" => __("Jan", WPI), "02" => __("Feb", WPI), "03" => __("Mar", WPI), "04" => __("Apr", WPI), "05" => __("May", WPI), "06" => __("Jun", WPI), "07" => __("Jul", WPI), "08" => __("Aug", WPI), "09" => __("Sep", WPI), "10" => __("Oct", WPI), "11" => __("Nov", WPI), "12" => __("Dec", WPI));
    }
    $output = "<select id='" . ($id ? $id : $class_from_name) . "' name='" . ($group ? $group . "[" . $name . "]" : $name) . "' class='$class_from_name " . ($group ? "group_$group" : '') . "' $special >";

    if (!empty($values_array) && is_array($values_array)) {
      foreach ($values_array as $key => $value) {
        $output .= "<option value='$key'";
        if ($key == $current_value)
          $output .= " selected";
        $output .= ">$value</option>";
      }
    } else {
      $output .= "<option>" . __('Values are empty', WPI) . "</option>";
    }
    $output .= "</select>";
    return $output;
  }

  /**
   * Add link to user profile in CRM for user data block
   *
   * @param int $user_id
   * @author korotkov@ud
   */
  function crm_user_panel($user_id) {

    if (!$user_id) {
      return;
    }

    // Determine if WP CRM is installed
    if (class_exists('WP_CRM_Core')) {

      echo '<div class="wpi_crm_link"><a  class="button" target="_blank" href="' . admin_url('admin.php?page=wp_crm_add_new&user_id=' . $user_id) . '">' . __('View Profile', WPI) . '</a></div>';
    } else {

      echo '<div class="wpi_crm_link"><a target="_blank" href="' . admin_url('plugin-install.php?tab=search&type=term&s=WP-CRM') . '">' . __('Get WP-CRM plugin to enhance user management.', WPI) . '</a></div>';
    }
  }

  /**
   * Add additional WPI attribute option for CRM
   *
   * @global object $wp_crm
   * @param array $args
   * @author korotkov@ud
   */
  function wp_crm_data_structure_attributes($args) {

    global $wp_crm;

    $default = array(
        'slug' => '',
        'data' => array(),
        'row_hash' => ''
    );

    extract(wp_parse_args($args, $default), EXTR_SKIP);

    if (!empty($slug) && !empty($data) && !empty($row_hash)) {
      ?>
      <li class="wp_crm_advanced_configuration">
        <input id="<?php echo $row_hash; ?>_no_edit_wpi" value='true' type="checkbox"  <?php checked($wp_crm['data_structure']['attributes'][$slug]['wp_invoice'], 'true'); ?> name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][wp_invoice]" />
        <label for="<?php echo $row_hash; ?>_no_edit_wpi" ><?php _e('WP-Invoice custom field', WPI); ?></label>
      </li>
      <?php
    }
  }

  /**
   * Add contextual help data when WPI and CRM installed
   *
   * @param type $data
   * @return array
   * @author korotkov@ud
   */
  function wp_crm_contextual_help($data) {

    $data['WP-Invoice Integration'][] = __('<h3>WP-Invoice</h3>', WPI);
    $data['WP-Invoice Integration'][] = __('<p>Advanced option <b>WP-Invoice custom field</b> may be used for adding custom user data fields for payments forms.</p>', WPI);
    $data['WP-Invoice Integration'][] = __('<p>Works for Authorize.net payment method only for now.</p>', WPI);
    $data['WP-Invoice Integration'][] = __('<h3>WP-Invoice Notifications</h3>', WPI);
    $data['WP-Invoice Integration'][] = __('<p>For your notifications on any of this Trigger actions &mdash; <i>WPI: Invoice Paid (Client Receipt)</i>, <i>WPI: Invoice Paid (Notify Administrator)</i>, <i>WPI: Invoice Paid (Notify Creator)</i> &mdash; you can use this shortcodes:</p>', WPI);
    $data['WP-Invoice Integration'][] = "
        <p>
          <b>[user_email]</b>,
          <b>[user_name]</b>,
          <b>[user_id]</b>,
          <b>[invoice_id]</b>,
          <b>[invoice_title]</b>,
          <b>[permalink]</b>,
          <b>[total]</b>,
          <b>[default_currency_code]</b>,
          <b>[total_payments]</b>,
          <b>[creator_name]</b>,
          <b>[creator_email]</b>,
          <b>[creator_id]</b>,
          <b>[site]</b>,
          <b>[business_name]</b>,
          <b>[from]</b>,
          <b>[admin_name]</b>,
          <b>[admin_email]</b>,
          <b>[admin_id]</b>.
        </p>
      ";

    return $data;
  }

  /**
   * Draws template search
   *
   * @global array $wpi_settings
   * @global type $wpdb
   * @param type $args
   */
  function draw_template_auto_complete_field( $args = '' ) {
    global $wpi_settings, $wpdb;
    wp_enqueue_script('jquery-ui-autocomplete');

    //** Extract passed args and load defaults */
    extract(wp_parse_args($args, array(
              'input_name' => 'wpi[new_invoice][template_copy]',
              'input_class' => 'input_field',
              'input_id' => 'wpi_template_lookup',
              'input_style' => ''
            )), EXTR_SKIP);
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        jQuery("#<?php echo $input_id; ?>").autocomplete({
          source:ajaxurl+'?action=wpi_template_autocomplete_handler',
          minLength: 3,
          select: function(event, ui) {
            event.preventDefault();
            jQuery("#<?php echo $input_id; ?>").val(ui.item.label);
            jQuery("#<?php echo $input_id; ?>_value").val(ui.item.value);
          }
        });
        jQuery("#<?php echo $input_id; ?>").focus();
      });
    </script>
    <input type="text" class="<?php echo $input_class; ?>" id="<?php echo $input_id; ?>"  style="<?php echo $input_style; ?>" />
    <input type="hidden" name="<?php echo $input_name; ?>" id="<?php echo $input_id; ?>_value" />
    <?php
  }

}

function wp_invoice_printYearDropdown($sel='') {
  $localDate = getdate();
  $minYear = $localDate["year"];
  $maxYear = $minYear + 15;
  $output = "<option value=''>--</option>";
  for ($i = $minYear; $i < $maxYear; $i++) {
    $output .= "<option value='" . substr($i, 2, 2) . "'" . ($sel == (substr($i, 2, 2)) ? ' selected' : '') .
            ">" . $i . "</option>";
  }
  return($output);
}

function wp_invoice_printMonthDropdown($sel='') {
  $output = "<option value=''>--</option>";
  $output .= "<option " . ($sel == 1 ? ' selected' : '') . " value='01'>01 - " . __('Jan', WPI) . "</option>";
  $output .= "<option " . ($sel == 2 ? ' selected' : '') . "  value='02'>02 - " . __('Feb', WPI) . "</option>";
  $output .= "<option " . ($sel == 3 ? ' selected' : '') . "  value='03'>03 - " . __('Mar', WPI) . "</option>";
  $output .= "<option " . ($sel == 4 ? ' selected' : '') . "  value='04'>04 - " . __('Apr', WPI) . "</option>";
  $output .= "<option " . ($sel == 5 ? ' selected' : '') . "  value='05'>05 - " . __('May', WPI) . "</option>";
  $output .= "<option " . ($sel == 6 ? ' selected' : '') . "  value='06'>06 - " . __('Jun', WPI) . "</option>";
  $output .= "<option " . ($sel == 7 ? ' selected' : '') . "  value='07'>07 - " . __('Jul', WPI) . "</option>";
  $output .= "<option " . ($sel == 8 ? ' selected' : '') . "  value='08'>08 - " . __('Aug', WPI) . "</option>";
  $output .= "<option " . ($sel == 9 ? ' selected' : '') . "  value='09'>09 - " . __('Sep', WPI) . "</option>";
  $output .= "<option " . ($sel == 10 ? ' selected' : '') . "  value='10'>10 - " . __('Oct', WPI) . "</option>";
  $output .= "<option " . ($sel == 11 ? ' selected' : '') . "  value='11'>11 - " . __('Nov', WPI) . "</option>";
  $output .= "<option " . ($sel == 12 ? ' selected' : '') . "  value='12'>12 - " . __('Dec', WPI) . "</option>";
  return($output);
}

function wp_invoice_format_phone($phone) {
  $phone = preg_replace("/[^0-9]/", "", $phone);
  if (strlen($phone) == 7)
    return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
  elseif (strlen($phone) == 10)
    return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
  else
    return $phone;
}
