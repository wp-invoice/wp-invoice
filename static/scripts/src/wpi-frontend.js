/**
 * -
 *
 * -
 *
 */
function process_cc_checkout () {
  /*jQuery('#wp_invoice_process_wait span').html('<img src="'+ajax_image+'">');*/
  link_id = 'wp_cc_response';
  var req = jQuery.post( site_url, jQuery( '#checkout_form' ).serialize(), function ( html ) {
      var explode = html.toString().split( '\n' );
      var shown = false;
      var msg = '<b>There are problems with your transaction:</b><ol>';
      for ( var i in explode ) {
        var explode_again = explode[i].toString().split( '|' );
        if ( explode_again[0] == 'error' ) {
          if ( !shown ) {
            jQuery( '#' + link_id ).fadeIn( "slow" );
          }
          shown = true;
          add_remove_class( 'ok', 'error', explode_again[1] );
          /*jQuery('#err_' + explode_again[1]).html(explode_again[2]); */
          msg += "<li>" + explode_again[2] + "</li>";
        } else if ( explode_again[0] == 'ok' ) {
          add_remove_class( 'error', 'ok', explode_again[1] );
          /*jQuery('#err_' + explode_again[1]).hide(); */
        }
      }
      if ( !shown ) {
        if ( html == 'Transaction okay.' ) {
          jQuery( '.online_payment_form' ).fadeOut( "slow" );
          jQuery( '#wp_cc_response' ).fadeIn( "slow" );
          jQuery( '#wp_cc_response' ).html( "<?php _e('Thank you! <br />Payment processed successfully!', WP_INVOICE_TRANS_DOMAIN); ?>" );
          jQuery( "#credit_card_information" ).hide();
          jQuery( "#welcome_message" ).html( 'Invoice Paid!' );
          jQuery( '#' + link_id ).show();
        }
      } else {
        add_remove_class( 'success', 'error', link_id );
        jQuery( '#' + link_id ).html( msg + "</ol>" );
      }
      jQuery( '#wp_invoice_process_wait span' ).html( '' );
      req = null;
    } );
}

/**
 * -
 *
 * -
 *
 * @param search
 * @param replace
 * @param element_id
 */
function add_remove_class ( search, replace, element_id ) {
  if ( jQuery( '#' + element_id ).hasClass( search ) ) {
    jQuery( '#' + element_id ).removeClass( search );
  }
  jQuery( '#' + element_id ).addClass( replace );
}
