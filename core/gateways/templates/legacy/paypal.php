<script type="text/javascript">
/*jQuery(document).ready(function(){
    jQuery("#wpi_paypal_payment_form").submit(function() {
    <?php if($invoice['meta']['terms_acceptance_required'] == 'on') { ?>        
        if (jQuery(".wpi_term_acceptance").is(":not(:checked)")) {
            jQuery("li.wpi_terms").css('background','#FFEFEF');
            return false;                
        }
    <?php } ?>
   });
});*/
</script>

<form action="https://www.paypal.com/us/cgi-bin/webscr" id="wpi_paypal_payment_form" class="wpi_payment_form" method="post" class="clearfix">
    <input type="hidden" name="currency_code" value="<?php echo $invoice['meta']['default_currency_code']; ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="upload" value="1">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="<?php echo $invoice['billing']['paypal']['settings']['paypal_address']['value']; ?>">
    <input type="hidden" name="return" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>">
    <input type="hidden" name="notify_url" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>">
    <input type="hidden" name="rm" value="2">
    <input type="hidden" name="cancel_return" value="<?php echo get_invoice_permalink($invoice['invoice_id']); ?>&return_info=cancel">
    <input type="hidden" name="amount" value="<?php echo $invoice['amount']; ?>">
    <input type="hidden" name="cbt" value="Go back to Merchant">
    <input type="hidden" name="item_name" value="<?php echo $invoice['subject']; ?>"> 
    <input type="hidden" name="invoice" id="invoice_id" value="<?php echo $invoice['invoice_id']; ?>">

    <fieldset id="credit_card_information">
        <ol>
            <li>
                <label for="first_name"><?php _e('First Name', WPI); ?></label>
                <input name="first_name" value="<?php echo $invoice['user_data'][first_name];?>" />
            </li>
            <li>
                <label for="last_name"><?php _e('Last Name', WPI); ?></label>
                <input name="last_name" value="<?php echo $invoice[user_data][last_name];?>" />
            </li>
            <li>
                <label for="email"><?php _e('Email Address', WPI); ?></label>
                <input name="email_address" value="<?php echo $invoice[user_data][email_address];?>" />
            </li>
            <?php list($night_phone_a, $night_phone_b, $night_phone_c) = split('[/.-]', $invoice[user_data][phone_number]); ?>
            <li>
                <label for="day_phone_a"><?php _e('Phone Number', WPI); ?></label>
                <input name="night_phone_a" value="<?php echo $night_phone_a;?>" style="width:25px;" size="3" maxlength="3" />
                <input name="night_phone_b" value="<?php echo $night_phone_b;?>" style="width:25px;" size="3" maxlength="3" />
                <input name="night_phone_c" value="<?php echo $night_phone_c;?>" style="width:25px;" size="3" maxlength="3" />
            </li>
            <li>
                <label for="address"><?php _e('Address', WPI); ?></label>
                <input name="address1" value="<?php echo $invoice[user_data][streetaddress];?>" />
            </li>
            <li>
                <label for="city"><?php _e('City', WPI); ?></label>
                <input name="city" value="<?php echo $invoice[user_data][city];?>" />
             </li>
            <?php 
            switch ($wpi_settings['state_selection']) {
                case 'hide':
                    break;
                case 'dropdown':
                    echo '<li id="state_field"><label for="state">State</label>';                
                    echo WPI_UI::select("name=state&current_value={$invoice[user_data][state]}&values=us_states");
                    echo '</li>';
                    break;
                case 'input_field':
                    echo '<li id="state_field"><label for="state">State</label>';                
                    echo "<input name='state' value='{$invoice[user_data][state]}' />";
                    echo '</li>';
                    break;
            }
            ?>
            <li>
                <label for="zip"><?php _e('Zip Code', WPI); ?></label>
                <input name="zip" value="<?php echo $invoice[user_data][zip];?>" />
            </li>
            <li>
                <label for="country"><?php _e('Country', WPI); ?></label>
                <?php echo WPI_UI::select("name=country&current_value={$invoice[user_data][country]}&values=countries"); ?>
            </li>
            <?php /*if($invoice['meta']['terms_acceptance_required'] == 'on') { ?>
                <li class="wpi_terms">
                    <label for="term_acceptance">&nbsp;</label>
                    <?php show_terms_acceptance(); ?>I accept the terms defined in the service agreement.
                </li>
            <?php }*/ ?>
            <li>
                <label for="submit">&nbsp;</label>
                <input type="image"  src="<?php echo $invoice['billing']['paypal']['settings']['button_url']['value']; ?>" class="paypal_button" name="submit" alt="<?php _e('Pay with PayPal', WPI); ?>">
            </li>

            <br class="cb" />    
        </ol>
    </fieldset>

</form>
