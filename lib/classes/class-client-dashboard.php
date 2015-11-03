<?php

/**
 * WP-Invoice Client Dashboards
 */

namespace UsabilityDynamics\WPI {

  if ( !class_exists( '\UsabilityDynamics\WPI\ClientDashboard' ) ) {
    /**
     * Class ClientDashboard
     * @package UsabilityDynamics\WPI
     * @author korotkov@ud
     */
    class ClientDashboard {

      /**
       * Init
       */
      public function __construct() {

        /**
         * Run if enabled
         */
        if ( $this->is_enabled() && $dashboard_page_id = $this->selected_template_page() ) {
          add_filter( 'template_include', array( $this, 'dashboard_template' ) );
        }

        /**
         * Display error if page is not selected
         */
        if ( $this->is_enabled() && !$dashboard_page_id ) {
          add_action( 'admin_notices', array( $this, 'notice_page_not_selected' ) );
        }
      }

      /**
       * @return bool
       */
      public function get_current_user_invoices() {
        if ( is_user_logged_in() ) {
          $current_user_email = wp_get_current_user()->user_email;
        } else {
          if ( !empty( $_GET['wpi_user_id'] ) ) {
            $user = get_user_by( 'id', $_GET['wpi_user_id'] );
            if ( !is_a( $user, 'WP_User' ) ) {
              return array();
            } else {
              $current_user_email = $user->user_email;
            }
          } else {
            return array();
          }
        }

        $invoices_query = new \WP_Query(array(
          'post_type' => 'wpi_object',
          'post_status' => 'any',
          'meta_key' => 'user_email',
          'meta_value' => $current_user_email,
          'posts_per_page' => -1
        ));

        if ( empty( $invoices_query->posts ) ) return array();

        $invoice_objects = array();
        foreach( $invoices_query->posts as $invoice_post ) {
          $_invoice = new \WPI_Invoice();
          $_invoice->load_invoice(array('id' => $invoice_post->ID));
          $invoice_objects[] = $_invoice;
        }

        return $invoice_objects;
      }

      /**
       * Error notice
       */
      public function notice_page_not_selected() {
        echo '<div class="error"><p>' . sprintf( __( 'Client Dashboard page is not selected. Visit <strong><i><a href="%s">Settings Page</a> - Business Process</i></strong> and set <b><i>Display Dashboard page</i></b> under <strong><i>When viewing an invoice</i></strong> section.', ud_get_wp_invoice()->domain ), 'admin.php?page=wpi_page_settings' ) . '</p></div>';
      }

      /**
       * If Client Dashboard is enabled
       * @return bool
       */
      public function is_enabled() {
        global $wpi_settings;
        return empty($wpi_settings['activate_client_dashboard'])?false:($wpi_settings['activate_client_dashboard']=='true'?true:false);
      }

      /**
       * Get selected template page
       * @return bool
       */
      public function selected_template_page() {
        global $wpi_settings;
        return empty($wpi_settings['web_dashboard_page'])?false:(get_post($wpi_settings['web_dashboard_page'])?$wpi_settings['web_dashboard_page']:false);
      }

      /**
       * Remove styles and add specific one
       */
      public function remove_all_theme_styles() {
        global $wp_styles;
        $wp_styles->queue = array();

        wp_enqueue_style('wpi-unified-page-styles', ud_get_wp_invoice()->path('/static/styles/wpi-unified-page.css', 'url'));
      }

      /**
       * Remove scripts except invoice page specific
       */
      public function remove_all_theme_scripts() {
        global $wp_scripts;
        $wp_scripts->queue = array();
      }

      /**
       * Dashboard Template
       */
      public function dashboard_template( $template ) {
        global $post;

        if ( !$dashboard_page_id = $this->selected_template_page() ) return $template;
        if ( $dashboard_page_id != $post->ID ) return $template;

        global $wp_filter;

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
         * Disable all unnecessary styles and scripts
         */
        add_action('wp_print_styles', array($this, 'remove_all_theme_styles'), 999);
        add_action('wp_print_scripts', array($this, 'remove_all_theme_scripts'), 999);

        /**
         * Remove admin bar
         */
        remove_action('wp_head', '_admin_bar_bump_cb');
        show_admin_bar(0);

        /**
         * Load template functions
         */
        include_once( ud_get_wp_invoice()->path('/lib/class_template_functions.php', 'dir') );
        $template = ud_get_wp_invoice()->path( 'static/views/client-dashboard.php', 'dir' );
        return $template;
      }

    }
  }
}