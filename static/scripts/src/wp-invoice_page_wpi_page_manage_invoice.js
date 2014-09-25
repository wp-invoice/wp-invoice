/**
 * Styles specific to the invoice editing page.
 *
 * since 3.0
 *
 */
jQuery( document ).ready( function () {

  if ( jQuery( '.wrap.wpi_invoice_status_paid' ).length ) {
    jQuery( 'select,textarea,input', jQuery( '.wpi_invoice_status_paid' ) ).attr( 'disabled', true );
    jQuery( 'select,textarea,input', jQuery( '#wpi_enter_payments' ) ).attr( 'disabled', false );
    jQuery( 'select,textarea,input', jQuery( '#send_notification_box' ) ).attr( 'disabled', false );
    jQuery( '.button.add-new-h2' ).attr( 'disabled', false );
  }

  jQuery( ".wpi_user_email_selection" ).change( function () {

    //** Clear out current values just in case */
    jQuery( '.wp_invoice_new_user input' ).val( '' );

    jQuery.post( ajaxurl, {
      action: 'wpi_get_user_date',
      user_email: jQuery( this ).val()
    }, function ( result ) {
      if ( result ) {
        user_data = result.user_data;

        for ( var field in user_data ) {
          jQuery( '.wp_invoice_new_user .wpi_' + field ).val( user_data[field] );

        }
      }
    }, 'json' );

  } );

  jQuery( '.wpi_toggle_advanced_payment_options' ).click( function () {
    jQuery( 'tr.wpi_advanced_payment_options' ).toggle();
    jQuery( '.wp_invoice_accordion' ).accordion( 'refresh' );
  } );

  var enable_manual_payment = function() {
    jQuery( '#wp_invoice_payment_method' )
      .removeAttr('name')
      .attr('disabled', 'disabled');
    if ( jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).is(":checked") )
      jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).click();
    jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).parent().hide();
  };

  var disable_manual_payment = function() {
    jQuery( '#wp_invoice_payment_method' )
      .attr('name', jQuery( '#wp_invoice_payment_method' ).data('name'))
      .removeAttr('disabled');
    jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).parent().show();
  };

  if ( jQuery('.wpi_use_manual_payment').is(':checked') ) {
    enable_manual_payment();
  } else {
    disable_manual_payment();
  }

  jQuery( '.wpi_use_manual_payment' ).click(function() {
    if ( jQuery(this).is(':checked') ) {
      enable_manual_payment();
    } else {
      disable_manual_payment();
    }
  });

} );