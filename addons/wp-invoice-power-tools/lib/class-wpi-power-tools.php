<?php
/**
 * Name: WP-Invoice Power Tools
 * Class: wpi_power_tools
 * Global Variable: wpi_power_tools
 * Feature ID: 17
 * Internal Slug: wpi_power_tools
 * JS Slug: wpi_power_tools
 * Version: 2.0.2
 * Minimum Core Version: 4.0.0
 * Description: Power Tools for WP-Invoice.
 */

//** Export formats */
define('WPI_EXPORT_JSON', 1);
define('WPI_EXPORT_XML',  2);

//** Line items types */
define('WPI_LINE_ITEM',   'line_item');
define('WPI_LINE_CHARGE', 'line_charge');

//** Invoice Data Types */
define('WPI_INVOICE_HEADER', 'header');
define('WPI_INVOICE_RECIPIENT', 'recipient');
define('WPI_INVOICE_LINEITEMS', 'line_items');
define('WPI_INVOICE_DATA', 'invoice_data');
define('WPI_INVOICE_LOG', 'log');

class wpi_power_tools {

  /**
   * Imported objects
   * @var type 
   */
  public static $imported_objects    = 0;
  
  /**
   * Imported attributes
   * @var type 
   */
  public static $imported_attributes = array();

  /**
   * Export type attribute
   *
   * @var string
   * @author korotkov@ud
   */
  public static $export_xml_type = 'WP-Invoice';

  /**
   * Array of keys that should be as attributes in in export object's node.
   *
   * @var array
   * @author korotkov@ud
   */
  public static $export_object_attributes = array(
    'post_date',
    'post_date_gmt',
    'post_content',
    'post_title',
    'post_excerpt',
    'post_status',
    'comment_status',
    'post_password',
    'post_modified',
    'post_modified_gmt',
    'post_type',
    'comment_count'
  );

  /**
   * Array of user data keys that should be as attributes in recipient's node.
   *
   * @var array
   * @author korotkov@ud
   */
  public static $export_recipient_attributes = array(
    'user_login',
    'user_pass',
    'user_nicename',
    'user_email',
    'user_url',
    'user_registered',
    'user_activation_key',
    'user_status',
    'display_name'
  );

  /**
   * Array of line item or line charge keys that should be as attributes in item's node.
   *
   * @var array
   * @author korotkov@ud
   */
  public static $export_item_attributes = array(
    'quantity',
    'price',
    'tax_rate',
    'line_total_tax',
    'line_total_before_tax',
    'line_total_after_tax',
    'amount',
    'tax',
    'tax_amount',
    'after_tax',
    'before_tax'
  );

  /**
   * Array of keys for log item attributes
   *
   * @var array
   * @author korotkov@ud
   */
  public static $export_log_item_attributes = array(
    'attribute',
    'action',
    'value',
    'time'
  );

  /**
   * Array of keys that should be in item log data as nodes.
   *
   * @var array
   * @author korotkov@ud
   */
  public static $export_log_item_data = array(
    'text'
  );

  /**
   * Array of keys that should be excluded from object meta
   *
   * @var array
   * @author korotkov@ud
   */
  public static $export_object_meta_exclude = array(
    'ID',
    'post_author',
    'post_date',
    'post_date_gmt',
    'post_content',
    'post_title',
    'post_excerpt',
    'post_status',
    'comment_status',
    'ping_status',
    'post_password',
    'post_name',
    'to_ping',
    'pinged',
    'post_modified',
    'post_modified_gmt',
    'post_content_filtered',
    'post_parent',
    'guid',
    'menu_order',
    'post_type',
    'post_mime_type',
    'comment_count',
    'itemized_list',
    'itemized_charges',
    'log',
    'user_data'
  );

  /**
   * Array of keys that should be serialized
   *
   * @var array
   */
  public static $json_data_keys = array(
    'billing',
    'recurring',
    'discount'
  );

  /**
   * Init feature filters and actions
   *
   * @global object $wpi_settings
   * @global object $wpi_power_tools
   * @author korotkov@ud
   */
  static function init() {

    /** Basic hooks */
    add_action( 'admin_enqueue_scripts',                   array( __CLASS__, 'admin_enqueue_scripts' ) );
    add_action( 'wpi_after_actions',                       array( __CLASS__, 'wpi_after_actions' ) );
    add_action( 'wpi_before_overview',                     array( __CLASS__, 'wpi_before_overview' ) );

    /** 'Visualize Sales' hooks */
    add_action( 'wp_ajax_wpi_visualize_sales_results',     array( __CLASS__, 'visualize_sales_results' ) );
    add_action( 'wp_ajax_wpi_visualize_new_sales_results', array( __CLASS__, 'visualize_new_sales_results' ) );

    /** Add settings tab for Export/Import */
    add_filter( 'wpi_settings_tabs',                       array( __CLASS__, 'wpi_settings_tabs_export_import' ) );
    add_action( 'wpi_after_actions',                       array( __CLASS__, 'wpi_filter_export_ui' ) );
    add_action( 'wp_ajax_wpi_export_invoice_filter',       array( __CLASS__, 'wpi_do_export_invoices' ) );
    add_action( 'admin_menu',                              array( __CLASS__, 'admin_menu' ) );
    add_action( 'admin_init',                              array( __CLASS__, 'admin_init' ) );
    add_action( 'wpi_settings_page_help',                  array( __CLASS__, 'ei_contextual_help' ) );
    add_action( 'wp_ajax_wpi_search_user',                 array( __CLASS__, 'search_user' ) );
  }

  /**
   * Generate data for visualisation
   *
   * @author korotkov@ud
   */
  static function visualize_sales_results() {

    /** Get invoices in order to a filter */
    parse_str( $_REQUEST['filters'], $wpi_filter_vars );
    $wpi_filter = $wpi_filter_vars['wpi_search'];
    $invoices = WPI_Functions::query( $wpi_filter );

    $data = array();

    /** Gather required data */
    foreach( $invoices as $invoice ) {

      /** Flush */
      $total_amount = 0;
      $date         = null;

      /** Load invoice by current invoice ID */
      $invoice_object =  new WPI_Invoice();
      $invoice_object -> load_invoice("id={$invoice->ID}");
      $invoice_log    =  (object)$invoice_object->data['log'];

      foreach( $invoice_log as $log_item ) {
        /** If current item is payment action with a balance */
        if ( $log_item['attribute'] == 'balance' && $log_item['action'] == 'add_payment' ) {
          $total_amount = $log_item['value'];
          /** Fix of months. 0 is Jan. */
          $date = date('Y', $log_item['time']).'-'.(date('m', $log_item['time'])-1).'-'.date('d', $log_item['time']);

          $data[] = array(
            'amount' => $total_amount,
            'date'   => $date
          );
        }

      }

    }

    /** If nothing to show */
    if(empty($data)) {
      die('<div class="wpi_visualize_results no_data">' . __('There is not enough quantifiable data to generate any graphs.', ud_get_wp_invoice_power_tools()->domain) . '</div>');
    }

    /** Flush */
    $prepared_data = array();
    $date          = $data[0]['date'];
    $amount        = 0;

    /** Count amount and sort by date */
    foreach( $data as $data_item ) {
      if ( $data_item['date'] != $date ) {
        $prepared_data[] = array(
          'total_amount' => $amount,
          'date' => $date
        );
        $amount = 0;
        $date   = $data_item['date'];
      }
      $amount += $data_item['amount'];
    }

    $prepared_data[] = array(
      'total_amount' => $amount,
      'date' => $date
    );

    /** Sort */
    usort( $prepared_data, 'self::sort_data_by_date' );

    /** Fix of months. 0 is Jan. */
    $zoomStartTime = date('Y', strtotime('-1 month')).'-'.(date('m', strtotime('-1 month'))-1).'-'.date('d', strtotime('-1 month'));

    ob_start();

?>
    <div class="wpi_visualize_results">
      <script type="text/javascript">
        jQuery(document).ready(function() {
           wpi_sales_chart();
        });
        function wpi_sales_chart() {
          var data = new google.visualization.DataTable({});
          data.addColumn('date', '<?php _e('Date', ud_get_wp_invoice_power_tools()->domain ); ?>');
          data.addColumn('number', '<?php _e('Daily Sales', ud_get_wp_invoice_power_tools()->domain); ?>');
          data.addRows(<?php echo count($prepared_data); ?>);
          <?php
          foreach($prepared_data as $row => $row_data) { ?>
          data.setValue(<?php echo $row; ?>, 0, new Date(<?php echo implode(',', explode('-', $row_data['date'])); ?>));
          data.setValue(<?php echo $row; ?>, 1, <?php echo $row_data['total_amount']; ?>);
          <?php } ?>
          var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('wpi_sales_chart'));
          chart.draw(data, {
            colors: ['red','blue'],
            fill:5,
            scaleType:'allmaximized',
            thickness:2,
            zoomStartTime:  new Date(<?php echo implode(',', explode('-', $zoomStartTime)); ?>)
          });
        }
      </script>
      <div class="wpi_chart_wrapper">
        <div id="wpi_sales_chart" class="wpi_sales_visualization_graph" style="width: 99%; height: 240px;"></div>

      </div>
    </div>
<?php

    $html = ob_get_contents();
    ob_clean();

