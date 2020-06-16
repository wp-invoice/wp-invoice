<?php
/**
 * Application namespace
 */

namespace UsabilityDynamics\WPI_BL {

  /**
   * Prevent class redeclaration
   */
  if ( !class_exists( 'UsabilityDynamics\WPI_BL\Application' ) ) {

    /**
     * Class Application
     * @package UsabilityDynamics\WPI_BL
     */
    class Application {

      /**
       * CPT
       * @var string
       */
      private $pt = 'wpi_bl';

      /**
       * Locations storage
       * @var array|bool
       */
      private $locations = array();

      /**
       * make things happen
       */
      public function __construct() {

        /**
         * Add menu pages
         */
        add_action( 'init', array( $this, 'init' ), 50 );

        /**
         * Front-end replace
         */
        add_filter( 'wpi_business_name', array( $this, 'business_name' ), 10, 2 );
        add_filter( 'wpi_business_address', array( $this, 'business_location' ), 10, 2 );
        add_filter( 'wpi_business_phone', array( $this, 'business_phone' ), 10, 2 );

        /**
         * Get locations
         */
        $this->locations = $this->get_available_locations();

      }

      /**
       * Replace title
       * @param $current
       * @return mixed
       */
      public function business_name( $current, $current_invoice = false ) {
        global $invoice;

        $_invoice = $invoice;

        if ( $current_invoice && is_object( $current_invoice ) && !empty( $current_invoice->data ) ) {
          $_invoice = $current_invoice->data;
        }

        if ( !empty( $_invoice['business_location'] ) && $_location = get_post($_invoice['business_location']) ) {
          if ( $_location->post_status == 'publish' ) $current = $_location->post_title;
        }

        return $current;
      }

      /**
       * Replace description
       * @param $current
       * @return mixed
       */
      public function business_location( $current, $current_invoice = false ) {
        global $invoice;

        $_invoice = $invoice;

        if ( $current_invoice && is_object( $current_invoice ) && !empty( $current_invoice->data ) ) {
          $_invoice = $current_invoice->data;
        }

        if ( !empty( $_invoice['business_location'] ) && $_location = get_post($_invoice['business_location']) ) {
          if ( $_location->post_status == 'publish' ) $current = $_location->post_content;
        }

        return $current;
      }

      /**
       * Stop displaying phone if locations assigned
       * @param $current
       * @return mixed
       */
      public function business_phone( $current, $current_invoice = false ) {
        global $invoice;

        $_invoice = $invoice;

        if ( $current_invoice && is_object( $current_invoice ) && !empty( $current_invoice->data ) ) {
          $_invoice = $current_invoice->data;
        }

        if ( !empty( $_invoice['business_location'] ) && $_location = get_post($_invoice['business_location']) ) {
          if ( $_location->post_status == 'publish' ) return '';
        }

        return $current;
      }

      /**
       * Initialize things
       */
      public function init() {
        global $wpi_settings;

        $labels = array(
          'name'               => _x( 'Locations', 'post type general name', ud_get_wp_invoice_business_locations()->domain ),
          'singular_name'      => _x( 'Location', 'post type singular name', ud_get_wp_invoice_business_locations()->domain ),
          'menu_name'          => _x( 'Locations', 'admin menu', ud_get_wp_invoice_business_locations()->domain ),
          'name_admin_bar'     => _x( 'Location', 'add new on admin bar', ud_get_wp_invoice_business_locations()->domain ),
          'add_new'            => _x( 'Add New', 'Location', ud_get_wp_invoice_business_locations()->domain ),
          'add_new_item'       => __( 'Add New Location', ud_get_wp_invoice_business_locations()->domain ),
          'new_item'           => __( 'New Location', ud_get_wp_invoice_business_locations()->domain ),
          'edit_item'          => __( 'Edit Location', ud_get_wp_invoice_business_locations()->domain ),
          'view_item'          => __( 'View Location', ud_get_wp_invoice_business_locations()->domain ),
          'all_items'          => __( 'Business Locations', ud_get_wp_invoice_business_locations()->domain ),
          'search_items'       => __( 'Search Locations', ud_get_wp_invoice_business_locations()->domain ),
          'parent_item_colon'  => __( 'Parent Locations:', ud_get_wp_invoice_business_locations()->domain ),
          'not_found'          => __( 'No Locations found.', ud_get_wp_invoice_business_locations()->domain ),
          'not_found_in_trash' => __( 'No Locations found in Trash.', ud_get_wp_invoice_business_locations()->domain )
        );

        $args = array(
          'public' => false,
          'show_ui' => true,
          'show_in_menu' => 'wpi_main',
          'publicly_queryable' => false,
          'rewrite' => false,
          'label'  => __('Business Locations'),
          'labels' => $labels
        );

        /**
         * Register post type
         */
        register_post_type( $this->pt, $args );

        /**
         * Save meta data for invoice
         */
        add_action('wpi_pre_header_' . $wpi_settings['pages']['edit'], array( $this, 'invoice_metaboxes' ));
        add_filter('wpi_invoice_pre_save', array( $this, 'invoice_pre_save' ), 10, 2);
      }

      /**
       * Save invoice custom data
       * @param $ni
       * @param $data
       * @return mixed
       */
      public function invoice_pre_save( $ni, $data ) {
        $ni->set(array(
          'business_location' => $data['business_location']
        ));
        return $ni;
      }

      /**
       * Metabox
       */
      public function invoice_metaboxes() {
        global $wpi_settings;
        add_meta_box( 'wpibl_options', __( 'Business Location', ud_get_wp_invoice_business_locations()->domain ), array($this, 'invoice_metabox_options_renderer'), $wpi_settings['pages']['edit'], 'normal', 'high' );
      }

      /**
       * Metabox renderer
       */
      public function invoice_metabox_options_renderer( $invoice, $args ) {
        ob_start();
        include ud_get_wp_invoice_business_locations()->path( '/static/views/metabox.php', 'dir' );
        echo ob_get_clean();
      }

      /**
       * Get available locations
       * @return array|bool
       */
      public function get_available_locations() {
        $_locations = get_posts(array(
            'post_type' => $this->pt,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));

        return !empty( $_locations ) ? $_locations : false;
      }
    }
  }
}