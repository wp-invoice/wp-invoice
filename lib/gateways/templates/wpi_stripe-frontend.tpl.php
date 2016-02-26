<?php
echo '<script type="text/javascript">Stripe.setPublishableKey("' . trim($invoice['billing'][$this->type]['settings'][$invoice['billing'][$this->type]['settings']['mode']['value'].'_publishable_key']['value']) . '");</script>';
?>
<form method="post" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
  <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
  <input type="hidden" id="wpi_form_type" name="type" value="<?php echo $this->type; ?>" />
  <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php echo $invoice['invoice_id']; ?>" />
  <input type="hidden" id="payment_amount" name="amount" value="<?php echo $invoice['net']; ?>" />
  <input type="hidden" name="security" value="<?php echo $process_payment_nonce; ?>">

  <div id="credit_card_information">

        <?php do_action('wpi_payment_fields_'.$this->type, $invoice); ?>

        <ul class="wpi_checkout_block">

          <li class="section_title"><?php _e('Billing Information', ud_get_wp_invoice()->domain); ?></li>

          <li class="wpi_checkout_row">
              <div class="control-group">
                  <label class="control-label" for="card-number"><?php _e('Card Number', ud_get_wp_invoice()->domain); ?></label>
                  <div class="controls">
                      <input type="text" autocomplete="off" id="card-number" class="card-number text-input" />
                  </div>
              </div>
          </li>

          <li class="wpi_checkout_row">
              <div class="control-group">
                  <label class="control-label" for="card-cvc"><?php _e('CVC', ud_get_wp_invoice()->domain); ?></label>
                  <div class="controls">
                      <input type="text" size="4" autocomplete="off" id="card-cvc" class="card-cvc text-input" />
                  </div>
              </div>
          </li>

          <li class="wpi_checkout_row">
              <div class="control-group">
                  <label class="control-label" for="card-expiry-month"><?php _e('Expiration (Month)', ud_get_wp_invoice()->domain); ?></label>
                  <div class="controls">
                      <input placeholder="MM" type="text" id="card-expiry-month" class="card-expiry-month text-input" />
                  </div>
              </div>
          </li>

          <li class="wpi_checkout_row">
              <div class="control-group">
                  <label class="control-label" for="card-expiry-year"><?php _e('Expiration (Year)', ud_get_wp_invoice()->domain); ?></label>
                  <div class="controls">
                      <input placeholder="YYYY" type="text" id="card-expiry-year" class="card-expiry-year text-input" />
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
                    <img style="display: none;" class="loader-img" src="<?php echo ud_get_wp_invoice()->path( "static/styles/images/processing-ajax.gif", 'url' ); ?>" alt="" />
                </div>
            </li>
        </ul>
        <br class="cb" />
    </div>
