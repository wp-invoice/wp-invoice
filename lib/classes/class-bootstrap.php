<?php
/**
 * Bootstrap
 *
 * @since 4.0.0
 */
namespace UsabilityDynamics\WPI {

  if( !class_exists( 'UsabilityDynamics\WPI\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap {
    
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type \UsabilityDynamics\WPI\Bootstrap object
       */
      protected static $instance = null;
    
      /**
       * Core object
       *
       * @private
       * @static
       * @property $settings
       * @type WPP_Core object
       */
      private $core = null;
      
      /**
       * Instantaite class.
       *
       * @todo: get rid of includes, - move to autoload. peshkov@UD
       */
      public function init() {
        
        //** Be sure we do not have errors. Do not initialize plugin if we have them. */
        if( !$this->has_errors() ) {
        
          //** Licenses Manager */
          global $_ud_license_updater;
          $this->client = new \UsabilityDynamics\UD_API\Bootstrap( $this->args );
          $_ud_license_updater[ $this->plugin ] = $this->client;
          
          //** Init Settings */
//          $this->settings = new Settings( array(
//            'key'  => 'wpp_settings',
//            'store'  => 'options',
//            'data' => array(
//              'name' => $this->name,
//              'version' => $this->version,
//              'domain' => $this->domain,
//            )
//          ));
          
          add_filter( "pre_update_option_wpi_options", array( 'WPI_Functions', 'pre_update_option_wpi_options' ), 10, 3 );
          add_filter( "option_wpi_options", array( 'WPI_Functions', 'option_wpi_options' ) );
        
          /**
           * Some UD helper.
           * @todo: get rid of this.
           */
          require_once( WPI_Path . '/lib/wpi_ud.php' );
          
          /**
           * Core
           */
          require_once( WPI_Path . '/lib/class_core.php' );
          
          /**
           * Functions helper
           */
          require_once( WPI_Path . '/lib/class_functions.php' );
          
          /** 
           * Settings API 
           * @todo: Refactor.
           */
          require_once( WPI_Path . '/lib/class_settings.php' );
          
          /**
           * Invoice Object class
           */
          require_once( WPI_Path . '/lib/class_invoice.php' );
          
          /**
           * Gateways base class
           */
          require_once( WPI_Path . '/lib/class_gateway_base.php' );
          
          /**
           * UI helper
           */
          require_once( WPI_Path . '/lib/class_ui.php' );
          
          /**
           * Ajax handlers
           */
          require_once( WPI_Path . '/lib/class_ajax.php' );
          
          /**
           * Widgets
           */
          require_once( WPI_Path . '/lib/class_widgets.php' );
          
          /** 
           * IDK WTF this is
           * @todo: get rid of this.
           **/
          require_once( WPI_Path . '/lib/template.php' );
          
          /**
           * Payments API
           */
          require_once( WPI_Path . '/lib/class_payment_api.php' );
          
          /**
           * Metaboxes
           */
          require_once( WPI_Path . '/lib/ui/class_metaboxes.php' );
          
          /**
           * XML-RPC API
           */
          require_once( WPI_Path . '/lib/class_xmlrpc_api.php' );
          
          /**
           * Dashboard Widgets API
           */
          require_once( WPI_Path . '/lib/class_dashboard_widget.php' );
          
          /**
           * Legacy utils
           */
          require_once( WPI_Path . '/lib/class_legacy.php' );
          
          //** Initiate the plugin */
          $this->core = \WPI_Core::getInstance();
        
        }
        
      }
      
      /**
       * Define property $schemas here since we can not set correct paths directly in property
       *
       */
      public function define_schemas() {
        $path = WPI_Path . 'static/schemas/';
        $this->schemas = array(
          //** Autoload Classes versions dependencies for Composer Modules */
          'dependencies' => $path . 'schema.dependencies.json',
          //** Plugins Requirements */
          'plugins' => $path . 'schema.plugins.json',
          //** Licenses */
          'licenses' => $path . 'schema.licenses.json',
        );
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {
        
        //** check if scheduler already sheduled */
        if ( !wp_next_scheduled( 'wpi_hourly_event' ) ) {

          //** Setup WPI schedule to handle recurring invoices */
          wp_schedule_event( time(), 'hourly', 'wpi_hourly_event' );
        }
        if ( !wp_next_scheduled( 'wpi_update' ) ) {

          //** Scheduling daily update event */
          wp_schedule_event( time(), 'daily', 'wpi_update' );
        }

        WPI_Functions::log( __( "Schedule created with plugin activation.", WPI ) );

        //** Try to create new schema tables */
        WPI_Functions::create_new_schema_tables();

        //** Get previous activated version */
        $current_version = get_option( 'wp_invoice_version' );

        //** If no version found at all, we do new install */
        if ( $current_version == WP_INVOICE_VERSION_NUM ) {
          WPI_Functions::log( __( "Plugin activated. No older versions found, installing version ", WPI ) . WP_INVOICE_VERSION_NUM . "." );
        } else if ( (int) $current_version < 3 ) {

          //** Determine if legacy data exist */
          WPI_Legacy::init();
          WPI_Functions::log( __( "Plugin activated.", WPI ) );
        }

        //** Update version */
        update_option( 'wp_invoice_version', WP_INVOICE_VERSION_NUM );

        update_option( 'wpi_activation_time', time() );

      }
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {
        wp_clear_scheduled_hook( 'wpi_hourly_event' );
        wp_clear_scheduled_hook( 'wpi_update' );
        wp_clear_scheduled_hook( 'wpi_spc_remove_abandoned_transactions' );
        WPI_Functions::log( __( "Plugin deactivated.", WPI ) );
      }

    }

  }

}
