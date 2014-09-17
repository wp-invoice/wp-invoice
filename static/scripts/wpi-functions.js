/**
 * Toggle advanced options that are somehow related to the clicked trigger
 *
 * If trigger element has an attr of 'show_type_source', then function attempt to find that element and get its value
 * if value is found, that value is used as an additional requirement when finding which elements to toggle
 *
 * Example: <span class="wpi_show_advanced" show_type_source="id_of_input_with_a_string" advanced_option_class="class_of_elements_to_trigger" show_type_element_attribute="attribute_name_to_match">Show Advanced</span>
 * The above, when clicked, will toggle all elements within the same parent tree of cicked element, with class of "advanced_option_class" and with attribute of "show_type_element_attribute" the equals value of "#id_of_input_with_a_string"
 *
 * Clicking the trigger in example when get the value of:
 * <input id="value_from_source_element" value="some_sort_of_identifier" />
 *
 * And then toggle all elements like below:
 * <li class="class_of_elements_to_trigger" attribute_name_to_match="some_sort_of_identifier">Data that will be toggled.</li>
 *
 * Copyright 2011 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 */
function wpi_toggle_advanced_options ( this_element ) {

  var advanced_option_class = false;
  var show_type = false;
  var show_type_element_attribute = false;

  //** Try getting arguments automatically */
  var wrapper = (jQuery( this_element ).attr( 'wrapper' ) ? jQuery( this_element ).closest( '.' + jQuery( this_element ).attr( 'wrapper' ) ) : jQuery( this_element ).parents( '.wpi_dynamic_table_row' ));

  if ( jQuery( this_element ).attr( "advanced_option_class" ) !== undefined ) {
    var advanced_option_class = "." + jQuery( this_element ).attr( "advanced_option_class" );
  }

  if ( jQuery( this_element ).attr( "show_type_element_attribute" ) !== undefined ) {
    var show_type_element_attribute = jQuery( this_element ).attr( "show_type_element_attribute" );
  }

  //** If no advanced_option_class is found in attribute, we default to 'wpi_advanced_option' */
  if ( !advanced_option_class ) {
    advanced_option_class = ".wpi_advanced_option";
  }

  //** If element does not have a table row wrapper, we look for the closts .wpi_something_advanced_wrapper wrapper */
  if ( wrapper.length == 0 ) {
    var wrapper = jQuery( this_element ).parents( '.wpi_something_advanced_wrapper' );
  }

  //** get_show_type_value forces the a look up a value of a passed element, ID of which is passed, which is then used as another conditional argument */
  if ( show_type_source = jQuery( this_element ).attr( "show_type_source" ) ) {
    var source_element = jQuery( "#" + show_type_source );

    if ( source_element ) {
      //** Element found, determine type and get current value */
      if ( jQuery( source_element ).is( "select" ) ) {
        show_type = jQuery( "option:selected", source_element ).val();
      }
    }
  }

  if ( !show_type ) {
    element_path = jQuery( advanced_option_class, wrapper );
  }

  //** Look for advanced options with show type */
  if ( show_type ) {
    element_path = jQuery( advanced_option_class + "[" + show_type_element_attribute + "='" + show_type + "']", wrapper );
  }

  //** Check if this_element element is a checkbox, we assume that we always show things when it is checked, and hiding when unchecked */
  if ( jQuery( this_element ).is( "input[type=checkbox]" ) ) {

    var toggle_logic = jQuery( this_element ).attr( "toggle_logic" );

    if ( jQuery( this_element ).is( ":checked" ) ) {
      if ( toggle_logic == 'reverse' ) {
        jQuery( element_path ).hide();
      } else {
        jQuery( element_path ).show();
      }
    } else {
      if ( toggle_logic == 'reverse' ) {
        jQuery( element_path ).show();
      } else {
        jQuery( element_path ).hide();
      }
    }

    return;

  } else if ( jQuery( this_element ).is( "select" ) ) {

    jQuery( advanced_option_class + "[" + show_type_element_attribute + "]", wrapper ).hide();

    if ( jQuery( this_element ).val() == show_type ) {
      jQuery( element_path ).show();
    } else {
      jQuery( element_path ).hide();
    }

    return;
  }

  jQuery( element_path ).toggle();

}

/*
 * Updates Row field names
 * @param object instance. DOM element
 * @param boolean allowRandomSlug. Determine if Row can contains random slugs.
 */
var wpi_updateRowNames = function ( instance, allowRandomSlug ) {
  if ( typeof instance == 'undefined' ) {
    return false;
  }
  if ( typeof allowRandomSlug == 'undefined' ) {
    var allowRandomSlug = false;
  }

  var this_row = jQuery( instance ).parents( 'tr.wpi_dynamic_table_row' );
  //** Slug of row in question */
  var old_slug = jQuery( this_row ).attr( 'slug' );
  //** Get data from input.slug_setter */
  var new_slug = jQuery( instance ).val();
  //** Convert into slug */
  var new_slug = wpi_create_slug( new_slug );

  //** Don't allow to blank out slugs */
  if ( new_slug == "" ) {
    if ( allowRandomSlug ) {
      new_slug = 'random_' + Math.floor( Math.random() * 1000 );
    } else {
      return;
    }
  }

  //** If slug input.slug exists in row, we modify it */
  jQuery( ".slug", this_row ).val( new_slug );
  //** Update row slug */
  jQuery( this_row ).attr( 'slug', new_slug );

  //** Cycle through all child elements and fix names */
  jQuery( 'input,select,textarea', this_row ).each( function ( element ) {
    var old_name = jQuery( this ).attr( 'name' );
    var new_name = old_name.replace( old_slug, new_slug );
    var old_id = jQuery( this ).attr( 'id' );
    var new_id = old_id.replace( old_slug, new_slug );
    //** Update to new name */
    jQuery( this ).attr( 'name', new_name );
    jQuery( this ).attr( 'id', new_id );
  } );

  //** Cycle through labels too */
  jQuery( 'label', this_row ).each( function ( element ) {
    var old_for = jQuery( this ).attr( 'for' );
    var new_for = old_for.replace( old_slug, new_slug );
    //** Update to new name */
    jQuery( this ).attr( 'for', new_for );
  } );
}

/**
 * Create slug
 */
function wpi_create_slug ( slug ) {

  slug = slug.replace( /[^a-zA-Z0-9_\s]/g, "" );
  slug = slug.toLowerCase();
  slug = slug.replace( /\s/g, '_' );

  return slug;
}

/**
 * Add row
 */
