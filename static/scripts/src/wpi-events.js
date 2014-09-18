/**
 * Global Plugin events
 */
jQuery.noConflict();

jQuery( document ).ready( function () {

  //** Cycle through all advanced UI options and toggle them */
  jQuery( ".wpi_show_advanced" ).each( function () {
    wpi_toggle_advanced_options( this );
  } );

  //** Enable monitoring of toggling of advanced UI options */
  jQuery( ".wpi_show_advanced" ).on( "click", function () {
    wpi_toggle_advanced_options( this );
  } );

  //** Add row to UD UI Dynamic Table */
  jQuery( ".wpi_add_row" ).on( "click", function () {
    wpi_add_row( this );
  } );

  jQuery( "#currency-list" ).on( "change", ".wpi_dynamic_table_row[new_row=true] input.names_changer", function () {

    var this_row = jQuery( this ).parents( 'tr.wpi_dynamic_table_row' );
    //** Slug of row in question */
    var old_slug = jQuery( this_row ).attr( 'slug' );
    var new_slug = jQuery( this ).val();

    //** Don't allow to blank out slugs */
    if ( new_slug == "" ) {
      return;
    }

    //** If slug input.slug exists in row, we modify it */
    jQuery( ".slug", this_row ).val( new_slug );
    //** Update row slug */
    jQuery( this_row ).attr( 'slug', new_slug );

    //** Cycle through all child elements and fix names */
    jQuery( 'input,select,textarea', this_row ).each( function ( element ) {
      var old_name = jQuery( this ).attr( 'name' );
      if ( typeof old_name != 'undefined' ) {
        var new_name = old_name.replace( old_slug, new_slug );
        jQuery( this ).attr( 'name', new_name );
      }
      var old_id = jQuery( this ).attr( 'id' );
      if ( typeof old_id != 'undefined' ) {
        var new_id = old_id.replace( old_slug, new_slug );
        jQuery( this ).attr( 'id', new_id );
      }
    } );

    //** Cycle through labels too */
    jQuery( 'label', this_row ).each( function ( element ) {
      var old_for = jQuery( this ).attr( 'for' );
      if ( typeof old_for != 'undefined' ) {
        var new_for = old_for.replace( old_slug, new_slug );
        jQuery( this ).attr( 'for', new_for );
      }
    } );

  } );

  //** remove html5 check for reqirements and make it manualy on submit */
  jQuery( "#currency-list :input[required]" ).each( function () {
    jQuery( this ).removeAttr( 'required' ).attr( 'validation_required', true );
  } );

  jQuery( "#minor-publishing table.form-table" ).find( 'tbody' ).toggle();
  jQuery( "#wpi_button_show_advanced" ).on( "click", function () {
    jQuery( this ).parents( "#minor-publishing table.form-table" ).find( 'tbody' ).toggle();
  } );

  //** Accordions */
  var first_time_setup_accordion = jQuery( "#first_time_setup_accordion" ).accordion( {heightStyle: "content", header: "h3", animated: false, autoHeight: false, icons: {'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus'}} );
  var wpi_payment_accordion = jQuery( ".wp_invoice_accordion" ).accordion( {
    heightStyle: "content",
    header: "h3",
    animated: false,
    autoHeight: false,
    icons: {
      'header': 'ui-icon-plus',
      'headerSelected': 'ui-icon-minus'
    }
  } );

  wpi_init_payment_method();
  jQuery( ".ui-state-error" ).dblclick( function () {
    jQuery( this ).remove();
  } );
  jQuery( ".wp_invoice_qc_report" ).dblclick( function () {
    jQuery( this ).remove();
  } );
  jQuery( '#contextual-help-link-wrap, #screen-options-link-wrap, #screen-functions-link-wrap' ).show();
  tooltip();

  //** If Deposit is allowed for the current invoice, we show/hide additional settings */
  if ( jQuery( "#wpi_wpi_invoice_deposit_" ).is( ":checked" ) ) {
    wpi_enable_deposit();
  }
  //** If Recurring is allowed for the current invoice, we show/hide additional settings */
  else if ( jQuery( "#wpi_wpi_invoice_meta_recurring_active_" ).is( ":checked" ) ) {
    wpi_enable_recurring();
  }
  //** If Quote is allowed for the current invoice, we show/hide quote option */
  else if ( jQuery( "#wpi_wpi_invoice_quote_" ).is( ":checked" ) ) {
    wpi_enable_quote();
    wpi_hide_deposit_option();
    wpi_hide_recurring_option();
  }

  /**
   * Toggle invoice deposit options
   */
  jQuery( "#wpi_wpi_invoice_deposit_" ).on( "click", function ( event ) {
    if ( jQuery( this ).is( ":checked" ) ) {
      wpi_enable_deposit();
    } else {
      wpi_disable_deposit();
    }
  } );

  if ( jQuery( ".wpi_wpi_invoice_recurring_active_" ).is( ":checked" ) ) {
    wpi_enable_recurring();
  } else {
    //** Singular invoice or quote. Clear out all values. */
    wpi_disable_recurring();
  }

  /**
   * Toggle recurring billing options
   */
  jQuery( ".wpi_wpi_invoice_recurring_active_" ).on( "click", function ( event ) {
    if ( jQuery( this ).is( ":checked" ) ) {
      wpi_enable_recurring();
    } else {
      //** Singular invoice or quote. Clear out all values. */
      wpi_disable_recurring();

      /**
       * This functions was moved from wpi_disable_recurring() (wpi-functions.js)
       */
      wpi_show_deposit_option();
      wpi_show_quote_option();
    }
  } );

  /**
   * Toggle recurring billing start date
   */
  jQuery( ".wpi_wpi_invoice_recurring_send_invoice_automatically" ).on( "click", function ( event ) {
    if ( jQuery( this ).is( ":checked" ) ) {
      wpi_disable_recurring_start_date( jQuery( this ).data('type') );
    } else {
      //** Singular invoice or quote. Clear out all values. */
      wpi_enable_recurring_start_date( jQuery( this ).data('type') );
    }
  } );

  /**
   * Turn off certain options when it quote mode
   */
  jQuery( ".wpi_wpi_invoice_quote_" ).click( function () {
    if ( jQuery( this ).is( ":checked" ) ) {
      wpi_disable_recurring();
      wpi_disable_deposit();
      wpi_hide_deposit_option();
      jQuery( '.wpi_turn_off_recurring' ).hide();
    } else {
      wpi_show_deposit_option();
      jQuery( '.wpi_turn_off_recurring' ).show();
    }
  } );

  /**
   * Update blank item rows count
   */
  jQuery( "#wpi_blank_item_rows" ).change( function () {
    var updated_row_count = jQuery( this ).val();
    var current_row_count = jQuery( ".wp_invoice_itemized_list_row" ).size();
    var row_difference = updated_row_count - current_row_count;
    wpi_update_user_option( 'wpi_blank_item_rows', updated_row_count );
    //** Insert rows if amount is more than current amount */
    if ( row_difference > 0 ) {
      var i = 0;
      while ( i < row_difference ) {
        add_itemized_list_row( 'invoice_list' );
        i++;
      }
    }
  } );

  /**
   * process manual event
   */
  jQuery( '#wpi_process_manual_event' ).on( 'click', function ( event ) {
    wpi_process_manual_event();
  } );

  /**
   * Update line tax when general tax is udpated
   * and Run recalculation function
   */
  jQuery( '#postbox_publish #wp_invoice_tax' ).on( 'keyup', function ( event ) {
    jQuery( '.line_tax_item' ).val( jQuery( this ).val() );
    jQuery( '.item_charge_tax' ).val( jQuery( this ).val() );
    wpi_recalc_totals();
  } );

  jQuery( "#charges_list .fixed_width_holder input" ).on( 'keyup', function () {
    wpi_recalc_totals();
  } );

  jQuery( "#wpi_tax_method" ).on( 'change', function () {
    wpi_recalc_totals();
  } );

  jQuery( ".wp_invoice_discount_row" ).keyup( function () {
    if ( jQuery.trim( jQuery( '.item_name', this ).val() ).length && !empty( jQuery( '.item_amount', this ).val() ) ) {
      jQuery( '.item_name', this ).removeClass( 'wpi_error' );
      jQuery( ".item_amount", this ).removeClass( 'wpi_error' );
    } else if ( jQuery.trim( jQuery( '.item_name', this ).val() ).length && empty( jQuery( '.item_amount', this ).val() ) ) {
      jQuery( ".item_amount", this ).addClass( 'wpi_error' );
      ;
    } else if ( !jQuery.trim( jQuery( '.item_name', this ).val() ).length && empty( jQuery( '.item_amount', this ).val() ) ) {
      jQuery( '.item_name', this ).removeClass( 'wpi_error' );
      jQuery( ".item_amount", this ).removeClass( 'wpi_error' );
    } else if ( !jQuery.trim( jQuery( '.item_name', this ).val() ).length && !empty( jQuery( '.item_amount', this ).val() ) ) {
      jQuery( ".item_name", this ).addClass( 'wpi_error' );
      ;
    }
  } );

  /**
   * Run recalculation function when certain fields are updates
   */
  jQuery( '#wpi_invoice_form, #wpi_predefined_services_div' ).change( jQuery.delegate( {
    '.item_type select': function () {
      wpi_recalc_totals();
    }
  } ) );

  jQuery(document).on( "blur", '.item_name, .item_quantity, .item_price, .item_price input, .item_amount, .line_tax_item, .item_charge_tax', function () {
    wpi_recalc_totals();
    var name = jQuery( this ).parents( '.wp_invoice_itemized_list_row' ).find( '.item_name' );
    var price = jQuery( this ).parents( '.wp_invoice_itemized_list_row' ).find( '.item_price' );
    var quantity = jQuery( this ).parents( '.wp_invoice_itemized_list_row' ).find( '.item_quantity' );
    if ( !jQuery.trim( name.val() ).length && !empty( price.val() ) && !empty( quantity.val() ) ) {
      name.addClass( 'wpi_error' );
    } else {
      name.removeClass( 'wpi_error' );
    }
  } );
  
  /**
   * Run recalculation function when certain fields are updates
   */
  jQuery( '#wpi_invoice_form, #wpi_predefined_services_div' ).keyup( jQuery.delegate( {
    '.line_tax_item, .item_charge_tax': function () {
      jQuery( "#wp_invoice_tax" ).val( "" );
    }
  } ) );

  /**
   * Get the Notification Data depending on the value selected
   */
  jQuery( "#wpi_change_notification" ).change( function () {
    wpi_load_email_notification();
  } );
  jQuery( '#wpi_send_notification' ).on( 'click', function ( event ) {
    event.preventDefault();
    wpi_send_notification();
  } );

  jQuery( '.wpi_add_description_text .content' ).on( 'click', function ( event ) {
    console.log(this);
    jQuery( this ).parents( '.wp_invoice_itemized_list_row' ).find( '.item_description' ).toggle();
  } );

  /**
   * Adjusts the UI for line-item tax.
   * Toggles tax column and fixes widths.
   */
  jQuery( "#invoice-details-itemized-list-tax" ).click( function () {
    if ( jQuery( "#invoice-details-itemized-list-tax" ).is( ":checked" ) ) {
      wpi_adjust_for_tax_column( 'show' );
    } else {
      wpi_adjust_for_tax_column( 'hide' );
    }
  } );

  /**
   * Button for adding another line to the itemized list
   */
  jQuery( '#wpi_predefined_services_select' ).click( function () {
    add_itemized_list_row( 'invoice_list' );
  } );

  /**
   * Add another discount item to the itemized list.
   */
  jQuery( '#wpi_add_discount' ).click( function () {
    //** To fix the mismach issues, only allow one discount */
    if ( jQuery( ".wp_invoice_discount_row:visible" ).size() > 0 ) {
    } else {
      add_itemized_list_row_discount();
    }
  } );

  /**
   * Triggers insertion of a predefined service
   */
  jQuery( '#wpi_predefined_services' ).change( function () {
    wpi_insert_predefined_service();
  } );

  /**
   * Adjusts settings based on if the client can change payment methods or not.
   * If user can't change paymetn method than we hide all methods except for the one selected
   */
  jQuery( '#wp_invoice_payment_method' ).on( 'change', function ( event ) {
    if ( jQuery( '.wpi_client_change_payment_method' ).is( ":not(:checked)" ) )
      wpi_disable_all_payment_methods();
    wpi_select_payment_method( jQuery( 'option:selected', this ).val(), true );
    wpi_can_client_change_payment_method();
  } );

  /**
   * Called when user changes wheather the client can change payment method, or must use the default
   * wpi_can_client_change_payment_method() handles toggling options
   */
  jQuery( '.wpi_client_change_payment_method' ).on( 'click', function ( event ) {
    wpi_can_client_change_payment_method();
  } );

  /**
   * Displays specified payment method box
   */
  jQuery( '.wpi_billing_section_show' ).on( 'click', function ( event ) {
    wpi_select_payment_method( jQuery( this ).attr( 'id' ) );
  } );

  /**
   * Handles invoice saving and updating
   * Validated invoice first, if validation is passed runs ajax saving functions
   */
  jQuery( '#wpi_invoice_form' ).on( 'submit', function ( event ) {
    if ( !wpi_validate_invoice() )
      return false;
    /** Timeout is added here for hacking IE7,8 (IE fires some events too late, so we need to wait). peshkov@UD */
    setTimeout( wpi_save_invoice, 100 );
    return false;
  } );

  /**
   * Deletes an itemized list row
   * Recalculates totals
   */
  jQuery( '#invoice_list' ).on( 'click', '.wp_invoice_itemized_list_row .row_delete', function ( event ) {
    if ( jQuery( ".wp_invoice_itemized_list_row" ).size() > 1 ) {
      jQuery( this ).parents( '.wp_invoice_itemized_list_row' ).remove();
    } else {
      jQuery( "#invoice_list .wp_invoice_itemized_list_row .input_field" ).val( '' );
    }
    wpi_recalc_totals();
  } );

  /**
   * Deletes an itemized list row
   * Recalculates totals
   */
  jQuery( '#charges_list' ).on( 'click', '.wp_invoice_itemized_charge_row .row_delete', function ( event ) {
    jQuery( this ).parents( '.wp_invoice_itemized_charge_row' ).remove();
    wpi_recalc_totals();
  } );

  /**
   * Deletes a dynamic table row
   */
  jQuery( '.wpi_dynamic_table_row .row_delete' ).on( 'click', function ( event ) {
    var table = jQuery( this ).parents( '.ud_ui_dynamic_table' );
    var current_row = jQuery( this ).parents( '.wpi_dynamic_table_row' );

    if ( jQuery( '.wpi_dynamic_table_row', table ).size() > 1 ) {
      current_row.remove();
    } else {
      jQuery( "input, textarea", current_row ).val( '' );
    }

    if ( table.attr( 'id' ) == 'itemized_list' ) {
      wpi_recalc_totals();
    }
  } );

  /**
   * Deletes a discount row, clears out all values in row
   * Recalculates totals
   */
  jQuery( '.wp_invoice_discount_row .row_delete' ).on( 'click', function ( event ) {
    if ( jQuery( ".wp_invoice_discount_row" ).size() > 1 ) {
      jQuery( this ).parents( '.wp_invoice_discount_row' ).remove();
    } else {
      jQuery( this ).parents( '.wp_invoice_discount_row' ).hide();
    }
    jQuery( '.wp_invoice_discount_row:hidden input' ).val( '' );
    wpi_recalc_totals();
  } );

  /**
   * Handles saving non-metabox Screen Options into a cookie.
   * On-load checking/unchecking is handled by PHP.
   */
  jQuery( "#wpi_screen_meta .non-metabox-option" ).click( function () {
    var action = (jQuery( this ).is( ":checked" ) ? true : false);
    jQuery.cookie( 'wpi_display_' + jQuery( this ).attr( 'name' ), action );
  } );

  /**
   * Handles result of a non-metabox item being clickec in Screen Options
   * Saving the settings is handled by a different event
   * Recalcs totals on events related to totals and taxes.
   */
  jQuery( '#wpi_screen_meta' ).click( jQuery.delegate( {
    '#wpi_ui_currency_options': function () {
      if ( jQuery( "#wpi_ui_currency_options" ).is( ":checked" ) ? true : false ) {
        jQuery( "tr.wpi_ui_currency_options" ).show();
        wpi_update_user_option( 'wpi_ui_currency_options', 'true' );
      } else {
        jQuery( "tr.wpi_ui_currency_options" ).hide();
        wpi_update_user_option( 'wpi_ui_currency_options', 'false' );
      }
    },
    '#wpi_ui_payment_method_options': function () {
      if ( jQuery( "#wpi_ui_payment_method_options" ).is( ":checked" ) ? true : false ) {
        jQuery( "tr.wpi_ui_payment_method_options" ).show();
        wpi_update_user_option( 'wpi_ui_payment_method_options', 'true' );
      } else {
        jQuery( "tr.wpi_ui_payment_method_options" ).hide();
        wpi_update_user_option( 'wpi_ui_payment_method_options', 'false' );
      }
    },
    '#wpi_itemized-list-tax.non-metabox-option': function () {
      if ( jQuery( "#wpi_itemized-list-tax.non-metabox-option" ).is( ":checked" ) ? true : false ) {
        wpi_adjust_for_tax_column( 'show' );
        wpi_update_user_option( 'wpi_ui_display_itemized_tax', 'true' );
      } else {
        wpi_update_user_option( 'wpi_ui_display_itemized_tax', 'false' );
        wpi_adjust_for_tax_column( 'hide' );
      }
    },
    '#wpi_overall-tax.non-metabox-option': function () {
      if ( jQuery( "#wpi_overall-tax.non-metabox-option" ).is( ":checked" ) ? true : false ) {
        wpi_update_user_option( 'wpi_ui_display_global_tax', 'true' );
        jQuery( "tr.wpi_ui_display_global_tax" ).show();
      } else {
        wpi_update_user_option( 'wpi_ui_display_global_tax', 'false' );
        jQuery( "tr.wpi_ui_display_global_tax" ).hide();
        jQuery( "tr.wpi_ui_display_global_tax .input_field" ).val( "" );
      }
      wpi_recalc_totals();
    }
  } ) );

  /**
   * Toggles Screen Options tab expansion and collapsing
   */
  jQuery( '#wpi_screen_meta #wpi-show-settings-link' ).click( function () {
    if ( !jQuery( '#screen-options-wrap' ).hasClass( 'screen-options-open' ) ) {
      jQuery( '#contextual-help-link-wrap' ).css( 'visibility', 'hidden' );
      jQuery( '#screen-functions-link-wrap' ).css( 'visibility', 'hidden' );
    }
    jQuery( '#screen-options-wrap' ).slideToggle( 'fast', function () {
      if ( jQuery( this ).hasClass( 'screen-options-open' ) ) {
        jQuery( '#wpi-show-settings-link' ).css( {'backgroundImage': 'url("images/screen-options-right.gif")'} );
        jQuery( '#contextual-help-link-wrap' ).css( 'visibility', '' );
        jQuery( '#screen-functions-link-wrap' ).css( 'visibility', '' );
        jQuery( this ).removeClass( 'screen-options-open' );
      } else {
        jQuery( '#wpi-show-settings-link' ).css( {'backgroundImage': 'url("images/screen-options-right-up.gif")'} );
        jQuery( this ).addClass( 'screen-options-open' );
      }
    } );
    return false;
  } );

  /**
   * Handles Screen Help tab expansion and collapsing
   */
  jQuery( '#wpi_screen_meta #wpi-contextual-help-link' ).click( function () {
    if ( !jQuery( '#contextual-help-wrap' ).hasClass( 'contextual-help-open' ) ) {
      jQuery( '#screen-options-link-wrap' ).css( 'visibility', 'hidden' );
      jQuery( '#screen-functions-link-wrap' ).css( 'visibility', 'hidden' );
    }
    jQuery( '#contextual-help-wrap' ).slideToggle( 'fast', function () {
      if ( jQuery( this ).hasClass( 'contextual-help-open' ) ) {
        jQuery( '#wpi-contextual-help-link' ).css( {'backgroundImage': 'url("images/screen-options-right.gif")'} );
        jQuery( '#screen-options-link-wrap' ).css( 'visibility', '' );
        jQuery( '#screen-functions-link-wrap' ).css( 'visibility', '' );
        jQuery( this ).removeClass( 'contextual-help-open' );
      } else {
        jQuery( '#contextual-help-link' ).css( {'backgroundImage': 'url("images/screen-options-right-up.gif")'} );
        jQuery( this ).addClass( 'contextual-help-open' );
      }
    } );
    return false;
  } );

  /**
   * Handles Special Functions tab expansion and collapsing
   */
  jQuery( '#wpi_screen_meta #wpi-show-functions-link' ).click( function () {
    if ( !jQuery( '#screen-functions-wrap' ).hasClass( 'screen-functions-open' ) ) {
      jQuery( '#contextual-help-link-wrap' ).css( 'visibility', 'hidden' );
      jQuery( '#screen-options-link-wrap' ).css( 'visibility', 'hidden' );
    }
    jQuery( '#screen-functions-wrap' ).slideToggle( 'fast', function () {
      if ( jQuery( this ).hasClass( 'screen-functions-open' ) ) {
        jQuery( '#wpi-show-functions-link' ).css( {'backgroundImage': 'url("images/screen-options-right.gif")'} );
        jQuery( '#contextual-help-link-wrap' ).css( 'visibility', '' );
        jQuery( '#screen-options-link-wrap' ).css( 'visibility', '' );
        jQuery( this ).removeClass( 'screen-functions-open' );
      } else {
        jQuery( '#wpi-show-settings-link' ).css( {'backgroundImage': 'url("images/screen-options-right-up.gif")'} );
        jQuery( this ).addClass( 'screen-functions-open' );
      }
    } );
    return false;
  } );

  /**
   * New invoice creation
   */
  jQuery( "#wpi_new_invoice_form" ).submit( function () {
    if ( wpi_validate_email( jQuery( "#wp_invoice_userlookup" ).val() ) ) {
      wpi_remove_errors();
      return true;
    } else {
      wpi_show_error( "Please enter a valid email address." );
      return false;
    }
  } );

  /**
   * Handle invoice copying
   */
  jQuery( "#wp_invoice_copy_invoice" ).click( function () {
    jQuery( ".wp_invoice_copy_invoice" ).toggle();
    jQuery( "#wp_invoice_create_new_invoice" ).toggle();
    jQuery( "#wp_invoice_copy_invoice" ).toggle();
  } );

  /**
   * Cancel invoice copying
   */
  jQuery( "#wp_invoice_copy_invoice_cancel" ).click( function () {
    jQuery( ".wp_invoice_copy_invoice" ).toggle();
    jQuery( "#wp_invoice_create_new_invoice" ).toggle();
    jQuery( "#wp_invoice_copy_invoice" ).toggle();
  } );

  /**
   * Do not submit form if no user is defined
   */
  jQuery( "#wpi_new_invoice_form" ).submit( function () {
    if ( jQuery( "#wp_invoice_userlookup" ).val() == "" ) return false;
  } );

  /**
   * Display notification of wheather custom template can be used based on if a "wpi" folder exists or not
   */
  jQuery( '.wpi_wpi_settings_use_custom_templates_' ).on( 'click', function ( event ) {
    if ( jQuery( this ).is( ":checked" ) ) {
      jQuery( ".wpi_use_custom_template_settings" ).show();
    } else {
      jQuery( ".wpi_use_custom_template_settings" ).hide();
    }
  } );

  /**
   * Confirms that user wants to overwrite any tempaltes in their wpi folder
   */
  jQuery( 'input.wpi_install_custom_templates' ).on( 'click', function () {
    var answer = confirm( "This will overwrite any theme files you currently have in your /wpi/ folder." )
    if ( answer ) {
      jQuery.post( ajaxurl, {
          'action': 'wpi_install_custom_templates'
        }, function ( response ) {
          jQuery( '.wpi_install_custom_templates_result' ).html( response.join() ).show();
        }, 'json' );
    }
  } );

  /**
   * Called when user changes wheather the client can change payment method, or must use the default
   * wpi_can_client_change_payment_method() handles toggling options
   */
  jQuery( '.wpi_settings_client_change_payment_method' ).on( 'change', function ( event ) {
    wpi_can_client_change_payment_method();
  } );

  /**
   * Currencies
   */
  var wpi_currency_accordion = jQuery( "#currency-list" ).accordion( {
    header: "h3",
    animated: false,
    autoHeight: false,
    collapsible: true,
    icons: {
      'header': 'ui-icon-plus',
      'headerSelected': 'ui-icon-minus'
    },
    active: false
  } );

  /**
   * Do any validation/data work before the settings page form is submitted
   */
  jQuery( "#wpi_settings_form" ).submit( function () {
    var validation_ok = true;
    jQuery( ".wpi_dynamic_table_row :input[validation_required=true]" ).each( function () {
      if ( !jQuery( this ).val() ) {
        wpi_show_error( "This is a required field." );
        error_field = this;
        validation_ok = false;
      }
    } );

    jQuery( ".wpi_dynamic_table_row[new_row=true] .code" ).each( function () {
      if ( !jQuery( this ).val().match( "[A-Z]{3}" ) ) {
        wpi_show_error( "Please enter a valid currency code." );
        error_field = this;
        validation_ok = false;
      }
    } );

    //** Convert list of favorite countries into CSV format, and paste CSV into hidden field */
    jQuery( "input[name='wpi_settings[globals][favorite_countries]']" ).val( jQuery( "#wpi_favorite_countries option" ).attrList( "value", "," ) );

    if ( !validation_ok ) {
      jQuery( "#wp_invoice_settings_page" ).tabs( 'select', 2 );
      if ( jQuery( "#currency-list" ).accordion( "option", "active" ) === false ) {
        jQuery( "#currency-list" ).accordion( "option", "active", 0 );
      }
      jQuery( error_field ).focus();
      return false;
    }

  } );

  /**
   * Confirm complete removal of WPI databases
   */
  jQuery( '#delete_all_wp_invoice_databases' ).click( function () {
    var txt = 'Are you sure you want to delete all the databases?  All your invoice and log data will be lost forever. ';
    jQuery.prompt( txt, {buttons: {Delete: true, Cancel: false}, callback: function ( v, m ) {
      if ( v ) {
        document.location = "admin.php?page=new_invoice&wp_invoice_action=complete_removal";
      }
    }
    } );
    return false;
  } );

  /**
   * Invoice overview table sorting and filtering
   */
  var tog = false; // or true if they are checked on load
  jQuery( '#invoice_sorter_table #CheckAll' ).click( function () {
    jQuery( "#invoice_sorter_table input[type=checkbox]" ).attr( "checked", !tog );
    tog = !tog;
  } );

  jQuery( "#invoice_sorter_table tr:has(td)" ).each( function () {
    var t = jQuery( this ).text().toLowerCase(); //all row text
    jQuery( "<td class='indexColumn'></td>" ).hide().text( t ).appendTo( this );
  } );

  /**
   * User ssearch
   */
  jQuery( ".invoice-search-input" ).keyup( function () {

    var s = jQuery( this ).val().toLowerCase().split( " " );

    jQuery( "#invoice_sorter_table tr:hidden" ).show();
    jQuery.each( s, function () {
      jQuery( "#invoice_sorter_table tr:visible .indexColumn:not(:contains('" + this + "'))" ).parent().hide();
    } );
  } );

  /**
   * Filter by recipients
   */
  jQuery( "#wpi_filter_overview_by_recipient" ).change( function () {
    var target_url = jQuery( "#wpi_target_url" ).val();
    window.location = target_url + "&recipient_filter=" + jQuery( this ).val();
  } );

  /**
   * Toggle display of archived invoices
   * Recalc amount owed.
   */
  jQuery( "#wp_invoice_show_archived" ).click( function () {
    jQuery( ".wp_invoice_archived" ).toggle();
    wp_invoice_calculate_owed();
    return false;
  } )

  /**
   * Perform bulk delete action
   */
  jQuery( "#submit_bulk_action" ).click( function () {
    if ( jQuery( "#wp_invoice_action :selected" ).text() == 'Delete' ) {
      var r = confirm( "Are you sure you want to delete the selected invoice(s)?" );
      if ( r == true ) {
        return true;
      } else {
        return false;
      }
    }
  } );

  /**
   * Do not submit invoice filter if no action is selected
   */
  jQuery( "#invoices-filter" ).submit( function () {
    if ( jQuery( "#invoices-filter select" ).val() == '-1' )
      return false;
  } )

  /**
   * Event date & time
   * @type Date
   */
  var curDate = new Date();
  var m = curDate.getMonth() + 1;
  jQuery( ".wpi_event_date" ).val( (m < 10 ? "0" + m : m) + "/" + (curDate.getDate() < 10 ? "0" + curDate.getDate() : curDate.getDate()) + "/" + curDate.getFullYear() ).datepicker();
  var h = curDate.getHours() < 10 ? "0" + curDate.getHours() : curDate.getHours();
  var minutes = curDate.getMinutes() < 10 ? "0" + curDate.getMinutes() : curDate.getMinutes();
  jQuery( ".wpi_event_time" ).val( h + ":" + minutes );

  //** Invoice link expanding */
  jQuery( '#edit-slug-box.wpi-edit-slug-box' ).click( function () {
    jQuery( this ).css( {height: function () {
      return jQuery( this ).height() == 18 ? "auto" : 18
    }} );
  } );

  //** event_type_selector */
  jQuery( "#wpi_event_type" ).change( function () {
    if ( jQuery( this ).val() == 'add_charge' ) {
      jQuery( "#event_tax_holder" ).show();
    } else {
      jQuery( "#event_tax_holder" ).hide();
    }
  } );

  /**
   * Permanently deletion confirm
   */
  jQuery( "#wp-list-table" ).on( "click", "a.submitdelete.permanently", function () {
    return confirm( "Remove this invoice permanently?" );
  } );

  /**
   * Bulk actions
   */
  jQuery( "#doaction" ).on( "click", function () {
    var action = jQuery( "select[name=action]" ).val();
    if ( action == 'delete' ) {
      var answer = confirm( "Remove selected invoices permanently?" )
      if ( answer ) {
        return true;
      }
      return false;
    }
    return true;
  } );

  /**
   * Prevent page reloading when list table is clicked
   */
  jQuery( "#wp-list-table th a" ).on( "click", function () {
    return false;
  } );

  //** DataTable check all checkbox */
  jQuery( "input.check-all", "#wp-list-table" ).click( function ( e ) {
    if ( e.target.checked ) {
      jQuery( "#the-list td.cb input:checkbox" ).attr( 'checked', 'checked' );
    } else {
      jQuery( "#the-list td.cb input:checkbox" ).removeAttr( 'checked' );
    }
  } );

  //** GA Track Events options hidding */
  jQuery( "#wpi_wpi_settings_ga_event_tracking_enabled_" ).click( function ( e ) {
    jQuery( this ).parents( 'ul' ).find( 'li.wpi_ga_events_list' ).toggle();
  } );

  //** When enabling recurring billing - disable the ability to set different payment options */
  jQuery( document ).bind('wpi_enable_recurring', function(){
    if ( jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).is(":checked") )
      jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).click();
    jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).parent().hide();
  });

  //** When disabling recurring billing - enable the ability to set different payment options */
  jQuery( document ).bind('wpi_disable_recurring', function(){
    jQuery( "#wpi_wpi_invoice_client_change_payment_method_" ).parent().show();
  });

} );