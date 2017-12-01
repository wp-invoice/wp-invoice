<?php

/**
 * Show URL of invoice
 * @global array $invoice
 */
if ( !function_exists('invoice_permalink') ) {
  function invoice_permalink() {
    global $invoice;
    echo get_invoice_permalink($invoice['invoice_id']);
  }
}

/**
 * Show PDF link of invoice
 * @global array $invoice
 */
if ( !function_exists('invoice_pdf_link') ) {
  function invoice_pdf_link() {
    global $invoice;
    echo get_invoice_permalink($invoice['invoice_id']) . "&format=pdf";
  }
}

/**
 * Print itemized table.
 *  show_quantities = will show quantity column
 *  item_heading = column heading for item
 *  cost_heading = column heading for cost
 *  quantity_heading = column heading for quantity
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('show_itemized_table') ) {
  function show_itemized_table($args = '') {
    global $invoice, $wpi_settings;

    $defaults = array('return' => false, 'item_heading' => __("Item", ud_get_wp_invoice()->domain), 'cost_heading' => __("Cost", ud_get_wp_invoice()->domain), 'show_quantities' => false, 'quantity_heading' => __('Quantity', ud_get_wp_invoice()->domain));

    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    // If hide_quantity is not passed by function, we referr to global setting
    if (!$show_quantities) {
      $show_quantities = ($wpi_settings['globals']['show_quantities'] == 'true' ? true : false);
    }

    $currency_symbol = (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$");

    ob_start();
    if ($wpi_settings['use_custom_templates'] != 'yes' || !file_exists(TEMPLATEPATH . '/wpi/table.php')):
      ?>
      <table id="wp_invoice_itemized_table" class="table table-striped wp_invoice_itemized_table">
        <thead>
          <tr>
            <th class="title_column"><?php echo $item_heading; ?></th>
      <?php if ($show_quantities): ?>
              <th class="quantity_column"><?php echo $quantity_heading; ?></th>
      <?php endif; ?>
            <th class="cost_column"><?php echo $cost_heading; ?></th>
          </tr>
        </thead>
        <tbody>
      <?php $i = 1; ?>
      <?php if (isset($invoice['itemized_list']) && is_array($invoice['itemized_list'])) : ?>
        <?php foreach ($invoice['itemized_list'] as $row) : ?>
              <tr class="<?php echo++$i % 2 ? 'alt_row' : '' ?>">
                <td class="title_column">
                  <div class="wpi_line_item_title"><?php echo stripslashes($row['name']); ?></div>
                  <div class="description_text"><?php echo nl2br($row['description']); ?></div>
                </td>
                <?php if ($show_quantities): ?>
                  <td class="quantity_column">
            <?php echo $row['quantity']; ?>
                  </td>
              <?php endif; ?>
                <td class="cost_column"><?php echo $currency_symbol . wp_invoice_currency_format($row['line_total_before_tax']); ?></td>
              </tr>
        <?php endforeach; ?>
            <?php endif; ?>
            <?php if (isset($invoice['itemized_charges']) && is_array($invoice['itemized_charges'])): ?>
            <tr>
              <th class="title_column"><?php _e('Charges', ud_get_wp_invoice()->domain) ?></th>
        <?php if ($show_quantities): ?>
                <th class="quantity_column"></th>
            <?php endif; ?>
              <th class="cost_column"><?php echo $cost_heading; ?></th>
            </tr>
                <?php $i = 1; ?>
                <?php foreach ($invoice['itemized_charges'] as $row) : ?>
              <tr class="<?php echo++$i % 2 ? 'alt_row' : '' ?>">
                <td class="title_column">
          <?php echo stripslashes($row['name']); ?> <br>
                </td>
                <?php if ($show_quantities): ?>
                  <td class="quantity_column">
                  </td>
          <?php endif; ?>
                <td class="cost_column">
              <?php echo $currency_symbol . wp_invoice_currency_format($row['amount']); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <?php
          //** Figure out what colspan is based on how many columns we have */
          $colspan = $show_quantities ? 'colspan="2"' : '';
          if (!empty($invoice['subtotal'])):
            ?>
            <tr class="wpi_subtotal">
              <td class="bottom_line_title" <?php echo $colspan; ?>>
                <?php _e('Subtotal:', ud_get_wp_invoice()->domain) ?>
              </td>
              <td class="wpi_money">
            <?php echo $currency_symbol . wp_invoice_currency_format($invoice['subtotal']); ?></td>
            </tr>
      <?php endif; ?>
      <?php if (!empty($invoice['total_tax'])): ?>
            <tr class="wpi_subtotal">
              <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Tax:', ud_get_wp_invoice()->domain) ?></td>
              <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['total_tax']); ?></td>
            </tr>
      <?php endif; ?>
      <?php if (!empty($invoice['total_discount'])): ?>
            <tr class="wpi_subtotal">
              <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Discounts:', ud_get_wp_invoice()->domain) ?></td>
              <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['total_discount']); ?></td>
            </tr>
            <?php if ( !empty( $invoice['discount'][1]['name'] ) ): ?>
              <tr>
                <td colspan="2"><?php echo $invoice['discount'][1]['name']; ?></td>
              </tr>
            <?php endif; ?>
      <?php endif; ?>
      <?php if ($invoice['post_status'] != 'paid' && !empty($invoice['adjustments'])): ?>
            <tr class="wpi_subtotal">
              <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Adjustments:', ud_get_wp_invoice()->domain) ?></td>
              <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['adjustments']); ?></td>
            </tr>
      <?php endif; ?>
      <?php if ($invoice['post_status'] == 'paid' && !empty($invoice['total_payments'])): ?>
            <tr class="wpi_subtotal">
              <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Received Payment:', ud_get_wp_invoice()->domain) ?></td>
              <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['total_payments']); ?></td>
            </tr>
      <?php endif; ?>
      <?php if (!empty($invoice['net'])): ?>
            <tr class="wpi_subtotal">
              <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Balance:', ud_get_wp_invoice()->domain) ?></td>
              <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['net']); ?></td>
            </tr>
      <?php endif;
      ?>
        </tfoot>
      </table>
    <?php
    else:
      require_once TEMPLATEPATH . '/wpi/table.php';
    endif;
    ?>
    <?php
    $result = ob_get_contents();
    ob_end_clean();
    if ($return)
      return $result;
    echo $result;
  }
}