function wpi_add_row ( element ) {
  var auto_increment = false;
  var table = jQuery( element ).parents( '.ud_ui_dynamic_table' );
  var table_id = jQuery( table ).attr( "id" );

  //** Determine if table rows are numeric */
  if ( jQuery( table ).attr( 'auto_increment' ) == 'true' ) {
    var auto_increment = true;
  } else if ( jQuery( table ).attr( 'allow_random_slug' ) == 'true' ) {
    var allow_random_slug = true;
  }

  //** Clone last row */
  var cloned = jQuery( ".wpi_dynamic_table_row:last", table ).clone();

  //** Insert new row after last one */
  jQuery( cloned ).appendTo( table );

  //** Get Last row to update names to match slug */
  var added_row = jQuery( ".wpi_dynamic_table_row:last", table );

  //** Display row just in case */
  jQuery( added_row ).show();

  //** Blank out all values */
  jQuery( "textarea", added_row ).val( '' );
  jQuery( "input[type=text]", added_row ).val( '' );
  jQuery( "input[type=checkbox]", added_row ).attr( 'checked', false );
  jQuery( "textarea:disabled,input[type=text]:disabled,input[type=checkbox]:disabled", added_row ).removeAttr( 'disabled' );

  //** Increment name value automatically */
  if ( auto_increment ) {
    //** Cycle through all child elements and fix names */
    jQuery( 'input,select,textarea', added_row ).each( function ( element ) {
      var old_name = jQuery( this ).attr( 'name' );

      var matches = old_name.match( /\[(\d{1,2})\]/ );
      var old_count = false;
      var new_count = false;
      if ( matches ) {
        old_count = parseInt( matches[1] );
        new_count = (old_count + 1);
      }
      var new_name = old_name.replace( '[' + old_count + ']', '[' + new_count + ']' );

      //** Update to new name */
      jQuery( this ).attr( 'name', new_name );

    } );
  } else if ( allow_random_slug ) {
    //** Update Row names */
    var slug_setter = jQuery( "input.slug_setter", added_row );
    if ( slug_setter.length > 0 ) {
      wpi_updateRowNames( slug_setter.get( 0 ), true );
    }
  }

  //** Unset 'new_row' attribute */
  jQuery( added_row ).attr( 'new_row', 'true' );

  //** Focus on new element */
  jQuery( 'input.slug_setter', added_row ).focus();

  return added_row;

}

/**
 * Display error
 */
function wpi_show_error ( message ) {
  jQuery( '#wpi_single_error' ).remove();
  jQuery( "<div id='wpi_single_error' class='error fade below-h2'><p>" + message + "</p></div>" ).insertAfter( '.wrap h2' );
}

/**
 * Displays success
 */
function wpi_show_success ( message ) {
  jQuery( '#message' ).remove();
  jQuery( "<div id='message' class='updated below-h2'><p>" + message + "</p></div>" ).insertAfter( '.wrap h2' );
}

/**
 * Hide all errors
 */
function wpi_remove_errors ( message ) {
  jQuery( '#wpi_single_error' ).remove();
}

/**
 * Validates email address
 */
function wpi_validate_email ( address ) {
  var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
  if ( reg.test( address ) == false ) {
    return false;
  } else {
    return true;
  }
}

/**
 * Update user option
 */
function wpi_update_user_option ( meta_key, meta_value ) {
  jQuery.post( ajaxurl, {
    action: 'wpi_update_user_option',
    meta_key: meta_key,
    meta_value: meta_value
  }, function ( data ) {
  } );
}

/**
 * Turn off quote. Usually happens when activating recurring.
 */
function wpi_disable_quote () {
  jQuery( '.wpi_wpi_invoice_quote_' ).attr( "checked", false );
}

/**
 * Turn on quote option.
 */
function wpi_enable_quote () {
  jQuery( '.wpi_quote_option' ).show();
  jQuery( '.wpi_wpi_invoice_quote_' ).attr( "checked", true );
}

/**
 * Fixes the payment/charge/adjustment dropdown to only allow for
 * accepting charges.  This way we don't overcomplicate things with recurring billing.
 */
function wpi_toggle_wpi_event_type () {
  if ( is_recurring ) {
    jQuery( "#wpi_event_type" ).val( 2 );
    jQuery( "#wpi_event_type" ).attr( "disabled", "disabled" );
  } else {
    jQuery( "#wpi_event_type" ).removeAttr( "disabled" );
  }
}

/**
 * Turns ON deposit options on invoice
 */
function wpi_enable_deposit () {
  //** Recurring invoice */
  wpi_disable_quote();
  wpi_disable_recurring();
  wpi_hide_recurring_option();
  wpi_hide_quote_option();
  jQuery( '.wpi_deposit_settings' ).show();
}
/**
 * TurnsOFF deposit options on invoice
 */
function wpi_disable_deposit () {
  wpi_show_recurring_option();
  wpi_show_quote_option();
  jQuery( '.wpi_deposit_settings' ).hide();
  jQuery( '.wpi_deposit_settings input' ).val( "" );
}
/*
 Hides deposit options on invoice
 */
function wpi_hide_deposit_option () {
  jQuery( '.wpi_hide_deposit_option' ).hide();
}
/*
 Show deposit options on invoice
 */
function wpi_show_deposit_option () {
  jQuery( '.wpi_hide_deposit_option' ).show();
}
/*
 Hides quote options on invoice
 */
function wpi_hide_quote_option () {
  jQuery( '.wpi_quote_option' ).hide();
}
/*
 Show quote options on invoice
 */
function wpi_show_quote_option () {
  jQuery( '.wpi_quote_option' ).show();
}
/*
 Turns ON recurring billing on invoice
 */
function wpi_enable_recurring () {
  // Recurring invoice
  wpi_disable_quote();
  wpi_disable_deposit();
  wpi_hide_deposit_option();

  /* Hide quote option */
  wpi_hide_quote_option();

  jQuery( '.wpi_recurring_options' ).show();
  jQuery( '.wpi_turn_off_recurring' ).show();
  jQuery( ".wpi_recurring_bill_settings" ).show();
  jQuery( ".wpi_not_for_recurring" ).hide();

  jQuery( document ).trigger('wpi_enable_recurring');

  is_recurring = true;
  wpi_toggle_wpi_event_type();
  wpi_recalc_totals();
}

/**
 * Toggle recurring start date
 */
