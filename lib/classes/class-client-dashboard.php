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
       *
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
       * Dashboard Template
       */
      public function dashboard_template( $template ) {
        global $post;

        if ( !$dashboard_page_id = $this->selected_template_page() ) return $template;
        if ( $dashboard_page_id != $post->ID ) return $template;

        $template = ud_get_wp_invoice()->path( 'static/views/client-dashboard.php', 'dir' );

        return $template;

      }

    }
  }
}