<?php include_once WPI_Path.'/core/wpi_template_functions.php'; ?>
<form action="<?php echo $invoice['billing']['wpi_paypal']['settings']['test_mode']['value']; ?>" method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
  <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
  <input type="hidden" id="wpi_form_type" name="type" value="<?php print $this->type; ?>" />
  <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php print $invoice['invoice_id']; ?>" />
  <input type="hidden" name="wp_invoice[hash]" value="<?php echo wp_create_nonce($invoice['invoice_id'] .'hash'); ?>" />
  <input type="hidden" name="currency_code" value="<?php echo $invoice['default_currency_code']; ?>">
  <input type="hidden" name="no_shipping" value="1">
  <input type="hidden" name="upload" value="1">
  <input type="hidden" name="business" value="<?php echo $invoice['billing']['wpi_paypal']['settings']['paypal_address']['value']; ?>">
  <input type="hidden" name="return" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>">
  <input type="hidden" name="cancel_return" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>">
  <input type="hidden" name="cbt" value="Go back to Merchant">
  <input type="hidden" name="item_name" value="<?php echo $invoice['post_title']; ?>">
  <input type="hidden" name="invoice" id="invoice_id" value="<?php echo $invoice['invoice_id']; ?>">

  <?php if ( is_recurring() ): ?>
  <?php switch ( $invoice['recurring']['unit'] ) {
          case 'days':
            $subscription_unit = "D";
            break;
          case 'months':
            $subscription_unit = "M";
            break;
          case 'weeks':
            $subscription_unit = "W";
            break;
          case 'years':
            $subscription_unit = "Y";
          break;
        }
    ?>
    <input type="hidden" name="cmd" value="_xclick-subscriptions">
    <input type="hidden" name="item_number" value="<?php echo $invoice['invoice_id']; ?>">
    <input type="hidden" name="src" value="1">
    <input type="hidden" name="srt" value="<?php echo (int)$invoice['recurring']['cycles']; ?>">
    <input type="hidden" name="a3" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
    <input type="hidden" name="p3" value="<?php echo (int)$invoice['recurring']['length']; ?>">
    <input type="hidden" name="t3" value="<?php echo $subscription_unit; ?>">
  <?php else: ?>
    <input type="hidden" id="payment_amount" name="amount" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="rm" value="2">
  <?php endif; ?>

	<div id="credit_card_information">

		<?php do_action('wpi_payment_fields_'.$this->type, $invoice); ?>

		<ul id="wp_invoice_process_wait">
			<li>
				<div class="wpi-control-group">
          <div class="controls">
            <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php _e('Process Payment of ', WPI); ?><?php echo (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$"); ?><span id="pay_button_value"><?php echo WPI_Functions::money_format($invoice['net']); ?></span></button>
          </div>
          <img style="display: none;" class="loader-img" src="<?php echo WPI_URL; ?>/core/css/images/processing-ajax.gif" alt="" />
        </div>
      </li>
		</ul>

	</div>