function wpi_enable_recurring_start_date( type ) {
  jQuery( "#wpi_wpi_invoice_recurring_send_invoice_automatically_"+type ).removeAttr( "checked" );
  jQuery( ".wpi_recurring_start_date."+type ).show();
}
function wpi_disable_recurring_start_date( type ) {
  jQuery( "#wpi_wpi_invoice_recurring_send_invoice_automatically_"+type ).attr( "checked", "checked" );
  jQuery( ".wpi_recurring_start_date."+type ).hide();
}
/*
 Hide recurring option on invoice
 */
function wpi_hide_recurring_option () {
  jQuery( ".wpi_turn_off_recurring" ).hide();
}
/*
 Show recurring option on invoice
 */
function wpi_show_recurring_option () {
  jQuery( ".wpi_turn_off_recurring" ).show();
}
/*
 Turns off recurring billing on invoice
 */
function wpi_disable_recurring () {
  jQuery( '.wpi_invoice_adjustment_event_type_row' ).hide();
  jQuery( '.wpi_recurring_options' ).hide();
  jQuery( '.wpi_wpi_invoice_meta_recurring_active_' ).attr( 'checked', false );
  jQuery( '.wpi_turn_off_recurring input' ).attr( "checked", false );
  jQuery( '.wpi_recurring_bill_settings' ).val( '' );
  jQuery( '.wpi_recurring_bill_settings input' ).attr( 'checked', false );
  jQuery( '.wpi_recurring_bill_settings' ).hide();
  jQuery( ".wpi_not_for_recurring" ).show();
//    jQuery(".wpi_not_for_deposit").show();

  jQuery( document ).trigger('wpi_disable_recurring');

  is_recurring = false;
  wpi_recalc_totals();
  wpi_toggle_wpi_event_type();
}
/*
 Displays payment charge box
 */
function wpi_show_paycharge_box () {
  jQuery( '#postbox_status_and_history' ).show();
  jQuery( '#wpi_enter_payments' ).toggle();
  jQuery( '#wpi_enter_payments .input_field' ).val( '' );
  if ( jQuery( '#wpi_enter_payments' ).is( ":visible" ) ) {
    jQuery( '#wpi_enter_payments' ).parent().css( 'max-height', '286px' );
  } else {
    jQuery( '#wpi_enter_payments' ).parent().css( 'max-height', '150px' );
  }
  // Clear out fields
}
/*
 Process event from payment charge box
 */
function wpi_process_manual_event () {
  var event_data;

  event_data = {
    action: "wpi_process_manual_event",
    nonce: jQuery( '#wpi_process_manual_event_nonce' ).val(),
    invoice_id: jQuery( '#wpi_invoice_id' ).val(),
    event_type: jQuery( '#wpi_event_type' ).val(),
    event_amount: jQuery( '#wpi_event_amount' ).val(),
    event_tax: jQuery( '#wpi_event_tax' ).val(),
    event_note: jQuery( '#wpi_event_note' ).val(),
    event_date: jQuery( '.wpi_event_date' ).val(),
    event_time: jQuery( '.wpi_event_time' ).val()
  };

  jQuery.ajax( {
    dataType: "json",
    data: event_data,
    beforeSend: function () {
    },
    type: "POST",
    url: ajaxurl,
    success: function ( data ) {

      if ( data.success == "true" ) {

        jQuery( '#wpi_event_amount' ).val( '' );
        jQuery( '#wpi_event_note' ).val( '' );
        jQuery( '#wpi_event_tax' ).val( '' );
        jQuery( '#wpi_event_type' ).val( 'add_payment' );
        jQuery( "#event_tax_holder" ).hide();
        jQuery( "#ajax-response" ).show();
        jQuery( "#ajax-response p" ).html( data.message );

        wpi_update_status_box();
        wpi_update_charges_list();

        // Recalculate totals if adjustment is monetary
        if ( event_data.event_type == 'add_payment' ) {
          window.adjustments -= event_data.event_amount;
        }
        if ( event_data.event_type == 'do_adjustment' ) {
          window.adjustments -= event_data.event_amount;
        }

        wpi_recalc_totals();

      } else {
        jQuery( ".wpi_ajax_response" ).addClass( 'wpi_error' );
        jQuery( ".wpi_ajax_response" ).html( data.message );
      }

    }


  } );

}
/*
 Displays email notification metabox
 */
function wpi_show_notification_box () {
  jQuery( '#send_notification_box' ).toggle();
  if ( jQuery( '#send_notification_box' ).is( ":visible" ) ) {
  } else {
  }
}
/*
 Insert a new line of pre-defined services.
 Triggered by .onchange event on dropdown
 */
function wpi_insert_predefined_service () {
  add_itemized_list_row( 'invoice_list' );
  var option_value = jQuery( '#wpi_predefined_services option:selected' ).val();
  option_value = option_value.split( "|" );
  var name = option_value[0];
  var description = option_value[1];
  var quantity = option_value[2];
  var price = option_value[3];
  var tax = option_value[4];

  /* Clean Global tax if values are different */
  var global_tax = jQuery( '#wp_invoice_tax' ).val();
  if ( global_tax > 0 && tax != global_tax ) {
    jQuery( '#wp_invoice_tax' ).val( '' );
  }

  jQuery( '#invoice_list li.wp_invoice_itemized_list_row:last input.item_name' ).val( name );
  jQuery( '#invoice_list li.wp_invoice_itemized_list_row:last input.item_quantity' ).val( quantity );
  jQuery( '#invoice_list li.wp_invoice_itemized_list_row:last input.item_price' ).val( price );
  jQuery( '#invoice_list li.wp_invoice_itemized_list_row:last input.line_tax_item' ).val( tax );
  // Show description if one is iset
  if ( description != '' ) {
    jQuery( "#invoice_list li.wp_invoice_itemized_list_row:last .item_description" ).show();
    jQuery( '#invoice_list li.wp_invoice_itemized_list_row:last .item_description' ).val( description );
  }
  jQuery( "#wpi_predefined_services" ).val( 1 );
  wpi_recalc_totals();
}
/*
 Load email notification via ajax and display on the invoice page for sending
 */
function wpi_load_email_notification () {
  var selected = jQuery( "#wpi_change_notification option:selected" ).val();
  var invoice_id;
  // Get invoice id. If this is a new invoice it will be returned with update message as #new_invoice_id
  // If existing invoice its stored in input field #wpi_invoice_id
  var invoice_id = jQuery( "#wpi_invoice_id" ).val();
  if ( empty( invoice_id ) )
    invoice_id = jQuery( "#new_invoice_id" ).text();
  if ( selected > 0 ) {
    jQuery( "#wpi_template_loading" ).show();
    template_id_val = selected;
    jQuery.getJSON( ajaxurl, {action: 'wpi_get_notification_email', template_id: template_id_val, wpi_invoiceid: invoice_id}, function ( response ) {
      jQuery( '#wpi_notification_message' ).val( response.wpi_content );
      jQuery( '#wpi_notification_subject' ).val( response.wpi_subject );
      jQuery( "#wpi_template_loading" ).hide();
    } );
  } else {
    return;
  }
}
/*
 Send notification to client via ajax
 */
