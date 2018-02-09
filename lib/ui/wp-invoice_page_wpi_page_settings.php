<?php
global $wpi_settings;

$wpi_settings_tabs = array(
    'basic' => array(
        'label' => __('Main', ud_get_wp_invoice()->domain),
        'position' => 10,
        'callback' => array('WPI_Settings_page', 'basic')
    ),
    'business_process' => array(
        'label' => __('Business Process', ud_get_wp_invoice()->domain),
        'position' => 20,
        'callback' => array('WPI_Settings_page', 'business_process')
    ),
    'payment' => array(
        'label' => __('Payment', ud_get_wp_invoice()->domain),
        'position' => 30,
        'callback' => array('WPI_Settings_page', 'payment')
    ),
    'email_templates' => array(
        'label' => __('E-Mail Templates', ud_get_wp_invoice()->domain),
        'position' => 50,
        'callback' => array('WPI_Settings_page', 'email_templates')
    ),
    'predefined' => array(
        'label' => __('Line Items', ud_get_wp_invoice()->domain),
        'position' => 60,
        'callback' => array('WPI_Settings_page', 'predefined')
    ),
    'help' => array(
        'label' => __('Help', ud_get_wp_invoice()->domain),
        'position' => 500,
        'callback' => array('WPI_Settings_page', 'help')
    ),
    'feedback' => array(
        'label' => __('Feedback', ud_get_wp_invoice()->domain),
        'position' => 600,
        'callback' => function() {
          ?>
          <!--[if lte IE 8]>
          <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
          <![endif]-->
          <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>
          <script>
            hbspt.forms.create({
              portalId: '3453418',
              formId: 'e2062a29-5a8a-410c-89d7-76c49154be1b'
            });
          </script>
          <?php
        }
    )
);

// Allow third-party plugins and premium features to insert and remove tabs via API
$wpi_settings_tabs = apply_filters('wpi_settings_tabs', $wpi_settings_tabs);

//** Put the tabs into position */
usort($wpi_settings_tabs, create_function('$a,$b', ' return $a["position"] - $b["position"]; '));

