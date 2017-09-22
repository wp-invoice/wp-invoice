<?php
/** Set our header */
header("Content-type: text/javascript");
/** For each type of plugin we have, lets load the JavaScript file that's associated with it */
if ($dir = opendir(getcwd())) {
  while (false !== ($file = readdir($dir))) {
    $exploded = explode(".", $file);
    
    if( end($exploded) == 'js' && !strstr($exploded[0], '_checkout') ) {
      $f = file_get_contents($file);
      print $f;
    }
  }
}
?>

//Handle our payment type selection item
var changeFunction = function() {
  var data = {
    action: 'wpi_front_change_payment_form_ajax',
    type: jQuery("option:selected", this).val(),
    invoice_id: jQuery("#wpi_form_invoice_id").val()
  }
  //Remove the entire form (also includes all attached events)
  jQuery(".online_payment_form").remove();
  //Reload the div with our new content
  jQuery("#online_payment_form_wrapper").load(wpi_ajax.url, data, function(d){
    //Hide the errors
    jQuery("#wpi_gateway_form_errors").html("").hide();
    //Go ahead and re-init the form
    wpi_init_form();
  });
}

jQuery(document).ready(function(){
  //Init the Form!
  wpi_init_form();
  //jQuery("#wp_invoice_select_payment_method_selector").ready(changeFunction);
  jQuery("#wp_invoice_select_payment_method_selector").change(changeFunction);
});

// This function calls reattachs our validation rules
function wpi_init_form(){
  var type = jQuery("#wpi_form_type").val();
  if ( typeof type == 'undefined' ) return false;
  var type_messages = eval(type + '_messages');
  var type_rules = eval(type + '_rules');
  jQuery(".online_payment_form").validate({
    messages: type_messages,
    rules: type_rules,
    errorLabelContainer: "#wpi_gateway_form_errors",
    wrapper: "li",
    errorClass: "wpi_error",
    showErrors: function(errorMap, errorList) {
      this.defaultShowErrors();
      /* Hack: adds 'ul' container for errors list */
      if(!jQuery('#wpi_gateway_form_errors').children('ul').length > 0) {
        var children = jQuery('#wpi_gateway_form_errors').children();
        jQuery('<ul></ul>').appendTo('#wpi_gateway_form_errors');
        children.each(function(i,e){
          jQuery(e).appendTo('#wpi_gateway_form_errors ul');
        });
      }
    }
  });
  eval(type + '_init_form();');
  //Attach our validation function
  jQuery(".online_payment_form").submit(function(e){
    if(jQuery(this).valid()){
      //We have a valid form, run our form specific validation!
      eval('var wpi_validates = ' + type + '_validate_form();');
      if(wpi_validates){
        //We validated again, run our form specific handler, and
        //return the value to determine if we do a full page submit
        eval('var wpi_submit = ' + type + '_submit();');
        return wpi_submit;
      }else{
        return false;
      }
    }else{
      return false;
    }
  });

  jQuery(document).trigger('wpi_payment_form_changed');
}