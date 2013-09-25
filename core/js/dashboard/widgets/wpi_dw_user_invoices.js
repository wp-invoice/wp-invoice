jQuery(document).ready(function(){

  var widget_container = jQuery('#wpi_dw_user_invoices .inside');

  widget_container.load( ajaxurl, {
    action: 'wpi_dw_user_invoices'
  } );

});