    die( $html );

  }

  /**
   * New instance of visualize_sales_results function
   *
   * @global type $wpdb
   */
  static function visualize_new_sales_results() {

    /** Get invoices in order to a filter */
    parse_str( $_REQUEST['filters'], $filters );
    $interval_type = (!empty($_REQUEST['interval_type'])) ? $_REQUEST['interval_type'] : 'weekly';

    $intervals = WPI_Functions::get_sales_by($filters,$interval_type);

    foreach ($intervals as $interval){
      switch ($interval_type){
        case "daily":
          $time = strtotime("+{$interval->int_erval} day",mktime(0, 0, 0, 1, 1, $interval->int_year));
          break;
        case "weekly":
          $time = strtotime("monday +{$interval->int_erval} week",mktime(0, 0, 0, 1, 1, $interval->int_year));
          break;
        case "monthly":
        default:
          $time = strtotime("first day of +{$interval->int_erval} month",mktime(0, 0, 0, 1, 1, $interval->int_year));
          break;
      }
      $data[] = array(
        'amount' => $interval->sum_interval,
        'date'   => date('Y', $time).'-'.(date('m',$time)-1).'-'.date('d',$time)
      );
    }


    /** If nothing to show */
    if(empty($data)) {
      die('<div class="wpi_visualize_results no_data">' . __('There is not enough quantifiable data to generate any graphs.', ud_get_wp_invoice_power_tools()->domain) . '</div>');
    }

    /** Fix of months. 0 is Jan. */
    $zoomStartTime = date('Y', strtotime('-1 month',$time)).'-'.(date('m', strtotime('-1 month',$time))-1).'-'.date('d', strtotime('-1 month',$time));

    ob_start();

?>
    <div class="wpi_visualize_results">
      <script type="text/javascript">
        jQuery(document).ready(function() {
           wpi_sales_chart();
        });
        function wpi_sales_chart() {
          var data = new google.visualization.DataTable({});
          data.addColumn('date', '<?php _e('Date', ud_get_wp_invoice_power_tools()->domain); ?>');
          data.addColumn('number', '<?php _e(ucfirst($interval_type).' Sales', ud_get_wp_invoice_power_tools()->domain); ?>');
          data.addRows(<?php echo count($data); ?>);
          <?php
          foreach($data as $row => $row_data) { ?>
          data.setValue(<?php echo $row; ?>, 0, new Date(<?php echo implode(',', explode('-', $row_data['date'])); ?>));
          data.setValue(<?php echo $row; ?>, 1, <?php echo $row_data['amount']; ?>);
          <?php } ?>
          var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('wpi_sales_chart'));
          chart.draw(data, {
            colors: ['red','blue'],
            fill:5,
            scaleType:'allmaximized',
            thickness:2,
            zoomStartTime:  new Date(<?php echo implode(',', explode('-', $zoomStartTime)); ?>)
          });
        }
      </script>
      <div class="wpi_chart_wrapper">

        <div id="wpi_sales_chart_toolbar" style="text-align:right; height:32px; margin-right:1em;"><span class="loading"></span>&nbsp;<a class='btn button<?php echo (($interval_type=='monthly')?" active":"");?> wpi_visualize_sales_results' interval_type="mohthly"><?php echo __("Monthly", ud_get_wp_invoice_power_tools()->domain) ?></a>&nbsp;<a class='btn button<?php echo (($interval_type=='weekly')?" active":"");?> wpi_visualize_sales_results' interval_type="weekly"><?php echo __("Weekly", ud_get_wp_invoice_power_tools()->domain) ?></a>&nbsp;<a class='btn button<?php echo (($interval_type=='daily')?" active":"");?> wpi_visualize_sales_results' interval_type="daily"><?php echo __("Daily", ud_get_wp_invoice_power_tools()->domain) ?></a></div>

        <div id="wpi_sales_chart" class="wpi_sales_visualization_graph" style='width: 99%; height: 240px;'></div>

      </div>
    </div>
<?php

    $html = ob_get_contents();
    ob_clean();

    die( $html );

  }

  /**
   * Load required scripts on specific pages.
   *
   * @global object $current_screen
   * @author korotkov@ud
   */
  static function admin_enqueue_scripts() {
    global $current_screen;

    switch( $current_screen->id ) {
      default: break;

      case 'toplevel_page_wpi_main':
        /** Use this script only for Power Tools for now */
        wp_enqueue_script('google-jsapi', 'https://www.google.com/jsapi');
        break;

    }
  }

  /**
   * Add additional actions after invoice filter.
   *
   * @author korotkov@ud
   */
  static function wpi_after_actions() {

    ob_start();

?>
    <div class="wpi_other_action_list_wrapper">
      <h3><?php _e( 'Charts Tools', ud_get_wp_invoice_power_tools()->domain ); ?></h3>
      <ul class="wpi_other_action_list">
        <li class="button wpi_visualize_sales_results"><?php _e('Sales Chart', ud_get_wp_invoice_power_tools()->domain); ?></li>
      </ul>
    </div>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["annotatedtimeline"]});
      jQuery(".wpi_visualize_sales_results").live('click',function() {
        var filters = jQuery('[name="custom-page-form"]').serialize();
        var interval_type = (typeof jQuery(this).attr('interval_type')!='undefined')?jQuery(this).attr('interval_type'):'weekly';
        var img_loading = '<img src="<?php echo ud_get_wp_invoice()->path( 'static/styles/images/ajax-loader-arrows.gif', 'url' ); ?>" height="16" width="16" style="margin: 0pt auto;" alt="<?php _e('loading', ud_get_wp_invoice_power_tools()->domain) ?>"/>';

        if (jQuery(".wpi_pt_sales_visualization .loading").length!=0){
          jQuery(".wpi_pt_sales_visualization .loading").html(img_loading);
        }else
          jQuery(".wpi_pt_sales_visualization").html(img_loading);

        jQuery.ajax({
          url: ajaxurl,
          context: document.body,
          data: {
            action : 'wpi_visualize_new_sales_results',
            filters: filters,
            interval_type:interval_type
          },
          success: function(result){
            jQuery('.wpi_pt_sales_visualization').html(result);
            jQuery('.wpi_pt_sales_visualization').show("slide", { direction: "down" }, 1000);
          }
        });
      });
    </script>
