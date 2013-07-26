<?php
/**
 * Name: Chargify
 * Class: wpi_chargify
 * Internal Slug: wpi_chargify
 * Version: 1.0a
 * Feature ID: N/A
 * Minimum Core Version: 3.07.0
 * Description: Allows the chargify premium gateway
 */

/** First, require our main library */
require_once( WPI_Path . '/third-party/chargify/chargify.class.php' );

/** The our class */
class wpi_chargify {

  /** Whether or not this plugin is enabled, based on WPI SPC functionality */
  private $enabled = false;

  /**
   * Default feature settings
   */
  private static $default_options = array(
    'domain' => '',
    'api_key' => '',
    'products' => false,
    'products_last_updated' => 0,
    'enabled' => false,
  );
  
  /** Constructor, simple */
  function wpi_chargify(){
    add_action( 'init', array( $this, 'init' ) );
  }
  
  /** Getter */
  function get( $name ){
    if( !isset( $this->{$name} ) ) return false;
    return $this->{$name};
  }

  /**
   * Initis our settings
   */
  function init(){
    global $wpi_settings;
    
    /** Do we have the necessary items to run this */
    $this->enabled = class_exists( 'wpi_spc' );
    $this->shell = false;
    if( !isset( $wpi_settings[ 'wpi_api_key' ] ) || empty( $wpi_settings[ 'wpi_api_key' ] ) ){
      $this->valid_api_key = false;
    }else{
      $this->valid_api_key = true;
    }
    
    /** Setup our terminology map */
    $this->term_map = array(
      'per_unit' => __( 'per unit', WPI ),
      'quantity_based_component' => __( 'quantity based', WPI ),
      'metered_component' => __( 'metered', WPI )
    );
    
    /** Make sure we've got settings loaded */
    if( !isset( $wpi_settings[ 'chargify' ] ) || !is_array( $wpi_settings[ 'chargify' ] ) ) $wpi_settings[ 'chargify' ] = wpi_chargify::$default_options;

    if( $this->enabled ){
      /** Add our AJAX actions */
      add_action( 'wp_ajax_wpi_ajax_check_chargify_products', array( 'wpi_chargify', 'check_chargify_products' ) );
      add_action( 'wp_ajax_wpi_chargify_checkout', array( 'wpi_chargify', 'chargify_checkout' ) );
      add_action( 'wp_ajax_nopriv_wpi_chargify_checkout', array( 'wpi_chargify', 'chargify_checkout' ) );
      add_action( 'wp_ajax_wpi_chargify_cancel', array( 'wpi_chargify', 'chargify_cancel' ) );
      add_action( 'wp_ajax_nopriv_wpi_chargify_cancel', array( 'wpi_chargify', 'chargify_cancel' ) );

      /** Add our shortcodes */
      add_shortcode( 'wpi_chargify_product', array( 'wpi_chargify', 'shortcode_product' ) );
      add_shortcode( 'wpi_chargify_frontend_manage', array( 'wpi_chargify', 'shortcode_frontend_manage' ) );
    }
  }