if (isset($_REQUEST['message'])) {
  switch ($_REQUEST['message']) {
    case 'updated':
      WPI_Functions::add_message(__("Settings updated.", ud_get_wp_invoice()->domain));
      break;
  }
}
?>
<script type="text/javascript">

  var wpi = {
    'currency':'<?php echo $wpi_settings['currency']['symbol'][$wpi_settings['currency']['default_currency_code']]; ?> ',
    'thousandsSeparator':'<?php echo!isset($wpi_settings['thousands_separator_symbol']) ? ',' : ($wpi_settings['thousands_separator_symbol'] == '0' ? '' : $wpi_settings['thousands_separator_symbol']) ?>',
    'decimalSeparator':'<?php echo !isset( $wpi_settings['decimal_separator_symbol'] )?'.':($wpi_settings['decimal_separator_symbol'] == '0'?'':$wpi_settings['decimal_separator_symbol']) ?>'
  };

  jQuery(document).ready( function() {
    var wp_invoice_settings_page = jQuery("#wp_invoice_settings_page").tabs({cookie: {expires: 30, name: 'wp_invoice_settings_page_tabs'}});
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
    <h2><?php _e("WP-Invoice Global Settings", ud_get_wp_invoice()->domain) ?></h2>

    <?php WPI_Functions::print_messages(); ?>

    <div id="wp_invoice_settings_page" class="wpi_tabs wp_invoice_tabbed_content">
      <ul class="wp_invoice_settings_tabs tabs">
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
            echo __('Warning:', ud_get_wp_invoice()->domain) . ' ' . implode(':', $tab['callback']) . ' ' . __('not found', ud_get_wp_invoice()->domain) . '.';
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
              <input type="submit" value="<?php esc_attr(_e('Save All Settings', ud_get_wp_invoice()->domain)) ?>" class="button-primary">
            </div>
            <div class="clear"></div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div><?php /* end: .wrap */ ?>

<?php

/**
 * Settings Page class
 */
class WPI_Settings_page {

  /**
   * Basic tab
   *
   * @global type $wpdb
   * @param type $wpi_settings
   */
  static function basic($wpi_settings) {
    wp_enqueue_media();
    ?>

    <table class="form-table">
      <tr>
        <th width="200"><?php _e("Business Logo", ud_get_wp_invoice()->domain); ?></th>
        <td>
          <input type="hidden" id="business_logo_path" name="wpi_settings[business_logo]" value="<?php echo !empty($wpi_settings['business_logo']) ?$wpi_settings['business_logo']: '' ; ?>" />

          <table>
            <tr>
              <td>
                <img id="business_logo_img" style="max-width:100px;" src="<?php echo !empty( $wpi_settings['business_logo'] ) ? $wpi_settings['business_logo'] : '//placehold.it/100?text='.__('No Logo', ud_get_wp_invoice()->domain); ?>" />
              </td>
              <td>
                <button class="button-secondary business-logo-select" style="width:100px;" data-uploader_title="<?php _e('Select Logo', ud_get_wp_invoice()->domain); ?>"><?php _e('Select Logo', ud_get_wp_invoice()->domain); ?></button>
                <?php if ( !empty( $wpi_settings['business_logo'] ) ): ?>
                  <button id="business-logo-disable" class="button-secondary business-logo-disable"><?php _e('Disable', ud_get_wp_invoice()->domain); ?></button>
                <?php endif; ?>
              </td>
            </tr>
          </table>

          <script type="text/javascript">
            jQuery(document).ready(function(){
              jQuery('.business-logo-select').business_logo_select({
                url_input: "#business_logo_path",
                image: "#business_logo_img",
                disable: "#business-logo-disable"
              });
            });
          </script>
        </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Business Name", ud_get_wp_invoice()->domain) ?></th>
        <td><?php echo WPI_UI::input(array(
              'type'=>'text',
              'name'=>'business_name',
              'group'=>'wpi_settings',
              'value'=>$wpi_settings['business_name']
          )); ?> </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Business Address", ud_get_wp_invoice()->domain) ?></th>
        <td><?php echo WPI_UI::textarea(array(
              'name'=>'business_address',
              'group'=>'wpi_settings',
              'value'=>$wpi_settings['business_address']
          )); ?> </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Business Phone", ud_get_wp_invoice()->domain) ?></th>
        <td><?php echo WPI_UI::input("type=text&name=business_phone&group=wpi_settings&value={$wpi_settings['business_phone']}") ?> </td>
      </tr>
      <tr>
        <th width="200"><?php _e("Email Address", ud_get_wp_invoice()->domain) ?></th>
        <td><?php echo WPI_UI::input("type=text&name=email_address&group=wpi_settings&value={$wpi_settings['email_address']}") ?> </td>
      </tr>

      <tr>
        <th><?php _e("Display Styles", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul>
            <?php if (WPI_Functions::has_theme_specific_stylesheet()) : ?>
              <li>
                <?php echo WPI_UI::checkbox("name=wpi_settings[do_not_load_theme_specific_css]&value=yes&label=" . __('Do <b>not</b> load theme specific styles.', ud_get_wp_invoice()->domain), WPI_Functions::is_true($wpi_settings['do_not_load_theme_specific_css'])); ?>
                <div class="description"><?php echo sprintf( __("WP-Invoice is shipped with a custom stylesheet designed for <b>%s</b>", ud_get_wp_invoice()->domain), wp_get_theme() ); ?></div>
              </li>
            <?php endif; ?>
            <li><?php echo WPI_UI::checkbox("name=wpi_settings[use_css]&value=yes&label=" . __('Load default CSS styles on the front-end', ud_get_wp_invoice()->domain), WPI_Functions::is_true($wpi_settings['use_css'])); ?></li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e("Tax Handling", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul class="wpi_something_advanced_wrapper">
            <li><label for="wpi_tax_method"><?php _e('Calculate Taxable Subtotal', ud_get_wp_invoice()->domain) ?> <?php echo WPI_UI::select("name=tax_method&group=wpi_settings&values=" . serialize(array("after_discount" => __("After Discount", ud_get_wp_invoice()->domain), "before_discount" => __("Before Discount", ud_get_wp_invoice()->domain))) . "&current_value=" . (!empty($wpi_settings['tax_method']) ? $wpi_settings['tax_method'] : "")); ?> </label></li>
            <li><?php echo WPI_UI::checkbox("name=use_global_tax&class=wpi_show_advanced&group=wpi_settings&value=true&label=" . __('Use global tax.', ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['use_global_tax'])?$wpi_settings['use_global_tax']:false ) ); ?></li>
            <li class="wpi_advanced_option">
              Tax value: <?php echo WPI_UI::input("type=text&style=width:50px;&name=global_tax&group=wpi_settings&value={$wpi_settings['global_tax']}") ?>%
              <div class="description wpi_advanced_option"><?php _e("This will make all new invoices have default Tax value which can be changed for different invoice.", ud_get_wp_invoice()->domain) ?></div>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th><?php _e("Advanced Settings", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul class="wpi_settings_list wpi_something_advanced_wrapper">
            <li><?php echo WPI_UI::checkbox("name=allow_deposits&class=wpi_show_advanced&group=wpi_settings&value=true&label=" . __('Allow partial payments.', ud_get_wp_invoice()->domain), $wpi_settings['allow_deposits']); ?></li>

            <li class="wpi_advanced_option"><?php echo WPI_UI::checkbox("name=allow_deposits_by_default&group=wpi_settings&value=true&label=" . __('Partial payments allowed by default.', ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['allow_deposits_by_default'])?$wpi_settings['allow_deposits_by_default']:false ) ); ?></li>

            <li><?php echo WPI_UI::checkbox("name=show_recurring_billing&group=wpi_settings&value=true&label=" . __('Show recurring billing options.', ud_get_wp_invoice()->domain), $wpi_settings['show_recurring_billing']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=force_https&group=wpi_settings&value=true&label=" . __('Enforce HTTPS on invoice pages, if available on this server.', ud_get_wp_invoice()->domain), $wpi_settings['force_https']); ?> </li>

            <li>
              <label for="wpi_user_level"><?php _e("Minimum user level to manage WP-Invoice", ud_get_wp_invoice()->domain) ?> <?php echo WPI_UI::select("name=user_level&group=wpi_settings&values=" . serialize(array("0" => __('Subscriber', ud_get_wp_invoice()->domain), "1" => __('Contributor', ud_get_wp_invoice()->domain), "2" => __('Author', ud_get_wp_invoice()->domain), "5" => __('Editor', ud_get_wp_invoice()->domain), "8" => __('Administrator', ud_get_wp_invoice()->domain))) . "&current_value={$wpi_settings['user_level']}"); ?> </label>
            </li>
            <li>
              <?php _e("Using Godaddy Hosting:", ud_get_wp_invoice()->domain) ?> <?php echo WPI_UI::select("name=using_godaddy&group=wpi_settings&values=yon&current_value={$wpi_settings['using_godaddy']}"); ?>
              <div class="description"><?php _e("Special proxy must be used to process credit card transactions on GoDaddy servers.", ud_get_wp_invoice()->domain) ?></div>
            </li>

            <li>
              <?php
              if (!file_exists($wpi_settings['frontend_template_path'])) {
                $no_template_folder = true;
              }
              echo WPI_UI::checkbox("class=use_custom_templates&name=wpi_settings[use_custom_templates]&value=yes&label=" . __("Use custom templates. If checked, WP-Invoice will use templates in the 'wpi' folder in your active theme's folder.", ud_get_wp_invoice()->domain), WPI_Functions::is_true($wpi_settings['use_custom_templates']));
              ?>
            </li>
            <li class="wpi_use_custom_template_settings" style="<?php echo ( empty($wpi_settings['use_custom_templates']) || $wpi_settings['use_custom_templates'] == 'no' ? 'display:none;' : ''); ?>">
              <?php if (!empty($no_template_folder)) { ?>
                <span class="wpi_red_notification"><?php _e('Note: Currently there is no "wpi" folder in your active template\'s folder, WP-Invoice will attempt to create it after saving.', ud_get_wp_invoice()->domain) ?></span>
              <?php } else { ?>
                <span class="wpi_green_notification"><?php _e('A "wpi" folder has been found, any files with the proper file names will be used instead of the default template files.', ud_get_wp_invoice()->domain) ?></span>
    <?php } ?>
            </li>
            <li><input class="button wpi_install_custom_templates" type="button" value="<?php _e("Install", ud_get_wp_invoice()->domain); ?>" /> <?php _e("the custom templates inside the <b>wpi</b> folder in your active theme's folder.", ud_get_wp_invoice()->domain); ?></li>
            <li class="wpi_install_custom_templates_result" style="display:none;"></li>
            <li>
              <label for="wpi_thousands_separator_symbol">
                <?php _e('Thousands Separator Symbol', ud_get_wp_invoice()->domain); ?>
                <?php
                echo WPI_UI::select(array(
                    'name' => 'thousands_separator_symbol',
                    'group' => 'wpi_settings',
                    'values' => array(
                        '0' => __('None', ud_get_wp_invoice()->domain),
                        '.' => '.(period)',
                        ',' => ',(comma)'
                    ),
                    'current_value' => !isset($wpi_settings['thousands_separator_symbol']) ? ',' : $wpi_settings['thousands_separator_symbol']
                ));
                ?>
              </label>
            </li>
            <li>
              <label for="wpi_thousands_separator_symbol">
                <?php _e('Decimal Separator Symbol', ud_get_wp_invoice()->domain); ?>
                <?php
                echo WPI_UI::select(array(
                    'name' => 'decimal_separator_symbol',
                    'group' => 'wpi_settings',
                    'values' => array(
                        '.' => '.(period)',
                        ',' => ',(comma)'
                    ),
                    'current_value' => !isset($wpi_settings['decimal_separator_symbol']) ? '.' : $wpi_settings['decimal_separator_symbol']
                ));
                ?>
              </label>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[logged_in_only]&value=true&label=" . __("Show invoices only for logged in recipients.", ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['logged_in_only'])?$wpi_settings['logged_in_only']:false )); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[send_password_to_new_users]&value=true&label=" . __("Send passwords to newly created recipients.", ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['send_password_to_new_users'])?$wpi_settings['send_password_to_new_users']:false )); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[turn_off_compatibility_mode]&value=true&label=" . __("Turn off compatibility mode.", ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['turn_off_compatibility_mode'])?$wpi_settings['turn_off_compatibility_mode']:false )); ?>
              <div class="description"><?php _e( 'By default the Compatibility Mode is on. If you encounter problems displaying your invoices then turn it off.', ud_get_wp_invoice()->domain ); ?></div>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[pre_release_updates]&value=true&label=" . __("Enable pre-release updates.", ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['pre_release_updates'])?$wpi_settings['pre_release_updates']:false )); ?>
            </li>
          </ul>
        </td>
      </tr>

      <tr>
        <th></th>
        <td>
          <ul class="wpi_something_advanced_wrapper">
            <li>
              <?php
              echo WPI_UI::checkbox(array(
                  'name'  => 'wpi_settings[tos_checkbox]',
                  'value' => 'true',
                  'label' => __('Add "Terms &amp; Conditions" checkbox to regular invoices.', ud_get_wp_invoice()->domain),
                  'class' => 'wpi_show_advanced'
              ), isset($wpi_settings['tos_checkbox'])?$wpi_settings['tos_checkbox']:false );
              ?>
              <div class="description"><?php _e('This option allows you to add "Terms &amp; Conditions" checkbox to your regular invoices. Be sure you have specified Terms page ID below.', ud_get_wp_invoice()->domain);?></div>
            </li>
            <li class="wpi_advanced_option">
              <label for=""><?php _e( '"T&amp;C" Page ID:', ud_get_wp_invoice()->domain ); ?></label>
              <?php echo WPI_UI::input(array(
                  'type' => 'text',
                  'style' => 'width:50px;',
                  'name' => 'tos_page_id',
                  'group' => 'wpi_settings',
                  'value' => !empty($wpi_settings['tos_page_id'])?$wpi_settings['tos_page_id']:''
              )); ?>
              <div class="description wpi_advanced_option"><?php _e('Numeric value of WordPress page ID that has "Terms &amp; Conditions".', ud_get_wp_invoice()->domain) ?></div>
            </li>
          </ul>
        </td>
      </tr>

    <?php do_action('wpi_settings_page_basic_settings', $wpi_settings); ?>


    </table>

  <?php
  }

  /**
   * Business tab
   *
   * @global type $wpdb
   * @param type $wpi_settings
   */
  static function business_process($wpi_settings) {

    global $wpdb;
    ?>

    <table class="form-table">
      <tr>
        <th><?php _e("When creating an invoice", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li><?php echo WPI_UI::checkbox("name=increment_invoice_id&group=wpi_settings&value=true&label=" . __('Automatically increment the invoice\'s custom ID by one.', ud_get_wp_invoice()->domain), $wpi_settings['increment_invoice_id']) ?></li>
          </ul>
        </td>
      </tr>

      <tr>
        <th> <a class="wp_invoice_tooltip"  title="<?php _e('Select whether to overwrite all page content, insert at the bottom of the content, or to look for the [wp-invoice] tag.', ud_get_wp_invoice()->domain); ?>">
    <?php _e('How to Insert Invoice', ud_get_wp_invoice()->domain); ?>
          </a></th>
        <td><?php echo WPI_UI::select("name=where_to_display&group=wpi_settings&values=" . serialize(apply_filters('wpi_where_to_display_options', array("overwrite" => __("Overwrite All Page Content", ud_get_wp_invoice()->domain), "below_content" => __("Place Below Content", ud_get_wp_invoice()->domain), "above_content" => __("Above Content", ud_get_wp_invoice()->domain), "replace_tag" => __("Replace [wp-invoice] Tag", ud_get_wp_invoice()->domain)))) . "&current_value={$wpi_settings['where_to_display']}"); ?> <?php _e('If using the tag, place <span class="wp_invoice_explanation">[wp-invoice]</span> somewhere within your page content.', ud_get_wp_invoice()->domain) ?> </td>
      </tr>


      <tr>
        <th><?php _e("When viewing an invoice", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li><?php echo WPI_UI::checkbox(
                  array(
                    'name' => 'activate_client_dashboard',
                    'group' => 'wpi_settings',
                    'value' => 'true',
                    'label' => __('Activate Client Dashboard.', ud_get_wp_invoice()->domain)
                  ), !empty($wpi_settings['activate_client_dashboard'])?$wpi_settings['activate_client_dashboard']:'false'); ?>
            <span style="font-size: 10px;color:red;font-weight:bold;"><?php _e('New', ud_get_wp_invoice()->domain); ?></span></li>
            <li>
              <label for="wpi_settings[web_dashboard_page]"><?php _e("Display dashboard on the", ud_get_wp_invoice()->domain) ?>
                <select name='wpi_settings[web_dashboard_page]'>
                  <option><?php _e( 'Not selected', ud_get_wp_invoice()->domain ); ?></option>
                  <?php
                  $list_pages = $wpdb->get_results("SELECT ID, post_title, post_name, guid FROM " . $wpdb->prefix . "posts WHERE post_status = 'publish' AND post_type = 'page' ORDER BY post_title");
                  $wp_invoice_web_dashboard = $wpi_settings['web_dashboard_page'];
                  foreach ($list_pages as $page) {
                    echo "<option  style='padding-right: 10px;'";
                    if (isset($wp_invoice_web_dashboard) && $wp_invoice_web_dashboard == $page->ID)
                      echo " SELECTED ";
                    echo " value=\"" . $page->ID . "\">" . $page->post_title . "</option>\n";
                  }
                  echo "</select>";
                  ?>
                  <?php _e("page.", ud_get_wp_invoice()->domain) ?> </label>
              <span style="font-size: 10px;color:red;font-weight:bold;"><?php _e('New', ud_get_wp_invoice()->domain); ?></span>
            </li>
            <li>
              <label for="wpi_settings[web_invoice_page]"><?php _e("Display invoices on the", ud_get_wp_invoice()->domain) ?>
                <select name='wpi_settings[web_invoice_page]'>
                  <option></option>
                  <?php
                  $wp_invoice_web_invoice_page = $wpi_settings['web_invoice_page'];
                  foreach ($list_pages as $page) {
                    echo "<option  style='padding-right: 10px;'";
                    if (isset($wp_invoice_web_invoice_page) && $wp_invoice_web_invoice_page == $page->ID)
                      echo " SELECTED ";
                    echo " value=\"" . $page->ID . "\">" . $page->post_title . "</option>\n";
                  }
                  echo "</select>";
                  ?>
    <?php _e("page.", ud_get_wp_invoice()->domain) ?> </label>
            </li>
            <li><?php echo WPI_UI::checkbox("name=replace_page_title_with_subject&group=wpi_settings&value=true&label=" . __('Replace HTML title with invoice subject when viewing invoice.', ud_get_wp_invoice()->domain), $wpi_settings['replace_page_title_with_subject']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=replace_page_heading_with_subject&group=wpi_settings&value=true&label=" . __('Replace page heading and navigation link title with invoice subject when viewing invoice.', ud_get_wp_invoice()->domain), $wpi_settings['replace_page_heading_with_subject']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=hide_page_title&group=wpi_settings&value=true&label=" . __('Hide page heading and navigation link completely.', ud_get_wp_invoice()->domain), $wpi_settings['hide_page_title']); ?></li>

            <li><?php echo WPI_UI::checkbox("name=show_business_address&group=wpi_settings|globals&value=true&label=" . __('Show my business address.', ud_get_wp_invoice()->domain), $wpi_settings['globals']['show_business_address']); ?> </li>
            
            
            <li><?php echo WPI_UI::checkbox("name=show_quantities&group=wpi_settings|globals&value=true&label=" . __('Show quantity breakdowns in the itemized list.', ud_get_wp_invoice()->domain), $wpi_settings['globals']['show_quantities']); ?> </li>
          </ul></td>
      </tr>
      <tr>
        <th><?php _e("After a payment has been completed", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li><?php echo WPI_UI::checkbox("name=send_thank_you_email&group=wpi_settings&value=true&label=" . __('Send email confirmation to the client.', ud_get_wp_invoice()->domain), $wpi_settings['send_thank_you_email']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=cc_thank_you_email&group=wpi_settings&value=true&label=" . sprintf(__('Send email notification to the address set for administrative purposes from <a href="%s">General Settings</a>', ud_get_wp_invoice()->domain), get_option('home') . "/wp-admin/options-general.php") . " (<u>" . get_option('admin_email') . "</u>)", $wpi_settings['cc_thank_you_email']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=send_invoice_creator_email&group=wpi_settings&value=true&label=" . __('Send email notification to invoice creator.', ud_get_wp_invoice()->domain), $wpi_settings['send_invoice_creator_email']); ?></li>
            <li><?php echo WPI_UI::checkbox("name=use_wp_crm_to_send_notifications&group=wpi_settings&value=true&label=" . __('Use CRM to send notifications', ud_get_wp_invoice()->domain) . (((!function_exists('wp_crm_send_notification') )) ? "&special=disabled='disabled'" : ''), ((function_exists('wp_crm_send_notification')) ? $wpi_settings['use_wp_crm_to_send_notifications'] : false)); ?><div class="description"><?php if (!function_exists('wp_crm_send_notification')) : ?><?php echo sprintf( __('Get <a class="small" href="%s">WP-CRM plugin</a> to enhance notification management.', ud_get_wp_invoice()->domain), admin_url('plugin-install.php?tab=search&amp;type=term&amp;s=WP-CRM+andypotanin') ); ?><?php else: ?>You can visit WP-CRM <a class="small" href="<?php echo admin_url('admin.php?page=wp_crm_settings#tab_notifications'); ?>">Notifications</a> tab to adjust email templates.<?php endif; ?></div></li>
          </ul>
        </td>
      </tr>
      <tr>
        <th>
          <a class="wp_invoice_tooltip" title="<?php _e("This options allow you to change the default email address that WordPress sends it's mail from, and the name of the sender that the email is from.", ud_get_wp_invoice()->domain); ?>">
              <?php _e("Mail From options", ud_get_wp_invoice()->domain); ?>
          </a>
        </th>
        <td>
          <ul class="wpi_settings_list">
            <li>
              <?php
              echo WPI_UI::input(array(
                  'label' => __('Sender Name', ud_get_wp_invoice()->domain),
                  'type' => 'text',
                  'name' => 'mail_from_sender_name',
                  'group' => 'wpi_settings',
                  'value' => empty($wpi_settings['mail_from_sender_name']) ? 'WordPress' : $wpi_settings['mail_from_sender_name']
              ));
              ?>
              <div class="description"><?php _e('The sender name that the email is from', ud_get_wp_invoice()->domain); ?></div>
            </li>
            <li>
              <?php
              echo WPI_UI::input(array(
                  'label' => __('User E-mail', ud_get_wp_invoice()->domain),
                  'type' => 'text',
                  'name' => 'mail_from_user_email',
                  'group' => 'wpi_settings',
                  'value' => empty($wpi_settings['mail_from_user_email']) ? 'wordpress@' . strtolower($_SERVER['SERVER_NAME']) : $wpi_settings['mail_from_user_email']
              ));
              ?>
              <div class="description"><?php _e('Email address e.g. username@example.com', ud_get_wp_invoice()->domain); ?></div>
            </li>
            <li>
            <?php
            echo WPI_UI::checkbox(array(
                'label' => __("Apply 'Mail From' settings.", ud_get_wp_invoice()->domain),
                'name' => 'change_mail_from',
                'value' => 'true',
                'group' => 'wpi_settings'
                    ), isset($wpi_settings['change_mail_from'])?$wpi_settings['change_mail_from']:false );
            ?>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th>
          <a class="wp_invoice_tooltip" title="<?php _e("If you are using <b>Google Analytics Site Tracking</b> code on your site then you can track WP-Invoice events.", ud_get_wp_invoice()->domain); ?>">
    <?php _e('Google Analytics Events Tracking', ud_get_wp_invoice()->domain); ?>
          </a>
        </th>
        <td>
          <ul class="wpi_settings_list">
            <!-- Google Analytics Event Tracking option -->
            <li>
    <?php echo WPI_UI::checkbox("name=wpi_settings[ga_event_tracking][enabled]&value=true&label=" . __('I want to track events.', ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['ga_event_tracking']['enabled'])?$wpi_settings['ga_event_tracking']['enabled']:false )); ?>
            </li>
            <li class="wpi_ga_events_list" style="<?php echo (empty($wpi_settings['ga_event_tracking']['enabled']) || $wpi_settings['ga_event_tracking']['enabled'] == 'false' ) ? 'display:none;' : ''; ?>">
              <ul>
                <li>
                  <strong><?php _e('Track Invoices events:', ud_get_wp_invoice()->domain); ?></strong>
                  <ul class="wpi_sublist">
                    <li>
    <?php echo WPI_UI::checkbox("name=wpi_settings[ga_event_tracking][events][invoices][attempting_pay_invoice]&value=true&label=" . __('Attempting to pay Invoices', ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['ga_event_tracking']['events']['invoices']['attempting_pay_invoice'])?$wpi_settings['ga_event_tracking']['events']['invoices']['attempting_pay_invoice']:false )); ?>
                    </li>
                    <li>
    <?php echo WPI_UI::checkbox("name=wpi_settings[ga_event_tracking][events][invoices][view_invoice]&value=true&label=" . __('View Invoices', ud_get_wp_invoice()->domain), WPI_Functions::is_true( isset($wpi_settings['ga_event_tracking']['events']['invoices']['view_invoice'])?$wpi_settings['ga_event_tracking']['events']['invoices']['view_invoice']:false )); ?>
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

  /**
   * Payment tab
   *
   * @param type $wpi_settings
   */
  static function payment($wpi_settings) {
    ?>
    <table class="form-table">
      <tr>
        <th><?php _e("Default Currency", ud_get_wp_invoice()->domain); ?></th>
        <td><?php echo WPI_UI::select("name=wpi_settings[currency][default_currency_code]&values=" . serialize($wpi_settings['currency']['types']) . "&current_value={$wpi_settings['currency']['default_currency_code']}"); ?></td>
      </tr>
      <tr>
        <th></th>
        <td>

    <?php $currency_array = apply_filters('wpi_currency', $wpi_settings['currency']); ?>

          <div id="currency-list">
            <h3><a href="#"><?php _e("Currency list", ud_get_wp_invoice()->domain); ?></a></h3>
            <div>
              <table class="ud_ui_dynamic_table widefat form-table edit-currency-tab" style="margin-bottom:8px;" auto_increment="true">
                <thead>
                  <tr>
                    <th style="width:10%; padding-left: 50px;"><?php _e('Code', ud_get_wp_invoice()->domain); ?></th>
                    <th style="text-align: center;"><?php _e('Symbol', ud_get_wp_invoice()->domain); ?></th>
                    <th style="width:40%; text-align: center;"><?php _e('Name', ud_get_wp_invoice()->domain); ?></th>
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
                      <input type='button' class="button wpi_button wpi_add_row" value="<?php esc_attr(_e('Add Currency', ud_get_wp_invoice()->domain)); ?>"/>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </td>
      </tr>

      <tr class="column-payment-method-default">
        <th><?php _e("Default Payment Method", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <select id="wp_invoice_payment_method">
            <?php foreach ( (array)$wpi_settings['installed_gateways'] as $key => $payment_option ) { ?>
                <option value="<?php echo $key; ?>" <?php echo $payment_option['object']->options['default_option']?'SELECTED':''; ?>><?php echo $payment_option['name']; ?></option>
            <?php } ?>
          </select>&nbsp;&nbsp;
        <?php echo WPI_UI::checkbox("class=wpi_client_change_payment_method&name=wpi_settings[client_change_payment_method]&value=yes&label=" . __('Client can change payment option.', ud_get_wp_invoice()->domain), WPI_Functions::is_true($wpi_settings['client_change_payment_method'])) ?>
        </td>
      </tr>

      <tr class='wpi-payment-setting column-paymenth-method-<?php echo $key; ?>'>
        <th><?php _e('Payment Gateways', ud_get_wp_invoice()->domain); ?></th>
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
                            $setting_value['value'] = urldecode(isset($setting_value['value'])?$setting_value['value']:'');
                            $setting_value['type'] = !empty($setting_value['type']) ? $setting_value['type'] : 'input';
                            ?>
                        <tr>
                          <th width="300"><span class="<?php echo (!empty($setting_value['description']) ? "wp_invoice_tooltip" : ""); ?>" title="<?php echo (!empty($setting_value['description']) ? $setting_value['description'] : ''); ?>"><?php echo !empty($setting_value['label'])?$setting_value['label']:''; ?></span></th>
                          <td>
                        <?php if ($setting_value['type'] == 'select') : ?>
            <?php echo WPI_UI::select("name=wpi_settings[billing][{$key}][settings][{$key2}][value]&values=" . serialize($setting_value['data']) . "&current_value={$setting_value['value']}"); ?>
          <?php elseif ($setting_value['type'] == 'textarea') : ?>
                    <?php echo WPI_UI::textarea("name=wpi_settings[billing][{$key}][settings][{$key2}][value]&value={$setting_value['value']}"); ?>
                  <?php elseif ($setting_value['type'] == 'readonly') : ?>
                              <p class="wpi_readonly"><?php echo $setting_value['value']; ?></p>
            <?php elseif (isset($setting_value['type']) && $setting_value['type'] == 'static') : ?>
                    <p><?php echo !empty($setting_value['data'])?$setting_value['data']:''; ?></p>
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
      <?php _e("Manual Payment information", ud_get_wp_invoice()->domain) ?>
        </th>

        <td>
    <?php echo WPI_UI::textarea("name=manual_payment_info&group=wpi_settings&value=" . (!empty($wpi_settings['manual_payment_info']) ? $wpi_settings['manual_payment_info'] : '')) ?>
          <div class="description"><?php _e('If an invoice has no payment gateways, this message will be displayed offering the customer guidance on their course of action.', ud_get_wp_invoice()->domain) ?></div>
        </td>

      </tr>

    </table>

<?php }

  /**
   * Email templates tab
   *
   * @param type $wpi_settings
   */
  static function email_templates($wpi_settings) {
?>
<?php $notifications_array = apply_filters('wpi_email_templates', $wpi_settings['notification']); ?>
    <table class="ud_ui_dynamic_table widefat form-table" style="margin-bottom:8px;" auto_increment="true">
      <thead>
        <tr>
          <th><?php _e('Name', ud_get_wp_invoice()->domain); ?></th>
          <th style="width:150px;"><?php _e('Subject', ud_get_wp_invoice()->domain); ?></th>
          <th style="width:400px;"><?php _e('Content', ud_get_wp_invoice()->domain); ?></th>
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
            <input type='button' class="button wpi_button wpi_add_row" value="<?php esc_attr(_e('Add Template', ud_get_wp_invoice()->domain)); ?>"/>
          </th>
        </tr>
      </tfoot>
    </table>

    <?php
  }

  /**
   * Predefined Items tab
   *
   * @param type $wpi_settings
   */
  static function predefined($wpi_settings) {

    do_action('wpi_settings_before_predefined', $wpi_settings);

    ?>
    <p><?php _e('Setup your common services and products in here to streamline invoice creation.', ud_get_wp_invoice()->domain); ?></p>
    <script type="text/javascript">
      jQuery(document).ready( function() {
        wpi_recalc_totals();
        jQuery('#wpi_predefined_services_div tbody').sortable({
          handle: '.row_drag',
          stop: function( event, ui ) {
            jQuery.each(jQuery('tr', ui.item.parent()), function(key, tr){
              var slug = jQuery(tr).attr('slug');
              jQuery('input,textarea,select', tr).each(function(k, v){
                jQuery(v).attr('name', String(jQuery(v).attr('name')).replace(String(slug), String(key)));
              });
              jQuery(tr).attr('slug', key);
            });
          }
        });
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
            <th style="width:400px;"><?php _e("Name & Description", ud_get_wp_invoice()->domain) ?></th>
            <th style="width:40px;"><?php _e("Qty.", ud_get_wp_invoice()->domain) ?></th>
            <th style="width:40px;"><?php _e("Price", ud_get_wp_invoice()->domain) ?></th>
            <th style="width:40px;"><?php _e("Tax %", ud_get_wp_invoice()->domain) ?></th>
            <th style="width:40px;"><?php _e("Total", ud_get_wp_invoice()->domain) ?></th>
            <?php do_action( 'wpi_predefined_services_after_table_head_col' ); ?>
          </tr>
        </thead>
        <tbody>
    <?php foreach ($wpi_settings['predefined_services'] as $slug => $itemized_item) : ?>
            <tr class="wpi_dynamic_table_row wp_invoice_itemized_list_row" slug="<?php echo $slug; ?>" new_row="false">
              <td>
                <span>
                  <span class="row_delete">&nbsp;</span>
                  <span class="row_drag" style="left: 54px;">&nbsp;</span>
                    <input type="text" class="item_name input_field" name="wpi_settings[predefined_services][<?php echo $slug; ?>][name]" value="<?php echo htmlspecialchars(stripslashes($itemized_item['name'])); ?>" />
                    <span class="wpi_add_description_text">&nbsp;<span class="content"><?php _e("Toggle Description", ud_get_wp_invoice()->domain) ?></span></span>
                </span>
                <div class="flexible_width_holder">
                  <div class="flexible_width_holder_content">
                    <textarea style="display:<?php echo (empty($itemized_item['description']) ? 'none' : 'block'); ?>" name="wpi_settings[predefined_services][<?php echo $slug; ?>][description]" class="item_description"><?php echo esc_attr(!empty($itemized_item['description'])?htmlspecialchars(stripslashes($itemized_item['description'])):''); ?></textarea>
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
                <span class="row_tax"><input type="text" autocomplete="off" value="<?php echo esc_attr(!empty($itemized_item['tax'])?$itemized_item['tax']:''); ?>"  name="wpi_settings[predefined_services][<?php echo $slug; ?>][tax]" id="price_item_<?php echo $slug; ?>" class="item_tax input_field"></span>
              </td>
              <td>
                <span class="row_total" id="total_item_<?php echo $slug; ?>" ></span>
              </td>
              <?php do_action( 'wpi_predefined_services_after_table_body_col', array( 'slug' => $slug, 'item' => $itemized_item ) ); ?>
            </tr>
    <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="<?php echo apply_filters( 'wpi_predefined_services_footer_colspan', 5 ) ?>">
              <input type='button' class="button wpi_button wpi_add_row" value="<?php esc_attr(_e("Add Line Item", ud_get_wp_invoice()->domain)) ?>"/>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php
  }

  /**
   * Help tab
   *
   * @param type $wpi_settings
   */
  static function help($wpi_settings) {
    ?>
    <script type='text/javascript'>
      jQuery(document).ready(function() {
        //** Do the JS for our view link */
        jQuery('.wpi_settings_view').click(function(e){
          e.preventDefault();
          jQuery(this).parent().find('.wpi_settings_row').toggle();
        });
      });
    </script>

    <?php
    do_action('wpi_settings_before_help');
    ?>

    <div class="wpi_settings_block">
    <?php _e('Look up the $wpi_settings global settings array:', ud_get_wp_invoice()->domain); ?> <input type="button" class="wpi_settings_view button-primary" value="<?php esc_attr(_e('Toggle $wpi_settings', ud_get_wp_invoice()->domain)); ?>">
      <div class="wpi_settings_row hidden">
    <?php echo WPI_Functions::pretty_print_r($wpi_settings); ?>
      </div>
    </div>

    <div class="wpi_settings_block">
    <?php _e("Restore Backup of WP-Invoice Configuration", ud_get_wp_invoice()->domain); ?>: <input name="wpi_settings[settings_from_backup]" type="file" />
      <a href="<?php echo wp_nonce_url("admin.php?page=wpi_page_settings&wpi_action=download-wpi-backup", 'download-wpi-backup'); ?>"><?php _e('Download Backup of Current WP-Invoice Configuration.', ud_get_wp_invoice()->domain); ?></a>
    </div>

    <?php
    do_action('wpi_settings_after_help');
    ?>

  <?php
  }
}