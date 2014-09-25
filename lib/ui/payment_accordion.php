<div class="wp_invoice_accordion_section paypalsetup <?php if (get_option('wp_invoice_paypal_allow') != "yes") {
    echo " hidden ";
} ?>">
    <h3 id="paypalsetup"><a href="#"><?php _e("PayPal Setup", ud_get_wp_invoice()->domain) ?></a></h3>
    <div>
        <table class="form-table">
            <tr>
                <th width="300"><?php _e("PayPal Username", ud_get_wp_invoice()->domain); ?></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_paypal_address', get_option('wp_invoice_paypal_address')); ?></td>
            </tr>
            <tr>
                <th width="300"><?php _e("PayPal Pay Button URL", ud_get_wp_invoice()->domain); ?></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_fe_paypal_link_url', get_option('wp_invoice_fe_paypal_link_url')); ?></td>
            </tr>
        </table>
    </div>
</div>
<div class="wp_invoice_accordion_section ccsetup  <?php if (get_option('wp_invoice_cc_allow') != "yes") {
    echo " hidden ";
} ?>">
    <h3 id="ccsetup"><a href="#"><?php _e("Credit Card Setup", ud_get_wp_invoice()->domain) ?></a></h3>
    <div>
        <table class="form-table">
            <tr class="gateway_info">
                <th width="300"><?php _e('Merchant Email', ud_get_wp_invoice()->domain); ?></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_merchant_email', get_option('wp_invoice_gateway_merchant_email')); ?></td>
            </tr>
            <tr class="gateway_info payment_info">
                <th width="300"><a class="wp_invoice_tooltip" title="<?php _e('Your credit card processor will provide you with a gateway username.', ud_get_wp_invoice()->domain); ?>"><?php _e('Gateway Username', ud_get_wp_invoice()->domain); ?></a></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_username', get_option('wp_invoice_gateway_username'), ' AUTOCOMPLETE="off"  '); ?>
                </td>
            </tr>
            <tr class="gateway_info payment_info">
                <th width="300"><a class="wp_invoice_tooltip" title="<?php _e("You will be able to generate this in your credit card processor's control panel.", ud_get_wp_invoice()->domain); ?>"><?php _e('Gateway Transaction Key', ud_get_wp_invoice()->domain); ?></a></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_tran_key', get_option('wp_invoice_gateway_tran_key'), ' AUTOCOMPLETE="off"  '); ?></td>
            </tr>
            <tr class="gateway_info payment_info">
                <th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('This is the URL provided to you by your credit card processing company.', ud_get_wp_invoice()->domain); ?>"><?php _e('Gateway URL', ud_get_wp_invoice()->domain); ?></a></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_url', get_option('wp_invoice_gateway_url')); ?><br />
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi');">MerchantPlus</span> |
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://secure.authorize.net/gateway/transact.dll');">Authorize.Net</span> |
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://test.authorize.net/gateway/transact.dll');">Authorize.Net Developer</span>
                </td>
            </tr>
            <tr class="gateway_info payment_info">
                <th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('Recurring billing gateway URL is most likely different from the Gateway URL, and will almost always be with Authorize.net. Be advised - test credit card numbers will be declined even when in test mode.', ud_get_wp_invoice()->domain); ?>"><?php _e('Recurring Billing Gateway URL', ud_get_wp_invoice()->domain); ?></a></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_recurring_gateway_url', get_option('wp_invoice_recurring_gateway_url')); ?><br />
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_recurring_gateway_url').val('https://api.authorize.net/xml/v1/request.api');">Authorize.net ARB</span> |
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_recurring_gateway_url').val('https://apitest.authorize.net/xml/v1/request.api');">Authorize.Net ARB Testing</span>
                </td>
            </tr>
        </table>
    </div>
</div>
