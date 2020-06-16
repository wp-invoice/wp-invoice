<!-- USA ePay -->
<form method="POST" 
      class="<?php echo apply_filters('wpi_spc::form_class', "wpi_checkout {$gateway_key}"); ?>" 
      action="#" <?php echo $display ? '' : 'style="display:none;"'; ?>>
  
  <?php do_action( 'wpi::spc::payment_form_top', $atts); ?>
  
  <input type="hidden" name="wpi_checkout[payment_method]" value="<?php echo $gateway_key; ?>" />
  <input type="hidden" name="wpi_checkout[currency_code]" value="<?php echo $wpi_settings['currency']['default_currency_code']; ?>" />

  <?php if ( !empty( $atts['items'] ) ) : ?>
  <?php   foreach($atts['items'] as $item): $item['tax'] = isset( $item['tax'] ) ? $item['tax'] : 0; ?>
            <input type="checkbox" <?php echo ($atts['uncheck_items'] != 'true' ? 'checked="true"' : ''); ?>  style="display:none;" class="wpi_checkout_products" item_price="<?php echo esc_attr( number_format( (float)($item['price']*$item['quantity'] + ($item['price']*$item['quantity']/100*$item['tax'])), 2, '.', '') ); ?>"  item_name="<?php echo esc_attr($item['name']); ?>" name="wpi_checkout[items][<?php echo esc_attr($item['name']); ?>]" value="true" />
  <?php   endforeach; ?>
  <?php endif; ?>
          
  <input type="hidden" name="wpi_checkout[default_price]" id="default_price" value="<?php echo number_format((float)$total, 2, '.', ''); ?>" />
  <input type="hidden" class="wpi_checkout_security_hash" name="wpi_checkout[security_hash]" value="<?php echo self::generate_security_hash( !empty( $atts['fee'] )?$atts['fee']:'', number_format( (float)$total, 2, '.', '') ); ?>" />

  <?php if(!empty($atts['callback_function'])) { ?>
    <input type="hidden" name="wpi_checkout[callback_action]" value="<?php echo $atts['callback_function']; ?>" />
  <?php } ?>
    
  <?php if(!empty($atts['fee'])) { ?>
    <input type="hidden" class="wpi_checkout_fee <?php echo $gateway_key; ?>" name="wpi_checkout[fee]" value="<?php echo (int)$atts['fee']; ?>" />
  <?php } ?>
    
  <?php if(!empty($atts['title'])) { ?>
          <input type="hidden" name="wpi_checkout[spc_title]" value="<?php echo $atts['title']; ?>" />
  <?php } ?>
          
  <?php
    add_filter('wpi_spc::input_attributes', array( 'UsabilityDynamics\WPI_USA_EPAY\Gateway', 'input_attributes' ), 10, 2);
  ?>

<!-- CUSTOMER INFORMATION -->
<ul class="wpi_checkout_block wpi_checkout_customer_information">
  <li class="section_title"><?php _e( 'Customer Information', ud_get_wp_invoice_usa_epay()->domain ); ?></li>
<?php foreach($wpi_checkout['info_block']['customer_information'] as $slug => $data): ?>
    <li class="wpi_checkout_row_<?php echo $slug; ?> wpi_checkout_row">
<?php
    $group_control_attrs = apply_filters('wpi_spc::group_control_attrs', array(), $slug, $data);
    $attrs = '';
    if ( !empty( $group_control_attrs ) && is_array( $group_control_attrs ) )
      foreach ($group_control_attrs as $attr_name => $attr_value) {
        $attrs .= $attr_name.'="'.$attr_value.'" ';
      }
?>
    <div <?php echo $attrs; ?> class="<?php echo apply_filters('wpi_spc::group_coltrol_class', 'control-group'); ?>">