/**
 * Display invoice history
 * @global array $invoice
 */
if ( !function_exists('show_invoice_history') ) {
  function show_invoice_history( $args = array() ) {
    global $invoice;

    $args = wp_parse_args( $args, array(
      'create'      => true,
      'add_payment' => true,
      'paid'        => true,
      'refund'      => true,
      'add_charge'  => false,
      'do_adjustment' => false
    ));

    echo '<b class="wpi_greeting">Log</b>';
    if (!empty($invoice['log']) && is_array($invoice['log'])) {
      ?>
      <table class="invoice_history">
        <thead>
          <tr>
            <th><?php _e('Time', ud_get_wp_invoice()->domain); ?></th>
            <th><?php _e('Event', ud_get_wp_invoice()->domain); ?></th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($invoice['log'] as $key => $value) : ?>
            <?php if ($value['action'] == 'create' && $args['create']) : ?>
              <tr class="invoice-history-item">
                <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
                <td class="description"><?php echo $value['text']; ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($value['action'] == 'add_payment' && $args['add_payment']) : ?>
              <?php
              $by = '';
              if ($value['user_id'] != 0) {
                $user = get_user_by('id', $value['user_id']);
                $by = apply_filters('wpi_history_log_by', " by " . $user->display_name);
              }
              ?>
              <tr class="invoice-history-item">
                <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
                <td class="description"><?php echo $value['text'] . $by; ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($value['value'] == 'paid' && $args['paid']) : ?>
              <tr class="invoice-history-item">
                <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
                <td class="description"><?php echo $value['text']; ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($value['action'] == 'refund' && $args['refund']) : ?>
              <tr class="invoice-history-item">
                <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
                <td class="description"><?php echo $value['text']; ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($value['action'] == 'add_charge' && $args['add_charge']) : ?>
              <tr class="invoice-history-item">
                <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
                <td class="description"><?php echo $value['text']; ?></td>
              </tr>
            <?php endif; ?>
            <?php if ($value['action'] == 'do_adjustment' && $args['do_adjustment']) : ?>
              <tr class="invoice-history-item">
                <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
                <td class="description"><?php echo $value['text']; ?></td>
              </tr>
            <?php endif; ?>
      <?php endforeach; ?>
        </tbody>
      </table>
      <?php
    } else {
      echo __('There are no any actions', ud_get_wp_invoice()->domain);
    }
  }
}

/**
 * Determine if partial payment are available
 * @global array $invoice
 * @param mixed $args
 * @return bool
 */
if ( !function_exists('allow_partial_payments') ) {
  function allow_partial_payments($args = '') {
    global $invoice;

    if (!empty($invoice['deposit_amount']) && $invoice['deposit_amount'] > 0 && $invoice['net'] > $invoice['deposit_amount']) {
      return true;
    }

    return false;
  }
}

