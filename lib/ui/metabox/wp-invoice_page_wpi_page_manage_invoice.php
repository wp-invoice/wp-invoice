<?php

/**
 * Hidden metabox information stored in DB.
 * WordPress gets this information using the get_hidden_meta_boxes function:
 * $hidden = get_hidden_meta_boxes($page); (template.php line 2905)
 * The get_hidden_meta_boxes() is in (template.php line 3007), the data is stored in
 * user options: $hidden = (array) get_user_option( "meta-box-hidden_$page", 0, false );
 * The meta-box-hidden_$page is updated in (admin-ajax.php line 997) via Ajax calls
 * $page = 'admin_page_wpi_invoice_edit'
 */

/**
 * Metabox for notifications
 * @global type $wpi_settings
 * @param type $this_invoice
 */
function message_meta_box($this_invoice) {
  ?>
  <div id="send_notification_box" class="hidden postbox">
    <h3 class='hndle'><span><?php _e("Send Notification", ud_get_wp_invoice()->domain) ?></span></h3>
    <div class="inside">
      <div id="submitpost" class="submitbox">
        <div id="minor-publishing">
          <div id="minor-publishing-actions">
            <div id="preview-action" style="text-align: left">
              <table id="wpi_invoice_notification_table">
                <tr>
                  <th><?php _e('To:', ud_get_wp_invoice()->domain); ?></th>
                  <td>
                    <?php
                      $user_emails = apply_filters( 'wpi_emails_for_notifications', array( $this_invoice['user_data']['user_email'] ), $this_invoice );
                      if ( count( $user_emails ) == 1 ):
                    ?>
                      <input id="wpi_notification_send_to" class="input_field" name="wpi_invoice_notification[email_address]" value="<?php echo $this_invoice['user_data']['user_email']; ?>" />
                    <?php elseif ( count( $user_emails ) > 1 ): ?>
                      <select id="wpi_notification_send_to" class="input_field" name="wpi_invoice_notification[email_address]">
                      <?php foreach( $user_emails as $_email ): ?>
                        <option value="<?php echo $_email; ?>"><?php echo $_email; ?></option>
                      <?php endforeach; ?>
                      </select>
                    <?php else : ?>
                      <p><?php _e( 'No email addresses presented', ud_get_wp_invoice()->domain ); ?></p>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <th><?php _e('Template:', ud_get_wp_invoice()->domain); ?></th>
                  <td>
                    <select id="wpi_change_notification">
                      <option value="0"></option>
                      <?php
                      global $wpi_settings;
                      foreach( $wpi_settings['notification'] as $notification_key => $notification ) {
                        ?>
                        <option value="<?php echo $notification_key; ?>"><?php echo $notification['name']; ?></option>
                        <?php
                      }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <th><?php _e('Subject:', ud_get_wp_invoice()->domain); ?></th>
                  <td><input id="wpi_notification_subject" class="input_field" name="wpi_invoice_notification[subject]" value="<?php echo!empty($this_invoice['subject']) ? $this_invoice['subject'] : ''; ?>" /></td>
                </tr>

                <tr>
                  <th><?php _e('Message:', ud_get_wp_invoice()->domain); ?></th>
                  <td><textarea id="wpi_notification_message" name="wpi_invoice_notification[notification_message]" class="wpi_notification_message " value=""></textarea></td>
                </tr>

              </table>
            </div>
            <div class="clear"></div>
          </div>
        </div>
        <div class="major-publishing-actions clearfix">
          <div class="alignleft">
            <span class="wpi_cancel" onclick="wpi_show_notification_box();"><?php _e('Cancel', ud_get_wp_invoice()->domain); ?></span>
          </div>
          <div id="wpi_template_loading"  style="display:none;" ></div>
          <input type="submit" class="alignright button-primary" value="<?php esc_attr(_e('Send Notification', ud_get_wp_invoice()->domain)); ?>" id="wpi_send_notification">
        </div>
      </div>
    </div>
  </div>
  <?php
}

/**
 * Overview metabox
 * @param type $this_invoice
 */