function wpi_send_notification () {
  /* Show the loading box */
  jQuery( "#wpi_template_loading" ).show();
  jQuery.ajax( {
    data: {
      action: "wpi_send_notification",
      subject: jQuery( "#wpi_notification_subject" ).val(),
      invoice_id: jQuery( "#wpi_invoice_id" ).val(),
      template: jQuery( "#wpi_change_notification option:selected" ).text(),
      to: jQuery( "#wpi_notification_send_to" ).val(),
      body: jQuery( "#wpi_notification_message" ).val()
    },
    type: "POST",
    url: ajaxurl,
    success: function ( data ) {
      if ( data.status == 200 ) {
        wpi_show_success( data.msg );
      } else {
        wpi_show_error( data.msg );
      }
      //Hide the box
      jQuery( "#send_notification_box" ).hide();
      // Hide the AJAX div
      jQuery( "#wpi_template_loading" ).hide();
      // Update status box
      wpi_update_status_box();
      // Scroll to the top
      jQuery( 'html, body' ).animate( {scrollTop: 0}, 'slow' );
    },
    dataType: 'json'
  } );
}

function checkdate ( input ) {
  var validformat = /^\d{2}\/\d{2}\/\d{4}$/; //Basic check for format validity
  if ( !validformat.test( input ) ) {
    return false;
  } else { //Detailed check for valid date ranges
    var monthfield = input.split( "/" )[0];
    var dayfield = input.split( "/" )[1];
    var yearfield = input.split( "/" )[2];
    var dayobj = new Date( yearfield, monthfield - 1, dayfield );
    if ( (dayobj.getMonth() + 1 != monthfield) || (dayobj.getDate() != dayfield) || (dayobj.getFullYear() != yearfield) ) {
      return false;
    } else {
      return true;
    }
  }
}

function wpi_validate_invoice () {
  var validated = true;

  if ( jQuery( '[name^="wpi_invoice[subject]"]' ).val() == '' ) {
    jQuery( '[name^="wpi_invoice[subject]"]' ).addClass( 'wpi_error' );
    validated = false;
  } else {
    jQuery( '[name^="wpi_invoice[subject]"]' ).removeClass( 'wpi_error' );
  }

  jQuery( "input.item_name:visible", "#charges_list" ).each( function ( k, v ) {
    jQuery( v ).removeClass( 'wpi_error' );
    if ( empty( jQuery( v ).val() ) ) {
      jQuery( v ).addClass( 'wpi_error' );
      validated = false;
    }
  } );
  jQuery( "input.item_amount:visible", "#charges_list" ).each( function ( k, v ) {
    jQuery( v ).removeClass( 'wpi_error' );
    if ( empty( jQuery( v ).val() ) ) {
      jQuery( v ).addClass( 'wpi_error' );
      validated = false;
    }
  } );
  jQuery( "input.item_charge_tax:visible", "#charges_list" ).each( function ( k, v ) {
    jQuery( v ).removeClass( 'wpi_error' );
    if ( isNaN( jQuery( v ).val() ) || jQuery( v ).val() < 0 ) {
      jQuery( v ).addClass( 'wpi_error' );
      validated = false;
    }
  } );

  return validated;
}

function wpi_save_invoice () {
  var invoice_data;
  // primary data to set things up
  invoice_data = {
    action: "wpi_save_invoice",
    nonce: jQuery( '#wpi-update-invoice' ).val()
  };
  // select all input fields
  jQuery( '[name^="wpi_invoice"]' ).each( function () {
    invoice_data[this.name] = this.value;
    if ( jQuery( this ).is( ':checkbox' ) ) {
      if ( jQuery( this ).is( ':checked' ) ) {
        invoice_data[this.name] = 'on';
      } else {
        invoice_data[this.name] = 'off';
      }
    }
  } );
  // Get data from MCE Editor
  invoice_data['wpi_invoice[description]'] = jQuery( 'textarea[name="content"]' ).val();

  jQuery.ajax( {
    data: invoice_data,
    beforeSend: function () {
    },
    type: "POST",
    url: ajaxurl,
    success: function ( data ) {
      jQuery( "#ajax-response" ).show();
      jQuery( "#ajax-response p" ).html( data );
      // Show invoice message box
      // Show invoice log
      jQuery( "#send_notification_box" ).show();
      jQuery( "#postbox_status_and_history" ).show();
      jQuery( ".wpi_hide_until_saved" ).show();

      /* If data contains invoice's premalink, we update it in editor */
      var dom = "<div>" + data + "</div>";
      var a = jQuery( "a", dom );

      if ( a.length > 0 ) {

        var permalink = a.attr( 'href' );
        if ( jQuery( '#sample-permalink' ).length > 0 ) {
          jQuery( '#sample-permalink' ).html( permalink );
        }

      }

      jQuery( "a.wpi_update_with_invoice_url" ).each( function () {
        var url_annex;

        if ( jQuery( this ).attr( 'url_annex' ) ) {
          url_annex = jQuery( this ).attr( 'url_annex' );
        } else {
          url_annex = '';
        }

        jQuery( this ).attr( 'href', permalink + url_annex );

      } );

      // Update status box
      wpi_update_status_box();
    }
  } );

  // Hide Payment postbox if quote checkbox is checked
  if ( jQuery( "#wpi_wpi_invoice_quote_" ).is( ':checked' ) ) {
    jQuery( "#postbox_payment_methods" ).hide();
  } else {
    jQuery( "#postbox_payment_methods" ).show();
  }

  // Enter values into send notification box
  if ( empty( jQuery( "#wpi_notification_send_to" ).val() ) ) {
    jQuery( "#wpi_notification_send_to" ).val( jQuery( "#wpi_user_email" ).text() );
  }
  if ( empty( jQuery( "#wpi_notification_subject" ).val() ) ) {
    jQuery( "#wpi_notification_subject" ).val( jQuery( "#title" ).val() );
  }
  return false;
}

/*
 Update invoice history status box via ajax call
 */
