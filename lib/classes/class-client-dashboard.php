<?php

/**
 * WP-Invoice Client Dashboards
 */

namespace UsabilityDynamics\WPI {

  use UsabilityDynamics\Utility;

  if ( !class_exists( '\UsabilityDynamics\WPI\ClientDashboard' ) ) {
    /**
     * Class ClientDashboard
     * @package UsabilityDynamics\WPI
     * @author korotkov@ud
     */
    class ClientDashboard {

      /**
       * Init
       */
      public function __construct() {

        /**
         * Run if enabled
         */
        if ( $this->is_enabled() && $dashboard_page_id = $this->selected_template_page() ) {
          add_filter( 'template_include', array( $this, 'dashboard_template' ) );
        }

        /**
         * Display error if page is not selected
         */
        if ( $this->is_enabled() && !$dashboard_page_id ) {
          add_action( 'admin_notices', array( $this, 'notice_page_not_selected' ) );
        }

        /**
         * Ajax methods
         */
        add_action( 'wp_ajax_cd_get_invoices', array( $this, 'ajax_load_invoices' ) );
        add_action( 'wp_ajax_nopriv_cd_get_invoices', array( $this, 'ajax_load_invoices' ) );

        /**
         * JSON filter
         */
        add_filter( 'wpi:cd:ajax_pre_load', array( $this, 'prepare_invoice_object_for_json' ) );

        /**
         * Exclude certain types from Dashboard
         */
        add_filter( 'cd_viewable_invoice_types', function( $current ) {
          foreach( $current as $key => $type ) {
            if ( $type == 'pending' ) {
              unset($current[$key]);
            }
          }
          return $current;
        });
      }

      /**
       * Filter invoices before JSONing
       * @param $_invoice
       * @return mixed
       */
      public function prepare_invoice_object_for_json( $_invoice ) {

        global $invoice;

        $invoice = $_invoice;
        
        $invoice['cd_permalink'] = get_invoice_permalink( $invoice['ID'] );
        $invoice['cd_due_date']  = wpi_get_invoice_due_date( 'm/d/Y' );
        $invoice['cd_invoice_id'] = invoice_id( array( 'return' => true ) );
        $invoice['cd_invoice_type'] = __( wpi_get_invoice_type(), ud_get_wp_invoice()->domain );
        $invoice['cd_invoice_title'] = wpi_get_invoice_title();
        $invoice['cd_is_paid'] = is_paid();
        $invoice['cd_invoice_status'] = __( ucfirst( $invoice['post_status'] ), ud_get_wp_invoice()->domain );
        

        if ( is_paid() ) {
          $invoice['cd_date_paid'] = date(apply_filters('wpi_date_paid_format', 'm/d/Y'), get_post_modified_time('U', false, $invoice['ID']));//date_paid();

          $invoice['cd_invoice_total'] = wpi_get_total_payments( wpi_get_invoice_currency_sign() );
        } else {
          $invoice['cd_invoice_total'] = wpi_get_amount_due( wpi_get_invoice_currency_sign() );
        }

        return $invoice;
      }

      /**
       * Ajax call for loading invoices
       */
      public function ajax_load_invoices() {

        include_once( ud_get_wp_invoice()->path('/lib/class_template_functions.php', 'dir') );

        $_invoices = $this->get_current_user_invoices( isset($_REQUEST['offset'])?(int)$_REQUEST['offset']:0, apply_filters( 'wpi:cd:ajax_load_invoices:per_page', isset($_REQUEST['per_page'])?(int)$_REQUEST['per_page']:10 ) );

        $_invoices['amount'] = 0;

        if ( !empty( $_invoices['items'] ) && is_array( $_invoices['items'] ) ) {
          foreach( $_invoices['items'] as &$_invoice ) {
            unset($_invoice->data['billing']);
            unset($_invoice->data['itemized_list']);
            unset($_invoice->data['user_data']);
            unset($_invoice->data['signature_data']);
            unset($_invoice->data['log']);

            $_invoices['amount'] += $_invoice->data['post_status'] == 'paid' ? $_invoice->data['total_payments'] : $_invoice->data['net'];

            $_invoice = apply_filters( 'wpi:cd:ajax_pre_load', $_invoice->data );
          }
        }

        $_invoices['amount'] = wp_invoice_currency_format($_invoices['amount']);

        wp_send_json( $_invoices );
      }

      /**
       * Get current user invoices
       * @return bool
       */
      public function get_current_user_invoices( $offset = 0, $per_page = -1 ) {
        if ( is_user_logged_in() ) {
          $current_user_email = wp_get_current_user()->user_email;
        } else {
          if ( !empty( $_GET['wpi_user_id'] ) ) {
            $user = get_user_by( 'id', $_GET['wpi_user_id'] );
            if ( !is_a( $user, 'WP_User' ) || !wpi_user_can_view_dashboard() ) {
              return array();
            } else {
              $current_user_email = $user->user_email;
            }
          } else {
            return array();
          }
        }
		if( isset( $_GET['allowed_status'] ) && $_GET['allowed_status'] == 'paid' ){
			$arr_allowed_status = array( 'paid' );
		}else{
			$arr_allowed_status = array_diff(apply_filters('cd_viewable_invoice_types', \WPI_Core::getInstance()->viewable_types()),array('paid'));
		}

        $invoices_query = new \WP_Query(array(
          'post_type' => 'wpi_object',
          'post_status' => $arr_allowed_status,//apply_filters('cd_viewable_invoice_types', \WPI_Core::getInstance()->viewable_types()),
          'orderby' => 'modified',
          'meta_key' => 'user_email',
          'meta_value' => $current_user_email,
          'offset' => $offset,
          'posts_per_page' => $per_page
        ));

        if ( empty( $invoices_query->posts ) ) return array();

        $invoice_objects = array();
        foreach( $invoices_query->posts as $invoice_post ) {
          $_invoice = new \WPI_Invoice();
          $_invoice->load_invoice(array('id' => $invoice_post->ID));
          $invoice_objects[] = $_invoice;
        }

        return array(
          'total' => (int)$invoices_query->found_posts,
          'items' => $invoice_objects,
          'invoice_date_title' => 
				  ( isset( $_GET['allowed_status'] ) && $_GET['allowed_status'] == 'paid' )
			? __( "Paid date", ud_get_wp_invoice()->domain )
			: __( "Due date", ud_get_wp_invoice()->domain )

        );
      }

      /**
       * Error notice
       */
      public function notice_page_not_selected() {
        echo '<div class="error"><p>' . sprintf( __( 'Client Dashboard page is not selected. Visit <strong><i><a href="%s">Settings Page</a> - Business Process</i></strong> and set <b><i>Display Dashboard page</i></b> under <strong><i>When viewing an invoice</i></strong> section.', ud_get_wp_invoice()->domain ), 'admin.php?page=wpi_page_settings' ) . '</p></div>';
      }

      /**
       * If Client Dashboard is enabled
       * @return bool
       */
      public function is_enabled() {
        global $wpi_settings;
        return empty($wpi_settings['activate_client_dashboard'])?false:($wpi_settings['activate_client_dashboard']=='true'?true:false);
      }

      /**
       * Get selected template page
       * @return bool
       */
      public function selected_template_page() {
        global $wpi_settings;
        return empty($wpi_settings['web_dashboard_page'])?false:(get_post($wpi_settings['web_dashboard_page'])?$wpi_settings['web_dashboard_page']:false);
      }

      /**
       * Remove styles and add specific one
       */
      public function remove_all_theme_styles() {
        global $wp_styles;
        $wp_styles->queue = array();

        wp_enqueue_style('wpi-unified-page-styles', ud_get_wp_invoice()->path('/static/styles/wpi-unified-page.css', 'url'));

        do_action( 'wpi-client-dashboard-styles' );
      }

      /**
       * Remove scripts except invoice page specific
       */
      public function remove_all_theme_scripts() {
        global $wp_scripts;
        $wp_scripts->queue = array();

        wp_enqueue_script('angular-js', ud_get_wp_invoice()->path('/static/scripts/vendor/angular.min.js', 'url'), null, '1.4.8', true );
        wp_enqueue_script('angular-js-sanitize', ud_get_wp_invoice()->path('/static/scripts/vendor/angular-sanitize.js', 'url'), null, '1.4.8', true );
        wp_enqueue_script('wpi-client-dashboard', ud_get_wp_invoice()->path('/static/scripts/wpi-client-dashboard.js', 'url'), array('angular-js', 'angular-js-sanitize'), WP_INVOICE_VERSION_NUM, false );
      }

      /**
       * Dashboard Template
       */
      public function dashboard_template( $template ) {
        global $post;

        if ( !$dashboard_page_id = $this->selected_template_page() ) return $template;
        if ( !is_object($post) || $dashboard_page_id != $post->ID ) return $template;
        if ( !empty( $_GET['invoice_id'] ) ) return $template;

        global $wp_filter;

        /**
         * Remove unwanted wp_head hooks
         */
        foreach ($wp_filter['wp_head'] as $priority => $wp_head_hooks) { // Loop the hook. Hook's actions are categorized as multidimensional array by priority
          if (is_array($wp_head_hooks)) { // Check if this is an array
            foreach ($wp_head_hooks as $wp_head_hook) { // Loop the hook
              if (!is_array($wp_head_hook['function']) && !in_array($wp_head_hook['function'], array('wp_print_head_scripts', 'wp_enqueue_scripts', 'wp_print_styles'))) { // Check the action against the whitelist
                remove_action('wp_head', $wp_head_hook['function'], $priority); // Remove the action from the hook
              }
            }
          }
        }

        /**
         * Disable all unnecessary styles and scripts
         */
        add_action('wp_print_styles', array($this, 'remove_all_theme_styles'), 999);
        add_action('wp_print_scripts', array($this, 'remove_all_theme_scripts'), 999);

        /**
         * Remove admin bar
         */
        remove_action('wp_head', '_admin_bar_bump_cb');
        show_admin_bar(0);

        /**
         * Load template functions
         */
        include_once( ud_get_wp_invoice()->path('/lib/class_template_functions.php', 'dir') );
        $best_template = Utility::get_template_part( array(
          'client-dashboard',
        ), array( get_stylesheet_directory() . '/wpi', ud_get_wp_invoice()->path( 'static/views', 'dir' ) ) );
        $template = !empty( $best_template ) ? $best_template : ud_get_wp_invoice()->path( 'static/views/client-dashboard.php', 'dir' );
        return $template;
      }

    }
  }
}