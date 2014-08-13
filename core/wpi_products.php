<?php

/**
 * Classes set for managing WP-Invoice Products
 * @since 3.09.5
 * @author korotkov@ud
 */
namespace WPI {
  
  if ( !class_exists( '\WPI\ProductsAPI' ) ) {
    
    /**
     * WP-Invoice Products API
     */
    class ProductsAPI {
      
      /**
       * WPI Products post type
       * @var type 
       */
      private static $post_type = 'wpi_product';
      
      /**
       * Singleton instance
       * @var type 
       */
      private static $instance = null;
      
      /**
       * Construct
       */
      public function __construct() {

        //** Add new post type for Products */
        $this->register_post_type();
        
        //** Add different UIs. Settings etc. */
        add_filter( 'wpi_settings_tabs', array( $this, 'register_ui' ) );
        
      }
      
      /**
       * Returns singleton object
       * @return type
       */
      public static function init() {
        return self::$instance ? self::$instance : self::$instance = new ProductsAPI();
      }
      
      private function register_post_type() {}
      
      /**
       * Settings UI
       * @param type $wpi_settings
       */
      public static function settings_tab( $wpi_settings ) {
        include_once WPI_Path . "/core/ui/".self::$post_type."_settings.php";
      }
      
      /**
       * 
       * @param type $current
       * @return int
       */
      public static function register_ui( $current ) {
        $current['products'] = array(
          'label' => __('Products', WPI),
          'position' => 80,
          'callback' => array( __CLASS__, 'settings_tab' )
        );
        return $current;
      }
      
    }
    
  }
  
  if ( !class_exists( '\WPI\Product' ) ) {
    
    /**
     * WP-Invoice Product custructor
     */
    class Product {
      
    }
    
  }
  
  /** 
   * Products API is going to be accessible via global or via direct call of \WPI\ProductsAPI::init()
   */
  function init() {
    global $WPIProductsAPI;
    $WPIProductsAPI = \WPI\ProductsAPI::init();
  }
  
  /**
   * Run all the functionality
   */
  add_action( 'init', '\WPI\init', 0 );
  
}