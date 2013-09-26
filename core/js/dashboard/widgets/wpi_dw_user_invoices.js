jQuery(document).ready(function(){

  var widget_container = jQuery('#wpi_dw_user_invoices .inside');

  //** Load widget content */
  widget_container.load( ajaxurl, {
    action: 'wpi_dw_user_invoices'
  } );

  //** Toggle invoice lists */
  jQuery('.toggler', widget_container).live('click', function(){
    jQuery( this ).parents('tr').next().toggle();
  });

});