/**
 * Single Page Checkout
 *
 * Copyright 2011 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 */
/* <![CDATA[ */

(function( $, w, d ) {

  w.wpi_spc = function( params ) {

    var self = {};

    /**
     * Prints/Logs errors
     */
    self.debug = function( error ) {
      if( typeof console !== 'undefined' && typeof console.log !== 'undefined' ) {
        console.log( error );
      }
      return null;
    }

    /* Determine if params is json string */
    if( typeof params === 'string' ) {
      try {
        var json = $.parseJSON( params );
        params = json;
      } catch (e) {
        self.debug( 'WPI SPC Init error. Params can not be converted to object.' );
        return false;
      }
    }

    var defaults = {
      'available_gateways': {},
      'system_gateways' : {},
      'id' : false,
      'total' : 0,
      'total_before_filters' : 0,
      'ga_event_tracking' : {
        'enabled': 'false',
        'events': {
          'invoices' : {}
        }
      }
    };

    self.params = $.extend( true, defaults, params );

    if( !self.params.id ||  !self.params.id.length > 0 ) {
      self.debug( 'WPI SPC Error. template ID is not set.' );
      return false;
    }

    self.instance = jQuery( '#' + self.params.id );

    if( !self.instance.length > 0 ) {
      self.debug( 'WPI SPC Error. template is not found.' );
      return false;
    }

    /**
     *
     */
    self.update_checkboxes = function( venue ) {

      /* Disable and uncheck all internal line items */
      if( $( 'input.wpi_checkout_toggle_item', self.instance ).length ) {
        $( '.wpi_checkout_products', self.instance ).removeAttr( "checked" );
        $( '.wpi_checkout_products', self.instance ).attr( "disabled", "disabled" );
      }

      /* Disable all PayPal line items */
      if( $( "input[paypal_item_name]", self.instance ).length ) {
        $( "input[paypal_item_name]", self.instance ).each( function() {
          $( this ).attr( "disabled", "disabled" );
        });
      }

      /* If there is no wpi_checkout_toggle_item functionality */
      if ( !$( 'input.wpi_checkout_toggle_item', self.instance ).length ) {
        $( '.wpi_checkout_products', self.instance ).each(function(){
          var checked;
          var paypal_item_name;
          if( $( this ).is( ":checked" ) ) {
            checked = true;
          } else {
            checked = false;
          }
          if ( checked ) {
            if($(this).attr("paypal_item_name")) {
              paypal_item_name = $(this).attr( "paypal_item_name" );

              $( "input[paypal_item_name=" + paypal_item_name + "]", self.instance ).removeAttr("disabled");
            }

            $(this).attr("checked", "checked");
            $(this).removeAttr("disabled");
          }
        });
      }

      /* Cycle back through all available wpi_checkout_products and enable their correspondign checkout items */
      $( 'input.wpi_checkout_toggle_item', self.instance ).each( function() {
        var checked;
        var this_element;
        var paypal_item_name;
        var item_name = $(this).attr('item_name');

        if( $(this).is( ":checked" ) ) {
          checked = true;
        } else {
          checked = false;
        }

        this_element = $( '.wpi_checkout_products[item_name="'+item_name+'"]', self.instance );

        //* Find corresponding checkout item */
        if( checked ) {

          //** If there is only one payment venue */
          if( this_element.length == 1 ) {

            this_element.each( function() {

              //** Check if this is a PayPal form, and then disable the PayPal line items */
              if( $(this).attr( "paypal_item_name" ) ) {
                paypal_item_name = $(this).attr("paypal_item_name");
                $( "input[paypal_item_name=" + paypal_item_name + "]", self.instance ).removeAttr("disabled");
              }

              $(this).attr("checked", "checked");
              $(this).removeAttr("disabled");

            });

          } else if ( this_element.length > 1 ) {
            //** If there are multiple payment venues */

            this_element.each(function() {

              //** Check if this is a PayPal form, and then disable the PayPal line items */
              if($(this).attr( "paypal_item_name" ) ) {
                paypal_item_name = $(this).attr("paypal_item_name");

                $( "input[paypal_item_name=" + paypal_item_name + "]", self.instance ).removeAttr("disabled");
              }

              $(this).attr( "checked", "checked" );
              $(this).removeAttr( "disabled" );

            });

          }

        }

        /* Rename PayPal item names to be in proper order */
        $( "input[paypal_item_name^=item_name_].wpi_checkout_products:enabled", self.instance ).each( function(key, value){
          $( "input[paypal_item_name="+$(value).attr('paypal_item_name')+"]", self.instance ).each( function(key_a, value_b){

            if ( $(value_b).attr('name').match(/^item_name_(.*\d)$/) ) {
              $(value_b).attr('name', 'item_name_'+(key+1));
            }
            if ( $(value_b).attr('name').match(/^item_number_(.*\d)$/) ) {
              $(value_b).attr('name', 'item_number_'+(key+1));
            }
            if ( $(value_b).attr('name').match(/^amount_(.*\d)$/) ) {
              $(value_b).attr('name', 'amount_'+(key+1));
            }
            if ( $(value_b).attr('name').match(/^quantity_(.*\d)$/) ) {
              $(value_b).attr('name', 'quantity_'+(key+1));
            }
            if ( $(value_b).attr('name').match(/^tax_rate_(.*\d)$/) ) {
              $(value_b).attr('name', 'tax_rate_'+(key+1));
            }
          });
        });

      });

      /* Update total price */
      self.update_price( venue );
    }


    /**
     * Calculate total displayed price based on checked products
     *
     */
    self.update_price = function( venue ) {

      /* Cycle through every checkout form */
      $('form.wpi_checkout', self.instance).each(function() {
        venue.wpi_checkout_total   = 0;
        var wpi_checkout_fee = 0;
        $('.wpi_checkout_products', $(this)).each(function() {
          if($(this).attr('checked') == 'checked') {
            venue.wpi_checkout_total = venue.wpi_checkout_total + parseFloat($(this).attr('item_price'));
          } else {
            // do nothing
          }
        });
        venue.wpi_checkout_total += venue.amount;
        if ( $('.wpi_checkout_fee', self.instance).length ) {
          wpi_checkout_fee = parseInt( $('.wpi_checkout_fee', self.instance).val() );
          $('.wpi_fee_amount', self.instance).html(' ('+wpi_checkout_fee+'% fee)');
          venue.wpi_checkout_total += venue.wpi_checkout_total/100*wpi_checkout_fee;
        }
        venue.wpi_checkout_total = !isNaN( venue.wpi_checkout_total ) ? venue.wpi_checkout_total : 0;
        $('.wpi_price', self.instance).text($().number_format(venue.wpi_checkout_total));
      });

    }

    /**
     * Change payment method
     *
     */
    self.change_payment_method = function(e) {
      var selected_form_slug = $(e.target).val();
      var context = $(e.target).parents('div.wpi_checkout');

      $('form.wpi_checkout', context).animate({'opacity':0}, function() {
        $(this).hide().css({'opacity':1});
        $('form.wpi_checkout.'+selected_form_slug, context).css({'opacity':'0'}).show().animate({'opacity':1});
      });
    }

    /**
     * Synchronize payment data
     */
    self.sync_input_data = function(e) {
      var context = $(e.target, self.instance ).parents('div.wpi_checkout');
      var full_class_path = '.wpi_checkout_row ';
      $( $(e.target).attr('class').split(' '), context).each(function(k, v) {
        if ( v != '' ) {
          full_class_path += '.'+v;
        }
      });
      $( full_class_path, context ).val( $(e.target).val() );
    }

    /**
     * Initialize
     */
    var _init = function( self ) {

      //console.log( self );

      /* Sync input data */
      jQuery( '.wpi_checkout_row input.text-input', self.instance ).change( self.sync_input_data );

      /* Validation messages control */
      jQuery( '.wpi_checkout input', self.instance ).change( function() {
        var parent_row = jQuery( this ).parents( '.wpi_checkout_row' );
        jQuery( 'input', parent_row ).removeClass( 'wpi_checkout_input_error' );
        //jQuery( 'span.validation', parent_row ).hide();
        jQuery( 'span.validation', parent_row ).text('');
      });

      if( self.params.ga_event_tracking.enabled == 'true' && typeof w._gaq != 'undefined' && typeof w.wpi == 'object' ) {
        if ( typeof w.wpi.ga != 'undefined' ) w.wpi.ga.tracking.init( self.params.ga_event_tracking.events.invoices );
      }


      /** Now we add gateways functionality using hooks. */
      self.gateways = {};
      for( var i in self.params.available_gateways ) {
        if( typeof self.params.available_gateways[i] != 'function' ) {
          $( d ).trigger( 'wpi_checkout_init-' + i, [self] );
        }
      }

    }

    return _init( self );

  }

})( jQuery, window, document );