function postbox_overview($this_invoice) {

  ?>
  <table class="form-table">
   <?php
     do_action('postbox_overview', $this_invoice);
   ?>
    <tr>
      <th>
        <?php _e('Invoice ID', ud_get_wp_invoice()->domain); ?>
      </th>
      <td>
        <?php echo $this_invoice['invoice_id']; ?>
      </td>
    </tr>
    <?php
    if ( !empty($this_invoice['customer_information']) )
      foreach ( $this_invoice['customer_information'] as $key => $value ) {
        $title = str_replace('_', ' ', $key);
        $title = ucwords($title);
    ?>
      <tr>
        <th>
          <?php _e($title, ud_get_wp_invoice()->domain); ?>
        </th>
        <td>
          <?php echo $value; ?>
        </td>
      </tr>
    <?php
      }
    ?>
  </table>
  <?php

}

/**
 * Publish metabox
 * @global type $wpi_settings
 * @param type $this_invoice
 */
function postbox_publish($this_invoice) {

  global $wpi_settings;
  $invoice_id = $this_invoice['invoice_id'];

  $status_names = apply_filters('wpi_invoice_statuses', $wpi_settings['invoice_statuses']);

  if (!empty($this_invoice['status'])) {
    $status_label = ( !empty($status_names[$this_invoice['status']]) ? $status_names[$this_invoice['status']] : $this_invoice['status']);
  }

  ?>
  <div id="submitpost" class="submitbox">
    <div id="minor-publishing">
      <ul class="wpi_publish_seetings">
        <li class="wpi_hide_until_saved"><a target="_blank" class="wpi_new_win wpi_update_with_invoice_url" href="<?php echo get_invoice_permalink(!empty($this_invoice['invoice_id']) ? $this_invoice['invoice_id'] : '' ); ?>"><?php _e('View Online', ud_get_wp_invoice()->domain); ?></a></li>

        <?php do_action('wpi_publish_options', $this_invoice); ?>

        <li class="wpi_hide_until_saved"><span onclick="wpi_show_paycharge_box();" class="wpi_link" id="wpi_button_show_paycharge_box"><?php _e('Enter Payment', ud_get_wp_invoice()->domain); ?></span></li>
        <li class="wpi_hide_until_saved"><span onclick='wpi_show_notification_box();' class="wpi_link" id="wpi_button_show_notification"><?php _e('Send Notification', ud_get_wp_invoice()->domain); ?></span></li>

        <?php if ($wpi_settings['allow_deposits'] == 'true') { ?>
          <li class="wpi_not_for_recurring wpi_hide_deposit_option wpi_not_for_quote">
            <?php $app_title = __("Allow Partial Payment", ud_get_wp_invoice()->domain); ?>
            <?php echo WPI_UI::checkbox("name=wpi_invoice[deposit]&value=true&label={$app_title}", ((!empty($this_invoice['deposit_amount']) && (int) $this_invoice['deposit_amount'] > 0) ? true : (WPI_Functions::is_true( isset($wpi_settings['allow_deposits_by_default'])?$wpi_settings['allow_deposits_by_default']:false ) && !empty($this_invoice['new_invoice']) ? true : false ) )) ?></li>
          <li class="wpi_deposit_settings">
            <table class="wpi_deposit_settings">
              <tr>
                <th><?php _e("Minimum Payment", ud_get_wp_invoice()->domain); ?></th>
                <td><?php echo WPI_UI::input("id=wpi_meta_deposit_amount&name=wpi_invoice[deposit_amount]&value=" . (!empty($this_invoice['deposit_amount']) ? $this_invoice['deposit_amount'] : 0)); ?></td>
              </tr>
            </table>
          </li>
        <?php } ?>

        <?php if ($wpi_settings['show_recurring_billing'] == 'true') { ?>
          <li class="wpi_turn_off_recurring wpi_not_for_quote">
            <?php echo WPI_UI::checkbox("name=wpi_invoice[recurring][active]&value=true&label=".__('Recurring Bill', ud_get_wp_invoice()->domain), (!empty($this_invoice['recurring']) ? $this_invoice['recurring']['active'] : false)); ?>
          </li>
          <li class="wpi_recurring_bill_settings <?php if (!empty($this_invoice['recurring']) && $this_invoice['recurring']['active'] != 'on') {
            ?>hidden<?php } ?>">

            <?php
              $recurring_settings = apply_filters( 'wpi_recurring_settings', array(), $this_invoice );
              foreach( $recurring_settings as $gateway ) {
                do_action( 'wpi_recurring_settings_'.$gateway, $this_invoice );
              }
            ?>

          </li>
        <?php } ?>

        <?php do_action('wpi_publish_options_after', $this_invoice); ?>

      </ul>
      <table class="form-table">
        <thead>
          <th colspan="2">
            <span id="wpi_button_show_advanced" class="wpi_link"><?php _e('Toggle Advanced', ud_get_wp_invoice()->domain); ?></span>
          </th>
        </thead>
        <tbody>
          <tr class="column-publish-due-date wpi_not_for_recurring wpi_not_for_quote">
            <th><?php _e('Due Date', ud_get_wp_invoice()->domain); ?></th>
            <td>
              <div class="timestampdiv" style="display:block;">
                <?php echo WPI_UI::select("id=due_date_mm&name=wpi_invoice[due_date_month]&values=months&current_value=" . (!empty($this_invoice['due_date_month']) ? $this_invoice['due_date_month'] : '')); ?>
                <?php echo WPI_UI::input("id=due_date_jj&name=wpi_invoice[due_date_day]&value=" . (!empty($this_invoice['due_date_day']) ? $this_invoice['due_date_day'] : '') . "&special=size='2' maxlength='2' autocomplete='off'") ?>
                <?php echo WPI_UI::input("id=due_date_aa&name=wpi_invoice[due_date_year]&value=" . (!empty($this_invoice['due_date_year']) ? $this_invoice['due_date_year'] : '') . "&special=size='2' maxlength='4' autocomplete='off'") ?><br />
                <span onclick="wp_invoice_add_time('due_date', 7);" class="wp_invoice_click_me"><?php _e('In One Week', ud_get_wp_invoice()->domain); ?></span> | <span onclick="wp_invoice_add_time('due_date', 30);" class="wp_invoice_click_me"><?php _e('In 30 Days', ud_get_wp_invoice()->domain); ?></span> | <span onclick="wp_invoice_add_time('due_date','clear');" class="wp_invoice_click_me"><?php _e('Clear', ud_get_wp_invoice()->domain); ?></span>
              </div>
            </td>
          </tr>
          <tr class="invoice_main column-publish-invoice_id">
            <th><?php _e('Invoice ID', ud_get_wp_invoice()->domain); ?> </th>
            <td>
              <?php
                $custom_invoice_id = !empty($this_invoice['custom_id']) ? $this_invoice['custom_id'] : '';
                if (empty($custom_invoice_id) && $wpi_settings['increment_invoice_id'] == 'true') {
                  $highest_custom_id = WPI_Functions::get_highest_custom_id();
                  $custom_invoice_id = ($highest_custom_id ? ($highest_custom_id + 1) : $this_invoice['invoice_id']);
                  echo WPI_UI::input("name=wpi_invoice[meta][custom_id]&value=$custom_invoice_id");
                } else {
              ?>
                <input style="width: 80px;" class="input_field wp_invoice_custom_invoice_id<?php echo empty($this_invoice['custom_id'])?" wp_invoice_hidden":""; ?>" name="wpi_invoice[meta][custom_id]" value="<?php echo !empty($this_invoice['custom_id']) ? $this_invoice['custom_id'] : ''; ?>">
                <span class="wp_invoice_custom_invoice_id">
                  <?php echo $this_invoice['invoice_id']; ?>
                </span>
                <a onClick="jQuery('.wp_invoice_custom_invoice_id').toggle(); return false;" class="wp_invoice_click_me <?php echo empty($this_invoice['custom_id'])?" wp_invoice_hidden":""; ?>" href="#"><?php _e('Custom Invoice ID', ud_get_wp_invoice()->domain); ?></a>
              <?php } ?>
            </td>
          </tr>
          <tr class="invoice_main column-publish-global_tax">
            <th><?php _e('Global Tax', ud_get_wp_invoice()->domain); ?></th>
            <td>
              <?php echo WPI_UI::input("id=wp_invoice_tax&name=wpi_invoice[meta][tax]&value=" . (!empty($this_invoice['tax']) ? $this_invoice['tax'] : '')) ?>
            </td>
          </tr>
          <tr class="invoice_main column-publish-global_tax">
            <th><?php _e('Tax Method', ud_get_wp_invoice()->domain); ?></th>
            <td>
    <?php $tax_method = !empty($this_invoice['tax_method']) ? $this_invoice['tax_method'] : (isset($wpi_settings['tax_method']) ? $wpi_settings['tax_method'] : ''); ?>
    <?php echo WPI_UI::select("id=wpi_tax_method&name=wpi_invoice[tax_method]&values=" . serialize(array('before_discount' => __('Before Discount', ud_get_wp_invoice()->domain), 'after_discount' => __('After Discount', ud_get_wp_invoice()->domain))) . "&current_value={$tax_method}"); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="major-publishing-actions" class="clearfix">
      <div id="delete-action" class="wpi_hide_until_saved">
        <a href="<?php echo admin_url($wpi_settings['links']['overview_page']) . "&action=trash&post=" . (!empty($this_invoice['ID']) ? $this_invoice['ID'] : '') . "&_wpnonce=" . wp_create_nonce('wpi-status-change-' . (!empty($this_invoice['ID']) ? $this_invoice['ID'] : '')); ?>" class="submitdelete deletion"><?php _e('Trash Invoice', ud_get_wp_invoice()->domain); ?></a>
      </div>
      <div id="publishing-action">
        <input type="submit" class="alignright button-primary" value="<?php esc_attr(_e('Save', ud_get_wp_invoice()->domain)); ?>" id="wpi_save_invoice">
      </div>
    </div>
  </div>
  <?php
}

