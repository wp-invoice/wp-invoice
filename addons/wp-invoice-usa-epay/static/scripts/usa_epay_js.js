/* Our Rules for this type of form */
var wpi_usa_epay_rules = {
  "first_name": {
    required: true
  },
  "last_name": {
    required: true
  },
  "email": {
    required: true,
    email: true
  },
  "street": {
    required: true
  },
  "city": {
    required: true
  },
  "acct": {
    required: true,
    creditcard: true
  },
  "exp_m": {
    required: true
  },
  "exp_y": {
    required: true
  },
  "cvv2": {
    required: true
  }
};

/* Our messages for this type of form */
var wpi_usa_epay_messages = {
  "first_name": {
    required: "First name is required."
  },
  "last_name": {
    required: "Last name is required."
  },
  "email": {
    required: "An e-mail address is required.",
    email: "E-mail address is not valid."
  },
  "exp_m": {
    required: "Expiration month is required."
  },
  "exp_y": {
    required: "Expiration year is required."
  },
  "cvv2": {
    required: "CVV code is required."
  },
  "acct": {
    required: "Credit card number is required.",
    creditcard: "Credit card number is not valid"
  }
};

/* This function happens when the form is initialized */
var wpi_usa_epay_init_form = function() {
  jQuery("#online_payment_form_wrapper").trigger('formLoaded');
};

/* This function adds to form validation, and returns true or false */
var wpi_usa_epay_validate_form = function(){
  return true;
};

/* This function handles the submit event */
var wpi_usa_epay_submit = function(){
  jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
  jQuery( ".loader-img" ).show();
  var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
  var message = '';
  jQuery.post(url, jQuery("#online_payment_form-wpi_usa_epay").serialize(), function(d){
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