<?php
/**
 * Bootstrap
 *
 * @since 2.0.0
 */
namespace UsabilityDynamics\WPI {

  if( !class_exists( 'UsabilityDynamics\WPI\WPI_SPC_Bootstrap' ) ) {

    final class WPI_SPC_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI\WPI_SPC_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
        require_once( ud_get_wp_invoice_spc()->path( 'lib/class-wp-invoice-spc.php', 'dir' ) );
        
        add_action( 'init', function(){
          \wpi_spc::init_feature();
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
