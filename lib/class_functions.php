<?php
/**
 *
 * Handles all functions
 *
 */
setlocale( LC_MONETARY, 'en_US' );

class WPI_Functions {

  /**
   * PHP function to echoing a message to JS console
   * Ported from WP-Property
   *
   * @since 3.0.3
   */
  static function console_log( $text = false ) {
    global $wpi_settings;

    if ( isset( $wpi_settings[ 'developer_mode' ] ) && $wpi_settings[ 'developer_mode' ] != 'true' ) {
      return;
    }

    if ( empty( $text ) ) {
      return;
    }

    if ( is_array( $text ) || is_object( $text ) ) {
      $text = str_replace( "\n", '', print_r( $text, true ) );
    }

    //** Cannot use quotes */
    $text = str_replace( '"', '-', $text );

    add_filter( 'wp_footer', create_function( '$nothing,$echo_text = "' . $text . '"', 'echo \'<script type="text/javascript">if(typeof console == "object"){console.log("\' . $echo_text . \'");}</script>\'; ' ) );
    add_filter( 'admin_footer', create_function( '$nothing,$echo_text = "' . $text . '"', 'echo \'<script type="text/javascript">if(typeof console == "object"){console.log("\' . $echo_text . \'");}</script>\'; ' ) );

  }

  /**
   * Function for performing a wpi_object search
   *
   * @since 3.0
   */
  static function query( $search_vars = false ) {
    global $wpdb;

    $sort_by = " ORDER BY post_modified DESC ";
    //** Start our SQL */
    $sql = "SELECT * FROM {$wpdb->posts} AS p WHERE post_type = 'wpi_object' ";

    if ( !empty( $search_vars ) ) {

      if ( is_string( $search_vars ) ) {
        $args = array();
        parse_str( $search_vars, $args );
        $search_vars = $args;
      }

      foreach ( $search_vars as $primary_key => $key_terms ) {

        //** Handle search_string differently, it applies to all meta values */
        if ( $primary_key == 's' ) {
          //** First, go through the posts table */
          $tofind = strtolower( $key_terms );
          $sql .= " AND (";
          $sql .= " p.ID IN (SELECT ID FROM {$wpdb->posts} WHERE LOWER(post_title) LIKE '%$tofind%')";
          //** Now go through the post meta table */
          $sql .= " OR p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE LOWER(meta_value) LIKE '%$tofind%')";
          $sql .= ")";
          continue;
        }

        //** Type */
        if ( $primary_key == 'type' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          if ( is_array( $key_terms ) ) {
            $key_terms = implode( "','", $key_terms );
          }
          $sql .= " AND ";
          $sql .= " p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'type' AND meta_value IN ('{$key_terms}'))";
          continue;
        }

        //** Status */
        if ( $primary_key == 'status' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          if ( is_array( $key_terms ) ) {
            $sql .= " AND (";
            $i = 0;
            foreach ( $key_terms as $term ) {
              if ( empty( $term ) ) {
                continue;
              }
              if ( $i > 0 ) {
                $sql .= " OR ";
              }
              $sql .= " post_status = '{$term}' ";
              $i++;
            }
            $sql .= ")";
          }
        }

        //** Recipient */
        if ( $primary_key == 'recipient' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          $user = get_user_by( 'id', $key_terms );

          $sql .= " AND ";
          $sql .= " p.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'user_email' AND meta_value = '{$user->user_email}')";

          continue;
        }

        //** Sorting */
        if ( $primary_key == 'sorting' ) {
          $sort_by = " ORDER BY {$key_terms['order_by']} {$key_terms['sort_dir']} ";
        }

        //** Date */
        if ( $primary_key == 'm' ) {
          if ( empty( $key_terms ) || (int) $key_terms == 0 ) {
            continue;
          }

          $key_terms = '' . preg_replace( '|[^0-9]|', '', $key_terms );
          $sql .= " AND YEAR(post_date)=" . substr( $key_terms, 0, 4 );
          if ( strlen( $key_terms ) > 5 ) {
            $sql .= " AND MONTH(post_date)=" . substr( $key_terms, 4, 2 );
          }
          if ( strlen( $key_terms ) > 7 ) {
            $sql .= " AND DAYOFMONTH(post_date)=" . substr( $key_terms, 6, 2 );
          }
          if ( strlen( $key_terms ) > 9 ) {
            $sql .= " AND HOUR(post_date)=" . substr( $key_terms, 8, 2 );
          }
          if ( strlen( $key_terms ) > 11 ) {
            $sql .= " AND MINUTE(post_date)=" . substr( $key_terms, 10, 2 );
          }
          if ( strlen( $key_terms ) > 13 ) {
            $sql .= " AND SECOND(post_date)=" . substr( $key_terms, 12, 2 );
          }
        }
      }
    }

    $sql = $sql . $sort_by;
    $results = $wpdb->get_results( $sql );

    return $results;
  }

  /**
   * Query sales.
   *
   * @global object $wpdb
   *
   * @param array $search_vars
   * @param string $interval
   *
   * @return mixed
   * @author odokienko@UD
   */
  static function get_sales_by( $search_vars = false, $interval = "weekly" ) {
    global $wpdb;
    
    $sql = '';

    switch ( $interval ) {
      case "weekly":
        $interval_function = 'WEEK';
        break;
      case 'daily':
        $interval_function = 'DAYOFYEAR';
        break;
      case 'monthly':
      default:
        $interval_function = 'MONTH';
        break;
    }

    if ( !empty( $search_vars ) ) {

      if ( is_string( $search_vars ) ) {
        $args = array();
        parse_str( $search_vars, $args );
        $search_vars = $args;
      }

      foreach ( $search_vars as $primary_key => $key_terms ) {

        //** Handle search_string differently, it applies to all meta values */
        if ( $primary_key == 'wplt_filter_s' && !empty( $key_terms ) ) {
          /* First, go through the posts table */
          $tofind = strtolower( $key_terms );
          $sql .= " AND (";
          $sql .= " LOWER(`{$wpdb->posts}`.post_title) LIKE '%$tofind%'";
          /* Now go through the post meta table */
          $sql .= " OR
                    `{$wpdb->posts}`.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE LOWER(meta_value) LIKE '%$tofind%')";
          $sql .= ")";
          continue;
        }

        // Type
        if ( $primary_key == 'wplt_filter_type' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          if ( is_array( $key_terms ) ) {
            $key_terms = implode( "','", $key_terms );
          }
          $sql .= " AND ";
          $sql .= " `{$wpdb->posts}`.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'type' AND meta_value IN ('{$key_terms}'))";
          continue;
        }

        // Status
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

        // Recipient
        if ( $primary_key == 'wplt_filter_user_email' ) {
          if ( empty( $key_terms ) ) {
            continue;
          }

          $sql .= " AND ";
          $sql .= " `{$wpdb->posts}`.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'user_email' AND meta_value = '{$key_terms}')";

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
      }
    }

    $date_table = "
        select @maxDate - interval (a.a+(10*b.a)+(100*c.a)) day aDate from
        (select 0 as a union all select 1 union all select 2 union all select 3
        union all select 4 union all select 5 union all select 6 union all
        select 7 union all select 8 union all select 9) a, /*10 day range*/
        (select 0 as a union all select 1 union all select 2 union all select 3
        union all select 4 union all select 5 union all select 6 union all
        select 7 union all select 8 union all select 9) b, /*100 day range*/
        (select 0 as a union all select 1 union all select 2 union all select 3
        union all select 4 union all select 5 union all select 6 union all
        select 7 union all select 8 union all select 9) c, /*1000 day range*/
        (select
          @minDate := date_format(FROM_UNIXTIME((select min(time) from `{$wpdb->base_prefix}wpi_object_log` mn join {$wpdb->posts} on (mn.object_id=`{$wpdb->posts}`.id and `{$wpdb->posts}`.post_type = 'wpi_object' {$sql}))),'%Y-%m-%d'),
          @maxDate := date_format(FROM_UNIXTIME((select max(time) from `{$wpdb->base_prefix}wpi_object_log` mx join {$wpdb->posts} on (mx.object_id=`{$wpdb->posts}`.id and `{$wpdb->posts}`.post_type = 'wpi_object' {$sql}))),'%Y-%m-%d')
        ) e
      ";

    $sql = "
      SELECT distinct
      YEAR(datetable.aDate) as int_year,
      {$interval_function}(datetable.aDate) int_erval,
      sum(COALESCE(if (payment.action='refund',payment.value*-1,payment.value),0)) as sum_interval
      FROM ($date_table) datetable
      left join `{$wpdb->base_prefix}wpi_object_log` as payment on (
        datetable.aDate=date_format(FROM_UNIXTIME(payment.time),'%Y-%m-%d')
        and (
          payment.object_id in (
            select `{$wpdb->posts}`.id from `{$wpdb->posts}` where `{$wpdb->posts}`.post_type = 'wpi_object' {$sql}
          )
        )
        AND (payment.action = 'add_payment' or payment.action = 'refund')
        AND payment.attribute = 'balance'
      )

      WHERE datetable.aDate between @minDate and @maxDate
      GROUP BY 1,2
    ";

    $results = $wpdb->get_results( $sql );

    return $results;
  }

  /**
   * Get Search filter fields
   *
   * @global array $wpi_settings
   * @global object $wpdb
   * @return type
   */
  static function get_search_filters() {
    global $wpi_settings, $wpdb;

    $filters = array();

    $default = array( array(
      'key' => 'all',
      'label' => __( 'All', ud_get_wp_invoice()->domain ),
      'amount' => 0
    ) );

    if ( isset( $wpi_settings[ 'types' ] ) ) {
      $f = $default;
      $i = 1;
      $all = 0;
      foreach ( $wpi_settings[ 'types' ] as $key => $value ) {
        $amount = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'type' AND meta_value = '{$key}'" );
        $all = $all + $amount;
        if ( $amount > 0 ) {
          $f[ $i ][ 'key' ] = $key;
          $f[ $i ][ 'label' ] = $value[ 'label' ];
          $f[ $i ][ 'amount' ] = $amount;
          $i++;
        }
      }
      if ( $all > 0 ) {
        $f[ 0 ][ 'amount' ] = $all;
        $filters[ 'type' ] = $f;
      }
      // If there is only 1 type - hide Types option
      if ( $i == 2 ) {
        unset( $filters[ 'type' ] );
      }
    }

    if ( !empty( $wpi_settings[ 'statuses' ] ) ) {
      $f = array();
      $amount = 0;
      $i = 1;
      $all = 0;
      foreach ( $wpi_settings[ 'statuses' ] as $status ) {
        $amount = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = '{$status}' AND post_type = 'wpi_object'" );
        $all = $all + $amount;
        if ( $amount > 0 ) {
          $f[ $i ][ 'key' ] = $status;
          $f[ $i ][ 'label' ] = strtoupper( substr( $status, 0, 1 ) ) . substr( $status, 1 );
          $f[ $i ][ 'amount' ] = $amount;
          $i++;
        }
      }
      if ( $all > 0 ) {
        $filters[ 'status' ] = $f;
      }
    }

    return $filters;
  }

  /**
   * Convert a string to a url-like slug
   *
   * @param type $slug
   *
   * @return type
   */
  static function slug_to_label( $slug = false ) {

    if ( !$slug )
      return;

    $slug = str_replace( "_", " ", $slug );
    $slug = ucwords( $slug );
    return $slug;
  }

  /**
   * Convert a string into a number. Allow invoice ID to be passed for currency symbol localization
   *
   * @global array $wpi_settings
   *
   * @param type $amount
   * @param type $invoice_id
   *
   * @return type
   */
  static function currency_format( $amount, $invoice_id = false ) {
    global $wpi_settings;
    
    $invoice = false;

    if ( $invoice_id ) {
      $invoice = get_invoice( $invoice_id );
    }
    
    if ( $invoice ) {
      $currency_symbol = !empty( $wpi_settings[ 'currency' ][ 'symbol' ][ $invoice[ 'default_currency_code' ] ] ) ? $wpi_settings[ 'currency' ][ 'symbol' ][ $invoice[ 'default_currency_code' ] ] : '$';
    } else {
      $currency_symbol = $wpi_settings[ 'currency' ][ 'symbol' ][ $wpi_settings[ 'currency' ][ 'default_currency_code' ] ];
    }

    $amount = (float) $amount;

    $thousands_separator_symbol = !isset( $wpi_settings[ 'thousands_separator_symbol' ] ) ? ',' : ( $wpi_settings[ 'thousands_separator_symbol' ] == '0' ? '' : $wpi_settings[ 'thousands_separator_symbol' ] );
    $decimal_separator_symbol = !isset( $wpi_settings[ 'decimal_separator_symbol' ] ) ? ',' : ( $wpi_settings[ 'decimal_separator_symbol' ] == '0' ? '' : $wpi_settings[ 'decimal_separator_symbol' ] );

    return $currency_symbol . number_format( $amount, 2, $decimal_separator_symbol, $thousands_separator_symbol );
  }