/**
 * Show payment switcher
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 */
if ( !function_exists('show_partial_payments') ) {
  function show_partial_payments($args = '') {
    global $invoice, $wpi_settings;

    if (!empty($invoice['deposit_amount']) && $invoice['deposit_amount'] > 0):

      $currency_symbol = (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$");
      $full_balance = wp_invoice_currency_format($invoice['net']);
      $minimum = wp_invoice_currency_format($invoice['deposit_amount']);
      ?>
      <form class="wpi_checkout">
        <div class="wpi_checkout_partial_payment wpi_checkout_payment_box">
          <ul class="wpi_checkout_block">

            <li class="section_title">
              <?php _e('Payment Amount', ud_get_wp_invoice()->domain); ?>
            </li>

            <li class="section_description"><p><?php echo apply_filters('wpi_show_partial_payments_message', __('This invoice allows partial payments, please select the amount you would like to pay.', ud_get_wp_invoice()->domain)); ?></p></li>

            <li class="wpi_checkout_row">
              <label for="wpi_minimum_amount_option"><?php _e("Min. Payment Due:", ud_get_wp_invoice()->domain); ?></label>
              <input type="radio" name="payment_amount" id="wpi_minimum_amount_option" value="<?php echo $invoice['deposit_amount']; ?>" />
              <span><?php echo $currency_symbol . wp_invoice_currency_format($invoice['deposit_amount']); ?></span>
            </li>

            <li class="wpi_checkout_row">
              <label for="wpi_full_amount_option"><?php _e("Statement Balance:", ud_get_wp_invoice()->domain); ?></label>
              <input checked="checked" type="radio" name="payment_amount" id="wpi_full_amount_option" value="<?php echo $invoice['net']; ?>" />
              <span><?php echo $currency_symbol . wp_invoice_currency_format($invoice['net']); ?></span>
            </li>

            <li class="wpi_checkout_row">
              <label for="wpi_custom_amount_option"><?php _e("Other", ud_get_wp_invoice()->domain); ?></label>
              <input type="radio" name="payment_amount" id="wpi_custom_amount_option"  value="<?php echo wp_invoice_currency_format($invoice['net']); ?>" />

              <span id="wpi_custom_amount_option_field_wrapper"><?php echo $currency_symbol; ?>
                <input class="text-input small" id="my_amount" name="my_amount" type="text" value="<?php echo wp_invoice_currency_format($invoice['net']); ?>" />
              </span>
            </li>
          </ul>
        </div>
      </form>
      <script type="text/javascript">
        /**
         * Partial payments JS
         */
        var minimum_payment = <?php echo $invoice['deposit_amount'] ?>;
        var balance         = <?php echo $invoice['net'] ?>;
        jQuery(document).ready(function(){
          var validate_amount = function(amount) {
            amount = Math.abs( parseFloat( amount ) );
            if ( amount < minimum_payment ) return minimum_payment;
            if ( amount > balance ) return balance;
            if ( isNaN( amount ) ) return balance;
            return amount;
          }
          var set_pay_button_value = function() {
            if(jQuery("#pay_button_value").length > 0){
              var pa = jQuery("#payment_amount").val();
              jQuery("#pay_button_value").html(pa);
            }
          }
          //** Find fields */
          var payment_amount        = jQuery("#payment_amount");
          var my_amount             = jQuery("#my_amount");
          //** Find radios */
          var custom_amount_option  = jQuery("#wpi_custom_amount_option");
          var minimum_amount_option = jQuery("#wpi_minimum_amount_option");
          var full_amount_option    = jQuery("#wpi_full_amount_option");
          var custom_amount_field = jQuery("#wpi_custom_amount_option_field_wrapper");
          my_amount.on("focus", function(){
            custom_amount_option.attr("checked", "checked");
          });
          custom_amount_option.click(function(){
            my_amount.focus();
            custom_amount_field.show();
          });
          minimum_amount_option.click(function(){
            payment_amount.val( validate_amount( minimum_amount_option.val() ) );
            custom_amount_field.hide();
            set_pay_button_value();
          });
          full_amount_option.click(function(){
            payment_amount.val( validate_amount( full_amount_option.val() ) );
            custom_amount_field.hide();
            set_pay_button_value();
          });
          //** Handle changing of payment method */
          jQuery("#online_payment_form_wrapper").on("formLoaded", function(){
            payment_amount = jQuery("#payment_amount");
            my_amount      = jQuery("#my_amount");
            //** update field data */
            if ( custom_amount_option.is(":checked") ) {
              payment_amount.val( validate_amount( my_amount.val() ) );
            }
            if ( minimum_amount_option.is(":checked") ) {
              payment_amount.val( validate_amount( minimum_amount_option.val() ) );
            }
            set_pay_button_value();
          });
          //** If there are required fields */
          if ( payment_amount.length && my_amount.length ) {
            //** update field data */
            my_amount.on("keyup", function(){
              var new_value = my_amount.val();
              payment_amount.val( validate_amount( new_value ) );
              set_pay_button_value();
            });
            my_amount.on("blur", function(){
              my_amount.val( payment_amount.val() );
              set_pay_button_value();
            });
            my_amount.on("focus", function(){
              my_amount.val( payment_amount.val() );
              set_pay_button_value();
            });
          } else {
            alert( "<?php _e('Partial payment is not available because of an error.\nContact Administirator for more information.', ud_get_wp_invoice()->domain) ?>" );
          }
        });
      </script>
    <?php
    endif;
  }
}

/**
 * Display payment method select
 *
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('show_payment_selection') ) {
  function show_payment_selection($args = '') {
    global $invoice, $wpi_settings;

    $defaults = array(
      'title' => __("Payment Method", ud_get_wp_invoice()->domain),
      'return' => false
    );

    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    //** Make sure invoice allows for user to change payment, and that there is more than one payment method */
    if (!empty($invoice['client_change_payment_method'])) {
      if ($invoice['client_change_payment_method'] == 'off' || $invoice['client_change_payment_method'] == false || $invoice['client_change_payment_method'] == "false") {
        return;
      }
    } else {
      return;
    }

    //** Count number of available payment methods */
    $count = 0;
    foreach ($invoice['billing'] as $value) {
      if ($value['allow'] == 'on' || $value['allow'] == 'true') {
        $count++;
      }
    }

    if ($count < 2) return;

    $result = '';

    ob_start();

    ?>
    <div class="wpi_checkout_payment_box">
      <ul class="wpi_checkout_block wpi_checkout_method_selection">
        <li class="section_title"><?php echo $title; ?></li>
          <li class="wpi_checkout_row">
            <div class="control-group">
              <label class="control-label" for="wp_invoice_select_payment_method_selector"><?php echo $title; ?></label>
              <div class="controls">
                <select name="wp_invoice_select_payment_method_selector" id="wp_invoice_select_payment_method_selector">
                  <?php
                    foreach ($invoice['billing'] as $key => $value) :
                      $method = $value;
                      if (empty($method['name'])) continue;
                      if ($method['allow'] == 'on') :
                        ?>
                        <option value="<?php echo $key; ?>" <?php selected($key, $invoice['default_payment_method']); ?>><?php echo (!empty($method['public_name']) ? $method['public_name'] : $method['name']); ?></option>
                        <?php
                      endif;
                    endforeach;
                  ?>
                </select>
              </div>
            </div>
          </li>
      </ul>
    </div>
    <div style="clear:both;"></div>

    <?php
      $result .= ob_get_contents();
      ob_end_clean();
      if ($return)
        return $result;
      echo $result;
  }
}