function wpi_update_status_box () {
  jQuery.post( ajaxurl, {
    action: 'wpi_get_status',
    invoice_id: jQuery( '#wpi_invoice_id' ).val()
  }, function ( data ) {
    jQuery( "#wpi_invoice_status_table" ).html( data );
    if ( is_recurring )
      jQuery( ".wpi_not_for_recurring" ).hide();
  } );
}

/*

 */
function wpi_update_charges_list () {
  jQuery.post( ajaxurl, {
    action: 'wpi_get_charges',
    invoice_id: jQuery( '#wpi_invoice_id' ).val()
  }, function ( data ) {
    jQuery( "#charges_list .wp_invoice_itemized_charge_row" ).remove();
    jQuery( "#charges_list" ).append( data ).show();
    wpi_recalc_totals();
  } );
}

/*
 Adjusts UI widths dependong on if we are using itemized tax or not
 Also toggles .row_tax elements, which apply to header and tax input box span.
 Totals have to be recalculated in the end to reflect no line tax.
 */
function wpi_adjust_for_tax_column ( action ) {
  if ( action == 'show' ) {
    flexible_width_holder_content = '300px'
    fixed_width_holder = '280px'
    jQuery( ".row_tax" ).show();
  } else {
    flexible_width_holder_content = '250px'
    fixed_width_holder = '240px'
    /* Set global Tax value as default */
    if ( jQuery( '#wp_invoice_tax' ).length > 0 ) {
      jQuery( ".row_tax input" ).val( jQuery( '#wp_invoice_tax' ).val() );
      jQuery( ".row_charge_tax input" ).val( jQuery( '#wp_invoice_tax' ).val() );
    } else {
      jQuery( ".row_tax input" ).val( '' );
    }
    jQuery( ".row_tax" ).hide();
  }
  jQuery( ".header .flexible_width_holder_content, .wp_invoice_itemized_list_row .flexible_width_holder_content" ).css( 'margin-right', flexible_width_holder_content );
  jQuery( ".header .fixed_width_holder, .wp_invoice_itemized_list_row .fixed_width_holder" ).css( 'width', fixed_width_holder );
  wpi_recalc_totals();
}
function wp_invoice_add_time ( target, add_days ) {
  if ( add_days == 'clear' ) {
    jQuery( "#wpi_invoice_form #" + target + "_mm" ).val( '' );
    jQuery( "#wpi_invoice_form #" + target + "_jj" ).val( '' );
    jQuery( "#wpi_invoice_form #" + target + "_aa" ).val( '' );
  } else {
    var myDate = new Date();
    var week_from_now = new Date( myDate.getTime() + add_days * 24 * 60 * 60 * 1000 );
    ;
    var month = week_from_now.getMonth() + 1;

    // make month two digit
    month = (month < 10 ? '0' : '') + month;
    var day = (week_from_now.getDate() < 10 ? '0' : '') + week_from_now.getDate();

    jQuery( "#wpi_invoice_form #" + target + "_mm" ).val( month );
    jQuery( "#wpi_invoice_form #" + target + "_jj" ).val( day );
    jQuery( "#wpi_invoice_form #" + target + "_aa" ).val( week_from_now.getFullYear() );
  }
  return false;
}
/*
 Calculates total owed based on displayed invoices on invoice overview page
 */
function wp_invoice_calculate_owed () {
  alert( 'running wp_invoice_calculate_owed, obsolete?' );
  jQuery( "#wp_invoice_total_owed" ).html( jQuery( "#invoice_sorter_table tr:visible .row_money" ).sum() );
  jQuery( "#wp_invoice_total_owed" ).formatCurrency( {useHtml: true} );
}

/*function wpi_load_email_template() {
 if(confirm('Do you want to restore the WP-Invoice generated message?')) {
 jQuery("#wp_invoice_email_message_content").val(jQuery("#wp_invoice_email_message_content_original").val());
 }
 }*/

function wp_invoice_cancel_recurring () {
  jQuery( "#wp_invoice_subscription_name" ).val( '' );
  jQuery( "#wp_invoice_subscription_unit" ).val( '' );
  jQuery( "#wp_invoice_subscription_length" ).val( '' );
  jQuery( "#wp_invoice_subscription_start_month" ).val( '' );
  jQuery( "#wp_invoice_subscription_start_day" ).val( '' );
  jQuery( "#wp_invoice_subscription_start_year" ).val( '' );
  jQuery( "#wp_invoice_subscription_total_occurances" ).val( '' );
  //jQuery(".wp_invoice_enable_recurring_billing").toggle();
  jQuery( "#wp_invoice_enable_recurring_billing" ).toggle();
  jQuery( ".wp_invoice_enable_recurring_billing" ).toggle();
}

function wp_invoice_subscription_start_time ( add_days ) {
  function formatNum ( num ) {
    var mynum = num * 1;
    var retVal = mynum < 10 ? '0' : '';
    return (retVal + mynum)
  }

  if ( add_days == 'clear' ) {
    jQuery( "#wp_invoice_subscription_start_month" ).val( '' );
    jQuery( "#wp_invoice_subscription_start_day" ).val( '' );
    jQuery( "#wp_invoice_subscription_start_year" ).val( '' );
  } else {
    myDate = new Date();
    var week_from_now = new Date( myDate.getTime() + add_days * 24 * 60 * 60 * 1000 );
    ;
    month = week_from_now.getMonth() + 1;
    jQuery( "#wp_invoice_subscription_start_month" ).val( formatNum( month ) );
    jQuery( "#wp_invoice_subscription_start_day" ).val( week_from_now.getDate() );
    jQuery( "#wp_invoice_subscription_start_year" ).val( week_from_now.getFullYear() );
  }
  return false;
}

function wpi_disable_all_payment_methods () {
  // uncheck app payment method checkboxes
  jQuery( '.wpi_billing_section_show' ).attr( 'checked', false );
  // blank out all billing venue default_payment settings
  jQuery( ".billing-default-option" ).val( '' );
  // hide all accordion sections
  jQuery( ".wp_invoice_accordion .wp_invoice_accordion_section" ).hide();
  // Hide all notices that reappear when checkbox is checked
  jQuery( '.wpi_notice' ).hide();
}

function wpi_init_payment_method () {
  // cycle through checked methods and run the wpi_select_payment_method which will either turn them on or off
  jQuery( '.wpi_billing_section_show' ).each( function () {
    wpi_select_payment_method( jQuery( this ).attr( 'id' ), false, true );
  } );
  // if client cannot change payment method, we hide payment method checkboxes
  wpi_can_client_change_payment_method()
}

