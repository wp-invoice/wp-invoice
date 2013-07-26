<?php
global $wpi_settings;

$wpi_settings_tabs = array(
    'basic' => array(
        'label' => __('Main', WPI),
        'position' => 10,
        'callback' => array('WPI_Settings_page', 'basic')
    ),
    'business_process' => array(
        'label' => __('Business Process', WPI),
        'position' => 20,
        'callback' => array('WPI_Settings_page', 'business_process')
    ),
    'payment' => array(
        'label' => __('Payment', WPI),
        'position' => 30,
        'callback' => array('WPI_Settings_page', 'payment')
    ),
    'email_templates' => array(
        'label' => __('E-Mail Templates', WPI),
        'position' => 50,
        'callback' => array('WPI_Settings_page', 'email_templates')
    ),
    'predefined' => array(
        'label' => __('Line Items', WPI),
        'position' => 60,
        'callback' => array('WPI_Settings_page', 'predefined')
    ),
    'plugins' => array(
        'label' => __('Premium Features', WPI),
        'position' => 70,
        'callback' => array('WPI_Settings_page', 'plugins')
    ),
    'help' => array(
        'label' => __('Help', WPI),
        'position' => 500,
        'callback' => array('WPI_Settings_page', 'help')
    )
);

// Allow third-party plugins and premium features to insert and remove tabs via API
$wpi_settings_tabs = apply_filters('wpi_settings_tabs', $wpi_settings_tabs);

//** Put the tabs into position */
usort($wpi_settings_tabs, create_function('$a,$b', ' return $a["position"] - $b["position"]; '));

if (isset($_REQUEST['message'])) {
  switch ($_REQUEST['message']) {
    case 'updated':
      WPI_Functions::add_message(__("Settings updated.", WPI));
      break;
  }
}
?>
<script type="text/javascript">

  var wpi = {
    'currency':'<?php echo $wpi_settings['currency']['symbol'][$wpi_settings['currency']['default_currency_code']]; ?> ',
    'thousandsSeparator':'<?php echo!isset($wpi_settings['thousands_separator_symbol']) ? ',' : ($wpi_settings['thousands_separator_symbol'] == '0' ? '' : $wpi_settings['thousands_separator_symbol']) ?>'
  };

  jQuery(document).ready( function() {
    var wp_invoice_settings_page = jQuery("#wp_invoice_settings_page").tabs({cookie: {expires: 30}});
    // The following runs specific functions when a given tab is loaded
    jQuery('#wp_invoice_settings_page').bind('tabsshow', function(event, ui) {
      var selected = wp_invoice_settings_page.tabs('option', 'selected');

      if(selected == 5) { }
    });
    // @TODO: Simple hack to fix setting page scrolling down on load. But cause of it not found.
    jQuery(this).scrollTop(0);
  });

</script>

<div class="wrap">
  <form method="post" id="wpi_settings_form" enctype="multipart/form-data">
    <?php echo WPI_UI::input("type=hidden&name=wpi_settings_update&value=true") ?>
    <h2><?php _e("WP-Invoice Global Settings", WPI) ?></h2>

    <?php WPI_Functions::print_messages(); ?>

    <div id="wp_invoice_settings_page" class="wp_invoice_tabbed_content">
      <ul class="wp_invoice_settings_tabs">
        <?php foreach ($wpi_settings_tabs as $tab_id => $tab) {
          if (!is_callable($tab['callback'])) continue; ?>
          <li><a href="#wpi_tab_<?php echo $tab_id; ?>"><?php echo $tab['label']; ?></a></li>
        <?php } ?>
      </ul>

      <?php foreach ($wpi_settings_tabs as $tab_id => $tab) { ?>
        <div id="wpi_tab_<?php echo $tab_id; ?>" class="wp_invoice_tab" >
          <?php
          if (is_callable($tab['callback'])) {
            call_user_func($tab['callback'], $wpi_settings);
          } else {
            echo __('Warning:', WPI) . ' ' . implode(':', $tab['callback']) . ' ' . __('not found', WPI) . '.';
          }
          ?>
        </div>
      <?php } ?>

    </div><?php /* end: #wp_invoice_settings_page */ ?>
    <div id="poststuff" class="metabox-holder">
      <div id="submitdiv" class="postbox" style="">
        <div class="inside">
          <div id="major-publishing-actions">
            <div id="publishing-action">
              <input type="submit" value="<?php esc_attr(_e('Save All Settings', WPI)) ?>" class="button-primary">
            </div>
            <div class="clear"></div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div><?php /* end: .wrap */ ?>

<?php

class WPI_Settings_page {

