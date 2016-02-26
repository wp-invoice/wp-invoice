<?php require_once( ud_get_wp_invoice()->path( "lib/class_template_functions.php", 'dir' ) ); ?>

<form action="<?php echo $this->get_api_url( $invoice ); ?>" method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
  
  <!-- Standard options -->
  <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
  <input type="hidden" id="wpi_form_type" name="type" value="<?php print $this->type; ?>" />
  <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php print $invoice['invoice_id']; ?>" />
  
  <!-- 2co options -->
  <input type="hidden" name="sid" value="<?php echo $this->get_sid( $invoice ); ?>">
  <input type="hidden" name="mode" value="2CO">
  <input type="hidden" name="li_0_type" value="product">
  <input type="hidden" name="li_0_name" value="<?php echo $invoice['post_title']; ?>">
  <input type="hidden" name="li_0_quantity" value="1">
  <input type="hidden" id="payment_amount" name="li_0_price" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
  <input type="hidden" name="li_0_tangible" value="N">
  <input type="hidden" name="currency_code" value="<?php echo $invoice['default_currency_code']; ?>">
  <input type="hidden" name="merchant_order_id" value="<?php echo $invoice['invoice_id']; ?>">
  <input type="hidden" name="security" value="<?php echo $process_payment_nonce; ?>">
  
  <?php if ( is_recurring() ): ?>
    <input type="hidden" name="li_0_recurrence" value="<?php echo $this->get_recurrence( $invoice ); ?>">
    <input type="hidden" name="li_0_duration" value="<?php echo $this->get_duration( $invoice ); ?>">
  <?php endif; ?>
  
  <div id="credit_card_information">

    <?php do_action('wpi_payment_fields_' . $this->type, $invoice); ?>

    <?php do_action('wpi_after_payment_fields', $invoice); ?>

    <ul id="wp_invoice_process_wait">
      <li>
        <div class="wpi-control-group">
          <div class="controls">
            <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php _e('Process Payment of ', ud_get_wp_invoice()->domain); ?><?php echo (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$"); ?><span id="pay_button_value"><?php echo WPI_Functions::money_format($invoice['net']); ?></span></button>
          </div>
          <img style="display: none;" class="loader-img" src="<?php echo ud_get_wp_invoice()->path( "static/styles/images/processing-ajax.gif", 'url' ); ?>" alt="" />
        </div>
      </li>
    </ul>

  </div>