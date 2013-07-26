<?php

// Add Billing Options
add_filter('wpi_billing_method', 'wpi_billing_google_checkout', 0, 2);

function wpi_billing_google_checkout($billing, $invoice_detail = false) {

    // Add Google Checkout    
    $billing['google_checkout']['name'] = __("Google Checkout", WPI);
    $billing['google_checkout']['allow'] = true; // Check the allowed payment box or not


    // Admin Form Settings
    $billing['google_checkout']['settings']['pay_to_email']['label'] = __('Payment Address', WPI);
    $billing['google_checkout']['settings']['pay_to_email']['value'] = 'me@moneybookers.com';
    $billing['google_checkout']['settings']['button_url']['label'] = 'Button URL';
    $billing['google_checkout']['settings']['button_url']['value'] = 'http://acitykiosks.com/images/moneybookers.gif';
   
    // Checkout Settings
    $billing['google_checkout']['form_options']['pay_to_email'] = $invoice_detail->admin_email;
    $billing['google_checkout']['form_options']['status_url'] = $invoice_detail->admin_email;
    $billing['google_checkout']['form_options']['language'] = 'EN';
    $billing['google_checkout']['form_options']['amount'] = $invoice_detail->amount;
    $billing['google_checkout']['form_options']['currency'] = $invoice_detail->currency;
    $billing['google_checkout']['form_options']['detail1_description'] = $invoice_detail->description;
    $billing['google_checkout']['form_options']['detail1_text'] = $invoice_detail->subject;
 
    // Visible Form Fields
    $billing['google_checkout']['form_fields']['detail1_text'] = $invoice_detail->subject;

    return $billing;
}

function wpi_billing_google_checkout_form($invoice) { ?>
    <form method="POST"  action="https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/1234567890" accept-charset="utf-8">
      <input type="hidden" name="item_name_1" value="Peanut Butter"/>
      <input type="hidden" name="item_description_1" value="Chunky peanut butter."/>
      <input type="hidden" name="item_quantity_1" value="1"/>
      <input type="hidden" name="item_price_1" value="3.99"/>
      <input type="hidden" name="item_currency_1" value="USD"/>
      <input type="hidden" name="ship_method_name_1" value="UPS Ground"/>
      <input type="hidden" name="ship_method_price_1" value="10.99"/>
      <input type="hidden" name="ship_method_currency_1" value="USD"/>
      <input type="hidden" name="tax_rate" value="0.0875"/>
      <input type="hidden" name="tax_us_state" value="NY"/>
      <input type="hidden" name="_charset_"/>
      <input type="image" name="<?php _e('Google Checkout', WPI) ?>" alt="<?php _e('Fast checkout through Google', WPI) ?>" src="http://checkout.google.com/buttons/checkout.gif?merchant_id=1234567890&w=180&h=46&style=white&variant=text&loc=en_US" height="46" width="180"/>
    </form>
    <?php 
} 
?>