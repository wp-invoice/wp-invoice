<?php
/**
 * Returns an invoice object as an array.
 *
 * @param type $args
 * @return WPI_Invoice
 */
function get_invoice($args) {
  if (is_numeric($args)) {
    $invoice_id = $args;
  } else {
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    $defaults = array('invoice_id' => '', 'return_class' => false);
  }
  $invoice = new WPI_Invoice();
  $invoice->load_invoice("id=$invoice_id");

  if (!empty($invoice->error) && $invoice->error) {
    return sprintf(__("Invoice %s not found.", WPI), $invoice_id);
  }

  if (!empty($return_class) && $return_class) {
    return $invoice;
  }

  return $invoice->data;
}

/**
 * Logging
 *
 * @param type $event
 */
function wpi_log_event($event) {
  WPI_Functions::log($event);
}

/**
 * Converted to WP 2.0
 * Archives an invoice, or multiple invoices.
 *
 * @global type $wpdb
 * @param type $invoice_id
 * @return type
 */
function wpi_archive_invoice($invoice_id) {
  global $wpdb;
  // Check to see if array is passed or single.
  if (is_array($invoice_id)) {
    $counter = 0;
    foreach ($invoice_id as $single_invoice_id) {
      $this_invoice = new WPI_Invoice();
      $this_invoice->load_invoice("id=$single_invoice_id");
      $this_invoice->set("status=archive");
      $this_invoice->add_entry(__("Archived.", WPI));
      if ($this_invoice->save_invoice())
        $counter++;
    }
    return __("$counter  invoice(s) archived.", WPI);
  } else {
    $this_invoice = new WPI_Invoice();
    $this_invoice->load_invoice("id=$invoice_id");
    $this_invoice->set("status=archive");
    $this_invoice->add_entry(__("Archived.", WPI));
    if ($this_invoice->save_invoice())
      return __('Successfully archived.', WPI);
  }
}

/**
 * Invoice lookup function
 * If return is passed as true, function is returned.
 *
 * @global type $wpi_settings
 * @param type $args
 * @return type
 */
function wp_invoice_lookup($args = '') {
  global $wpi_settings, $current_user;
  $defaults = array(
      'message' => __('Enter Invoice ID', WPI),
      'button' => __('Lookup', WPI),
      'return' => false
  );
  extract(wp_parse_args($args, $defaults), EXTR_SKIP);

  if (!$current_user->ID)
    return;

  ob_start();
  if (WPI_Functions::wpi_use_custom_template('invoice_lookup.php'))
    include($wpi_settings['frontend_template_path'] . 'invoice_lookup.php');
  else
    include($wpi_settings['default_template_path'] . 'invoice_lookup.php');
  $result .= ob_get_contents();
  ob_end_clean();
  if ($return)
    return $result;
  echo $result;
}

/**
 * TO keep wpi naming structure
 *
 * @param type $args
 * @return type
 */
function wpi_invoice_lookup($args = '') {
  return wp_invoice_lookup($args);
}

/**
 * Draw widget by shortcode
 *
 * @param array $args
 */
function wp_invoice_history($args = '') {
  echo the_widget('InvoiceHistoryWidget', $args);
}

?>