/**
 * Show term acceptance checkbox
 *   label= will insert label
 *   force=will override invoice setting for terms acceptance, and show acceptance
 *   pade_id = page id of terms acceptance page
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('show_terms_acceptance') ) {
  function show_terms_acceptance($args = '') {
    global $invoice;
    $defaults = array('label' => false, 'force' => false, 'page_id' => false);

    extract(wp_parse_args($args, $defaults), EXTR_SKIP);
    // Make sure invoice requires terms acceptance, unless it is being forced

    if (!$force && $invoice['terms_acceptance_required'] != 'on')
      return;

    if ($page_id)
      $terms_link = get_permalink($page_id);

    if (!empty($label)) {
      if ($terms_link)
        $result .= "<label for='wpi_term_acceptance'><a href='$terms_link'>$label</a></label>";
      if (!$terms_link)
        $result .= "<label for='wpi_term_acceptance'>$label</a></label>";
    }

    $result .= '<input style="width: 20px;" type="checkbox" value="accept"  class="wpi_term_acceptance" id="wpi_term_acceptance" name="wpi_term_acceptance">';

    if ($return)
      return $result;

    echo $result;
  }
}

/**
 * Show amount owed.
 *  hide_currency=true will not automatically insert currency.
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('balance_due') ) {
  function balance_due($args = '') {
    global $invoice, $wpi_settings;

    $result = "";

    $defaults = array('return' => false, 'hide_currency' => false);
    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    if (!$hide_currency) {
      $currency_symbol = (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$");
    }

    $result .= $currency_symbol . wp_invoice_currency_format($invoice['net']);

    if ($return) {
      return $result;
    }

    echo $result;
  }
}

/**
 * Show invoice description
 * Filter Applied: 'wpi_description'
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
if (!function_exists('the_description')) {
  function the_description($args = '') {
    global $invoice;

    $defaults = array('return' => false, 'show_all' => false, 'show_hidden' => false);
    $args = wp_parse_args($args, $defaults);

    if (empty($invoice['post_content']))
      return;

    $content = $invoice['post_content'];

    if (preg_match('/<!--more(.*?)?-->/', $content, $matches)) {
      $content = explode($matches[0], $content, 2);
    } else {
      $content = array($content);
    }

    $_output = '';

    foreach ($content as $_step => $_content) {

      $_content = apply_filters('wpi_description', $_content);

      if ($_step === 0) {
        $_output .= '<div class="wpi-above-fold">' . $_content . '</div>';
      } else {


        // by default don't go below the --more-- break.
        if (isset($args['show_all']) && $args['show_all']) {
          $_output .= '<div class="wpi-below-fold">' . $_content . '</div>';
        }

        // by default don't go below the --more-- break.
        if (isset($args['show_hidden']) && $args['show_hidden'] && isset($_content)) {
          $_output .= '<span class="wpi-below-the-fold-content" style="display:none">' . $_content . '</span>';
          $_output .= '<a href="#detail" class="wpi-below-the-fold-trigger">Toggle Detail</a>';
        }

      }
    }

    if ($args['return']) {
      return $_output;
    }

    echo $_output;
  }
}

/**
 * Show invoice id.
 *   force_original=true will display the actual ID, even if custom ID is set
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('invoice_id') ) {
  function invoice_id($args = '') {
    global $invoice;

    $defaults = array('return' => false, 'force_original' => false);

    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    if (!empty($invoice['custom_id']) && !$force_original) {
      $result = $invoice['custom_id'];
    } else {
      $result = wpi_post_id_to_invoice_id($invoice['ID']);
    }

    if ($return)
      return $result;

    echo $result;
  }
}

/**
 * Display recipients name
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('recipients_name') ) {
  function recipients_name($args = '') {
    global $invoice;

    $defaults = array('return' => false);

    extract(wp_parse_args($args, $defaults), EXTR_SKIP);

    //** If display name exists, return it */
    if (!empty($invoice['user_data']['display_name'])) {
      if ($return) {
        return $invoice['user_data']['display_name'];
      }
      echo $invoice['user_data']['display_name'];
      return;
    }

    $user = get_userdata($invoice['user_data']['ID']);

    $display_name = $user->display_name;

    if ($return) {
      return $display_name;
    }

    echo $display_name;
  }
}