<?php

    $html = ob_get_contents();
    ob_clean();

    echo $html;

  }

  /**
   * Add chart container
   *
   * @author korotkov@ud
   */
  static function wpi_before_overview() {

    ob_start();

?>
    <div class="wpi_pt_sales_visualization"></div>
<?php

    $html = ob_get_contents();
    ob_clean();

    echo $html;

  }

  /**
   * Sort data by date via usort()
   *
   * @param array $a
   * @param array $b
   * @return int
   */
  static function sort_data_by_date($a, $b) {
    return strcmp($a["date"], $b["date"]);
  }

  /**
   * Add settings tab for Export/Import
   *
   * @param array $tabs
   * @return array
   * @author korotkov@ud
   */
  static function wpi_settings_tabs_export_import( $tabs ) {
    $tabs['export_import'] = array(
      'label' => __('Import/Export', ud_get_wp_invoice_power_tools()->domain),
      'position' => 100,
      'callback' => array( __CLASS__, 'wpi_export_import_settings' )
    );
    return $tabs;
  }

  /**
   * Settings page for Export/Import
   *
   * @global object $wpi_settings
   */
  static function wpi_export_import_settings() {
    global $wpi_settings;
    ?>
    <h3 class="title"><?php _e( 'Export Options', ud_get_wp_invoice_power_tools()->domain ); ?></h3>
    <table class="form-table">
      <tr>
        <th><?php _e("Default Format", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <?php echo WPI_UI::select(array(
            'name'   => 'wpi_settings[power_tools][export_import][export_format]',
            'values' => apply_filters( 'wpi_pt_export_formats', array( WPI_EXPORT_JSON => __('JSON', ud_get_wp_invoice_power_tools()->domain), WPI_EXPORT_XML => __('XML', ud_get_wp_invoice_power_tools()->domain) ) ),
            'current_value' => !empty( $wpi_settings['power_tools']['export_import']['export_format'] ) ? $wpi_settings['power_tools']['export_import']['export_format'] : WPI_EXPORT_JSON
          )); ?>
        </td>
      </tr>
      <tr>
        <th><?php _e("XML Charset", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <ul class="wpi_settings_list wpi_something_advanced_wrapper">
            <li>
              <?php
                echo WPI_UI::checkbox(array(
                  'name'    => 'wpi_settings[power_tools][export_import][use_blog_charset]',
                  'value'   => 'true',
                  'class'   => 'wpi_show_advanced',
                  'special' => 'toggle_logic="reverse"',
                  'label'   => sprintf( __('Use default blog\'s charset. It is %s. <i>(Recommended)</i>', ud_get_wp_invoice_power_tools()->domain), get_bloginfo('charset'))
                ), !empty( $wpi_settings['power_tools']['export_import']['use_blog_charset'] ) ? $wpi_settings['power_tools']['export_import']['use_blog_charset'] : true );
              ?>
            </li>
            <li class="wpi_advanced_option">
              <?php
                _e('Custom Charset:', ud_get_wp_invoice_power_tools()->domain);
                echo WPI_UI::input(array(
                  'type'  => 'text',
                  'name'  => 'wpi_settings[power_tools][export_import][charset]',
                  'value' => !empty( $wpi_settings['power_tools']['export_import']['charset'] ) ? $wpi_settings['power_tools']['export_import']['charset'] : ''
                ));
              ?>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th><?php _e("XML Version", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <?php
            echo WPI_UI::input(array(
              'type'  => 'text',
              'name'  => 'wpi_settings[power_tools][export_import][xml_version]',
              'value' => !empty( $wpi_settings['power_tools']['export_import']['xml_version'] ) ? $wpi_settings['power_tools']['export_import']['xml_version'] : '1.0'
            ));
          ?>
        </td>
      </tr>
      <tr>
        <th><?php _e("Log History", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li>
              <?php
                echo WPI_UI::checkbox(array(
                  'name'    => 'wpi_settings[power_tools][export_import][export_log_history]',
                  'value'   => 'true',
                  'label'   => __( 'Export Invoice log history.' , ud_get_wp_invoice_power_tools()->domain )
                ), !empty( $wpi_settings['power_tools']['export_import']['export_log_history'] ) ? $wpi_settings['power_tools']['export_import']['export_log_history'] : true );
              ?>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th><?php _e("Filename Format", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <?php
            echo WPI_UI::input(array(
              'special' => 'size="60"',
              'type'  => 'text',
              'name'  => 'wpi_settings[power_tools][export_import][filename_format]',
              'value' => !empty( $wpi_settings['power_tools']['export_import']['filename_format'] ) ? $wpi_settings['power_tools']['export_import']['filename_format'] : '[blog_name]-export-[month]-[day]-[year]-[hour]-[minute]-[second]'
            ));
          ?>
          <div class="description">
            <?php _e('Allowed tags: <code>[blog_name]</code>, <code>[month]</code>, <code>[day]</code>, <code>[year]</code>, <code>[hour]</code>, <code>[minute]</code>, <code>[second]</code> and <code>[timestamp]</code>', ud_get_wp_invoice_power_tools()->domain); ?>
          </div>
        </td>
      </tr>
    </table>
    <h3 class="title"><?php _e( 'Import Options', ud_get_wp_invoice_power_tools()->domain ); ?></h3>
    <table class="form-table">
      <tr>
        <th><?php _e("Log History", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <ul class="wpi_settings_list">
            <li>
              <?php
                echo WPI_UI::checkbox(array(
                  'name'    => 'wpi_settings[power_tools][export_import][add_export_log_item]',
                  'value'   => 'true',
                  'label'   => __( "Add import log item." , ud_get_wp_invoice_power_tools()->domain )
                ), !empty( $wpi_settings['power_tools']['export_import']['add_export_log_item'] ) ? $wpi_settings['power_tools']['export_import']['add_export_log_item'] : true );
              ?>
            </li>
          </ul>
        </td>
      </tr>
      <tr>
        <th><?php _e("Recipients", ud_get_wp_invoice_power_tools()->domain); ?></th>
        <td>
          <ul class="wpi_settings_list wpi_something_advanced_wrapper">
            <li>
              <?php
                echo WPI_UI::checkbox(array(
                  'name'    => 'wpi_settings[power_tools][export_import][override_user_data]',
                  'value'   => 'true',
                  'label'   => __( "Override Recipient information if already exists." , ud_get_wp_invoice_power_tools()->domain )
                ), !empty( $wpi_settings['power_tools']['export_import']['override_user_data'] ) ? $wpi_settings['power_tools']['export_import']['override_user_data'] : false );
              ?>
            </li>
            <li>
              <?php _e( 'If Recipient does not exist:', ud_get_wp_invoice_power_tools()->domain ); ?>
              <?php echo WPI_UI::select(array(
                'special' => 'show_type_source="export_import_recipient_creating" show_type_element_attribute="user_selector"',
                'id' => 'export_import_recipient_creating',
                'class'   => 'wpi_show_advanced',
                'name'   => 'wpi_settings[power_tools][export_import][recipient_creating]',
                'values' => array( '0' => __('Create new Recipient', ud_get_wp_invoice_power_tools()->domain), '1' => __('Use existing one for all imported invoices', ud_get_wp_invoice_power_tools()->domain) ),
                'current_value' => !empty( $wpi_settings['power_tools']['export_import']['recipient_creating'] ) ? $wpi_settings['power_tools']['export_import']['recipient_creating'] : '0'
              )); ?>
            </li>
            <script type="text/javascript">
              jQuery(document).ready(function(){
                var wpi_constant_recipient_timer = 0;
                jQuery('#wpi_export_import_constant_recipient').live('keyup', function(){
                  var typing_timeout = 700;
                  var input  = jQuery(this);
                  var loader = jQuery('.wpi_constant_recipient_loader');
                  window.clearTimeout( wpi_constant_recipient_timer );
                  wpi_constant_recipient_timer = window.setTimeout( function(){
                    loader.show();
                    jQuery.post( ajaxurl, { action: 'wpi_search_user', email: input.val() },
                      function( response ) {
                        loader.hide();
                        if ( response && typeof response == 'object' ) {
                          input.removeClass('wpi_error');
                          jQuery('.wpi_export_import_constant_recipient_error').html('').hide();
                        } else {
                          input.addClass('wpi_error');
                          jQuery('.wpi_export_import_constant_recipient_error').html('<?php _e( sprintf( 'User not found. Admin E-mail will be used. (%s)', get_bloginfo('admin_email') ), ud_get_wp_invoice_power_tools()->domain ); ?>').show();
                        }
                      }, 'json'
                    );
                  }, typing_timeout );
                })
                .live('keydown', function(event){
                  if(event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                  }
                });
                jQuery('#wpi_export_import_constant_recipient').trigger('keyup');
              });
            </script>
            <style type="text/css">
              .wpi_export_import_constant_recipient_error {color:#B73737;display:none;}
              .wpi_constant_recipient_loader {display:none;}
            </style>
            <li class="wpi_advanced_option" user_selector="1">
              <?php _e( 'Specify Recipient (e-mail):', ud_get_wp_invoice_power_tools()->domain ); ?>
              <?php
                echo WPI_UI::input(array(
                  'id'    => 'wpi_export_import_constant_recipient',
                  'type'  => 'text',
                  'name'  => 'wpi_settings[power_tools][export_import][constant_recipient]',
                  'value' => !empty( $wpi_settings['power_tools']['export_import']['constant_recipient'] ) ? $wpi_settings['power_tools']['export_import']['constant_recipient'] : get_option('admin_email')
                ));
              ?>
              <span class="wpi_export_import_constant_recipient_error"></span>
              <img class="wpi_constant_recipient_loader" src="<?php echo ud_get_wp_invoice()->path( 'static/styles/images/ajax-loader-arrows.gif', 'url' ); ?>" />
            </li>
            <li class="wpi_advanced_option" user_selector="0">
              <?php _e( 'User Role:', ud_get_wp_invoice_power_tools()->domain ); ?>
              <?php
                echo WPI_UI::select(array(
                  'name'   => 'wpi_settings[power_tools][export_import][new_recipient_role]',
                  'values' => apply_filters('wpi_new_recipient_role', array(
                      'administrator' => __( 'Administrator', ud_get_wp_invoice_power_tools()->domain ),
                      'subscriber'    => __( 'Subscriber', ud_get_wp_invoice_power_tools()->domain ),
                      'editor'        => __( 'Editor', ud_get_wp_invoice_power_tools()->domain ),
                      'author'        => __( 'Author', ud_get_wp_invoice_power_tools()->domain ),
                      'contributor'   => __( 'Contributor', ud_get_wp_invoice_power_tools()->domain ),
                  )),
                  'current_value' => !empty( $wpi_settings['power_tools']['export_import']['new_recipient_role'] ) ? $wpi_settings['power_tools']['export_import']['new_recipient_role'] : 'subscriber'
                ));
              ?>
            </li>
          </ul>
        </td>
      </tr>
    </table>
    <?php
  }

  /**
   * Export UI (button)
   *
   * @author korotkov@ud
   */
  static function wpi_filter_export_ui() {

    $options = apply_filters( 'wpi_pt_export_formats', array( WPI_EXPORT_JSON => __('JSON', ud_get_wp_invoice_power_tools()->domain), WPI_EXPORT_XML => __('XML', ud_get_wp_invoice_power_tools()->domain) ) );

    ob_start();
    ?>
    <style type="text/css">
      ul.wpi_export_invoices_btn { float: left; }
    </style>
    <div class="wpi_other_action_list_wrapper">
      <h3><?php _e( 'Export Tools', ud_get_wp_invoice_power_tools()->domain ); ?></h3>
      <ul style="width:50%;" class="wpi_other_action_list wpi_export_invoices_btn">
        <li class="button wpi_export_invoices"><?php _e('Export', ud_get_wp_invoice_power_tools()->domain); ?></li>
      </ul>
      <select style="width:49%;margin:0;" id="wpi_export_format_select" name="format">
        <?php foreach( $options as $key => $option ): ?>
            <option value="<?php echo $key ?>"><?php echo $option ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <iframe id="wpi_export_downloader" src="" style="display:none; visibility:hidden;"></iframe>
    <script type="text/javascript">
      jQuery(".wpi_export_invoices").live('click', function(){
        var filters = jQuery('[name="custom-page-form"]').serialize();
        //** Allow users download file requested by AJAX request using iframe*/
        jQuery("#wpi_export_downloader").attr("src", ajaxurl+'?'+filters+'&action=wpi_export_invoice_filter');
      });
    </script>
    <div class="clear"></div>
    <?php
    $html = ob_get_clean();

    echo $html;
  }

  /**
   * @param bool $search_vars
   * @return mixed
   */
  public static function query_invoices( $search_vars = false ) {
    global $wpdb;

    $sort_by = " ORDER BY post_modified DESC ";
    //** Start our SQL */
    $sql = "SELECT * FROM {$wpdb->posts} WHERE post_type = 'wpi_object' ";

    if ( !empty( $search_vars ) ) {

      if ( is_string( $search_vars ) ) {
        $args = array();
        parse_str( $search_vars, $args );
        $search_vars = $args;
      }

      foreach ( $search_vars as $primary_key => $key_terms ) {

        //** Handle search_string differently, it applies to all meta values */
        if ( $primary_key == 'wplt_filter_s' && !empty($key_terms)) {
          //** First, go through the posts table */
          $tofind = strtolower( $key_terms );
          $sql .= " AND (";
          $sql .= " {$wpdb->posts}.ID IN (SELECT ID FROM {$wpdb->posts} WHERE LOWER(post_title) LIKE '%$tofind%')";
          //** Now go through the post meta table */
          $sql .= " OR {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE LOWER(meta_value) LIKE '%$tofind%')";
          $sql .= ")";
          continue;
        }

        //** Type */
        if ( $primary_key == 'wplt_filter_type' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          if ( is_array( $key_terms ) ) {
            $key_terms = implode( "','", $key_terms );
          }
          $sql .= " AND ";
          $sql .= " {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'type' AND meta_value IN ('{$key_terms}'))";
          continue;
        }

        //** Status */
        if ( $primary_key == 'wplt_filter_post_status' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          if ( !empty( $key_terms ) && $key_terms != 'any' ) {
            $sql .= " AND (";
            $sql .= " `{$wpdb->posts}`.post_status = '{$key_terms}' ";
            $sql .= ")";
          }
        }

        //** Recipient */
        if ( $primary_key == 'wplt_filter_user_email' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          $user = get_user_by( 'id', $key_terms );

          $sql .= " AND ";
          $sql .= " {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'user_email' AND meta_value = '{$user->user_email}')";

          continue;
        }

        /* Date */
        if ( $primary_key == 'wplt_filter_post_date_max' ) {
          if ( empty( $key_terms ) || (int) $key_terms == 0 ) {
            continue;
          }

          $key_terms = '' . preg_replace( '|[^0-9]|', '', $key_terms );
          $sql .= " AND YEAR(`{$wpdb->posts}`.post_date)<=" . substr( $key_terms, 0, 4 );
          if ( strlen( $key_terms ) > 5 ) {
            $sql .= " AND MONTH(`{$wpdb->posts}`.post_date)<=" . substr( $key_terms, 4, 2 );
          }
          if ( strlen( $key_terms ) > 7 ) {
            $sql .= " AND DAYOFMONTH(`{$wpdb->posts}`.post_date)<=" . substr( $key_terms, 6, 2 );
          }
          if ( strlen( $key_terms ) > 9 ) {
            $sql .= " AND HOUR(`{$wpdb->posts}`.post_date)<=" . substr( $key_terms, 8, 2 );
          }
          if ( strlen( $key_terms ) > 11 ) {
            $sql .= " AND MINUTE(`{$wpdb->posts}`.post_date)<=" . substr( $key_terms, 10, 2 );
          }
          if ( strlen( $key_terms ) > 13 ) {
            $sql .= " AND SECOND(`{$wpdb->posts}`.post_date)<=" . substr( $key_terms, 12, 2 );
          }
        }

        if ( $primary_key == 'wplt_filter_post_date_min' ) {
          if ( empty( $key_terms ) || (int) $key_terms == 0 ) {
            continue;
          }

          $key_terms = '' . preg_replace( '|[^0-9]|', '', $key_terms );
          $sql .= " AND YEAR(`{$wpdb->posts}`.post_date)>=" . substr( $key_terms, 0, 4 );
          if ( strlen( $key_terms ) > 5 ) {
            $sql .= " AND MONTH(`{$wpdb->posts}`.post_date)>=" . substr( $key_terms, 4, 2 );
          }
          if ( strlen( $key_terms ) > 7 ) {
            $sql .= " AND DAYOFMONTH(`{$wpdb->posts}`.post_date)>=" . substr( $key_terms, 6, 2 );
          }
          if ( strlen( $key_terms ) > 9 ) {
            $sql .= " AND HOUR(`{$wpdb->posts}`.post_date)>=" . substr( $key_terms, 8, 2 );
          }
          if ( strlen( $key_terms ) > 11 ) {
            $sql .= " AND MINUTE(`{$wpdb->posts}`.post_date)>=" . substr( $key_terms, 10, 2 );
          }
          if ( strlen( $key_terms ) > 13 ) {
            $sql .= " AND SECOND(`{$wpdb->posts}`.post_date)>=" . substr( $key_terms, 12, 2 );
          }
        }

        $sql = apply_filters( 'wpi_pt_query_invoices_sql', $sql, $primary_key, $key_terms );
      }
    }

    $sql = $sql . $sort_by;
    $results = $wpdb->get_results( $sql );

    return $results;
  }

  /**
   * Does the export
   *
   * @global object $wpi_settings
   * @author korotkov@ud
   */
  static function wpi_do_export_invoices() {
    global $wpi_settings;

    ob_start();

    //** Flush vars */
    $affected_ids = array();

    //** Query invoices' posts by filter params */
    //$invoice_posts = WPI_Functions::query( !empty($_REQUEST['wpi_search'])?$_REQUEST['wpi_search']:false );
    $invoice_posts = self::query_invoices( $_REQUEST );

    //** Export format */
    $format = !empty( $_GET['format'] ) ? $_GET['format'] : (!empty($wpi_settings['power_tools']['export_import']['export_format'])?$wpi_settings['power_tools']['export_import']['export_format']:1);

    //** Objects node for invoices */
    $objects = new wpi_export_object( null, null, 'objects' );

    //** Load each invoice and prepare for export */
    foreach( $invoice_posts as $invoice_post ) {
      $invoice_object =  new WPI_Invoice();
      $invoice_object->load_invoice("id={$invoice_post->ID}");

      $invoice      = new wpi_export_object();
      $header       = new wpi_export_object( null, null, 'header' );
      $recipient    = new wpi_export_object( null, null, 'recipient' );
      $line_items   = new wpi_export_object( array( 'type' => 'invoice_specific' ), null, 'line_items');
      $invoice_data = new wpi_export_object( null, null, 'invoice_data' );
      $log          = new wpi_export_object( null, null, 'log' );

      //** Fill object */
      foreach( $invoice_object->data as $data_item_key => $invoice_data_item ) {
        if ( in_array( $data_item_key, self::$export_object_attributes ) ) {
          $invoice->append_attribute( array( $data_item_key => $invoice_data_item ) );
        }
      }

      //** Fill header */
      $header->append_data( new wpi_export_object_meta( array(
        'type' => 'title'
      ), $invoice_object->data['post_title'] ) )
      ->append_data( new wpi_export_object_meta( array(
        'type' => 'description'
      ), $invoice_object->data['post_content'] ) );

      //** Fill recipient */
      foreach( $invoice_object->data['user_data'] as $user_data_item_key => $user_data_item ) {
        if ( in_array( $user_data_item_key, self::$export_recipient_attributes ) ) {
          $recipient->append_attribute( array( $user_data_item_key => $user_data_item ) );
        } else {
          $recipient->append_data( new wpi_export_object_meta( array(
            'type' => $user_data_item_key
          ), $user_data_item ) );
        }
      }

      //** Fill items */
      if ( !empty( $invoice_object->data['itemized_list'] ) ) {
        foreach( $invoice_object->data['itemized_list'] as $line_item ) {
          $item = new wpi_export_object_item( array( 'type' => 'line_item' ) );

          foreach( $line_item as $line_key => $line_value ) {
            if ( in_array( $line_key, self::$export_item_attributes ) ) {
              $item->append_attribute( array( $line_key => $line_value ) );
            } else {
              $item->append_data( new wpi_export_object_meta( array(
                'type' => $line_key
              ), $line_value ) );
            }
          }

          $line_items->append_data( $item );
        }
      }

      //** Fill charges */
      if ( !empty( $invoice_object->data['itemized_charges'] ) ) {
        foreach( $invoice_object->data['itemized_charges'] as $line_charge ) {
          $item = new wpi_export_object_item( array( 'type' => 'line_charge' ) );

          foreach( $line_charge as $line_key => $line_value ) {
            if ( in_array( $line_key, self::$export_item_attributes ) ) {
              $item->append_attribute( array( $line_key => $line_value ) );
            } else {
              $item->append_data( new wpi_export_object_meta( array(
                'type' => $line_key
              ), $line_value ) );
            }
          }

          $line_items->append_data( $item );
        }
      }

      //** Fill log if enabled */
      if ( empty($wpi_settings['power_tools']['export_import']['export_log_history']) || $wpi_settings['power_tools']['export_import']['export_log_history'] == 'true' ) {

        if ( !empty( $invoice_object->data['log'] ) ) {

          foreach( $invoice_object->data['log'] as $log_item ) {
            $item = new wpi_export_object_item();

            foreach( $log_item as $log_item_key => $log_item_value ) {
              if ( in_array( $log_item_key, self::$export_log_item_attributes ) ) {
                $item->append_attribute( array( $log_item_key => $log_item_value ) );
              }
              if ( in_array( $log_item_key, self::$export_log_item_data ) ) {
                $item->append_data( new wpi_export_object_meta( array(
                  'type' => $log_item_key
                ), $log_item_value ) );
              }
            }

            $log->append_data( $item );
          }
        }
      }

      //** Fill meta data */
      foreach( $invoice_object->data as $invoice_data_key => $invoice_data_value ) {
        if ( !in_array( $invoice_data_key, self::$export_object_meta_exclude ) ) {
          $attrs = array( 'type' => $invoice_data_key );

          $invoice_data_value = maybe_serialize($invoice_data_value);

          $data_item = new wpi_export_object_item( $attrs, $invoice_data_value );

          $invoice_data->append_data( $data_item );
        }
      }

      //** Put everything into invoice export object */
      $invoice->append_data( $header )
              ->append_data( $recipient )
              ->append_data( $line_items )
              ->append_data( $invoice_data );

      //** If log is not empty */
      if ( count( $log->data ) ) {
        $invoice->append_data( $log );
      }

      //** Add invoice to Objects */
      $objects->append_data( $invoice );

      //** get every post ID to ba able to do some extra actions later ith this IDs */
      $affected_ids[] = $invoice_post->ID;
    }

    //** Root node */
    $export = new wpi_export_object( array(
      'source'        => get_bloginfo('url'),
      'name'          => get_bloginfo('url'),
      'date'          => time(),
      'objects_count' => count($invoice_posts)
    ), null, 'export' );

    $export->append_data( $objects );

    //** Output export with format */
    switch( $format ) {
      case WPI_EXPORT_JSON:

        $export->append_attribute( array(
          'type' => self::$export_xml_type.' JSON'
        ) );
        //** JSON of export object */
        $output = json_encode( $export );
        //** File ext */
        $ext = 'json';

        break;

      case WPI_EXPORT_XML:

        $export->append_attribute( array(
          'type' => self::$export_xml_type.' XML'
        ) );
        //** XML of export object */
        $output = self::generate_export_xml( $export );
        //** File ext */
        $ext = 'xml';

        break;

      case apply_filters( 'wpi_pt_export_custom_format', false, $format ):

        $output = apply_filters( 'wpi_pt_export_custom_format_output', $format, $export );
        $ext = apply_filters( 'wpi_pt_export_custom_format_ext', $format );

        break;

        default: break;
    }

    $export_time = time();

    $export_filename_tags = array(
      'blog_name' => sanitize_title( get_bloginfo('name') ),
      'month'     => date('m', $export_time),
      'day'       => date('d', $export_time),
      'year'      => date('Y', $export_time),
      'hour'      => date('H', $export_time),
      'minute'    => date('i', $export_time),
      'second'    => date('s', $export_time),
      'timestamp' => $export_time
    );

    $filename_format = !empty( $wpi_settings['power_tools']['export_import']['filename_format'] )
      ? $wpi_settings['power_tools']['export_import']['filename_format'] : '[blog_name]-export-[month]-[day]-[year]-[hour]-[minute]-[second]';

    //** Replace filename tags to match format */
    foreach( $export_filename_tags as $tag => $value ) {
      $filename_format = str_replace('['.$tag.']', $value, $filename_format);
    }

    $filename = $filename_format.'.'.$ext;
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Transfer-Encoding: binary");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    ob_clean();

    //** If output is empty for some reason - print error */
    if ( empty( $output ) ) {
      die( __('Something went wrong with exporting process. Check if XML Encoding you selected is supported.', ud_get_wp_invoice_power_tools()->domain) );
    }

    //** Final output */
    die( $output );

  }

  /**
   *
   * @param type $export
   * @return type
   */
  static function generate_export_xml( $export ) {
    global $wpi_settings;

    $root = new wpi_export_object( null, null, 'root' );
    $root->append_data($export);

    $charset = ( !isset( $wpi_settings['power_tools']['export_import']['use_blog_charset'] ) || $wpi_settings['power_tools']['export_import']['use_blog_charset'] == 'true' ) ? get_bloginfo('charset') : ( !empty( $wpi_settings['power_tools']['export_import']['charset'] ) ? $wpi_settings['power_tools']['export_import']['charset'] : get_bloginfo('charset') );

    $dom = new WPI_Export_Xml(
      !empty($wpi_settings['power_tools']['export_import']['xml_version']) ? $wpi_settings['power_tools']['export_import']['xml_version'] : '1.0',
      $charset
    );

    $dom->generate_from_object( $root );

    $xml = $dom->saveXML();
    return $xml;
  }

  /**
   * Add menu items
   *
   * @author korotkov@ud
   */
  static function admin_menu() {
    global $wpi_settings;

    $capability = WPI_UI::get_capability_by_level( $wpi_settings['user_level'] );

    add_management_page( __( 'Import Invoices', ud_get_wp_invoice_power_tools()->domain ), __( 'Invoice Import', ud_get_wp_invoice_power_tools()->domain ), $capability, 'wpi-import-invoices', array( __CLASS__, 'import_invoices_page' ) );
    add_management_page( __( 'Export Invoices', ud_get_wp_invoice_power_tools()->domain ), __( 'Invoice Export', ud_get_wp_invoice_power_tools()->domain ), $capability, 'wpi-export-invoices', array( __CLASS__, 'export_invoices_page' ) );
  }

  /**
   * Admin init
   *
   * @author korotkov@ud
   */
  static function admin_init() {
    if ( !empty( $_POST ) && !empty( $_FILES['import_objects_dump'] ) ){
      if($_FILES['import_objects_dump']['error'] === UPLOAD_ERR_INI_SIZE){
        global $pagenow;

        if ( $pagenow == 'tools.php' )
          add_action('admin_notices', array(__CLASS__, 'size_limit_notice'));
      }else{
        if( isset( $_FILES['import_objects_dump']['tmp_name'] ) && $export_file = $_FILES['import_objects_dump']['tmp_name'] ) {
          $export_contents = file_get_contents($export_file);

          //** Prevent errors and warnings appearing */
          //ob_start();

          //** Try to decode dump from uploaded file */
          if ( $decoded = self::decode_dump( $export_contents ) ) {
            if ( self::wpi_do_import_invoices( $decoded ) ) {
              global $pagenow;
              if ( $pagenow == 'tools.php' )
                add_action('admin_notices', array(__CLASS__, 'import_success_notice'));
            }
          } else {
            global $pagenow;
            //** Show error admin notice of fault */
            if ( $pagenow == 'tools.php' )
              add_action('admin_notices', array(__CLASS__, 'import_error_notice'));
          }

          //** Prevent errors and warnings appearing */
          //ob_end_clean();

        } else {
          global $pagenow;
          //** Show error admin notice if user did not specified the file at all */
          if ( $pagenow == 'tools.php' )
            add_action('admin_notices', array(__CLASS__, 'no_file_notice'));
        }
      }
    }
  }

  /**
   * Show error massage if encountered
   *
   * @author korotkov@ud
   */
  static function import_error_notice() {
    ob_start();

    ?>
      <div class="error">
        <p>
          <?php _e( 'It is unable to import data from the file that you specified. It may be broken or empty. Make sure that you select proper <code>WP-Invoice XML</code> or <code>WP-Invoice JSON</code> file which has at least 1 object.', ud_get_wp_invoice_power_tools()->domain ); ?>
        </p>
      </div>
    <?php

    echo apply_filters('wpi_import_error_notice', ob_get_clean());
  }

  /**
   * Show success message
   *
   * @author korotkov@ud
   */
  static function import_success_notice() {
    ob_start();

    ?>
      <div class="updated">
        <p>
          <?php _e( sprintf( 'Imported %s of %s objects using export "%s". Type - %s, date - %s', self::$imported_objects, self::$imported_attributes['objects_count'], self::$imported_attributes['name'], self::$imported_attributes['type'], date( 'm.d.Y H:i:s', self::$imported_attributes['date'] ) ), ud_get_wp_invoice_power_tools()->domain ); ?>
        </p>
      </div>
    <?php

    echo apply_filters('wpi_import_success_notice', ob_get_clean());
  }

  /**
   * Show error message if encountered
   *
   * @author korotkov@ud
   */
  static function no_file_notice() {
    ob_start();

    ?>
      <div class="error">
        <p>
          <?php _e( 'You forgot to specify dump file. Make sure that you select proper <code>WP-Invoice XML</code> or <code>WP-Invoice JSON</code> file.', ud_get_wp_invoice_power_tools()->domain ); ?>
        </p>
      </div>
    <?php

    echo apply_filters('wpi_no_file_notice', ob_get_clean());
  }

  /**
   * Show error message if filesize more than upload_max_filesize
   *
   * @author fq.jony@ud
   */
  static function size_limit_notice() {
    ob_start();

    ?>
    <div class="error">
      <p>
        <?php _e( 'File size bigger than <code>upload_max_filesize</code> server setup.<code>upload_max_filesize='.ini_get('upload_max_filesize').'</code>', ud_get_wp_invoice_power_tools()->domain ); ?>
      </p>
    </div>
    <?php

    echo apply_filters('wpi_size_limit_notice', ob_get_clean());
  }

  /**
   * Import Invoices page content
   *
   * @author korotkov@ud
   */
  static function import_invoices_page() {

    ob_start();
    ?>
    <div class="wrap">
      <div class="icon32" id="icon-tools"><br></div>
      <h2><?php _e( 'Import Invoices', ud_get_wp_invoice_power_tools()->domain ); ?></h2>
      <p><?php _e( '<b>WP-Invoice Power Tools</b> feature allows you to Import data from another WP-Invoice powered site.', ud_get_wp_invoice_power_tools()->domain ); ?></p>
      <p><?php _e( 'If you already have <code>WP-Invoice XML</code> or <code>WP-Invoice JSON</code> export file from another site then you can specify it in the form below. Once ready click \'<b>Import</b>\' button.', ud_get_wp_invoice_power_tools()->domain ); ?></p>
      <p><?php _e( sprintf('You can find additional options for import process on <a href="%s"><b>Invoice -> Settings</b></a> page under <b>Export/Import</b> tab.', get_admin_url(null, 'admin.php?page=wpi_page_settings') ), ud_get_wp_invoice_power_tools()->domain ); ?></p>
      <form method="POST" enctype="multipart/form-data" action="">
        <?php _e( 'Specify dump file:', ud_get_wp_invoice_power_tools()->domain ); ?>
        <input type="file" name="import_objects_dump" size="20" />
        <?php submit_button( __('Import', ud_get_wp_invoice_power_tools()->domain), 'primary'); ?>
      </form>
    </div>
    <?php

    echo apply_filters('wpi_import_invoices_page', ob_get_clean());
  }

  /**
   * Export Invoices Page content
   *
   * @author korotkov@ud
   */
  static function export_invoices_page() {

    ob_start();
    ?>
    <div class="wrap">
      <div class="icon32" id="icon-tools"><br></div>
      <h2><?php _e( 'Export Invoices', ud_get_wp_invoice_power_tools()->domain ); ?></h2>
      <p><?php _e( '<b>WP-Invoice Power Tools</b> feature allows you to export your existing <i>WP-Invoice Objects</i><sup>1</sup> and import them into another WP-Invoice powered site.', ud_get_wp_invoice_power_tools()->domain ); ?></p>
      <p><?php _e( 'Export dump can be downloaded in two different formats: <code>JSON</code> and <code>XML</code><sup>2</sup>.', ud_get_wp_invoice_power_tools()->domain ); ?></p>
      <p><?php _e( 'Click button below if you need to export <b>ALL</b> WP-Invoice Objects.', ud_get_wp_invoice_power_tools()->domain ); ?></p>
      <p><?php _e( sprintf( 'Also, you can specify particular objects that you need to export by using Filter and clicking <b>\'Download Export File\'</b> button below on the <a href="%s">Invoice -> View All</a> page.', get_admin_url(null, 'admin.php?page=wpi_main')), ud_get_wp_invoice_power_tools()->domain); ?></p>
      <p>
        <input type="button" class="button-secondary wpi_export_invoices" value="<?php _e('Download Export File', ud_get_wp_invoice_power_tools()->domain); ?>" />
      </p>
      <p>
        <small>
          _________________________________________
          <ol>
            <li><?php _e('Under WP-Invoice Objects we mean Invoices, Quotes, Receipts, <abbr title="Single Page Checkout">SPC</abbr> transactions and any other types of invoices.', ud_get_wp_invoice_power_tools()->domain); ?></li>
            <li><?php _e( sprintf('These and other settings you can find on <a href="%s"><b>Invoice -> Settings</b></a> page under <b>Export/Import</b> tab.', get_admin_url(null, 'admin.php?page=wpi_page_settings') ), ud_get_wp_invoice_power_tools()->domain); ?></li>
          </ol>
        </small>
      </p>
    </div>
    <iframe id="wpi_export_downloader" src="" style="display:none; visibility:hidden;"></iframe>
    <script type="text/javascript">
      jQuery(".wpi_export_invoices").live('click', function(){
        jQuery("#wpi_export_downloader").attr("src", ajaxurl+'?action=wpi_export_invoice_filter');
      });
    </script>
    <?php

    echo apply_filters('wpi_export_invoices_page', ob_get_clean());
  }

  /**
   * Dump decoder
   *
   * @param mixed $content
   * @author korotkov@ud
   */
  static function decode_dump( $content ) {
    if ( $data = self::json_decode( $content ) ) {
      return $data;
    } elseif ( $data = self::xml_decode( $content ) ) {
      return $data;
    }
  }

  /**
   * Custom json decoder. Because we need a wrapper for native json_decode in our case.
   *
   * @param mixed $content
   * @author korotkov@ud
   */
  static function json_decode( $content ) {

    //** Usual decode of object */
    $data = json_decode( $content );

    //** If not decodable - return false and try another method */
    if ( is_null( $data ) ) {
      return false;
    }

    //** Objects accumulator */
    $objects = array();

    //** Get import attributes */
    foreach( $data->attributes as $attr_key => $attr_value )
      $import_attrs[$attr_key] = $attr_value;

    //** If export is not empty */
    if ( !empty( $data->data[0]->data ) && is_array( $data->data[0]->data ) ) {

      foreach( $data->data[0]->data as $key => $post ) {

        $object = new wpi_import_object();

        //** Fill post object */
        foreach( $post->attributes as $attr => $attr_value ) {
          if ( in_array( $attr, self::$export_object_attributes ) )
            $object->append_post( array( $attr => $attr_value ) );
        }

        //** Fill different post data */
        foreach( $post->data as $post_meta ) {

          switch( $post_meta->node ) {

            //** If iteration is on Invcoie Meta */
            case WPI_INVOICE_DATA:

              if ( !empty( $post_meta->data ) )
                foreach( $post_meta->data as $meta_item ) {
                  $object->append_meta( array( $meta_item->attributes->type => $meta_item->data ) );
                }

              break;

            //** If iteration is on User Data */
            case WPI_INVOICE_RECIPIENT:

              $u_object = array();
              $u_meta   = array();

              if ( !empty( $post_meta->attributes ) )
                foreach( $post_meta->attributes as $attr_key => $attr_value ) {
                  $u_object[$attr_key] = $attr_value;
                }

              $object->append_user_data( array( 'object' => $u_object ) );

              if ( !empty( $post_meta->data ) )
                foreach( $post_meta->data as $user_meta ) {
                  $u_meta[$user_meta->attributes->type] = $user_meta->data;
                }

              $object->append_user_data( array( 'meta' => $u_meta ) );

              break;

            //** If iteration is on Invoice Log */
            case WPI_INVOICE_LOG:

              if ( !empty( $post_meta->data ) ) {

                foreach( $post_meta->data as $log_item ) {
                  $log = array();

                  foreach( $log_item->attributes as $log_key => $log_value ) {
                    $log[$log_key] = $log_value;
                  }

                  $log[$log_item->data[0]->attributes->type] = $log_item->data[0]->data;

                  $object->append_log( $log );

                }

              }

              break;

            //** If iteation is on Line Items */
            case WPI_INVOICE_LINEITEMS:

              //** Flush vars */
              $items_accumulator = array();
              $charges_accumulator = array();

              foreach( $post_meta->data as $item ) {

                $line_item   = array();
                $line_charge = array();

                foreach( $item->attributes as $item_key => $item_value ) {

                  if ( $item_key == 'type' ) {
                    $type = $item_value;
                  }

                  switch( $type ) {
                    case WPI_LINE_ITEM:

                      if ( $item_key != 'type' ) {
                        $line_item[$item_key] = $item_value;
                      }

                      break;
                    case WPI_LINE_CHARGE:

                      if ( $item_key != 'type' ) {
                        $line_charge[$item_key] = $item_value;
                      }

                      break;

                    default: break;
                  }

                }

                foreach( $item->data as $item_meta ) {
                  switch( $type ) {
                    case WPI_LINE_ITEM:

                      $line_item[$item_meta->attributes->type] = !empty( $item_meta->data ) ? $item_meta->data : '';

                      break;
                    case WPI_LINE_CHARGE:

                      $line_charge[$item_meta->attributes->type] = !empty( $item_meta->data ) ? $item_meta->data : '';

                      break;

                    default: break;
                  }
                }

                if ( !empty( $line_item ) )
                  $items_accumulator[] = $line_item;

                if ( !empty( $line_charge ) ) {
                  $charges_accumulator[] = $line_charge;
                }

              }

              if ( !empty( $charges_accumulator ) )
                $object->append_meta( array( 'itemized_charges' => serialize( $charges_accumulator ) ) );

              if ( !empty( $items_accumulator ) )
                $object->append_meta( array( 'itemized_list' => serialize( $items_accumulator ) ) );

              break;

            default: break;
          }

        }

        //** Put object into objects array that will be returned after all */
        $objects[] = $object;

      }

    }

    //** Return objects if they exist and false if not */
    return !empty( $objects ) && !empty( $import_attrs ) ? array('objects' => $objects, 'attributes' => $import_attrs ) : false;
  }

  /**
   * Decode our XML
   *
   * @param type $content
   * @author korotkov@ud
   */
  static function xml_decode( $content ) {

    //** Flush vars */
    $ready_objects = array();

    //** Create XML doc based on our loaded file */
    $xml = new DOMDocument;
    @$success = $xml->loadXML( $content );

    if ( !$success ) return $success;

    //** Get every object node */
    $objects = $xml->getElementsByTagName( 'object' );

    //** Get export attributes */
    $export_node = $xml->getElementsByTagName( 'export' );

    $import_attrs['type']          = $export_node->item(0)->hasAttribute('type')
                                   ? $export_node->item(0)->getAttribute('type')
                                   : null;
    $import_attrs['objects_count'] = $export_node->item(0)->hasAttribute('objects_count')
                                   ? $export_node->item(0)->getAttribute('objects_count')
                                   : null;
    $import_attrs['date']          = $export_node->item(0)->hasAttribute('date')
                                   ? $export_node->item(0)->getAttribute('date')
                                   : null;
    $import_attrs['name']          = $export_node->item(0)->hasAttribute('name')
                                   ? $export_node->item(0)->getAttribute('name')
                                   : null;
    $import_attrs['source']        = $export_node->item(0)->hasAttribute('source')
                                   ? $export_node->item(0)->getAttribute('source')
                                   : null;

    //** Loop objects */
    foreach( $objects as $object ) {

      //** New object of import */
      $wpi_object = new wpi_import_object();

      //** Find post data */
      foreach( self::$export_object_attributes as $o_attr ) {
        if ( $object->hasAttribute( $o_attr ) ) {
          $wpi_object->append_post( array( $o_attr => $object->getAttribute( $o_attr ) ) );
        }
      }

      //** Find post meta */
      $meta_data = $object->getElementsByTagName( 'invoice_data' );
      $metas = $meta_data->item(0)->getElementsByTagName( 'item' );
      foreach( $metas as $meta ) {
        $key = $meta->getAttribute( 'type' );
        $value = $meta->nodeValue;
        $wpi_object->append_meta( array( $key => $value ) );
      }

      //** Find line items and charges */
      $line_items = $object->getElementsByTagName( 'line_items' );
      if ( $line_items->length ) {
        $items = $line_items->item(0)->getElementsByTagName( 'item' );

        //** Flush vars */
        $items_accumulator   = array();
        $charges_accumulator = array();

        foreach( $items as $item ) {
          $type = $item->getAttribute( 'type' );

          switch( $type ) {
            case WPI_LINE_ITEM:

              //** If processing line item */
              $line_item = array(
                'line_total_after_tax'  => $item->getAttribute( 'line_total_after_tax' ),
                'line_total_before_tax' => $item->getAttribute( 'line_total_before_tax' ),
                'line_total_tax'        => $item->getAttribute( 'line_total_tax' ),
                'tax_rate'              => $item->getAttribute( 'tax_rate' ),
                'price'                 => $item->getAttribute( 'price' ),
                'quantity'              => $item->getAttribute( 'quantity' )
              );

              $items_meta = $item->getElementsByTagName( 'meta' );
              foreach( $items_meta as $item_meta ) {
                $line_item[$item_meta->getAttribute('type')] = $item_meta->nodeValue;
              }

              $items_accumulator[] = $line_item;

              break;

            case WPI_LINE_CHARGE:

              //** If processing line charge */
              $line_item = array(
                'before_tax' => $item->getAttribute( 'before_tax' ),
                'after_tax'  => $item->getAttribute( 'after_tax' ),
                'tax_amount' => $item->getAttribute( 'tax_amount' ),
                'tax'        => $item->getAttribute( 'tax' ),
                'amount'     => $item->getAttribute( 'amount' )
              );

              $items_meta = $item->getElementsByTagName( 'meta' );
              foreach( $items_meta as $item_meta ) {
                $line_item[$item_meta->getAttribute('type')] = $item_meta->nodeValue;
              }

              $charges_accumulator[] = $line_item;

              break;
          }

        }

        //** If we have line items - add them as serialized meta */
        if ( !empty( $items_accumulator ) ) {
          $wpi_object->append_meta( array( 'itemized_list' => serialize( $items_accumulator ) ) );
        }

        //** If we have line charges - add them as serialized meta */
        if ( !empty( $charges_accumulator ) ) {
          $wpi_object->append_meta( array( 'itemized_charges' => serialize( $charges_accumulator ) ) );
        }

      }

      //** Find log items */
      if ($log = $object->getElementsByTagName('log')) {
        if ($log->item(0)) {

          $log_items = $log->item(0)->getElementsByTagName('item');

          foreach ($log_items as $log_item) {
            $log_item_meta = $log_item->getElementsByTagName('meta');
            $text = '';
            foreach ($log_item_meta as $meta_item) {
              if ($meta_item->getAttribute('type') == 'text') {
                $text = $meta_item->nodeValue;
                break;
              }
            }

            $wpi_object->append_log(array(
              'time' => $log_item->getAttribute('time'),
              'value' => $log_item->getAttribute('value'),
              'action' => $log_item->getAttribute('action'),
              'attribute' => $log_item->getAttribute('attribute'),
              'text' => $text
            ));
          }
        }
      }

      //** Find user data */
      $user = $object->getElementsByTagName( 'recipient' );
      $user_metas = $user->item(0)->getElementsByTagName( 'meta' );

      //** Flush vars */
      $u_object = array();
      $u_meta   = array();

      foreach( self::$export_recipient_attributes as $r_attr ) {
        if ( $user->item(0)->hasAttribute( $r_attr ) ) {
          $u_object[$r_attr] = $user->item(0)->getAttribute( $r_attr );
        }
      }

      foreach( $user_metas as $user_meta ) {
        $type = $user_meta->getAttribute( 'type' );
        $u_meta[$type] = $user_meta->nodeValue;
      }

      $wpi_object->append_user_data( array( 'object' => $u_object ) )->append_user_data( array( 'meta' => $u_meta ) );

      //** Collect ready object */
      $ready_objects[] = $wpi_object;

    }

    //** Return an array of ready objects and attrs */
    return (!empty( $ready_objects ) && !empty( $import_attrs )) ? array('objects' => $ready_objects, 'attributes' => $import_attrs ) : false;
  }

  /**
   * Do import process
   *
   * @todo FINISH IMPORT PROCESS
   * @param array $decoded
   * @return bool
   */
  static function wpi_do_import_invoices( $decoded = null ) {
    //** Do nothing if empty args */
    if ( !$decoded ) return false;

    global $wpdb, $current_user, $blog_id, $wpi_settings;

    self::$imported_attributes = $decoded['attributes'];

    //** Loop objects */
    if ( !empty( $decoded['objects'] ) && is_array( $decoded['objects'] ) ) {
      foreach( $decoded['objects'] as $wpi_object ) {

        //** If user is presented */
        if ( !empty( $wpi_object->user['object']['user_email'] ) ) {

          //** If already have this user */
          if ( $recipient = get_user_by( 'email', $wpi_object->user['object']['user_email'] ) ) {

            //** Override user information if enabled */
            if ( !empty($wpi_settings['power_tools']['export_import']['override_user_data']) && $wpi_settings['power_tools']['export_import']['override_user_data'] == 'true' ) {
              foreach( $wpi_object->user['meta'] as $meta_key => $meta_value ) {
                update_user_meta( $recipient->ID, $meta_key, $meta_value );
              }
            }
          }
          //** If we have no user */
          else {

            //** Get user handling method from settings */
            $recipient_handling_type = !empty( $wpi_settings['power_tools']['export_import']['recipient_creating'] ) ? $wpi_settings['power_tools']['export_import']['recipient_creating'] : '0';

            //** If we decided to create new user */
            if ( $recipient_handling_type == '0' ) {

              //** Get default role from settings for new user */
              $user_role = !empty( $wpi_settings['power_tools']['export_import']['new_recipient_role'] ) ? $wpi_settings['power_tools']['export_import']['new_recipient_role'] : 'subscriber';

              //** Check if login already exist to avoid collisions */
              if ( username_exists( $wpi_object->user['object']['user_login'] ) ) {

                //** Just add _imported suffix to new login */
                $wpi_object->user['object']['user_login'] .= '_imported';
              }

              //** Try to insert user */
              $user_id = wp_insert_user( array(
                  'user_email' => $wpi_object->user['object']['user_email'],
                  'user_login' => $wpi_object->user['object']['user_login'],
                  'user_pass' => $wpi_object->user['object']['user_pass'],
                  'role' => $user_role
                ) );

              //** If user has been inserted */
              if ( is_numeric($user_id) ) {

                //** Update his meta data */
                foreach( $wpi_object->user['meta'] as $meta_key => $meta_value ) {
                  update_user_meta( $user_id, $meta_key, $meta_value );
                }
              }
            }
            //** If we decided to assign objects to some existing user */
            elseif ( $recipient_handling_type == '1' ) {

              //** Just change email in post meta */
              $wpi_object->meta['user_email'] = !empty( $wpi_settings['power_tools']['export_import']['constant_recipient'] ) ? $wpi_settings['power_tools']['export_import']['constant_recipient'] : get_option('admin_email');

              if ( !get_user_by('email', $wpi_object->meta['user_email']) ) {
                $wpi_object->meta['user_email'] = get_option('admin_email');
              }
            }
          }
        }

        //** Insert new post */
        if ( $post_id = wp_insert_post( $wpi_object->post ) ) {

          //** Flush vars */
          $invoice_id = false;
          $custom_id  = false;

          //** Loop meta */
          foreach( $wpi_object->meta as $meta_key => $meta_value ) {

            //** Find invoice id */
            if ( $meta_key == 'invoice_id' ) {
              $invoice_id = $meta_value;
            }

            //** Find custom invoice id */
            if ( $meta_key == 'custom_id' ) {
              $custom_id  = $meta_value;
            }
            add_post_meta($post_id, $meta_key, maybe_unserialize($meta_value));
          }

          //** Handle custom ID */
          if ( $custom_id ) {

            //** If this ID is already in use */
            if ( $wpdb->query("SELECT `meta_id`
                               FROM `{$wpdb->postmeta}`
                               WHERE (`meta_key` = 'custom_id' || `meta_key` = 'invoice_id')
                                 AND `meta_value` = '{$custom_id}'") > 1 ) {
              //** Remove custom id because it doen't make any sence */
              delete_post_meta( $post_id, 'custom_id' );
            }

          }

          //** Handle Invoice ID */
          if ( $invoice_id ) {

            //** If this ID is already in use */
            if ( $wpdb->query("SELECT `meta_id`
                               FROM `{$wpdb->postmeta}`
                               WHERE (`meta_key` = 'custom_id' || `meta_key` = 'invoice_id')
                                 AND `meta_value` = '{$invoice_id}'") > 1 ) {
              //** Remove custom id because it doen't make any sence */
              $new_invoice_id = rand( 100, 99999999 );
              update_post_meta( $post_id, 'invoice_id', $new_invoice_id );
              update_post_meta( $post_id, 'hash', md5( $new_invoice_id.time() ) );
            }
          }

          //** Loop log */
          foreach( $wpi_object->log as $log_item ) {
            $wpdb->insert($wpdb->base_prefix . 'wpi_object_log', array(
              'object_id' => $post_id,
              'user_id' => $current_user->ID,
              'attribute' => $log_item['attribute'],
              'action' => $log_item['action'],
              'value' => '',
              'text' => $log_item['text'],
              'time' => $log_item['time'],
              'blog_id' => $blog_id
            ));
          }

          //** Add 'Imported' log item if enabled */
          if ( !empty($wpi_settings['power_tools']['export_import']['add_export_log_item']) && $wpi_settings['power_tools']['export_import']['add_export_log_item'] == 'true' ) {
            $wpdb->insert($wpdb->base_prefix . 'wpi_object_log', array(
              'object_id' => $post_id,
              'user_id' => $current_user->ID,
              'attribute' => 'invoice',
              'action' => 'update',
              'value' => '',
              'text' => __( sprintf( 'Imported from "%1s" (%2s) of %3s' , $decoded['attributes']['name'], $decoded['attributes']['source'], date( 'm.d.Y H:i:s', $decoded['attributes']['date'] )), ud_get_wp_invoice_power_tools()->domain ),
              'time' => time(),
              'blog_id' => $blog_id
            ));
          }
          self::$imported_objects++;
        }
      }
    } else {
      return false;
    }

    return true;
  }

  /**
   * Premium feature help
   *
   * @param array $data
   * @return array
   * @author korotkov@ud
   */
  static function ei_contextual_help( $data ) {

    $data['Export/Import'][] = '<h3>'.__( 'Export/Import Invoices', ud_get_wp_invoice_power_tools()->domain ).'</h3>';
    $data['Export/Import'][] = '<p>'.__( '<b>Export Options</b>', ud_get_wp_invoice_power_tools()->domain ).'</p>';
    $data['Export/Import'][] = '<p>'.__( '<u>Default Format</u> - format that will be used for generating export file dump. You can specify format using dropdown next to "Export" button.', ud_get_wp_invoice_power_tools()->domain ).'</p>';
    $data['Export/Import'][] = '<p>'.__( '<u>XML Charset</u> - the charset of the dump file content. It is recommended to use default but you can change it to some supported charset. Note that unsupported charsets can make export failed.', ud_get_wp_invoice_power_tools()->domain ).'</p>';
    $data['Export/Import'][] = '<p>'.__( '<u>XML Version</u> - the value which is used for XML formatted dump files. Usually is "1.0"', ud_get_wp_invoice_power_tools()->domain ).'</p>';
    $data['Export/Import'][] = '<p>'.__( '<u>Log History</u> - options for log history exports. See descriptions below:', ud_get_wp_invoice_power_tools()->domain ).'</p>';

    $data['Export/Import'][] = '<ul>';
    $data['Export/Import'][] = '<li><i>'.__( 'Export Invoice log history.', ud_get_wp_invoice_power_tools()->domain ).'</i> - '.__( 'whether or not to export history logs for every invoice object.', ud_get_wp_invoice_power_tools()->domain ).'</li>';
    $data['Export/Import'][] = '</ul>';

    $data['Export/Import'][] = '<p>'.__( '<u>Filename Format</u> - specify pattern for dump filename to use. Use allowed tags below.', ud_get_wp_invoice_power_tools()->domain ).'</p>';
    $data['Export/Import'][] = '<p>'.__( '<u>Advanced Options</u> - some advanced useful options. See descriptions below:', ud_get_wp_invoice_power_tools()->domain ).'</p>';

    $data['Export/Import'][] = '<ul>';
    $data['Export/Import'][] = '<li><i>'.__( 'Delete exported Objects (Invoices) after export.', ud_get_wp_invoice_power_tools()->domain ).'</i> - '.__( 'if you are SURE that you don\'t need your invoice objects any more then this option will delete invoices after export is done. Note that you won\'t be able to restore them.', ud_get_wp_invoice_power_tools()->domain ).'</li>';
    $data['Export/Import'][] = '</ul>';

    $data['Export/Import'][] = '<p>'.__( '<b>Import Options</b>', ud_get_wp_invoice_power_tools()->domain ).'</p>';
    $data['Export/Import'][] = '<p>'.__( '<u>Log History</u> - options for log history imports. See descriptions below:', ud_get_wp_invoice_power_tools()->domain ).'</p>';

    $data['Export/Import'][] = '<ul>';
    $data['Export/Import'][] = '<li><i>'.__( 'Add import log item.', ud_get_wp_invoice_power_tools()->domain ).'</i> - '.__( 'whether or not to add log item with information about importing invoice object.', ud_get_wp_invoice_power_tools()->domain ).'</li>';
    $data['Export/Import'][] = '</ul>';

    $data['Export/Import'][] = '<p>'.__( '<u>Recipients</u> - options for management recipients. See descriptions below:', ud_get_wp_invoice_power_tools()->domain ).'</p>';

    $data['Export/Import'][] = '<ul>';
    $data['Export/Import'][] = '<li><i>'.__( 'Override Recipient information if already exists.', ud_get_wp_invoice_power_tools()->domain ).'</i> - '.__( 'whether or not to replace existing user\'s information.', ud_get_wp_invoice_power_tools()->domain ).'</li>';
    $data['Export/Import'][] = '<li>'.__( 'You can decide what to do if user doesn\'t exist using the dropdown <i>\'If Recipient does not exist\'.</i>', ud_get_wp_invoice_power_tools()->domain ).'</li>';
    $data['Export/Import'][] = '<li>'.__( 'In order to the option that you have selected above, you\'ll be able to select <i>\'User Role\'</i> or <i>\'Specify Recipient (e-mail)\'</i>.', ud_get_wp_invoice_power_tools()->domain ).'</li>';
    $data['Export/Import'][] = '</ul>';

    return $data;
  }

  /**
   * AJAX handler for searcing user
   *
   * @author korotkov@ud
   */
  static function search_user() {
    if ( !empty( $_POST['email'] ) ) {
      die( json_encode( get_user_by('email', trim($_POST['email']) ) ) );
    }
  }
}

/**
 * Internal base class for Export Objects
 *
 * @author korotkov@ud
 */
if ( !class_exists('wpi_export_object') ) :
class wpi_export_object {

  /**
   * List of attributes for current object node.
   * @var mixed
   */
  public $attributes;

  /**
   * List of items for data node of current object.
   * @var mixed
   */
  public $data;

  /**
   * Current node name.
   * @var string
   */
  public $node = 'object';

  /**
   * Construct
   *
   * @param array $attrs
   * @param array $data
   * @param string $node
   */
  public function __construct( $attrs=null, $data=null, $node=false ) {
    if ( !empty( $attrs ) ) $this->attributes = $attrs;
    if ( !empty( $data ) ) $this->data = $data;
    if ( $node != false ) $this->node = $node;
  }

  /**
   * Appender for data.
   *
   * @param array $data
   * @return wpi_export_object
   */
  public function append_data( $data ) {
    $this->data[] = $data;
    return $this;
  }

  /**
   * Appender for attributes.
   *
   * @param array $attr
   * @return wpi_export_object
   */
  public function append_attribute( $attr ) {
    $this->attributes[key($attr)] = $attr[key($attr)];
    return $this;
  }

}
endif;

/**
 * Internal class for Import Object.
 *
 * This class used for generating import object (from XML or JSON) that will be
 * used for function that does Invoice inserting.
 *
 * @author korotkov@ud
 */
if ( !class_exists('wpi_import_object') ) :
class wpi_import_object {

  /**
   * Data that is responsible for data for post.
   *
   * @var array
   */
  public $post;

  /**
   * Data that is responsible for meta data of invoice post
   *
   * @var array
   */
  public $meta;

  /**
   * Current object's user data
   *
   * @var array
   */
  public $user;

  /**
   * Current object's log data
   *
   * @var array
   */
  public $log;

  /**
   * Appender for post fields
   *
   * @param array $data
   * @return wpi_import_object
   */
  public function append_post( $data ) {
    $this->post[key($data)] = $data[key($data)];
    return $this;
  }

  /**
   * Appender for meta fields
   *
   * @param array $data
   * @return wpi_import_object
   */
  public function append_meta( $data ) {
    $this->meta[key($data)] = $data[key($data)];
    return $this;
  }

  /**
   * Appender for user information
   *
   * @param array $data
   * @return wpi_import_object
   */
  public function append_user_data( $data ) {
    $this->user[key($data)] = $data[key($data)];
    return $this;
  }

  /**
   * Appender for log items
   *
   * @param array $data
   * @return wpi_import_object
   */
  public function append_log( $data ) {
    $this->log[] = $data;
    return $this;
  }

}
endif;

/**
 * Object meta
 *
 * @uses wpi_export_object
 * @author korotkov@ud
 */
if ( !class_exists('wpi_export_object_meta') ) :
class wpi_export_object_meta extends wpi_export_object {
  public $node = 'meta';
}
endif;

/**
 * Object item
 *
 * @uses wpi_export_object
 * @author korotkov@ud
 */
if ( !class_exists('wpi_export_object_item') ) :
class wpi_export_object_item extends wpi_export_object {
  public $node = 'item';
}
endif;

/**
 * Extended class for generating WP-Invoice Export XML
 *
 * @uses DOMDocument
 * @author korotkov@ud
 */
if ( !class_exists( 'WPI_Export_Xml' ) ) :
class WPI_Export_Xml extends DOMDocument {

  /**
   * Recursive node generator
   *
   * @param type $object
   * @param DOMElement $element
   * @author korotkov@ud
   */
  public function generate_from_object( $object, DOMElement $element = null ) {
    $element = is_null($element) ? $this : $element;

    if ( !is_null( $object->data ) ) {
      if ( is_array( $object->data ) ) {
        foreach( $object->data as $o ) {
          $node = $this->createElement($o->node);
          $element->appendChild($node);

          if ( !is_null( $o->attributes ) ) {
            foreach( $o->attributes as $attr_key => $attr_value ) {
              $node->setAttribute( $attr_key, $attr_value );
            }
          }

          $this->generate_from_object($o, $node);
        }
      } else {
        $element->appendChild($this->createTextNode($object->data));
      }
    }
  }
}
endif;