  /**
   * Run numbers for reporting purposes.
   *
   * @global object $wpdb
   * @global type $wpi_reports
   * @return type
   */
  static function run_reports() {
    global $wpdb, $wpi_reports;

    //* Get all invoices */
    $invoice_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'wpi_object' " );

    $totals = array();
    $objects = array();
    $r = array(
      'line_item_counts' => array()
    );

    foreach ( $invoice_ids as $post_id ) {
      $wpi_invoice_object = new WPI_Invoice();
      $wpi_invoice_object->load_invoice( "id=$post_id" );
      $objects[ $post_id ] = $wpi_invoice_object->data;
    }

    foreach ( $objects as $object ) {

      //** Total paid invoices per client  */
      if ( $object[ 'post_status' ] == 'paid' ) {
        $r[ 'collected_client_value' ][ $object[ 'user_email' ] ] = !empty( $r[ 'client_value' ][ $object[ 'user_email' ] ] ) ? $r[ 'client_value' ][ $object[ 'user_email' ] ] : 0 + $object[ 'subtotal' ] + $object[ 'total_tax' ] - $object[ 'total_discount' ];
        $r[ 'total_paid' ][ ] = $objects[ $object[ 'ID' ] ][ 'subtotal' ] + $objects[ $object[ 'ID' ] ][ 'total_tax' ] - $objects[ $object[ 'ID' ] ][ 'total_discount' ];;

        foreach ( $object[ 'itemized_list' ] as $list_item ) {
          $r[ 'collected_line_items' ][ $list_item[ 'name' ] ] = !empty( $r[ 'collected_line_items' ][ $list_item[ 'name' ] ] ) ? $r[ 'collected_line_items' ][ $list_item[ 'name' ] ] : 0 + $list_item[ 'line_total_after_tax' ];
          if ( !empty( $r[ 'line_item_counts' ][ $list_item[ 'name' ] ] ) ) {
            $r[ 'line_item_counts' ][ $list_item[ 'name' ] ]++;
          } else {
            $r[ 'line_item_counts' ][ $list_item[ 'name' ] ] = 1;
          }
        }
      }

      if ( $object[ 'post_status' ] == 'active' ) {
        $r[ 'uncollected_client_value' ][ $object[ 'user_email' ] ] = !empty( $r[ 'uncollected_client_value' ][ $object[ 'user_email' ] ] ) ? $r[ 'uncollected_client_value' ][ $object[ 'user_email' ] ] : 0 + $object[ 'subtotal' ] + $object[ 'total_tax' ] - $object[ 'total_discount' ];
        $r[ 'total_unpaid' ][ ] = $objects[ $object[ 'ID' ] ][ 'subtotal' ] + $objects[ $object[ 'ID' ] ][ 'total_tax' ] - $objects[ $object[ 'ID' ] ][ 'total_discount' ];
      }

    }

    if ( isset( $r[ 'collected_line_items' ] ) && is_array( $r[ 'collected_line_items' ] ) ) {
      arsort( $r[ 'collected_line_items' ] );
    }

    if ( isset( $r[ 'uncollected_client_value' ] ) && is_array( $r[ 'uncollected_client_value' ] ) ) {
      arsort( $r[ 'uncollected_client_value' ] );
    }

    if ( isset( $r[ 'collected_client_value' ] ) && is_array( $r[ 'collected_client_value' ] ) ) {
      arsort( $r[ 'collected_client_value' ] );
    }

    if ( isset( $r[ 'total_paid' ] ) && is_array( $r[ 'total_paid' ] ) ) {
      $r[ 'total_paid' ] = array_sum( $r[ 'total_paid' ] );
    }

    if ( isset( $r[ 'total_unpaid' ] ) && is_array( $r[ 'total_unpaid' ] ) ) {
      $r[ 'total_unpaid' ] = array_sum( $r[ 'total_unpaid' ] );
    }

    $wpi_reports = $r;
    return $r;
  }

  /**
   * Check if theme-specific stylesheet exists.
   *
   * get_option('template') seems better choice than get_option('stylesheet'), which returns the current theme's slug
   * which is a problem when a child theme is used. We want the parent theme's slug.
   *
   * @since 3.0
   *
   */
  static function has_theme_specific_stylesheet() {

    $theme_slug = get_option( 'template' );
    
    if ( file_exists( ud_get_wp_invoice()->path( "static/views/theme-specific/{$theme_slug}.css", 'dir' ) ) ) {
      return true;
    }

    return false;
  }

  /**
   * Check if Payment method allowed
   *
   * @param String $param
   *
   * @return bool
   */
  static function is_true( $param ) {
    if ( empty( $param ) )
      return false;
    return ( $param == 'true' || $param == 'on' || $param == 'yes' ) ? true : false;
  }

  /**
   * Fixes billing structure
   *
   * @param array $wpi_settings_billings
   * @param array &$invoice_billings
   */
  static function merge_billings( $wpi_settings_billings, &$invoice_billings ) {
    if ( !isset( $invoice_billings ) || !is_array( $invoice_billings ) ) {
      $invoice_billings = array();
    }
    if ( is_array( $wpi_settings_billings ) ) {
      foreach ( $wpi_settings_billings as $key => $value ) {
        if ( empty($value[ 'allow' ]) || !WPI_Functions::is_true( $value[ 'allow' ] ) ) {
          unset( $invoice_billings[ $key ] );
        } else {
          if ( !empty( $invoice_billings[ $key ] ) ) {
            if ( !isset( $invoice_billings[ $key ][ 'name' ] ) ) {
              $invoice_billings[ $key ][ 'name' ] = $value[ 'name' ];
            }
            if ( !isset( $invoice_billings[ $key ][ 'allow' ] ) ) {
              $invoice_billings[ $key ][ 'allow' ] = $value[ 'allow' ];
            }
            if ( !isset( $invoice_billings[ $key ][ 'default_option' ] ) ) {
              $invoice_billings[ $key ][ 'default_option' ] = $value[ 'default_option' ];
            }
            if ( !empty( $value[ 'settings' ] ) ) {
              foreach ( $value[ 'settings' ] as $setting_key => $setting_value ) {
                foreach ( $setting_value as $setting_key_field => $setting_value_field ) {
                  if ( !isset( $invoice_billings[ $key ][ 'settings' ][ $setting_key ][ $setting_key_field ] ) ) {
                    $invoice_billings[ $key ][ 'settings' ][ $setting_key ][ $setting_key_field ] = $setting_value_field;
                  }
                }
              }
            }
          } else {
            $invoice_billings[ $key ] = $value;
          }
        }
      }
    }
  }

  function set_default_payment_method( $wpi_settings_billings, $invoice_data ) {
    $settings_dpm = '';

    if ( !empty( $wpi_settings_billings ) && is_array( $wpi_settings_billings ) ) {
      foreach ( $wpi_settings_billings as $method => $value ) {
        if ( $value[ 'default_option' ] == 'true' ) {
          $settings_dpm = $method;
        }
      }
    }

    $invoice_data[ 'default_payment_method' ] = $settings_dpm;
  }

  /**
   * Returns an array of users
   * Used for user-email auto-completion.
   *
   * @uses $wpdb
   * @since 3.0
   * @return array. Users List
   *
   */
  function build_user_array() {
    global $wpdb;

    return $wpdb->get_results( "SELECT display_name,user_email,ID FROM {$wpdb->prefix}users", ARRAY_A );
  }

  /**
   * Handle user data updating
   *
   * Typically called when saving an invoice.
   *
   * @since 3.0
   */
  static function update_user( $userdata ) {

    $user_id = email_exists( $userdata[ 'user_email' ] );

    if ( $user_id ) {
      $userdata[ 'ID' ] = $user_id;
    }

    if ( empty( $userdata[ 'ID' ] ) && empty( $userdata[ 'user_email' ] ) ) {
      return false;
    }

    if ( $user_id ) {
      $user_id = wp_update_user( $userdata );
    } else {
      if ( empty( $userdata[ 'user_login' ] ) ) {
        if ( !empty( $userdata[ 'first_name' ] ) && !empty( $userdata[ 'last_name' ] ) ) {
          $userdata[ 'display_name' ] = $userdata[ 'first_name' ] . ' ' . $userdata[ 'last_name' ];
        } else {
          //** Nothing to do here... */
        }
      }

      $userdata[ 'user_login' ] = $userdata[ 'user_email' ];

      if ( empty( $userdata[ 'user_pass' ] ) ) {
        $userdata[ 'user_pass' ] = wp_generate_password( 12, false );
      }

      $user_id = wp_insert_user( $userdata );

      global $wpi_settings;
      if ( is_int( $user_id ) && WPI_Functions::is_true( $wpi_settings['send_password_to_new_users'] ) ) {
        wp_mail(
          $userdata[ 'user_email' ],
          sprintf( __( 'Your Access Credentials [%s]', ud_get_wp_invoice()->domain ), get_bloginfo('url') ),
          sprintf( __( "Dear customer,\n\nwe have set an account for you on %s. You are able to login now using the following credentials:\n\nURL: %s\nLogin: %s\nPassword: %s", ud_get_wp_invoice()->domain ), get_bloginfo('url'), wp_login_url(), $userdata[ 'user_login' ], $userdata[ 'user_pass' ] )
        );
      }

    }

    //** Prevent entering of wrong phone number to avoid errors on front-end */
    if ( !preg_match( '/\A[\d.+?]{0,3}-[\d.+?]{0,3}-[\d.+?]{0,4}\Z/si', $userdata[ 'phonenumber' ] ) ) {
      if ( preg_match( '/\A[\d.+?]{0,10}\Z/si', $userdata[ 'phonenumber' ] ) ) {
        $phonenumber = $userdata[ 'phonenumber' ];
        $userdata[ 'phonenumber' ] = substr( $phonenumber, 0, 3 ) . '-' . substr( $phonenumber, 3, 3 ) . '-' . substr( $phonenumber, 6, 4 );
      } else {
        $userdata[ 'phonenumber' ] = '';
      }
    }

    if ( !is_object( $user_id ) && $user_id > 0 ) {
      /* Update user's meta data */
      $non_meta_data = array(
        'ID',
        'first_name',
        'last_name',
        'nickname',
        'description',
        'user_pass',
        'user_email',
        'user_url',
        'user_nicename',
        'display_name',
        'user_registered',
        'role'
      );
      foreach ( $userdata as $key => $value ) {
        if ( !in_array( $key, $non_meta_data ) ) {
          update_user_meta( $user_id, $key, $value );
        }
      }

      return $user_id;
    }

    return $user_id;
  }

  /**
   * Add itemized charge like itemized list item
   *
   * @param int $invoice_id
   * @param string $name
   * @param float $amount
   * @param float $tax
   *
   * @return array
   */
  function add_itemized_charge( $invoice_id, $name, $amount, $tax ) {

    $post_id = wpi_invoice_id_to_post_id( $invoice_id );
    $charge_items = get_post_meta( $post_id, 'itemized_charges', true );

    $new_item = array(
      'name' => $name,
      'amount' => $amount,
      'tax' => $tax,
      'before_tax' => $amount,
      'after_tax' => $amount + ( $amount / 100 * $tax )
    );

    if ( !empty( $charge_items ) ) {
      $charge_items[ ] = $new_item;
    } else {
      $charge_items[ 0 ] = $new_item;
    }

    update_post_meta( $post_id, 'itemized_charges', $charge_items );

    return end( $charge_items );
  }

  /**
   * Loads invoice variables into post if it is a wpi_object
   *
   * @hooked_into setup_postdata()
   * @uses $wpdb
   * @since 3.0
   *
   */
  static function the_post( &$post ) {
    global $post;

    if ( $post->post_type == 'wpi_object' ) {
      $this_invoice = new WPI_Invoice();
      $invoice_id = $post->ID;
      $this_invoice->load_invoice( "id=$invoice_id" );

      $t_post = (array) $post;
      $t_data = (array) $this_invoice->data;

      $t_post = WPI_Functions::array_merge_recursive_distinct( $t_post, $t_data );
      $post = (object) $t_post;
    }
  }

  /**
   *
   * @param type $object
   * @return type
   */
  static function objectToArray( $object ) {
    if ( !is_object( $object ) && !is_array( $object ) ) {
      return $object;
    }
    if ( is_object( $object ) ) {
      $object = get_object_vars( $object );
    }
    return array_map( array( 'WPI_functions', 'objectToArray' ), $object );
  }

  /**
   *
   * @param type $title
   * @return type
   */
  function generateSlug( $title ) {
    $slug = preg_replace( "/[^a-zA-Z0-9 ]/", "", $title );
    $slug = str_replace( " ", "_", $slug );
    return $slug;
  }