/**
 * Paid or not
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_paid') ) {
  function is_paid() {
    global $invoice;
    return $invoice['post_status'] == 'paid';
  }
}

/**
 * Pending or not
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_pending') ) {
  function is_pending() {
    global $invoice;
    return $invoice['post_status'] == 'pending';
  }
}

/**
 * Return paid date
 * Filter Applied: 'wpi_date_paid_format'
 * @global array $invoice
 * @return string
 */
if ( !function_exists('date_paid') ) {
  function date_paid() {
    global $invoice;
    return date(apply_filters('wpi_date_paid_format', 'd F Y, \o\n H:i'), get_post_modified_time('U', false, $invoice['ID']));
  }
}

/**
 * Returns true if any payments have been made at all.
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_payment_made') ) {
  function is_payment_made() {
    global $invoice;
    return $invoice['net'] > 0 && $invoice['total_payments'] > 0;
  }
}

/**
 * PayPal is allowed or not
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_paypal_allowed') ) {
  function is_paypal_allowed() {
    global $invoice;

    if ($invoice['billing']['paypal']['allow'] == 'true')
      return true;

    return false;
  }
}

/**
 * Renders paid amount
 * @global array $invoice
 * @global array $wpi_settings
 */
if ( !function_exists('paid_amount') ) {
  function paid_amount() {
    global $invoice, $wpi_settings;

    $currency_symbol = (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$");
    echo $currency_symbol . wp_invoice_currency_format(!empty($invoice['total_payments']) ? $invoice['total_payments'] : 0 );
  }
}

/**
 * Is quote or not
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_quote') ) {
  function is_quote() {
    global $invoice;

    return !empty($invoice['is_quote']);
  }
}

/**
 * Determines is this invoice is a single invoice, and not a quote, nor a recurring bill.
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_invoice') ) {
  function is_invoice() {
    global $invoice;

    /**
     * Hook for custom stuff
     */
    if ( apply_filters( 'wpi_invoice_is_invoice', false, $invoice ) ) {
      return true;
    }

    return $invoice['type'] == 'invoice' ? true : false ;
  }
}

/**
 * Determines is this is a recurring bill.
 * @global array $invoice
 * @return bool
 */
if ( !function_exists('is_recurring') ) {
  function is_recurring() {
    global $invoice;

    if (!empty($invoice['type']) &&
            $invoice['type'] == 'recurring' &&
            !empty($invoice['recurring']['active']) &&
            $invoice['recurring']['active'] == 'on') {
      return true;
    }

    return false;
  }
}


/**
 * Show business nam
 * @return bool
 */
if ( !function_exists('show_business_name') ) {
  function show_business_name() {
    $core = WPI_Core::getInstance();
    return $core->Settings->options['globals']['show_business_name'] == 'false' ? FALSE : TRUE;
  }
}


/**
 * Show business info or not
 * @return bool
 */
if ( !function_exists('show_business_info') ) {
  function show_business_info() {
    $core = WPI_Core::getInstance();
    return $core->Settings->options['globals']['show_business_address'] == 'false' ? FALSE : TRUE;
  }
}

/**
 * Render Invoice Due Date
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
if ( !function_exists('wpi_invoice_due_date') ) {
  function wpi_invoice_due_date( $args = "" ) {
    global $invoice;

    $defaults = array(
        'return' => false,
        'text'   => __('Due Date: ', ud_get_wp_invoice()->domain),
        'format' => 'd F Y'
    );

    extract( wp_parse_args($args, $defaults) );

    if ( empty( $invoice['due_date_year'] )
         || empty( $invoice['due_date_month'] )
         || empty( $invoice['due_date_day'] ) )  return;

    if ( !$return ) {
      echo $text.date($format, strtotime( $invoice['due_date_day'].'-'.$invoice['due_date_month'].'-'.$invoice['due_date_year'] ));
      return;
    }

    return $text.date($format, strtotime( $invoice['due_date_day'].'-'.$invoice['due_date_month'].'-'.$invoice['due_date_year'] ));
  }
}

/**
 * Get invoice date
 * @global type $invoice
 * @param type $args
 * @return type
 */
if ( !function_exists('wpi_invoice_date') ) {
  function wpi_invoice_date( $args = array() ) {
    global $invoice;

    $defaults = array(
        'return' => false,
        'format' => 'd F Y'
    );

    extract( wp_parse_args($args, $defaults) );

    if ( $return ) return date($format, strtotime( $invoice['post_date'] ));
    echo date($format, strtotime( $invoice['post_date'] ));
  }
}

