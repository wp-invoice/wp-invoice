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

/**
 * This function adds to form validation, and returns true or false
 */
var wpi_stripe_validate_form = function(){

  //** Get the values */
  var ccNum = jQuery('.card-number').val(),
  cvcNum = jQuery('.card-cvc').val(),
  expMonth = jQuery('.card-expiry-month').val(),
  expYear = jQuery('.card-expiry-year').val();

  jQuery('.card-number').removeClass('wpi_error');
  jQuery('.card-cvc').removeClass('wpi_error');
  jQuery('.card-expiry-month').removeClass('wpi_error'),
  jQuery('.card-expiry-year').removeClass('wpi_error');

  //** Validate the number */
  if (!Stripe.validateCardNumber(ccNum)) {
    jQuery('.card-number').addClass('wpi_error');
    return false;
  }

  //** Validate the CVC */
  if (!Stripe.validateCVC(cvcNum)) {
    jQuery('.card-cvc').addClass('wpi_error');
    return false;
  }

  //** Validate the expiration */
  if (!Stripe.validateExpiry(expMonth, expYear)) {
    jQuery('.card-expiry-month').addClass('wpi_error'),
    jQuery('.card-expiry-year').addClass('wpi_error');
    return false;
  }

  return true;

};

/**
 * Form submit handler
 */
var wpi_stripe_submit = function(){

  jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
  jQuery( ".loader-img" ).show();

  //** Get the values */
  var ccNum = jQuery('.card-number').val(),
  cvcNum = jQuery('.card-cvc').val(),
  expMonth = jQuery('.card-expiry-month').val(),
  expYear = jQuery('.card-expiry-year').val();

  //** Get the Stripe token */
  try {
    Stripe.createToken({
      number: ccNum,
      cvc: cvcNum,
      exp_month: expMonth,
      exp_year: expYear
    }, stripeResponseHandler);
  } catch ( e ) {
    alert( e );
    location.reload(true);
  }

  return false;

};

/**
 * STRIPE response handler
 */
function stripeResponseHandler(status, response) {

  //** Check for an error */
  if (response.error) {
    alert(response.error.message);
  } else {

    //** No errors, submit the form */
    var f = jQuery("#online_payment_form-wpi_stripe");

    //** Token contains id, last4, and card type */
    var token = response['id'];

    //** Insert the token into the form so it gets submitted to the server */
    f.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

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

/**
 * Form init
 */
function wpi_stripe_init_form() {
  jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}