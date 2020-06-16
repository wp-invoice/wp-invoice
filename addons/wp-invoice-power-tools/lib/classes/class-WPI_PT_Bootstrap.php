<?php
/**
 * Bootstrap
 *
 * @since 2.0.0
 */
namespace UsabilityDynamics\WPI {

  if( !class_exists( 'UsabilityDynamics\WPI\WPI_PT_Bootstrap' ) ) {

    final class WPI_PT_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI\WPI_PT_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
        require_once( ud_get_wp_invoice_power_tools()->path( 'lib/class-wpi-power-tools.php', 'dir' ) );
        
        add_action( 'init', function(){
          \wpi_power_tools::init();
        }, 0 );
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
