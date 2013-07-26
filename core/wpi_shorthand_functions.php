<?php
/**
 * Place for functions that may be used throughout WP, particularly in themes
 * @param int/string $identificator. It can be hash, invoice_id or post ID
 */
function get_invoice_permalink($identificator) {
  global $wpi_settings, $wpdb;

  $hash = "";
  //** Check Invoice by ID and get hash */
  if (empty($identificator)) return false;

  $id = get_invoice_id($identificator);

  //** Get hash by post ID */
  if(!empty($id)) {
    $hash = $wpdb->get_var($wpdb->prepare("SELECT md5(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND post_id = %d",
      $id
    ));
  }

  if(empty($hash) || empty($wpi_settings['web_invoice_page'])) {
    return false;
  }

  if(get_option("permalink_structure")) {
      return get_permalink($wpi_settings['web_invoice_page']) . "?invoice_id=" . $hash;
  } else {
    //** check if page is on front-end */
    if(get_option('page_on_front') == $wpi_settings['web_invoice_page']) {
      return get_permalink($wpi_settings['web_invoice_page']) . "?invoice_id=" . $hash;
    } else {
      return get_permalink($wpi_settings['web_invoice_page']) . "&invoice_id=" . $hash;
    }
  }
}

/*
 * This function can be used to get invoice id by several identifiers (hash, custom_id, invoice_id, post_id)
 * @author odokienko@UD
 * @return bool|int False or the invoice id
 */
function get_invoice_id($identificator){
  global $wpdb;

  $id = false;
  if (strlen($identificator) == 32) {
    //** Determine if $identificator is invoice HASH */
    $id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='invoice_id' and md5(meta_value)=%s",
        $identificator
    ));
  }

  if ( empty( $id ) ){
    //** Determine if $identificator id custom_id - korotkov@ud */
    $id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'custom_id' AND meta_value = %s",
        $identificator
    ));
  }

  //** Determine if $identificator is invoice_id */
  if ( empty( $id ) ) {
    $id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = %s",
      $identificator
    ));
  }

  //** If empty id, determine if $identificator is post ID */
  if ( empty( $id ) ) {
    $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='wpi_object' and ID=%s",
      $identificator
    ));
  }

  return $id;
}

/**
 * Checks Invoice Exists or not
 * @return boolean
 */
function wpi_check_invoice($ID) {
  global $wpdb;

  if(empty($ID) || (int)$ID == 0) {
    return false;
  }
  $result = $wpdb->get_var("SELECT post_status FROM {$wpdb->posts} WHERE ID = '$ID'");
  if(empty($result)) {
    return false;
  }
  return true;
}

/**
 * Used to return array of favorite coutnries
 */
function wpi_get_favorite_countries($args) {
  global $wpi_settings;
  $defaults = array('return' => 'options');
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
  if($return == 'options') {
    foreach(explode(",", $wpi_settings[globals][favorite_countries]) as $country_code) {
      $return .= "<option value='$country_code' >{$wpi_settings[countries][$country_code]}</option>";
    }
    return $return;
  }
  if($return == 'array') {
    return explode(",", $wpi_settings[globals][favorite_countries]);
  }
}

/**
 * This function converts an invoices invoice_id to a post_id or returns post_id if it was passed
 * @param int $invoice_id The invoice ID
 * @return bool|int False or the post id
 * @since 3.0
 */
function wpi_invoice_id_to_post_id($invoice_id){
  global $wpdb;
  $maybe_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = '{$invoice_id}'");

  return $maybe_id ? $maybe_id : $invoice_id;

}

/**
 * This function converts a ARB subscription id subscription_id to a post_id
 * @param int $subscription_id The subscription ID
 * @return bool|int False or the post id
 * @since 3.0
 */
function wpi_subscription_id_to_post_id($subscription_id) {
  global $wpdb;
  return $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'subscription_id' AND meta_value = '{$subscription_id}'");
}

/**
 * This function converts an invoices post_id to a invoice_id
 * @param int $post_id The post ID
 * @return bool|int False or the invoice id
 * @since 3.0
 */
function wpi_post_id_to_invoice_id($post_id){
  return get_metadata('post', $post_id, 'invoice_id', true);
}