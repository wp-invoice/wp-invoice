<form action="https://www.moneybookers.com/app/payment.pl" method="post" class="clearfix">
	<input type="hidden" name="currency" value="<?php echo $invoice->display('currency'); ?>" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="pay_to_email" value="<?php echo get_option('wp_invoice_moneybookers_address'); ?>" />
	<input type="hidden" name="return_url" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>" />
	<input type="hidden" name="cancel_url" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>" />
	<input type="hidden" name="status_url" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>" />
	<input type="hidden" name="transaction_id" id="invoice_num" value="<?php echo  $invoice->display('display_id'); ?>" />
	<?php
	if (wp_invoice_recurring($invoice_id)) {
	?>
	<input type="hidden" name="rec_payment_id" value="<?php echo $invoice->display('display_id').date('YMD'); ?>" />
	<input type="hidden" name="rec_payment_type" value="recurring" />
	<input type="hidden" name="rec_status_url" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>" />
	<input type="hidden" name="rec_cycle" value="<?php echo preg_replace('/s$/', '', $invoice->display('interval_unit')); ?>" />
	<input type="hidden" name="rec_period" value="<?php echo $invoice->display('interval_length'); ?>" />
	<input type="hidden" name="rec_start_date" value="<?php echo $invoice->display('startDate'); ?>" />
	<input type="hidden" name="rec_end_date" value="<?php echo $invoice->display('endDate'); ?>" />
	<input type="hidden" name="rec_amount" value="<?php echo $invoice->display('amount'); ?>" />
	<?php
	} else {
	?>
	<input type="hidden" name="amount" value="<?php echo $invoice->display('amount'); ?>" />
	<?php 
		// Convert Itemized List into Moneybookers Item List
		if(is_array($invoice->display('itemized'))) {
			echo wp_invoice_create_moneybookers_itemized_list($invoice->display('itemized'),$invoice_id);
		}
	}
	?>
	<fieldset id="credit_card_information">
	<ol>
		<li>
		<label for="firstname"><?php _e('First Name', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_inputfield("firstname",$invoice->recipient('first_name')); ?>
		</li>

		<li>
		<label for="lastname"><?php _e('Last Name', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_inputfield("lastname",$invoice->recipient('last_name')); ?>
		</li>

		<li>
		<label for="pay_from_email"><?php _e('Email Address', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_inputfield("pay_from_email",$invoice->recipient('email_address')); ?>
		</li>

		<li>
		<label class="inputLabel" for="phone_number"><?php _e('Phone Number', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<input name="phone_number" class="input_field"  type="text" id="phone_number" size="40" maxlength="50" value="<?php print $invoice->recipient('phonenumber'); ?>" />
		</li>

		<li>
		<label for="address"><?php _e('Address', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_inputfield("address",$invoice->recipient('streetaddress')); ?>
		</li>

		<li>
		<label for="city"><?php _e('City', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_inputfield("city",$invoice->recipient('city')); ?>
		</li>

		<li>
		<label for="state"><?php _e('State', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php print wp_invoice_draw_select('state',wp_invoice_state_array(),$invoice->recipient('state'));  ?>
		</li>

		<li>
		<label for="postal_code"><?php _e('Zip Code', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_inputfield("postal_code",$invoice->recipient('zip')); ?>
		</li>

		<li>
		<label for="country"><?php _e('Country', WP_INVOICE_TRANS_DOMAIN); ?></label>
		<?php echo wp_invoice_draw_select('country',wp_invoice_country_array(),$invoice->recipient('country')); ?>
		</li>

		<li>
		<label for="submit">&nbsp;</label>
		<input type="image" src="http://www.moneybookers.com/images/logos/checkout_logos/checkoutlogo_CCs_240x80.gif" style="border:0; width:240px; height:80px; padding:0;" name="submit" alt="Moneybookers.com and money moves" />
		</li>
	<br class="cb" />
	</ol>
</fieldset>
</form>