  function basic($wpi_settings) {

    global $wpdb;
    ?>

    <table class="form-table">
      <tr>
        <th width="200"><?php _e("Business Name", WPI) ?></th>
        <td><?php echo WPI_UI::input("type=text&name=business_name&group=wpi_settings&value={$wpi_settings['business_name']}") ?> </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Business Address", WPI) ?></th>
        <td><?php echo WPI_UI::textarea("name=business_address&group=wpi_settings&value={$wpi_settings['business_address']}") ?> </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Business Phone", WPI) ?></th>
        <td><?php echo WPI_UI::input("type=text&name=business_phone&group=wpi_settings&value={$wpi_settings['business_phone']}") ?> </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Email Address", WPI) ?></th>
        <td><?php echo WPI_UI::input("type=text&name=email_address&group=wpi_settings&value={$wpi_settings['email_address']}") ?> </td>
      </tr>

      <tr>
        <th><?php _e("Display Styles", WPI) ?></th>
        <td>
          <ul>
            <?php if (WPI_Functions::has_theme_specific_stylesheet()) : ?>
              <li>
                <?php echo WPI_UI::checkbox("name=wpi_settings[do_not_load_theme_specific_css]&value=yes&label=" . __('Do <b>not</b> load theme specific styles.', WPI), WPI_Functions::is_true($wpi_settings['do_not_load_theme_specific_css'])); ?>
                <div class="description"><?php echo sprintf( __("WP-Invoice is shipped with a custom stylesheet designed for <b>%s</b>", WPI), get_current_theme() ); ?></div>
              </li>
            <?php endif; ?>
            <li><?php echo WPI_UI::checkbox("name=wpi_settings[use_css]&value=yes&label=" . __('Load default CSS styles on the front-end', WPI), WPI_Functions::is_true($wpi_settings['use_css'])); ?></li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e("Tax Handling", WPI) ?></th>
        <td>
          <ul class="wpi_something_advanced_wrapper">
            <li><label for="wpi_tax_method"><?php _e('Calculate Taxable Subtotal', WPI) ?> <?php echo WPI_UI::select("name=tax_method&group=wpi_settings&values=" . serialize(array("after_discount" => __("After Discount", WPI), "before_discount" => __("Before Discount", WPI))) . "&current_value=" . (!empty($wpi_settings['tax_method']) ? $wpi_settings['tax_method'] : "")); ?> </label></li>
            <li><?php echo WPI_UI::checkbox("name=use_global_tax&class=wpi_show_advanced&group=wpi_settings&value=true&label=" . __('Use global tax.', WPI), $wpi_settings['use_global_tax']); ?></li>
            <li class="wpi_advanced_option">
              Tax value: <?php echo WPI_UI::input("type=text&style=width:50px;&name=global_tax&group=wpi_settings&value={$wpi_settings['global_tax']}") ?>%
              <div class="description wpi_advanced_option"><?php _e("This will make all new invoices have default Tax value which can be changed for different invoice.", WPI) ?></div>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e("Advanced Settings", WPI) ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li><?php echo WPI_UI::checkbox("name=allow_deposits&group=wpi_settings&value=true&label=" . __('Allow partial payments.', WPI), $wpi_settings['allow_deposits']); ?></li>
            <!--<li><?php echo WPI_UI::checkbox("name=terms_acceptance_required&group=wpi_settings&value=true&label=" . __('Show checkbox for mandatory terms acceptance.', WPI), $wpi_settings['terms_acceptance_required']); ?></li>-->
            <li><?php echo WPI_UI::checkbox("name=show_recurring_billing&group=wpi_settings&value=true&label=" . __('Show recurring billing options.', WPI), $wpi_settings['show_recurring_billing']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=force_https&group=wpi_settings&value=true&label=" . __('Enforce HTTPS on invoice pages, if available on this server.', WPI), $wpi_settings['force_https']); ?> </li>

            <li>
              <label for="wpi_user_level"><?php _e("Minimum user level to manage WP-Invoice", WPI) ?> <?php echo WPI_UI::select("name=user_level&group=wpi_settings&values=" . serialize(array("0" => __('Subscriber', WPI), "1" => __('Contributor', WPI), "2" => __('Author', WPI), "5" => __('Editor', WPI), "8" => __('Administrator', WPI))) . "&current_value={$wpi_settings['user_level']}"); ?> </label>
            </li>
            <li>
              <?php _e("Using Godaddy Hosting:", WPI) ?> <?php echo WPI_UI::select("name=using_godaddy&group=wpi_settings&values=yon&current_value={$wpi_settings['using_godaddy']}"); ?>
              <div class="description"><?php _e("Special proxy must be used to process credit card transactions on GoDaddy servers.", WPI) ?></div>
            </li>

            <li>
              <?php
              if (!file_exists($wpi_settings['frontend_template_path'])) {
                $no_template_folder = true;
              }
              echo WPI_UI::checkbox("class=use_custom_templates&name=wpi_settings[use_custom_templates]&value=yes&label=" . __("Use custom templates. If checked, WP-Invoice will use templates in the 'wpi' folder in your active theme's folder.", WPI), WPI_Functions::is_true($wpi_settings['use_custom_templates']));
              ?>
            </li>
            <li class="wpi_use_custom_template_settings" style="<?php echo ( empty($wpi_settings['use_custom_templates']) || $wpi_settings['use_custom_templates'] == 'no' ? 'display:none;' : ''); ?>">
              <?php if (!empty($no_template_folder)) { ?>
                <span class="wpi_red_notification"><?php _e('Note: Currently there is no "wpi" folder in your active template\'s folder, WP-Invoice will attempt to create it after saving.', WPI) ?></span>
              <?php } else { ?>
                <span class="wpi_green_notification"><?php _e('A "wpi" folder has been found, any files with the proper file names will be used instead of the default template files.', WPI) ?></span>
    <?php } ?>
            </li>
            <li><input class="button wpi_install_custom_templates" type="button" value="<?php _e("Install", WPI); ?>" /> <?php _e("the custom templates inside the <b>wpi</b> folder in your active theme's folder.", WPI); ?></li>
            <li class="wpi_install_custom_templates_result" style="display:none;"></li>
            <?php if (WPI_Functions::has_installed_premium_features()): ?>
              <li><?php echo WPI_UI::checkbox("name=wpi_settings[disable_automatic_feature_update]&value=true&label=" . __("Disable automatic Premium Feature updates.", WPI), WPI_Functions::is_true($wpi_settings['disable_automatic_feature_update'])); ?></li>
                <?php endif; ?>
            <li>
              <label for="wpi_thousands_separator_symbol">
                <?php _e('Thousands Separator Symbol', WPI); ?>
                <?php
                echo WPI_UI::select(array(
                    'name' => 'thousands_separator_symbol',
                    'group' => 'wpi_settings',
                    'values' => array(
                        '0' => __('None'),
                        '.' => '.(period)',
                        ',' => ',(comma)'
                    ),
                    'current_value' => !isset($wpi_settings['thousands_separator_symbol']) ? ',' : $wpi_settings['thousands_separator_symbol']
                ));
                ?>
              </label>
            </li>
          </ul>
        </td>
      </tr>

    <?php do_action('wpi_settings_page_basic_settings', $wpi_settings); ?>


    </table>

  <?php
  }

/* end "Basic" */

  function business_process($wpi_settings) {

    global $wpdb;
    ?>

    <table class="form-table">
      <tr>
        <th><?php _e("When creating an invoice", WPI) ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li><?php echo WPI_UI::checkbox("name=increment_invoice_id&group=wpi_settings&value=true&label=" . __('Automatically increment the invoice\'s custom ID by one.', WPI), $wpi_settings['increment_invoice_id']) ?></li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e("When viewing an invoice", WPI) ?></th>
        <td><ul class="wpi_settings_list">
            <li>
              <label for="wpi_settings[web_invoice_page]"><?php _e("Display invoices on the", WPI) ?>
                <select name='wpi_settings[web_invoice_page]'>
                  <option></option>
                  <?php
                  $list_pages = $wpdb->get_results("SELECT ID, post_title, post_name, guid FROM " . $wpdb->prefix . "posts WHERE post_status = 'publish' AND post_type = 'page' ORDER BY post_title");
                  $wp_invoice_web_invoice_page = $wpi_settings['web_invoice_page'];
                  foreach ($list_pages as $page) {
                    echo "<option  style='padding-right: 10px;'";
                    if (isset($wp_invoice_web_invoice_page) && $wp_invoice_web_invoice_page == $page->ID)
                      echo " SELECTED ";
                    echo " value=\"" . $page->ID . "\">" . $page->post_title . "</option>\n";
                  }
                  echo "</select>";
                  ?>
    <?php _e("page.", WPI) ?> </label>
            </li>
            <li><?php echo WPI_UI::checkbox("name=replace_page_title_with_subject&group=wpi_settings&value=true&label=" . __('Replace HTML title with invoice subject when viewing invoice.', WPI), $wpi_settings['replace_page_title_with_subject']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=replace_page_heading_with_subject&group=wpi_settings&value=true&label=" . __('Replace page heading and navigation link title with invoice subject when viewing invoice.', WPI), $wpi_settings['replace_page_heading_with_subject']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=hide_page_title&group=wpi_settings&value=true&label=" . __('Hide page heading and navigation link completely.', WPI), $wpi_settings['hide_page_title']); ?></li>

            <li><?php echo WPI_UI::checkbox("name=show_business_address&group=wpi_settings|globals&value=true&label=" . __('Show my business name and address.', WPI), $wpi_settings['globals']['show_business_address']); ?> </li>
            <li><?php echo WPI_UI::checkbox("name=show_quantities&group=wpi_settings|globals&value=true&label=" . __('Show quantity breakdowns in the itemized list.', WPI), $wpi_settings['globals']['show_quantities']); ?> </li>
          </ul></td>
      </tr>
      <tr>
        <th> <a class="wp_invoice_tooltip"  title="<?php _e('Select whether to overwrite all page content, insert at the bottom of the content, or to look for the [wp-invoice] tag.', WPI); ?>">
    <?php _e('How to Insert Invoice', WPI); ?>
          </a></th>
        <td><?php echo WPI_UI::select("name=where_to_display&group=wpi_settings&values=" . serialize(array("overwrite" => __("Overwrite All Page Content", WPI), "below_content" => __("Place Below Content", WPI), "above_content" => __("Above Content", WPI), "replace_tag" => __("Replace [wp-invoice] Tag", WPI))) . "&current_value={$wpi_settings['where_to_display']}"); ?> <?php _e('If using the tag, place <span class="wp_invoice_explanation">[wp-invoice]</span> somewhere within your page content.', WPI) ?> </td>
      </tr>
      <tr>
        <th><?php _e("After a payment has been completed", WPI) ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li><?php echo WPI_UI::checkbox("name=send_thank_you_email&group=wpi_settings&value=true&label=" . __('Send email confirmation to the client.', WPI), $wpi_settings['send_thank_you_email']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=cc_thank_you_email&group=wpi_settings&value=true&label=" . sprintf(__('Send email notification to the address set for administrative purposes from <a href="%s">General Settings</a>', WPI), get_option('home') . "/wp-admin/options-general.php") . " (<u>" . get_option('admin_email') . "</u>)", $wpi_settings['cc_thank_you_email']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=send_invoice_creator_email&group=wpi_settings&value=true&label=" . __('Send email notification to invoice creator.', WPI), $wpi_settings['send_invoice_creator_email']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=use_wp_crm_to_send_notifications&group=wpi_settings&value=true&label=" . __('Use CRM to send notifications', WPI) . (((!function_exists('wp_crm_send_notification') )) ? "&special=disabled='disabled'" : ''), ((function_exists('wp_crm_send_notification')) ? $wpi_settings['use_wp_crm_to_send_notifications'] : false)); ?><div class="description"><?php if (!function_exists('wp_crm_send_notification')) : ?><?php echo sprintf( __('Get <a class="small" href="%s">WP-CRM plugin</a> to enhance notification management.', WPI), admin_url('plugin-install.php?tab=search&amp;type=term&amp;s=WP-CRM+andypotanin') ); ?><?php else: ?>You can visit WP-CRM <a class="small" href="<?php echo admin_url('admin.php?page=wp_crm_settings#tab_notifications'); ?>">Notifications</a> tab to adjust email templates.<?php endif; ?></div></li>
          </ul>
        </td>
      </tr>
      <tr>
        <th>
          <a class="wp_invoice_tooltip" title="<?php _e("This options allow you to change the default email address that WordPress sends it's mail from, and the name of the sender that the email is from.", WPI); ?>">
              <?php _e("Mail From options", WPI); ?>
          </a>
        </th>
        <td>
          <ul class="wpi_settings_list">
            <li>
    <?php
    echo WPI_UI::input(array(
        'label' => __('Sender Name', WPI),
        'type' => 'text',
        'name' => 'mail_from_sender_name',
        'group' => 'wpi_settings',
        'value' => empty($wpi_settings['mail_from_sender_name']) ? 'WordPress' : $wpi_settings['mail_from_sender_name']
    ));
    ?>
              <div class="description"><?php _e('The sender name that the email is from', WPI); ?></div>
            </li>
            <li>
              <?php
              echo WPI_UI::input(array(
                  'label' => __('User E-mail', WPI),
                  'type' => 'text',
                  'name' => 'mail_from_user_email',
                  'group' => 'wpi_settings',
                  'value' => empty($wpi_settings['mail_from_user_email']) ? 'wordpress@' . strtolower($_SERVER['SERVER_NAME']) : $wpi_settings['mail_from_user_email']
              ));
              ?>
              <div class="description"><?php _e('Email address e.g. username@example.com', WPI); ?></div>
            </li>
            <li>
    <?php
    echo WPI_UI::checkbox(array(
        'label' => __("Apply 'Mail From' settings.", WPI),
        'name' => 'change_mail_from',
        'value' => 'true',
        'group' => 'wpi_settings'
            ), $wpi_settings['change_mail_from']);
    ?>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th>
          <a class="wp_invoice_tooltip" title="<?php _e("If you are using <b>Google Analytics Site Tracking</b> code on your site then you can track WP-Invoice events.", WPI); ?>">
    <?php _e('Google Analytics Events Tracking', WPI); ?>
          </a>
        </th>
        <td>
          <ul class="wpi_settings_list">
            <!-- Google Analytics Event Tracking option -->
            <li>
    <?php echo WPI_UI::checkbox("name=wpi_settings[ga_event_tracking][enabled]&value=true&label=" . __('I want to track events.', WPI), WPI_Functions::is_true($wpi_settings['ga_event_tracking']['enabled'])); ?>
            </li>
            <li class="wpi_ga_events_list" style="<?php echo (empty($wpi_settings['ga_event_tracking']['enabled']) || $wpi_settings['ga_event_tracking']['enabled'] == 'false' ) ? 'display:none;' : ''; ?>">
              <ul>
                <li>
                  <strong><?php _e('Track Invoices events:', WPI); ?></strong>
                  <ul class="wpi_sublist">
                    <li>
    <?php echo WPI_UI::checkbox("name=wpi_settings[ga_event_tracking][events][invoices][attempting_pay_invoice]&value=true&label=" . __('Attempting to pay Invoices', WPI), WPI_Functions::is_true($wpi_settings['ga_event_tracking']['events']['invoices']['attempting_pay_invoice'])); ?>
                    </li>
                    <li>
    <?php echo WPI_UI::checkbox("name=wpi_settings[ga_event_tracking][events][invoices][view_invoice]&value=true&label=" . __('View Invoices', WPI), WPI_Functions::is_true($wpi_settings['ga_event_tracking']['events']['invoices']['view_invoice'])); ?>
                    </li>
                  </ul>
                </li>

    <?php do_action('wpi_settings_page_ga_events_list', $wpi_settings); ?>

              </ul>
            </li>
          </ul>
        </td>
      </tr>

    </table>


  <?php
  }

/* end "business_process" */

  function payment($wpi_settings) {
    global $wpi_chargify;
    ?>
    <table class="form-table">
      <tr>
        <th><?php _e("Default Currency", WPI); ?></th>
        <td><?php echo WPI_UI::select("name=wpi_settings[currency][default_currency_code]&values=" . serialize($wpi_settings['currency']['types']) . "&current_value={$wpi_settings['currency']['default_currency_code']}"); ?></td>
      </tr>
      <tr>
        <th></th>
        <td>

    <?php $currency_array = apply_filters('wpi_currency', $wpi_settings['currency']); ?>

          <div id="currency-list">
            <h3><a href="#"><?php _e("Currency list", WPI); ?></a></h3>
            <div>
              <table class="ud_ui_dynamic_table widefat form-table edit-currency-tab" style="margin-bottom:8px;" auto_increment="true">
                <thead>
                  <tr>
                    <th style="width:10%; padding-left: 50px;"><?php _e('Code', WPI); ?></th>
                    <th style="text-align: center;"><?php _e('Symbol', WPI); ?></th>
                    <th style="width:40%; text-align: center;"><?php _e('Name', WPI); ?></th>
                  </tr>
                </thead>
                <tbody>
    <?php foreach ($currency_array['types'] as $slug => $title): ?>
                    <tr class="wpi_dynamic_table_row" slug="<?php echo $slug; ?>" new_row="false">
                      <td >
                        <span class="row_delete" verify_action="true">&nbsp;</span>
      <?php echo WPI_UI::input("name=wpi_settings[currency][code][{$slug}]&value={$slug}&type=text&class=" . urlencode("names_changer code") . "&special=disabled='disabled'&pattern=[A-Z]{3}&required=required&title=" . urlencode("Please,fill in the field with three capital letters (A-Z)") . "&style=width:4em;margin-left:50px;") ?>
                      </td>
                      <td align="left" class="symbol-row" style="text-align: center;">
      <?php echo WPI_UI::input("name=wpi_settings[currency][symbol][{$slug}]&class=currency_sign&value=" . urlencode($currency_array['symbol'][$slug]) . "&type=text&required=required&style=float:right;width:150px;margin-right:55px;") ?>
                      </td>
                      <td>
      <?php echo WPI_UI::input("name=wpi_settings[currency][types][{$slug}]&value={$title}&type=text&required=required&style=width:150px;margin-left:35px;") ?>
                      </td>

                    </tr>
    <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="4">
                      <input type='button' class="button wpi_button wpi_add_row" value="<?php esc_attr(_e('Add Currency', WPI)); ?>"/>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </td>
      </tr>

      <tr class="column-payment-method-default">
        <th><?php _e("Default Payment Method", WPI) ?></th>
        <td>
          <select id="wp_invoice_payment_method">
            <?php foreach ($wpi_settings['installed_gateways'] as $key => $payment_option) { ?>
                <option value="<?php echo $key; ?>" <?php echo $payment_option['object']->options['default_option']?'SELECTED':''; ?>><?php echo $payment_option['name']; ?></option>
            <?php } ?>
          </select>&nbsp;&nbsp;
        <?php echo WPI_UI::checkbox("class=wpi_client_change_payment_method&name=wpi_settings[client_change_payment_method]&value=yes&label=" . __('Client can change payment option.', WPI), WPI_Functions::is_true($wpi_settings['client_change_payment_method'])) ?>
        </td>
      </tr>

      <tr class='wpi-payment-setting column-paymenth-method-<?php echo $key; ?>'>
        <th><?php _e('Payment Gateways', WPI); ?></th>
        <td>
          <ul>
                  <?php foreach ($wpi_settings['installed_gateways'] as $key => $value) { ?>
              <li>
                    <?php echo WPI_UI::checkbox("&name=wpi_settings[billing][{$key}][allow]&id={$key}&value=true&label=" . $value['name'] . "&class=wpi_billing_section_show", $value['object']->options['allow']); ?>
              </li>
    <?php } ?>
          </ul>
        </td>
      </tr>
      <tr>
        <th>&nbsp;</th>
        <td><div class="wp_invoice_accordion">
                      <?php foreach ((array) $wpi_settings['installed_gateways'] as $key => $value) { ?>
              <div class="<?php echo $key; ?>-setup-section wp_invoice_accordion_section">
                <h3 id="<?php echo $key; ?>-setup-section-header"><a href="#" class="selector"><?php echo $value['name'] ?></a></h3>
                <div> <?php echo!empty($wpi_settings['billing'][$key]) ? WPI_UI::input("type=hidden&name=wpi_settings[billing][{$key}][default_option]&class=billing-default-option billing-{$key}-default-option&value={$wpi_settings['billing'][$key]['default_option']}") : ''; ?>
                  <table class="form-table">

                        <?php
                        if ($value['object']->options['settings'])
                          foreach ($value['object']->options['settings'] as $key2 => $setting_value) {
                            $setting_value['value'] = urldecode($setting_value['value']);
                            $setting_value['type'] = !empty($setting_value['type']) ? $setting_value['type'] : 'input';
                            ?>
                        <tr>
                          <th width="300"><span class="<?php echo (!empty($setting_value['description']) ? "wp_invoice_tooltip" : ""); ?>" title="<?php echo (!empty($setting_value['description']) ? $setting_value['description'] : ''); ?>"><?php echo $setting_value['label']; ?></span></th>
                          <td>
                        <?php if ($setting_value['type'] == 'select') : ?>
            <?php echo WPI_UI::select("name=wpi_settings[billing][{$key}][settings][{$key2}][value]&values=" . serialize($setting_value['data']) . "&current_value={$setting_value['value']}"); ?>
          <?php elseif ($setting_value['type'] == 'textarea') : ?>
                    <?php echo WPI_UI::textarea("name=wpi_settings[billing][{$key}][settings][{$key2}][value]&value={$setting_value['value']}"); ?>
                  <?php elseif ($setting_value['type'] == 'readonly') : ?>
                              <p class="wpi_readonly"><?php echo $setting_value['value']; ?></p>
          <?php else : ?>
            <?php echo WPI_UI::input("type=text&name=wpi_settings[billing][{$key}][settings][{$key2}][value]&value={$setting_value['value']}"); ?>
          <?php endif; ?>
          <?php if (!empty($setting_value['special']) && is_array($setting_value['special']) && $setting_value['type'] != 'select') : ?>
                  <?php $s_count = 0; ?>
                              <br/>
            <?php foreach ($setting_value['special'] as $s_label => $s_value): ?>
                                <span class="wp_invoice_click_me" onclick="jQuery('input[name=\'wpi_settings[billing][<?php echo $key; ?>][settings][<?php echo $key2; ?>][value]\']').val('<?php echo $s_value; ?>');"><?php echo $s_label; ?></span>
                    <?php echo (++$s_count < count($setting_value['special']) ? ' | ' : '' ); ?>
                  <?php endforeach; ?>
          <?php endif; ?>
                          </td>
                        </tr>
        <?php } ?>
                  </table>
                </div>
              </div>
          <?php } ?>
          </div></td>
      </tr>

      <tr>

        <th>
      <?php _e("Manual Payment information", WPI) ?>
        </th>

        <td>
    <?php echo WPI_UI::textarea("name=manual_payment_info&group=wpi_settings&value=" . (!empty($wpi_settings['manual_payment_info']) ? $wpi_settings['manual_payment_info'] : '')) ?>
          <div class="description"><?php _e('If an invoice has no payment gateways, this message will be displayed offering the customer guidance on their course of action.', WPI) ?></div>
        </td>

      </tr>

    <?php if (is_callable(array($wpi_chargify, 'wpi_chargify_settings'))): ?>
        <tr>
          <th>
      <?php _e("Subscription Payments", WPI) ?>
          </th>
          <td>
      <?php $wpi_chargify->wpi_chargify_settings(); ?>
          </td>
        </tr>
    <?php endif; ?>

    </table>

            <?php }

            function email_templates($wpi_settings) {
              ?>
            <?php $notifications_array = apply_filters('wpi_email_templates', $wpi_settings['notification']); ?>
    <?php //WPI_Functions::qc($notifications_array);  ?>
    <table class="ud_ui_dynamic_table widefat form-table" style="margin-bottom:8px;" auto_increment="true">
      <thead>
        <tr>
          <th><?php _e('Name', WPI); ?></th>
          <th style="width:150px;"><?php _e('Subject', WPI); ?></th>
          <th style="width:400px;"><?php _e('Content', WPI); ?></th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($notifications_array as $slug => $notification): ?>
          <tr class="wpi_dynamic_table_row" slug="<?php echo $slug; ?>" new_row="false">
            <td>
              <div style="position:relative;">
                <span class="row_delete">&nbsp;</span>
      <?php echo WPI_UI::input("name=wpi_settings[notification][{$slug}][name]&value={$notification['name']}&type=text&style=width:150px;margin-left:35px;") ?>
              </div>
            </td>
            <td>
      <?php echo WPI_UI::input("name=wpi_settings[notification][{$slug}][subject]&value={$notification['subject']}&type=text&style=width:240px;") ?>
            </td>
            <td>
      <?php echo WPI_UI::textarea("class=wpi_notification_template_content&name=wpi_settings[notification][{$slug}][content]&value=" . urlencode($notification['content'])) ?>
            </td>
          </tr>
    <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3">
            <input type='button' class="button wpi_button wpi_add_row" value="<?php esc_attr(_e('Add Template', WPI)); ?>"/>
          </th>
        </tr>
      </tfoot>
    </table>

    <?php
  }

  function log($wpi_settings) {
    ?>
    <?php $wpi_log = get_option('wpi_log'); ?>
    <?php if (is_array($wpi_log)) : ?>
      <table class="form-table widefat" style="margin-bottom:10px;border-collapse:separate;">
        <thead>
          <tr>
            <th style="width: 200px;"><?php _e('Time', WPI) ?></th>
            <th style="width: 600px;" ><?php _e('Event', WPI) ?></th>
          </tr>
        </thead>
        <tbody>
      <?php foreach (array_reverse($wpi_log) as $event) : ?>
            <tr>
              <td><?php echo date("F j, Y, g:i a", $event[0]); ?></td>
              <td><?php echo $event[1]; ?></td>
            </tr>
      <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th style="width: 200px;"><?php _e('Time', WPI) ?></th>
            <th><?php _e('Event', WPI) ?></th>
          </tr>
        </tfoot>
      </table>
    <?php
    endif;
  }

  function predefined($wpi_settings) {
    ?>
    <p><?php _e('Setup your common services and products in here to streamline invoice creation.', WPI); ?></p>
    <script type="text/javascript">
      jQuery(document).ready( function() {
        wpi_recalc_totals();
      });
    </script>
    <?php
    // Create some blank rows if non exist
    if (!is_array($wpi_settings['predefined_services'])) {
      $wpi_settings['predefined_services'][1] = true;
      $wpi_settings['predefined_services'][2] = true;
    }
    ?>
    <div id="wpi_predefined_services_div">
      <table id="itemized_list" class="ud_ui_dynamic_table itemized_list form-table widefat" auto_increment="true">
        <thead>
          <tr>
            <th style="width:400px;"><?php _e("Name & Description", WPI) ?></th>
            <th style="width:40px;"><?php _e("Qty.", WPI) ?></th>
            <th style="width:40px;"><?php _e("Price", WPI) ?></th>
            <th style="width:40px;"><?php _e("Tax", WPI) ?></th>
            <th style="width:40px;"><?php _e("Total", WPI) ?></th>
          </tr>
        </thead>
        <tbody>
    <?php foreach ($wpi_settings['predefined_services'] as $slug => $itemized_item) : ?>
            <tr class="wpi_dynamic_table_row wp_invoice_itemized_list_row" slug="<?php echo $slug; ?>" new_row="false">
              <td>
                <div class="flexible_width_holder">
                  <div class="flexible_width_holder_content"> <span class="row_delete">&nbsp;</span>
                    <input type="text" class="item_name input_field" name="wpi_settings[predefined_services][<?php echo $slug; ?>][name]" value="<?php echo esc_attr($itemized_item['name']); ?>" />
                    <span class="wpi_add_description_text">&nbsp;<span class="content"><?php _e("Toggle Description", WPI) ?></span></span>
                  </div>
                </div>
                <div class="flexible_width_holder">
                  <div class="flexible_width_holder_content">
                    <textarea style="display:<?php echo (empty($itemized_item['description']) ? 'none' : 'block'); ?>" name="wpi_settings[predefined_services][<?php echo $slug; ?>][description]" class="item_description"><?php echo esc_attr($itemized_item['description']); ?></textarea>
                  </div>
                </div>
              </td>
              <td>
                <span class="row_quantity"><input type="text" autocomplete="off"  value="<?php echo esc_attr($itemized_item['quantity']); ?>" name="wpi_settings[predefined_services][<?php echo $slug; ?>][quantity]" id="qty_item_<?php echo $slug; ?>"  class="item_quantity input_field"></span>
              </td>
              <td>
                <span class="row_price"><input type="text" autocomplete="off" value="<?php echo esc_attr($itemized_item['price']); ?>"  name="wpi_settings[predefined_services][<?php echo $slug; ?>][price]" id="price_item_<?php echo $slug; ?>" class="item_price input_field"></span>
              </td>
              <td>
                <span class="row_tax"><input type="text" autocomplete="off" value="<?php echo esc_attr($itemized_item['tax']); ?>"  name="wpi_settings[predefined_services][<?php echo $slug; ?>][tax]" id="price_item_<?php echo $slug; ?>" class="item_tax input_field"></span>
              </td>
              <td>
                <span class="row_total" id="total_item_<?php echo $slug; ?>" ></span>
              </td>
            </tr>
    <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="5">
              <input type='button' class="button wpi_button wpi_add_row" value="<?php esc_attr(_e("Add Line Item", WPI)) ?>"/>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php
  }

  function help($wpi_settings) {
    ?>
    <script type='text/javascript'>
      jQuery(document).ready(function() {
        //** Do the JS for our view link */
        jQuery('#wpi_settings_view').click(function(e){
          e.preventDefault();
          jQuery('.wpi_settings_row').toggle();
        });
      });
    </script>

    <div class="wpi_settings_block">
    <?php _e('Look up the $wpi_settings global settings array:', WPI); ?> <input type="button" id="wpi_settings_view" value="<?php esc_attr(_e('Toggle $wpi_settings', WPI)); ?>">
      <div class="wpi_settings_row hidden">
    <?php echo WPI_Functions::pretty_print_r($wpi_settings); ?>
      </div>
    </div>

    <div class="wpi_settings_block">
    <?php _e("Restore Backup of WP-Invoice Configuration", WPI); ?>: <input name="wpi_settings[settings_from_backup]" type="file" />
      <a href="<?php echo wp_nonce_url("admin.php?page=wpi_page_settings&wpi_action=download-wpi-backup", 'download-wpi-backup'); ?>"><?php _e('Download Backup of Current WP-Invoice Configuration.', WPI); ?></a>
    </div>

  <?php
  }

  function plugins($wpi_settings) {

    $parseUrl = parse_url(trim(get_bloginfo('url')));
    $this_domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
    ?>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        //** Check plugin updates */
        jQuery("#wpi_ajax_check_plugin_updates").click(function() {
          jQuery('.plugin_status').remove();
          jQuery.post(ajaxurl, {
            action: 'wpi_ajax_check_plugin_updates'
          }, function(data) {
            message = "<div class='plugin_status updated fade'><p>" + data + "</p></div>";
            jQuery(message).insertAfter("h2");
          });
        });
      });
    </script>

    <table class="form-table wpi_premium_feature_intro">
      <tbody>
        <tr>
          <th><?php _e('Check for Updates', WPI); ?></th>
          <td>
    <?php _e('Check for any premium feature updates from the Usability Dynamics Update server:', WPI); ?>
            <input type="button" id="wpi_ajax_check_plugin_updates" value="<?php esc_attr(_e('Check Updates', WPI)); ?>">
          </td>
        </tr>
        <tr>
          <th><?php _e('Your Domain', WPI); ?></th>
          <td>
    <?php _e('When purchasing the premium features you will need to specify your domain to add the license correctly.  This is your domain:', WPI);
    echo ' <b>' . $this_domain . '</b>'; ?>
            <div id="wpi_plugins_ajax_response" class="hidden"></div>
          </td>
        </tr>
        <tr>
          <th><?php _e('WP-Invoice API Key', WPI); ?> for <?php echo $this_domain; ?></th>
          <td>
    <?php echo WPI_UI::input("type=text&name=wpi_api_key&group=wpi_settings&value={$wpi_settings['wpi_api_key']}") ?>
            <div class="description">
      <?php _e('Some subscription based premium features require an API key that is specific to this domain and WP-Invoice. You can get this from your account on <a href="#" target="_blank">UsabilityDynamics.com</a>.', WPI); ?>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <table id="wpi_premium_feature_table" cellpadding="0" cellspacing="0">
      <tr>
    <?php if (!empty($wpi_settings['available_features'])) :
      foreach ($wpi_settings['available_features'] as $plugin_slug => $plugin_data):
        ?>
          <input type="hidden" name="wpi_settings[available_features][<?php echo $plugin_slug; ?>][title]" value="<?php echo esc_attr(stripslashes($plugin_data['title'])); ?>" />
          <input type="hidden" name="wpi_settings[available_features][<?php echo $plugin_slug; ?>][tagline]" value="<?php echo esc_attr(stripslashes($plugin_data['tagline'])); ?>" />
          <input type="hidden" name="wpi_settings[available_features][<?php echo $plugin_slug; ?>][image]" value="<?php echo esc_attr(stripslashes($plugin_data['image'])); ?>" />
          <input type="hidden" name="wpi_settings[available_features][<?php echo $plugin_slug; ?>][description]" value="<?php echo esc_attr(stripslashes($plugin_data['description'])); ?>" />

        <?php $installed = WPI_Functions::check_premium($plugin_slug); ?>
        <?php $active = (@$wpi_settings['installed_features'][$plugin_slug]['disabled'] != 'false' ? true : false); ?>

        <?php if ($installed): ?>
                    <?php /* Do this to preserve settings after page save. */ ?>
            <input type="hidden" name="wpi_settings[installed_features][<?php echo $plugin_slug; ?>][disabled]" value="<?php echo esc_attr(stripslashes($wpi_settings['installed_features'][$plugin_slug]['disabled'])); ?>" />
            <input type="hidden" name="wpi_settings[installed_features][<?php echo $plugin_slug; ?>][name]" value="<?php echo esc_attr(stripslashes($wpi_settings['installed_features'][$plugin_slug]['name'])); ?>" />
            <input type="hidden" name="wpi_settings[installed_features][<?php echo $plugin_slug; ?>][version]" value="<?php echo esc_attr(stripslashes($wpi_settings['installed_features'][$plugin_slug]['version'])); ?>" />
            <input type="hidden" name="wpi_settings[installed_features][<?php echo $plugin_slug; ?>][description]" value="<?php echo esc_attr(stripslashes($wpi_settings['installed_features'][$plugin_slug]['description'])); ?>" />
                    <?php endif; ?>
          <tr class="wpi_premium_feature_block">

            <td valign="top" class="wpi_premium_feature_image">
                    <?php if (!empty($plugin_data['image'])) { ?>
                <a target="_blank" href="https://usabilitydynamics.com/products/wp-invoice/"><img src="<?php echo $plugin_data['image']; ?>" /></a>
                    <?php } ?>
            </td>

            <td valign="top">
              <div class="wpi_box">
                <div class="wpi_box_header">
                  <strong><?php echo $plugin_data['title']; ?></strong>
                  <p><?php echo $plugin_data['tagline']; ?> <a target="_blank" href="https://usabilitydynamics.com/products/wp-invoice/premium-features/"><?php _e('[purchase feature]', WPI) ?></a>
                  </p>
                </div>
                <div class="wpi_box_content">
                  <p><?php echo stripslashes($plugin_data['description']); ?></p>
                </div>

                <div class="wpi_box_footer clearfix">
          <?php if ($installed) { ?>

                    <div class="alignleft">
          <?php
          if ($wpi_settings['installed_features'][$plugin_slug]['needs_higher_wpi_version'] == 'true') {
            printf(__('This feature is disabled because it requires WP-Invoice %1$s or higher.'), $wpi_settings['installed_features'][$plugin_slug]['minimum_wpi_version']);
          } else {
            echo WPI_UI::checkbox("value=true&name=wpi_settings[installed_features][$plugin_slug][disabled]&label=" . __('Disable premium feature.', WPI), $wpi_settings['installed_features'][$plugin_slug]['disabled']);
            ?>
                      </div>
                      <div class="alignright"><?php _e('Feature installed, using version', WPI) ?> <?php echo $wpi_settings['installed_features'][$plugin_slug]['version']; ?>.</div>
          <?php
          }
        } else {
          $pr_link = 'https://usabilitydynamics.com/products/wp-invoice/premium/';
          echo sprintf(__('Please visit <a href="%s">UsabilityDynamics.com</a> to purchase this feature.', WPI), $pr_link);
        }
        ?>
                </div>
              </div>
            </td>
          </tr>
      <?php endforeach;
    else: ?>
        <tr>
          <td class="wpi_features_not_found"><?php _e('There are no available premium features.', WPI); ?></td><td></td>
        </tr>
    <?php endif; ?>
    </table> <?php
  }

}

/* end class WPI_Settings_page */
?>