function wpi_can_client_change_payment_method () {
  if ( jQuery( '.wpi_client_change_payment_method' ).is( ":not(:checked)" ) ) {
    // hide all payment selection checkboxes
    jQuery( ".wpi-payment-setting" ).hide();
    // hide all payment options
    wpi_disable_all_payment_methods();
    // display only the default selected payment option
    wpi_select_payment_method( jQuery( '#wp_invoice_payment_method option:selected' ).val(), true, true )
  } else {
    jQuery( ".wpi-payment-setting" ).show();
  }
}

function wpi_select_payment_method ( method, force, init ) {
  var method_checked;
  // force set to true means we check the checkbox for this repsective method automatically
  if ( force ) jQuery( "#" + method + ".wpi_billing_section_show" ).attr( 'checked', true );
  if ( jQuery( "#" + method + ".wpi_billing_section_show" ).is( ":checked" ) ) {
    method_checked = true
  } else {
    method_checked = false;
  }
  if ( method_checked ) {
    // checked, we are turning payment method on
    // Set all "default payment" values to nothing
    jQuery( ".billing-default-option" ).val( '' );
    // enter in billing venue option to set it to default
    jQuery( ".billing-" + method + "-default-option" ).val( 'true' );
    // display accordion secttion if its not displayed
    jQuery( "div." + method + "-setup-section" ).show();
    // activate accordion section unless this function was ran at initilization
    if ( !init ) {
      jQuery( '.wp_invoice_accordion' ).accordion( 'option', 'active', '#' + method + '-setup-section-header' );
    }
    jQuery( "#" + method + ".wpi_billing_section_show" ).parent().parent().find( '.wpi_notice' ).show();
    jQuery( "#" + method + ".wpi_billing_section_show" ).parent().parent().find( '.wpi_notice' ).animate( {backgroundColor: 'lightyellow'}, 1000 );
  } else {
    // not checked, we are turning this thing off
    // enter in billing venue option to set it to default
    jQuery( ".billing-" + method + "-default-option" ).val( '' );
    // display accordion secttion if its not displayed
    jQuery( "div." + method + "-setup-section" ).hide();
    // this is being hidden, so we activate the default
    if ( !init ) jQuery( '.wp_invoice_accordion' ).accordion( 'option', 'active', '#' + jQuery( '#wp_invoice_payment_method option:selected' ).val() + '-setup-section-header' );
    jQuery( "#" + method + ".wpi_billing_section_show" ).parent().parent().find( '.wpi_notice' ).hide();
  }
}

function wpi_focus_payment_method ( method ) {
  jQuery( '.ui-accordion' ).accordion( 'option', 'active', '#' + method + '-setup-section-header' );
}
// initlizes delegate function
jQuery.delegate = function ( rules ) {
  return function ( e ) {
    var target = jQuery( e.target );
    for ( var selector in rules )
      if ( target.is( selector ) ) return rules[selector].apply( this, jQuery.makeArray( arguments ) );
  }
}

function add_itemized_list_row ( where ) {
  var lastRow = jQuery( '#' + where + ' .wp_invoice_itemized_list_row:last' ).clone();
  var id = parseInt( jQuery( '.id', lastRow ).html() ) + 1;
  jQuery( '.id', lastRow ).html( id );
  if ( where == 'invoice_list' ) {
    // Update items if this is an itemized list
    jQuery( '.item_name', lastRow ).attr( 'name', 'wpi_invoice[itemized_list][' + id + '][name]' );
    jQuery( '.item_description', lastRow ).attr( 'name', 'wpi_invoice[itemized_list][' + id + '][description]' );
    jQuery( '.row_quantity input', lastRow ).attr( 'name', 'wpi_invoice[itemized_list][' + id + '][quantity]' );
    jQuery( '.row_price input', lastRow ).attr( 'name', 'wpi_invoice[itemized_list][' + id + '][price]' );
    jQuery( '.row_tax input', lastRow ).attr( 'name', 'wpi_invoice[itemized_list][' + id + '][tax]' );
    jQuery( '.row_total', lastRow ).attr( 'id', 'total_item_' + id + '' );
  }
  if ( where == 'wpi_predefined_services_div' ) {
    // Update items if this is a predefined services itemized list
    jQuery( '.item_name', lastRow ).attr( 'name', 'wpi_settings[predefined_services][' + id + '][name]' );
    jQuery( '.item_description', lastRow ).attr( 'name', 'wpi_settings[predefined_services][' + id + '][description]' );
    jQuery( '.row_quantity input', lastRow ).attr( 'name', 'wpi_settings[predefined_services][' + id + '][quantity]' );
    jQuery( '.row_price input', lastRow ).attr( 'name', 'wpi_settings[predefined_services][' + id + '][price]' );
    jQuery( '.row_tax input', lastRow ).attr( 'name', 'wpi_settings[predefined_services][' + id + '][tax]' );
    jQuery( '.row_total', lastRow ).attr( 'id', 'total_item_' + id + '' );
  }
  // Clear out all old values
  jQuery( 'input, textarea', lastRow ).val( '' );
  jQuery( '.row_total', lastRow ).text( '0' );
  // Add global tax if value is set
  if ( jQuery( "#wp_invoice_tax" ).val() != '' ) {
    global_tax = jQuery( "#wp_invoice_tax" ).val();
    jQuery( '.row_tax input', lastRow ).val( global_tax );
  }
  jQuery( '#' + where + ' li.wp_invoice_itemized_list_row:last' ).after( lastRow );
  return false;
}

/*
 * Adds Discount Row
 */
function add_itemized_list_row_discount () {
  // Count number of discount rows
  discount_count = jQuery( ".wp_invoice_discount_row" ).size();
  discounts_hidden = (jQuery( ".wp_invoice_discount_row:last" ).is( ":visible" ) ? false : true);
  // Prevent user from adding multiple percentage discounts, and mixing and matching percentages and amount discounts

  if ( discounts_hidden ) {
    jQuery( ".wp_invoice_discount_row:last" ).show();
  } else {
    // clone last row
    var lastRow = jQuery( '#invoice_list .wp_invoice_discount_row:last' ).clone();
    var id = parseInt( jQuery( '.id', lastRow ).html() ) + 1;
    jQuery( '.id', lastRow ).html( id );
    // Update items if this is an itemized list
    jQuery( '.item_name', lastRow ).attr( 'name', 'wpi_invoice[meta][discount][' + id + '][name]' );
    jQuery( '.item_amount', lastRow ).attr( 'name', 'wpi_invoice[meta][discount][' + id + '][amount]' );
    jQuery( '.item_type', lastRow ).attr( 'name', 'wpi_invoice[meta][discount][' + id + '][type]' );
    // Clear out all old values
    jQuery( 'input', lastRow ).val( '' );
    jQuery( '#invoice_list li.wpi_invoice_totals' ).before( lastRow );
  }
  return false;
}