/**
 * New User metabox
 * @global type $wpi_settings
 * @param type $this_invoice
 */
function postbox_user_new($this_invoice) {
  global $wpi_settings;
  ?>
  <div class="postbox">
    <h3 class="hndle"><?php _e('New User Information', ud_get_wp_invoice()->domain); ?></h3>
    <div class="inside">
      <table class="form-table wp_invoice_new_user">
        <tr>
          <th><?php _e('Email', ud_get_wp_invoice()->domain); ?></th>
          <td id="wpi_user_email"><?php echo $_REQUEST['wpi']['new_invoice']['user_email']; ?>
        <?php echo WPI_UI::input("type=hidden&name=wpi_invoice[user_data][user_email]&value={$_REQUEST['wpi']['new_invoice']['user_email']})") ?>
          </td>
        </tr>
        <?php
        $custom_user_information = apply_filters('wpi_user_information', $wpi_settings['user_meta']['custom']);
        $user_information = array_merge($wpi_settings['user_meta']['required'], $custom_user_information);
        foreach ($user_information as $field_id => $field_name) {
          ?>
          <tr>
            <th><?php _e($field_name, ud_get_wp_invoice()->domain) ?></th>
            <td><?php echo WPI_UI::input("name=wpi_invoice[user_data][$field_id]&value=" . $this_invoice['user_data'][$field_id]); ?></td>
          </tr>
              <?php } ?>
        <tr>
          <th>
            <a class="wp_invoice_tooltip" title="<?php _e("If checked a WordPress user account will be created, otherwise the new user will only be visible within WP-Invoice.", ud_get_wp_invoice()->domain) ?>">
              <?php _e("Create WordPress User Account?", ud_get_wp_invoice()->domain) ?>
            </a>
          </th>
          <td><input  onclick="if(jQuery(this).is(':checked')) { jQuery('#wpi_new_user_username input').val('<?php echo $_REQUEST['wpi']['new_invoice']['user_email']; ?>'); jQuery('#wpi_new_user_username').show();} else { jQuery('#wpi_new_user_username input').val('');  jQuery('#wpi_new_user_username').hide();}"  type="checkbox" name='wpi_invoice[user_data][create_wp_account]'>
            <label for="wpi_invoice[user_data][create_wp_account]">
  <?php _e("Yes", ud_get_wp_invoice()->domain) ?>
            </label></td>
        </tr>
        <tr class="hidden" id="wpi_new_user_username">
          <th><?php _e('Username', ud_get_wp_invoice()->domain); ?></th>
          <td><?php echo WPI_UI::input("name=wpi_invoice[user_data][username]"); ?></td>
        </tr>
      </table>
    </div>
  </div>
  <?php
}