  /**
   * Figure out current page for front-end AJAX function
   */
  static function current_page() {
    $pageURL = 'http';
    if ( is_ssl() ) {
      $pageURL .= "s";
    }
    $pageURL .= "://";
    if ( $_SERVER[ "SERVER_PORT" ] != "80" ) {
      $pageURL .= $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ];
    } else {
      $pageURL .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
    }
    return $pageURL;
  }

  /**
   * get invoices by user and status
   *
   * @global object $wpdb
   *
   * @param type $args
   *
   * @return boolean|\WPI_Invoice
   */
  static function get_user_invoices( $args ) {
    
    $defaults = array( 'user_id' => false, 'status' => false, 'type' => false );
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    //** User email and id are the same thing */
    if ( !$user_id && isset( $user_email ) ) {
      $user_id = $user_email;
    }

    //** If nothing is set, nothing we can do */
    if ( !isset( $user_id ) ) {
      return;
    }
          
    $query_invoices = new WP_Query(array(
      'post_type' => 'wpi_object',
      'post_status' => $status,
      'meta_query' => array(
		array(
          'key'     => 'user_email',
          'value'   => $user_id
		),
        array(
          'key'   => 'type',
          'value' => $type
        )
      ),
      'posts_per_page' => -1
    ));

    //** Nothing found */
    if ( empty( $query_invoices->posts ) ) {
      return array();
    }

    $return = array();

    foreach ( $query_invoices->posts as $_post ) {

      $invoice_id = wpi_post_id_to_invoice_id( $_post->ID );

      $this_invoice = new WPI_Invoice();
      $this_invoice->load_invoice( "id={$invoice_id}" );

      $return[ ] = $this_invoice;
    }

    return $return;
  }

  /**
   * Add message to notice queue
   */
  static function add_message( $message, $type = 'good', $class = '' ) {
    global $wpi_messages;
    if ( !is_array( $wpi_messages ) ) {
      $wpi_messages = array();
    }

    array_push( $wpi_messages, array( 'message' => $message, 'type' => $type, 'class' => $class ) );
  }

  /**
   * Display messages in queve
   * @global type $wpi_messages
   * @return type
   */
  static function print_messages() {
    global $wpi_messages;

    if ( count( $wpi_messages ) < 1 ) {
      return;
    }

    $update_messages = array();
    $warning_messages = array();

    echo '<div id="wpi_message_stack">';

    foreach ( $wpi_messages as $message ) {

      if ( $message[ 'type' ] == 'good' ) {
        array_push( $update_messages, array( 'message' => $message[ 'message' ], 'class' => $message[ 'class' ] ) );
      }

      if ( $message[ 'type' ] == 'bad' ) {
        array_push( $warning_messages, array( 'message' => $message[ 'message' ], 'class' => $message[ 'class' ] ) );
      }
    }

    if ( count( $update_messages ) > 0 ) {
      echo "<div class='wpi_message wpi_yellow_notification'>";
      foreach ( $update_messages as $u_message )
        echo "<div class='wpi_message_holder {$message['class']}' >{$u_message['message']}</div>";
      echo "</div>";
    }

    if ( count( $warning_messages ) > 0 ) {
      echo "<div class='wpi_message wpi_red_notification'>";
      foreach ( $warning_messages as $w_message )
        echo "<div class='wpi_message_holder {$w_message['class']}' >{$w_message['message']}</div>";
      echo "</div>";
    }

    echo "</div>";
  }

  /**
   * Checks if particular template exists in the template folder
   *
   * @global array $wpi_settings
   *
   * @param type $template
   *
   * @return type
   */
  static function wpi_use_custom_template( $template ) {
    global $wpi_settings;

    /* if custom templates are turned off, don't bother checking */
    if ( !isset( $wpi_settings[ 'use_custom_templates' ] ) || $wpi_settings[ 'use_custom_templates' ] != 'yes' ) {
      return false;
    }

    if ( file_exists( $wpi_settings[ 'frontend_template_path' ] . "$template" ) ) {
      return true;
    }

    return false;
  }

  /**
   * Determine WPI front-end template path
   *
   * @global array $wpi_settings
   * @return type
   */
  function template_path() {
    global $wpi_settings;
    $use_custom_templates = false;

    if ( file_exists( STYLESHEETPATH . "/wpi/" ) ) {
      return STYLESHEETPATH . "/wpi/";
    }
  }

  /**
   * Display invoice status formatted for back-end
   *
   * @param type $invoice_id
   */
  static function get_status( $invoice_id ) {

    $this_invoice = new WPI_Invoice();
    $this_invoice->load_invoice( "id=$invoice_id" );

    if ( is_array( $this_invoice->data[ 'log' ] ) ) {
      foreach ( array_reverse( $this_invoice->data[ 'log' ] ) as $event ) {

        if ( empty( $event[ 'text' ] ) ) {
          continue;
        }

        ?>
        <tr
          class="wpi_event_<?php echo $event[ 'action' ]; ?> <?php if ( $event[ 'action' ] == 'add_charge' || $event[ 'action' ] == 'do_adjustment' )
            echo "wpi_not_for_recurring"; ?>">
          <th><?php echo date( get_option( 'time_format' ) . ' ' . get_option( 'date_format' ), $event[ 'time' ] + get_option( 'gmt_offset' ) * 60 * 60 ); ?> </th>
          <td><?php echo $event[ 'text' ]; ?></td>
        </tr>
      <?php
      }
    } else {
      ?>
      <tr class="wpi_event_error">
        <th colspan="2">No log entries.</th>
      </tr>

    <?php
    }
  }

  /**
   * Render itemized charges list
   *
   * @param int $post_id
   */
  static function get_charges( $post_id ) {

    $charges_list = get_post_meta( $post_id, 'itemized_charges', true );

    $result = '';

    ob_start();

    if ( !empty( $charges_list ) ) {
      foreach ( $charges_list as $key => $value ) {
        ?>
        <li class="wp_invoice_itemized_charge_row clearfix" id="wp_invoice_itemized_charge_row_<?php echo $key; ?>">
          <span class="id hidden"><?php echo $key; ?></span>

          <div class="flexible_width_holder">
            <div class="flexible_width_holder_content">
              <span class="row_delete">&nbsp;</span>
              <input class="item_name input_field" name="wpi_invoice[itemized_charges][<?php echo $key; ?>][name]"
                     value="<?php echo stripslashes( $value[ 'name' ] ); ?>"/>
            </div>
          </div>

          <span class="fixed_width_holder">
            <span class="row_amount">
              <input autocomplete="off" value="<?php echo stripslashes( $value[ 'amount' ] ); ?>"
                     name="wpi_invoice[itemized_charges][<?php echo $key; ?>][amount]"
                     id="amount_item_<?php echo $key; ?>" class="item_amount input_field">
            </span>
            <span class="row_charge_tax">
              <input autocomplete="off" value="<?php echo stripslashes( $value[ 'tax' ] ); ?>"
                     name="wpi_invoice[itemized_charges][<?php echo $key; ?>][tax]"
                     id="charge_tax_item_<?php echo $key; ?>" class="item_charge_tax input_field">
            </span>
            <span class="row_total" id="total_item_<?php echo $key; ?>"><?php echo $value[ 'after_tax' ]; ?></span>
          </span>

        </li>
      <?php
      }

      $result .= ob_get_contents();
      ob_end_clean();
    }

    echo $result;
  }

  /**
   * Returns highest invoice ID
   *
   * 3.08.4 - fix added to proper counting of max value
   *
   * @global object $wpdb
   * @return longint
   * @author korotkov@ud
   * @since 3.08.4
   */
  static function get_highest_custom_id() {
    global $wpdb;

    $max_custom_id = $wpdb->get_results( "
      SELECT max( cast( `meta_value` AS UNSIGNED ) ) AS max
      FROM `{$wpdb->postmeta}`
      WHERE `meta_key` = 'custom_id'
    ", ARRAY_A );

    return !empty( $max_custom_id[ 0 ][ 'max' ] ) ? $max_custom_id[ 0 ][ 'max' ] : false;
  }

  /**
   * Removes empty values from array
   *
   * @param array $array
   *
   * @return array
   * @author korotkov@ud
   */
  function remove_blank_values( $array ) {
    if ( !is_array( $array ) ) {
      return false;
    }
    foreach ( $array as $key => $value ) {
      if ( !empty( $value ) ) {
        $return[ $key ] = $value;
      }
    }
    return $return;
  }

  /**
   * Called by profile_update action/hook
   * Used to save profile settings for WP Users.
   *
   * @param int $user_id User ID
   * @param object $old_user_data old value.
   *
   * @return bool True on successful update, false on failure.
   */
  static function save_update_profile( $user_id, $old_user_data ) {
    global $wpi_settings;

    if ( empty( $user_id ) || $user_id == 0 ) {
      return false;
    }

    $custom_user_information = apply_filters( 'wpi_user_information', $wpi_settings[ 'user_meta' ][ 'custom' ] );
    $user_information = array_merge( $wpi_settings[ 'user_meta' ][ 'required' ], $custom_user_information );

    //** On Adding/Editing Invoice user data exists in ['wpi_invoice']['user_data'] */
    $data = !empty( $_POST[ 'wpi_invoice' ][ 'user_data' ] ) ? $_POST[ 'wpi_invoice' ][ 'user_data' ] : $_POST;

    if ( !is_array( $data ) ) {
      return false;
    }

    foreach ( $user_information as $field_id => $field_name ) {
      if ( isset( $data[ $field_id ] ) ) {
        update_user_meta( $user_id, $field_id, $data[ $field_id ] );
      }
    }
  }

  /**
   * Called by profile_update action/hook
   * If user_email changed then it updates user invoices with new user_email
   *
   * @param type $user_id
   * @param type $old_user_data
   *
   * @return boolean
   * @author odokienko@UD
   */
  static function protect_user_invoices( $user_id, $old_user_data ) {
    global $wpdb;

    if ( empty( $user_id ) || $user_id == 0 ) {
      return false;
    }

    $userdata = get_userdata( $user_id );

    if ( $userdata->user_email != $old_user_data->user_email ) {

      $wpdb->query( "
        UPDATE {$wpdb->postmeta} postmeta, {$wpdb->posts} posts
        SET postmeta.meta_value = '" . $userdata->user_email . "'
        WHERE posts.ID = postmeta.post_id
          AND postmeta.meta_key = 'user_email'
          AND postmeta.meta_value = '" . $old_user_data->user_email . "'
          AND posts.post_type = 'wpi_object'
      " );

    }

  }

  /**
   * Called by template_redirect to validate whether an invoice should be displayed
   */
  static function validate_page_hash( $md5_invoice_id ) {
    global $wpdb, $wpi_settings, $post, $invoice_id;

    $invoice_id = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='invoice_id' AND MD5(meta_value) = '{$md5_invoice_id}'" );

    if ( !$invoice_id ) {
      return false;
    }

    if ( $wpi_settings[ 'web_invoice_page' ] != $post->ID ) {
      return false;
    }

    // Verify HTTPS.  If its enforced, but not active, we reload page, and do the process again

    if ( !function_exists( 'wp_https_redirect' ) ) {
      session_start();
      if ( !is_ssl() ) {
        if ( $wpi_settings[ 'force_https' ] == 'true' && !is_ssl() ) {
          $_SESSION[ 'https' ] = 1;
          header( "Location: https://" . $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'REQUEST_URI' ] );
          @session_destroy();
          exit;
        } else {
          if ( session_id() != '' )
            @session_destroy();
        }
      }
      //Added to see how the invoice looks once it is created...
      if ( $wpi_settings[ 'force_https' ] == 'false' ) {
        if ( session_id() != '' )
          @session_destroy();
        // Nothing should be done here, this function is simply for validating.
        // If we got this far, means invoice_id and page are validated, and HTTPS is NOT enforced
        return true;
        //print $_SERVER['SERVER_NAME']; print $_SERVER['REQUEST_URI']; die;
        //header("Location: http://" . $_SERVER['SERVER_NAME']'/wp-login.php');
        //header("Location: http://localhost/wordpress/wp-login.php");
        //exit;
      }
    }

    // 5. Validation passed
    return true;
  }

  /**
  Displayed how many days it has been since a certain date.

   */
  function days_since( $date1, $return_number = false ) {
    if ( empty( $date1 ) )
      return "";

    if ( is_array( $date1 ) )
      $date1 = $date1[ year ] . "-" . $date1[ month ] . "-" . $date1[ day ];

    $date2 = date( "Y-m-d" );
    $date1 = date( "Y-m-d", strtotime( $date1 ) );

    // determine if future or past
    if ( strtotime( $date2 ) < strtotime( $date1 ) )
      $future = true;

    $difference = abs( strtotime( $date2 ) - strtotime( $date1 ) );
    $days = round( ( ( ( $difference / 60 ) / 60 ) / 24 ), 0 );

    if ( $return_number )
      return $days;

    if ( $days == 0 ) {
      return __( 'Today', ud_get_wp_invoice()->domain );
    } elseif ( $days == 1 ) {
      return ( $future ? __( " Tomorrow ", ud_get_wp_invoice()->domain ) : __( " Yesterday ", ud_get_wp_invoice()->domain ) );
    } elseif ( $days > 1 && $days <= 6 ) {
      return ( $future ? __sprintf( __( " in %s days ", ud_get_wp_invoice()->domain ), $days ) : " " . $days . " " . __( "days ago", ud_get_wp_invoice()->domain ) );
    } elseif ( $days > 6 ) {
      return date( get_option( 'date_format' ), strtotime( $date1 ) );
    }
  }

  /**
   * Render money in format.
   *
   * @refactoring korotkov@ud
   * @global array $wpi_settings
   *
   * @param type $number
   *
   * @return type
   */
  static function money_format( $number ) {
    return wp_invoice_currency_format( $number );
  }

  /**
   * We use this to merge two arrays.
   * Used when loading default billing data, and then being updated by invoice-specific data
   * Awesome function from http://us2.php.net/manual/en/function.array-merge-recursive.php
   * @return type
   */
  static function &array_merge_recursive_distinct() {
    $aArrays = func_get_args();
    $aMerged = $aArrays[ 0 ];

    for ( $i = 1; $i < count( $aArrays ); $i++ ) {
      if ( is_array( $aArrays[ $i ] ) ) {
        foreach ( $aArrays[ $i ] as $key => $val ) {
          if ( is_array( $aArrays[ $i ][ $key ] ) ) {
            $aMerged[ $key ] = ( isset( $aMerged[ $key ] ) && is_array( $aMerged[ $key ] ) ) ? WPI_Functions::array_merge_recursive_distinct( $aMerged[ $key ], $aArrays[ $i ][ $key ] ) : $aArrays[ $i ][ $key ];
          } else {
            $aMerged[ $key ] = $val;
          }
        }
      }
    }

    return $aMerged;
  }

  /**
   * Alternative to print_r
   * @param type $data
   * @param type $echo
   * @return string
   */
  static function pretty_print_r( $data, $echo = false ) {
    // Clean $_REQUEST array
    $result = '';
    if ( $data == $_REQUEST ) {
      foreach ( $data as $key => $value ) {
        $pattern = "/PHPSESSID|ui-tab/";
        if ( preg_match( $pattern, $key ) ) {
          unset( $data[ $key ] );
        }
      }
    }
    if ( is_array( $data ) ) { //If the given variable is an array, print using the print_r function.
      $result .= "<pre class='wpi_class_pre'>\n";
      $result .= print_r( $data, true );
      $result .= "</pre>";
    } elseif ( is_object( $data ) ) {
      $result .= "<pre>\n";
      var_dump( $data, true );
      $result .= "</pre>";
    } else {
      $result .= "=========&gt; ";
      $result .= var_dump( $data, true );
      $result .= " &lt;=========";
    }
    if ( $echo == false ) {
      return $result;
    }
    $echo;
  }

  /**
   * Settings action
   */
  static function settings_action() {

    if ( isset( $_REQUEST[ 'wpi_settings' ] ) ) {

      if ( $_REQUEST[ 'page' ] == 'wpi_page_settings' ) {
        unset( $_REQUEST );
        wp_redirect( admin_url( "admin.php?page=wpi_page_settings&message=updated" ) );
        exit;
      }

    }

  }

  /**
   * @param $invoice_data
   */
  static function select_publish_time( $invoice_data ) {
    global $wp_locale;

    if ( empty($invoice_data['post_status']) ) {
      $edit = false;
    } else {
      $edit = !(in_array($invoice_data['post_status'], array('paid', 'pending')) && (!$invoice_data['post_date_gmt'] || '0000-00-00 00:00:00' == $invoice_data['post_date_gmt']));
      $post_date = $invoice_data['post_date'];
    }

    $time_adj = current_time('timestamp');
    $jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
    $mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
    $aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
    $hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
    $mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
    $ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

    $month = '<label><span class="screen-reader-text">' . __( 'Month' ) . '</span><select id="mm" name="wpi_invoice[publish_date_time][mm]">';
    for ( $i = 1; $i < 13; $i = $i +1 ) {
      $monthnum = zeroise($i, 2);
      $monthtext = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
      $month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected( $monthnum, $mm, false ) . '>';
      $month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $monthtext ) . "</option>\n";
    }
    $month .= '</select></label>';

    $day = '<label><span class="screen-reader-text">' . __( 'Day' ) . '</span><input type="text" id="jj" name="wpi_invoice[publish_date_time][jj]" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" /></label>';
    $year = '<label><span class="screen-reader-text">' . __( 'Year' ) . '</span><input type="text" id="aa" name="wpi_invoice[publish_date_time][aa]" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" /></label>';
    $hour = '<label><span class="screen-reader-text">' . __( 'Hour' ) . '</span><input type="text" id="hh" name="wpi_invoice[publish_date_time][hh]" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" /></label>';
    $minute = '<label><span class="screen-reader-text">' . __( 'Minute' ) . '</span><input type="text" id="mn" name="wpi_invoice[publish_date_time][mn]" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" /></label>';

    echo '<div class="timestamp-wrap">';
    /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
    printf( __( '%1$s %2$s, %3$s @ %4$s:%5$s' ), $month, $day, $year, $hour, $minute );

    echo '</div><input type="hidden" id="ss" name="wpi_invoice[publish_date_time][ss]" value="' . $ss . '" />';

  }

  /**
   * Handles saving and updating
   * Can also handle AJAX save/update function
   *
   * @param type $invoice
   * @param type $args
   *
   * @return boolean
   */
  static function save_invoice( $invoice, $args = '' ) {

    //** Set function additional params */
    $defaults = array(
      'type' => 'default'
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    if ( $type != 'import' ) {
      if ( !wp_verify_nonce( $_REQUEST[ 'nonce' ], 'wpi-update-invoice' ) ) {
        die( 'Security check' );
      }
    }

    //** Init New Invoice object from passed variables */
    $ni = new WPI_Invoice();

    //** ID */
    $ni->set( array( 'ID' => $invoice[ 'ID' ] ) );

    //** invoice_id */
    $ni->set( array( 'invoice_id' => $invoice[ 'invoice_id' ] ) );

    //** subject */
    $ni->set( array( 'subject' => $invoice[ 'subject' ] ) );

    //** description */
    $ni->set( array( 'description' => $invoice[ 'description' ] ) );

    //** deposit */
    if ( !empty($invoice[ 'deposit' ]) && ( $invoice[ 'deposit' ] == 'on' || $invoice[ 'deposit' ] == 'true' ) ) {
      $ni->set( array( 'deposit_amount' => $invoice[ 'deposit_amount' ] ) );
    } else {
      $ni->set( array( 'deposit_amount' => 0 ) );
    }

    //** Due date */
    $ni->set( array( 'due_date_year' => $invoice[ 'due_date_year' ] ) );
    $ni->set( array( 'due_date_month' => $invoice[ 'due_date_month' ] ) );
    $ni->set( array( 'due_date_day' => $invoice[ 'due_date_day' ] ) );

    //** Currency */
    $ni->set( array( 'default_currency_code' => $invoice[ 'default_currency_code' ] ) );

    //** Terms? */
    if ( !empty( $invoice[ 'meta' ][ 'terms' ] ) ) {
      $ni->set( array( 'terms' => $invoice[ 'meta' ][ 'terms' ] ) );
    }

    //** Tax */
    $ni->set( array( 'tax' => $invoice[ 'meta' ][ 'tax' ] ) );

    //** Custom ID */
    $ni->set( array( 'custom_id' => $invoice[ 'meta' ][ 'custom_id' ] ) );

    //** type is 'invoice' by default */
    $invoice_type = 'invoice';

    //** If $invoice object has type definition then use it */
    if ( !empty( $invoice[ 'type' ] ) ) {
      $invoice_type = $invoice[ 'type' ];
    }

    //** Save status of invoice (quote or not quote) */
    if ( isset ( $invoice[ 'quote' ] ) ) {
      if ( $invoice[ 'quote' ] == "on" ) {
        $ni->set( array( 'status' => 'quote' ) );
        $ni->set( array( 'is_quote' => 'true' ) );
        $invoice_type = 'quote';
      } else {
        $ni->set( array( 'status' => 'null' ) );
      }
    }

    //** But if recurring settings are defined then invoice type should be recurring */
    if ( !empty( $invoice['recurring'] ) && $invoice[ 'recurring' ][ 'active' ] == 'on' ) {
      $ni->create_schedule( $invoice['recurring'] );
      $invoice_type = 'recurring';
    }

    //** Finally set invoice type */
    $ni->set( array( 'type' => $invoice_type ) );

    //** Set invoice status */
    $status = ( !empty( $invoice[ 'post_status' ] ) ? $invoice[ 'post_status' ] : 'active' );
    $ni->set( array( 'post_status' => $status ) );

    //** Add discounts if exist */
    if ( is_array( $invoice[ 'meta' ][ 'discount' ] ) ) {
      foreach ( $invoice[ 'meta' ][ 'discount' ] as $discount ) {
        if ( !empty( $discount[ 'name' ] ) && !empty( $discount[ 'amount' ] ) ) {
          $ni->add_discount( array(
            'name' => $discount[ 'name' ],
            'type' => $discount[ 'type' ],
            'amount' => $discount[ 'amount' ]
          ) );
        }
      }
    }

    //** Ability to change payment method */
    if ( !empty( $invoice[ 'client_change_payment_method' ] ) ) {
      $ni->set( array( 'client_change_payment_method' => $invoice[ 'client_change_payment_method' ] ) );
    }

    //** Ability to turn off all payment methods and turn on manual that way */
    if ( !empty( $invoice[ 'use_manual_payment' ] ) ) {
      $ni->set( array( 'use_manual_payment' => $invoice[ 'use_manual_payment' ] ) );
    }

    //** Default payment method */
    $ni->set( array( 'default_payment_method' => $invoice[ 'default_payment_method' ] ) );

    //** Tax method */
    $ni->set( array( 'tax_method' => $invoice[ 'tax_method' ] ) );

    //** Manually set billing settings due to the complexity of the hierarchy */
    $ni->data[ 'billing' ] = !empty( $invoice[ 'billing' ] ) ? $invoice[ 'billing' ] : array();

    //** Add line items */
    foreach ( $invoice[ 'itemized_list' ] as $line_item ) {
      $ni->line_item( apply_filters( 'wpi_save_invoice_itemized_list_item', array(
        'name' => $line_item[ 'name' ],
        'description' => $line_item[ 'description' ],
        'quantity' => $line_item[ 'quantity' ],
        'price' => $line_item[ 'price' ],
        'tax_rate' => $line_item[ 'tax' ]
      ), $line_item ) );
    }

    //** Add line items for charges */
    if ( !empty( $invoice[ 'itemized_charges' ] ) ) {
      foreach ( $invoice[ 'itemized_charges' ] as $charge_item ) {
        $ni->line_charge(
          array(
            'name' => $charge_item[ 'name' ],
            'amount' => $charge_item[ 'amount' ],
            'tax' => $charge_item[ 'tax' ]
          )
        );
      }
    }

    /**
     * Save Invoice Object to DB and update user
     * (trimming is a precaution because it could cause problems in inserted in DB w/ whitespace on end)
     */
    $ni->set( array(
      'user_email' => trim( $invoice[ 'user_data' ][ 'user_email' ] )
    ) );

    if ( $type != 'import' ) {
      WPI_Functions::update_user( $invoice[ 'user_data' ] );
    }

    $ni = apply_filters( 'wpi_invoice_pre_save', $ni, $invoice );

    $invoice_id = $ni->save_invoice();
    if ( $invoice_id ) {
      do_action( 'wpi_invoice_saved', $invoice, $invoice_id );
      return $invoice_id;
    } else {
      return false;
    }
  }

  /**
   * It's a callback function. It calls on a option "wpi_options" changes
   *
   * @param mixed $new_value
   * @param mixed $old_value (do not used)
   *
   * @return $new_value
   * @author odokienko@UD
   */
  static function pre_update_option_wpi_options( $new_value, $old_value ) {
    global $wpdb;

    $default_currency_code = !empty($new_value[ 'currency' ][ 'default_currency_code' ])?$new_value[ 'currency' ][ 'default_currency_code' ]:'';
    $protected_currencies = array();

    //** check for curency with default_currency_code */
    $protected_currencies[ ] = $default_currency_code;

    //** check for currencies that are already used in invoices */
    $results = $wpdb->get_results( "
      SELECT DISTINCT `{$wpdb->postmeta}`.meta_value
      FROM `{$wpdb->posts}`
      JOIN `{$wpdb->postmeta}` ON ( `{$wpdb->posts}`.id = `{$wpdb->postmeta}`.post_id )
      WHERE `{$wpdb->posts}`.post_type = 'wpi_object'
      AND `{$wpdb->postmeta}`.meta_key = 'default_currency_code'
    " );
    foreach ( $results as $curr_row ) {
      $protected_currencies[ ] = $curr_row->meta_value;
    }

    foreach ( $protected_currencies as $curr ) {
      if ( empty( $new_value[ 'currency' ][ 'types' ][ $curr ] ) ) {
        $new_value[ 'currency' ][ 'types' ][ $curr ] = $old_value[ 'currency' ][ 'types' ][ $curr ];
      }
      if ( empty( $new_value[ 'currency' ][ 'symbol' ][ $curr ] ) ) {
        $new_value[ 'currency' ][ 'symbol' ][ $curr ] = $old_value[ 'currency' ][ 'symbol' ][ $curr ];
      }
    }

    foreach ( $new_value[ 'currency' ][ 'symbol' ] as &$symbol ) {
      $symbol = base64_encode( $symbol );
    }

    /**
     * and checking the option "use_wp_crm_to_send_notifications". If it is set to true
     * than retrieves an option 'wp_crm_settings' and check it for containing
     * a default WPI notification templates and add them if necessary
     */
    if ( !empty( $new_value[ 'use_wp_crm_to_send_notifications' ] ) ) {

      $wp_crm_settings = get_option( 'wp_crm_settings' );
      $update_needed = false;
      if ( empty( $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ] ) ) {
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ][ 'subject' ] = __( 'Invoice #[invoice_id] has been paid', ud_get_wp_invoice()->domain );
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ][ 'to' ] = '[user_email]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ][ 'send_from' ] = '[from]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ][ 'send_from_name' ] = '[business_name]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ][ 'message' ] = __( "Dear [user_name],\n[business_name] has received your payment for the invoice.\n\nYou can overview invoice status and payment history by clicking this link:\n[permalink]\n\nThank you very much for your patronage.\n\nBest regards,\n[business_name] ([from])", ud_get_wp_invoice()->domain );
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_thank_you_email' ][ 'fire_on_action' ] = array( 'wpi_send_thank_you_email' );
        $update_needed = true;
      }
      if ( empty( $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ] ) ) {
        $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ][ 'subject' ] = __( 'Invoice #[invoice_id] has been paid by [user_name]', ud_get_wp_invoice()->domain );
        $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ][ 'to' ] = '[admin_email]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ][ 'send_from' ] = '[from]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ][ 'send_from_name' ] = '[business_name]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ][ 'message' ] = __( "[user_name] has paid invoice #[invoice_id].\n[invoice_title]\nTotal payments: [default_currency_code] [total_payments] of [default_currency_code] [total].\n\nYou can overview invoice status and payment history by clicking this link:\n[permalink]\n\nUser information:\n\nID: [user_id]\nName: [user_name]\nEmail: [user_email]\n\n--------------------\n[site]", ud_get_wp_invoice()->domain );
        $wp_crm_settings[ 'notifications' ][ 'wpi_cc_thank_you_email' ][ 'fire_on_action' ] = array( 'wpi_cc_thank_you_email' );
        $update_needed = true;
      }
      if ( empty( $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ] ) ) {
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ][ 'subject' ] = __( 'Invoice #[invoice_id] has been paid by [user_name]', ud_get_wp_invoice()->domain );
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ][ 'to' ] = '[creator_email]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ][ 'send_from' ] = '[from]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ][ 'send_from_name' ] = '[business_name]';
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ][ 'message' ] = __( "Dear [creator_name],\n[user_name] has paid invoice #[invoice_id].\n\n[invoice_title]\nTotal payments: [default_currency_code] [total_payments] of [default_currency_code] [total].\n\nYou can overview invoice status and payment history by clicking this link:\n[permalink]\n\nUser information:\n\nID: [user_id]\nName: [user_name]\nEmail: [user_email]\n\n--------------------\n[site]", ud_get_wp_invoice()->domain );
        $wp_crm_settings[ 'notifications' ][ 'wpi_send_invoice_creator_email' ][ 'fire_on_action' ] = array( 'wpi_send_invoice_creator_email' );
        $update_needed = true;
      }
      if ( $update_needed ) {
        update_option( 'wp_crm_settings', $wp_crm_settings );
      }
    }

    return $new_value;
  }

  /**
   * It's a callback function. It calls on a option "wpi_options" get_option
   *
   * @param mixed $new_value
   * @param mixed $old_value (do not used)
   *
   * @return $new_value
   * @author odokienko@UD
   */
  static function option_wpi_options( $value ) {

    if ( empty( $value[ 'currency' ][ 'symbol' ] ) ) return $value;

    foreach ( $value[ 'currency' ][ 'symbol' ] as $key => $symbol ) {
      $value[ 'currency' ][ 'symbol' ][ $key ] = base64_decode( $symbol );
    }
    return $value;
  }

  /**
   * Creates post type.
   *
   * Ran everytime.
   *
   * @since 3.0
   */
  function register_post_type() {
    global $wpi_settings;

    $wpi_settings[ 'statuses' ] = array();

    $labels = array(
      'name' => __( 'Invoices', ud_get_wp_invoice()->domain ),
      'singular_name' => __( 'Invoice', ud_get_wp_invoice()->domain ),
      'add_new' => __( 'Add New', ud_get_wp_invoice()->domain ),
      'add_new_item' => __( 'Add New Invoice', ud_get_wp_invoice()->domain ),
      'edit_item' => __( 'Edit Invoice', ud_get_wp_invoice()->domain ),
      'new_item' => __( 'New Invoice', ud_get_wp_invoice()->domain ),
      'view_item' => __( 'View Invoice', ud_get_wp_invoice()->domain ),
      'search_items' => __( 'Search Invoices', ud_get_wp_invoice()->domain ),
      'not_found' => __( 'No invoices found', ud_get_wp_invoice()->domain ),
      'not_found_in_trash' => __( 'No invoices found in Trash', ud_get_wp_invoice()->domain ),
      'parent_item_colon' => ''
    );

    // Register custom post types
    register_post_type( 'wpi_object', array(
      'labels' => $labels,
      'singular_label' => __( 'Invoice', ud_get_wp_invoice()->domain ),
      'public' => false,
      'show_ui' => false,
      '_builtin' => false,
      '_edit_link' => $wpi_settings[ 'links' ][ 'manage_invoice' ] . '&wpi[existing_invoice][invoice_id]=%d',
      'capability_type' => 'post',
      'hierarchical' => false,
      'supports' => array( 'title', 'editor', 'thumbnail' ),
      'menu_icon' => 'dashicons-money'
    ) );

    register_post_status( 'archived', array(
      'label' => _x( 'Archived', 'wpi_object' ),
      'public' => false,
      '_builtin' => false,
      'label_count' => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>' ),
    ) );
    $wpi_settings[ 'statuses' ][ ] = 'archived';

    register_post_status( 'active', array(
      'label' => _x( 'Active', 'wpi_object' ),
      'public' => false,
      '_builtin' => false,
      'label_count' => _n_noop( 'Due Invoices <span class="count">(%s)</span>', 'Due Invoices <span class="count">(%s)</span>' ),
    ) );
    $wpi_settings[ 'statuses' ][ ] = 'active';

    register_post_status( 'paid', array(
      'label' => _x( 'Paid', 'wpi_object' ),
      'public' => false,
      '_builtin' => false,
      'label_count' => _n_noop( 'Paid <span class="count">(%s)</span>', 'Paid <span class="count">(%s)</span>' ),
    ) );
    $wpi_settings[ 'statuses' ][ ] = 'paid';

    register_post_status( 'trash', array(
      'label' => _x( 'Trash', 'wpi_object' ),
      'public' => false,
      '_builtin' => false,
      'label_count' => _n_noop( 'Trash  <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>' ),
    ) );
    $wpi_settings[ 'statuses' ][ ] = 'trash';

    register_post_status( 'pending', array(
      'label' => _x( 'Pending', 'wpi_object' ),
      'public' => false,
      '_builtin' => false,
      'label_count' => _n_noop( 'Pending  <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>' ),
    ) );
    $wpi_settings[ 'statuses' ][ ] = 'pending';

    register_post_status( 'refund', array(
      'label' => _x( 'Refund', 'wpi_object' ),
      'public' => false,
      '_builtin' => false,
      'label_count' => _n_noop( 'Refund  <span class="count">(%s)</span>', 'Refund <span class="count">(%s)</span>' ),
    ) );
    $wpi_settings[ 'statuses' ][ ] = 'refund';

    do_action( 'wpi_register_object' );
  }

  /**
   * Creates WPI 3.0 Database Schema
   *
   * Creates
   *
   * @uses $wpdb
   * @since 3.0
   *
   */
  static function create_new_schema_tables() {
    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta( "CREATE TABLE {$wpdb->base_prefix}wpi_object_log (
      ID mediumint(9) NOT NULL auto_increment,
      blog_id mediumint(9) NOT NULL,
      object_id mediumint(9) NOT NULL,
      user_id mediumint(9) NOT NULL,
      attribute varchar(255) collate utf8_unicode_ci NOT NULL,
      action varchar(255) collate utf8_unicode_ci NOT NULL,
      value varchar(255) collate utf8_unicode_ci NOT NULL,
      text text collate utf8_unicode_ci NOT NULL,
      time bigint(11) NOT NULL default '0',
      UNIQUE KEY id (ID),
      KEY time (time),
      KEY object_id (object_id),
      KEY user_id (user_id),
      KEY event_type (action)
    ) " );
  }

  /**
   * This function loads our payment gateways
   *
   * @since 3.0
   */
  function load_gateways() {
    global $wpi_settings;

    $default_headers = array(
      'Name' => 'Name',
      'Version' => 'Version',
      'Description' => 'Description'
    );

    if ( !is_dir( ud_get_wp_invoice()->path( 'lib/gateways', 'dir' ) ) ) {
      return;
    }

    if ( $gateways_dir = opendir( ud_get_wp_invoice()->path( 'lib/gateways', 'dir' ) ) ) {

      if ( file_exists( ud_get_wp_invoice()->path( 'lib/gateways', 'dir' ) . "/index.php" ) ) {
        if ( WP_DEBUG ) {
          include_once( ud_get_wp_invoice()->path( 'lib/gateways', 'dir' ) . "/index.php" );
        } else {
          @include_once( ud_get_wp_invoice()->path( 'lib/gateways', 'dir' ) . "/index.php" );
        }
      }

      while ( false !== ( $file = readdir( $gateways_dir ) ) ) {
        if ( $file == 'index.php' )
          continue;

        $ex = explode( ".", $file );
        if ( end( $ex ) == 'php' ) {
          $slug = str_replace( array( '.php' ), '', $file );
          if ( substr( $slug, 0, 6 ) == "class_" ) {
            $t = explode( "class_", $slug );
            $slug = $t[ 1 ];
          }

          $plugin_data = @get_file_data( ud_get_wp_invoice()->path( 'lib/gateways/', 'dir' ) . $file, $default_headers, 'plugin' );
          $wpi_settings[ 'installed_gateways' ][ $slug ][ 'name' ] = $plugin_data[ 'Name' ];
          $wpi_settings[ 'installed_gateways' ][ $slug ][ 'version' ] = $plugin_data[ 'Version' ];
          $wpi_settings[ 'installed_gateways' ][ $slug ][ 'description' ] = $plugin_data[ 'Description' ];

          if ( WP_DEBUG ) {
            include_once( ud_get_wp_invoice()->path( 'lib/gateways/', 'dir' ) . $file );
          } else {
            @include_once( ud_get_wp_invoice()->path( 'lib/gateways/', 'dir' ) . $file );
          }

          // Disable plugin if class does not exists - file is empty
          if ( !class_exists( $slug ) ) {
            unset( $wpi_settings[ 'installed_gateways' ][ $slug ] );
          } else {
            /** Initialize the object, then update the billing permissions to show whats in the object */
            eval( "\$wpi_settings['installed_gateways']['" . $slug . "']['object'] = new " . $slug . "();" );
          }
        }
      }

      /** Sync our options */
      wpi_gateway_base::sync_billing_objects();
    }
  }

  /**
   * Run manually when a version mismatch is detected.
   *
   * Holds official current version designation.
   * Called in admin_init hook.
   *
   **/
  static function manual_activation() {

    $installed_ver = get_option( "wp_invoice_version" );
    $wpi_version = WP_INVOICE_VERSION_NUM;

    if ( @version_compare( $installed_ver, $wpi_version ) == '-1' ) {
      //** We are upgrading */

      //** Update option to latest version so this isn't run on next admin page load */
      update_option( "wp_invoice_version", $wpi_version );

      //** Try to create new schema tables */
      WPI_Functions::create_new_schema_tables();

    }

    return;

  }

  /**
   * Logs an action
   *
   * @since 3.0
   */
  static function log_event( $object_id, $attribute, $action, $value, $text = '', $time = false ) {
    global $wpdb, $current_user, $blog_id;

    if ( !$time ) {
      $time = time();
    }

    $wpdb->show_errors();

    $wpdb->insert( $wpdb->base_prefix . 'wpi_object_log', array(
      'object_id' => $object_id,
      'user_id' => $current_user->ID,
      'attribute' => $attribute,
      'action' => $action,
      'value' => $value,
      'text' => $text,
      'time' => $time,
      'blog_id' => $blog_id
    ) );

    if ( $wpdb->insert_id ) {
      return $wpdb->insert_id;
    } else {
      return false;
    }

  }

  /**
   * Detect browser
   *
   * @global bool $is_lynx
   * @global bool $is_gecko
   * @global bool $is_IE
   * @global bool $is_opera
   * @global bool $is_NS4
   * @global bool $is_safari
   * @global bool $is_chrome
   * @global bool $is_iphone
   *
   * @author korotkov@ud
   * @return array
   */
  static function browser() {
    global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

    if ( $is_lynx )
      $classes[ 'name' ] = 'lynx';
    elseif ( $is_gecko )
      $classes[ 'name' ] = 'gecko'; elseif ( $is_opera )
      $classes[ 'name' ] = 'opera'; elseif ( $is_NS4 )
      $classes[ 'name' ] = 'ns4'; elseif ( $is_safari )
      $classes[ 'name' ] = 'safari'; elseif ( $is_chrome )
      $classes[ 'name' ] = 'chrome'; elseif ( $is_IE ) {
      $classes[ 'name' ] = 'ie';
      if ( preg_match( '/MSIE ([0-9]+)([a-zA-Z0-9.]+)/', $_SERVER[ 'HTTP_USER_AGENT' ], $browser_version ) )
        $classes[ 'version' ] = $browser_version[ 1 ];
    } else {
      $classes[ 'name' ] = 'unknown';
    }
    if ( $is_iphone ) {
      $classes[ 'name' ] = 'iphone';
    }
    if ( stristr( $_SERVER[ 'HTTP_USER_AGENT' ], "mac" ) ) {
      $classes[ 'sys' ] = 'osx';
    } elseif ( stristr( $_SERVER[ 'HTTP_USER_AGENT' ], "linux" ) ) {
      $classes[ 'sys' ] = 'linux';
    } elseif ( stristr( $_SERVER[ 'HTTP_USER_AGENT' ], "windows" ) ) {
      $classes[ 'sys' ] = 'windows';
    }
    return $classes;
  }

  /**
   * Returns WP-CRM attributes list
   *
   * @global object $wp_crm
   * @return array
   * @author korotkov@ud
   */
  static function get_wpi_crm_attributes() {
    //** If WP-CRM not installed */
    if ( !class_exists( 'WP_CRM_Core' ) ) return;

    global $wp_crm;

    $attributes = array();
    if ( !empty( $wp_crm[ 'data_structure' ][ 'attributes' ] ) ) {
      foreach ( $wp_crm[ 'data_structure' ][ 'attributes' ] as $slug => $attribute ) {
        if ( !empty( $attribute[ 'wp_invoice' ] ) && $attribute[ 'wp_invoice' ] == 'true' ) {
          $attributes[ $slug ] = $attribute;
        }
      }
    }

    return $attributes;
  }

  /**
   * WP-CRM custom fields procces filter
   *
   * @param array $current_fields
   * @param string $name
   *
   * @return array
   * @author korotkov@ud
   */
  static function wpi_crm_custom_fields( $current_fields, $name ) {
    $attributes = self::get_wpi_crm_attributes();
    if ( empty( $attributes ) ) return $current_fields;

    global $invoice;
    


    foreach ( $attributes as $attr_key => $attr_value ) {
      switch ( $attr_value['input_type'] ) {
        case 'dropdown':
          
          $values = array();
          foreach( $attr_value['option_keys'] as $o_k => $o_v ) {
            $values[$o_v] = $attr_value['option_labels'][$o_k];
          }
          
          $current_fields[ 'customer_information' ][ $attr_key ] = array(
            'type' => 'select',
            'class' => 'dropdown-input',
            'name' => $name . '[' . $attr_key . ']',
            'label' => __( $attr_value[ 'title' ], ud_get_wp_invoice()->domain ),
            'values' => serialize($values),
            'value' => get_user_meta( $invoice['user_data']['ID'], $attr_key, 1 )
          );
          break;

		case 'checkbox':

          $values = array();
          foreach( $attr_value['option_keys'] as $o_k => $o_v ) {
            $values[$o_v] = $attr_value['option_labels'][$o_k];
          }

          $current_fields[ 'customer_information' ][ $attr_key ] = array(
            'type' => 'checkbox',
            'class' => 'checkbox-input',
            'name' => $name . '[' . $attr_key . ']',
            'label' => __( $attr_value[ 'title' ], ud_get_wp_invoice()->domain ),
            'values' => serialize($values),
            'value' => get_user_meta( $invoice['user_data']['ID'], $attr_key, 1 )
          );
          break;

		case 'textarea':
          $current_fields[ 'customer_information' ][ $attr_key ] = array(
            'type' => 'textarea',
            'class' => 'textarea-input',
            'name' => $name . '[' . $attr_key . ']',
            'label' => __( $attr_value[ 'title' ], ud_get_wp_invoice()->domain ),
            'value' => get_user_meta( $invoice['user_data']['ID'], $attr_key, 1 )
          );
          break;
        
    case 'recaptcha':
          $current_fields[ 'customer_information' ][ $attr_key ] = array(
            'type' => 'recaptcha',
            'class' => 'recaptcha-input',
            'name' => $name . '[' . $attr_key . ']',
            'label' => __( $attr_value[ 'title' ], ud_get_wp_invoice()->domain ),
            'value' => '',
          );
          break;
          
        default:
          $current_fields[ 'customer_information' ][ $attr_key ] = array(
            'type' => 'text',
            'class' => 'text-input',
            'name' => $name . '[' . $attr_key . ']',
            'label' => __( $attr_value[ 'title' ], ud_get_wp_invoice()->domain ),
            'value' => get_user_meta( $invoice['user_data']['ID'], $attr_key, 1 )
          );
          break;
      }
    }

    return $current_fields;
  }

  /**
   * Filter CRM actions list
   *
   * @param array $current
   *
   * @author odokienko@UD
   * @return array
   */
  static function wpi_crm_custom_notification( $current ) {

    foreach ( WPI_Core::$crm_notification_actions as $action_key => $action_name ) {
      $current[ $action_key ] = $action_name;
    }

    return $current;
  }

  /**
   * Create label for user activity stream attribute
   *
   * @version 1.0
   * @author odokienko@UD
   */
  static function wp_crm_entry_type_label( $attr, $entity ) {
    global $wp_crm;
    switch ( $attr ) {
      case "wpi_notification":
        $attr = __( "WP-Invoice Notification", ud_get_wp_invoice()->domain );
        break;
    }

    return $attr;
  }

  /**
   * Changing of Mail From for Notifications.
   *
   * @global array $wpi_settings
   * @return string
   */
  static function notification_mail_from() {
    global $wpi_settings;

    $email = empty( $wpi_settings[ 'mail_from_user_email' ] ) ? "wordpress@" . strtolower( $_SERVER[ 'SERVER_NAME' ] ) : $wpi_settings[ 'mail_from_user_email' ];

    return $email;
  }

  /**
   * Changing of Mail From Name for Notifications.
   *
   * @global array $wpi_settings
   * @return type
   */
  static function notification_mail_from_name() {
    global $wpi_settings;

    $sendername = empty( $wpi_settings[ 'mail_from_sender_name' ] ) ? "WordPress" : stripslashes( $wpi_settings[ 'mail_from_sender_name' ] );

    return $sendername;
  }

  /**
   * Delete invoice log by args
   *
   * @global object $wpdb
   * @global type $blog_id
   *
   * @param type $args
   *
   * @author korotkov@ud
   * @since 3.08.4
   */
  static function delete_invoice_log( $args ) {

    $defaults = array(
      'post_id' => 0
    );

    extract( wp_parse_args( $args, $defaults ) );

    if ( $post_id ) {
      global $wpdb, $blog_id;

      $wpdb->query( "
        DELETE
        FROM `{$wpdb->base_prefix}wpi_object_log`
        WHERE `object_id` = '{$post_id}'
          AND `blog_id` = '{$blog_id}'
      " );
    }

    return true;

  }

  /**
   *
   * @global type $current_user
   * @param type $invoice_data
   * @return boolean
   */
  static function user_is_invoice_recipient( $invoice_data ) {
    global $current_user;

    if ( !$current_user->ID ) return false;

    if ( $current_user->data->user_email != $invoice_data->data['user_email'] ) return false;

    return true;
  }

  /**
   * Override mail from if set in settings
   */
  static function maybe_override_mail_from() {
    global $wpi_settings;

    //** If we are going to change our Mail From */
    if ( !empty( $wpi_settings[ 'change_mail_from' ] ) && $wpi_settings[ 'change_mail_from' ] == 'true' ) {
      add_filter( 'wp_mail_from', array( 'WPI_Functions', 'notification_mail_from' ) );
      add_filter( 'wp_mail_from_name', array( 'WPI_Functions', 'notification_mail_from_name' ) );
    }
  }

  /**
   * If current user can send notifications
   * @return bool
   */
  static function current_user_can_send_notifications() {
    global $wpi_settings;
    return current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level']) );
  }

  /**
   * @param $template_id
   * @param $invoice_id
   * @return stdClass
   */
  static function preprocess_notification_template( $template_id, $invoice_id ) {
    global $wpi_settings, $invoice;

    require_once( ud_get_wp_invoice()->path( 'lib/class_template_functions.php', 'dir' ) );

    $invoice = get_invoice( wpi_invoice_id_to_post_id( $invoice_id ) );
    $currency_symbol = ( !empty( $wpi_settings[ 'currency' ][ 'symbol' ][ $invoice[ 'default_currency_code' ] ] ) ? $wpi_settings[ 'currency' ][ 'symbol' ][ $invoice[ 'default_currency_code' ] ] : "$" );
    $invoice_id = ( !empty( $invoice[ 'meta' ][ 'custom_id' ] ) ? $invoice[ 'meta' ][ 'custom_id' ] : $invoice[ 'invoice_id' ] );
    $custom_invoice_id = ( !empty( $invoice[ 'custom_id' ] ) ? $invoice[ 'custom_id' ] : $invoice[ 'invoice_id' ] );

    //** Get creator user data */
    $creator = get_userdata( $invoice[ 'post_author' ] );

    //** Due Date */
    $due_date = get_due_date( $invoice );
    $due_date = $due_date ? $due_date : __( 'Due date is not set', ud_get_wp_invoice()->domain );

    //** Type */
    $type = !empty( $invoice[ 'type' ] ) ? $invoice[ 'type' ] : __( 'invoice', ud_get_wp_invoice()->domain );
    $type = $type == 'recurring' ? $type . ' ' . __( 'invoice', ud_get_wp_invoice()->domain ) : $type;

    //** Load Templates */
    $template_array = apply_filters( 'wpi_email_templates', $wpi_settings[ 'notification' ] );

    $ary[ 'NotificationContent' ] = $template_array[ $template_id ][ 'content' ];

    //**
    // Tags which can be used in Content of notification email
    //*/
    //** Invoice ID */
    $ary[ 'NotificationContent' ] = str_replace( "%invoice_id%", $invoice_id, $ary[ 'NotificationContent' ] );

    //** Custom Invoice ID */
    $ary[ 'NotificationContent' ] = str_replace( "%custom_invoice_id%", $custom_invoice_id, $ary[ 'NotificationContent' ] );

    //** Format description */
    $desc = ( !empty( $invoice[ 'post_content' ] ) ? strip_tags( $invoice[ 'post_content' ] ) : __( "No description given.", ud_get_wp_invoice()->domain ) );
    $ary[ 'NotificationContent' ] = str_replace( "%description%", $desc, $ary[ 'NotificationContent' ] );

    //** Recipient name */
    $ary[ 'NotificationContent' ] = str_replace( "%recipient%", recipients_name( array( 'return' => true ) ), $ary[ 'NotificationContent' ] );

    //** Invoice link */
    $ary[ 'NotificationContent' ] = str_replace( "%link%", get_invoice_permalink( $invoice[ 'invoice_id' ] ), $ary[ 'NotificationContent' ] );

    //** Invoice balance */
    $ary[ 'NotificationContent' ] = str_replace( "%amount%", $currency_symbol . wp_invoice_currency_format( $invoice[ 'net' ] ), $ary[ 'NotificationContent' ] );

    //** Invoice subject/title */
    $ary[ 'NotificationContent' ] = str_replace( "%subject%", $invoice[ 'post_title' ], $ary[ 'NotificationContent' ] );

    //** Business name according to business settings */
    $ary[ 'NotificationContent' ] = str_replace( "%business_name%", $wpi_settings[ 'business_name' ], $ary[ 'NotificationContent' ] );

    //** Business email according to business settings */
    $ary[ 'NotificationContent' ] = str_replace( "%business_email%", $wpi_settings[ 'email_address' ], $ary[ 'NotificationContent' ] );

    //** Invoice creator name */
    $ary[ 'NotificationContent' ] = str_replace( "%creator_name%", $creator->display_name, $ary[ 'NotificationContent' ] );

    //** Invoice creator email */
    $ary[ 'NotificationContent' ] = str_replace( "%creator_email%", $creator->user_email, $ary[ 'NotificationContent' ] );

    //** Invoice Due Date */
    $ary[ 'NotificationContent' ] = str_replace( "%due_date%", $due_date, $ary[ 'NotificationContent' ] );

    //** Invoice type */
    $ary[ 'NotificationContent' ] = str_replace( "%type%", $type, $ary[ 'NotificationContent' ] );

    $ary[ 'NotificationSubject' ] = $template_array[ $template_id ][ 'subject' ];

    //**
    // Tags which can be used in Subject of notification email
    //*/

    //** Business name according to business settings */
    $ary[ 'NotificationSubject' ] = str_replace( "%business_name%", $wpi_settings[ 'business_name' ], $ary[ 'NotificationSubject' ] );

    //** Invoice link */
    $ary[ 'NotificationSubject' ] = str_replace( "%link%", get_invoice_permalink( $invoice[ 'invoice_id' ] ), $ary[ 'NotificationSubject' ] );

    //** Format description */
    $ary[ 'NotificationSubject' ] = str_replace( "%description%", $desc, $ary[ 'NotificationSubject' ] );

    //** Business email according to business settings */
    $ary[ 'NotificationSubject' ] = str_replace( "%business_email%", $wpi_settings[ 'email_address' ], $ary[ 'NotificationSubject' ] );

    //** Invoice creator name */
    $ary[ 'NotificationSubject' ] = str_replace( "%creator_name%", $creator->display_name, $ary[ 'NotificationSubject' ] );

    //** Invoice creator email */
    $ary[ 'NotificationSubject' ] = str_replace( "%creator_email%", $creator->user_email, $ary[ 'NotificationSubject' ] );

    //** Invoice Due Date */
    $ary[ 'NotificationSubject' ] = str_replace( "%due_date%", $due_date, $ary[ 'NotificationSubject' ] );

    //** Invoice type */
    $ary[ 'NotificationSubject' ] = str_replace( "%type%", $type, $ary[ 'NotificationSubject' ] );

    //** Invoice ID */
    $ary[ 'NotificationSubject' ] = str_replace( "%invoice_id%", $invoice_id, $ary[ 'NotificationSubject' ] );

    //** Custom Invoice ID */
    $ary[ 'NotificationSubject' ] = str_replace( "%custom_invoice_id%", $custom_invoice_id, $ary[ 'NotificationSubject' ] );

    //** Recipients name */
    $ary[ 'NotificationSubject' ] = str_replace( "%recipient%", $invoice[ 'user_data' ][ 'display_name' ], $ary[ 'NotificationSubject' ] );

    //** Invoice balance */
    $ary[ 'NotificationSubject' ] = str_replace( "%amount%", $invoice[ 'net' ], $ary[ 'NotificationSubject' ] );

    //** Invoice subject/title */
    $ary[ 'NotificationSubject' ] = str_replace( "%subject%", $invoice[ 'post_title' ], $ary[ 'NotificationSubject' ] );

    $return = new stdClass();
    $return->ary = $ary;
    $return->invoice = $invoice;

    return $return;
  }
}