<?php					$input_classes = implode(' ', apply_filters('wpi_spc::form_input_classes', array("input-large", "text-input", "wpi_checkout_payment_{$slug}_input"), $slug, $data, 'wpi_paypal_pro') ); ?>
<?php					$value = !empty($current_user->$slug) ? $current_user->$slug : '' ; ?>
<?php         if ( $slug == 'amount' ) { $value = !empty($atts['amount'])?$atts['amount']:''; } ?>
<?php					echo apply_filters("wpi_checkout_input_{$slug}", "<label class='control-label' for='wpi_checkout_payment_{$slug}'>{$data['label']}</label><div class='controls'><input ".implode(' ', apply_filters('wpi_spc::input_attributes', array("type='text'"),$slug))." name='wpi_checkout[billing][{$slug}]' value='".apply_filters('wpi_spc::input_value', esc_attr($value), $slug, $atts)."' id='wpi_checkout_payment_{$slug}_{$gateway_key}' class='{$input_classes}' /><span class='help-inline validation'></span></div>", $slug, $data); ?>
    </div>
  </li>
<?php endforeach; ?>
</ul>
      
<!-- BILLING ADDRESS -->       
<ul class="wpi_checkout_block wpi_checkout_billing_address">
  <li class="section_title"><?php _e( 'Billing Address', ud_get_wp_invoice_usa_epay()->domain ); ?></li>
  
  <li class="wpi_checkout_row_street wpi_checkout_row">
    <div class="control-group">
      <label class="control-label" for="wpi_checkout_payment_street">
        <?php _e( 'Street', ud_get_wp_invoice_usa_epay()->domain ); ?>
      </label>
      <div class="controls">
        <input name="wpi_checkout[billing][streetaddress]" value="<?php echo !empty($current_user->streetaddress) ? $current_user->streetaddress : ''; ?>" id='wpi_checkout_payment_street' class="input-large text-input wpi_checkout_payment_streetaddress_input" />
        <span class="help-inline validation"></span>
      </div>
    </div>
  </li>
  
  <li class="wpi_checkout_row_city wpi_checkout_row">
    <div class="control-group">
      <label class="control-label" for="wpi_checkout_payment_city">
        <?php _e( 'City', ud_get_wp_invoice_usa_epay()->domain ); ?>
      </label>
      <div class="controls">
        <input name="wpi_checkout[billing][city]" value="<?php echo !empty($current_user->city) ? $current_user->city : ''; ?>" id='wpi_checkout_payment_city' class="input-large text-input wpi_checkout_payment_city_input" />
        <span class="help-inline validation"></span>
      </div>
    </div>
  </li>
  
  <li class="wpi_checkout_row_zip wpi_checkout_row">
    <div class="control-group">
      <label class="control-label" for="wpi_checkout_payment_zip">
        <?php _e( 'Zip/Postal Code', ud_get_wp_invoice_usa_epay()->domain ); ?>
      </label>
      <div class="controls">
        <input name="wpi_checkout[billing][zip]" value="<?php echo !empty($current_user->zip) ? $current_user->zip : ''; ?>" id='wpi_checkout_payment_zip' class="input-large text-input wpi_checkout_payment_zip_input" />
        <span class="help-inline validation"></span>
      </div>
    </div>
  </li>
  
  <li class="wpi_checkout_row_state wpi_checkout_row">
    <div class="control-group">
      <label class="control-label" for="wpi_checkout_payment_state">
        <?php _e( 'State', ud_get_wp_invoice_usa_epay()->domain ); ?>
      </label>
      <div class="controls">
        <input name="wpi_checkout[billing][state]" value="<?php echo !empty($current_user->state) ? $current_user->state : ''; ?>" id='wpi_checkout_payment_state' class="input-large text-input wpi_checkout_payment_state_input" />
        <span class="help-inline validation"></span>
      </div>
    </div>
  </li>
  
  <li class="wpi_checkout_row_country wpi_checkout_row">
    <div class="control-group">
      <label class="control-label" for="wpi_checkout_payment_country">
        <?php _e( 'Country', ud_get_wp_invoice_usa_epay()->domain ); ?>
      </label>
      <div class="controls">
        <?php echo \WPI_UI::select(
            array('name' => 'wpi_checkout[billing][country]',
              'values' => 'countries',
              'class' => 'input-large text-input')); ?>
        <span class="help-inline validation"></span>
      </div>
    </div>
  </li>
  
</ul>
          
