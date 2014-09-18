/**
 * Handle user events and interact with GA
 *
 * @author korotkov@UD
 * @uses Google Analytics object '_gaq'
 */

//** Create 'wpi' object if it doesn't exist yet */
var wpi = wpi || {};

//** Create 'ga' object which contains objects and methods for interacting with Google Analytics */
wpi.ga = {

  //** Object with properties and methods for Event Tracking */
  tracking: {

    //** Available Event categories and actions */
    event : {
      category : {
        invoices : 'Invoice',
        spc : 'Single Page Checkout'
      },
      action : {
        pay : 'Pay',
        view : 'View'
      }
    },

    //** Getter for Event Category */
    get_event_cat : function( category ) {
      return typeof this.event.category[category] == 'string' ? this.event.category[category] : 'Unknown Category';
    },

    //** Getter for Event Action */
    get_event_act : function( action ) {
      return typeof this.event.action[action] == 'string' ? this.event.action[action] : 'Unknown Action';
    },

    //** Initialize and run push events mentioned in 'options' if they exist */
    init : function ( options ) {
      for ( var i in options ) {
        if ( options[i] == 'true' && typeof this[i] == 'function' ) this[i]();
      }
    },

    //** Add Event for '#cc_pay_button' click */
    attempting_pay_invoice : function () {
      var self = this;
      wpi = wpi || {};
      jQuery('#cc_pay_button').on('click',function(){
        if (typeof wpi.invoice_id !='undefined'){
          self.track_pay_invoice();
        }
      });

      jQuery(document).bind('wpi_spc_success', function(event, ui) {
          wpi.invoice_title = ui.wpi_invoice.title;
          wpi.invoice_amount = ui.wpi_invoice.invoice_amount;
          wpi.invoice_id = ui.wpi_invoice.invoice_id;
          wpi.tax = 0;
          wpi.business_name = ui.wpi_invoice.business_name;
          wpi.user_data = ui.wpi_invoice.user_data
          wpi.invoice_items = ui.wpi_invoice.invoice_items;

          self.track_pay_invoice();
      });
    },

    track_pay_invoice : function(){
        if (typeof wpi.invoice_items !='undefined'){
          _gaq.push(['_trackPageview']);
          _gaq.push(['_addTrans',
            wpi.invoice_id,           // order ID - required
            wpi.business_name, // affiliation or store name
            wpi.invoice_amount,          // total - required
            wpi.tax,           // tax
            '0',          // shipping
            wpi.user_data.city,       // city
            wpi.user_data.state,     // state or province
            wpi.user_data.country             // country
          ]);
          jQuery.each(wpi.invoice_items , function(i,item) {
            _gaq.push(['_addItem',
              wpi.invoice_id,           // order ID - necessary to associate item with transaction
              item.id,           // SKU/code - required
              item.name,        // product name
              '',   // category or variation
              item.price,          // unit price - required
              item.quantity               // quantity - required
            ]);
          });
          _gaq.push(['_trackTrans']);
        }
       //_gaq.push(['_trackEvent', wpi.ga.tracking.get_event_cat('invoices'), wpi.ga.tracking.get_event_act('pay'), invoice_title?invoice_title:'Unknown Label', parseInt(invoice_amount)]);

      },

    //** Event of invoice viewing */
    view_invoice : function () {
      _gaq.push(['_trackEvent', wpi.ga.tracking.get_event_cat('invoices'), wpi.ga.tracking.get_event_act('view'), (typeof wpi.invoice_title !='undefined')?wpi.invoice_title:'Unknown Label']);
    }
  }
}