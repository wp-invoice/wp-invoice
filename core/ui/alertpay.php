<form action="https://www.alertpay.com/PayProcess.aspx" method="post" class="clearfix" >
	<input type="hidden" name="ap_currency" value="<?php echo $invoice->display('currency'); ?>" />
	<input type="hidden" name="ap_purchasetype" value="Service">
	<input type="hidden" name="ap_merchant" value="<?php echo $invoice->display('wp_invoice_alertpay_address'); ?>" />
	<input type="hidden" name="ap_totalamount" value="<?php echo $invoice->display('amount'); ?>" />
	<input type="hidden" name="ap_itemname" id="invoice_num" value="<?php echo  $invoice->display('display_id'); ?>" />
	<input type="hidden" name="ap_returnurl" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>" />
	<?php
	// Convert Itemized List into AlertPay Item List (Not supported, we just show an aggregated fields)
	if(is_array($invoice->display('itemized'))) echo wp_invoice_create_alertpay_itemized_list($invoice->display('itemized'),$invoice_id);
	?>
	<fieldset id="credit_card_information">
	<ol>
		<li>
		<label for="submit">&nbsp;</label>
		<input type="image" src="https://www.alertpay.com//PayNow/4FF7280888FE4FD4AE1B4A286ED9B8D5a.gif" style="border:0; width:170px; height:70px; padding:0;" name="submit" alt="Pay now with AlertPay" />
		</li>
	</ol>
	</fieldset>
</form>