<!-- BILLING INFORMATION -->
<ul class="wpi_checkout_block wpi_checkout_billing_information">
  <li class="section_title"><?php _e( 'Billing Information', ud_get_wp_invoice_usa_epay()->domain ); ?></li>
  
<?php foreach($wpi_checkout['info_block']['billing_information'] as $slug => $data): ?>
    <li class="wpi_checkout_row_<?php echo $slug; ?> wpi_checkout_row">
<?php
    $group_control_attrs = apply_filters('wpi_spc::group_control_attrs', array(), $slug, $data);
    $attrs = '';
    if ( !empty( $group_control_attrs ) && is_array( $group_control_attrs ) )
      foreach ($group_control_attrs as $attr_name => $attr_value) {
        $attrs .= $attr_name.'="'.$attr_value.'" ';
      }
?>
    <div <?php echo $attrs; ?> class="<?php echo apply_filters('wpi_spc::group_coltrol_class', 'control-group'); ?>">
<?php					$input_classes = implode(' ', apply_filters('wpi_spc::form_input_classes', array("input-large", "text-input", "wpi_checkout_payment_{$slug}_input"), $slug, $data, 'wpi_usa_epay') ); ?>
<?php					$value = !empty($current_user->$slug) ? $current_user->$slug : '' ; ?>
<?php         if ( $slug == 'amount' ) { $value = !empty($atts['amount'])?$atts['amount']:''; } ?>
<?php					echo apply_filters("wpi_checkout_input_{$slug}", "<label class='control-label' for='wpi_checkout_payment_{$slug}'>{$data['label']}</label><div class='controls'><input ".implode(' ', apply_filters('wpi_spc::input_attributes', array("type='text'"),$slug))." name='wpi_checkout[billing][{$slug}]' value='".apply_filters('wpi_spc::input_value', esc_attr($value), $slug, $atts)."' id='wpi_checkout_payment_{$slug}_{$gateway_key}' class='{$input_classes}' /><span class='help-inline validation'></span></div>", $slug, $data); ?>
    </div>
  </li>
<?php endforeach; ?>
</ul>
          
          
  <?php if( !empty($atts['terms']) ) { ?>
      <ul class="wpi_checkout_block wpi_checkout_terms">
        <li class="wpi_checkout_row_terms wpi_checkout_row">
          <div validation_type="checked" class="<?php echo apply_filters('wpi_spc::group_coltrol_class', 'control-group'); ?>">
            <label class="control-label"><?php echo apply_filters('wpi_spc::terms_label', __('Agreement', ud_get_wp_invoice()->domain)); ?></label>
            <div class="controls">
              <input type="hidden"  name="wpi_checkout[terms]" value="false" />
              <label class="checkbox" for="wpi_checkout_payment_terms_input">
                <input type="checkbox" class="wpi_checkout_payment_terms_input" id="wpi_checkout_payment_terms_input_<?php echo $gateway_key; ?>" name="wpi_checkout[terms]" value="true" />
                <?php echo $atts['terms']; ?>
              </label>
              <span class="help-inline validation"></span>
            </div>
          </div>
        </li>
      </ul>
  <?php } ?>
          
  <div class="clearfix"></div>
  <div class="<?php echo apply_filters('wpi_spc::form_actions_class', 'form-actions', 'wpi_paypal_pro'); ?>">
      <?php $checkout_button_classes = implode(' ', apply_filters('wpi_spc::checkout_button_classes', array("btn", "btn-success", "wpi_checkout_process_payment", "wpi_paypal_pro"))); ?>
      <input type="submit" class="wpi_checkout_submit_btn <?php echo $checkout_button_classes; ?>" value="<?php esc_attr(_e('Process Payment', ud_get_wp_invoice()->domain)); ?>" />
      <span class="total_price">of
        <span class="wpi_checkout_final_price"><?php echo $wpi_settings['currency']['symbol'][$wpi_settings['currency']['default_currency_code']]; ?><span class="wpi_price"><?php echo wp_invoice_currency_format( (float)$total ); ?></span>
          <span class="wpi_fee_amount"></span>
        </span>
      </span>
  </div>
  <div class="<?php echo apply_filters('wpi_spc::response_box_class', 'wpi_checkout_payment_response hidden'); ?>"></div>
</form>