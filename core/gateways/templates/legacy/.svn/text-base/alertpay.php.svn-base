<form method="post" action="https://www.alertpay.com/PayProcess.aspx" >
   <input type="hidden" name="ap_merchant" value="<?php echo $wpi_settings['billing']['alertpay']['settings']['pay_to_email']['value'] ?>"/>
    <input type="hidden" name="ap_purchasetype" value="service"/>
    <input type="hidden" name="ap_itemname" value="<?php echo $invoice['subject'] ?>"/>
    <input type="hidden" name="ap_amount" value="<?php echo $invoice['amount'] ?>"/>
    <input type="hidden" name="ap_currency" value="<?php echo $invoice['meta']['default_currency_code'] ?>"/>

    <input type="image" style="width:86px; height:30px" src="https://www.alertpay.com//PayNow/4F59239578EA46C1AD168BA6E9BD2067g.gif"/>
</form>