/**
 * Main application scripts
 */

jQuery(document).ready(function(){
  jQuery('.sigPad').signaturePad({drawOnly:true,lineTop:120});

  jQuery("#online_payment_form_wrapper").on('formLoaded', function(){
    jQuery('.sigPad').signaturePad({drawOnly:true,lineTop:120});
  });
});