if ( !function_exists( 'wpi_get_business_logo_url' ) ) {
  /**
   * @return bool
   */
  function wpi_get_business_logo_url() {
    global $wpi_settings;
    return !empty( $wpi_settings['business_logo'] ) ? $wpi_settings['business_logo'] : false;
  }
}

if ( !function_exists( 'wpi_get_business_name' ) ) {
  /**
   * @return bool
   */
  function wpi_get_business_name() {
    global $wpi_settings;
    return apply_filters('wpi_business_name', !empty( $wpi_settings['business_name'] ) ? $wpi_settings['business_name'] : false );
  }
}

if ( !function_exists( 'wpi_get_business_address' ) ) {
  /**
   * @return bool
   */
  function wpi_get_business_address() {
    global $wpi_settings;
    return nl2br(strip_tags(apply_filters('wpi_business_address', !empty( $wpi_settings['business_address'] ) ? $wpi_settings['business_address'] : false)));
  }
}

if ( !function_exists( 'wpi_get_business_email' ) ) {
  /**
   * @return bool
   */
  function wpi_get_business_email() {
    global $wpi_settings;
    return !empty( $wpi_settings['email_address'] ) ? $wpi_settings['email_address'] : false;
  }
}

if ( !function_exists( 'wpi_get_business_phone' ) ) {
  /**
   * @return bool
   */
  function wpi_get_business_phone() {
    global $wpi_settings;
    return apply_filters('wpi_business_phone', !empty( $wpi_settings['business_phone'] ) ? $wpi_settings['business_phone'] : false);
  }
}

if ( !function_exists( 'wpi_get_invoice_issue_date' ) ) {
  /**
   * @return bool
   */
  function wpi_get_invoice_issue_date($format = false) {
    global $invoice;
    $format = $format ? $format : get_option('date_format');
    return !empty( $invoice['post_date'] ) ? date($format, strtotime($invoice['post_date'])) : false;
  }
}

if ( !function_exists( 'wpi_invoice_has_due_date' ) ) {
  /**
   * @return bool
   */
  function wpi_invoice_has_due_date() {
    global $invoice;
    return !empty($invoice['due_date_year']) && !empty($invoice['due_date_month']) && !empty($invoice['due_date_day']);
  }
}

if ( !function_exists( 'wpi_get_invoice_due_date' ) ) {
  /**
   * @param bool $format
   * @return bool|string|void
   */
  function wpi_get_invoice_due_date($format = false) {
    global $invoice;
    if ( empty($invoice['due_date_day']) || empty($invoice['due_date_month']) || empty($invoice['due_date_year']) ) {
      return __('Not set', ud_get_wp_invoice()->domain);
    }
    $format = $format ? $format : get_option('date_format');
    $strtime = sprintf("%s.%s.%s", $invoice['due_date_day'], $invoice['due_date_month'], $invoice['due_date_year']);
    return !empty($strtime) ? date($format, strtotime($strtime)) : __('Not set', ud_get_wp_invoice()->domain);
  }
}

if ( !function_exists('wpi_get_company_address') ) {
  /**
   * @return string|void
   */
  function wpi_get_company_address() {
    global $invoice;
    $address_parts = array();

    $address_parts[] = !empty($invoice['user_data']['company_name']) ? $invoice['user_data']['company_name'] : false;
    $address_parts[] = !empty($invoice['user_data']['streetaddress']) ? $invoice['user_data']['streetaddress'] : false;
    $address_parts[] = !empty($invoice['user_data']['city']) ? $invoice['user_data']['city'] : false;
    $address_parts[] = !empty($invoice['user_data']['country']) ? $invoice['user_data']['country'] : false;
    $address_parts[] = !empty($invoice['user_data']['state']) ? $invoice['user_data']['state'] : false;
    $address_parts[] = !empty($invoice['user_data']['zip']) ? $invoice['user_data']['zip'] : false;

    $address_parts = array_filter($address_parts);

    return !empty($address_parts) && is_array($address_parts) ? implode(', ', $address_parts) : '';
  }
}

if ( !function_exists('wpi_get_invoice_type') ) {
  /**
   * @return mixed
   */
  function wpi_get_invoice_type() {
    global $wpi_settings, $invoice;
    return !empty($wpi_settings['types'][$invoice['type']]) ? $wpi_settings['types'][$invoice['type']]['label'] : $invoice['type'];
  }
}

if ( !function_exists('wpi_invoice_has_pdf') ) {
  /**
   * @return bool
   */
  function wpi_invoice_has_pdf() {
    if (!class_exists('\UsabilityDynamics\WPI\WPI_PDF_Bootstrap')) return false;
    return true;
  }
}

if ( !function_exists('wpi_get_invoice_title') ) {
  /**
   * @return string|void
   */
  function wpi_get_invoice_title() {
    global $invoice;
    return !empty($invoice['post_title']) ? $invoice['post_title'] : __('Untitled', ud_get_wp_invoice()->domain);
  }
}