  /**
   * Our main settings page
   */
  function wpi_chargify_settings(){
    global $wpi_settings;
    
    if( $this->enabled ) {

      /** Chargify is enabled */
      $products = ( isset( $wpi_settings[ 'chargify' ][ 'products' ] ) && $wpi_settings[ 'chargify' ][ 'products' ] ? json_decode( $wpi_settings[ 'chargify' ][ 'products' ] ) : false );
      $components = ( isset( $wpi_settings[ 'chargify' ][ 'components' ] ) && $wpi_settings[ 'chargify' ][ 'components' ] ? json_decode( $wpi_settings[ 'chargify' ][ 'components' ], ARRAY_A ) : false );
      if( $products ) usort( $products, 'wpi_chargify::sort_by_product_family' ); ?>
      
      <div class="wpi_box chargify">
        <div class="wpi_box_header">
          <strong><?php _e( 'Subscription Based Payments via Chargify', WPI ); ?></strong>
        </div>
        <div class="wpi_box_content">

          <h3 class="title"><?php _e( 'General Information', WPI ); ?></h3> <?php
          
          if( $this->shell ){ ?>
            <table class="form-table">
              <tr class="wpi_something_advanced_wrapper">
                <th><?php _e("WPI API Key ", WPI); ?></th>
                <td> <?php
                  if( $this->valid_api_key ){
                    echo $wpi_settings[ 'wpi_api_key' ];
                  }else{
                    _e( 'Oops, it looks like you haven\'t set your WPI API key on the Premium Features tab. Chargify will <strong>not</strong> work in it\'s current state.', WPI );
                  } ?>
                </td>
              </tr>
            </table> <?php
          }else { ?>
            <table class="form-table">
              <tr class="wpi_something_advanced_wrapper">
                <th><?php _e("Domain", WPI); ?></th>
                <td><?php echo WPI_UI::input("name=wpi_settings[chargify][domain]&value={$wpi_settings['chargify']['domain']}")?></td>
              </tr>
              <tr class="wpi_something_advanced_wrapper">
                <th><?php _e("API Key", WPI); ?></th>
                <td><?php echo WPI_UI::input("name=wpi_settings[chargify][api_key]&value={$wpi_settings['chargify']['api_key']}")?></td>
              </tr>
            </table> <?php
          }
          
          if( $this->valid_api_key ) { ?>
            <h3><?php _e( 'Chargify Product Information', WPI ); ?></h3> <?php
            if( !$products ) { ?>

              <?php _e( 'No products currently found, please update your API settings and ', WPI ); ?>
              <a id="wpi_ajax_check_chargify_products" href="#" class="button"><?php _e( 'Check for Chargify Products', WPI ); ?></a> <?php

            }else{ ?>

              <table class="form-table">
                <tr class="wpi_something_advanced_wrapper">
                  <th><?php _e("Update Products", WPI); ?></th>
                  <td><a id="wpi_ajax_check_chargify_products" href="#" class="button"><?php _e( 'Go', WPI ); ?></a></td>
                </tr>
                <tr class="wpi_something_advanced_wrapper">
                  <th><?php _e("Products Last Updated At", WPI); ?></th>
                  <td><?php echo wpi_chargify::format_date( $wpi_settings[ 'chargify' ][ 'products_last_updated' ] ); ?></td>
                </tr>
                <tr class="wpi_something_advanced_wrapper">
                  <th><?php _e("Management Shortcode", WPI); ?></th>
                  <td><code>[wpi_chargify_frontend_manage]</code></td>
                </tr>
                <tr class="wpi_something_advanced_wrapper">
                  <th colspan="2"><?php _e("Products", WPI); ?></th>
                </tr>
                <tr class="wpi_something_advanced_wrapper">
                  <td colspan="2">

                    <table class="wp-list-table widefat wpi_chargify_products">
                        <thead>
                          <tr>
                            <th class="name"><?php _e( 'Name', WPI ); ?></th>
                            <th class="trial"><?php _e( 'Trial', WPI ); ?></th>
                            <th class="price"><?php _e( 'Price', WPI ); ?></th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th><?php _e( 'Name', WPI ); ?></th>
                            <th><?php _e( 'Trial', WPI ); ?></th>
                            <th><?php _e( 'Price', WPI ); ?></th>
                          </tr>
                        </tfoot>
                      <tbody> <?php
                        foreach( $products as $product ) {
                          $alternate = ( isset( $alternate ) && !$alternate ? 'alternate' : '' ); ?>
                          <tr class="product <?php echo $alternate; ?>">
                            <td>
                              <?php echo $product->product_family->name; ?> - <?php echo $product->name; ?>
                              <div class="shortcode">
                                <code>[wpi_chargify_product name="<?php echo $product->handle; ?>"]</code>
                              </div>
                            </td>
                            <td><?php echo wpi_chargify::get_trial_line( $product ); ?></td>
                            <td><?php echo wpi_chargify::get_price_line( $product ); ?></td>
                          </tr> <?php
                        } ?>
                      </tbody>
                    </table>

                  </td>
                </tr> <?php 
                if( isset( $components ) && is_array( $components ) && count( $components ) ) { ?>
                  <tr class="wpi_something_advanced_wrapper">
                    <th colspan="2"><?php _e("Components", WPI); ?></th>
                  </tr>
                  <tr class="wpi_something_advanced_wrapper">
                    <td colspan="2">

                      <table class="wp-list-table widefat wpi_chargify_components">
                        <thead>
                          <tr>
                            <th class="name"><?php _e( 'Name', WPI ); ?></th>
                            <th class="trial"><?php _e( 'Type', WPI ); ?></th>
                            <th class="price"><?php _e( 'Price', WPI ); ?></th>
                          </tr>
                        </thead>
                        <tfoot>
                          <tr>
                            <th><?php _e( 'Name', WPI ); ?></th>
                            <th><?php _e( 'Type', WPI ); ?></th>
                            <th><?php _e( 'Price', WPI ); ?></th>
                          </tr>
                        </tfoot>
                        <tbody> <?php
                          foreach( $products as $product ){
                            if( isset( $last_component ) && $last_component == $product->product_family->id ) continue;
                            if( !is_array( $components[ $product->product_family->id ] ) || !count( $components[ $product->product_family->id ] ) ) continue;
                            foreach( $components[ $product->product_family->id ] as $component ){
                              $alternate = ( isset( $alternate ) && !$alternate ? 'alternate' : '' ); ?>
                              <tr class="component <?php echo $alternate; ?>">
                                <td><?php echo $product->product_family->name; ?> - <?php echo $component[ 'name' ]; ?></td>
                                <td><?php echo wpi_chargify::get_component_type_line( $component ); ?></td>
                                <td><?php echo wpi_chargify::get_component_price_line( $component ); ?></td>
                              </tr> <?php
                              $last_component = $product->product_family->id;
                            }
                          } ?>
                        </tbody>
                      </table>

                    </td>
                  </tr> <?php
                } ?>
              </table> <?php
            }
          } ?>
        </div> <!-- /wpi_box_content -->
        <div class="wpi_box_footer">
          <?php //pr( json_decode( $wpi_settings[ 'chargify' ][ 'products' ], ARRAY_A ), 1 ); ?>
          <?php //pr( json_decode( $wpi_settings[ 'chargify' ][ 'components' ], ARRAY_A ), 1 ); ?>
        </div>
      </div>

      <script type="text/javascript">
        jQuery( document ).ready(function() {
          /** Check for product updates */
          jQuery( "#wpi_ajax_check_chargify_products" ).click( function( e ){

            e.preventDefault();
            jQuery('.plugin_status').remove();
            jQuery.post( ajaxurl, { action: 'wpi_ajax_check_chargify_products', from_ajax: '1' }, function( data ) {
              message = "<div class='plugin_status updated fade'><p>" + data + "</p></div>";
              jQuery(message).insertAfter("h2");
            } );

          } );
        } );
      </script> <?php
    }else{
      /** Chargify is disabled */ ?>
      <div class="wpi_box chargify">
        <div class="wpi_box_header">
          <b><?php _e( 'Subscription Based Payments via Chargify', WPI ); ?></b>
        </div>
        <div class="wpi_box_content">
          <p><?php _e( 'Did you know that you can now accept subscription payments using <a href="http://chargify.com" target="_blank">Chargify</a> and our <a href="#">Single Page Checkout</a> Premium Feature?' ); ?></p>
        </div>
        <div class="wpi_box_footer clearfix">
          <?php _e( '<a href="#" target="_blank">Tell Me More</a>', WPI ); ?>
        </div>
      </div> <?php
    }
  }

