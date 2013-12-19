<?php

/**
 * Name: Dashboard Widgets
 * Class: wpi_dashboard_widgets
 * Global Variable: wpi_dashboard_widgets
 * Internal Slug: wpi_dashboard_widgets
 * JS Slug: wpi_dashboard_widgets
 * Version: 1.0
 * Minimum Core Version: 3.08.8
 * Feature ID: 20
 * Description: Adds some useful widgets to the dashboard.
 */

class wpi_dashboard_widgets {

  //** Default options for all widgets separated by keys */
  static $default_options = array(
      'wpi_dw_user_invoices' => array(
          'types2display' => array(
              0 => 'invoice'
          )
      ),
      'wpi_dw_invoice_statistics' => array()
  );

  /**
   * When premium features loaded
   */
  function wpi_premium_loaded() {
    global $wpi_settings;

    //** Register widgets */
    add_action('wp_dashboard_setup', create_function('', ' new WPI_DW_User_Invoices(wpi_dashboard_widgets::$default_options["wpi_dw_user_invoices"]); ') );
    if ( current_user_can( WPI_UI::get_capability_by_level( $wpi_settings['user_level'] ) ) )
      add_action('wp_dashboard_setup', create_function('', ' new WPI_DW_Invoice_Statistics(wpi_dashboard_widgets::$default_options["wpi_dw_invoice_statistics"]); ') );

    //** Ajax actions */
    add_action('wp_ajax_wpi_dw_user_invoices', array(WPI_DW_User_Invoices, 'ajax_handler'));
    add_action('wp_ajax_wpi_dw_invoice_statistics', array(WPI_DW_Invoice_Statistics, 'ajax_handler'));

  }

}

/**
 * Widget Invoice Statistics
 */
class WPI_DW_Invoice_Statistics extends WPI_Dashboard_Widget {

  /**
   * Construct
   * @param type $d
   */
  public function __construct( $d ) {
    $this->widget_id = 'wpi_dw_invoice_statistics';
    $this->widget_title = __( 'Sales Statistics', WPI );

    parent::__construct( $d );
  }

  /**
   * Widget callback
   */
  public function widget() {
    wp_enqueue_script('wpi_chart_js', WPI_URL.'/third-party/Chart/Chart.min.js');
    wp_enqueue_script('wpi_dw_invoice_statistics', WPI_URL.'/core/js/dashboard/widgets/wpi_dw_invoice_statistics.js');
    wp_enqueue_style('wpi_dw_invoice_statistics', WPI_URL.'/core/css/dashboard/widgets/wpi_dw_invoice_statistics.css' );

    ?>
      <img class="loader" src="<?php echo WPI_URL; ?>/core/css/images/ajax-loader-blue.gif" />

      <div class="day_chart chart">
        <p class="sub"><?php _e( 'Last Day', WPI ); ?></p>
        <canvas id="day_chart" height="200"></canvas>
      </div>

      <div class="week_chart chart">
        <p class="sub"><?php _e( 'Last Week', WPI ); ?></p>
        <canvas id="week_chart" height="200"></canvas>
      </div>

      <div class="month_chart chart">
        <p class="sub"><?php _e( 'Last Month', WPI ); ?></p>
        <canvas id="month_chart" height="200"></canvas>
      </div>
    <?php
  }

  /**
   * Ajax handler for widget
   * @global type $wpdb
   */
  function ajax_handler() {
    global $wpdb;

    $now = time();

    switch( $_POST['period'] ) {

      case 'day':
        $stamp = $now - 86400;
        $columns = 24;
        $period_item = 3600;
        break;

      case 'week':
        $stamp = $now - 604800;
        $columns = 7;
        $period_item = 86400;
        break;

      case 'month':
        $stamp = $now - 2592000;
        $columns = 30;
        $period_item = 86400;
        break;

      default: break;
    }

    $last_period = $wpdb->get_results( "SELECT * FROM {$wpdb->base_prefix}wpi_object_log WHERE time > {$stamp} AND action = 'add_payment' ORDER BY time", ARRAY_A );

    $data = array();
    for( $i = 1; $i < $columns+1; $i++ ) {
      $data[$i] = 0;
      foreach( $last_period as $period ) {
        if ( $period['time'] > $stamp+($i-1)*$period_item && $period['time'] <= $stamp+$i*$period_item ) {
          $data[$i] += $period['value'];
        }
      }
    }

    $labels = array_keys( $data );
    $values = array_values( $data );

    if ( !empty( $data ) ) {
      die( json_encode( array( 'success' => true, 'data' => array('labels' => $labels, 'values' => $values) ) ) );
    }

    die( array( 'success' => false ) );
  }
}

/**
 * Widget User Invoices
 */
class WPI_DW_User_Invoices extends WPI_Dashboard_Widget {

  /**
   * Construct
   * @param type $d
   */
  public function __construct( $d ) {
    $this->widget_id = 'wpi_dw_user_invoices';
    $this->widget_title = __( 'My Invoice Statistics', WPI );

    parent::__construct( $d );
  }

