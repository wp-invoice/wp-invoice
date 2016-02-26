<?php
/**
 * Unified Invoice Page handler
 */

namespace UsabilityDynamics\WPI {

  use UsabilityDynamics\Utility;

  if ( !class_exists( '\UsabilityDynamics\WPI\UnifiedInvoicePage' ) ) {
    /**
     * Class UnifiedInvoicePage
     * @package UsabilityDynamics\WPI
     * @author korotkov@ud
     */
    class UnifiedInvoicePage {

      /**
       * Init
       */
      public function __construct() {
        global $wpi_settings;

        /**
         * Add new display option
         */
        add_filter('wpi_where_to_display_options', array($this, 'add_new_display_option'));

        /**
         * If we are on front-end and display option is set to 'Unified Page Template'
         * Change template
         */
        if (
          !is_admin()
          &&
          (
            (
              !empty($wpi_settings['where_to_display'])
              && $wpi_settings['where_to_display'] == 'unified_page'
            )
          )
        ) {
          add_action('wpi_template_redirect', array($this, 'template_redirect_change'));
        }
      }

      /**
       *
       */
      public function page_specific_styles() {
        wp_enqueue_style('wpi-unified-page-styles', ud_get_wp_invoice()->path('/static/styles/wpi-unified-page.css', 'url'));
      }

      /**
       * Remove styles and add specific one
       */
      public function remove_all_theme_styles() {
        global $wp_styles;
        $wp_styles->queue = array();

        global $wpi_invoice_object;
        do_action('wpi_unified_page_styles', $wpi_invoice_object);
      }

      /**
       * Remove scripts except invoice page specific
       */
      public function remove_all_theme_scripts() {
        global $wp_scripts, $wpi_settings;

        $wp_scripts->queue = array();

        wp_enqueue_script('jquery.validate');
        wp_enqueue_script('wpi-gateways');
        wp_enqueue_script('jquery.maskedinput');
        wp_enqueue_script('wpi-frontend-scripts');

        if (!empty($wpi_settings['ga_event_tracking']) && $wpi_settings['ga_event_tracking']['enabled'] == 'true') {
          wp_enqueue_script('wpi-ga-tracking', ud_get_wp_invoice()->path("static/scripts/wpi.ga.tracking.js", 'url'), array('jquery'));
        }

        wp_enqueue_script( 'wpi-unified-invoice-page', ud_get_wp_invoice()->path("static/scripts/unified-invoice-page.js", 'url'), array('jquery'));

        global $wpi_invoice_object;
        do_action('wpi_unified_page_scripts', $wpi_invoice_object);
      }

      /**
       * New display option
       * @param $options
       * @return mixed
       */
      public function add_new_display_option($options) {
        $options['unified_page'] = __('Unified Page Template', ud_get_wp_invoice()->domain);
        return $options;
      }

      /**
       * Custom template redirect definition
       */
      public function template_redirect_change() {
        global $wpi_settings, $wpi_invoice_object, $invoice, $wp_filter;

        $invoice = $wpi_invoice_object->data;

        /**
         * Remove unwanted wp_head hooks
         */
        foreach ($wp_filter['wp_head'] as $priority => $wp_head_hooks) { // Loop the hook. Hook's actions are categorized as multidimensional array by priority
          if (is_array($wp_head_hooks)) { // Check if this is an array
            foreach ($wp_head_hooks as $wp_head_hook) { // Loop the hook
              if (!is_array($wp_head_hook['function']) && !in_array($wp_head_hook['function'], array('wp_print_head_scripts', 'wp_enqueue_scripts', 'wp_print_styles'))) { // Check the action against the whitelist
                remove_action('wp_head', $wp_head_hook['function'], $priority); // Remove the action from the hook
              }
            }
          }
        }

        /**
         * Fix quote comments
         */
        add_filter('get_comments_pagenum_link', array($this, 'fix_comments_pages'));
        add_filter('get_comment_link', array($this, 'fix_comment_link'), 10, 3);

        /**
         * Disable all unnecessary styles and scripts
         */
        add_action('wp_print_styles', array($this, 'remove_all_theme_styles'), 999);
        add_action('wp_print_scripts', array($this, 'remove_all_theme_scripts'), 999);
        add_action('wpi_unified_page_styles', array($this, 'page_specific_styles'));

        /**
         * Track invoice widget
         */
        wpi_track_invoice_page_visit($wpi_invoice_object);

        /**
         * Prepare description
         */
        add_action('wpi_description', 'wpautop');
        add_action('wpi_description', 'wptexturize');
        add_action('wpi_description', 'shortcode_unautop');
        add_action('wpi_description', 'convert_chars');
        add_action('wpi_description', 'capital_P_dangit');

        /**
         * Declare the variable that will hold our AJAX url for JavaScript purposes
         */
        wp_localize_script('wpi-gateways', 'wpi_ajax', array('url' => admin_url('admin-ajax.php')));

        /**
         * Necessary header hook
         */
        add_action('wp_head', array('WPI_UI', 'frontend_header'));

        /**
         * Pre-process title
         */
        if ($wpi_settings['replace_page_title_with_subject'] == 'true' || $wpi_settings['hide_page_title'] == 'true') {
          add_action('wp_title', array('WPI_UI', 'wp_title'), 0, 3);
        }
        if ($wpi_settings['replace_page_heading_with_subject'] == 'true' || $wpi_settings['hide_page_title'] == 'true') {
          add_action('the_title', array('WPI_UI', 'the_title'), 0, 2);
        }

        /**
         * Remove admin bar
         */
        remove_action('wp_head', '_admin_bar_bump_cb');
        show_admin_bar(0);

        /**
         * Load template functions
         */
        include_once(ud_get_wp_invoice()->path('/lib/class_template_functions.php', 'dir'));

        /**
         * Load template
         */
        $best_template = Utility::get_template_part( array(
            'unified-invoice-page-'.$invoice['post_status']
        ), array( get_stylesheet_directory() . '/wpi', ud_get_wp_invoice()->path( 'static/views', 'dir' ) ) );

        load_template($best_template, 1);
        exit;
      }

      /**
       * @param $url
       * @return mixed
       */
      public function fix_comments_pages($url) {
        return str_replace('#comments', '?' . $_SERVER['QUERY_STRING'] . '#comments', $url);
      }

      /**
       * @param $link
       * @param $comment
       * @param $args
       * @return mixed
       */
      public function fix_comment_link( $link, $comment, $args ) {
        return str_replace('#comment', '?' . $_SERVER['QUERY_STRING'] . '#comment', $link);
      }

    }
  }

}