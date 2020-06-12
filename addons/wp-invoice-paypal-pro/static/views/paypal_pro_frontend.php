<script src="<?php echo ud_get_wp_invoice_paypal_pro()->path('static/scripts/paypal_pro_js.js', 'url'); ?>"></script>

<form method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
  <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
  <input type="hidden" id="wpi_form_type" name="type" value="<?php echo $this->type; ?>" />
  <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php echo $invoice['invoice_id']; ?>" />
  <input type="hidden" id="payment_amount" name="amount" value="<?php echo $invoice['net']; ?>" />
  <input type="hidden" id="currency_code" name="currency_code" value="<?php echo $invoice['default_currency_code']; ?>" />
  <input type="hidden" name="bn" value="UsabilityDynamics_SP" />
  <input type="hidden" name="security" value="<?php echo $process_payment_nonce; ?>">

  <div id="credit_card_information">

    <?php do_action('wpi_payment_fields_' . $this->type, $invoice); ?>

    <ul class="wpi_checkout_block">

      <li class="section_title"><?php _e('Billing Information', ud_get_wp_invoice()->domain); ?></li>
      
      <li class="wpi_checkout_row">
        <div class="control-group">
          <label class="control-label" for="card-number"><?php _e('Card Type', ud_get_wp_invoice()->domain); ?></label>
          <div class="controls">
            <select class="text-input" name="credit_card_type" id="credit-card-type">
              <option value="Visa">Visa</option>
              <option value="MasterCard">MasterCard</option>
              <option value="Amex">American Express</option>
              <option value="Discover">Discover</option>
            </select>
          </div>
        </div>
      </li>

      <li class="wpi_checkout_row">
        <div class="control-group">
          <label class="control-label" for="card-number"><?php _e('Card Number', ud_get_wp_invoice()->domain); ?></label>
          <div class="controls">
            <input type="text" name="acct" autocomplete="off" id="card-number" class="card-number text-input" />
          </div>
        </div>
      </li>

      <li class="wpi_checkout_row">
        <div class="control-group">
          <label class="control-label" for="card-cvc"><?php _e('CVV', ud_get_wp_invoice()->domain); ?></label>
          <div class="controls">
            <input type="text" name="cvv2" size="4" autocomplete="off" id="card-cvv2" class="card-cvc text-input" />
          </div>
        </div>
      </li>

      <li class="wpi_checkout_row">
        <div class="control-group">
          <label class="control-label" for="card-expiry-month"><?php _e('Expiration', ud_get_wp_invoice()->domain); ?></label>
          <div class="controls">
            <input style="width:70px;" placeholder="MM" name="exp_m" type="text" id="card-expiry-month" class="card-expiry-month text-input" />
            <input style="width:155px;" placeholder="YYYY" name="exp_y" type="text" id="card-expiry-year" class="card-expiry-year text-input" />
          </div>
        </div>
      </li>

    </ul>

    <?php do_action('wpi_after_payment_fields', $invoice); ?>

    <ul id="wp_invoice_process_wait">
      <li>
        <div class="wpi-control-group">
          <div class="controls">
            <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php echo sprintf(__('Process Payment of %s', ud_get_wp_invoice()->domain), (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$")); ?><span id="pay_button_value"><?php echo WPI_Functions::money_format($invoice['net']); ?></span></button>
          </div>
          <img style="display: none;" class="loader-img" src="<?php echo ud_get_wp_invoice()->path("static/styles/images/processing-ajax.gif", 'url'); ?>" alt="" />
        </div>
      </li>
    </ul>
    <br class="cb" />
  </div>