  /**
   * This function checks for new chargify products and saves it to WPI settings
   */
  function check_chargify_products( ){
    global $wpi_settings;

    $from_ajax = ( isset( $_REQUEST[ 'from_ajax' ] ) && $_REQUEST[ 'from_ajax' ] ? true : false );

    try{

      /** Create the object */
      $wpi_chargify = new Chargify( $wpi_settings[ 'chargify' ][ 'domain' ], $wpi_settings[ 'chargify' ][ 'api_key' ] );
      /** Try to get the products */
      $products = $wpi_chargify->get_products();
      /** If we can't get products, something is wrong */
      if( !$products ) throw new Exception( __('Oops, something is wrong with the API. Did you remember to save the WPI settings first?', WPI ) );
      /** We've got products, lets try to get the associated components */
      $components = array();
      foreach( (array) $products as $p ){
        $components[ $p->product_family->id ] = $wpi_chargify->get_components( $p->product_family->id );
      }
      
      /** Now that we have our products, lets try to save the to the WPI settings predefined items list */
      if( !isset( $wpi_settings['predefined_services'] ) || !is_array( $wpi_settings['predefined_services'] ) ){
        $wpi_settings['predefined_services'] = array();
      }
      /** First, loop through, and remove all the ones which came from chargify */
      foreach( $wpi_settings[ 'predefined_services' ] as $key => $value ){
        if( isset( $value[ 'from_chargify' ] ) ){
          unset( $wpi_settings[ 'predefined_services' ][ $key ] );
        }
      }
      /** Now, loop through the settings, and save our wpi options again */
      foreach( $products as $product ){
        $wpi_settings[ 'predefined_services' ][] = array(
          'name' => wpi_chargify::get_product_title( $product ),
          'description' => __( 'This is a subscription based product. ', WPI ) . $product->description,
          'quantity' => 1,
          'price' => $product->price_in_cents / 100,
          'tax' => false,
          'from_chargify' => true,
        );
      }
      
      /** Otherwise, lets save the products to the WPI settings, and then save */
      $wpi_settings[ 'chargify' ][ 'products' ] = json_encode( $products );
      $wpi_settings[ 'chargify' ][ 'components' ] = json_encode( $components );
      $wpi_settings[ 'chargify' ][ 'products_last_updated' ] = date( 'c' );
      $wpi_settings[ 'chargify' ][ 'enabled' ] = true;
      /** Save the settings */
      $wpi_core = WPI_Core::getInstance();
      $wpi_core->Settings->SaveSettings( $wpi_settings );

      if( $from_ajax ) die( __( 'Products updated. Please <a href="javascript:window.location.reload();">refresh</a>.', WPI ) );
      else return $products;

    }catch( Exception $e ){

      /** We don't have anything now */
      $wpi_settings[ 'chargify' ][ 'products' ] = false;
      $wpi_settings[ 'chargify' ][ 'components' ] = false;
      $wpi_settings[ 'chargify' ][ 'products_last_updated' ] = date( 'c' );
      $wpi_settings[ 'chargify' ][ 'enabled' ] = false;
      /** Save the settings */
      $wpi_core = WPI_Core::getInstance();
      $wpi_core->Settings->SaveSettings( $wpi_settings );

      if( $from_ajax ) die( $e->getMessage() );
      else return false;

    }

  }

  /******************************************************************
   * AJAX functions
   ******************************************************************/

