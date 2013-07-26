<form action="https://www.moneybookers.com/app/payment.pl" method="post" class="clearfix">
<input type="hidden" name="currency" value="<?php echo $invoice->display('currency'); ?>" />
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="pay_to_email" value="<?php echo  $invoice->display('wp_invoice_moneybookers_address'); ?>" />
<input type="hidden" name="return_url" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>" />
<input type="hidden" name="amount" value="<?php echo $invoice->display('amount'); ?>" />
<input type="hidden" name="transaction_id" id="invoice_num" value="<?php echo  $invoice->display('display_id'); ?>" />
<?php
// Convert Itemized List into PayPal Item List
//if(is_array($invoice->display('itemized'))) echo wp_invoice_create_moneybookers_itemized_list($invoice->display('itemized'),$invoice_id);
?>



<fieldset id="credit_card_information">
	<ol>


	<li>
	<label for="firstname">First Name</label>
	<?php echo wp_invoice_draw_inputfield("firstname",$invoice->recipient('first_name')); ?>
	</li>

	<li>
	<label for="lastname">Last Name</label>
	<?php echo wp_invoice_draw_inputfield("lastname",$invoice->recipient('last_name')); ?>
	</li>

	<li>
	<label for="pay_from_email">Email Address</label>
	<?php echo wp_invoice_draw_inputfield("pay_from_email",$invoice->recipient('email_address')); ?>
	</li>

	<li>
	<label class="inputLabel" for="phone_number">Phone Number</label>
	<input name="phone_number" class="input_field"  type="text" id="phone_number" size="40" maxlength="50" value="<?php print $invoice->recipient('phonenumber'); ?>" />
	</li>

	<li>
	<label for="address">Address</label>
	<?php echo wp_invoice_draw_inputfield("address",$invoice->recipient('streetaddress')); ?>
	</li>

	<li>
	<label for="city">City</label>
	<?php echo wp_invoice_draw_inputfield("city",$invoice->recipient('city')); ?>
	</li>

	<li>
	<label for="state">State/Province</label>
	<?php print wp_invoice_draw_select('state',wp_invoice_state_array(),$invoice->recipient('state'));  ?>
	</li>


	<li>
	<label for="postal_code">Zip/Postal Code</label>
	<?php echo wp_invoice_draw_inputfield("postal_code",$invoice->recipient('zip')); ?>
	</li>
	
	<li>
	<label for="country">Country</label>
	<?php echo wp_invoice_draw_select('country',wp_invoice_country_array(),$invoice->recipient('country')); ?>
	</li>

	<li>
	<label for="submit">&nbsp;</label>
	<input type="image" src="https://www.moneybookers.com/images/logos/checkout_logos/checkoutlogo_CCs_240x80.gif" style="border:0; width:240px; height:80px; padding:0;" name="submit" alt="Moneybookers.com and money moves" />
	</li>
	

	<br class="cb" />
	</ol>
</fieldset>

</form>