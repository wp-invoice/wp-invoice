<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<?php
echo '<script type="text/javascript">Stripe.setPublishableKey("' . $invoice['billing'][$this->type]['settings'][$invoice['billing'][$this->type]['settings']['mode']['value'].'_publishable_key']['value'] . '");</script>';
?>
<form method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
    <div id="credit_card_information">
        <?php do_action('wpi_payment_fields_stripe', $invoice); ?>

        <label>Card Number</label>
		<input type="text" size="20" autocomplete="off" class="card-number input-medium">
		<span class="help-block">Enter the number without spaces or hyphens.</span>
		<label>CVC</label>
		<input type="text" size="4" autocomplete="off" class="card-cvc input-mini">
		<label>Expiration (MM/YYYY)</label>
		<input type="text" size="2" class="card-expiry-month input-mini">
		<span> / </span>
        <input type="text" size="4" class="card-expiry-year input-mini">

        <ul id="wp_invoice_process_wait">
            <li>
                <div class="wpi-control-group">
                    <div class="controls">
                        <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php echo sprintf(__('Process Payment of %s', WPI), (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$")); ?><span id="pay_button_value"><?php echo WPI_Functions::money_format($invoice['net']); ?></span></button>
                    </div>
                    <img style="display: none;" class="loader-img" src="<?php echo WPI_URL; ?>/core/css/images/processing-ajax.gif" alt="" />
                </div>
            </li>
        </ul>
        <br class="cb" />
    </div>