  /**
   * Handles our chargify processing
   */
  function chargify_checkout(){
    global $post, $wpi_settings, $wp_version;

    $data = array();
    parse_str($_REQUEST['data'], $data);

    /** Modify it to say we're back to chargify */
    $data[ 'wpi_checkout' ][ 'payment_method' ] = 'wpi_chargify';

    /** Validate our data here */
    $ret = array();

    /** Emulate the predefined item */
    $product = false;
    foreach( json_decode( $wpi_settings[ 'chargify' ][ 'products' ] ) as $t ){
      if( $t->handle == $_REQUEST[ 'chargify_product' ] ) $product = $t;
    }

    /** Required */
    $required = array(
      'first_name' => __( 'You must enter your first name.', WPI ),
      'last_name' => __( 'You must enter your last name.', WPI ),
      'user_email' => __( 'You must enter your e-mail.', WPI ),
      'cc_number' => __( 'You must enter your credit card number.', WPI ),
      'cc_expiration' => __( 'You must enter your credit card expiration date.', WPI ),
      'cc_code' => __( 'You must enter your credit card CVV code.', WPI ),
      'city' => __( 'You must enter your city.', WPI ),
      'state' => __( 'You must enter your state.', WPI ),
      'streetaddress' => __( 'You must enter your address.', WPI ),
      'country' => __( 'You must enter your country.', WPI ),
      'zip' =>  __( 'You must enter your zip.', WPI ),
    );

    /** Check empties */
    foreach( $required as $key => $value ){
      if( empty( $data[ 'wpi_checkout' ][ 'billing' ][ $key ] ) ){
        $return[ 'payment_status' ] = 'validation_fail';
        $return[ 'missing_data' ][ $key ] = $value ;
      }
    }

    /** Fix up our credit card numbers */
    $t = explode( '/', $data[ 'wpi_checkout' ][ 'billing' ][ 'cc_expiration' ] );
    if( count( $t ) == 1 || !preg_match( '/\d\d/', $t[ 0 ] ) || !preg_match( '/\d\d\d\d/', $t[ 1 ] ) ){
        $return[ 'payment_status' ] = 'validation_fail';
        $return[ 'missing_data' ][ 'cc_expiration' ] =  __( 'Please input the credit card expiration date as "MM/YYYY".', WPI );
    }

    /** If its bad, die */
    if( count( $return ) ) die( json_encode( $return ) );

    /** Send it on to SPC */
    wpi_spc::wpi_checkout_process( $data );

  }
  
  /**
   * Attempts to get customer subscriptions, via WP user ID, or WP object
   * @param $user mixed Either the ID or the user object itself
   */
  function get_active_subscriptions_for_user( $user ){

    global $wpi_settings;
    
    /** If we don't have the user object, get it */
    if( is_numeric( $user ) ){
      $user = get_user_by( 'id', $user );
    }
    
    try{
      /** Shorthand variables for settings, and chargify object */
      $s =& $wpi_settings[ 'chargify' ];
      $c = new Chargify( $s[ 'domain' ], $s[ 'api_key' ] );

      /** If we aren't enabled, then we need to return right away */
      if( !$s[ 'enabled' ] ) throw new Exception( __( 'The Chargify API is not enabled.', WPI ) );
      
      /** Get the e-mail, and try to find the customer */
      $email = $user->data->user_email;
      /** Get the existing customer */
      $customer = $c->get_customer( $email, 'local' );
      
      /** If we don't have a result, throw an exception */
      if( !$customer ) throw new Exception( __( 'Customer can not be found.', WPI ) );
      
      /** Now that we have a customer, try to grab the subscriptions */
      $subs = $c->get_subscriptions_by_customer( $customer->id );
      
      $ret = array();
      /** Loop through it, and ensure the subscriptions are active */
      foreach( $subs as $sub ){
        if( $sub->state != 'active' && $sub->state != 'trialing' ) continue;
        $ret[ $sub->id ] = $sub;
      }
      
      /** If we have one, return it */
      if( !count( $ret ) ) throw new Exception( __( 'No active subscriptions found for the user.', WPI ) );
      
      return $ret;
    
    }catch( Exception $e ){

      /** Do nothing with the message for now */
      return false;
    
    }
  
  }
  
  

  /**
   * Attempts to start a subscription
   */
  function start_subscription( $args ){
    global $wpi_settings;

    try{
      /** Shorthand variables for settings, and chargify object */
      $s =& $wpi_settings[ 'chargify' ];
      $c = new Chargify( $s[ 'domain' ], $s[ 'api_key' ] );

      /** If we aren't enabled, then we need to return right away */
      if( !$s[ 'enabled' ] ) return __( 'The Chargify API is not enabled.', WPI );

      /** Customer Data */
      $customer_data = array(
        'first_name' => $args[ 'payer_first_name' ],
        'last_name' => $args[ 'payer_last_name' ],
        'address' => $args[ 'address' ],
        'city' => $args[ 'city' ],
        'state' => $args[ 'state' ],
        'zip' => $args[ 'zip' ],
        'country' => $args[ 'country' ],
        'email' => $args[ 'payer_email' ],
        'phone' => $args[ 'phone_number' ],
        'reference' => $args[ 'payer_email' ]
      );

      /** Get the existing customer */
      $customer = $c->get_customer( $args[ 'payer_email' ], 'local' );

      /** If we don't have a customer, create a new one */
      if( !$customer ){
        $customer = $c->create_customer( $customer_data );
      }else{
        /** Update the old customer */
        $customer = $c->edit_customer( $customer->id, $customer_data );
      }

      /** Split our date */
      $t = explode( '/', $args [ 'cc_expiration' ] );

      /** Do our ccard info */
      $cc_data = array(
        'first_name' => $args[ 'payer_first_name' ],
        'last_name' => $args[ 'payer_last_name' ],
        'billing_address' => $args[ 'address' ],
        'billing_city' => $args[ 'city' ],
        'billing_state' => $args[ 'state' ],
        'billing_zip' => $args[ 'zip' ],
        'billing_country' => $args[ 'country' ],
        'full_number' => $args[ 'cc_number' ],
        'expiration_month' => $t[ 0 ],
        'expiration_year' => $t[ 1 ],
        'cvv' => $args[ 'cc_code' ],
      );

      /** Create the subscription */
      $subscription = $c->create_subscription( array(
        'product_handle' => $_REQUEST[ 'chargify_product' ],
        'customer_id' => $customer->id,
        'credit_card_attributes' =>  $cc_data
      ) );

      /** We're done, lets get outta here */
      if( !$subscription ) throw new Exception( __( 'Could not create subscription.', WPI ) );

      return $subscription->id;

    }catch( Exception $e ){

      return $e->getMessage();

    }
  }

