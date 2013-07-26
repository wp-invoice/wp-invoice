/**
 * Styles specific to the invoice editing page.
 *
 * since 3.0
 *
 */
jQuery(document).ready(function() {

  if (jQuery('.wrap.wpi_invoice_status_paid').length ) {
    jQuery('select,textarea,input', jQuery('.wpi_invoice_status_paid')).attr('disabled', true);
    jQuery('select,textarea,input', jQuery('#wpi_enter_payments')).attr('disabled', false);
    jQuery('.button.add-new-h2').attr('disabled', false);
  }

  jQuery(".wpi_user_email_selection").change(function() {

    //** Clear out current values just in case */
    jQuery('.wp_invoice_new_user input').val('');

    jQuery.post(ajaxurl, {
      action: 'wpi_get_user_date',
      user_email: jQuery(this).val()
    }, function(result) {
      if ( result ) {
        user_data = result.user_data;

        for (var field in user_data) {
          jQuery('.wp_invoice_new_user .wpi_' + field).val(user_data[field]);

        }
      }
    }, 'json');

  });

  jQuery('.wpi_toggle_advanced_payment_options').click(function() {
    jQuery('tr.wpi_advanced_payment_options').toggle();
  });

});