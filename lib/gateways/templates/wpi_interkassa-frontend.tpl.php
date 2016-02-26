<?php require_once( ud_get_wp_invoice()->path( "lib/class_template_functions.php", 'dir' ) ); ?>
<form action="https://sci.interkassa.com/" method="POST" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
    <?php if ( !is_recurring() ): ?>
      <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
      <input type="hidden" id="wpi_form_type" name="type" value="<?php print $this->type; ?>" />
      <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php print $invoice['invoice_id']; ?>" />

      <input type="hidden" name="ik_co_id" value="<?php echo $invoice['billing'][$this->type]['settings']['ik_shop_id']['value']; ?>">
      <input type="hidden" id="payment_amount" name="ik_am" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
      <input type="hidden" name="ik_pm_no" value="<?php print $invoice['invoice_id']; ?>">
      <input type="hidden" name="ik_desc" value="<?php print $invoice['post_title']; ?>">
      <input type="hidden" name="ik_cur" value="<?php echo $invoice['default_currency_code']; ?>">
      <input type="hidden" name="security" value="<?php echo $process_payment_nonce; ?>">

      <div id="credit_card_information">

        <?php do_action('wpi_payment_fields_'.$this->type, $invoice); ?>

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
    <?php else: ?>
      <p><?php _e( 'This payment gateway does not support Recurring Billing. Try another one or contact site Administrator.', ud_get_wp_invoice()->domain ); ?></p>
    <?php endif; ?>
