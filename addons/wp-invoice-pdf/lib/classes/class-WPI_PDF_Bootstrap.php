<?php
/**
 * Bootstrap
 *
 * @since 2.0.0
 */
namespace UsabilityDynamics\WPI {

  if( !class_exists( 'UsabilityDynamics\WPI\WPI_PDF_Bootstrap' ) ) {

    final class WPI_PDF_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPI\WPI_PDF_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {

        // disable DOMPDF's internal autoloader if you are using Composer
        define('DOMPDF_ENABLE_AUTOLOAD', false);

        require_once( ud_get_wp_invoice_pdf()->path( 'lib/class-wp-invoice-pdf.php', 'dir' ) );
        
        add_action( 'init', function(){
          \wpi_pdf::init();
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
