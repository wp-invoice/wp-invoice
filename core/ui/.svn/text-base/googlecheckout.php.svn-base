<form action="https://sandbox.google.com/checkout/cws/v2/Merchant/<?php echo $invoice->display('wp_invoice_googlecheckout_address'); ?>/checkoutForm" method="post" class="clearfix"  charset="utf-8">
<input  type="hidden" name="invoice" id="invoice_num"  value="<?php echo  $invoice->display('display_id'); ?>">
<?php
// Convert Itemized List into PayPal Item List 
if(is_array($invoice->display('itemized'))) echo wp_invoice_create_googlecheckout_itemized_list($invoice->display('itemized'),$invoice_id);
?>


<fieldset id="credit_card_information">
	<ol>
	
	<li>
	You will be taken to Google Checkout where your payment information will be processed.
	</li>
	
	

	<li>
	<label for="submit">&nbsp;</label>
	<input type="image" name="Google Checkout" alt="Fast checkout through Google"
        src="http://sandbox.google.com/checkout/buttons/checkout.gif?merchant_id=<?php echo $invoice->display('wp_invoice_googlecheckout_address'); ?>&w=180&h=46&style=white&variant=text&loc=en_US" height="46" width="180">

			  
			 
	</li>
	
	<br class="cb" />	
	</ol>
</fieldset>
</form>