  /**
   * This function is an ajax call that is used to cancel a subscription for the current user
   */
  function chargify_cancel(){
    global $wpi_settings, $current_user;

    $is_ajax = ( isset( $_REQUEST[ 'from_ajax' ] ) ? true : false );
    try{

      /** Do vars */
      $s =& $wpi_settings[ 'chargify' ];
      $c = new Chargify( $s[ 'domain' ], $s[ 'api_key' ] );

      /** If we're not logged in */
      if( !is_user_logged_in() ) throw new Exception ( __( 'Cannot cancel, you\'re no longer logged in.', WPI ) );

      /** Try to cancel */
      $subscription = $c->get_subscription( $_REQUEST[ 'id' ] );

      /** If we don't have it, we quit */
      if( !$subscription ) throw new Exception ( __( 'This subscription does not exist.', WPI ) );

      /** Check the current user against the subscription */
      if( $current_user->data->user_email != $subscription->customer->email ) throw new Exception( __( 'This is not your subscription.', WPI ) );

      /** Finally here, lets do it */
      $res = $c->cancel_subscription( $_REQUEST[ 'id' ] );

      /** We failed, throw the message */
      if( !$res ) throw new Exception ( __( 'Could not remove subscription.', WPI ) );

      /** Go ahead and die or return */
      if( $is_ajax ){

        die( json_encode( array (
          'success' => true,
          'data' => array(),
          'message' => sprintf( __( 'Subscription # %s successfully canceled.', WPI ), $_REQUEST[ 'id' ] )
        ) ) );

      }else return true;

    }catch( Exception $e ){

      if( $is_ajax ){

        die( json_encode( array (
          'success' => false,
          'data' => array(),
          'message' => $e->getMessage(),
        ) ) );

      }else{

        return $e->getMessage();

      }

    }
  }

  /******************************************************************
   * Short code functions
   ******************************************************************/

  /**
   * This shortcode handler handles our product_family shortcode
   */
  function shortcode_product( $default_atts = "",  $content = null, $code = "" ){
    global $wpi_settings;

    if( !is_array( $default_atts ) ){
      $default_atts = array();
    }

    /** Load up our defaults */
    $atts = array_merge( array(
      'show_header' => 'true', /** Show the header elements */
      'show_header_title' => 'true', /** Show the header title element */
      'show_prices' => 'true', /** Do we want to show the prices? */
      'show_components' => 'true', /** Do we want to show the components? */
      'show_description' => 'true', /** Show the product description? */
      'do_shortcodes' => 'true', /** In the description, do we want to execute shortcodes? */
      'form_title' => 'Checkout', /** The form checkout text to use */
      'show_form_title' => 'true', /** Do we want to show the form title */
      'wrapper_classes' => '', /** Extra classes for the wrapper */
      'header_classes' => '', /** Extra classes for the header div */
      'form_classes' => '', /** Extra classes for the form div */
      
    ), $default_atts );

    /** Loop through, and get all the products in the family */
    $product = false;
    foreach( json_decode( $wpi_settings[ 'chargify' ][ 'products' ] ) as $t ){
      if( $t->handle == $atts[ 'name' ] ) $product = $t;
    }
    
    /** Loop through, and get the components as well */
    $components = false;
    $t = json_decode( $wpi_settings[ 'chargify' ][ 'components' ], ARRAY_A );
    if( isset( $t[ $product->product_family->id ] ) ){
      $components = $t[ $product->product_family->id ];
    }
      
    /** If we don't have a product, get out of here */
    if( !$product ) return __( 'Could not find this Chargify product.', WPI );

    /** We need to emulate SPC features */
    $wpi_settings[ 'billing' ][ 'wpi_authorize' ][ 'allow' ] = 'true';

    /** Do the shortcode, but intercept everything, cause we've gotta add some things */
    ob_start(); ?>
    <div class="chargify_checkout <?php echo $atts[ 'form_classes' ]; ?>">
      <?php if( $atts[ 'show_form_title' ] == 'true' ): ?>
        <h2><?php _e( $atts[ 'form_title' ], WPI ); ?></h2>
      <?php endif;
      
      echo do_shortcode( '[wpi_checkout title="'.addcslashes( wpi_chargify::get_product_title( $product ), '"').'" item="'.addcslashes( wpi_chargify::get_product_title( $product ), '"' ).'" gateways="wpi_authorize"]' ); ?>
    </div> <?php
    $form_code = ob_get_clean();

    /** Change the action so that chargify can use it */
    $form_code = str_ireplace( "'wpi_checkout_process'", "'wpi_chargify_checkout', chargify_product: '".addcslashes( $product->handle, "'" )."'", $form_code );
    
    /** If we're getting the header */
    if( $atts[ 'show_header' ] == 'true' ){
      ob_start(); ?>
      <div class="wpi_chargify_header <?php echo $atts[ 'header_classes' ]; ?>">
        <?php if( $atts[ 'show_header_title' ] == 'true' ): ?>
          <h2><?php echo $product->product_family->name; ?> - <?php echo $product->name; ?></h2>
        <?php endif; ?>
        <?php if( $atts[ 'show_description' ] == 'true' && $product->description ): ?>
          <p> <?php
            if( $atts[ 'do_shortcodes' ] == 'true' ){
              echo do_shortcode( $product->description );
            }else{
              echo $product->description;
            } ?>
          </p>
        <?php endif; ?>
        <?php if( $atts[ 'show_prices' ] == 'true' ): ?>
          <h3><?php _e( 'Pricing', WPI ); ?></h3>
          <dl>
            <dt><?php _e( 'Trial', WPI ); ?>:</dt>
            <dd><?php echo wpi_chargify::get_trial_line( $product ); ?></dd>
            <dt><?php _e( 'Full Cost', WPI ); ?>:</dt>
            <dd><?php echo wpi_chargify::get_price_line( $product ); ?></dd>
          </dl>
        <?php endif; ?>
        <?php if( $atts[ 'show_components' ] == 'true' && $components ): ?>
          <h3><?php _e( 'Components (Add-Ons)', WPI ); ?></h3>
          <dl>
            <?php foreach( $components as $c ): ?>
              <dt><?php echo $c[ 'name' ]; ?></dt>
              <dd>
                <?php echo wpi_chargify::get_component_price_line( $c ); ?> / <?php echo wpi_chargify::get_component_type_line( $c ); ?> Charging
              </dd>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
      </div> <?php
      $head_code = ob_get_clean();
      $form_code = $head_code.$form_code;
    }

    /** Return the form code */
    return '<div class="wpi_chargify_wrapper ' . $atts[ 'wrapper_classes' ] . '">' . $form_code . "</div>";

  }

