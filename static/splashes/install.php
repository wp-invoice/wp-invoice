<style>
  .ud-badge {
    background: url( "<?php echo ud_get_wp_invoice()->path( '/static/images/icon.png', 'url' ); ?>" ) no-repeat center !important;
    background-size: 150px 150px !important;
    box-shadow: none !important;
  }
</style>
<div class="changelog">
  
  <div class="overview">

    <h2><?php printf( __( 'WP-Invoice %s has been installed', ud_get_wp_invoice()->domain ), ud_get_wp_invoice()->args['version'] ); ?></h2>

    <p><?php _e( 'Congratulations! You have just installed brand new version of WP-Invoice plugin. There are some important things that you need to be aware of in order to use our products successfully and with pleasure.', ud_get_wp_invoice()->domain ) ?></p>

    <p><?php _e( 'Please read the following instructions carefully. Explore links below to get more information on our site.', ud_get_wp_invoice()->domain ); ?></p>

    <hr />
    
    <p><i><?php _e( 'WP-Invoice lets WordPress blog owners send itemized invoices to their clients. Ideal for web developers, SEO consultants, general contractors, or anyone with a WordPress blog and clients to bill.', ud_get_wp_invoice()->domain ); ?></i></p>
    
    <p><i><?php printf( __( 'In addition to the default invoicing function WP-Invoice can be extended with a <a href="%s">set of add-ons</a>. They may help you manage your business more effectively.', ud_get_wp_invoice()->domain ), 'https://www.usabilitydynamics.com/products' ); ?></i></p>

    <?php $s = ud_get_wp_invoice(); if( isset( $s->uservoice_url ) ) : ?>
      <hr />
      <p><?php printf( __( 'Do you want to help us to improve %s? Or do you have any idea? We are waiting <a href="%s" target="_blank">feedback</a> from you!', ud_get_wp_invoice()->domain ), ud_get_wp_invoice()->name, $s->uservoice_url  ); ?></p>
    <?php endif; ?>

  </div>
  
  <hr />
  
  <div class="feature-section col two-col">
    
    <div class="col-1">
      
      <h3><?php _e( 'User license management', ud_get_wp_invoice()->domain ); ?></h3>
      
      <h4><?php _e( 'Installation', ud_get_wp_invoice()->domain ); ?></h4>
      
      <p><?php _e( 'In new version of WP-Invoice all your premium features will be as separate plugins. If you are upgrading you will need to activate them one more time.', ud_get_wp_invoice()->domain ); ?></p>

      <p><?php printf( __( 'After you purchased the product, visit your <a href="%s">UD Account</a>. You will find license keys and download links for all your purchased add-ons. Download plugins to your computer and Upload as new plugin on your site.', ud_get_wp_invoice()->domain ), 'https://www.usabilitydynamics.com/account' ); ?></p>
      
    </div>
    
    <div class="col-2 last-feature">
      
      <br /><br /><br />
      
      <h4><?php _e( 'Adding License Keys', ud_get_wp_invoice()->domain ); ?></h4>
      
      <p><?php _e( 'Click to activate plugin, you will see link to the Licenses admin screen. On the Licenses admin screen, you should see all your installed products, with an option to add your license key.', ud_get_wp_invoice()->domain ) ?></p>
      
      <p><?php _e( 'To add a license key:', ud_get_wp_invoice()->domain ); ?></p>
      
      <ul>
        <li><?php _e( 'copy the license key from your UD Account page or receipt email;', ud_get_wp_invoice()->domain ); ?></li>
        <li><?php _e( 'paste the license key into input field for your product;', ud_get_wp_invoice()->domain ); ?></li>
      </ul>
      
    </div>
    
  </div>
  
</div>