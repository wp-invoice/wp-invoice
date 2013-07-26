<form method="POST" action="https://payflowlink.paypal.com">
    
    <input type="hidden" name="LOGIN" value="<?php echo $invoice['billing']['paypal']['settings']['LOGIN']['value'] ?>">
    <input type="hidden" name="PARTNER" value="<?php echo $invoice['billing']['paypal']['settings']['PARTNER']['value'] ?>">
    <input type="hidden" name="AMOUNT" value="<?php echo $invoice['amount'] ?>">
    <input type="hidden" name="TYPE" value="S">
    <input type="hidden" name="DESCRIPTION" value="<?php echo $invoice['subject'] ?>">
    
    <fieldset id="credit_card_information">
        <ol>
            <li>
                <label for="NAME"><?php _e('Billing Name', WPI); ?></label>
                <input name="NAME" type="text" id="NAME" value="<?php echo $invoice['user_data']['first_name'] . ' ' . $invoice['user_data']['last_name'];?>" />
            </li>
            <li>
                <label for="ADDRESS"><?php _e('Billing Address', WPI); ?></label>
                <input name="ADDRESS" type="text" id="ADDRESS" value="<?php echo $invoice['user_data']['address'] ?>" />
            </li>
            <li>
                <label for="CITY"><?php _e('City', WPI); ?></label>
                <input name="CITY" type="text" id="CITY" value="<?php echo $invoice['user_data']['city'] ?>" />
            </li>
            <?php 
            switch ($wpi_settings['state_selection']) {
                case 'hide':
                    break;
                case 'dropdown':
                    echo '<li id="state_field"><label for="state">'.__('State', WPI). '</label>';                
                    echo WPI_UI::select("name=STATE&current_value={$invoice['user_data']['state']}&values=us_states");
                    echo '</li>';
                    break;
                case 'input_field':
                    echo '<li id="state_field"><label for="state">'. __('State', WPI) . '</label>';                
                    echo "<input name='STATE' value='{$invoice['user_data']['state']}' />";
                    echo '</li>';
                    break;
            }
            ?>
            <li>
                <label for="ZIP"><?php _e('Zip Code', WPI); ?></label>
                <input name="ZIP" value="<?php echo $invoice['user_data']['zip'];?>" />
            </li>
            <li>
                <label for="COUNTRY"><?php _e('Country', WPI); ?></label>
                <?php echo WPI_UI::select("name=COUNTRY&current_value={$invoice['user_data']['country']}&values=countries"); ?>
            </li>
            
            <li>
                <label for="USER1"><?php _e('Your email', WPI); ?></label>
                <input type="text" name="USER1" size="12" value="<?php echo $invoice['user_data']['email_address'] ?>">
            </li>
            
            <li>
                <label><?php _e('The form of payment', WPI); ?></label>
                <select name="METHOD" size="1">
                    <option selected value="CC"><?php _e('Credit Card', WPI); ?></option>
                </select>
            </li>
            
            <li>
                <label for="submit">&nbsp;</label>
                <input type="image"  src="<?php echo $invoice['billing']['payflow']['settings']['button_url']['value']; ?>" class="payflow_button" name="submit" alt="<?php _e('Pay with Payflow', WPI) ?>">
            </li>

            <br class="cb" />
        </ol>
    </fieldset>
</form>