  /**
   * Handles our subscription management on the frontend
   */
  function shortcode_frontend_manage( $default_atts = "",  $content = null, $code = "" ){
    global $wpi_settings, $current_user;

    try{
      if( !is_array( $default_atts ) ){
        $default_atts = array();
      }

      /** Load up our defaults */
      $atts = array_merge( array(
        'show_mine' => 'true',
        'show_available' => 'true',
        'do_shortcodes' => 'true', /** Do we execute short codes in descriptions */
        'family' => 'false',
        'available_title' => __( 'Available Subscriptions', WPI ),
        'mine_title' => __( 'My Subscriptions', WPI ),
      ), $default_atts );

      /** Init ou variables */
      $s =& $wpi_settings[ 'chargify' ];
      $c = new Chargify( $s[ 'domain' ], $s[ 'api_key' ] );
      $products = json_decode( $s[ 'products' ] );
      /** We have our user info, lets try to get their subscriptions */
      if( is_user_logged_in() ){
        $email = $current_user->data->user_email;
      }else{
        $email = false;
      }

      /** Make the call */
      $customer = $c->get_customer( $email, 'local' );
      if( !$customer ){
        $my_subs = array();
      }else{
        $my_subs = $c->get_subscriptions_by_customer( $customer->id );
        if(!$my_subs) $my_subs = array();
        /** Loop through, unsetting the bad ones */
        foreach( $my_subs as $key => $value ){
          if( $value->state == 'canceled' ) unset( $my_subs[ $key ] );
        }
      }

      /** We have our subscriptions */
      usort( $my_subs, 'wpi_chargify::sort_by_product_family' );
      usort( $products, 'wpi_chargify::sort_by_product_family' );

      /** Go through, and create our product family array */
      $families = array();
      $todo_families = array();
      if( $atts[ 'family' ] != 'false' ){
        $todo_families = explode( ',', $atts[ 'family' ] );
      }
      foreach( $products as $product ){
        if( $atts[ 'family' ] == 'false' || in_array( $product->product_family->handle, $todo_families ) ){
          if( !in_array( $product->product_family->name, array_keys( $families ) ) ) $families[ $product->product_family->name ] = $product->product_family->description;
        }
      }

      /** Catch the content */
      ob_start(); ?>

      <?php if( $atts[ 'show_mine' ] == 'true' && $email ): ?>
        <!-- My Subs First -->
        <div class="wpi_chargify_subs_wrap wpi_chargify_my_subs_wrap"> <?php

         if( $atts[ 'mine_title' ] != 'false' ){ ?>
            <h2><?php echo $atts[ 'mine_title' ]; ?></h2> <?php
          } ?>

          <table class="table table-striped">
            <thead>
              <tr>
                <th class="<?php echo sanitize_title( 'Product' ); ?>"><?php _e( 'Product', WPI ); ?></th>
                <th class="<?php echo sanitize_title( 'Expires' ); ?>"><?php _e( 'Expires', WPI ); ?></th>
                <th class="<?php echo sanitize_title( 'Card' ); ?>"><?php _e( 'Card', WPI ); ?></th>
                <th class="<?php echo sanitize_title( 'Cost' ); ?>"><?php _e( 'Cost', WPI ); ?></th>
                <th class="<?php echo sanitize_title( 'Status' ); ?>"><?php _e( 'Status', WPI ); ?></th>
              </tr>
            </thead>
            <tbody> <?php

              foreach( $my_subs as $sub ){ ?>
                <tr for_subscription="<?php echo $sub->id; ?>">
                  <td>
                    <div class="title-row">
                      <span class="actions">
                        <!-- <a href="#"><?php _e( 'Update', WPI ); ?></a> | -->
                        <a class="cancel" for_subscription="<?php echo $sub->id; ?>" href="#"><?php _e( 'Cancel', WPI ); ?></a>
                      </span>
                      <?php echo wpi_chargify::get_product_title( $sub->product ); ?>
                    </div>
                  </td>
                  <td><?php echo wpi_chargify::format_date( $sub->current_period_ends_at, false ); ?></td>
                  <td><?php echo wpi_chargify::get_last_four( $sub->credit_card->masked_card_number ); ?></td>
                  <td><?php echo wpi_chargify::get_price_line( $sub->product ); ?></td>
                  <td><?php echo wpi_chargify::format_product_sate( $sub->state ); ?></td>
                </tr> <?php
              }

              if( !count( $my_subs ) ){ ?>
                <tr>
                  <td colspan="5">
                    <?php _e( 'Oops, it looks like you\'re not subscribed to anything.', WPI ); ?>
                  </td>
                </tr> <?php
              } ?>

            </tbody>
          </table>

        </div>
        <!-- End my subs -->
      <?php endif; ?>

      <?php if( $atts[ 'show_available' ] == 'true' ): ?>
        <!-- Availalbe Subs -->
        <div class="wpi_chargify_subs_wrap wpi_chargify_avail_subs_wrap"> <?php

          if( $atts[ 'available_title' ] != 'false' ){ ?>
            <h2><?php echo $atts[ 'available_title' ]; ?></h2> <?php
          }

          foreach( $families as $family => $description ){ ?>

            <h3><?php echo $family; ?></h3> <?php

            if( $description ){ ?>

              <p class="description"> <?php
                if( $atts[ 'do_shortcodes' ] == 'true' ){
                 echo do_shortcode( $description );
                }else{
                  echo $description;
                } ?>
              </p> <?php

            } ?>

            <table class="table table-striped">
              <thead>
                <tr>
                  <th class="<?php echo sanitize_title( 'Product' ); ?>"><?php _e( 'Plan', WPI ); ?></th>
                  <th class="<?php echo sanitize_title( 'Cost' ); ?>"><?php _e( 'Trial', WPI ); ?></th>
                  <th class="<?php echo sanitize_title( 'Cost' ); ?>"><?php _e( 'Cost', WPI ); ?></th>
                </tr>
              </thead>
              <tbody> <?php

                foreach( $products as $product ){
                  if( $product->product_family->name != $family ) continue; ?>

                  <tr for_product="<?php echo $product->handle; ?>">
                    <td>
                    <div class="title-row">
                      <span class="actions">
                        <a href="#" for_product="<?php echo $product->handle; ?>" action="subscribe"><?php _e( 'Subscribe', WPI ); ?></a> <?php
                        if( $product->description ){ ?>
                          | <a href="#" for_product="<?php echo $product->handle; ?>" action="more-info"><?php _e( 'More Info', WPI ); ?></a> <?php
                        } ?>
                      </span>
                      <?php echo $product->name; ?>
                    </div>
                    </td>
                    <td><?php echo wpi_chargify::get_trial_line( $product ); ?></td>
                    <td><?php echo wpi_chargify::get_price_line( $product ); ?></td>
                  </tr> <?php

                  if( $product->description ){ ?>

                    <tr class="more-info" for_product="<?php echo $product->handle; ?>">
                      <td colspan="3">
                        <h2>
                          <a href="#" style="display:none;">Close</a>
                          <?php _e( 'About', WPI ); ?> <?php echo wpi_chargify::get_product_title( $product ); ?>
                        </h2>

                        <p> <?php
                          if( $atts[ 'do_shortcodes' ] == 'true' ){
                            echo do_shortcode( $product->description );
                          }else {
                            echo $product->description;
                          } ?>
                        </p>

                      </td>
                    </tr> <?php

                  } ?>

                  <tr class="subscribe" for_product="<?php echo $product->handle; ?>">
                    <td colspan="3">
                      <h2>
                        <a href="#" style="display:none;">Close</a>
                        <?php _e( 'Subscribe to', WPI ); ?> <?php echo wpi_chargify::get_product_title( $product ); ?>
                      </h2>
                      <?php echo do_shortcode( '[wpi_chargify_product do_shortcodes="'.addcslashes( $atts[ 'do_shortcodes' ], '"' ).'" name="'.addcslashes( $product->handle, '"' ).'" show_header="false"]' ); ?>
                    </td>
                  </tr>

                  <?php

                } ?>

              </tbody>
            </table> <?php

          } ?>

        </div>
        <!-- End available -->
      <?php endif; ?>

      <script type="text/javascript" language="javascript">

        if( typeof jQuery == 'function' ){
          jQuery( document ).ready( function() {

            /** Hide/show all our actions */
            jQuery( '.wpi_chargify_subs_wrap tr.subscribe, .wpi_chargify_subs_wrap tr.more-info' ).hide();
            jQuery( '.wpi_chargify_subs_wrap .subscribe a, .wpi_chargify_subs_wrap .more-info a' ).show();

            /** Attach the hover event for the title elements */
            jQuery( '.wpi_chargify_subs_wrap table tr' ).hover( function( e ){
              jQuery( this ).find( 'span.actions' ).show();
            }, function( e ){
              jQuery( this ).find( 'span.actions' ).hide();
            } );

            /** Handle my subscribe and more info links */
            jQuery( '.wpi_chargify_subs_wrap .title-row .actions a' ).click( function( e ) {

              e.preventDefault();
              /** Get the class name, and expand the next div that matches it */
              var product = jQuery( this ).attr( 'for_product' );
              var action = jQuery( this ).attr( 'action' );

              jQuery( '.wpi_chargify_subs_wrap tr.more-info:visible' ).hide();
              jQuery( '.wpi_chargify_subs_wrap tr.subscribe:visible' ).hide();
              jQuery( '.wpi_chargify_subs_wrap tr[for_product=' + product + '].' + action ).show();

            } );

            /** Close buttons */
            jQuery( '.wpi_chargify_subs_wrap .subscribe a, .wpi_chargify_subs_wrap .more-info a' ).click( function( e ){

              e.preventDefault();
              /** Close the parent above the parent */
              jQuery( this ).parent().parent().parent().hide();

            } );

            /** Cancel button */
            jQuery( '.wpi_chargify_subs_wrap a.cancel' ).click( function( e ){

              e.preventDefault();
              /** Remove the old */
              jQuery( '.wpi_chargify_message' ).remove();
              /** Grab the id */
              var id = jQuery( this ).attr( 'for_subscription' );
              var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
              jQuery.post( ajaxurl, { action: 'wpi_chargify_cancel', from_ajax: '1', id: id }, function( data ) {

                jQuery( '.wpi_chargify_my_subs_wrap h2' ).after( '<div class="wpi_chargify_message">' + data.message + '</div>' );

                if( data.success ){

                  /** Remove the row */
                  jQuery( 'tr[for_subscription=' + id + ']' ).remove();

                }

              }, 'json' );

            } );

          } );
        }

      </script>

      <style type="text/css">
        .wpi_chargify_subs_wrap div.title-row { position: relative; }
        .wpi_chargify_subs_wrap span.actions {
          font-size: 0.8em;
          position: absolute;
          right: 0;
          top: -1.5em;
          background-color: white;
          display: block;
          padding: 2px 4px;
          display: none;
        }
        .wpi_chargify_subs_wrap .product { width: 30%; }
        .wpi_chargify_subs_wrap .cost { width: 25%; }
        .wpi_chargify_subs_wrap .subscribe td, .wpi_chargify_subs_wrap .more-info td {
          padding: 20px;
          background-color: #eeeeee;
        }
        .wpi_chargify_subs_wrap .more-info p {
          margin-bottom: 0;
        }
        .wpi_chargify_subs_wrap .subscribe h2,
        .wpi_chargify_subs_wrap .more-info h2 {
          text-align: left;
          border-bottom: 1px solid black;
          font-weight: normal;
          font-size: 1.3em;
          margin-bottom: 0;
          position: relative;
        }
        .wpi_chargify_subs_wrap .subscribe h2 a,
        .wpi_chargify_subs_wrap .more-info h2 a {
          display: block;
          float: right;
          font-size: .5em;
          position: absolute;
          bottom: 0;
          right: 0;
        }
        .wpi_chargify_subs_wrap .wpi_checkout_customer_information {
          margin-top: 0 !important;
        }
        .wpi_chargify_message {
          margin-bottom: 1em;
          background-color: #eeeeee;
          padding: 5px 10px;
        }

      </style> <?php

      /** Save and return the content */
      return ob_get_clean();

    }catch( Exception $e ){

      return 'Oops, there\s been an issue: '.$e->getMessage();

    }
  }

