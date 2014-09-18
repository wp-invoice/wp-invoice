jQuery(document).ready(function() {

  /**
   * Filter sections toggling
   */
  jQuery('.wpi_filter_section_title').click(function(){
    var parent = jQuery(this).parents('.wpi_overview_filters');
    jQuery(' .wpi_checkbox_filter', parent).slideToggle('fast', function(){
      if(jQuery(this).css('display') == 'none') {
        jQuery('.wpi_filter_show', parent).html('Show');
      } else {
        jQuery('.wpi_filter_show', parent).html('Hide');
      }
    });
  });

});