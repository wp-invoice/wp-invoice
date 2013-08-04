<?php include_once WPI_Path.'/core/wpi_template_functions.php'; ?>
<form action="https://www.2checkout.com/checkout/spurchase" method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
  <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
  <input type="hidden" id="wpi_form_type" name="type" value="<?php print $this->type; ?>" />
  <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php print $invoice['invoice_id']; ?>" />
  <input type="hidden" name="wp_invoice[hash]" value="<?php echo wp_create_nonce($invoice['invoice_id'] .'hash'); ?>" />
  <input type="hidden" name="currency_code" value="<?php echo $invoice['default_currency_code']; ?>">
  <input type="hidden" name="mode" value="2CO">
  <input type="hidden" name="sid" value="<?php echo $invoice['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value']; ?>">
  <input type="hidden" name="demo" value="<?php echo $invoice['billing']['wpi_twocheckout']['settings']['test_mode']['value']; ?>">
  <input type="hidden" name="x_receipt_link_url" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>">
  <input type="hidden" name="return_url" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>">
  <input type="hidden" name="merchant_order_id" id="invoice_id" value="<?php echo $invoice['invoice_id']; ?>">

  <input type="hidden" name="li_0_name" value="<?php echo $invoice['post_title']; ?>">
  <?php if ( is_recurring() ): ?>
  <?php switch ( $invoice['recurring']['unit'] ) {
          case 'months':
            $subscription_unit = "Month";
            break;
          case 'weeks':
            $subscription_unit = "Week";
            break;
          case 'years':
            $subscription_unit = "Year";
          break;
        }
    ?>
    <input type="hidden" name="li_0_description" value="Invoice ID: <?php echo $invoice['invoice_id']; ?>">
    <input type="hidden" name="li_0_duration" value="<?php echo (int)$invoice['recurring']['cycles']; ?> <?php echo $subscription_unit; ?>">
    <input type="hidden" name="li_0_price" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
    <input type="hidden" name="li_0_recurrence" value="<?php echo (int)$invoice['recurring']['length']; ?> <?php echo $subscription_unit; ?>">
  <?php else: ?>
    <input type="hidden" name="li_0_description" value="Invoice ID: <?php echo $invoice['invoice_id']; ?>">
    <input type="hidden" name="li_0_price" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
  <?php endif; ?>

  <input type="hidden" name="card_holder_name" value="<?php echo $invoice['user_data']['display_name']; ?>">
  <input type="hidden" name="street_address" value="<?php echo $invoice['user_data']['streetaddress']; ?>">
  <input type="hidden" name="city" value="<?php echo $invoice['user_data']['city']; ?>">
  <input type="hidden" name="state" value="<?php echo $invoice['user_data']['state']; ?>">
  <input type="hidden" name="zip" value="<?php echo $invoice['user_data']['zip']; ?>">
  <input type="hidden" name="country" value="<?php echo $invoice['user_data']['country']; ?>">
  <input type="hidden" name="phone" value="<?php echo $invoice['user_data']['phonenumber']; ?>">
  <input type="hidden" name="email" value="<?php echo $invoice['user_data']['user_email']; ?>">

  <div id="credit_card_information">

    <?php do_action('wpi_payment_fields_twocheckout', $invoice); ?>

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
</form>

<?php
    if ( $invoice['billing']['wpi_twocheckout']['settings']['direct_checkout']['value'] == 'Y' ) {
        echo '<script src="https://www.2checkout.com/static/checkout/javascript/direct.min.js"></script>';
    }
?>