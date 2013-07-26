
<form action="https://www.paypal.com/us/cgi-bin/webscr" method="post" class="clearfix">
<input type="hidden" name="currency_code" value="<?php echo $invoice->display('currency'); ?>">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="upload" value="1">
<input type="hidden" name="business" value="<?php echo $invoice->display('wp_invoice_paypal_address'); ?>">
<input type="hidden" name="return" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>">
<input type="hidden" name="rm" value="2">
<input type="hidden" name="amount"  value="<?php echo $invoice->display('amount'); ?>">
<input  type="hidden" name="invoice" id="invoice_num"  value="<?php echo  $invoice->display('display_id'); ?>">
<?php
// Convert Itemized List into PayPal Item List 
if(is_array($invoice->display('itemized'))) echo wp_invoice_create_paypal_itemized_list($invoice->display('itemized'),$invoice_id);
?>


<fieldset id="credit_card_information">
	<ol>
	
	<li>
	<label for="first_name">First Name</label>
	<?php echo wp_invoice_draw_inputfield("first_name",$invoice->recipient('first_name')); ?>
	</li>
	
	<li>
	<label for="last_name">Last Name</label>
	<?php echo wp_invoice_draw_inputfield("last_name",$invoice->recipient('last_name')); ?>
	</li>

	<li>
	<label for="email">Email Address</label>
	<?php echo wp_invoice_draw_inputfield("email_address",$invoice->recipient('email_address')); ?>
	</li>
	
	<?php
	list($day_phone_a, $day_phone_b, $day_phone_c) = split('[/.-]', $invoice->recipient('paypal_phonenumber'));
	?>
	<li>
	<label for="day_phone_a">Phone Number</label>
	<?php echo wp_invoice_draw_inputfield("night_phone_a",$day_phone_a,' style="width:25px;" size="3" maxlength="3" '); ?>-
	<?php echo wp_invoice_draw_inputfield("night_phone_b",$day_phone_b,' style="width:25px;" size="3" maxlength="3" '); ?>-
	<?php echo wp_invoice_draw_inputfield("night_phone_c",$day_phone_c,' style="width:35px;" size="4" maxlength="4" '); ?>
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
	<label for="zip">Zip/Postal Code</label>
	<?php echo wp_invoice_draw_inputfield("zip",$invoice->recipient('zip')); ?>
	</li>

	<li>
	<label for="country">Country</label>
	<?php echo wp_invoice_draw_select('country',wp_invoice_country_array(),"US"); ?>
	</li>
	

	<li>
	<label for="submit">&nbsp;</label>
	<input type="image"  src="https://www.paypal.com/en_US/i/btn/btn_paynow_LG.gif" style="border:0; width:107px; height:26px;padding:0;" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
	</li>
	
	<br class="cb" />	
	</ol>
</fieldset>
</form>