/**
 * Our Rules for this type of form
 * @type type
 */
var wpi_payoneer_rules = {
    "first_name": {
        required: true
    },
    "last_name": {
        required: true
    }
};

/**
 * Our Messages for this type of form
 * @type type
 */
var wpi_payoneer_messages = {
    "first_name": {
        required: "First name is required."
    },
    "last_name": {
        required: "Last name is required."
    }
};

/**
 * This function adds to form validation, and returns true or false
 * @returns {Boolean}
 */
var wpi_payoneer_validate_form = function(){
    //** Just return, no extra validation needed */
    return true;
};

/**
 * This function handles the submit event
 * @returns {Boolean}
 */
var wpi_payoneer_submit = function(){

    jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
    jQuery( "#cc_pay_button" ).hide();
    jQuery( ".loader-img" ).show();
    var success = false;
    var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
    jQuery.ajaxSetup({
        async: false
    });
    jQuery.post(
        url,
        jQuery("#online_payment_form-wpi_payoneer").serialize(),
        function(msg){
            jQuery.ajaxSetup({
                async: true
            });
            if ( msg.success == 1 ) {
              jQuery( ".loader-img" ).hide();
              jQuery( ".payment_details" ).show();
            }
        }, 'json');
    return false;
};

/**
 * 
 * @returns {undefined}
 */
function wpi_payoneer_init_form() {
    jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}