this.tooltip = function () {
  /* CONFIG */
  xOffset = 10;
  yOffset = 20;
  // these 2 variable determine popup's distance from the cursor
  // you might want to adjust to get the right result
  /* END CONFIG */
  jQuery( ".wp_invoice_tooltip" ).hover( function ( e ) {
    this.t = this.title;
    this.title = "";
    jQuery( "body" ).append( "<p id='wp_invoice_tooltip'>" + this.t + "</p>" );
    jQuery( "#wp_invoice_tooltip" ).css( "top", (e.pageY - xOffset) + "px" ).css( "left", (e.pageX + yOffset) + "px" ).fadeIn( "fast" );
  }, function () {
    this.title = this.t;
    jQuery( "#wp_invoice_tooltip" ).remove();
  } );
  jQuery( "a.wp_invoice_tooltip" ).mousemove( function ( e ) {
    jQuery( "#tooltip" ).css( "top", (e.pageY - xOffset) + "px" ).css( "left", (e.pageX + yOffset) + "px" );
  } );
};

/*
 * Main function to calculate all the automatically generatd totals on invoice manage page.
 */
function wpi_recalc_totals () {
  // Empty all sums
  var taxable_subtotal = 0;
  var non_taxable_subtotal = 0;
  var tax_percents = [];
  var total = 0;
  var subtotal = 0;
  var total_tax = 0;
  var total_discount = 0;

  // Services itemized list
  jQuery( ".wp_invoice_itemized_list_row" ).each( function ( i ) {
    var row_price = parseFloat( jQuery( ".row_price input", this ).val() );
    row_price = row_price < 0 || isNaN( row_price ) ? '' : row_price;
    var row_quantity = parseFloat( jQuery( ".row_quantity input", this ).val() );
    row_quantity = row_quantity < 0 || isNaN( row_quantity ) ? '' : row_quantity;
    var row_tax = parseFloat( jQuery( ".row_tax input", this ).val() );
    row_tax = row_tax < 0 || isNaN( row_tax ) ? '' : row_tax;

    // Update fields with valid data
    if ( !isNaN( row_price ) )
      jQuery( ".row_price input", this ).val( row_price );
    if ( !isNaN( row_quantity ) )
      jQuery( ".row_quantity input", this ).val( row_quantity );
    if ( !isNaN( row_tax ) )
      jQuery( ".row_tax input", this ).val( row_tax );

    row_price = !isNaN( row_price ) ? row_price : 0;
    row_quantity = !isNaN( row_quantity ) ? row_quantity : 0;
    row_tax = !isNaN( row_tax ) ? row_tax : 0;

    if ( row_tax > 0 && row_price > 0 && row_quantity > 0 ) {
      taxable_subtotal += row_price * row_quantity;
      tax_percents.push( {
        'tax': row_tax,
        'qty': row_quantity,
        'prc': row_price
      } );
      var row_total = (row_price * row_quantity + row_price * row_quantity * row_tax / 100);
    } else {
      non_taxable_subtotal += row_price * row_quantity;
      var row_total = (row_price * row_quantity);
    }

    if ( !row_total ) {
      row_total = 0;
    }

    jQuery( ".row_total", this ).html( row_total );
    jQuery( ".row_total", this ).formatCurrency( {roundToDecimalPlace: 2, useHtml: true} );

  } );

  // Services itemized list
  jQuery( ".wp_invoice_itemized_charge_row" ).each( function ( i ) {
    var row_amount = parseFloat( jQuery( ".row_amount input", this ).val() );
    var row_charge_tax = parseFloat( jQuery( ".row_charge_tax input", this ).val() );

    row_amount = !isNaN( row_amount ) ? row_amount : 0;
    row_charge_tax = !isNaN( row_charge_tax ) ? row_charge_tax : 0;

    if ( row_charge_tax > 0 ) {
      taxable_subtotal += row_amount;
      //tax_percents.push(row_charge_tax);
      tax_percents.push( {
        'tax': row_charge_tax,
        'qty': 1,
        'prc': row_amount
      } );
      var row_total = (row_amount + row_amount * row_charge_tax / 100);
    } else {
      non_taxable_subtotal += row_amount;
      var row_total = row_amount;
    }

    if ( !row_total ) {
      row_total = 0;
    }

    jQuery( ".row_total", this ).html( row_total );
    jQuery( ".row_total", this ).formatCurrency( {roundToDecimalPlace: 2, useHtml: true} );

  } );

  //alert( 'Taxable: '+taxable_subtotal+' / NonTax: '+non_taxable_subtotal );

  var avg_tax = 0;
  for ( var i = 0; i < tax_percents.length; i++ ) {
    avg_tax += tax_percents[i].tax;
  }
  if ( avg_tax > 0 ) {
    avg_tax = avg_tax / tax_percents.length;
  }

  //alert( avg_tax );

  subtotal = taxable_subtotal + non_taxable_subtotal;

  //alert( subtotal );

  // Calculate Discounts if there are any
  jQuery( ".wp_invoice_discount_row:visible" ).each( function ( i ) {
    var name = jQuery( ".item_name", this );
    var type = jQuery( ".item_type select", this );
    var value = jQuery( ".item_price input", this );
    var discount_row_type = type.val();
    var discount_row_value = value.val();
    // Calculate total discount percent (by adding) or discoutn value (also by adding)
    if ( !empty( discount_row_value ) ) {
      discount_row_value = parseFloat( discount_row_value );
      discount_row_value = discount_row_value < 0 || isNaN( discount_row_value ) ? '' : discount_row_value;
      if ( !isNaN( discount_row_value ) )
        value.val( discount_row_value );
      if ( !empty( value.val() ) && !name.val().length ) {
        name.css( {'border-color': 'red'} );
      } else if ( empty( value.val() ) && name.val().length ) {
        value.css( {'border-color': 'red'} );
      } else {
        name.css( {'border-color': ''} );
        value.css( {'border-color': ''} );
      }

      if ( discount_row_type == 'percent' ) {
        total_discount = subtotal * (discount_row_value / 100);
      } else {
        total_discount += discount_row_value;
      }
    } else {
      name.css( {'border-color': ''} );
    }
  } );

  // alert( total_discount );

  var tax_method = jQuery( "#wpi_tax_method" ).val();

  switch ( tax_method ) {
    case 'before_discount':

      jQuery.each( tax_percents, function ( k, v ) {
        total_tax += v.prc / 100 * v.tax * v.qty;
      } );

      //total_tax = taxable_subtotal * avg_tax / 100;

      break;

    case 'after_discount':

      var subtotal_with_discount = subtotal - total_discount;
      var taxable_amount = taxable_subtotal / subtotal * subtotal_with_discount;
      total_tax = taxable_amount * avg_tax / 100;

      break;

    default:

      jQuery.each( tax_percents, function ( k, v ) {
        total_tax += v.prc / 100 * v.tax * v.qty;
      } );

      //total_tax = taxable_subtotal * avg_tax / 100;
      break;
  }

  total = subtotal - total_discount + total_tax;

  if ( total_discount > ( subtotal + total_tax ) && total_discount > 0 ) {
    jQuery( ".wp_invoice_discount_row:visible" ).each( function ( i ) {
      jQuery( ".item_price input", this ).val( '' );
    } );
    wpi_recalc_totals();
    return;
  }

  if ( typeof is_recurring == 'undefined' ) {
    is_recurring = false;
  }

  if ( !empty( window.adjustments ) && window.adjustments != 0 ) {
    jQuery( '.column-invoice-details-adjustments' ).show();
    jQuery( '.column-invoice-details-subtotal' ).show();
    adjustments = parseFloat( adjustments );
    total += adjustments;
  } else {
    jQuery( '.column-invoice-details-adjustments' ).hide();
    if ( window.adjustments == '0' ) {
      adjustments = '0';
    } else {
      // There is no return from payment/charge update. we use our original value
      adjustments = jQuery( ".calculate_invoice_adjustments" ).val();
      // Remove dollar symbol to make math possible
      if ( !empty( adjustments ) ) {
        adjustments = parseFloat( adjustments.replace( "$", "" ) );
      }
    }
  }

  var total_due = total;

  if ( !empty( total_tax ) ) {
    jQuery( '.column-invoice-details-tax' ).show();
    jQuery( '.column-invoice-details-subtotal' ).show();
  } else {
    jQuery( '.column-invoice-details-tax' ).hide();
    // dont hide subtotal it may be displayed of other reasons - adjustments
  }

  if ( !empty( total_discount ) ) {
    jQuery( '.column-invoice-details-discounts' ).show();
  } else {
    jQuery( '.column-invoice-details-discounts' ).hide();
    // dont hide subtotal it may be displayed of other reasons - adjustments
  }

  // Fix total is this is recurring. Note: on recurring bills, adjustments are not added to the balance
  if ( is_recurring ) {
    jQuery( ".column-invoice-details-adjustments" ).hide();
    /*
     * @TODO: There are no any place where discount_amount and tax_weighted_average variables is initialized.
     * Where are these variables from?
     * I commented out code below for the current moment to avoid js errors because of undefined variables.
     * But it should be revised and fixed!!! Maxim Peshkov
     */
    //total_tax = (subtotal  - (!empty(discount_amount) ? discount_amount : 0)) * tax_weighted_average;
    //total_due = subtotal + total_tax - (!empty(discount_amount) ? discount_amount : 0);
  } else {
    // re-evaluate if adjustments should be displayed
    if ( !empty( adjustments ) ) {
      jQuery( '.column-invoice-details-adjustments' ).show();
    }
  }

  jQuery( ".calculate_invoice_adjustments" ).val( adjustments ).formatCurrency( {roundToDecimalPlace: 2} );
  jQuery( ".calculate_invoice_subtotal" ).val( subtotal ).formatCurrency( {roundToDecimalPlace: 2} );
  jQuery( ".calculate_discount_total" ).val( total_discount ).formatCurrency( {roundToDecimalPlace: 2} );
  jQuery( ".calculate_invoice_tax" ).val( total_tax ).formatCurrency( {roundToDecimalPlace: 2} );
  jQuery( ".calculate_invoice_total" ).val( total_due ).formatCurrency( {roundToDecimalPlace: 2} );
}

