jQuery(document).ready(function(){

  var widget_container = jQuery('#wpi_dw_invoice_statistics .inside');

  jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'wpi_dw_invoice_statistics'
    },
    success: function(data) {

    }
  });

//  var month_line_data = {
//    labels : [],
//    datasets : [
//      {
//        fillColor : "rgba(220,220,220,0.5)",
//        strokeColor : "rgba(220,220,220,1)",
//        pointColor : "rgba(220,220,220,1)",
//        pointStrokeColor : "#fff",
//        data : []
//      }
//    ]
//  }

//  jQuery('#month_chart').attr( 'width', jQuery('#month_chart').parent().width() );
//  var month_line = new Chart( document.getElementById("month_chart").getContext("2d") ).Line( month_line_data );

});