/* Our Rules for this type of form */
var wpi_paypal_rules = {
    "first_name": {
        required: true
    },
    "last_name": {
        required: true
    },
    "email_address": {
        required: true
    },
    "night_phone_a": {
        required: true
    },
    "night_phone_b": {
        required: true
    },
    "night_phone_c": {
        required: true
    },
    "address1": {
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
    },
    "country": {
        required: true
    }
};

/* Our Messages for this type of form */
var wpi_paypal_messages = {
    "first_name": {
        required: "First name is required."
    },
    "last_name": {
        required: "Last name is required."
    },
    "email_address": {                            
        required: "email_address is required."
    },                                     
    "night_phone_a": {                            
        required: "Phone number(a) is required"
    },                                     
    "night_phone_b": {                            
        required: "Phone number(b) is required"
    },                                     
    "night_phone_c": {                            
        required: "Phone number(c) is required"
    },                                     
    "address1": {                            
        required: "Address required"
    },                                     
    "city": {                            
        required: "City is required"
    },                                     
    "state": {                            
        required: "State is required"
    },                                     
    "zip": {                            
        required: "Zip is required"
    },                                     
    "country": {                            
        required: "Country is required"
    }                                     
};
/* This function adds to form validation, and returns true or false */
var wpi_paypal_validate_form = function(){
    /* Just return, no extra validation needed */
    return true;
};

/* This function handles the submit event */
var wpi_paypal_submit = function(){

    jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
    jQuery( ".loader-img" ).show();
    var success = false;
    var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
    jQuery.ajaxSetup({
        async: false
    });
    jQuery.post(
        url,
        jQuery("#online_payment_form-wpi_paypal").serialize(),
        function(msg){
            jQuery.ajaxSetup({
                async: true
            });
            if ( msg.success == 1 ) {
                success = true;
            } else if ( msg.error == 1 ) {
              var message = '';
              jQuery.each( msg.data.messages, function(k, v){
                message += v +'\n\n';
              });
              alert( message );
              location.reload(true);
            }
        }, 'json');
    return success;

};

function wpi_paypal_init_form() {
    jQuery("#online_payment_form_wrapper").trigger('formLoaded');
}