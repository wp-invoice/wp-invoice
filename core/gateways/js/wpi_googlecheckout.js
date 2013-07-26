var wpi_googlecheckout_rules = {
  "first_name": {
    required: true
  },
  "last_name": {
    required: true
  }
};

var wpi_googlecheckout_messages = {
  "first_name": {
    required: "First name is required."
  },
  "last_name": {
    required: "Last name is required."
  }
};

/* This function adds to form validation, and returns true or false */
var wpi_googlecheckout_validate_form = function(){
  /* Just return, no extra validation needed */
  return true;
};

var wpi_googlecheckout_submit = function(){

	jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
  jQuery( ".loader-img" ).show();
	var success = false;
  var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
	jQuery.ajaxSetup({
		async: false
	});
  jQuery.post(
		url,
		jQuery("#online_payment_form-wpi_googlecheckout").serialize(),
		function(msg){
			jQuery.ajaxSetup({
				async: true
			});
			if ( msg.success == 1 ) {
				success = true;
			}
		}, 'json');
  return success;

};

function wpi_googlecheckout_init_form() {
  jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}