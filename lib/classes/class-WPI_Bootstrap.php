<?php
/**
 * Bootstrap
 *
 * @since 4.0.0
 */
namespace UsabilityDynamics\WPI {

  if( !class_exists( 'UsabilityDynamics\WPI\WPI_Bootstrap' ) ) {

    final class WPI_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI\WPI_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
        add_filter( "pre_update_option_wpi_options", array( 'WPI_Functions', 'pre_update_option_wpi_options' ), 10, 3 );
        add_filter( "option_wpi_options", array( 'WPI_Functions', 'option_wpi_options' ) );

        /**
         * Some UD helper.
         * @todo: get rid of this.
         */
        require_once( ud_get_wp_invoice()->path( 'lib/wpi_ud.php', 'dir' ) );

        /**
         * Core
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_core.php', 'dir' ) );

        /**
         * Functions helper
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_functions.php', 'dir' ) );

        /** 
         * Settings API 
         * @todo: Refactor.
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_settings.php', 'dir' ) );

        /**
         * Invoice Object class
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_invoice.php', 'dir' ) );

        /**
         * Gateways base class
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_gateway_base.php', 'dir' ) );

        /**
         * UI helper
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_ui.php', 'dir' ) );

        /**
         * Ajax handlers
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_ajax.php', 'dir' ) );

        /**
         * Widgets
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_widgets.php', 'dir' ) );

        /** 
         * IDK WTF this is
         * @todo: get rid of this.
         **/
        require_once( ud_get_wp_invoice()->path( 'lib/template.php', 'dir' ) );

        /**
         * Payments API
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_payment_api.php', 'dir' ) );

        /**
         * Metaboxes
         */
        require_once( ud_get_wp_invoice()->path( 'lib/ui/class_metaboxes.php', 'dir' ) );

        /**
         * XML-RPC API
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_xmlrpc_api.php', 'dir' ) );

        /**
         * Dashboard Widgets API
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_dashboard_widget.php', 'dir' ) );

        /**
         * Legacy utils
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_legacy.php', 'dir' ) );

        //** Initiate the plugin */
        $this->core = \WPI_Core::getInstance();
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {
        
        if ( !class_exists('\WPI_Functions') ) {
          require_once( ud_get_wp_invoice()->path( 'lib/class_functions.php', 'dir' ) );
        }
        
        //** check if scheduler already sheduled */
        if ( !wp_next_scheduled( 'wpi_hourly_event' ) ) {

          //** Setup WPI schedule to handle recurring invoices */
          wp_schedule_event( time(), 'hourly', 'wpi_hourly_event' );
        }
        if ( !wp_next_scheduled( 'wpi_update' ) ) {

          //** Scheduling daily update event */
          wp_schedule_event( time(), 'daily', 'wpi_update' );
        }

        //** Try to create new schema tables */
        \WPI_Functions::create_new_schema_tables();

        //** Get previous activated version */
        $current_version = get_option( 'wp_invoice_version' );

        //** If no version found at all, we do new install */
        if ( (int) $current_version < 3 ) {

          if ( !class_exists('\WPI_Legacy') ) {
            require_once( ud_get_wp_invoice()->path( 'lib/class_legacy.php', 'dir' ) );
          }
        
          //** Determine if legacy data exist */
          \WPI_Legacy::init();
        }

        //** Update version */
        update_option( 'wp_invoice_version', WP_INVOICE_VERSION_NUM );

        update_option( 'wpi_activation_time', time() );
        
      }
      
      /**
       * Return localization's list.
       *
       * @author peshkov@UD
       * @return array
       */
      public function get_localization() {
        return apply_filters( 'wpp::get_localization', array(
          'licenses_menu_title' => __( 'Add-ons', $this->domain ),
          'licenses_page_title' => __( 'WP-Invoice Add-ons Manager', $this->domain ),
        ) );
      }
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {
        wp_clear_scheduled_hook( 'wpi_hourly_event' );
        wp_clear_scheduled_hook( 'wpi_update' );
        wp_clear_scheduled_hook( 'wpi_spc_remove_abandoned_transactions' );
      }

    }

  }

}
