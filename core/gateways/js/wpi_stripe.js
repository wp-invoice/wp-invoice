var wpi_stripe_rules = {
  "first_name": {
    required: true
  },
  "last_name": {
    required: true
  }
};

var wpi_stripe_messages = {
  "first_name": {
    required: "First name is required."
  },
  "last_name": {
    required: "Last name is required."
  }
};

/* This function adds to form validation, and returns true or false */
var wpi_stripe_validate_form = function(){

  // Get the values:
  var ccNum = jQuery('.card-number').val(),
  cvcNum = jQuery('.card-cvc').val(),
  expMonth = jQuery('.card-expiry-month').val(),
  expYear = jQuery('.card-expiry-year').val();

  // Validate the number:
  if (!Stripe.validateCardNumber(ccNum)) {
    return false;
  }

  // Validate the CVC:
  if (!Stripe.validateCVC(cvcNum)) {
    return false;
  }

  // Validate the expiration:
  if (!Stripe.validateExpiry(expMonth, expYear)) {
    return false;
  }

  return true;

};

function reportError(msg) {

  // Show the error in the form:
  alert(msg);

  return false;

}

var wpi_stripe_submit = function(){

  // Get the Stripe token:
  Stripe.createToken({
    number: ccNum,
    cvc: cvcNum,
    exp_month: expMonth,
    exp_year: expYear
  }, stripeResponseHandler);

  return false;

};

function stripeResponseHandler(status, response) {

  // Check for an error:
  if (response.error) {

    reportError(response.error.message);

  } else { // No errors, submit the form:

    var f = jQuery("#online_payment_form-wpi_stripe");

    // Token contains id, last4, and card type:
    var token = response['id'];

    // Insert the token into the form so it gets submitted to the server
    f.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

    jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
    jQuery( ".loader-img" ).show();
    var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
    var message = '';
    jQuery.post(url, jQuery("#online_payment_form-wpi_stripe").serialize(), function(d){
      if ( d.success ) {
        jQuery('#trans-results').css({
          background:"#EDFFDF"
        });
      } else if ( d.error ) {
        jQuery('#trans-results').css({
          background:"#FFDFDF"
        });
      }
      jQuery.each( d.data.messages, function(k, v){
        message += v +'\n\n';
      });
      alert( message );
      location.reload(true);
    }, 'json');
    return false;

  }

}

function wpi_stripe_init_form() {
  jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}