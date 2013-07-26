<form method="post" name="credit_card_checkout_form" id="credit_card_checkout_form" class="online_payment_form" onsubmit="validate_cc_checkout(); return false;" class="clearfix">
  <input type="hidden" name="wp_invoice[action]" value="process_credit_card" />
  <input type="hidden" name="wp_invoice[hash]" value="<?php echo wp_create_nonce($invoice['invoice_id'] .'hash');; ?>" />
  <input type="hidden" name="cc_data[amount]" value="<?php echo $invoice['amount']; ?>" />
  <input type="hidden" name="cc_data[user_id]" value="<?php echo $invoice['user_data']['user_email']; ?>" />
  <input type="hidden" name="cc_data[invoice_id]" value="<?php echo  $invoice['invoice_id']; ?>" />
  <input type="hidden" name="cc_data[currency_code]" id="currency_code"  value="<?php echo $invoice['meta']['default_currency_code']; ?>" />
  <fieldset id="credit_card_information">
    <ol>
  <li>
    <label for="first_name"><?php _e('First Name', WPI); ?></label>
    <input name="cc_data[first_name]" value="<?php echo $invoice[user_data][first_name];?>" />
    </li>

    <li>
    <label for="last_name"><?php _e('Last Name', WPI); ?></label>
    <input name="cc_data[last_name]" value="<?php echo $invoice[user_data][last_name];?>" />

    </li>

    <li>
    <label for="email"><?php _e('Email Address', WPI); ?></label>
    <input name="cc_data[email_address]" value="<?php echo $invoice[user_data][email_address];?>" />
    </li>

    <li>
    <label class="inputLabel" for="phonenumber"><?php _e('Phone Number', WPI); ?></label>
    <input name="cc_data[phonenumber]" class="input_field"  type="text" id="phonenumber" size="40" maxlength="50" value="<?php print $invoice['user_data']['phonenumber']; ?>" />
    </li>

    <li>
    <label for="address"><?php _e('Address', WPI); ?></label>
    <input name="cc_data[address]" value="<?php echo $invoice[user_data][streetaddress];?>" />
    </li>

    <li>
    <label for="city"><?php _e('City', WPI); ?></label>
    <input name="cc_data[city]" value="<?php echo $invoice[user_data][city];?>" />
    </li>

    <?php 
    
    switch ($wpi_settings['state_selection']) {
      case 'hide':
      break;
      
      case 'dropdown':
        echo '<li id="state_field"><label for="state">State</label>';        
        echo WPI_UI::select("name=cc_data[state]&current_value={$invoice[user_data][state]}&values=us_states");
        echo '</li>';
      break;
    
      case 'input_field':
        echo '<li id="state_field"><label for="state">State</label>';        
        echo "<input name='cc_data[state]' value='{$invoice[user_data][state]}' />";
        echo '</li>';
      break;
    
    }
    
    ?>

    <li>
    <label for="zip"><?php _e('Zip Code', WPI); ?></label>
    <input name="cc_data[zip]" value="<?php echo $invoice[user_data][zip];?>" />
    </li>

    <li>
    <label for="country"><?php _e('Country', WPI); ?></label>
      <?php echo WPI_UI::select("name=cc_data[country]&current_value={$invoice[user_data][country]}&values=countries"); ?>
    </li>

    <li class="hide_after_success">
    <label class="inputLabel" for="cc_data[card_num]"><?php _e('Credit Card Number', WPI); ?></label>
    <input name="cc_data[card_num]" autocomplete="off" onkeyup="cc_card_pick();"  id="cc_data[card_num]" class="credit_card_number input_field"  type="text"  size="22"  maxlength="22" />
    </li>

    <li class="hide_after_success nocard"  id="cardimage" style="background: url(<?php echo $wpi_settings['frontend_path']; ?>/core/images/card_array.png) no-repeat;">
    </li>

    <li class="hide_after_success">
    <label class="inputLabel" for="exp_month"><?php _e('Expiration Date', WPI); ?></label>
    <?php _e('Month', WPI); ?> <?php echo WPI_UI::select("name=cc_data[exp_year]&values=months"); ?>
    <?php _e('Year', WPI); ?> <?php echo WPI_UI::select("name=cc_data[exp_year]&values=years"); ?>

    </li>

    <li class="hide_after_success">
    <label class="inputLabel" for="card_code"><?php _e('Security Code', WPI); ?></label>
    <input id="card_code" autocomplete="off"  name="cc_data[card_code]" class="input_field"  style="width: 70px;" type="text" size="4" maxlength="4" />
    </li>

    <li id="wp_invoice_process_wait">
    <label for="submit"><span></span>&nbsp;</label>
    <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php echo sprintf(__('Process Payment of %s', WPI), $invoice['meta']['currency_symbol']  . WPI_Functions::money_format($invoice['amount'])); ?></button>
    </li>  
    
  <br class="cb" />  
    </ol>
  </fieldset>
</form>
&nbsp;<div id="wp_cc_response"></div>  