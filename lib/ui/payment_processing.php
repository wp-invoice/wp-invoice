<div class="wp_invoice_accordion_section">
    <h3 id="paypalsetup"><a href="#" class="selector"><?php _e("PayPal", ud_get_wp_invoice()->domain) ?></a></h3>
    <div>
        <table class="form-table">
            <tr>
                <th width="300"><?php _e("PayPal Username", ud_get_wp_invoice()->domain); ?></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_paypal_address',$wp_invoice_paypal_address); ?></td>
            </tr>
            <tr>
                <th width="300"><?php _e("PayPal Pay Button URL", ud_get_wp_invoice()->domain); ?></th>
                <td><?php echo WPI_UI::draw_inputfield('wp_invoice_fe_paypal_link_url',$wp_invoice_fe_paypal_link_url); ?></td>
            </tr>
        </table>
    </div>
    <h3 id="ccsetup"><a href="#" class="selector"><?php _e("Credit Card", ud_get_wp_invoice()->domain) ?></a></h3>
    <div>
    <table class="form-table">
            <tr class="gateway_info">
                    <th width="300"><a class="wp_invoice_tooltip" title="<?php _e('Your credit card processor will provide you with a gateway username.', ud_get_wp_invoice()->domain); ?>"><?php _e('Gateway Username', ud_get_wp_invoice()->domain); ?></a></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_username',$wp_invoice_gateway_username, ' AUTOCOMPLETE="off"  '); ?>
                    </td>
            </tr>
            <tr class="gateway_info">
                    <th width="300"><a class="wp_invoice_tooltip" title="<?php _e("You will be able to generate this in your credit card processor's control panel.", ud_get_wp_invoice()->domain); ?>"><?php _e('Gateway Transaction Key', ud_get_wp_invoice()->domain); ?></a></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_tran_key',$wp_invoice_gateway_tran_key, ' AUTOCOMPLETE="off"  '); ?></td>
            </tr>
            <tr class="gateway_info">
                    <th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('This is the URL provided to you by your credit card processing company.', ud_get_wp_invoice()->domain); ?>"><?php _e('Gateway URL', ud_get_wp_invoice()->domain); ?></a></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_url',$wp_invoice_gateway_url); ?><br />
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi');">MerchantPlus</span> |
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://secure.authorize.net/gateway/transact.dll');">Authorize.Net</span> |
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://test.authorize.net/gateway/transact.dll');">Authorize.Net Developer</span>
                    </td>
            </tr>
            <tr class="gateway_info">
                    <th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('Recurring billing gateway URL is most likely different from the Gateway URL, and will almost always be with Authorize.net. Be advised - test credit card numbers will be declined even when in test mode.', ud_get_wp_invoice()->domain); ?>"><?php _e('Recurring Billing Gateway URL', ud_get_wp_invoice()->domain); ?></a></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_recurring_gateway_url',$wp_invoice_recurring_gateway_url); ?><br />
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_recurring_gateway_url').val('https://api.authorize.net/xml/v1/request.api');">Authorize.net ARB</span> |
                    <span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_recurring_gateway_url').val('https://apitest.authorize.net/xml/v1/request.api');">Authorize.Net ARB Testing</span>
                    </td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th><?php _e('Test / Live Mode:', ud_get_wp_invoice()->domain) ?></th>
                    <td><?php echo WPI_UI::draw_select('wp_invoice_gateway_test_mode',array("TRUE" => "Test - Do Not Process Transactions","FALSE" => "Live - Process Transactions"), $wp_invoice_gateway_test_mode); ?></td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('Get this from your credit card processor. If the transactions are not going through, this character is most likely wrong.', ud_get_wp_invoice()->domain); ?>"><?php _e('Delimiter Character', ud_get_wp_invoice()->domain); ?></a></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_delim_char',$wp_invoice_gateway_delim_char); ?>
            </tr>
            <tr class="advanced_gateway_info">
                    <th width="300"><a class="wp_invoice_tooltip" title="<?php _e('Authorize.net default is blank. Otherwise, get this from your credit card processor. If the transactions are going through, but getting strange responses, this character is most likely wrong.', ud_get_wp_invoice()->domain); ?>"><?php _e('Encapsulation Character', ud_get_wp_invoice()->domain); ?></a></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_encap_char',$wp_invoice_gateway_encap_char); ?></td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th width="300"><?php _e('Merchant Email', ud_get_wp_invoice()->domain); ?></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_merchant_email',$wp_invoice_gateway_merchant_email); ?></td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th><?php _e('Email Customer (on success):', ud_get_wp_invoice()->domain); ?></th>
                    <td><?php echo WPI_UI::draw_select('wp_invoice_gateway_email_customer',array("TRUE" => __("Yes", ud_get_wp_invoice()->domain),"FALSE" => __("No", ud_get_wp_invoice()->domain)), $wp_invoice_gateway_test_mode); ?></td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th width="300"><?php _e('Customer Receipt Email Header', ud_get_wp_invoice()->domain); ?></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_header_email_receipt',$wp_invoice_gateway_header_email_receipt); ?></td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th width="300"><?php _e('Security: MD5 Hash', ud_get_wp_invoice()->domain); ?></th>
                    <td><?php echo WPI_UI::draw_inputfield('wp_invoice_gateway_MD5Hash',$wp_invoice_gateway_MD5Hash); ?></td>
            </tr>
            <tr class="advanced_gateway_info">
                    <th><?php _e('Delim Data:', ud_get_wp_invoice()->domain); ?></th>
                    <td><?php echo WPI_UI::draw_select('wp_invoice_gateway_delim_data',array("TRUE" => __("True", ud_get_wp_invoice()->domain),"FALSE" => __("False", ud_get_wp_invoice()->domain)), $wp_invoice_gateway_delim_data); ?></td>
            </tr>
            <tr class="">
                    <th width="300">&nbsp;</th>
                    <td><span class="wp_invoice_click_me" onClick="jQuery('.advanced_gateway_info').toggle();"><?php _e('Toggle Advanced Options', ud_get_wp_invoice()->domain); ?></span></td>
            </tr>
        </table>
    </div>
</div>
