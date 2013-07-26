<?php

/**
 * Show URL of invoice
 * @global array $invoice
 */
function invoice_permalink() {
  global $invoice;
  echo get_invoice_permalink($invoice['invoice_id']);
}

/**
 * Show PDF link of invoice
 * @global array $invoice
 */
function invoice_pdf_link() {
  global $invoice;
  echo get_invoice_permalink($invoice['invoice_id']) . "&format=pdf";
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
function show_itemized_table($args = '') {
  global $invoice, $wpi_settings;

  $defaults = array('return' => false, 'item_heading' => __("Item", WPI), 'cost_heading' => __("Cost", WPI), 'show_quantities' => false, 'quantity_heading' => __('Quantity', WPI));

  extract(wp_parse_args($args, $defaults), EXTR_SKIP);

  // If hide_quantity is not passed by function, we referr to global setting
  if (!$show_quantities) {
    $show_quantities = ($wpi_settings['globals']['show_quantities'] == 'true' ? true : false);
  }

  $currency_symbol = (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$");

  ob_start();
  if (allow_partial_payments()) {
    ?>
    <script type="text/javascript">
      // Partial payments JS
      // @author Anton Korotkov
      var minimum_payment = <?php echo $invoice['deposit_amount'] ?>;
      var balance         = <?php echo $invoice['net'] ?>;
      jQuery(document).ready(function(){
        var validate_amount = function(amount) {
          amount = Math.abs( parseFloat( amount ) );
          //alert( amount );
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
        // Find fields
        var payment_amount        = jQuery("#payment_amount");
        var my_amount             = jQuery("#my_amount");
        // Find radios
        var custom_amount_option  = jQuery("#wpi_custom_amount_option");
        var minimum_amount_option = jQuery("#wpi_minimum_amount_option");
        var full_amount_option    = jQuery("#wpi_full_amount_option");
        var custom_amount_field = jQuery("#wpi_custom_amount_option_field_wrapper");
        my_amount.live("focus", function(){
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
        // Handle changing of payment method
        jQuery("#online_payment_form_wrapper").live("formLoaded", function(){
          payment_amount = jQuery("#payment_amount");
          my_amount      = jQuery("#my_amount");
          // update field data
          if ( custom_amount_option.is(":checked") ) {
            payment_amount.val( validate_amount( my_amount.val() ) );
          }
          if ( minimum_amount_option.is(":checked") ) {
            payment_amount.val( validate_amount( minimum_amount_option.val() ) );
          }
          set_pay_button_value();
        });
        // If there are required fields
        if ( payment_amount.length && my_amount.length ) {
          // update field data
          my_amount.live("keyup", function(){
            var new_value = my_amount.val();
            payment_amount.val( validate_amount( new_value ) );
            set_pay_button_value();
          });
          my_amount.live("blur", function(){
            my_amount.val( payment_amount.val() );
            set_pay_button_value();
          });
          my_amount.live("focus", function(){
            my_amount.val( payment_amount.val() );
            set_pay_button_value();
          });
        } else {
          alert( "<?php _e('Partial payment is not available because of an error.\nContact Administirator for more information.', WPI) ?>" );
        }
      });
    </script>
    <?php
  }
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
            <th class="title_column"><?php _e('Charges', WPI) ?></th>
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
        // Figure out what colspan is based on how many columns we have
        $colspan = $show_quantities ? 'colspan="2"' : '';
        if (!empty($invoice['subtotal'])):
          ?>
          <tr class="wpi_subtotal">
            <td class="bottom_line_title" <?php echo $colspan; ?>>
              <?php _e('Subtotal:', WPI) ?>
            </td>
            <td class="wpi_money">
          <?php echo $currency_symbol . wp_invoice_currency_format($invoice['subtotal']); ?></td>
          </tr>
    <?php endif; ?>
    <?php if (!empty($invoice['total_tax'])): ?>
          <tr class="wpi_subtotal">
            <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Tax:', WPI) ?></td>
            <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['total_tax']); ?></td>
          </tr>
    <?php endif; ?>
    <?php if (!empty($invoice['total_discount'])): ?>
          <tr class="wpi_subtotal">
            <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Discounts:', WPI) ?></td>
            <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['total_discount']); ?></td>
          </tr>
    <?php endif; ?>
    <?php if ($invoice['post_status'] != 'paid' && !empty($invoice['adjustments'])): ?>
          <tr class="wpi_subtotal">
            <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Adjustments:', WPI) ?></td>
            <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['adjustments']); ?></td>
          </tr>
    <?php endif; ?>
    <?php if ($invoice['post_status'] == 'paid' && !empty($invoice['total_payments'])): ?>
          <tr class="wpi_subtotal">
            <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Received Payment:', WPI) ?></td>
            <td class="wpi_money"><?php echo $currency_symbol . wp_invoice_currency_format($invoice['total_payments']); ?></td>
          </tr>
    <?php endif; ?>
    <?php if (!empty($invoice['net'])): ?>
          <tr class="wpi_subtotal">
            <td class="bottom_line_title" <?php echo $colspan; ?>><?php _e('Balance:', WPI) ?></td>
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
  $result .= ob_get_contents();
  ob_end_clean();
  if ($return)
    return $result;
  echo $result;
}

/**
 * Display invoice history
 * @global array $invoice
 */
function show_invoice_history() {
  global $invoice;
  echo '<b class="wpi_greeting">Log</b>';
  if (!empty($invoice['log']) && is_array($invoice['log'])) {
    ?>
    <table class="invoice_history">
      <thead>
        <tr>
          <th><?php _e('Time', WPI); ?></th>
          <th><?php _e('Event', WPI); ?></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($invoice['log'] as $key => $value) : ?>
          <?php if ($value['action'] == 'create') : ?>
            <tr class="invoice-history-item">
              <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
              <td class="description"><?php echo $value['text']; ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($value['action'] == 'add_payment') : ?>
            <?php
            $by = '';
            if ($value['user_id'] != 0) {
              $user = get_user_by('id', $value['user_id']);
              $by = " by " . $user->display_name;
            }
            ?>
            <tr class="invoice-history-item">
              <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
              <td class="description"><?php echo $value['text'] . $by; ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($value['value'] == 'paid') : ?>
            <tr class="invoice-history-item">
              <td class="time"><?php echo date(get_option('date_format'), $value['time']) ?></td>
              <td class="description"><?php echo $value['text']; ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($value['action'] == 'refund') : ?>
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
    echo __('There are no any actions', WPI);
  }
}

/**
 * Determine if partial payment are available
 * @global array $invoice
 * @param mixed $args
 * @return bool
 */
function allow_partial_payments($args = '') {
  global $invoice;

  if (!empty($invoice['deposit_amount']) && $invoice['deposit_amount'] > 0 && $invoice['net'] > $invoice['deposit_amount']) {
    return true;
  }

  return false;
}

/**
 * Show payment switcher
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 */
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

          <li class="section_title"><?php _e('Payment Amount', WPI); ?></li>

          <li class="wpi_checkout_row">
            <label for="wpi_minimum_amount_option"><?php _e("Min. Payment Due:", WPI); ?></label>
            <input type="radio" name="payment_amount" id="wpi_minimum_amount_option" value="<?php echo $invoice['deposit_amount']; ?>" />
            <span><?php echo $currency_symbol . wp_invoice_currency_format($invoice['deposit_amount']); ?></span>
          </li>

          <li class="wpi_checkout_row">
            <label for="wpi_full_amount_option"><?php _e("Statement Balance:", WPI); ?></label>
            <input checked="checked" type="radio" name="payment_amount" id="wpi_full_amount_option" value="<?php echo $invoice['net']; ?>" />
            <span><?php echo $currency_symbol . wp_invoice_currency_format($invoice['net']); ?></span>
          </li>

          <li class="wpi_checkout_row">
            <label for="wpi_custom_amount_option"><?php _e("Other", WPI); ?></label>
            <input type="radio" name="payment_amount" id="wpi_custom_amount_option"  value="<?php echo wp_invoice_currency_format($invoice['net']); ?>" />

            <span id="wpi_custom_amount_option_field_wrapper"><?php echo $currency_symbol; ?>
              <input class="text-input small" id="my_amount" name="my_amount" type="text" value="<?php echo wp_invoice_currency_format($invoice['net']); ?>" />
            </span>
          </li>

        </ul>
        <small class="notice"><?php echo apply_filters('wpi_show_partial_payments_message', __('This invoice allows partial payments, please select the amount you would like to pay.', WPI)); ?></small>
      </div>
    </form>
  <?php
  endif;
}

/**
 * Display payment method select
 *
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 * @return mixed
 */
function show_payment_selection($args = '') {
  global $invoice, $wpi_settings;

  $defaults = array(
    'title' => __("Payment Method", WPI)
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

/**
 * Show term acceptance checkbox
 *   label= will insert label
 *   force=will override invoice setting for terms acceptance, and show acceptance
 *   pade_id = page id of terms acceptance page
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
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

/**
 * Show amount owed.
 *  hide_currency=true will not automatically insert currency.
 * @global array $invoice
 * @global array $wpi_settings
 * @param mixed $args
 * @return mixed
 */
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

/**
 * Show invoice description
 * Filter Applied: 'wpi_description'
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
function the_description($args = '') {
  global $invoice;

  $defaults = array('return' => false);
  extract(wp_parse_args($args, $defaults), EXTR_SKIP);

  if (empty($invoice['post_content']))
    return;

  $result = apply_filters('wpi_description', $invoice['post_content']);

  if ($return) {
    return $result;
  }

  echo $result;
}

/**
 * Show invoice id.
 *   force_original=true will display the actual ID, even if custom ID is set
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
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

/**
 * Display recipients name
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
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

/**
 * Paid or not
 * @global array $invoice
 * @return bool
 */
function is_paid() {
  global $invoice;
  return $invoice['post_status'] == 'paid';
}

/**
 * Pending or not
 * @global array $invoice
 * @return bool
 */
function is_pending() {
  global $invoice;
  return $invoice['post_status'] == 'pending';
}

/**
 * Return paid date
 * Filter Applied: 'wpi_date_paid_format'
 * @global array $invoice
 * @return string
 */
function date_paid() {
  global $invoice;
  return date(apply_filters('wpi_date_paid_format', 'd F Y, \o\n H:i'), get_post_modified_time('U', false, $invoice['ID']));
}

/**
 * Returns true if any payments have been made at all.
 * @global array $invoice
 * @return bool
 */
function is_payment_made() {
  global $invoice;
  if (!empty($invoice['total_payments'])) {
    return $invoice['total_payments'] > 0 && $invoice['total_payments'] < $invoice['subtotal'];
  }
  return false;
}

/**
 * PayPal is allowed or not
 * @global array $invoice
 * @return bool
 */
function is_paypal_allowed() {
  global $invoice;

  if ($invoice['billing']['paypal']['allow'] == 'true')
    return true;

  return false;
}

/**
 * Renders paid amount
 * @global array $invoice
 * @global array $wpi_settings
 */
function paid_amount() {
  global $invoice, $wpi_settings;

  $currency_symbol = (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$");
  echo $currency_symbol . wp_invoice_currency_format(!empty($invoice['total_payments']) ? $invoice['total_payments'] : 0 );
}

/**
 * Is quote or not
 * @global array $invoice
 * @return bool
 */
function is_quote() {
  global $invoice;

  return !empty($invoice['is_quote']);
}

/**
 * Determines is this invoice is a single invoice, and not a quote, nor a recurring bill.
 * @global array $invoice
 * @return bool
 */
function is_invoice() {
  global $invoice;

  return $invoice['type'] == 'invoice' ? true : false ;
}

/**
 * Determines is this is a recurring bill.
 * @global array $invoice
 * @return bool
 */
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

/**
 * Show business info or not
 * @return bool
 */
function show_business_info() {
  $core = WPI_Core::getInstance();
  return $core->Settings->options['globals']['show_business_address'] == 'false' ? FALSE : TRUE;
}

/**
 * Render Invoice Due Date
 * @global array $invoice
 * @param mixed $args
 * @return mixed
 */
function wpi_invoice_due_date( $args = "" ) {
  global $invoice;

  $defaults = array(
      'return' => false,
      'text'   => __('Due Date: ', WPI),
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