/**
 * Metabox for existing user
 * @global type $wpi_settings
 * @global type $wpdb
 * @param type $this_invoice
 */
function postbox_user_existing($this_invoice) {
  global $wpi_settings;

  $user_email = $this_invoice['user_data']['user_email'];

  //** Get required user fields */
  $required_fields = $wpi_settings['user_meta']['required'];

  //** Get non essential user information */
  $custom_fields = apply_filters('wpi_user_information', $wpi_settings['user_meta']['custom']);

  //** Merge required and non-required user fields */
  $user_information = array_merge($required_fields, $custom_fields);

  wp_enqueue_script('wpi_select2_js');
  wp_enqueue_style('wpi_select2_css');

  ?>

  <script type="text/javascript">
    jQuery( document ).ready(function(){
      jQuery(".wpi_user_email_selection").select2({
        placeholder: 'Select User',
        multiple: false,
        width: '100%',
        minimumInputLength: 3,
        ajax: {
          url: ajaxurl,
          dataType: 'json',
          type: 'POST',
          data: function (term, page) {
            return {
              action: 'wpi_search_email',
              s: term
            };
          },
          results: function (data, page) {
            return {results: data};
          }
        },
        initSelection: function(element, callback) {
          callback(<?php echo json_encode(array('id'=>$user_email, 'title'=>$user_email)); ?>);
        },
        formatResult: function(o) {
          return o.title;
        },
        formatSelection: function(o) {
          return o.title;
        },
        escapeMarkup: function (m) { return m; }
      });
    });
  </script>

  <div class="wpi_user_email_selection_wrapper">
    <input type="text" value="<?php echo esc_attr($user_email); ?>" name="wpi_invoice[user_data][user_email]" class="wpi_user_email_selection" />
  </div>

  <table class="form-table wp_invoice_new_user">

  <?php foreach ($user_information as $field_id => $field_name) { ?>
    <tr>
      <th><?php _e($field_name, ud_get_wp_invoice()->domain) ?></th>
      <td>
        <?php
          echo WPI_UI::input(array(
              'name'  => 'wpi_invoice[user_data]['.$field_id.']',
              'class' => 'wpi_'.$field_id,
              'value' => (!empty($this_invoice['user_data'][$field_id]) ? $this_invoice['user_data'][$field_id] : '')
          ));
        ?>
      </td>
    </tr>
  <?php } ?>
  </table>
  <?php
  do_action('wpi_integrate_crm_user_panel', !empty($this_invoice['user_data']['ID'])?$this_invoice['user_data']['ID']:'' );
}

