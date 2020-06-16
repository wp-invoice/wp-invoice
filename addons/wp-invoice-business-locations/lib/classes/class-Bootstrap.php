<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPI_BL {

  if( !class_exists( 'UsabilityDynamics\WPI_BL\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI_BL\Bootstrap object
       */
      protected static $instance = null;

      /**
       * @var
       */
      private $application;
      
      /**
       * Instantaite class.
       */
      public function init() {

        require_once $this->path( 'lib/template-functions.php', 'dir' );

        $this->application = new Application();
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
