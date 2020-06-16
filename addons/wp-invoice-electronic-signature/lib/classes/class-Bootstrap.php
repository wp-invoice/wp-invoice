<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPIES {

  if( !class_exists( 'UsabilityDynamics\WPIES\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPIES\Bootstrap object
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
