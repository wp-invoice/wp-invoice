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

      add_filter( 'wpi_where_to_display_options', array( $this, 'add_new_display_option' ) );

      if ( !is_admin() && !empty($wpi_settings['where_to_display']) && $wpi_settings['where_to_display'] == 'unified_page' ) {
        add_action('wpi_template_redirect', array($this, 'template_redirect_change'));
      }
    }

    /**
     * @param $options
     * @return mixed
     */
    public function add_new_display_option( $options ) {
      $options['unified_page'] = __( 'Unified Page Template', ud_get_wp_invoice()->domain );
      return $options;
    }

    /**
     *
     */
    public function template_redirect_change() {
      global $wpi_settings, $wpi_invoice_object, $invoice;

      $invoice = $wpi_invoice_object->data;

      /** Mark invoice as viewed if not by admin */
      if ( !current_user_can( 'manage_options' ) ) {

        /** Prevent duplicating of 'viewed' item. */
        /** 1 time per $hours */
        $hours = 12;

        $viewed_today_from_cur_ip = false;

        foreach ( $invoice[ 'log' ] as $key => $value ) {
          if ( $value[ 'user_id' ] == '0' ) {
            if ( strstr( strtolower( $value[ 'text' ] ), "viewed by {$_SERVER['REMOTE_ADDR']}" ) ) {
              $time_dif = time() - $value[ 'time' ];
              if ( $time_dif < $hours * 60 * 60 ) {
                $viewed_today_from_cur_ip = true;
              }
            }
          }
        }

        if ( !$viewed_today_from_cur_ip ) {
          $wpi_invoice_object->add_entry( "note=Viewed by {$_SERVER['REMOTE_ADDR']}" );
        }
      }

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

      remove_action( 'wp_head', '_admin_bar_bump_cb' );
      show_admin_bar( 0 );

      include_once( ud_get_wp_invoice()->path( '/lib/class_template_functions.php', 'dir' )  );

      load_template( ud_get_wp_invoice()->path('/static/views/unified-invoice-page.php', 'dir'), 1 );
      exit;
    }

  }

}