/**
 * Payment methods metabox
 * @global type $wpi_settings
 * @param type $this_invoice
 */
function postbox_payment_methods($this_invoice) {
  global $wpi_settings;
  if (!empty($this_invoice['billing'])) {
    $this_invoice['billing'] = apply_filters('wpi_billing_method', $this_invoice['billing']);
    ?>
    <table class="form-table">

      <tr class="column-payment-method-default wpi_not_for_quote">
        <th><?php _e("Default Payment Option", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <select id="wp_invoice_payment_method" data-name="wpi_invoice[default_payment_method]" name="wpi_invoice[default_payment_method]">
            <?php foreach ($this_invoice['billing'] as $key => $payment_option) : ?>
            <?php if (!isset($payment_option['name']))
              continue; ?>
              <option value="<?php echo $key; ?>" <?php echo ($this_invoice['default_payment_method'] == $key) ? 'selected="selected"' : ''; ?> ><?php echo $payment_option['name']; ?></option>
          <?php endforeach; ?>
          </select>&nbsp;&nbsp;
          <?php
          if (count($this_invoice['billing']) > 1) {
            echo WPI_UI::checkbox("class=wpi_client_change_payment_method&name=wpi_invoice[client_change_payment_method]&value=true&label=".__('Client can change payment option.', ud_get_wp_invoice()->domain), !empty( $this_invoice['client_change_payment_method'] )? ( $this_invoice['client_change_payment_method'] == 'on' ? true : false ) : false);
          }
          ?>
          &nbsp;&nbsp;
          <?php
          echo WPI_UI::checkbox("class=wpi_use_manual_payment&name=wpi_invoice[use_manual_payment]&value=true&label=".__('Manual Payment only', ud_get_wp_invoice()->domain), !empty( $this_invoice['use_manual_payment'] )? ( $this_invoice['use_manual_payment'] == 'on' ? true : false ) : false);
          ?>
        </td>
      </tr>

      <tr class="wpi_not_for_quote wpi-payment-setting column-paymenth-method-<?php echo $key; ?>">
        <th><?php _e("Accepted Payments", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <ul class="wpi_settings_list">
              <?php foreach ($this_invoice['billing'] as $key => $value) : ?>
              <?php if (empty($value['name']))
                break; ?>
              <li class="clearfix">
      <?php echo WPI_UI::checkbox("name=wpi_invoice[billing][{$key}][allow]&id={$key}&value=true&label={$value['name']}&class=wpi_billing_section_show", $value['allow'] == 'on' ? true : false) ?>
              </li>
    <?php endforeach; ?>
          </ul>
        </td>
      </tr>


      <tr class="column-publish-currency">
        <th><?php _e("Currency", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <select name="wpi_invoice[default_currency_code]">
    <?php foreach ($wpi_settings['currency']['types'] as $value => $currency_x) : ?>
              <option value="<?php echo $value; ?>" <?php echo ($this_invoice['default_currency_code'] == $value) ? 'selected="selected"' : ''; ?>><?php echo $value; ?> - <?php echo $currency_x; ?></option>
    <?php endforeach; ?>
          </select>
        </td>
      </tr>



      <tr class="wpi_advanced_payment_options wpi_not_for_quote">
        <td colspan="2">
          <div class="wp_invoice_accordion">
    <?php foreach ($this_invoice['billing'] as $key => $value) : ?>
      <?php if (empty($this_invoice['default_payment_method']))
        $this_invoice['default_payment_method'] = key($this_invoice['billing']); ?>
                  <?php if (empty($value['name']))
                    break; ?>
              <div class="<?php echo $key; ?>-setup-section wp_invoice_accordion_section">
                <h3 id="<?php echo $key; ?>-setup-section-header" <?php if ($this_invoice['default_payment_method'] == $key) { ?>aria-expanded="true"<?php } else { ?>aria-expanded="false"<?php } ?>>
                  <span class="selector"><?php echo $value['name'] ?></span>
                </h3>
                <div style="display:<?php echo $this_invoice['default_payment_method'] == $key ? 'block' : 'none'; ?>">
      <?php echo WPI_UI::input("type=hidden&name=wpi_invoice[billing][{$key}][default_option]&class=billing-default-option billing-{$key}-default-option&value={$value['default_option']}") ?>
                  <table class="form-table">
                        <?php
                        foreach ($value['settings'] as $key2 => $setting_value) :
                          $setting_value['value'] = urldecode(isset($setting_value['value'])?$setting_value['value']:'');
                          $setting_value['type'] = !empty($setting_value['type']) ? $setting_value['type'] : 'input';
                          ?>
                      <tr>
                        <th width="300"><span class="<?php echo (!empty($setting_value['description']) ? "wp_invoice_tooltip" : ""); ?>" title="<?php echo (!empty($setting_value['description']) ? $setting_value['description'] : ''); ?>"><?php echo !empty($setting_value['label'])?$setting_value['label']:''; ?></span></th>
                        <td>
                          <?php if ($setting_value['type'] == 'select') : ?>
                            <?php echo WPI_UI::select("name=wpi_invoice[billing][{$key}][settings][{$key2}][value]&values=" . serialize($setting_value['data']) . "&current_value={$setting_value['value']}"); ?>
                          <?php elseif ($setting_value['type'] == 'textarea') : ?>
                            <?php echo WPI_UI::textarea("name=wpi_invoice[billing][{$key}][settings][{$key2}][value]&value={$setting_value['value']}"); ?>
                          <?php elseif ($setting_value['type'] == 'readonly') : ?>
                            <?php $setting_value['value'] = urlencode($setting_value['value']); ?>
                            <?php echo WPI_UI::textarea("name=wpi_invoice[billing][{$key}][settings][{$key2}][value]&value={$setting_value['value']}&special=readonly='readonly'"); ?>
                          <?php elseif (isset($setting_value['type']) && $setting_value['type'] == 'static') : ?>
                            <p><?php echo !empty($setting_value['data'])?$setting_value['data']:''; ?></p>
                            <input type="hidden" value="hidden" name="wpi_invoice[billing][<?php echo $key; ?>][settings][<?php echo $key2; ?>][value]" />
                          <?php else : ?>
                            <?php echo WPI_UI::input("name=wpi_invoice[billing][{$key}][settings][{$key2}][value]&value={$setting_value['value']}"); ?>
                          <?php endif; ?>
        <?php if (!empty($setting_value['special']) && is_array($setting_value['special']) && $setting_value['type'] != 'select') : ?>
                        <?php $s_count = 0; ?>
                            <br />
          <?php foreach ($setting_value['special'] as $s_label => $s_value): ?>
                              <span class="wp_invoice_click_me" onclick="jQuery('input[name=\'wpi_invoice[billing][<?php echo $key; ?>][settings][<?php echo $key2; ?>][value]\']').val('<?php echo $s_value; ?>');"><?php echo $s_label; ?></span>
                    <?php echo (++$s_count < count($setting_value['special']) ? ' | ' : '' ); ?>
                  <?php endforeach; ?>
        <?php endif; ?>
                        </td>
                      </tr>
      <?php endforeach; ?>
                  </table>
                </div>
              </div>
    <?php endforeach; ?>
          </div>
        </td>
      </tr>

      <tr>
        <th></th>
        <td class="wpi_toggle_advanced_payment_options"><span class="wpi_link"><?php _e('Toggle Advanced Payment Options', ud_get_wp_invoice()->domain); ?></span></td>
      </tr>

    </table>
  <?php } else { ?>
    <table class="form-table">
      <tr>
        <th><?php _e("Payment Method", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <input type="hidden" name="wpi_invoice[default_payment_method]" value="manual" />
          <p><?php echo sprintf(__('To manage payment settings you should accept at least one payment method. Visit <a href="%s">Payment Settings page</a> to setup.', ud_get_wp_invoice()->domain), admin_url('admin.php?page=wpi_page_settings#wpi_tab_payment')); ?></p>
          <p><?php echo sprintf(__('If you do not want to use any payment venue then <a href="%s">setup Manual Payment information</a>.', ud_get_wp_invoice()->domain), admin_url('admin.php?page=wpi_page_settings#wpi_tab_payment')); ?></p>
        </td>
      </tr>
      <tr class="column-publish-currency">
        <th><?php _e("Currency", ud_get_wp_invoice()->domain) ?></th>
        <td>
          <select name="wpi_invoice[default_currency_code]">
    <?php foreach ($wpi_settings['currency']['types'] as $value => $currency_x) : ?>
              <option value="<?php echo $value; ?>" <?php echo ($this_invoice['default_currency_code'] == $value) ? 'selected="selected"' : ''; ?>><?php echo $value; ?> - <?php echo $currency_x; ?></option>
    <?php endforeach; ?>
          </select>
        </td>
      </tr>
      <tr>
        <th></th>
        <td class="wpi_toggle_advanced_payment_options"><span class="wpi_link"><?php _e('Toggle Advanced Payment Options', ud_get_wp_invoice()->domain); ?></span></td>
      </tr>
    </table>

  <?php
  }

  do_action( 'wpi_payment_options_box', $this_invoice );
}

/**
 * Invoice status metabox
 * @param type $this_invoice
 */
function status_meta_box($this_invoice) {

  $hidden = '';
  if (!empty($_REQUEST['wpi']['new_invoice'])) {
    $hidden = ' hidden ';
  }
  ?>
  <div id="postbox_status_and_history" class="postbox <?php echo $hidden; ?>">
    <h3 class="hndle"><?php _e("Invoice Status and History", ud_get_wp_invoice()->domain) ?></h3>
    <div class="inside" style="margin:0;padding:0;">
      <div id="submitbox" class="submitbox" style="overflow: auto; max-height: 150px;">
        <table id="wpi_enter_payments" class="form-table hidden" >
          <tr>
            <th><?php _e("Event Type", ud_get_wp_invoice()->domain) ?></th>
            <td>
  <?php echo WPI_UI::select("name=event_type&values=" . serialize(array('add_payment' => __('Receive Payment', ud_get_wp_invoice()->domain), 'add_charge' => __('Add Charge', ud_get_wp_invoice()->domain), 'do_adjustment' => __('Administrative Adjustment', ud_get_wp_invoice()->domain), 'refund' => __('Refund', ud_get_wp_invoice()->domain)))); ?>
              <span class="wpi_recurring_options"><?php _e('Note: Recurring bills cannot have administrative adjustments or additional charges, only received payments.', ud_get_wp_invoice()->domain); ?></span>
            </td>
          </tr>
          <tr>
            <th><?php _e("Event Amount", ud_get_wp_invoice()->domain) ?></th>
            <td>
              <?php echo WPI_UI::input("type=text&name=wpi_event_amount&class=wpi_money&special=autocomplete='off'"); ?>
              <span id="event_tax_holder" class="hidden">
                <b style="padding:5px;"><?php _e("Charge Tax", ud_get_wp_invoice()->domain) ?></b><?php echo WPI_UI::input("type=text&name=wpi_event_tax&class=wpi_money&special=autocomplete='off'"); ?>%
              </span>
            </td>
          </tr>
          <tr>
            <th><?php _e("Event Date & Time", ud_get_wp_invoice()->domain) ?></th>
            <td>
              <?php echo WPI_UI::input("type=text&name=wpi_event_date&class=wpi_date"); ?>
              <?php echo WPI_UI::input("type=text&name=wpi_event_time&class=wpi_time"); ?>
            </td>
          </tr>
          <tr>
            <th><?php _e("Event Note", ud_get_wp_invoice()->domain) ?></th>
            <td><?php echo WPI_UI::input("name=wpi_event_note"); ?>
            </td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td>
              <?php wp_nonce_field('wpi_process_manual_event_nonce', 'wpi_process_manual_event_nonce'); ?>
              <input type="button" class="button" value="<?php esc_attr(_e('Process Charge / Payment', ud_get_wp_invoice()->domain)); ?>"  id="wpi_process_manual_event" />
              <input type="button" class="button" value="<?php esc_attr(_e('Cancel', ud_get_wp_invoice()->domain)); ?>" onclick="wpi_show_paycharge_box();" />
              <span class="wpi_ajax_response"></span>
            </td>
          </tr>
        </table>
        <div style="padding: 5px;">
          <table class="form-table" id="wpi_invoice_status_table">
            <?php
            if (!empty($this_invoice['log']) && is_array($this_invoice['log'])) {
              if (!empty($this_invoice['ID'])) {
                WPI_Functions::get_status($this_invoice['ID']);
              }
            }
            ?>
          </table>
        </div>
      </div>
      <div class="footer_functions">
        <span class="wpi_clickable" onclick="jQuery('.wpi_event_update').toggle();"><?php _e('Toggle History Detail', ud_get_wp_invoice()->domain); ?></span>
      </div>
    </div>
  </div>
<?php
  do_action('wpi_add_comments_box');
?>
  <?php } ?>
