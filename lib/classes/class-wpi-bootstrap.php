<?php
/**
 * Bootstrap
 *
 * @since 4.0.0
 */
namespace UsabilityDynamics\WPI {

  use UsabilityDynamics\SAAS_UTIL\Register;

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

        /**
         * Duplicates UsabilityDynamics\WP\Bootstrap_Plugin::load_textdomain();
         *
         * There is a bug with localisation in lib-wp-bootstrap 1.1.3 and lower.
         * So we load textdomain here again, in case old version lib-wp-bootstrap is being loaded
         * by another plugin.
         */
        load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( $this->boot_file ) ) . '/static/languages/' );

        add_filter( "pre_update_option_wpi_options", array( 'WPI_Functions', 'pre_update_option_wpi_options' ), 10, 3 );
        add_filter( "option_wpi_options", array( 'WPI_Functions', 'option_wpi_options' ) );

        // Handle forced pre-release update checks.
        if ( is_admin() && isset( $_GET[ 'force-check' ] ) && $_GET[ 'force-check' ] === '1' ) {
          add_filter( 'site_transient_update_plugins', array( $this, 'update_check_handler' ), 50, 2 );
        }

        // Handle regular pre-release checks.
        add_filter( 'pre_update_site_option__site_transient_update_plugins', array( $this, 'update_check_handler' ), 50, 2 );

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
         * Payments API
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_payment_api.php', 'dir' ) );

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

        /**
         * List table
         */
        require_once( ud_get_wp_invoice()->path( 'lib/class_list_table.php', 'dir' ) );

        //** Initiate the plugin */
        $this->core = \WPI_Core::getInstance();
        $this->uip  = new UnifiedInvoicePage();
        $this->cd   = new ClientDashboard();

        // Register Product with SaaS Services.
        if( class_exists( 'UsabilityDynamics\SAAS_UTIL\Register' ) && $this->get_schema( "extra.saasProduct", false ) ) {
          Register::product( $this->get_schema( "extra.saasProduct" ), array(
            "name" => $this->name,
            "slug" => $this->slug,
            "version" => $this->args[ "version" ],
            "type" => "plugin"
          ) );
        }
      }

      /**
       * Pre release updates handler
       * @param $response
       * @param null $old_value
       * @return mixed
       */
      public function update_check_handler( $response, $old_value = null ) {
        global $wpi_settings;

        if ( ! $response || !isset( $response->response ) || ! is_array( $response->response ) || ! isset( $wpi_settings ) || ! isset( $wpi_settings[ 'pre_release_updates' ] ) ) {
          return $response;
        }

        // If pre-release update checks are disabled, do nothing.
        if ( $wpi_settings[ 'pre_release_updates' ] !== 'true' ) {
          return $response;
        }

        // Last check was very recent. (This doesn't seem to be right place for this). That being said, if it's being forced, we ignore last time we tried.
        if ( current_filter() === 'site_transient_update_plugins' && !( isset( $_GET[ 'force-check' ] ) && $_GET[ 'force-check' ] === '1' ) && $response->last_checked && ( time() - $response->last_checked ) < 360 ) {
          return $response;
        }

        // e.g. "wp-invoice", the clean directory name that we are runnig from.
        $_plugin_name = plugin_basename( dirname( dirname( __DIR__ ) ) );

        // e.g. "wp-invoice/wp-invoice.php". Directory name may vary but the main plugin file should not.
        $_plugin_local_id = $_plugin_name . '/wp-invoice.php';

        // Bail, no composer.json file, something broken badly.
        if ( ! file_exists( WP_PLUGIN_DIR . '/' . $_plugin_name . '/composer.json' ) ) {
          return $response;
        }

        try {

          // Must be able to parse composer.json from plugin file, hopefully to detect the "_build.sha" field.
          $_composer = json_decode( file_get_contents( WP_PLUGIN_DIR . '/' . $_plugin_name . '/composer.json' ) );

          if ( is_object( $_composer ) && isset( $_composer->extra ) && isset( $_composer->extra->_build ) && isset( $_composer->extra->_build->sha ) ) {
            $_version = $_composer->extra->_build->sha;
          }

          // @todo Allow for latest branch to be swapped out for another track.
          $_response = wp_remote_get( 'https://api.usabilitydynamics.com/v1/product/updates/' . $_plugin_name . '/latest/' . ( isset( $_version ) && $_version ? '?version=' . $_version : '' ) );

          if ( wp_remote_retrieve_response_code( $_response ) === 200 ) {
            $_body = wp_remote_retrieve_body( $_response );
            $_body = json_decode( $_body );

            // If there is no "data" field then we have nothing to update.
            if ( isset( $_body->data ) ) {

              if( !isset( $response->response ) ) {
                $response->response = array();
              }

              if( !isset( $response->no_update ) ) {
                $response->no_update = array();
              }

              $response->response[ $_plugin_local_id ] = $_body->data;

              if ( isset( $response->no_update[ $_plugin_local_id ] ) ) {
                unset( $response->no_update[ $_plugin_local_id ] );
              }

            }

          }

        } catch( \Exception $e ) {}

        return $response;
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

      /**
       * Run Install Process.
       *
       * @author peshkov@UD
       */
      public function run_install_process() {
        /* Compatibility with WP-CRM 3.10.0 and less versions */
        $old_version = get_option( 'wp_invoice_version' );
        if( $old_version ) {
          $this->run_upgrade_process();
        }
      }

      /**
       * Run Upgrade Process:
       * - do WP-Invoice settings backup.
       *
       * @author peshkov@UD
       */
      public function run_upgrade_process() {
        /* Do automatic Settings backup! */
        $settings = get_option( 'wpi_options' );

        if( !empty( $settings ) ) {

          /**
           * Fixes allowed mime types for adding download files on Edit Product page.
           *
           * @see https://wordpress.org/support/topic/2310-download-file_type-missing-in-variations-filters-exe?replies=5
           * @author peshkov@UD
           */
          add_filter( 'upload_mimes', function( $t ){
            if( !isset( $t['json'] ) ) {
              $t['json'] = 'application/json';
            }
            return $t;
          }, 99 );

          $filename = md5( 'wpi_options_backup' ) . '.json';
          $upload = @wp_upload_bits( $filename, null, json_encode( $settings ) );

          if( !empty( $upload ) && empty( $upload[ 'error' ] ) ) {
            if( isset( $upload[ 'error' ] ) ) unset( $upload[ 'error' ] );
            $upload[ 'version' ] = $this->old_version;
            $upload[ 'time' ] = time();
            update_option( 'wpi_options_backup', $upload );
          }

        }

        do_action( $this->slug . '::upgrade', $this->old_version, $this->args[ 'version' ], $this );
      }

    }

  }

}
