/**
 * Unified Invoice Page Handler
 */
(function ( $ ) {

  $.fn.unified_page_template = function() {
    var that = this;

    this.payment_form_button = $('#open-payment-form');
    this.go_back_button = $('#close-payment-form');
    this.payment_form_container = $('#payment-form-container');
    this.invoice_data_container = $('#invoice-data-container');
    this.invoice_payment_success = $('#invoice-payment-success');
    this.invoice_page_content = $('#invoice-page-content');

    this.toggle_payment_form = function() {
      if ( !that.payment_form_container.is(':visible') ) {
        that.payment_form_container.show();
        that.invoice_data_container.hide();
        that.payment_form_button.hide();
        that.go_back_button.show().css('display','inline-block');
      } else {
        that.payment_form_container.hide();
        that.invoice_data_container.show();
        that.payment_form_button.show();
        that.go_back_button.hide();
      }
    };

    this.override_success_callback = function() {
      /**
       *
       * @returns {boolean}
       */
      window.wpi_paypal_pro_submit = function(){
        jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
        jQuery( ".loader-img" ).show();
        var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
        var message = '';
        jQuery.post(url, jQuery("#online_payment_form-wpi_paypal_pro").serialize(), function(d){
          if ( d.success ) {
            jQuery(document).trigger('wpi_payment_success');
          } else if ( d.error ) {
            jQuery.each( d.data.messages, function(k, v){
              message += v +'\n\n';
            });
            alert( message );
            location.reload(true);
          }
        }, 'json');
        return false;
      };

      /**
       *
       * @returns {boolean}
       */
      window.wpi_usa_epay_submit = function(){
        jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
        jQuery( ".loader-img" ).show();
        var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
        var message = '';
        jQuery.post(url, jQuery("#online_payment_form-wpi_usa_epay").serialize(), function(d){
          if ( d.success ) {
            jQuery(document).trigger('wpi_payment_success');
          } else if ( d.error ) {
            jQuery.each( d.data.messages, function(k, v){
              message += v +'\n\n';
            });
            alert( message );
            location.reload(true);
          }
        }, 'json');
        return false;
      };
    };

    this.payment_form_button.on( 'click', this.toggle_payment_form );
    this.go_back_button.on( 'click', this.toggle_payment_form );

    /**
     * Hack to fix layout a bit
     */
    $('ul.wpi_checkout_block').append('<li class="clearfix"></li>');
    $('.sigPad').append('<div class="clearfix"></div>');
    $('#credit_card_information').find('br.cb').remove();
    this.override_success_callback();
    $(document).on('wpi_payment_form_changed', function(){
      $('ul.wpi_checkout_block').append('<li class="clearfix"></li>');
      $('.sigPad').append('<div class="clearfix"></div>');
      $('#credit_card_information').find('br.cb').remove();
      that.override_success_callback();
    });

    $(document).on('wpi_payment_success', function(){
      that.invoice_payment_success.show();
      that.invoice_page_content.hide();
    });

    return this;
  };

}( jQuery ));

/**
 * Override existing functions to match unified page needs
 * @returns {boolean}
 */
var wpi_authorize_submit = function() {
  jQuery( "#cc_pay_button" ).attr("disabled", "disabled");
  jQuery( ".loader-img" ).show();
  var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
  var message = '';
  jQuery.post(url, jQuery("#online_payment_form-wpi_authorize").serialize(), function(d) {
    if ( d.success ) {
      jQuery(document).trigger('wpi_payment_success');
    } else if ( d.error ) {
      jQuery('#trans-results').css({background:"#FFDFDF"});
      jQuery.each( d.data.messages, function(k, v){
        message += v +'\n\n';
      });
      alert( message );
      location.reload(true);
    }
  }, 'json');
  return false;
};

/**
 * Override existing function to match unified page needs
 * @param status
 * @param response
 * @returns {boolean}
 */
function stripeResponseHandler(status, response) {
  if (response.error) {
    alert(response.error.message);
  } else {
    var f = jQuery("#online_payment_form-wpi_stripe");
    var token = response['id'];
    f.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
    var url = wpi_ajax.url+"?action="+jQuery("#wpi_action").val();
    var message = '';
    jQuery.post(url, jQuery("#online_payment_form-wpi_stripe").serialize(), function(d){
      if ( d.success ) {
        jQuery(document).trigger('wpi_payment_success');
      } else if ( d.error ) {
        jQuery.each( d.data.messages, function(k, v){
          message += v +'\n\n';
        });
        alert( message );
        location.reload(true);
      }
    }, 'json');
    return false;
  }
}