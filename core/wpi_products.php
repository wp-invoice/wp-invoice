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
       * Default options
       * @var type 
       */
      private static $defaults;
      
      /**
       * Construct
       */
      public function __construct() {
        $this->apply_defaults();

        //** Add new post type for Products */
        $this->register_post_type();
        
        //** Add different UIs. Settings etc. */
        add_filter( 'wpi_settings_tabs', array( $this, 'register_ui' ) );
        
      }
      
      /**
       * Apply filters 
       */
      public function apply_defaults() {
        self::$defaults = apply_filters('wpi_products_default_settings', array(
          'change_default_labels' => false,
          'labels' => array(
            'name' => __('Products', WPI),
            'singular_name' => __('Product', WPI),
            'menu_name' => __('Products', WPI),
            'name_admin_bar' => __('Product', WPI),
            'add_new' => __('Add New', WPI),
            'add_new_item' => __('Add New Product', WPI),
            'new_item' => __('New Product', WPI),
            'edit_item' => __('Edit Product', WPI),
            'view_item' => __('View Product', WPI),
            'all_items' => __('All Products', WPI),
            'search_items' => __('Search Products', WPI),
            'parent_item_colon' => __('Parent Products:', WPI),
            'not_found' => __('No Products Found', WPI),
            'not_found_in_trash' => __('No Products Found in Trash', WPI)
          ),
          'post_type' => array(
            'public' => true,
            'exclude_from_search' => false,
            'hierarchical' => false,
            'has_archive' => true,
            'menu_position' => 80,
            'rewrite' => array(
              'slug' => 'product'
            ),
            'supports' => array()
          )
        ));
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