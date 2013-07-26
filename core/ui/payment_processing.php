<div id="wp_invoice_billing_information" class="wp_invoice_tabbed_content"> 
	<ul> 
		<li><a <?php if($wp_invoice_payment_method == 'paypal') echo 'class="selected"';?>  href="#paypal_tab"><?php _e("PayPal") ?></a></li> 
<?php /*<li><a <?php if($wp_invoice_payment_method == 'moneybookers') echo 'class="selected"';?> href="#moneybookers_tab"><?php _e("Moneybookers") ?></a></li> */ ?>
		<li><a <?php if($wp_invoice_payment_method == 'cc') echo 'class="selected"';?> href="#cc_tab"><?php _e("Credit Card") ?></a></li> 
<?php /*<li><a <?php if($wp_invoice_payment_method == 'alertpay') echo 'class="selected"';?> href="#alertpay_tab"><?php _e("Alertpay") ?></a></li> */ ?>
	</ul> 


  <div id="paypal_tab" class="wp_invoice_tab" >
		<table class="form-table">
			<tr>
				<th width="300"><?php _e("Accept This Payment Venue?"); ?></th>
				<td><?php echo wp_invoice_draw_select('wp_invoice_paypal_allow',array("yes" => "Yes","no" => "No"), $wp_invoice_paypal_allow); ?></td>
			</tr>

			<tr>
				<th width="300"><?php _e("PayPal Username"); ?></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_paypal_address',$wp_invoice_paypal_address); ?></td>
			</tr>
			
<?php if($hide_advanced_paypal_features) { ?>
			<tr>
				<th width="300"><?php _e("PayPal Pay Button URL"); ?></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_fe_paypal_link_url',$wp_invoice_fe_paypal_link_url); ?></td>
			</tr>
<?php } ?>
			
		</table>
  </div>


  <div id="cc_tab" class="wp_invoice_tab" >
		<table class="form-table">

			<tr class="">
				<th width="300"><?php _e("Accept this Payment Venue?") ?></th>
				<td><?php echo wp_invoice_draw_select('wp_invoice_cc_allow',array("yes" => "Yes","no" => "No"), $wp_invoice_cc_allow); ?></td>
			</tr>
		
			<tr class="gateway_info payment_info">
				<th width="300"><a class="wp_invoice_tooltip" title="<?php _e('Your credit card processor will provide you with a gateway username.', WP_INVOICE_TRANS_DOMAIN); ?>"><?php _e('Gateway Username', WP_INVOICE_TRANS_DOMAIN); ?></a></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_username',$wp_invoice_gateway_username, ' AUTOCOMPLETE="off"  '); ?>
				</td>
			</tr>

			<tr class="gateway_info payment_info">
				<th width="300"><a class="wp_invoice_tooltip" title="<?php _e("You will be able to generate this in your credit card processor's control panel.", WP_INVOICE_TRANS_DOMAIN); ?>"><?php _e('Gateway Transaction Key', WP_INVOICE_TRANS_DOMAIN); ?></a></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_tran_key',$wp_invoice_gateway_tran_key, ' AUTOCOMPLETE="off"  '); ?></td>
			</tr>


			<tr class="gateway_info payment_info">
				<th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('This is the URL provided to you by your credit card processing company.', WP_INVOICE_TRANS_DOMAIN); ?>"><?php _e('Gateway URL', WP_INVOICE_TRANS_DOMAIN); ?></a></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_url',$wp_invoice_gateway_url); ?><br />
				<span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi');">MerchantPlus</span> |
				<span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://secure.authorize.net/gateway/transact.dll');">Authorize.Net</span> |
				<span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_gateway_url').val('https://test.authorize.net/gateway/transact.dll');">Authorize.Net Developer</span> 
				</td>
			</tr>

			<tr class="gateway_info payment_info">
				<th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('Recurring billing gateway URL is most likely different from the Gateway URL, and will almost always be with Authorize.net. Be advised - test credit card numbers will be declined even when in test mode.', WP_INVOICE_TRANS_DOMAIN); ?>"><?php _e('Recurring Billing Gateway URL', WP_INVOICE_TRANS_DOMAIN); ?></a></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_recurring_gateway_url',$wp_invoice_recurring_gateway_url); ?><br />
				<span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_recurring_gateway_url').val('https://api.authorize.net/xml/v1/request.api');">Authorize.net ARB</span> |
				<span class="wp_invoice_click_me" onclick="jQuery('#wp_invoice_recurring_gateway_url').val('https://apitest.authorize.net/xml/v1/request.api');">Authorize.Net ARB Testing</span>
				</td>
			</tr>

<?php if($hide_advanced_cc_features) { ?>
			<tr class="gateway_info payment_info">
				<th>Test / Live Mode:</th>
				<td><?php echo wp_invoice_draw_select('wp_invoice_gateway_test_mode',array("TRUE" => "Test - Do Not Process Transactions","FALSE" => "Live - Process Transactions"), $wp_invoice_gateway_test_mode); ?></td>
			</tr>

			<tr class="gateway_info">
				<th width="300"><a class="wp_invoice_tooltip"  title="<?php _e('Get this from your credit card processor. If the transactions are not going through, this character is most likely wrong.', WP_INVOICE_TRANS_DOMAIN); ?>"><?php _e('Delimiter Character', WP_INVOICE_TRANS_DOMAIN); ?></a></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_delim_char',$wp_invoice_gateway_delim_char); ?>
			</tr>

			<tr class="gateway_info">
				<th width="300"><a class="wp_invoice_tooltip" title="<?php _e('Authorize.net default is blank. Otherwise, get this from your credit card processor. If the transactions are going through, but getting strange responses, this character is most likely wrong.', WP_INVOICE_TRANS_DOMAIN); ?>"><?php _e('Encapsulation Character', WP_INVOICE_TRANS_DOMAIN); ?></a></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_encap_char',$wp_invoice_gateway_encap_char); ?></td>
			</tr>

			<tr class="gateway_info">
				<th width="300"><?php _e('Merchant Email', WP_INVOICE_TRANS_DOMAIN); ?></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_merchant_email',$wp_invoice_gateway_merchant_email); ?></td>
			</tr>

			<tr class="gateway_info">
				<th><?php _e('Email Customer (on success):', WP_INVOICE_TRANS_DOMAIN); ?></th>
				<td><?php echo wp_invoice_draw_select('wp_invoice_gateway_email_customer',array("TRUE" => "Yes","FALSE" => "No"), $wp_invoice_gateway_test_mode); ?></td>
			</tr>

			<tr class="gateway_info">
				<th width="300"><?php _e('Security: MD5 Hash', WP_INVOICE_TRANS_DOMAIN); ?></th>
				<td><?php echo wp_invoice_draw_inputfield('wp_invoice_gateway_MD5Hash',$wp_invoice_gateway_MD5Hash); ?></td>				</td>
			</tr>

			<tr class="gateway_info">
				<th><?php _e('Delim Data:', WP_INVOICE_TRANS_DOMAIN); ?></th>
				<td><?php echo wp_invoice_draw_select('wp_invoice_gateway_delim_data',array("TRUE" => "True","FALSE" => "False"), $wp_invoice_gateway_delim_data); ?></td>
			</tr>
<?php } ?>			
		</table>
  </div>

</div>
<script type="text/javascript"> 
  jQuery("#wp_invoice_billing_information ul").idTabs(); 
</script>
