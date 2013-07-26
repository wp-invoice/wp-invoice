<?php
/**
 * Name: PDF
 * Class: wpi_bb
 * Internal Slug: wpi_bb
 * Version: 1.0a
 * Feature ID: N/A
 * Minimum Core Version: 3.07.0
 * Description: Allows the connection to external networks, through the UD API. This is a SaaS feature.
 */
class wpi_bb {

  /**
   * Default feature settings
   */
  private static $default_options = array(
    'network_type' => 'intuit',
  );

  /**
   * Do our supported network types
   */
  private static $supported_network_types = array(
    'intuit' => 'Intuit (Quickbooks Online)',
    'freshbooks' => 'Freshbooks!',
  );

  /*
   * Init feature filters and actions
   * @global object $wpi_settings
   */
  function wpi_premium_loaded() {
    global $wpi_settings;

    /** Make sure we've got settings loaded */
    if( !isset( $wpi_settings[ 'bb' ] ) || !is_array( $wpi_settings[ 'bb' ] ) ) $wpi_settings[ 'bb' ] = wpi_bb::$default_options;

    /** Add our settings page */
    add_filter( 'wpi_settings_tabs', array( __CLASS__, 'wpi_settings_tabs_bb' ) );
  }


  /**
   * Settings tab for this app
   */
  function wpi_settings_tabs_bb( $wpi_settings_tabs ) {

    /** Add the filter */
    $wpi_settings_tabs[ 'bb' ] = array(
      'label' => __( 'Branded Billing', WPI ),
      'position' => 90,
      'callback' => array('wpi_bb', 'wpi_bb_settings')
    );
    return $wpi_settings_tabs;

  }

  /** Setup our UI screen here */
  function wpi_bb_settings(){
    global $wpi_settings;
    $type =& $wpi_settings[ 'bb' ][ 'network_type' ]; ?>

    <!-- Load up my local JavaScript -->
    <script type="text/javascript" src="https://appcenter.intuit.com/Content/IA/intuit.ipp.anywhere.js"></script>
    <script>intuit.ipp.anywhere.setup({
        menuProxy: 'https://jbrw.dyndns.org/invoice/intuit/oauth/bluedotmenu',
        grantUrl: 'https://jbrw.dyndns.org/invoice/intuit/oauth/requesttoken'
      });
    </script>

    <div class="wpi_settings_bb_wrapper"> <!-- So my jQuery doesn't have to search the whole dom -->
      <table class="form-table">
        <tr class="wpi_something_advanced_wrapper">
          <th><?php _e("Select Network Type", WPI); ?></th>
          <td><?php echo WPI_UI::select("id=wpi_settings_bb_network_type&name=wpi_settings[bb][network_type]&values=" . serialize( wpi_bb::$supported_network_types ) . "&current_value={$wpi_settings['bb']['network_type']}"); ?></td>
        </tr>
      </table>

      <table class="form-table form-table-network form-table-wpi-bb-intuit">
          <tr class="wpi_something_advanced_wrapper">
            <th>Connect to Intuit</th>
            <td>
              <ipp:connectToIntuit></ipp:connectToIntuit>
            </td>
        </tr>
      </table>

      <table class="form-table form-table-network form-table-wpi-bb-freshbooks">
          <tr class="wpi_something_advanced_wrapper">
            <th>Connect to Freshbooks</th>
            <td>
              Ok!
            </td>
        </tr>
      </table>
    </div>

    <script type="text/javascript" language="javascript">
      if( typeof jQuery == 'function' ){
        jQuery( document ).ready( function(){

          /** Hide all unselected divs */
          jQuery( '.wpi_settings_bb_wrapper .form-table-network' ).each( function( e ){
            /** If we're not selected, hide it */
            if( !jQuery( this ).hasClass( 'form-table-wpi-bb-<?php echo $type; ?>' ) )  jQuery( this ).hide();
          } );

          /** Hook into the change event */
          jQuery( '#wpi_settings_bb_network_type' ).change( function( e ) {
            var type = jQuery( this ).find( 'option:selected' ).val();
            /** Hide all unselected divs */
            jQuery( '.wpi_settings_bb_wrapper .form-table-network' ).each( function( e ){
              /** If we're not selected, hide it */
              if( !jQuery( this ).hasClass( 'form-table-wpi-bb-' + type ) )  jQuery( this ).hide();
              else jQuery( this ).show();
            } );
          } );

        } );
      }
    </script> <?php
  }
}

/** Init BB premium feature */
add_action('wpi_premium_loaded', array('wpi_bb', 'wpi_premium_loaded'));