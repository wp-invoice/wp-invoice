/*
  This file handles WPI events.
  All the functions are in the wpi-functions.js file.
  Some events that are very short (2-4 lines) may not have functions.
*/
jQuery.noConflict();

jQuery(document).ready(function(){

  /* Cycle through all advanced UI options and toggle them */
  jQuery(".wpi_show_advanced").each(function() {
    wpi_toggle_advanced_options(this);
  });

  /* Enable monitoring of toggling of advanced UI options */
  jQuery(".wpi_show_advanced").live("click", function() {
    wpi_toggle_advanced_options(this);
  });

  //* Hide WPI legacy import nag */
  jQuery('.wpi_hide_import_nag').live('click', function() {

    var message_holder = jQuery(this).parents('.wpi_message_holder');
    var import_type = jQuery(this).attr('import_type');

    jQuery(jQuery(this).parents('.wpi_message_holder')).val('Please wait...');

    jQuery.post(ajaxurl, {
      action: 'wpi_update_wpi_option',
      value: 'true',
      option: import_type,
      group: 'disable_legacy_import_nag'
    }, function(response) {

      if(response.success == 'true') {
        jQuery(message_holder).hide();
        wpi_message_stack_check();
      }

    }, 'json');


  });


  // Add row to UD UI Dynamic Table
  jQuery(".wpi_add_row").live("click" , function() {
    wpi_add_row(this);
  });

  // When the .slug_setter input field is modified, we update names of other elements in row
  jQuery(".wpi_dynamic_table_row[new_row=true] input.slug_setter").live("change", function() {
    //console.log('Name changed.');
    wpi_updateRowNames(this);
    /*
    jQuery('.wpi_width input', this_row).attr("name", "wpi_settings[image_sizes][" + new_slug + "][width]");
    jQuery('.wpi_height input', this_row).attr("name", "wpi_settings[image_sizes][" + new_slug + "][height]");
    */
  });

  jQuery("#currency-list .wpi_dynamic_table_row[new_row=true] input.names_changer").live("change", function() {


    var this_row = jQuery(this).parents('tr.wpi_dynamic_table_row');
  // Slug of row in question
    var old_slug = jQuery(this_row).attr('slug');
    var new_slug = jQuery(this).val();

    // Don't allow to blank out slugs
    if(new_slug == "") {
      return;
    }

    // If slug input.slug exists in row, we modify it
    jQuery(".slug" , this_row).val(new_slug);
    // Update row slug
    jQuery(this_row).attr('slug', new_slug);

    // Cycle through all child elements and fix names
    jQuery('input,select,textarea', this_row).each(function(element) {
      var old_name = jQuery(this).attr('name');
      if (typeof old_name!='undefined'){
        var new_name =  old_name.replace(old_slug,new_slug);
        jQuery(this).attr('name', new_name);
      }
      var old_id = jQuery(this).attr('id');
      if (typeof old_id !='undefined'){
        var new_id =  old_id.replace(old_slug,new_slug);
        jQuery(this).attr('id', new_id);
      }
    });

    // Cycle through labels too
    jQuery('label', this_row).each(function(element) {
      var old_for = jQuery(this).attr('for');
      if(typeof old_for!='undefined'){
        var new_for =  old_for.replace(old_slug,new_slug);
        jQuery(this).attr('for', new_for);
      }
    });

  });



  /** remove html5 check for reqirements and make it manualy on submit */
  jQuery("#currency-list :input[required]").each( function() {
    jQuery( this ).removeAttr( 'required' ).attr( 'validation_required', true );
  });


  jQuery("#minor-publishing table.form-table").find('tbody').toggle();
  jQuery("#wpi_button_show_advanced").live("click", function(){
    jQuery(this).parents("#minor-publishing table.form-table").find('tbody').toggle();
  });


  // Delete dynamic row
  jQuery(".wpi_delete_row").live("click", function() {

    var parent = jQuery(this).parents('tr.wpi_dynamic_table_row');
    var row_count = jQuery(".wpi_delete_row:visible").length;

    if(jQuery(this).attr('verify_action') == 'true') {
      if(!confirm('Are you sure?'))
        return false;
    }


    // Blank out all values
    jQuery("input[type=text]", parent).val('');
    jQuery("input[type=checkbox]", parent).attr('checked', false);

    // Don't hide last row
    if(row_count > 1) {
      jQuery(parent).hide();
      jQuery(parent).remove();
    }
  });


  // -- Global Event Handles -- //
  var first_time_setup_accordion = jQuery("#first_time_setup_accordion").accordion({header: "h3",   animated: false,  autoHeight: false ,  icons: {'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus'}});

  var wpi_payment_accordion = jQuery(".wp_invoice_accordion").accordion({
  header: "h3",
  animated: false,
   autoHeight: false,
  icons: {
    'header': 'ui-icon-plus',
    'headerSelected':
    'ui-icon-minus'
    }
  });

  wpi_init_payment_method();
  jQuery(".ui-state-error").dblclick(function() {jQuery(this).remove();});
  jQuery(".wp_invoice_qc_report").dblclick(function(){jQuery(this).remove();});
  jQuery('#contextual-help-link-wrap, #screen-options-link-wrap, #screen-functions-link-wrap').show();
  tooltip();

  // -- Invoice Page Event Handlers -- //

  /*
   * Allow Watermark
   */
  jQuery("#wpi_invoice_show_watermark").live("click",function(event) {
    if(jQuery(this).is(":checked")) {
      jQuery(".wpi_watermark_settings").show();
    } else {
      jQuery(".wpi_watermark_settings").hide();
      jQuery(".wpi_watermark_settings input").val("");
    }
  });

  //If Deposit is allowed for the current invoice, we show/hide additional settings
  if(jQuery("#wpi_wpi_invoice_deposit_").is(":checked")) {
    wpi_enable_deposit();
  }
  // If Recurring is allowed for the current invoice, we show/hide additional settings
  else if (jQuery("#wpi_wpi_invoice_meta_recurring_active_").is(":checked")) {
    wpi_enable_recurring();
  }
  // If Quote is allowed for the current invoice, we show/hide quote option
  else if (jQuery("#wpi_wpi_invoice_quote_").is(":checked")) {
    wpi_enable_quote();
    wpi_hide_deposit_option();
    wpi_hide_recurring_option();
  }

  /*
   * Toggle invoice deposit options
   */
  jQuery("#wpi_wpi_invoice_deposit_").live("click",function(event) {
    if(jQuery(this).is(":checked")) {
      wpi_enable_deposit();
    } else {
      wpi_disable_deposit();
    }
  });


  if(jQuery(".wpi_wpi_invoice_recurring_active_").is(":checked")) {
      wpi_enable_recurring();
  } else {
      // Singular invoice or quote. Clear out all values.
      wpi_disable_recurring();
  }

  if(jQuery(".wpi_wpi_invoice_recurring_send_invoice_automatically_").is(":checked")) {
      wpi_disable_recurring_start_date();
    } else {
      // Singular invoice or quote. Clear out all values.
      wpi_enable_recurring_start_date();
  }
  /*
   * Toggle recurring billing options
   */
  jQuery(".wpi_wpi_invoice_recurring_active_").live("click",function(event) {
    if(jQuery(this).is(":checked")) {
      wpi_enable_recurring();
    } else {
      // Singular invoice or quote. Clear out all values.
      wpi_disable_recurring();

      /*
       * This functions was moved from wpi_disable_recurring() (wpi-functions.js)
       */
      wpi_show_deposit_option();
      wpi_show_quote_option();
    }
  });
    /*
   * Toggle recurring billing start date
   */
  jQuery(".wpi_wpi_invoice_recurring_send_invoice_automatically_").live("click",function(event) {
    if(jQuery(this).is(":checked")) {
      wpi_disable_recurring_start_date();
    } else {
      // Singular invoice or quote. Clear out all values.
      wpi_enable_recurring_start_date();
    }
  });

  wpi_validate_recurring_units( ".wpi_bill_every_length" );
  jQuery("#wpi_wpi_invoice_recurring_unit_").live("change", function(e){
    wpi_validate_recurring_units( ".wpi_bill_every_length" );
  });
  jQuery(".wpi_bill_every_length").live("change", function(e){
    wpi_validate_recurring_units( e.target );
  });

  /*
   * Turn off certain options when it quote mode
   */
  jQuery(".wpi_wpi_invoice_quote_").click(function() {
    if(jQuery(this).is(":checked")) {
      wpi_disable_recurring();
      wpi_disable_deposit();
      wpi_hide_deposit_option();
      jQuery('.wpi_turn_off_recurring').hide();
    } else {
      wpi_show_deposit_option();
      jQuery('.wpi_turn_off_recurring').show();
    }
  });

/*
  Prevent differnet types of discounts
  Disables button if value changed to percent
  This function runs twice for some reason.
*/
/*
jQuery(".wp_invoice_discount_row .item_type").live('change', function(event) {
    var mismatch;
    var percent_active;
    if(jQuery(this).val() == 'percent') {
       jQuery(".wp_invoice_discount_row:visible").each(function() {
        if(jQuery('option:selected', this).val() == 'amount') {
          mismatch = true;
         } else {
          mismatch = false;
          jQuery('#wpi_discount_mismatch_error').text('You cannot mismatch discount types');
           jQuery('#wpi_add_discount').attr('disabled', true);
        }
      });
      if(mismatch) {
        jQuery('#wpi_discount_mismatch_error').text('You cannot mismatch discount types');
        jQuery(this).val('amount');
      }
    } else {
      // Enable button
      jQuery('#wpi_add_discount').removeAttr('disabled');
      jQuery('#wpi_discount_mismatch_error').text('');
    }
  });
*/
/*
  Update blank item rows count
*/
  jQuery("#wpi_blank_item_rows").change(function() {
    var updated_row_count = jQuery(this).val();
    var current_row_count = jQuery(".wp_invoice_itemized_list_row").size();
    var row_difference = updated_row_count - current_row_count;
    wpi_update_user_option('wpi_blank_item_rows', updated_row_count);
    // Insert rows if amount is more than current amount
    if(row_difference > 0) {
      var i = 0;
      while(i < row_difference) {
        add_itemized_list_row('invoice_list');
        i++;
      }
    }
  });
/*
  Process manual event
*/
jQuery('#wpi_process_manual_event').live('click', function(event) {
  wpi_process_manual_event();
});

/**
 * Process revalidation request
 **/
jQuery('#wpi_revalidate').live('click', function(){
  jQuery('#revalidate-loading').css({visibility:'visible'});
  jQuery(this).hide();
  var event_data = {
      action:"wpi_total_revalidate"
  };
  jQuery.ajax({
      dataType: "json",
      data: event_data,
      type: "POST",
      url: ajaxurl,
      success: function() {
        location.reload(true);
      }
    });
});

  /*
   * Update line tax when general tax is udpated
   * and Run recalculation function
   */
  jQuery('#postbox_publish #wp_invoice_tax').live('keyup', function(event) {
    jQuery('.line_tax_item').val(jQuery(this).val());
    jQuery('.item_charge_tax').val(jQuery(this).val());
    wpi_recalc_totals();
  });

  jQuery("#charges_list .fixed_width_holder input").live('keyup', function(){
    wpi_recalc_totals();
  });

  jQuery("#wpi_tax_method").live('change', function(){
    wpi_recalc_totals();
  });

  jQuery(".wp_invoice_discount_row").keyup(function(){
    if ( jQuery.trim( jQuery('.item_name', this).val() ).length && !empty(jQuery('.item_amount', this).val()) ) {
      jQuery('.item_name', this).removeClass('wpi_error');
      jQuery(".item_amount", this).removeClass('wpi_error');
    } else if ( jQuery.trim( jQuery('.item_name', this).val() ).length && empty(jQuery('.item_amount', this).val()) ) {
      jQuery(".item_amount", this).addClass('wpi_error');;
    } else if ( !jQuery.trim( jQuery('.item_name', this).val() ).length && empty(jQuery('.item_amount', this).val()) ) {
      jQuery('.item_name', this).removeClass('wpi_error');
      jQuery(".item_amount", this).removeClass('wpi_error');
    } else if ( !jQuery.trim( jQuery('.item_name', this).val() ).length && !empty(jQuery('.item_amount', this).val()) ) {
      jQuery(".item_name", this).addClass('wpi_error');;
    }
  });

  /*
   * Run recalculation function when certain fields are updates
   */
  jQuery('#wpi_invoice_form, #wpi_predefined_services_div').change(jQuery.delegate({
    '.item_type select': function() {wpi_recalc_totals();}
  }));


  jQuery('.item_name, .item_quantity, .item_price, .item_price input, .item_amount, .line_tax_item, .item_charge_tax').live("blur", function() {
    wpi_recalc_totals();
    var name  = jQuery(this).parents('.wp_invoice_itemized_list_row').find('.item_name');
    var price = jQuery(this).parents('.wp_invoice_itemized_list_row').find('.item_price');
    var quantity = jQuery(this).parents('.wp_invoice_itemized_list_row').find('.item_quantity');
    if ( !jQuery.trim(name.val()).length && !empty(price.val()) && !empty(quantity.val()) ) {
      name.addClass('wpi_error');
    } else {
      name.removeClass('wpi_error');
    }
  });
  /*
   * Run recalculation function when certain fields are updates
   */
  jQuery('#wpi_invoice_form, #wpi_predefined_services_div').keyup(
    jQuery.delegate({
      //'.item_quantity, .item_price, .item_price input': function() {wpi_recalc_totals();},
      '.line_tax_item, .item_charge_tax': function() {jQuery("#wp_invoice_tax").val("");}
    })
  );

/*
   Get the Notification Data depending on the value selected
*/
  jQuery("#wpi_change_notification").change(function () {
    wpi_load_email_notification();
  });
  jQuery('#wpi_send_notification').live('click', function(event) {
    event.preventDefault();
    wpi_send_notification();
  });
/*
  Event pack to toggle clickable link to add a description to an itemized row
*/
  /*jQuery('.wp_invoice_itemized_list_row').live('mouseover', function(event) {
    jQuery(".wpi_add_description_text .content", this).css('visibility','visible');
  });
  jQuery('.wp_invoice_itemized_list_row').live('mouseout', function(event) {
    jQuery(".wpi_add_description_text .content", this).css('visibility','hidden');
  });*/
  jQuery('.wpi_add_description_text .content').live('click', function(event) {
    jQuery(this).parents('.wp_invoice_itemized_list_row').find('.item_description').toggle();
  });
/*
  Adjusts the UI for line-item tax.
  Toggles tax column and fixes widths.
*/
  jQuery("#invoice-details-itemized-list-tax").click( function(){
    if(jQuery("#invoice-details-itemized-list-tax").is(":checked")) {
      wpi_adjust_for_tax_column('show');
    } else {
      wpi_adjust_for_tax_column('hide');
    }
  });
/*
  Button for adding another line to the itemized list
*/
  jQuery('#wpi_predefined_services_select').click( function() {
      add_itemized_list_row('invoice_list');
  });
/*
  Add another discount item to the itemized list.
*/
  jQuery('#wpi_add_discount').click( function() {
    // To fix the mismach issues, only allow one discount
    if(jQuery(".wp_invoice_discount_row:visible").size() > 0) {
    } else {
      add_itemized_list_row_discount();
    }
  });
/*
  Triggers insertion of a predefined service
*/
  jQuery('#wpi_predefined_services').change( function() {
    wpi_insert_predefined_service();
  });
/*
  Adjusts settings based on if the client can change payment methods or not.
  If user can't change paymetn method than we hide all methods except for the one selected
*/
  jQuery('#wp_invoice_payment_method').live('change', function(event) {
    if(jQuery('.wpi_client_change_payment_method').is(":not(:checked)"))
      wpi_disable_all_payment_methods();
    wpi_select_payment_method(jQuery('option:selected', this).val(), true);
    wpi_can_client_change_payment_method();
  });
/*
  Called when user changes wheather the client can change payment method, or must use the default
  wpi_can_client_change_payment_method() handles toggling options
*/
  jQuery('.wpi_client_change_payment_method').live('click', function(event) {
    wpi_can_client_change_payment_method();
  });
/*
  Displays specified payment method box
*/
  jQuery('.wpi_billing_section_show').live('click', function(event) {
		// if it is set as default, we can't turn it off
		/*if ( jQuery('#wp_invoice_payment_method option[value="'+jQuery(this).attr('id')+'"]').is(':selected') ) {
			if ( !jQuery(this).is(':checked') ) {
				return false;
			}
		}*/
    wpi_select_payment_method(jQuery(this).attr('id'));
  });

  /*
   * Handles invoice saving and updating
   * Validated invoice first, if validation is passed runs ajax saving functions
   */
  jQuery('#wpi_invoice_form').live('submit', function(event) {
    if(!wpi_validate_invoice())
      return false;
    /** Timeout is added here for hacking IE7,8 (IE fires some events too late, so we need to wait). peshkov@UD */
    setTimeout(wpi_save_invoice, 100);
    return false;
  });

  /*
   * Deletes an itemized list row
   * Recalculates totals
   */
  jQuery('.wp_invoice_itemized_list_row .row_delete', '#invoice_list').live('click', function(event) {
    if(jQuery(".wp_invoice_itemized_list_row").size() > 1) {
      jQuery(this).parents('.wp_invoice_itemized_list_row').remove();
    } else {
      jQuery("#invoice_list .wp_invoice_itemized_list_row .input_field").val('');
    }
    wpi_recalc_totals();
  });

  /*
   * Deletes an itemized list row
   * Recalculates totals
   */
  jQuery('.wp_invoice_itemized_charge_row .row_delete', '#charges_list').live('click', function(event) {
    jQuery(this).parents('.wp_invoice_itemized_charge_row').remove();
    wpi_recalc_totals();
  });

  /*
   * Deletes a dynamic table row
   */
  jQuery('.wpi_dynamic_table_row .row_delete').live('click', function(event) {
    var table = jQuery(this).parents('.ud_ui_dynamic_table');
    var current_row = jQuery(this).parents('.wpi_dynamic_table_row');

    if(jQuery('.wpi_dynamic_table_row', table).size() > 1) {
      current_row.remove();
    } else {
      jQuery("input, textarea" , current_row).val('');
    }

    if(table.attr('id') == 'itemized_list') {
      wpi_recalc_totals();
    }
  });

  /*
   * Deletes a discount row, clears out all values in row
   * Recalculates totals
   */
  jQuery('.wp_invoice_discount_row .row_delete').live('click', function(event) {
    if(jQuery(".wp_invoice_discount_row").size() > 1) {
      jQuery(this).parents('.wp_invoice_discount_row').remove();
    } else {
      jQuery(this).parents('.wp_invoice_discount_row').hide();
    }
    jQuery('.wp_invoice_discount_row:hidden input').val('');
    wpi_recalc_totals();
  });
/*
  UI Management for Invoice Management Page (admin_page_wpi_invoice_edit)
  Postboxes have class:     .wpi-toggle-postbox
  Other elements have class:   .wpi-toggle-element
  Different classes used because WP Function can be used for poxtboxes,
  but custom functions must be used for custom elements (elements within postboxes)
*/
  // Postboxes
  jQuery("#wpi_screen_meta .wpi-toggle-postbox").click(function() {
    // Toggle postbox
    postbox_name = jQuery(this).attr('name');
    //alert("#postbox_" + postbox_name);
    jQuery('#postbox_' + postbox_name).toggle();
    // Save changes to user options via database
    wpi_save_postboxes();
  });

  /*
   * Handles saving non-metabox Screen Options into a cookie.
   * On-load checking/unchecking is handled by PHP.
   */
  jQuery("#wpi_screen_meta .non-metabox-option").click(function() {
    var action = (jQuery(this).is(":checked") ? true : false);
    jQuery.cookie('wpi_display_' + jQuery(this).attr('name'), action);
   });

  /*
   * Handles result of a non-metabox item being clickec in Screen Options
   * Saving the settings is handled by a different event
   * Recalcs totals on events related to totals and taxes.
   */
  jQuery('#wpi_screen_meta').click(jQuery.delegate({
    '#wpi_ui_currency_options': function() {
        if(jQuery("#wpi_ui_currency_options").is(":checked") ? true : false) {
        jQuery("tr.wpi_ui_currency_options").show();
         wpi_update_user_option('wpi_ui_currency_options', 'true');
      } else {
        jQuery("tr.wpi_ui_currency_options").hide();
        wpi_update_user_option('wpi_ui_currency_options', 'false');
      }
    },
    '#wpi_ui_payment_method_options': function() {
        if(jQuery("#wpi_ui_payment_method_options").is(":checked") ? true : false) {
        jQuery("tr.wpi_ui_payment_method_options").show();
         wpi_update_user_option('wpi_ui_payment_method_options', 'true');
      } else {
        jQuery("tr.wpi_ui_payment_method_options").hide();
        wpi_update_user_option('wpi_ui_payment_method_options', 'false');
      }
    },
    '#wpi_itemized-list-tax.non-metabox-option': function() {
      if(jQuery("#wpi_itemized-list-tax.non-metabox-option").is(":checked") ? true : false) {
        wpi_adjust_for_tax_column('show');
        wpi_update_user_option('wpi_ui_display_itemized_tax', 'true');
      } else {
        wpi_update_user_option('wpi_ui_display_itemized_tax', 'false');
        wpi_adjust_for_tax_column('hide');
      }
    },
    '#wpi_overall-tax.non-metabox-option': function() {
        if(jQuery("#wpi_overall-tax.non-metabox-option").is(":checked") ? true : false) {
        wpi_update_user_option('wpi_ui_display_global_tax', 'true');
        jQuery("tr.wpi_ui_display_global_tax").show();
      } else {
        wpi_update_user_option('wpi_ui_display_global_tax', 'false');
        jQuery("tr.wpi_ui_display_global_tax").hide();
        jQuery("tr.wpi_ui_display_global_tax .input_field").val("");
      }
      wpi_recalc_totals();
    }
  }));
/*
  Toggles Screen Options tab expansion and collapsing
*/
  jQuery('#wpi_screen_meta #wpi-show-settings-link').click(function () {
    if ( ! jQuery('#screen-options-wrap').hasClass('screen-options-open') ) {
      jQuery('#contextual-help-link-wrap').css('visibility', 'hidden');
      jQuery('#screen-functions-link-wrap').css('visibility', 'hidden');
    }
    jQuery('#screen-options-wrap').slideToggle('fast', function(){
      if ( jQuery(this).hasClass('screen-options-open') ) {
        jQuery('#wpi-show-settings-link').css({'backgroundImage':'url("images/screen-options-right.gif")'});
        jQuery('#contextual-help-link-wrap').css('visibility', '');
        jQuery('#screen-functions-link-wrap').css('visibility', '');
        jQuery(this).removeClass('screen-options-open');
      } else {
        jQuery('#wpi-show-settings-link').css({'backgroundImage':'url("images/screen-options-right-up.gif")'});
        jQuery(this).addClass('screen-options-open');
      }
    });
    return false;
  });
/*
  Handles Screen Help tab expansion and collapsing
*/
  jQuery('#wpi_screen_meta #wpi-contextual-help-link').click(function () {
    if ( ! jQuery('#contextual-help-wrap').hasClass('contextual-help-open') ) {
      jQuery('#screen-options-link-wrap').css('visibility', 'hidden');
      jQuery('#screen-functions-link-wrap').css('visibility', 'hidden');
    }
    jQuery('#contextual-help-wrap').slideToggle('fast', function(){
      if ( jQuery(this).hasClass('contextual-help-open') ) {
        jQuery('#wpi-contextual-help-link').css({'backgroundImage':'url("images/screen-options-right.gif")'});
        jQuery('#screen-options-link-wrap').css('visibility', '');
        jQuery('#screen-functions-link-wrap').css('visibility', '');
        jQuery(this).removeClass('contextual-help-open');
      } else {
        jQuery('#contextual-help-link').css({'backgroundImage':'url("images/screen-options-right-up.gif")'});
        jQuery(this).addClass('contextual-help-open');
      }
    });
    return false;
  });
/*
  Handles Special Functions tab expansion and collapsing
*/
jQuery('#wpi_screen_meta #wpi-show-functions-link').click(function () {
    if ( ! jQuery('#screen-functions-wrap').hasClass('screen-functions-open') ) {
      jQuery('#contextual-help-link-wrap').css('visibility', 'hidden');
      jQuery('#screen-options-link-wrap').css('visibility', 'hidden');
    }
    jQuery('#screen-functions-wrap').slideToggle('fast', function(){
      if ( jQuery(this).hasClass('screen-functions-open') ) {
        jQuery('#wpi-show-functions-link').css({'backgroundImage':'url("images/screen-options-right.gif")'});
        jQuery('#contextual-help-link-wrap').css('visibility', '');
        jQuery('#screen-options-link-wrap').css('visibility', '');
        jQuery(this).removeClass('screen-functions-open');
      } else {
        jQuery('#wpi-show-settings-link').css({'backgroundImage':'url("images/screen-options-right-up.gif")'});
        jQuery(this).addClass('screen-functions-open');
      }
    });
    return false;
  });
// --- New Invoice Creation -- //
jQuery("#wpi_new_invoice_form").submit(function() {
  if(wpi_validate_email(jQuery("#wp_invoice_userlookup").val())) {
    wpi_remove_errors();
    return true;
  } else {
    wpi_show_error("Please enter a valid email address.");
    return false;
  }
});
/*
  Handle invoice copying
*/
jQuery("#wp_invoice_copy_invoice").click(function() {
  jQuery(".wp_invoice_copy_invoice").toggle();
  jQuery("#wp_invoice_create_new_invoice").toggle();
  jQuery("#wp_invoice_copy_invoice").toggle();
})
/*
  Cancel invoice copying
*/
jQuery("#wp_invoice_copy_invoice_cancel").click(function() {
  jQuery(".wp_invoice_copy_invoice").toggle();
  jQuery("#wp_invoice_create_new_invoice").toggle();
  jQuery("#wp_invoice_copy_invoice").toggle();
})
/*
  Do not submit form if no user is defined
*/
jQuery("#wpi_new_invoice_form").submit(function() {
  if(jQuery("#wp_invoice_userlookup").val() == "") return false;
});
// -- Settings Page -- //
/*
  Display notification of wheather custom template can be used based on if a "wpi" folder exists or not
*/
  jQuery('.wpi_wpi_settings_use_custom_templates_').live('click', function(event) {
    if(jQuery(this).is(":checked")) {
      jQuery(".wpi_use_custom_template_settings").show();
    } else {
      jQuery(".wpi_use_custom_template_settings").hide();
     }
  });
/*
  Confirms that user wants to overwrite any tempaltes in their wpi folder
*/
  jQuery('input.wpi_install_custom_templates').live('click', function() {
      var answer = confirm("This will overwrite any theme files you currently have in your /wpi/ folder.")
      if( answer ) {
        jQuery.post(ajaxurl,
          {
            'action':'wpi_install_custom_templates'
          }, function( response ){
            jQuery('.wpi_install_custom_templates_result').html(response.join()).show();
          }, 'json');
      }
  });
/*
  Called when user changes wheather the client can change payment method, or must use the default
  wpi_can_client_change_payment_method() handles toggling options
*/
  jQuery('.wpi_settings_client_change_payment_method').live('change', function(event) {
    wpi_can_client_change_payment_method();
  });

  var wpi_currency_accordion = jQuery("#currency-list").accordion({
    header: "h3",
    animated: false,
    autoHeight: false,
    collapsible: true,
    icons: {
      'header': 'ui-icon-plus',
      'headerSelected':'ui-icon-minus'
    },
    active:false
  });

/*
  Do any validation/data work before the settings page form is submitted
*/
  jQuery("#wpi_settings_form").submit(function() {
    var validation_ok = true;
    jQuery(".wpi_dynamic_table_row :input[validation_required=true]").each(function(){
      if (!jQuery(this).val()){
        wpi_show_error("This is a required field.");
        error_field = this;
        validation_ok = false;
      }
    });

    jQuery(".wpi_dynamic_table_row[new_row=true] .code").each(function(){
      if (!jQuery(this).val().match("[A-Z]{3}")){
        wpi_show_error("Please enter a valid currency code.");
        error_field = this;
        validation_ok = false;
      }
    });

    // Convert list of favorite countries into CSV format, and paste CSV into hidden field
     jQuery("input[name='wpi_settings[globals][favorite_countries]']").val(jQuery("#wpi_favorite_countries option" ).attrList( "value", "," ));

     if (!validation_ok) {
       jQuery("#wp_invoice_settings_page").tabs('select', 2); // switch to third tab
       if(jQuery("#currency-list").accordion( "option", "active")===false){
         jQuery("#currency-list").accordion( "option", "active",0);
       }
       jQuery(error_field).focus();
       return false;
     }

  });
/*
  Confirm complete removal of WPI databases
*/
  jQuery('#delete_all_wp_invoice_databases').click(function() {
      var txt = 'Are you sure you want to delete all the databases?  All your invoice and log data will be lost forever. ';
      jQuery.prompt(txt,{buttons:{Delete:true, Cancel:false}, callback: function(v,m){if(v){document.location = "admin.php?page=new_invoice&wp_invoice_action=complete_removal";}}
    });
    return false
  });
/*
  Invoice overview table sorting and filtering
*/
  var tog = false; // or true if they are checked on load
   jQuery('#invoice_sorter_table #CheckAll').click(function() {
    jQuery("#invoice_sorter_table input[type=checkbox]").attr("checked",!tog);
    tog = !tog;
   });

  jQuery("#invoice_sorter_table tr:has(td)").each(function(){
     var t = jQuery(this).text().toLowerCase(); //all row text
     jQuery("<td class='indexColumn'></td>")
    .hide().text(t).appendTo(this);
  });//each tr
  jQuery(".invoice-search-input").keyup(function(){
      //wp_invoice_calculate_owed();
      var s = jQuery(this).val().toLowerCase().split(" ");
      //show all rows.
     jQuery("#invoice_sorter_table tr:hidden").show();
     jQuery.each(s, function(){
       jQuery("#invoice_sorter_table tr:visible .indexColumn:not(:contains('"
        + this + "'))").parent().hide();
     });//each
   });//key up.
 // -- First Time Setup -- //
/*
  Validate first-time setup form
*/
/*
var validator =  jQuery("#wp_invoice_first_time_setup").validate({
  rules: {
  "wp_invoice_business_name": {
    required: true
  },
  "wp_invoice_web_invoice_page": {
    required: true
  },
  // If we are accepting PayPal
  "wp_invoice_paypal_address": {
    required: function(element) {return jQuery("#wp_invoice_paypal_allow").attr("checked"); },
    email: true
  },
  "wp_invoice_fe_paypal_link_url": {
    required: function(element) {return jQuery("#wp_invoice_paypal_allow").attr("checked"); },
    url: true
  },
  // If we are accepting credit cards
  "wp_invoice_gateway_merchant_email ": {
    required: function(element) {return jQuery("#wp_invoice_cc_allow").attr("checked"); },
    email: true
  },
  "wp_invoice_gateway_tran_key": {
    required: function(element) {return jQuery("#wp_invoice_cc_allow").attr("checked"); }
  },
  "wp_invoice_gateway_username": {
    required: function(element) {return jQuery("#wp_invoice_cc_allow").attr("checked"); }
  },
  "wp_invoice_gateway_url": {
    required: function(element) {return jQuery("#wp_invoice_cc_allow").attr("checked"); },
    url: true
  },
},
invalidHandler: function() {
  alert('invalid handler form for new user setup');
  },
submitHandler: function(form) {
  jQuery("#wp_invoice_first_time_setup").submit();
 }});
  // overwrite focusInvalid to activate tab with invalid elements
  jQuery("#wp_invoice_first_time_setup").focusInvalid = function() {
    if( this.settings.focusInvalid ) {
      try {
        var focused = jQuery(this.errorList.length && this.errorList[0].element || []);
        jQuery('.wp_invoice_accordion_section').accordion('activate' , '#' + focused.parents('.wp_invoice_accordion_section').children('h3').attr("id"));
        focused.focus();
      } catch(e) {
        // ignore IE throwing errors when focusing hidden elements
      }
    }
  };
/*
// --- Invoice Overview Page -- //
/*
  Filter by recipients
*/
  jQuery("#wpi_filter_overview_by_recipient").change(function() {
    var target_url = jQuery("#wpi_target_url").val();
    window.location = target_url + "&recipient_filter="+ jQuery(this).val();
  });

/*
  Toggle display of archived invoices
  Recalc amount owed.
*/
  jQuery("#wp_invoice_show_archived").click(function() {
    jQuery(".wp_invoice_archived").toggle();
    wp_invoice_calculate_owed();
    return false;
  })

/*
  Perform bulk delete action
*/
  jQuery("#submit_bulk_action").click( function(){
    if(jQuery("#wp_invoice_action :selected").text() == 'Delete') {
      var r=confirm("Are you sure you want to delete the selected invoice(s)?");
      if (r==true){
        return true;
      }else{
        return false;
      }
    }
  });

/*
  Do not submit invoice filter if no action is selected
*/
  jQuery("#invoices-filter").submit(function() {
    if(jQuery("#invoices-filter select").val() == '-1')
      return false;
  })

  // Anton Korotkov
  // Event date & time
  var curDate = new Date();
  var m = curDate.getMonth()+1;
  jQuery(".wpi_event_date")
    .val((m<10?"0"+m:m)+"/"+(curDate.getDate()<10?"0"+curDate.getDate():curDate.getDate())+"/"+curDate.getFullYear())
    .datepicker();
  var h = curDate.getHours()<10?"0"+curDate.getHours():curDate.getHours();
  var minutes = curDate.getMinutes()<10?"0"+curDate.getMinutes():curDate.getMinutes();
  jQuery(".wpi_event_time").val(h+":"+minutes);

  // Invoice link expanding
  jQuery('#edit-slug-box.wpi-edit-slug-box').click(function(){
    jQuery(this).css({height:function(){return jQuery(this).height()==18?"auto":18}});
  });

  // event_type_selector
  jQuery("#wpi_event_type").change(function(){
    if ( jQuery(this).val() == 'add_charge' ) {
      jQuery("#event_tax_holder").show();
    } else {
      jQuery("#event_tax_holder").hide();
    }
  });

  // Permanently deletion confirm
  jQuery("a.submitdelete.permanently").live("click", function(){
    var answer = confirm("Remove this invoice permanently?")
    if (answer){
      return true;
    }
    return false;
  });

  jQuery("#doaction").live("click", function(){
    var action = jQuery("select[name=action]").val();
    if ( action == 'delete' ) {
      var answer = confirm("Remove selected invoices permanently?")
      if (answer){
        return true;
      }
      return false;
    }
    return true;
  });

  // Prevent page reloading when list table is clicked
  jQuery("#wp-list-table th a").live("click", function(){
    return false;
  });

  // DataTable check all checkbox
  jQuery("input.check-all", "#wp-list-table").click(function(e){
    if ( e.target.checked ) {
      jQuery("#the-list td.cb input:checkbox").attr('checked', 'checked');
    } else {
      jQuery("#the-list td.cb input:checkbox").removeAttr('checked');
    }
  });

  //** GA Track Events options hidding */
  jQuery("#wpi_wpi_settings_ga_event_tracking_enabled_").click(function(e) {
    jQuery(this).parents('ul').find('li.wpi_ga_events_list').toggle();
  });

});