function empty ( mixed_var ) {
  // http://kevin.vanzonneveld.net
  // +   original by: Philippe Baumann
  // +    input by: Onno Marsman
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +    input by: LH
  // +   improved by: Onno Marsman
  // +   improved by: Francesco
  // +   improved by: Marc Jansen
  // *   example 1: empty(null);
  // *   returns 1: true
  // *   example 2: empty(undefined);
  // *   returns 2: true
  // *   example 3: empty([]);
  // *   returns 3: true
  // *   example 4: empty({});
  // *   returns 4: true
  // *   example 5: empty({'aFunc' : function () { alert('humpty'); } });
  // *   returns 5: false
  var key;
  if ( mixed_var === "" || mixed_var === 0 || mixed_var === "0" || mixed_var === null || mixed_var === false || mixed_var === undefined ) {
    return true;
  }
  if ( typeof mixed_var == 'object' ) {
    for ( key in mixed_var ) {
      return false;
    }
    return true;
  }
  return false;
}
/*
 function SaveFavroiteCountries() {
 var favcountries = "";
 var  i = 0;
 jQuery('select#d option:selected').each(function(){
 if (i == 0) {
 favcountries = jQuery(this).val();
 } else {
 favcountries = favcountries  + ',' + jQuery(this).val();
 }
 i++;
 });
 document.getElementById('hdnFavroiteCountries').value = favcountries;
 }
 */
// This jQuery v1.1.3 plugin will return an delimited
// list of the given attribute of all elements in the
// current jQuery stack.
// Source: http://www.bennadel.com/blog/861-jQuery-Plugin-To-Return-Delimited-Value-List-Of-Stack-Element-Attributes-Follow-Up-.htm
jQuery.fn.attrList = function ( strAttribute, strDelimiter ) {
  // Create an array to store the attribute values of
  // the jQuery stack items.
  var arrValues = new Array();
  // Check to see if we were given a delimiter.
  // By default, we will use the comma.
  strDelimiter = (strDelimiter ? strDelimiter : ",");
  // Loop over each element in the jQuery stack and
  // add the given attribute to the value array.
  this.each( function ( intI ) {
    // Get a jQuery version of the current
    // stack element.
    var jNode = jQuery( this );
    // Add the given attribute value to our
    // values array.
    arrValues[ arrValues.length ] = jNode.attr( strAttribute );
  } );
  // Return the value list by joining the array.
  return(
    arrValues.join( strDelimiter )
    );
}