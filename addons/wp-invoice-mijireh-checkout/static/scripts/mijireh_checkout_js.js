/* Our Rules for this type of form */
var wpi_mijireh_checkout_rules = {
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
  "state": {
    required: true
  },
  "zip": {
    required: true
  }
};

/* Our messages for this type of form */
var wpi_mijireh_checkout_messages = {
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
  "street": {
    required: "Street is required."
  },
  "city": {
    required: "City is required."
  },
  "state": {
    required: "State is required."
  },
  "zip": {
    required: "Zip Code is required."
  }
};

/* This function happens when the form is initialized */
var wpi_mijireh_checkout_init_form = function() {
  jQuery("#online_payment_form_wrapper").trigger('formLoaded');
};

/* This function adds to form validation, and returns true or false */
var wpi_mijireh_checkout_validate_form = function(){
  return true;
};

/* This function handles the submit event */
var wpi_mijireh_checkout_submit = function(){
  jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
  jQuery( ".loader-img" ).show();
  var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
  var message = '';
  jQuery.post(url, jQuery("#online_payment_form-wpi_mijireh_checkout").serialize(), function(d){
    if ( d.success ) {
      jQuery('#trans-results').css({background:"#EDFFDF"});
      if ( d.data.redirect ) {
        window.location.href = d.data.redirect;
      }
    } else if ( d.error ) {
      jQuery('#trans-results').css({background:"#FFDFDF"});
      jQuery.each( d.data.messages, function(k, v){
        message += v +'\n\n';
      });
      alert( message );
      location.reload(true);
    }
  }, 'json');
  return false;
};