/* Our Rules for this type of form */
var wpi_interkassa_rules = {
    "first_name": {
        required: true
    },
    "last_name": {
        required: true
    }
};

/* Our Messages for this type of form */
var wpi_interkassa_messages = {
    "first_name": {
        required: "First name is required."
    },
    "last_name": {
        required: "Last name is required."
    }
};

/* This function adds to form validation, and returns true or false */
var wpi_interkassa_validate_form = function(){
    /* Just return, no extra validation needed */
    return true;
};

/* This function handles the submit event */
var wpi_interkassa_submit = function(){

    jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
    jQuery( ".loader-img" ).show();
    var success = false;
    var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
    jQuery.ajaxSetup({
        async: false
    });
    jQuery.post(
        url,
        jQuery("#online_payment_form-wpi_interkassa").serialize(),
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

function wpi_interkassa_init_form() {
    jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}