  /**
   * Widget callback
   * @global type $current_user
   */
  function widget() {
    wp_enqueue_script('wpi_dw_user_invoices', WPI_URL.'/core/js/dashboard/widgets/wpi_dw_user_invoices.js', array('jquery'));
    wp_enqueue_style('wpi_dw_user_invoices', WPI_URL.'/core/css/dashboard/widgets/wpi_dw_user_invoices.css' );
    ?>
    <img src="<?php echo WPI_URL; ?>/core/css/images/ajax-loader-blue.gif" />
    <?php
  }

  /**
   * Ajax handler for widget
   * @global type $wpi_settings
   */
  function ajax_handler() {
    global $wpi_settings;

    $invoices = array();

    $options = WPI_DW_User_Invoices::get_dashboard_widget_options( 'wpi_dw_user_invoices' );

    $invoices['active'] = WPI_Functions::query(array(
        'status' => array('active'),
        'recipient' => $current_user_id = get_current_user_id(),
        'type' => $options['types2display']
    ));

    $invoices['paid'] = WPI_Functions::query(array(
        'status' => array('paid'),
        'recipient' => $current_user_id,
        'type' => $options['types2display']
    ));

    $invoices['archived'] = WPI_Functions::query(array(
        'status' => array('archived'),
        'recipient' => $current_user_id,
        'type' => $options['types2display']
    ));

    $invoices['trashed'] = WPI_Functions::query(array(
        'status' => array('trashed'),
        'recipient' => $current_user_id,
        'type' => $options['types2display']
    ));

    $total_invoices = 0;

    $active_and_paid = array_merge((array)$invoices['active'], (array)$invoices['paid']);

    $objects = array();
    $r = array(
      'total_paid' => array(),
      'total_unpaid' => array()
    );

    foreach ( $active_and_paid as $post ) {
      $wpi_invoice_object = new WPI_Invoice();
      $wpi_invoice_object->load_invoice( "id={$post->ID}" );
      $objects[ $post->ID ] = $wpi_invoice_object->data;
    }

    foreach ( $objects as $object ) {

      if ( $object[ 'post_status' ] == 'paid' ) {
        $r[ 'total_paid' ][ ] = $objects[ $object[ 'ID' ] ][ 'subtotal' ] + $objects[ $object[ 'ID' ] ][ 'total_tax' ] - $objects[ $object[ 'ID' ] ][ 'total_discount' ];
      }

      if ( $object[ 'post_status' ] == 'active' ) {
        $r[ 'total_unpaid' ][ ] = $objects[ $object[ 'ID' ] ][ 'subtotal' ] + $objects[ $object[ 'ID' ] ][ 'total_tax' ] - $objects[ $object[ 'ID' ] ][ 'total_discount' ];
      }

    }

    if ( isset( $r[ 'total_paid' ] ) && is_array( $r[ 'total_paid' ] ) ) {
      $r[ 'total_paid' ] = array_sum( $r[ 'total_paid' ] );
    }

    if ( isset( $r[ 'total_unpaid' ] ) && is_array( $r[ 'total_unpaid' ] ) ) {
      $r[ 'total_unpaid' ] = array_sum( $r[ 'total_unpaid' ] );
    }

    ?>
      <div class="table table_invoices">

        <p class="sub"><?php _e( 'My Invoices', WPI ); ?></p>

        <table>
          <tbody>

            <tr class="first">
              <td class="b b-active">
                <a href="javascript:void(0);">
                  <span class="active-count"><?php echo $count = count( $invoices['active'] ); $total_invoices += $count; ?></span>
                </a>
              </td>
              <td class="last t active">
                <a href="javascript:void(0);" class="active toggler"><?php _e( 'Active', WPI ); ?></a>
              </td>
            </tr>

            <tr style="display:none;">
              <td colspan="2">
                <?php
                  if ( !empty( $invoices['active'] ) ) {
                    ?>
                    <ul>
                    <?php
                    foreach( $invoices['active'] as $invoice ) {
                      ?>
                        <li>
                          <a target="_blank" href="<?php echo get_invoice_permalink( $invoice->ID ); ?>">
                            <?php echo $invoice->post_title ?>
                          </a>
                        </li>
                      <?php
                    }
                    ?>
                    </ul>
                    <?php
                  } else {
                    _e( 'No invoices', WPI );
                  }
                ?>
              </td>
            </tr>

            <tr>
              <td class="b b-paid">
                <a href="javascript:void(0);">
                  <span class="paid-count"><?php echo $count = count( $invoices['paid'] ); $total_invoices += $count; ?></span>
                </a>
              </td>
              <td class="last t">
                <a href="javascript:void(0);" class="paid toggler"><?php _e( 'Paid', WPI ); ?></a>
              </td>
            </tr>

            <tr style="display:none;">
              <td colspan="2">
                <?php
                  if ( !empty( $invoices['paid'] ) ) {
                    ?>
                    <ul>
                    <?php
                    foreach( $invoices['paid'] as $invoice ) {
                      ?>
                        <li>
                          <a target="_blank" href="<?php echo get_invoice_permalink( $invoice->ID ); ?>">
                            <?php echo $invoice->post_title ?>
                          </a>
                        </li>
                      <?php
                    }
                    ?>
                    </ul>
                    <?php
                  } else {
                    _e( 'No invoices', WPI );
                  }
                ?>
              </td>
            </tr>

            <tr>
              <td class="b b-archived">
                <a href="javascript:void(0);">
                  <span class="archived-count"><?php echo $count = count( $invoices['archived'] ); $total_invoices += $count; ?></span>
                </a>
              </td>
              <td class="last t">
                <a href="javascript:void(0);" class="archived toggler"><?php _e( 'Archived', WPI ); ?></a>
              </td>
            </tr>

            <tr style="display:none;">
              <td colspan="2">
                <?php
                  if ( !empty( $invoices['archived'] ) ) {
                    ?>
                    <ul>
                    <?php
                    foreach( $invoices['archived'] as $invoice ) {
                      ?>
                        <li>
                            <?php echo $invoice->post_title ?>
                        </li>
                      <?php
                    }
                    ?>
                    </ul>
                    <?php
                  } else {
                    _e( 'No invoices', WPI );
                  }
                ?>
              </td>
            </tr>

            <tr>
              <td class="b b-trashed">
                <a href="javascript:void(0);">
                  <span class="trashed-count"><?php echo $count = count( $invoices['trashed'] ); $total_invoices += $count; ?></span>
                </a>
              </td>
              <td class="last t">
                <a href="javascript:void(0);" class="trashed toggler"><?php _e( 'Trashed', WPI ) ?></a>
              </td>
            </tr>

            <tr style="display:none;">
              <td colspan="2">
                <?php
                  if ( !empty( $invoices['trashed'] ) ) {
                    ?>
                    <ul>
                    <?php
                    foreach( $invoices['trashed'] as $invoice ) {
                      ?>
                        <li>
                            <?php echo $invoice->post_title ?>
                        </li>
                      <?php
                    }
                    ?>
                    </ul>
                    <?php
                  } else {
                    _e( 'No invoices', WPI );
                  }
                ?>
              </td>
            </tr>

          </tbody>
        </table>
      </div>


      <div class="table table_statistics">

        <p class="sub"><?php _e( 'My Statistics', WPI ); ?></p>

        <table>
          <tbody>
            <tr>
              <td class="t total-invoices">
                <?php _e('Total invoices', WPI); ?>
              </td>
              <td class="t total-number">
                <?php echo $total_invoices; ?>
              </td>
            </tr>

            <tr>
              <td class="t total-paid">
                <?php _e('Total paid', WPI); ?>
              </td>
              <td class="t total-paid total-number">
                <?php echo $wpi_settings[ 'currency' ][ 'symbol' ][ $wpi_settings[ 'currency' ][ 'default_currency_code' ] ].WPI_Functions::money_format( $r['total_paid'] ); ?>
              </td>
            </tr>

            <tr>
              <td class="t total-dept">
                <?php _e('Total dept', WPI); ?>
              </td>
              <td class="t total-dept total-number">
                <?php echo $wpi_settings[ 'currency' ][ 'symbol' ][ $wpi_settings[ 'currency' ][ 'default_currency_code' ] ].WPI_Functions::money_format( $r['total_unpaid'] ); ?>
              </td>
            </tr>

            <tr>
              <td class="t total-invoices">
                <?php _e('Total balance', WPI); ?>
              </td>
              <td class="t total-number">
                <?php echo $wpi_settings[ 'currency' ][ 'symbol' ][ $wpi_settings[ 'currency' ][ 'default_currency_code' ] ].WPI_Functions::money_format( $r['total_paid']+$r['total_unpaid'] ); ?>
              </td>
            </tr>
          </tbody>
        </table>

      </div>

      <div class="clear"></div>

    <?php

    die();
  }

  /**
   * Configuration callback
   * @global type $wpi_settings
   */
  function config() {
    global $wpi_settings;

    wp_enqueue_style('wpi_dw_user_invoices', WPI_URL.'/core/css/dashboard/widgets/wpi_dw_user_invoices.css' );

    if ( !empty($_POST['opts']) )
      self::update_dashboard_widget_options(
              $this->widget_id,
              $_POST['opts']
      );

    $widget_options = self::get_dashboard_widget_options( $this->widget_id );

    ?>
      <div class="table table_settings">

        <p class="sub"><?php _e( 'Display', WPI ); ?></p>

        <table>
        <?php foreach( (array)$wpi_settings['types'] as $type => $data ): ?>
          <tr>
            <td>
              <input <?php echo in_array($type, $widget_options['types2display'])?'checked="checked"':''; ?> type="checkbox" value="<?php echo $type; ?>" name="opts[types2display][]" />
              <?php echo $data['label']; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </table>

      </div>

    <?php
  }

}

//** Run premium feature */
add_action('wpi_premium_loaded', array('wpi_dashboard_widgets', 'wpi_premium_loaded'));