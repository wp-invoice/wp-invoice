/**
 * WPI Paypal. Single Page Checkout
 *
 * @author korotkov@UD
 * @author peshkov@UD
 */

jQuery( document ).on( 'wpi_checkout_init-wpi_paypal', function( event, self ) {

  if( typeof self.params.available_gateways.wpi_paypal === 'undefined' ) {
    return false;
  }

  var i = self.params.available_gateways.wpi_paypal.data;
  i = jQuery.extend( true, {
    ajaxurl: '',
    time: '',
    strings: {
      processing : 'Processing ...',
      process_payment : 'Process Payment',
      redirecting_to_paypal: 'Redirecting to PayPal. Wait please...'
    }
  }, i );

  /**
   * Each payment form has it's own 'venue' object which stores required data.
   * It used for multiple forms.
   */
  self.gateways.wpi_paypal = {
    amount:0,
    wpi_checkout_total:0,
    context: self.instance,
    extended_validation_passed:true,
    submit_function : function(){
      self.update_checkboxes( self.gateways.wpi_paypal );
      // current form
      var wpi_checkout_form = this;
      /**
       * Return 'false' to prevent posting data to PayPal if error occured, and 'true' to send data on success
       */
      var success = false;
      /**
       * Handle custom amount
       */
      if ( jQuery('.wpi_checkout_hidden_custom_amount', wpi_checkout_form).length ) {
        if ( parseFloat( jQuery('.wpi_checkout_hidden_custom_amount', wpi_checkout_form).val() ) == 0 ) {
          jQuery('.wpi_checkout_payment_amount_input', wpi_checkout_form).addClass('wpi_checkout_input_error');
          return false;
        }
      }

      // if venue has null amount, return false
      if( self.gateways.wpi_paypal.wpi_checkout_total == 0){
        return false;
      }

      // disable UI
      jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).attr("disabled", "disabled");
      jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).val( i.strings.processing );
      // send post data
      var data = jQuery( wpi_checkout_form ).serialize();
      jQuery.ajaxSetup({
        async: false
      });
      jQuery.post( i.ajaxurl + '?wpi_timestamp=' + i.time, {
        action: 'wpi_checkout_process',
        data: data
        },
        function( result ) {
          jQuery.ajaxSetup({
            async: true
          });
          if ( result.payment_status == 'validation_fail' ) {
            jQuery.each(result.missing_data, function(field, message) {
              //** Trigger added to be able to hook it to add some custom stuff @author korotkov@UD */
              jQuery(document).trigger('wpi_spc_validation_fail', [field, wpi_checkout_form, result]);
              jQuery('.wpi_checkout_payment_' + field + '_input', wpi_checkout_form).addClass('wpi_checkout_input_error');
              jQuery('.wpi_checkout_row_' + field + ' span.validation', wpi_checkout_form).show();
              jQuery('.wpi_checkout_row_' + field + ' span.validation', wpi_checkout_form).html(message);
            });
            jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).removeAttr('disabled');
            jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).val( i.strings.process_payment );
          } else if ( result.payment_status == 'processing_failure' ) {
            //** Trigger added to be able to hook it to add some custom stuff @author korotkov@UD */
            jQuery(document).trigger('wpi_spc_processing_failure', [result, wpi_checkout_form]);
            jQuery('.wpi_checkout_payment_response', wpi_checkout_form).show();
            jQuery('.wpi_checkout_payment_response', wpi_checkout_form).html(result.message);
            jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).removeAttr('disabled');
            jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).val( i.strings.process_payment );
          } else if ( result.payment_status == 'success' && result.invoice_id ) {
            //** Trigger added to be able to hook it to add some custom stuff @author korotkov@UD */
            result.message = i.strings.redirecting_to_paypal;
            jQuery(document).trigger('wpi_spc_success', [result, wpi_checkout_form, 'wpi_paypal']);
            jQuery('.wpi_chechout_invoice_id', wpi_checkout_form).val( result.invoice_id );
            jQuery('.wpi_checkout_payment_response', wpi_checkout_form).show();
            jQuery('.wpi_checkout_payment_response', wpi_checkout_form).html( i.strings.redirecting_to_paypal );
            success = true;
          }
        }, 'json');
      return success;
    }
  };

  /**
   * Update current checkboxes on ready
   */
  if( jQuery( '.wpi_checkout_payment_amount_input', self.instance ).length > 0 ){
    self.gateways.wpi_paypal.amount = parseFloat( jQuery('.wpi_checkout_payment_amount_input', self.instance ).val() );
    self.update_checkboxes( self.gateways.wpi_paypal );
  }

  /**
   * Handle items toggling
   */
  jQuery( '.wpi_checkout_toggle_item', self.instance ).change( function() {
    self.update_checkboxes( self.gateways.wpi_paypal );
  });

  /**
   * Handle payment venue switching
   */
  jQuery('.wpi_checkout_select_payment_method_dropdown', self.instance ).change( self.change_payment_method ).change();

  /**
   * Handle custom amount typing
   */
  jQuery('.wpi_checkout_payment_amount_input', self.instance ).keyup(function(){
    self.gateways.wpi_paypal.amount = parseFloat(jQuery(this).val());
    self.update_checkboxes( self.gateways.wpi_paypal );
  });

  /**
   * Display fee
   */
  if ( jQuery('.wpi_checkout_fee.wpi_paypal', self.instance ).length ) {
    jQuery('.wpi_fee_amount.wpi_paypal', self.instance ).html(' ('+ jQuery('.wpi_checkout_fee.wpi_paypal', self.instance ).val() +'% fee)');
  }

  /**
   * Handle Custom Amount changing
   */
  jQuery('.wpi_checkout_payment_amount_input', self.instance ).change(function(){
    var amount = parseFloat( jQuery(this).val() );
    var fee    = parseFloat( jQuery(this).val()/100*jQuery('.wpi_checkout_fee.wpi_paypal', self.instance ).val() );
    fee = isNaN( fee ) ? 0 : fee;
    var total  = amount + fee;
    jQuery('.wpi_checkout_hidden_custom_amount', self.instance ).val( jQuery().number_format( isNaN( amount )?0:amount ) );
    jQuery('.wpi_checkout_hidden_fee', self.instance ).val( jQuery().number_format( isNaN( fee )?0:fee ) );
    jQuery(this).val( isNaN( amount )?0:amount );
    jQuery('.wpi_price.wpi_paypal', self.instance ).html( jQuery().number_format( isNaN( total )?0:total ) );
  });
  jQuery('.wpi_checkout_payment_amount_input', self.instance ).trigger('change');

  /**
   * Handle form submitting
   */
  if( typeof jQuery.fn.form_helper == 'function' ) {
    jQuery('form.wpi_paypal', self.instance ).bind('form_helper::success', function(){
      jQuery(this).unbind().submit( self.gateways.wpi_paypal.submit_function).submit();
    });
  } else {
    jQuery('form.wpi_paypal', self.instance ).submit( self.gateways.wpi_paypal.submit_function);
  }

} );