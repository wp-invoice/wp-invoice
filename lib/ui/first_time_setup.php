<?php global $wpdb, $wpi_settings; ?>
<div class="wrap">
  <form id="wp_invoice_first_time_setup" action="<?php echo $wpi_settings['links']['manage_invoice']; ?>" method="POST">
    <?php echo WPI_UI::input(array(
        'type' => 'hidden',
        'name' => 'first_time_setup_ran',
        'value' => 'true',
        'group' => 'wpi_settings'
    )); ?>

    <div id="wp_invoice_potential_error"></div>
    <div style="margin: 5px 0 15px 0;"><?php _e('Thank you for installing WP-Invoice.  Please provide the necessary information to complete the first-time setup.', ud_get_wp_invoice()->domain); ?></div>
    <div id="first_time_setup_accordion" class="">
      <div class="wp_invoice_accordion_section">
        <h3 id="basic_setup"><a href="#" class="selector"><?php _e('Basic Setup', ud_get_wp_invoice()->domain); ?></a></h3>
        <div>
          <table class="form-table">
            <tr>
              <th width="200"><?php _e('Business Name:', ud_get_wp_invoice()->domain) ?></th>
              <td>
                <?php echo WPI_UI::input(array(
                    'type' => 'text',
                    'name' => 'business_name',
                    'group' => 'wpi_settings',
                    'value' => $wpi_settings['business_name']
                )); ?>
              </td>
            </tr>
            <tr>
              <th width="200">
                <a class="wp_invoice_tooltip" title="<?php _e('Your clients will have to follow their secure link to this page to see their invoice. Opening this page without following a link will result in the standard page content begin shown.', ud_get_wp_invoice()->domain); ?>"><?php _e('Select a page to display invoices:', ud_get_wp_invoice()->domain); ?></a>
              </th>
              <td>
                <?php wp_dropdown_pages(array(
                    'name'     => 'wpi_settings[web_invoice_page]',
                    'id'       => 'wp_invoice_web_invoice_page',
                    'selected' => (isset($wp_invoice_web_invoice_page)?$wp_invoice_web_invoice_page:'')
                )); ?>
              </td>
            </tr>
            <tr>
              <th> <a class="wp_invoice_tooltip"  title="<?php _e('Select whether to overwrite all page content, insert at the bottom of the content, or to look for the [wp-invoice] tag.', ud_get_wp_invoice()->domain); ?>"><?php _e('How to Insert Invoice:', ud_get_wp_invoice()->domain); ?></a></th>
              <td>
              <?php
                echo WPI_UI::select(array(
                  'name' => 'where_to_display',
                  'group' => 'wpi_settings',
                  'values' => serialize(apply_filters('wpi_where_to_display_options', array("overwrite" => __("Overwrite All Page Content", ud_get_wp_invoice()->domain), "below_content" => __("Place Below Content", ud_get_wp_invoice()->domain), "above_content" => __("Above Content", ud_get_wp_invoice()->domain), "replace_tag" => __("Replace [wp-invoice] Tag", ud_get_wp_invoice()->domain)))),
                  'current_value' => $wpi_settings['where_to_display']
                ));
              ?>
                <div>
                  <small class="description"><?php _e('If using the tag, place <span class="wp_invoice_explanation">[wp-invoice]</span> somewhere within your page content.', ud_get_wp_invoice()->domain) ?></small>
                </div>
              </td>
            </tr>
            <tr class="column-payment-method-default">
              <th><?php _e("Default Payment Method:", ud_get_wp_invoice()->domain) ?></th>
              <td >
                <select id="wp_invoice_payment_method">
                  <?php foreach ($wpi_settings['billing'] as $key => $payment_option) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $payment_option['name']; ?></option>
                  <?php } ?>
                </select>
              </td>
            </tr>
            <tr class="column-payment-method-change-method">
              <th><?php _e("Client can change payment method:", ud_get_wp_invoice()->domain) ?></th>
              <td>
                <?php echo WPI_UI::select(array(
                    'name' => 'wpi_settings[globals][client_change_payment_method]',
                    'id'   => 'wpi_invoice_client_change_payment_method',
                    'values' => 'yon',
                    'current_value' => $wpi_settings['globals']['client_change_payment_method']
                )); ?>
              </td>
            </tr>
            <?php foreach ($wpi_settings['billing'] as $key => $value) { ?>
              <tr class="wpi-payment-setting column-paymenth-method-<?php echo $key; ?>">
                <th><?php _e("Accept ", ud_get_wp_invoice()->domain) ?><?php echo $value['name']; ?>?</th>
                <td>
                  <?php echo WPI_UI::input(array(
                      'type' => 'checkbox',
                      'name' => "wpi_settings[billing][{$key}][allow]",
                      'id'   => $key,
                      'value' => 'true',
                      'label' => __('Yes', ud_get_wp_invoice()->domain),
                      'class' => 'wpi_billing_section_show',
                      'special' => ($value['allow'] ? 'checked=true ' : '')
                  )); ?>
                  <div class="wpi_notice"><?php _e("Notice the ", ud_get_wp_invoice()->domain) ?> <span onClick="wpi_focus_payment_method('<?php echo $key; ?>');"><u><?php echo $value['name']; ?> <?php _e(" Tab ", ud_get_wp_invoice()->domain) ?></u></span> <?php _e(" below. ", ud_get_wp_invoice()->domain) ?></div>
                </td>
              </tr>
            <?php } ?>
          </table>
        </div>
      </div>
      <?php foreach ($wpi_settings['billing'] as $key => $value) { ?>
        <div class="<?php echo $key; ?>-setup-section wp_invoice_accordion_section">
          <h3 id="<?php echo $key; ?>-setup-section-header"><a href="#" class="selector"><?php echo $value['name'] ?></a></h3>
          <div>
            <?php echo WPI_UI::input(array(
                'type' => 'hidden',
                'name' => "wpi_settings[billing][{$key}][default_option]",
                'class' => "billing-default-option billing-{$key}-default-option",
                'value' => $wpi_settings['billing'][$key]['default_option']
            )) ?>
            <table class="form-table">
            <?php foreach($value['settings'] as $key2 => $setting_value) { ?>
              <tr>
                <th width="300"><span class="<?php echo (!empty($setting_value['description']) ? "wp_invoice_tooltip" : ""); ?>" title="<?php echo (!empty($setting_value['description']) ? $setting_value['description'] : ''); ?>"><?php echo !empty($setting_value['label'])?$setting_value['label']:''; ?></span></th>
                <td>
                  <?php if (isset($setting_value['type']) && $setting_value['type'] == 'select') : ?>
                    <?php echo WPI_UI::select(array(
                        'name' => "wpi_settings[billing][{$key}][settings][{$key2}][value]",
                        'values' => serialize($setting_value['data']),
                        'current_value' => (!empty($setting_value['value']) ? $setting_value['value'] : "")
                    )); ?>
                  <?php elseif (isset($setting_value['type']) && $setting_value['type'] == 'textarea') : ?>
                    <?php echo WPI_UI::textarea(array(
                        'name' => "wpi_settings[billing][{$key}][settings][{$key2}][value]",
                        'value' => $setting_value['value']
                    )); ?>
                  <?php elseif (isset($setting_value['type']) && $setting_value['type'] == 'readonly') : ?>
                    <?php echo WPI_UI::textarea(array(
                        'name' => "wpi_settings[billing][{$key}][settings][{$key2}][value]",
                        'value' => $setting_value['value'],
                        'special' => 'readonly="readonly"'
                    )); ?>
                  <?php elseif (isset($setting_value['type']) && $setting_value['type'] == 'static') : ?>
                    <p><?php echo !empty($setting_value['data'])?$setting_value['data']:''; ?></p>
                  <?php else : ?>
                    <?php echo WPI_UI::input(array(
                        'type' => 'text',
                        'name' => "wpi_settings[billing][{$key}][settings][{$key2}][value]",
                        'value' => (!empty($setting_value['value']) ? $setting_value['value'] : "")
                    )); ?>
                  <?php endif; ?>
                  <?php if (!empty($setting_value['special']) && is_array($setting_value['special']) && (!isset($setting_value['type']) || $setting_value['type'] != 'select')) : ?>
                    <?php $s_count = 0; ?>
                    <br/>
                    <?php foreach ($setting_value['special'] as $s_label => $s_value): ?>
                    <span class="wp_invoice_click_me <?php echo $key; ?> <?php echo sanitize_title($s_label); ?>"><?php echo $s_label; ?></span>
                      <script type="text/javascript">
                        jQuery(document).ready(function(){
                          jQuery('.wp_invoice_click_me.<?php echo $key; ?>.<?php echo sanitize_title($s_label); ?>').on('click', function(){
                            jQuery('input[name="wpi_settings[billing][<?php echo $key; ?>][settings][<?php echo $key2; ?>][value]"]').val('<?php echo $s_value; ?>');
                          });
                        });
                      </script>
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
    </div>
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
</div>