  /******************************************************************
   * Simple formatting functions
   ******************************************************************/

  /** Takes a date, and returns it in the chargify format */
  function format_date( $date, $include_time = true ){
    if( $include_time ){
      return date( 'F j, Y g:i A', strtotime( $date ) );
    }else{
      return date( 'F j, Y', strtotime( $date ) );
    }
  }

  /** Formats a it for the price line */
  function get_price_line( $product ){ return '$'.wp_invoice_currency_format( $product->price_in_cents / 100).' for '.$product->interval.' '.$product->interval_unit.'(s)'; }

  /** Gets our properly formatted header */
  function get_product_title( $product ){ return $product->product_family->name.' - '.$product->name; }
  
  /** Formats a component price line */
  function get_component_price_line( $component ){
    global $wpi_chargify;
    return '$'.wp_invoice_currency_format( $component[ 'unit_price' ] ) . ' ' . $wpi_chargify->term_map[ $component [ 'pricing_scheme' ] ] . ' (' . ucwords( $component[ 'unit_name' ] ) . ')';
  }
  
  /** Formats a component type line */
  function get_component_type_line( $component ){
    global $wpi_chargify;
    return ucwords( $wpi_chargify->term_map[ $component[ 'kind' ] ] );
  }

  /** Formats it for the trial line */
  function get_trial_line( $product ){
    if( !$product->trial_interval ){
      return __( 'N/A', WPI );
    }else{
      return '$'.wp_invoice_currency_format( $product->trial_price_in_cents / 100 ).' for '.$product->trial_interval.' '.$product->trial_interval_unit.'(s)';
    }
  }

  /** Gets the true last four of a CC number */
  function get_last_four( $card ){ return substr( str_ireplace( '-', '', $card ), -4 ); }

  /** State of the product */
  function format_product_sate( $state ){ return ucfirst( $state ); }

  /** This function is a custom sorter to sort by product family name */
  function sort_by_product_family( $a, $b ){

    /** If we're comparing from a subscription feed */
    if( is_object( $a->product ) ){
      $a = $a->product;
      $b = $b->product;
    }

    if( $a->product_family->name == $b->product_family->name ){
      if( $a->price_in_cents == $b->price_in_cents ) return 0;
      if( $a->price_in_cents < $b->price_in_cents ) return -1;
      return 1;
    }else{
      return strcmp( $a->product_family->name, $b->product_family->name );
    }

  }

}

/** Init the object */
global $wpi_chargify;
$wpi_chargify = new wpi_chargify();