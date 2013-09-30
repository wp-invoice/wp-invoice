jQuery(document).ready(function(){

  var widget_container = jQuery('#wpi_dw_invoice_statistics .inside');

  jQuery('.columns-prefs input[type="radio"]').bind('click.postboxes', function(){
    jQuery('.chart').hide();
    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'wpi_dw_invoice_statistics',
        period: 'day'
      },
      success: function(data) {
        if ( data.success ) draw_chart( 'day', data.data, widget_container );
      }
    });

    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'wpi_dw_invoice_statistics',
        period: 'week'
      },
      success: function(data) {
        if ( data.success ) draw_chart( 'week', data.data, widget_container );
      }
    });

    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'wpi_dw_invoice_statistics',
        period: 'month'
      },
      success: function(data) {
        if ( data.success ) draw_chart( 'month', data.data, widget_container );
      }
    });
  });

  /**
   *
   */
  function draw_chart( type, data, container ) {
    var line_data = {
      labels : data.labels,
      datasets : [
          {
            fillColor : "rgba(33,117,155,0.5)",
            strokeColor : "rgba(33,117,155,1)",
            pointColor : "#fff",
            pointStrokeColor : "rgba(33,117,155,1)",
            data : data.values
          }
        ]
      };

    jQuery('.'+type+'_chart', container).show();
    jQuery('.loader', container).hide();
    jQuery('#'+type+'_chart', container).attr( 'width', jQuery('#'+type+'_chart').parent().width() );
    return new Chart( document.getElementById(type+"_chart").getContext("2d") ).Line( line_data );
  }

  jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'wpi_dw_invoice_statistics',
      period: 'day'
    },
    success: function(data) {
      if ( data.success ) draw_chart( 'day', data.data, widget_container );
    }
  });

  jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'wpi_dw_invoice_statistics',
      period: 'week'
    },
    success: function(data) {
      if ( data.success ) draw_chart( 'week', data.data, widget_container );
    }
  });

  jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'wpi_dw_invoice_statistics',
      period: 'month'
    },
    success: function(data) {
      if ( data.success ) draw_chart( 'month', data.data, widget_container );
    }
  });

});