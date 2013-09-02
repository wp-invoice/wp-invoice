<?php echo '<pre>';
print_r( $invoice );
echo '</pre>'; ?>
<form action="http://www.interkassa.com/lib/payment.php" method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
    <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
    <input type="hidden" id="wpi_form_type" name="type" value="<?php print $this->type; ?>" />
    <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php print $invoice['invoice_id']; ?>" />

    <input type="hidden" name="ik_shop_id" value="<?php echo $invoice['billing'][$this->type]['settings']['ik_shop_id']['value']; ?>">
    <input type="hidden" id="payment_amount" name="ik_payment_amount" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
    <input type="hidden" name="ik_payment_id" value="<?php print $invoice['invoice_id']; ?>">
    <input type="hidden" name="ik_payment_desc" value="<?php print $invoice['post_title']; ?>">
    <input type="hidden" name="ik_paysystem_alias" value="">

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