if ( !function_exists('wpi_invoice_has_items') ) {
  /**
   * @return bool
   */
  function wpi_invoice_has_items() {
    global $invoice;
    return !empty($invoice['itemized_list']) && is_array($invoice['itemized_list']);
  }
}

if ( !function_exists( 'wpi_invoice_has_charges' ) ) {
  /**
   * @return bool
   */
  function wpi_invoice_has_charges() {
    global $invoice;
    return !empty($invoice['itemized_charges']) && is_array($invoice['itemized_charges']);
  }
}

if ( !function_exists( 'wpi_get_line_item' ) ) {
  /**
   * @param $index
   * @return bool|PDF_Invoice_Item
   */
  function wpi_get_line_item(&$index) {
    global $invoice;
    $invoice['itemized_list'] = array_values($invoice['itemized_list']);
    if (!empty($invoice['itemized_list'][$index]) && is_array($invoice['itemized_list'][$index])) {
      return new \UsabilityDynamics\WPI\LineItem($invoice['itemized_list'][$index++]);
    }
    return false;
  }
}

if ( !function_exists( 'wpi_get_line_charge' ) ) {
  /**
   * @param $index
   * @return bool|\UsabilityDynamics\WPI\LineItem
   */
  function wpi_get_line_charge(&$index) {
    global $invoice;
    $invoice['itemized_charges'] = array_values($invoice['itemized_charges']);
    if (!empty($invoice['itemized_charges'][$index]) && is_array($invoice['itemized_charges'][$index])) {
      return new \UsabilityDynamics\WPI\LineCharge($invoice['itemized_charges'][$index++]);
    }
    return false;
  }
}

if ( !function_exists( 'wpi_get_invoice_currency_sign' ) ) {
  /**
   * @return mixed
   */
  function wpi_get_invoice_currency_sign() {
    global $wpi_settings, $invoice;
    return !empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : $wpi_settings['currency']['symbol'][$wpi_settings['currency']['default_currency_code']];
  }
}

if ( !function_exists( 'wpi_get_invoice_total_tax' ) ) {
  /**
   * @param string $currency_sign
   * @return bool|string
   */
  function wpi_get_invoice_total_tax($currency_sign = '$') {
    global $invoice;
    return !empty($invoice['total_tax']) && $invoice['total_tax'] > 0 ? sprintf("$currency_sign%s", wp_invoice_currency_format($invoice['total_tax'])) : false;
  }
}

if ( !function_exists( 'wpi_show_quantity_column' ) ) {
  /**
   * @return bool
   */
  function wpi_show_quantity_column() {
    global $wpi_settings;
    return $wpi_settings['globals']['show_quantities'] == 'true' ? true : false;
  }
}

if ( !function_exists( 'wpi_get_total' ) ) {
  /**
   * @param string $currency_sign
   * @return int|string
   */
  function wpi_get_total($currency_sign = '$') {
    global $invoice;
    return !empty($invoice['subtotal']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($invoice['subtotal'])) : 0;
  }
}

if ( !function_exists( 'wpi_get_discount' ) ) {
  /**
   * @param string $currency_sign
   * @return int|string
   */
  function wpi_get_discount($currency_sign = '$') {
    global $invoice;
    return !empty($invoice['total_discount']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($invoice['total_discount'])) : 0;
  }
}

if ( !function_exists( 'wpi_get_adjustments' ) ) {
  /**
   * @param string $currency_sign
   * @return int|string
   */
  function wpi_get_adjustments( $currency_sign = '$' ) {
    global $invoice;
    if (!isset($invoice['adjustments']))$invoice['adjustments']=0;
    $adjustments = (float)$invoice['adjustments'] + (float)$invoice['total_payments'];
    return !empty($adjustments) ? sprintf("$currency_sign%s", wp_invoice_currency_format($adjustments)) : 0;
  }
}

if ( !function_exists( 'wpi_get_total_payments' ) ) {
  /**
   * @param string $currency_sign
   * @return int|string
   */
  function wpi_get_total_payments($currency_sign = '$') {
    global $invoice;
    return !empty($invoice['total_payments']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($invoice['total_payments'])) : 0;
  }
}

if ( !function_exists( 'wpi_get_amount_due' ) ) {
  /**
   * @param string $currency_sign
   * @return string
   */
  function wpi_get_amount_due($currency_sign = '$') {
    global $invoice;
    if ($invoice['post_status'] == 'refund') {
      return !empty($invoice['net']) ? sprintf("<s>$currency_sign%s</s>", wp_invoice_currency_format($invoice['net'])) : '-';
    }
    return !empty($invoice['net']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($invoice['net'])) : '-';
  }
}

if ( !function_exists('wpi_get_invoice_log') ) {
  /**
   * @param array $actions
   * @return array|bool
   */
  function wpi_get_invoice_log($actions = array()) {
    global $invoice, $wpi_settings;

    if ( empty($invoice['log']) || !is_array($invoice['log']) ) return false;

    if ( !current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) return false;

    $log = array();
    foreach( $invoice['log'] as $log_item ) {
      if ( array_key_exists( $log_item['action'], $actions ) ) {
        $log[] = array(
          'label' => $actions[$log_item['action']],
          'action' => $log_item['action'],
          'text' => $log_item['text'],
          'time' => date('d M Y, g:i A', $log_item['time'] + get_option( 'gmt_offset' ) * 60 * 60)
        );
      }
    }

    $log = array_reverse($log);

    return !empty($log)?$log:false;
  }
}

