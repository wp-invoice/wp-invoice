<?php
/**
 * Bootstrap
 *
 * @since 2.0.0
 */
namespace UsabilityDynamics\WPI {

  if( !class_exists( 'UsabilityDynamics\WPI\WPI_Q_Bootstrap' ) ) {

    final class WPI_Q_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI\WPI_Q_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {        
        require_once( ud_get_wp_invoice_quotes()->path( 'lib/class-wp-invoice-quotes.php', 'dir' ) );
        
        add_action( 'init', function(){
          \wpi_quotes::init();
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