/**
 * Mark invoice as Paid
 *
 * @param int $invoice_id
 * @param bool $check_balance
 *
 * @return bool
 * @author korotkov@ud
 */
function wp_invoice_mark_as_paid( $invoice_id, $check_balance = false ) {
  if ( $check_balance ) {
    if ( wpi_is_full_paid_invoice( $invoice_id ) ) {
      $post_id = wpi_invoice_id_to_post_id( $invoice_id );
      wp_update_post(
        array(
          'ID' => $post_id,
          'post_status' => 'paid'
        )
      );
      WPI_Functions::log_event( $post_id, 'invoice', 'update', '', __( 'Payment status: Complete', ud_get_wp_invoice()->domain ) );
      return true;
    } else {
      $post_id = wpi_invoice_id_to_post_id( $invoice_id );
      wp_update_post(
        array(
          'ID' => $post_id,
          'post_status' => 'active'
        )
      );
      return true;
    }
  } else {
    $post_id = wpi_invoice_id_to_post_id( $invoice_id );
    wp_update_post(
      array(
        'ID' => $post_id,
        'post_status' => 'paid'
      )
    );
    WPI_Functions::log_event( $post_id, 'invoice', 'update', '', __( 'Payment status: Complete', ud_get_wp_invoice()->domain ) );
    return true;
  }
}

