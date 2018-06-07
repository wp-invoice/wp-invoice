<?php


  //** Function loads reports into global variable $wpi_reports */
  WPI_Functions::run_reports();

  //** Hookable array of reporting page sections and their callsback functions */
  $wpi_report_sections = apply_filters('wpi_report_sections', array(
    'overview_reports' => array(
      'label' => __('Overview', ud_get_wp_invoice()->domain),
      'callback' => array('WPI_Reports_page','overview_reports')
    )
  ));

class WPI_Reports_page {

/**
   * Show basic invoice information and averages, include a pie graph.
   *
   * These are basic reports mostly for demonstration purposes.
   *
   * @since 3.0
   *
   */
  static function overview_reports() {
    global $wpi_reports;

    ?>
      <script type="text/javascript">

        var wpi_graph_wrapper = {}
        var wpi_num_up = 3;
        var wpi_block_width;

        google.load('visualization', '1.0', {'packages':['corechart']});

        jQuery(document).ready(function() {

          wpi_graph_wrapper.width = (jQuery('.wpi_graph_wrapper').width() - 50);
          wpi_block_width = (wpi_graph_wrapper.width / wpi_num_up);

          jQuery("#wpi_overall_status").css('width', wpi_block_width + 'px');
          jQuery("#wpi_mvcs").css('width', wpi_block_width + 'px');

          wpi_overall_status();

          wpi_mvcs();

          wpi_top_line_items();

        });


        function wpi_overall_status() {

        <?php
        $overall_satus_array = array();
        if(isset($wpi_reports['total_paid'])) {
          $overall_satus_array[] = "['Paid', {$wpi_reports['total_paid']}]";
        }
        if(isset($wpi_reports['total_unpaid'])) {
          $overall_satus_array[] = "['Unpaid', {$wpi_reports['total_unpaid']}]";
        }
        ?>


          var data = new google.visualization.DataTable();
          data.addColumn('string', 'Status');
          data.addColumn('number', 'Count');
          data.addRows([<?php echo @implode(',', $overall_satus_array); ?>]);

          var options = {
          'title':'<?php _e('Collected and Uncollected Funds', ud_get_wp_invoice()->domain) ?>',
          'is3D': true,
          'legend': 'none',
          'width': (wpi_graph_wrapper.width / wpi_num_up),
          'height':400
          };

          var chart = new google.visualization.PieChart(document.getElementById('wpi_overall_status'));

          var formatter = new google.visualization.NumberFormat({prefix: '$', negativeColor: 'red', negativeParens: true});
          formatter.format(data, 1);

          chart.draw(data, options);
        }


        function wpi_mvcs() {
        <?php
        if(isset($wpi_reports['collected_client_value']) && is_array($wpi_reports['collected_client_value'])) {
          $counter = 0;
          $mvcs_array = array();
          foreach($wpi_reports['collected_client_value'] as $client_email => $client_value) {
            if($counter == 10) {
              break;
            }
            $mvcs_array[] = "['{$client_email}', $client_value] ";
            $counter++;
          }
        }
        ?>



          var data = new google.visualization.DataTable();
          data.addColumn('string', 'Status');
          data.addColumn('number', 'Count');
          data.addRows([<?php echo @implode(',', $mvcs_array); ?>]);

          var options = {
          'title':'<?php _e('Top 10 Most Valuable Clients', ud_get_wp_invoice()->domain) ?>',
          'is3D': true,
          'legend': 'none',
          'width': (wpi_graph_wrapper.width / wpi_num_up),
          'height':400
          };

          var chart = new google.visualization.PieChart(document.getElementById('wpi_mvcs'));

          var formatter = new google.visualization.NumberFormat({prefix: '$', negativeColor: 'red', negativeParens: true});
          formatter.format(data, 1);

          chart.draw(data, options);
        }


        function wpi_top_line_items() {
        <?php
        if($wpi_reports['line_item_counts']) {
          $counter = 0;
          $line_items_array = array();
          foreach($wpi_reports['line_item_counts'] as $line_item_name => $line_item_count) {

            if($counter == 10) {
              break;
            }

            //** Do not include rate line items */
            if($line_item_count < 2) {
              continue;
            }

            //** Do not include negative items */
            if($wpi_reports['collected_line_items'][$line_item_name] < 0) {
              continue;
            }

            $_name = addslashes($line_item_name);

            $line_items_array[] = "['{$_name}', {$wpi_reports['collected_line_items'][$line_item_name]}] ";

            $counter++;
          }
        }
        ?>
          var data = new google.visualization.DataTable();
          data.addColumn('string', 'Line Item');
          data.addColumn('number', 'Value');
          data.addRows([<?php echo @implode(',', $line_items_array); ?>]);

          var options = {
          'title':'<?php _e('Top Grossing Line Items', ud_get_wp_invoice()->domain) ?>',
          'is3D': true,
          'legend': 'none',
          'width': (wpi_graph_wrapper.width / wpi_num_up),
          'height':400
          };

          var chart = new google.visualization.PieChart(document.getElementById('wpi_top_line_items'));

          var formatter = new google.visualization.NumberFormat({prefix: '$', negativeColor: 'red', negativeParens: true});
          formatter.format(data, 1);

          chart.draw(data, options);
        }

    </script>

    <h3 class="section_header"><?php _e('Invoice Statistics', ud_get_wp_invoice()->domain); ?></h3>
    <div class="wpi_graph_wrapper">
      <div id="wpi_overall_status" class="wpi_report_block"></div>
      <div id="wpi_mvcs" class="wpi_report_block"></div>
      <div id="wpi_top_line_items" class="wpi_report_block"></div>
    </div>


    <?php
  }
}
?>

<script type="text/javascript">
  jQuery(document).ready( function() {

  });
</script>

<div class="wrap">
  <h2><?php _e('Reports', ud_get_wp_invoice()->domain); ?></h2>
  <?php WPI_Functions::print_messages(); ?>

  <div id="wpi_report_page" class="wpi_sectioned_content">


    <?php foreach($wpi_report_sections as $tab_id => $tab) {    ?>
      <div id="wpi_tab_<?php echo $tab_id; ?>" class="wpi_section" >
        <?php
        if(is_callable($tab['callback'])) {
          call_user_func($tab['callback'], $wpi_settings);
        } else {
          echo __('Warning:', ud_get_wp_invoice()->domain) . ' ' . implode(':', $tab['callback']) .' ' .  __('not found', ud_get_wp_invoice()->domain) . '.';
        }
        ?>
      </div>
    <?php } ?>

  </div><?php /* end: #wpi_report_page */ ?>


</div>