if ( !function_exists('wpi_user_can_view_dashboard') ) {
  /**
   * @return bool
   */
  function wpi_user_can_view_dashboard() {
    /**
     * Always true for logged in users
     */
    if ( is_user_logged_in() ) return true;

    /**
     * Otherwise check for wpi_token
     */
    if ( empty( $_GET['wpi_token'] ) || empty( $_GET['wpi_user_id'] ) ) return false;

    /**
     * Get user data by passed ID
     */
    $user = get_user_by('id', (int)$_GET['wpi_user_id']);
    if ( !is_a($user, 'WP_User') ) return false;

    $token_to_check = md5( $user->ID.$user->user_email. ( defined( 'AUTH_SALT' ) ? AUTH_SALT : '' ) );

    if ( $token_to_check == $_GET['wpi_token'] ) return true;

    return false;
  }
}

if ( !function_exists('wpi_get_dashboard_permalink') ) {
  /**
   * @param $invoice_id
   * @return string
   */
  function wpi_get_dashboard_permalink( $invoice_id ) {
    if ( empty( $invoice_id ) ) return '#';

    /**
     * Get Invoice information
     */
    $invoice_data = new WPI_Invoice();
    $invoice_data->load_invoice(array('id'=>$invoice_id));
    if ( empty($invoice_data->data) || empty($invoice_data->data['user_data']) ) return '#';

    /**
     * Generate link to dashboard
     */
    $wpi_token = md5( $invoice_data->data['user_data']['ID'].$invoice_data->data['user_data']['user_email']. ( defined( 'AUTH_SALT' ) ? AUTH_SALT : '' ) );

    global $wpi_settings;
    if ( get_option( "permalink_structure" ) ) {
      return get_permalink( $wpi_settings[ 'web_dashboard_page' ] ) . "?wpi_user_id=" . $invoice_data->data['user_data']['ID'] . "&wpi_token=" . $wpi_token;
    } else {
      //** check if page is on front-end */
      if ( get_option( 'page_on_front' ) == $wpi_settings[ 'web_invoice_page' ] ) {
        return get_permalink( $wpi_settings[ 'web_dashboard_page' ] ) . "?wpi_user_id=" . $invoice_data->data['user_data']['ID'] . "&wpi_token=" . $wpi_token;
      } else {
        return get_permalink( $wpi_settings[ 'web_dashboard_page' ] ) . "&wpi_user_id=" . $invoice_data->data['user_data']['ID'] . "&wpi_token=" . $wpi_token;
      }
    }
  }
}

if ( !function_exists('wpi_get_default_currency_sign') ) {
  /**
   * @return mixed
   */
  function wpi_get_default_currency_sign() {
    global $wpi_settings;
    return $wpi_settings['currency']['symbol'][$wpi_settings['currency']['default_currency_code']];
  }
}

if ( !function_exists('wpi_dashboard_is_active') ) {
  /**
   * @return bool
   */
  function wpi_dashboard_is_active() {
    global $wpi_settings;

    if ( empty( $wpi_settings['web_dashboard_page'] ) || !get_post($wpi_settings['web_dashboard_page']) ) return false;
    return !empty( $wpi_settings['activate_client_dashboard'] ) ? ( $wpi_settings['activate_client_dashboard'] == 'true' ? true : false ) : false;
  }
}

if ( !function_exists( 'wpi_get_client_dashboard_company_name' ) ) {
  /**
   * @return array
   */
  function wpi_get_client_dashboard_company_name() {
    if ( is_user_logged_in() ) {
      $user_data = wp_get_current_user();
    } else {
      if ( !empty( $_GET['wpi_user_id'] ) ) {
        $user = get_user_by( 'id', $_GET['wpi_user_id'] );
        if ( !is_a( $user, 'WP_User' ) ) {
          return __( 'Unknown Client', ud_get_wp_invoice()->domain );
        } else {
          $user_data = $user;
        }
      } else {
        return __('Unknown Client', ud_get_wp_invoice()->domain);
      }
    }

    $user_data_array = array();
    $user_name = array();

    if ( !empty( $user_data->user_firstname ) ) {
      $user_name[] = $user_data->user_firstname;
    }
    if ( !empty( $user_data->user_lastname ) ) {
      $user_name[] = $user_data->user_lastname;
    }

    if ( !empty($user_name) ) {
      $user_data_array['name'] = implode(' ', $user_name);
    }
    $company_name = get_user_meta( $user_data->ID, 'company_name', 1 );
    if ( !empty( $company_name ) ) {
      $user_data_array['company'] = $company_name;
    }

    if ( empty($user_data_array) ) {
      $user_data_array[] = $user_data->user_email;
    }

    return implode(', ', $user_data_array);
  }
}
