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

    this.payment_form_button.on( 'click', this.toggle_payment_form );
    this.go_back_button.on( 'click', this.toggle_payment_form );

    /**
     * Hack to fix layout a bit
     */
    $('ul.wpi_checkout_block').append('<li class="clearfix"></li>');

    return this;
  };

}( jQuery ));