/**
 * Mark invoice as Pending (for PayPal IPN)
 *
 * @param int $invoice_id
 *
 * @author korotkov@ud
 */
function wp_invoice_mark_as_pending( $invoice_id ) {
  $post_id = wpi_invoice_id_to_post_id( $invoice_id );

  wp_update_post(
    array(
      'ID' => $post_id,
      'post_status' => 'pending'
    )
  );

  /** Mark invoice as processed by IPN (used for trashing abandoned SPC transactions) */
  update_post_meta( $post_id, 'processed_by_ipn', 'true' );
  WPI_Functions::log_event( $post_id, 'invoice', 'update', '', __( 'Pending', ud_get_wp_invoice()->domain ) );
}

/**
 * Determine if invoice is paid full
 *
 * @global object $wpdb
 *
 * @param int $invoice_id
 *
 * @return bool
 * @author korotkov@ud
 */
function wpi_is_full_paid_invoice( $invoice_id ) {
  global $wpdb;

  $invoice_obj = new WPI_Invoice();
  $invoice_obj->load_invoice( "id={$invoice_id}" );

  $object_id = wpi_invoice_id_to_post_id( $invoice_id );
  $payment_history = $wpdb->get_results( "SELECT *
    FROM {$wpdb->base_prefix}wpi_object_log
    WHERE object_id = '{$object_id}'
      AND ( action = 'add_payment' OR action = 'refund' OR action = 'do_adjustment' )", ARRAY_A );
  $paid_amount = 0;

  foreach ( $payment_history as $payment ) {
    if ( $payment['action'] == 'add_payment' )
      $paid_amount += abs( $payment[ 'value' ] );
    if ( $payment['action'] == 'do_adjustment' )
      $paid_amount += $payment[ 'value' ];
    if ( $payment['action'] == 'refund' )
      $paid_amount -= $payment[ 'value' ];
  }

  return $paid_amount >= ( $invoice_obj->data[ 'subtotal' ] - $invoice_obj->data[ 'total_discount' ] );
}

/**
 * Formates amount
 *
 * @param number $amount
 *
 * @return string
 */
function wp_invoice_currency_format( $amount ) {
  global $wpi_settings;

  $thousands_separator_symbol = !isset( $wpi_settings[ 'thousands_separator_symbol' ] ) ? ',' : ( $wpi_settings[ 'thousands_separator_symbol' ] == '0' ? '' : $wpi_settings[ 'thousands_separator_symbol' ] );
  $decimal_separator_symbol = !isset( $wpi_settings[ 'decimal_separator_symbol' ] ) ? '.' : ( $wpi_settings[ 'decimal_separator_symbol' ] == '0' ? '' : $wpi_settings[ 'decimal_separator_symbol' ] );

  if ( $amount ) {
    return number_format( $amount, 2, $decimal_separator_symbol, $thousands_separator_symbol );
  } else {
    return $amount;
  }
}

/**
 * Emails user after payment is done
 *
 * @param array $invoice
 *
 * @author korotkov@ud
 * @refactoring odokienko@UD
 */
function wp_invoice_send_email_receipt( $invoice, $notification_data ) {
  global $wpi_settings;

  $subject = sprintf( __( "Invoice #%s has been paid", ud_get_wp_invoice()->domain ), $notification_data[ 'invoice_id' ] );
  $headers = array(
    "From: {$notification_data['business_name']} <{$notification_data['from']}>\r\n",
    "Content-Type: text/html"
  );
  $message = sprintf(
    __( "Dear %1s,<br>%2s has received your payment for the invoice.<br><br>You can overview invoice status and payment history by clicking this link:<br>%3s<br><br>Thank you very much for your patronage.<br><br>Best regards,<br>%4s (%5s)", ud_get_wp_invoice()->domain ),
    $notification_data[ 'user_name' ],
    $notification_data[ 'business_name' ],
    $notification_data[ 'permalink' ],
    $notification_data[ 'business_name' ],
    $notification_data[ 'from' ]
  );

  /**
   * @todo add condition witch will be look at this option odokienko@UD
   */
  if ( function_exists( 'wp_crm_send_notification' ) && !empty( $wpi_settings[ 'use_wp_crm_to_send_notifications' ] ) && $wpi_settings[ 'use_wp_crm_to_send_notifications' ] == 'true' ) {
    wp_crm_send_notification( 'wpi_send_thank_you_email', $notification_data );
    //** Add message to user activity stream */
    wp_crm_add_to_user_log( $notification_data[ 'user_id' ], sprintf( __( "WP-Invoice: Message with subject '%1s' was sent", ud_get_wp_invoice()->domain ), $subject ), false, array( 'attribute' => 'wpi_notification' ) );
  } else {
    $message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );
    $subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );

    if ( wp_mail( $notification_data['user_email'], $subject, $message, implode( "\r\n", (array) $headers ) . "\r\n" ) ) {
      WPI_Functions::log_event( $notification_data[ 'invoice_id' ], 'invoice', 'emailed', '', __( 'Receipt eMailed', ud_get_wp_invoice()->domain ) );
    }
  }

  return $message;
}

