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
  
  /**
   * 'Show actions toggle'
   */
  jQuery('.wpi_toggle').live('click',function() {
    var toggle_what = jQuery(this).attr('toggle');

    jQuery("." + toggle_what).toggle();
  });
  
});