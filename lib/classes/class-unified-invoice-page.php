<?php
/**
 * Unified Invoice Page handler
 */

namespace UsabilityDynamics\WPI {

  /**
   * Class UnifiedInvoicePage
   * @package UsabilityDynamics\WPI
   */
  class UnifiedInvoicePage {

    /**
     *
     */
    public function __construct() {
      global $wpi_settings;

      if ( !is_admin() ) {
        add_action('wpi_template_redirect', array($this, 'template_redirect_change'));
      }
    }

    /**
     *
     */
    public function template_redirect_change() {
      global $wpi_settings;

      //** Load front end scripts */
      wp_enqueue_script('jquery.validate');
      wp_enqueue_script('wpi-gateways');
      wp_enqueue_script('jquery.maskedinput');
      wp_enqueue_script('wpi-frontend-scripts');

      if (!empty($wpi_settings['ga_event_tracking']) && $wpi_settings['ga_event_tracking']['enabled'] == 'true') {
        wp_enqueue_script('wpi-ga-tracking', ud_get_wp_invoice()->path( "static/scripts/wpi.ga.tracking.js", 'url' ), array('jquery'));
      }

      //** Apply Filters to the invoice description */
      add_action('wpi_description', 'wpautop');
      add_action('wpi_description', 'wptexturize');
      add_action('wpi_description', 'shortcode_unautop');
      add_action('wpi_description', 'convert_chars');
      add_action('wpi_description', 'capital_P_dangit');

      //** Declare the variable that will hold our AJAX url for JavaScript purposes */
      wp_localize_script('wpi-gateways', 'wpi_ajax', array('url' => admin_url('admin-ajax.php')));

      add_action('wp_head', array('WPI_UI', 'frontend_header'));

      if ($wpi_settings['replace_page_title_with_subject'] == 'true' || $wpi_settings['hide_page_title'] == 'true') {
        add_action('wp_title', array('WPI_UI', 'wp_title'), 0, 3);
      }

      if ($wpi_settings['replace_page_heading_with_subject'] == 'true' || $wpi_settings['hide_page_title'] == 'true') {
        add_action('the_title', array('WPI_UI', 'the_title'), 0, 2);
      }

      add_action('the_content', array('WPI_UI', 'the_content'), 20);

      load_template( ud_get_wp_invoice()->path('/static/views/unified-invoice-page.php', 'dir'), 1 );
      exit;
    }
  }

}