/**
 * Emails merchant after payment is done
 *
 * @global array $wpi_settings
 *
 * @param array $invoice
 *
 * @author korotkov@UD
 *
 * @refactoring odokienko@UD
 */
function wp_invoice_send_me_notification( $invoice, $notification_data ) {
  global $wpi_settings;
  $headers = array(
    "From: {$notification_data['business_name']} <{$notification_data['from']}>\r\n",
    "Content-Type: text/html"
  );
  $subject = sprintf( __( "Invoice #%s has been paid", ud_get_wp_invoice()->domain ), $notification_data[ 'invoice_id' ] );

  $message = sprintf(
    __( "%1s has paid invoice #%2s.<br><br>%3s<br>Total payments: %4s %5s of %6s %7s.<br><br>You can overview invoice status and payment history by clicking this link:<br>%8s<br><br>User information:<br><br>ID: %9s<br>Name: %10s<br>Email: %11s<br><br>--------------------<br>%12s", ud_get_wp_invoice()->domain ),
    $notification_data[ 'user_name' ],
    $notification_data[ 'invoice_id' ],
    $notification_data[ 'invoice_title' ],
    $notification_data[ 'default_currency_code' ],
    $notification_data[ 'total_payments' ],
    $notification_data[ 'default_currency_code' ],
    $notification_data[ 'total' ],
    $notification_data[ 'permalink' ],
    $notification_data[ 'user_id' ],
    $notification_data[ 'user_name' ],
    $notification_data[ 'user_email' ],
    $notification_data[ 'site' ]
  );

  if ( function_exists( 'wp_crm_send_notification' ) && !empty( $wpi_settings[ 'use_wp_crm_to_send_notifications' ] ) && $wpi_settings[ 'use_wp_crm_to_send_notifications' ] == 'true' ) {
    wp_crm_send_notification( 'wpi_cc_thank_you_email', $notification_data );
    //** Add message to user activity stream */
    wp_crm_add_to_user_log( $notification_data[ 'admin_id' ], sprintf( __( "WP-Invoice: Message with subject '%1s' was sent", ud_get_wp_invoice()->domain ), $subject ), false, array( 'attribute' => 'wpi_notification' ) );

  } else {

    $message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );
    $subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );

    wp_mail( $notification_data['admin_email'], $subject, $message, implode( "\r\n", (array) $headers ) . "\r\n" );
  }
}

