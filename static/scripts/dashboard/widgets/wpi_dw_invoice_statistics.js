jQuery(document).ready(function(){

  var widget_container = jQuery('#wpi_dw_invoice_statistics .inside');

  jQuery('.columns-prefs input[type="radio"], #wpi_dw_invoice_statistics.postbox h3, #wpi_dw_invoice_statistics.postbox .handlediv').bind('click.postboxes', function(){
    jQuery('.loader', widget_container).show();
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
        if ( data.success ) draw_chart( 'day', data, widget_container );
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
        if ( data.success ) draw_chart( 'week', data, widget_container );
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
        if ( data.success ) draw_chart( 'month', data, widget_container );
      }
    });
  });

  /**
   * Draw Chart
   */
  function draw_chart( type, data, container ) {
    var line_data = {
      labels : data.data.labels,
      datasets : [
          {
            fillColor : "rgba(33,117,155,0.5)",
            strokeColor : "rgba(33,117,155,1)",
            pointColor : "#fff",
            pointStrokeColor : "rgba(33,117,155,1)",
            data : data.data.values
          }
        ]
      };

    if ( data.goals.enable ) {
      var goal_array = [];
      for( var i=0; i<data.data.values.length; i++ ) {
        goal_array.push(data.goals[type]);
      }
      line_data.datasets.push({
        fillColor : "rgba(159,198,159,0.2)",
        strokeColor : "rgba(159,198,159,0.8)",
        pointColor : "#fff",
        pointStrokeColor : "rgba(159,198,159,0.8)",
        data : goal_array
      });
    }

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
      if ( data.success ) draw_chart( 'day', data, widget_container );
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
      if ( data.success ) draw_chart( 'week', data, widget_container );
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
      if ( data.success ) draw_chart( 'month', data, widget_container );
    }
  });

});