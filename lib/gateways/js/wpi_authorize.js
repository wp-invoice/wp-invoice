/* Our Rules for this type of form */
var wpi_authorize_rules = {
  "cc_data[first_name]": {
    required: true
  },
  "cc_data[last_name]": {
    required: true
  },
  "cc_data[card_num]": {
    required: true,
    creditcard: true
  },
  "cc_data[email_address]": {
    required: true,
    email: true
  },
  "cc_data[phonenumber]": {
    required: true
  },
  "cc_data[address]": {
    required: true
  },
  "cc_data[city]": {
    required: true
  },
  "cc_data[zip]": {
    required: true
  },
  "cc_data[exp_month]": {
    required: true
  },
  "cc_data[card_code]": {
    required: true
  }
};

/* Our messages for this type of form */
var wpi_authorize_messages = {
  "cc_data[first_name]": {
    required: "First name is required."
  },
  "cc_data[last_name]": {
    required: "Last name is required."
  },
  "cc_data[card_num]": {
    required: "Credit card number is required.",
    creditcard: "Credit card number is not valid"
  },
  "cc_data[email_address]": {
    required: "An e-mail address is required.",
    email: "E-mail address is not valid."
  },
  "cc_data[phonenumber]": {
    required: "Phone number is required."
  },
  "cc_data[address]": {
    required: "Address line is required."
  },
  "cc_data[city]": {
    required: "City is required."
  },
  "cc_data[zip]": {
    required: "Zip code is required"
  },
  "cc_data[exp_month]": {
    required: "Expiration month is required."
  },
  "cc_data[card_code]": {
    required: "CCV code is required."
  }
};

/* This function happens when the form is initialized */
var wpi_authorize_init_form = function() {
  jQuery("#online_payment_form_wrapper").trigger('formLoaded');
  /* Do our masks */
  //jQuery("#phonenumber").mask("999-999-9999");
  //jQuery("#zip").mask("99999");
  //jQuery("#card_num").mask("9999999999999999");
  //jQuery("#card_code").mask("999");
  /* Setup the function to validate CCards*/
  jQuery("#card_num").keyup(function(){
    numLength = jQuery('#card_num').val().length;
    number = jQuery('#card_num').val();
    if(numLength > 10)
    {
      if((number.charAt(0) == '4') && ((numLength == 13)||(numLength==16))) { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('visa_card'); }
      else if((number.charAt(0) == '5' && ((number.charAt(1) >= '1') && (number.charAt(1) <= '5'))) && (numLength==16)) { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('mastercard'); }
      else if(number.substring(0,4) == "6011" && (numLength==16))   { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('amex'); }
      else if((number.charAt(0) == '3' && ((number.charAt(1) == '4') || (number.charAt(1) == '7'))) && (numLength==15)) { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('discover_card'); }
      else { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('nocard'); }
    }
  });
};

/* This function adds to form validation, and returns true or false */
var wpi_authorize_validate_form = function(){
  /* Add some extra validation for the masked elements */
  if(
    jQuery("#phonenumber").val() == "___-___-____" || 
    jQuery("#card_num").val() == "________________" || 
    jQuery("#card_code").val() == "___"
  ){
    return false;
  }
  return true;
};

/* This function handles the submit event */
var wpi_authorize_submit = function(){
  jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
  jQuery( ".loader-img" ).show();
  var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
  var message = '';
  jQuery.post(url, jQuery("#online_payment_form-wpi_authorize").serialize(), function(d){
    if ( d.success ) {
      jQuery('#trans-results').css({background:"#EDFFDF"});
    } else if ( d.error ) {
      jQuery('#trans-results').css({background:"#FFDFDF"});
    }
    jQuery.each( d.data.messages, function(k, v){
      message += v +'\n\n';
    });
    alert( message );
    location.reload(true);
  }, 'json');
  return false;
};