/**
 * Sends notification to invoice creator
 *
 * @global array $wpi_settings
 *
 * @param array $invoice
 *
 * @author korotkov@UD
 *
 * @refactoring odokienko@UD
 */
function wp_invoice_send_creator_notification( $invoice, $notification_data ) {
  global $wpi_settings;
  $headers = array(
    "From: {$notification_data['business_name']} <{$notification_data['from']}>\r\n",
    "Content-Type: text/html"
  );
  $subject = sprintf( __( "Invoice #%s has been paid", ud_get_wp_invoice()->domain ), $notification_data[ 'invoice_id' ] );
  $message = sprintf(
    __( "Hello %1s,<br><br>%2s has paid invoice #%3s.<br><br>%4s<br>Total payments: %5s %6s of %7s %8s.<br><br>You can overview invoice status and payment history by clicking this link:<br>%9s<br><br>User information:<br><br>ID: %10s<br>Name: %11s<br>Email: %12s<br><br>--------------------<br>%13s", ud_get_wp_invoice()->domain ),
    $notification_data[ 'creator_name' ],
    $notification_data[ 'user_name' ],
    $notification_data[ 'invoice_id' ],
    $notification_data[ 'invoice_title' ],
    $notification_data[ 'default_currency_code' ],
    $notification_data[ 'total_payments' ],
    $notification_data[ 'default_currency_code' ],
    $notification_data[ 'total' ],
    $notification_data[ 'permalink' ],
    $notification_data[ 'user_id' ],
    $notification_data[ 'user_name' ],
    $notification_data[ 'user_email' ],
    $notification_data[ 'site' ]
  );
  if ( function_exists( 'wp_crm_send_notification' ) && !empty( $wpi_settings[ 'use_wp_crm_to_send_notifications' ] ) && $wpi_settings[ 'use_wp_crm_to_send_notifications' ] == 'true' ) {
    wp_crm_send_notification( 'wpi_send_invoice_creator_email', $notification_data );
    //** Add message to user activity stream */
    wp_crm_add_to_user_log( $notification_data[ 'creator_id' ], sprintf( __( "WP-Invoice: Message with subject '%1s' was sent", ud_get_wp_invoice()->domain ), $subject ), false, array( 'attribute' => 'wpi_notification' ) );
  } else {
    $message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );
    $subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );
    wp_mail( $notification_data['creator_email'], $subject, $message, implode( "\r\n", (array) $headers ) . "\r\n" );
  }
}

/**
 * Sends required notifications
 *
 * @global array $wpi_settings
 *
 * @param array $invoice
 *
 * @author korotkov@UD
 * @refactoring odokienko@UD
 */
function send_notification( $invoice ) {
  global $wpi_settings;

  if ( ( !empty( $wpi_settings[ 'send_thank_you_email' ] ) && $wpi_settings[ 'send_thank_you_email' ] == 'true' ) ||
    ( !empty( $wpi_settings[ 'cc_thank_you_email' ] ) && $wpi_settings[ 'cc_thank_you_email' ] == 'true' ) ||
    ( !empty( $wpi_settings[ 'send_invoice_creator_email' ] ) && $wpi_settings[ 'send_invoice_creator_email' ] == 'true' )
  ) {

    $paid_invoice = new WPI_Invoice();
    $paid_invoice->load_invoice( "id={$invoice['invoice_id']}" );
    $invoice = $paid_invoice->data;

    $notification_data[ 'invoice_id' ] = ( !empty( $invoice[ 'custom_id' ] ) ) ? $invoice[ 'custom_id' ] : $invoice[ 'invoice_id' ];
    $notification_data[ 'invoice_title' ] = $invoice[ 'post_title' ];
    $notification_data[ 'from' ] = stripslashes( get_option( 'admin_email' ) );
    $notification_data[ 'permalink' ] = get_invoice_permalink( $invoice[ 'invoice_id' ] );
    $notification_data[ 'business_name' ] = $wpi_settings[ 'business_name' ];
    $notification_data[ 'site' ] = stripslashes( $wpi_settings[ 'business_name' ] );

    $notification_data[ 'user_email' ] = $invoice[ 'user_data' ][ 'user_email' ];
    $notification_data[ 'user_name' ] = wpi_get_user_display_name( $invoice );
    $notification_data[ 'user_id' ] = $invoice[ 'user_data' ][ 'ID' ];

    $admin = get_user_by( 'email', get_option( 'admin_email' ) );
    $notification_data[ 'admin_email' ] = stripslashes( $admin->user_email );
    $notification_data[ 'admin_id' ] = $admin->ID;
    $notification_data[ 'admin_name' ] = stripslashes( $admin->display_name );

    $creator = get_userdata( $invoice[ 'post_author' ] );
    $notification_data[ 'creator_email' ] = stripslashes( $creator->user_email );
    $notification_data[ 'creator_name' ] = stripslashes( $creator->display_name );
    $notification_data[ 'creator_id' ] = $creator->ID;

    $notification_data[ 'total' ] = $invoice[ 'subtotal' ] - $invoice[ 'total_discount' ] + $invoice[ 'total_tax' ];
    $notification_data[ 'default_currency_code' ] = $invoice[ 'default_currency_code' ];
    $notification_data[ 'total_payments' ] = $invoice[ 'total_payments' ];

    //** If we are going to change our Mail From */
    if ( !empty( $wpi_settings[ 'change_mail_from' ] ) && $wpi_settings[ 'change_mail_from' ] == 'true' ) {
      add_filter( 'wp_mail_from', array( 'WPI_Functions', 'notification_mail_from' ) );
      add_filter( 'wp_mail_from_name', array( 'WPI_Functions', 'notification_mail_from_name' ) );
    }

    /** Email client */
    if ( !empty( $wpi_settings[ 'send_thank_you_email' ] ) && $wpi_settings[ 'send_thank_you_email' ] == 'true' ) {
      wp_invoice_send_email_receipt( $invoice, $notification_data );
    }

    /** Email site admin */
    if ( !empty( $wpi_settings[ 'cc_thank_you_email' ] ) && $wpi_settings[ 'cc_thank_you_email' ] == 'true' ) {
      wp_invoice_send_me_notification( $invoice, $notification_data );
    }

    /** Email invoice creator */
    if ( !empty( $wpi_settings[ 'send_invoice_creator_email' ] ) && $wpi_settings[ 'send_invoice_creator_email' ] == 'true' ) {
      wp_invoice_send_creator_notification( $invoice, $notification_data );
    }

    remove_filter( 'wp_mail_from', array( 'WPI_Functions', 'notification_mail_from' ) );
    remove_filter( 'wp_mail_from_name', array( 'WPI_Functions', 'notification_mail_from_name' ) );

  }

}

