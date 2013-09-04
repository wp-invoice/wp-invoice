/**
 * WPI Authorize.net. Single Page Checkout
 *
 * @author korotkov@UD
 * @author peshkov@UD
 */

jQuery( document ).on( 'wpi_checkout_init-wpi_authorize', function( event, self ) {

  if( typeof self.params.available_gateways.wpi_authorize === 'undefined' ) {
    return false;
  }

  var i = self.params.available_gateways.wpi_authorize.data;
  i = jQuery.extend( true, {
    ajaxurl: '',
    time: '',
    strings: {
      processing : 'Processing ...',
      process_payment : 'Process Payment',
      hacking : 'Hacking?'
    }
  }, i );

  /**
   * Each payment form has it's own 'venue' object which stores required data.
   * It used for multiple forms.
   */
  self.gateways.wpi_authorize = {
    amount:0,
    wpi_checkout_total:0,
    extended_validation_passed:true,
    submit_function: function() {
      self.update_checkboxes( self.gateways.wpi_authorize );
      var wpi_checkout_form = this;
      // if venue has null amount
      if( self.gateways.wpi_authorize.wpi_checkout_total == 0 ){
        return false;
      }
      // disable UI
      jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).attr("disabled", "disabled");
      jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).val( i.strings.processing );
      // send post data
      var data = jQuery(this).serialize();
      jQuery.post( i.ajaxurl + '?wpi_timestamp=' + i.time, {
        action: 'wpi_checkout_process',
        data: data
        }, function(result) {
            jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).val( i.strings.process_payment );
            if( result.payment_status == 'validation_fail') {
              jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).removeAttr('disabled');
              jQuery.each(result.missing_data, function(field, message) {
                //** Trigger added to be able to hook it to add some custom stuff @author korotkov@UD */
                jQuery(document).trigger('wpi_spc_validation_fail', [field, wpi_checkout_form, result]);
                jQuery('.wpi_checkout_payment_' + field + '_input', wpi_checkout_form).addClass('wpi_checkout_input_error');
                jQuery('.wpi_checkout_row_' + field + ' span.validation', wpi_checkout_form).show();
                jQuery('.wpi_checkout_row_' + field + ' span.validation', wpi_checkout_form).html(message);
              });
              jQuery('input.text-input.wpi_checkout_input_error:first', wpi_checkout_form).focus();
            } else if(result.payment_status == 'success') {
              //** Trigger added to be able to hook it to add some custom stuff @author korotkov@UD */
              jQuery(document).trigger('wpi_spc_success', [result, wpi_checkout_form, 'wpi_authorize']);
              jQuery('.wpi_checkout_process_payment, .btn.btn-success', wpi_checkout_form).hide();
              jQuery('.total_price', wpi_checkout_form).hide();
              jQuery('.wpi_checkout_block.wpi_checkout_billing_information', wpi_checkout_form).remove();
              jQuery('.wpi_checkout_block.wpi_checkout_billing_address', wpi_checkout_form).remove();
              jQuery('.wpi_checkout_payment_response', wpi_checkout_form).show();
              jQuery('.wpi_checkout_payment_response', wpi_checkout_form).html(result.message);
              return;
            } else if(result.payment_status == 'processing_failure') {
              //** Trigger added to be able to hook it to add some custom stuff @author korotkov@UD */
              jQuery(document).trigger('wpi_spc_processing_failure', [result, wpi_checkout_form]);
              jQuery('.wpi_checkout_payment_response', wpi_checkout_form).show();
              jQuery('.wpi_checkout_payment_response', wpi_checkout_form).html(result.message);
              jQuery('.wpi_checkout_submit_btn', wpi_checkout_form).removeAttr('disabled');
            } else if(result.payment_status == 'hacking_attempt') {
              alert( i.strings.hacking );
            }
        }, 'json');
      return false;
    }

  }

  /**
   * Update current checkboxes on ready
   */
  if( jQuery( '.wpi_checkout_payment_amount_input', self.instance ).length > 0 ){
    self.gateways.wpi_authorize.amount = parseFloat(jQuery('.wpi_checkout_payment_amount_input', self.instance ).val());
    self.update_checkboxes( self.gateways.wpi_authorize );
  }

  /**
   * Handle payment venue switching
   */
  jQuery('.wpi_checkout_select_payment_method_dropdown', self.instance ).change( self.change_payment_method ).change();

  /**
   * Handle custom amount typing
   */
  jQuery('.wpi_checkout_payment_amount_input', self.instance ).keyup( function(){
    self.gateways.wpi_authorize.amount = parseFloat(jQuery(this).val());
    self.update_checkboxes( self.gateways.wpi_authorize );
  });

  /**
   * Handle items toggling
   */
  jQuery('.wpi_checkout_toggle_item').change( function() {
    self.update_checkboxes( self.gateways.wpi_authorize );
  });

  /**
   * Handle form submitting
   */
  if( typeof jQuery.fn.form_helper == 'function' ) {
    jQuery('form.wpi_authorize', self.instance ).bind( 'form_helper::success', function(){
      jQuery(this).unbind().submit( self.gateways.wpi_authorize.submit_function ).submit();
    });
  } else {
    jQuery('form.wpi_authorize', self.instance ).submit( self.gateways.wpi_authorize.submit_function );
  }

} );