/**
 * Returns display_name from invoice if exists or from userdata
 *
 * @param type $invoice
 *
 * @return type
 * @author korotkov@ud
 */
function wpi_get_user_display_name( $invoice ) {
  /** If display name exists, return it */
  if ( !empty( $invoice[ 'user_data' ][ 'display_name' ] ) ) {
    return $invoice[ 'user_data' ][ 'display_name' ];
  }

  /** Get current user data and return it */
  $user = get_userdata( $invoice[ 'user_data' ][ 'ID' ] );
  return $user->display_name;
}

/**
 * This function checks to see if a plugin is installed
 *
 * @param string $slug The class name of the plugin
 *
 * @return bool Whether or not its installed
 * @since 3.0
 */
function wpi_feature_installed( $slug ) {
  global $wpi_settings;
  if ( is_array( $wpi_settings[ 'installed_features' ][ $slug ] ) && !$wpi_settings[ 'installed_features' ][ $slug ][ 'disabled' ] ) {
    return true;
  }
  return false;
}

/**
 * Shows business information on front-end
 */
function wp_invoice_show_business_information() {
  $core = WPI_Core::getInstance();
  $business_info[ 'name' ] = apply_filters( 'wpi_business_name', $core->Settings->options[ 'business_name' ] );
  $business_info[ 'address' ] = apply_filters( 'wpi_business_address', $core->Settings->options[ 'business_address' ] );
  $business_info[ 'phone' ] = apply_filters( 'wpi_business_phone', $core->Settings->options[ 'business_phone' ] );
  ?>
  <div id="invoice_business_info" class="clearfix">
    <p class="invoice_page_subheading"><strong>Bill From:</strong></p>

    <p class="wp_invoice_bi wp_invoice_business_name"><?php echo $business_info[ 'name' ]; ?></p>

    <p class="wp_invoice_bi wp_invoice_business_address"><?php echo $business_info[ 'address' ]; ?></p>

    <p class="wp_invoice_bi wp_invoice_business_phone"><?php echo $business_info[ 'phone' ]; ?></p>
  </div>
<?php
}

/**
 * Returns due date in format
 *
 * @param type $invoice
 *
 * @return type
 * @author korotkov@ud
 */
function get_due_date( $invoice ) {

  if ( !empty( $invoice[ 'due_date_year' ] ) && !empty( $invoice[ 'due_date_month' ] ) && !empty( $invoice[ 'due_date_day' ] ) ) {
    return date( get_option( 'date_format' ), strtotime( $invoice[ 'due_date_day' ] . '-' . $invoice[ 'due_date_month' ] . '-' . $invoice[ 'due_date_year' ] ) );
  }

  return false;

}

/**
 * Find a full diff between two arrays.
 *
 * @param array $array1
 * 
 * @param array $array2
 *
 * @return array
 * @author korotkov@ud
 */
function wpi_multi_array_diff( $array1, $array2 ) {
  $ret = array();
  foreach ( $array1 as $k => $v ) {
    if ( !isset( $array2[ $k ] ) ) $ret[ $k ] = $v;
    else if ( is_array( $v ) && is_array( $array2[ $k ] ) ) {
      $u = wpi_multi_array_diff( $v, $array2[ $k ] );
      if ( !empty( $u ) ) {
        $ret[ $k ] = $u;
      }
    } else if ( $v != $array2[ $k ] ) {
      $ret[ $k ] = $v;
    }
  }
  return $ret;
}

/**
 * Returns Invoice Permalink by invoice id
 *
 * @global array $wpi_settings
 * @global object $wpdb
 *
 * @param type $identificator
 *
 * @return boolean
 */
function get_invoice_permalink( $identificator ) {
  global $wpi_settings, $wpdb;

  $hash = "";
  
  //** Check Invoice by ID and get hash */
  if ( empty( $identificator ) ) {
    return false;
  }

  // try to get cached permalin
  if( $_cached = wp_cache_get( 'wpi-permalink-' . $identificator, 'wp-invoice' ) ) {
    $_result = $_cached; 
  }


  if( !isset( $_result ) ) {
    
    $id = get_invoice_id( $identificator );
  
    //** Get hash by post ID */
    if ( !empty( $id ) ) {
      $hash = $wpdb->get_var( $wpdb->prepare( "SELECT `meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key` = 'hash' AND `post_id` = '%d'",
        $id
      ) );
    }
  
    if ( empty( $hash ) || empty( $wpi_settings[ 'web_invoice_page' ] ) ) {
      $_result =  false;
    } else {
  
    if ( get_option( "permalink_structure" ) ) {
      $_result = get_permalink( $wpi_settings[ 'web_invoice_page' ] ) . "?invoice_id=" . $hash;
    } else {
      //** check if page is on front-end */
      if ( get_option( 'page_on_front' ) == $wpi_settings[ 'web_invoice_page' ] ) {
        $_result = get_permalink( $wpi_settings[ 'web_invoice_page' ] ) . "?invoice_id=" . $hash;
      } else {
        $_result = get_permalink( $wpi_settings[ 'web_invoice_page' ] ) . "&invoice_id=" . $hash;
      }
    }
    
    }
    
  }

  // cache result 2
  wp_cache_set( 'wpi-permalink-' . $identificator, $_result, 'wp-invoice' );

  return apply_filters( 'wpi_invoice_permalink', $_result, array( 
    'id' => isset( $_cached ) ? $_cached : $id, 
    'hash' => $hash, 
    'identificator' => $identificator,
    'cached' => isset( $_cached ) ? true : false
  ));
  
}

/**
 * This function can be used to get invoice id by several identifiers (hash, custom_id, invoice_id, post_id)
 *
 * @author odokienko@UD
 * @return bool|int False or the invoice id
 */
function get_invoice_id( $identificator ) {
  global $wpdb;


  if( $_cached = wp_cache_get( 'wpi-alias-' . $identificator, 'wp-invoice' ) ) {
    return $_cached; 
  } 
  
      //error_log( "invoice_id not in cache " . $identificator );


  $id = false;
  if ( strlen( $identificator ) == 32 ) {
    //** Determine if $identificator is invoice HASH */
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='invoice_id' and md5(meta_value)=%s",
      $identificator
    ) );
  }

  //** Determine if $identificator is invoice_id */
  if ( empty( $id ) ) {
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = %s",
      $identificator
    ) );
  }

  //** Determine if $identificator is custom_id */
  if ( empty( $id ) ) {
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'custom_id' AND meta_value = %s",
        $identificator
    ) );
  }

  //** If empty id, determine if $identificator is post ID */
  if ( empty( $id ) ) {
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='wpi_object' and ID=%s",
      $identificator
    ) );
  }
  
  
  // cache alias
  wp_cache_set( 'wpi-alias-' . $identificator, $id, 'wp-invoice' );
  

  return $id;
}

/**
 * Checks Invoice Exists or not
 *
 * @return boolean
 */
function wpi_check_invoice( $ID ) {
  global $wpdb;

  if ( empty( $ID ) || (int) $ID == 0 ) {
    return false;
  }
  $result = $wpdb->get_var( "SELECT post_status FROM {$wpdb->posts} WHERE ID = '$ID'" );
  if ( empty( $result ) ) {
    return false;
  }
  return true;
}

/**
 * This function converts an invoices invoice_id to a post_id or returns post_id if it was passed
 *
 * @param int $invoice_id The invoice ID
 *
 * @return bool|int False or the post id
 * @since 3.0
 */
function wpi_invoice_id_to_post_id( $invoice_id ) {
  global $wpdb;
  $maybe_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'invoice_id' AND meta_value = '{$invoice_id}'" );

  return $maybe_id ? $maybe_id : $invoice_id;
}

/**
 * This function converts a ARB subscription id subscription_id to a post_id
 *
 * @param int $subscription_id The subscription ID
 *
 * @return bool|int False or the post id
 * @since 3.0
 */
function wpi_subscription_id_to_post_id( $subscription_id ) {
  global $wpdb;
  return $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'subscription_id' AND meta_value = '{$subscription_id}'" );
}

/**
 * This function converts an invoices post_id to a invoice_id
 *
 * @param int $post_id The post ID
 *
 * @return bool|int False or the invoice id
 * @since 3.0
 */
function wpi_post_id_to_invoice_id( $post_id ) {
  return get_metadata( 'post', $post_id, 'invoice_id', true );
}

/**
 * @author korotkov@ud
 *
 * @param array $data
 * <b>Example:</b><br>
 * <pre>
 * array(
 *    'venue' => 'wpi_authorize',
 *    'amount' => '100.00',
 *    'payer_email' => 'john.smith@gmail.com',
 *    'payer_first_name' => 'John',
 *    'payer_last_name' => 'Smith',
 *    'cc_number' => '411111111111111',
 *    'cc_expiration' => '0412',
 *    'cc_code' => '356',
 *    'items' => array(
 *      array(
 *        'name' => 'Name 1',
 *        'description' => 'Item 1',
 *        'quantity' => 1,
 *        'price' => '10.00'
 *      ),
 *      array(
 *        'name' => 'Name 2',
 *        'description' => 'Item 2',
 *        'quantity' => 2,
 *        'price' => '10.00'
 *      )
 *    )
 * )
 * </pre>
 *
 * @return array
 */
function wpi_process_transaction( $data ) {
  $wpa = new WPI_Payment_Api();
  return $wpa->process_transaction( $data );
}

/**
 * @param null $data
 */
function wpi_send_json_error( $data = null ) {
  $response = array( 'success' => false, 'error' => true );

  if ( isset( $data ) ) {
    if ( is_wp_error( $data ) ) {
      $result = array();
      foreach ( $data->errors as $code => $messages ) {
        foreach ( $messages as $message ) {
          $result[] = array( 'code' => $code, 'message' => $message );
        }
      }

      $response['data'] = $result;
    } else {
      $response['data'] = $data;
    }
  }

  wp_send_json( $response );
}

/**
 * Mark invoice as viewed if not by admin
 * @param $invoice_object
 */
function wpi_track_invoice_page_visit( $invoice_object ) {

  if ( !current_user_can( 'manage_options' ) ) {
    $hours = 12;
    $viewed_today_from_cur_ip = false;
    foreach ( $invoice_object->data[ 'log' ] as $key => $value ) {
      if ( $value[ 'user_id' ] == '0' ) {
        if ( strstr( strtolower( $value[ 'text' ] ), "viewed by {$_SERVER['REMOTE_ADDR']}" ) ) {
          $time_dif = time() - $value[ 'time' ];
          if ( $time_dif < $hours * 60 * 60 ) {
            $viewed_today_from_cur_ip = true;
          }
        }
      }
    }
    if ( !$viewed_today_from_cur_ip ) {
      $invoice_object->add_entry( "note=Viewed by {$_SERVER['REMOTE_ADDR']}" );
    }
  }
}

/**
 * Returns an invoice object as an array.
 * @param type $args
 */
function get_invoice( $args ) {
  if ( is_numeric( $args ) ) {
    $invoice_id = $args;
  } else {
    $defaults = array( 'invoice_id' => '', 'return_class' => false );
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );
  }
  $invoice = new WPI_Invoice();
  $invoice->load_invoice( "id=$invoice_id" );

  if ( !empty( $invoice->error ) && $invoice->error ) {
    return sprintf( __( "Invoice %s not found.", ud_get_wp_invoice()->domain ), $invoice_id );
  }

  if ( !empty( $return_class ) && $return_class ) {
    return $invoice;
  }

  return $invoice->data;
}

/**
 * Invoice lookup function
 * If return is passed as true, function is returned.
 *
 * @global type $wpi_settings
 *
 * @param type $args
 *
 * @return type
 */
function wp_invoice_lookup( $args = '' ) {
  global $wpi_settings, $current_user;

  $result = '';

  $defaults = array(
      'message' => __( 'Enter Invoice ID', ud_get_wp_invoice()->domain ),
      'button' => __( 'Lookup', ud_get_wp_invoice()->domain ),
      'return' => true
  );
  extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

  if ( !$current_user->ID ) {
    return;
  }

  ob_start();
  if ( WPI_Functions::wpi_use_custom_template( 'invoice_lookup.php' ) ) {
    include( $wpi_settings[ 'frontend_template_path' ] . 'invoice_lookup.php' );
  } else {
    include( $wpi_settings[ 'default_template_path' ] . 'invoice_lookup.php' );
  }
  $result .= ob_get_clean();

  if ( $return ) {
    return $result;
  }
  echo $result;
}

/**
 * TO keep wpi naming structure
 *
 * @param type $args
 *
 * @return type
 */
function wpi_invoice_lookup( $args = '' ) {
  return wp_invoice_lookup( $args );
}

/**
 * Draw widget by shortcode
 *
 * @param array $args
 */
function wp_invoice_history( $args = '' ) {
  ob_start();
  echo the_widget( 'InvoiceHistoryWidget', $args );
  return ob_get_clean();
}