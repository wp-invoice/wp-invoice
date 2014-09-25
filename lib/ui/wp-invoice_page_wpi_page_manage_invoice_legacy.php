<script type="text/javascript">

  var wpi = {
    'currency':'<?php echo $wpi_settings['currency']['symbol'][$this_invoice->data['default_currency_code']]; ?> ',
    'thousandsSeparator':'<?php echo !isset( $wpi_settings['thousands_separator_symbol'] )?',':($wpi_settings['thousands_separator_symbol'] == '0'?'':$wpi_settings['thousands_separator_symbol']) ?>'
  };

  var adjustments = <?php echo !empty( $this_invoice->data['adjustments'] )?$this_invoice->data['adjustments']:0; ?>;

  <?php if(!empty($this_invoice->data['meta']['is_recurring'])) { ?>

  var is_recurring = true;

  <?php } else { ?>

  var is_recurring = false;

  <?php } ?>

  jQuery(document).ready( function() {

    wpi_toggle_wpi_event_type();

    jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

    postboxes.add_postbox_toggles('<?php echo $screen_id; ?>');

    wpi_recalc_totals();

  });

  jQuery(window).load(function(){
    if ( jQuery('#wp_invoice_tax').val() ) {
      jQuery('#wp_invoice_tax').trigger('keyup');
    }
  });

</script>

<style>

  <?php if(!empty($this_invoice->data['allow_deposits'])):  ?>

    .wpi_deposit_settings {}

  <?php else: ?>

    .wpi_deposit_settings {display:none;}

  <?php endif; ?>

  <?php if(!empty($this_invoice->data['is_quote'])) { ?>

    .wpi_turn_off_recurring{display:none;}

    .wpi_not_for_quote {display:none;}

  <?php } ?>

  <?php if(!empty($this_invoice->data['is_recurring'])) { ?>

    .wpi_not_for_recurring {display:none;}

  <?php } else { ?>

    .wpi_recurring_options {display:none;}

  <?php } ?>

  <?php if(!empty($this_invoice->data['new_invoice'])) { ?>

  .wpi_hide_until_saved {display:none;}

  <?php }

  // Toggle inline tax CSS

  if(get_user_option('wpi_ui_display_itemized_tax') != 'true') { ?>

  .row_tax {display:none;}

  .header .flexible_width_holder_content, .wp_invoice_itemized_list_row .flexible_width_holder_content, .wp_invoice_itemized_charge_row .flexible_width_holder_content { margin-right: 250px;}

  .header .fixed_width_holder, .wp_invoice_itemized_list_row .fixed_width_holder, .wp_invoice_itemized_charge_row .fixed_width_holder { width: 240px; }

  <?php } else { ?>

  .row_tax {display:inline;}

  .header .flexible_width_holder_content, .wp_invoice_itemized_list_row .flexible_width_holder_content, .wp_invoice_itemized_charge_row .flexible_width_holder_content { margin-right: 300px;}

  .header .fixed_width_holder, .wp_invoice_itemized_list_row .fixed_width_holder, .wp_invoice_itemized_charge_row .fixed_width_holder { width: 280px; }

  <?php }

  // Toggle global tax CSS display

  if(get_user_option('wpi_ui_display_global_tax') != 'true') { ?>

  tr.wpi_ui_display_global_tax {display:none;}

  <?php }  ?>

  <?php

  if(get_user_option('wpi_ui_currency_options') != 'true') { ?>

  tr.wpi_ui_currency_options {display:none;}

  <?php }  ?>

  <?php

  if(get_user_option('wpi_ui_payment_method_options') != 'true') { ?>

  tr.wpi_ui_payment_method_options {display:none;}

  <?php }  ?>

  #post-body .wp_themeSkin .mceStatusbar a.mceResize {top:0;}

</style>

<div id="wpi_manage_page" class="wrap <?php echo (!empty($this_invoice->data['post_status']) ? 'wpi_invoice_status_' . $this_invoice->data['post_status'] : ''); ?>">

<?php screen_icon("wpi"); ?>

<h2>

  <span id="wpi_page_title"><?php _e((empty($this_invoice->data['ID']) ? 'New Invoice' : 'Edit Invoice'), ud_get_wp_invoice()->domain); ?></span>

  <input type="button" class="wpi_hide_until_saved button add-new-h2" onclick="wpi_show_paycharge_box();" value="<?php esc_attr(_e('Add Payment / Charge', ud_get_wp_invoice()->domain)) ?>" />

</h2>

<?php if( !empty($this_invoice->data['post_status']) && $this_invoice->data['post_status'] == 'paid') { ?>

  <div class="wpi_invoice_paid">

    <?php echo apply_filters('wpi_object_paid_message', __('Invoice paid in full.', ud_get_wp_invoice()->domain), $this_invoice); ?>

  </div>

<?php } ?>

<form id="wpi_invoice_form" action="" method="post">

    <?php if ( !empty($notice) ) : ?>

        <div id="notice" class="error"><p><?php echo $notice ?></p></div>

    <?php endif; ?>

    <?php if (isset($_GET['message'])) : ?>

        <div id="message" class="updated fade"><p><?php echo $messages[$_GET['message']]; ?></p></div>

    <?php endif; ?>

    <div id="ajax-response" class="updated fade below-h2" id="message"><p></p></div>

    <?php

    echo WPI_UI::input("id=wpi_post_id&type=hidden&name=wpi_invoice[ID]&value=".(!empty($this_invoice->data['ID'])? $this_invoice->data['ID'] : 0));

    echo WPI_UI::input("id=wpi_invoice_id&type=hidden&name=wpi_invoice[invoice_id]&value=".(!empty($this_invoice->data['invoice_id'])?$this_invoice->data['invoice_id']:''));

    echo WPI_UI::input("type=hidden&name=wp_invoice_action&value=wpi_save_and_preview");

    echo WPI_UI::input("type=hidden&name=referredby&value=".esc_url(stripslashes(wp_get_referer())));

    wp_nonce_field( 'wpi-update-invoice', 'wpi-update-invoice', false );

    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

    // displays information if the invoice has been paid, a payment schedule has been started, etc.  Basic log is displayed on bottom as before

    ?>

<div id="poststuff" class="crm-wp-less-v34 metabox-holder<?php echo 2 == get_user_option("screen_layout_admin_page_wpi_invoice_edit") ? ' has-right-sidebar' : ''; ?>">

  <div id="side-info-column" class="inner-sidebar">

    <?php

    do_action('submitpage_box');

    $side_meta_boxes = do_meta_boxes($screen_id, 'side', !empty( $this_invoice->data )?$this_invoice->data:null );

    ?>

  </div>

  <div id="post-body" class="<?php echo $side_meta_boxes ? 'has-sidebar' : 'has-sidebar'; ?>">

  <div id="post-body-content">

  <?php

  // Always included, but hidden if the invoice is not yet saved

  status_meta_box( !empty( $this_invoice->data )?$this_invoice->data:null );

  ?>

  <div id="titlediv">

    <div id="titlewrap">

      <?php echo WPI_UI::input("id=title&name=wpi_invoice[subject]&value=".(!empty( $this_invoice->data['post_title'] )?$this_invoice->data['post_title']:'')."&special autocomplete='off'")?>

      <?php echo WPI_UI::input("id=title&name=wpi_invoice[post_status]&value=".(!empty( $this_invoice->data['post_status'] )?$this_invoice->data['post_status']:'')."&type=hidden")?>

    </div>

    <div class="inside">

      <div id="edit-slug-box" class="wpi-edit-slug-box" title="<?php _e('Click to view full link',ud_get_wp_invoice()->domain); ?>"><strong><?php _e('Invoice Link',ud_get_wp_invoice()->domain); ?>:</strong>

        <span id="sample-permalink"><?php echo get_invoice_permalink( !empty($this_invoice->data['invoice_id'])?$this_invoice->data['invoice_id']:'' ); ?></span>

      </div>

    </div>

  </div>

  <?php

  // Fixed Metaboxes

  // Always included but hidden until button is pressed

  message_meta_box( !empty( $this_invoice->data )?$this_invoice->data:null );

    if(!empty($new_user)) {

      postbox_user_new($this_invoice->data);

    }

  ?>

  <div id="poststuff" class="postarea">

    <?php the_editor( !empty( $this_invoice->data['post_content'] )?$this_invoice->data['post_content']:'', 'content', 'title', false); ?>

  </div>

  <?php

    $hidden = '';

    if ( empty( $this_invoice->data['itemized_charges'] ) ) {

      $hidden = 'hidden';

    }

  ?>

  <ul id="charges_list" class="itemized_list <?php echo $hidden ?>">

    <li class="header clearfix">

      <span class="name"><?php _e("Charge Name", ud_get_wp_invoice()->domain) ?></span>

      <span class="fixed_width_holder">

        <span style="margin: 0; width: 10px; float: left;">&nbsp;</span>

        <span class="row_amount"><?php _e("Amount", ud_get_wp_invoice()->domain) ?></span>

        <span class="row_charge_tax"><?php _e("Tax", ud_get_wp_invoice()->domain) ?>&nbsp;%</span>

        <span class="row_total"><?php _e("Total", ud_get_wp_invoice()->domain) ?></span>

      </span>

    </li>

    <?php

      if ( !empty( $this_invoice->data['itemized_charges'] ) ) {

        foreach ( $this_invoice->data['itemized_charges'] as $key => $value ) {

    ?>

    <li class="wp_invoice_itemized_charge_row clearfix" id="wp_invoice_itemized_charge_row_<?php echo $key; ?>">

      <span class="id hidden"><?php echo $key; ?></span>

      <div class="flexible_width_holder">

        <div class="flexible_width_holder_content">

          <span class="row_delete">&nbsp;</span>

          <input class="item_name input_field" name="wpi_invoice[itemized_charges][<?php echo $key; ?>][name]" value="<?php echo stripslashes($value['name']); ?>" />

        </div>

      </div>

      <span class="fixed_width_holder">

        <span class="row_amount">

          <input autocomplete="off" value="<?php echo stripslashes($value['amount']); ?>" name="wpi_invoice[itemized_charges][<?php echo $key; ?>][amount]" id="amount_item_<?php echo $key; ?>"  class="item_amount input_field">

        </span>

        <span class="row_charge_tax">

          <input autocomplete="off" value="<?php echo stripslashes($value['tax']); ?>"  name="wpi_invoice[itemized_charges][<?php echo $key; ?>][tax]" id="charge_tax_item_<?php echo $key; ?>"  class="item_charge_tax input_field">

        </span>

        <span class="row_total" id="total_item_<?php echo $key; ?>" ><?php echo $value['after_tax']; ?></span>

      </span>

    </li>

    <?php

        }

      }

    ?>

  </ul>

    <ul id="invoice_list" class="itemized_list clearfix">

    <li class="header clearfix">

      <span class="name"><?php _e("Name", ud_get_wp_invoice()->domain) ?></span>

      <span class="fixed_width_holder">

        <span style="margin: 0; width: 10px; float: left;">&nbsp;</span>

        <span class="row_quantity"><?php _e("Qty.", ud_get_wp_invoice()->domain) ?></span>

        <span class="row_price"><?php _e("Price", ud_get_wp_invoice()->domain) ?></span>

        <span class="row_tax column-invoice-details-itemized-list-tax"><?php _e("Tax", ud_get_wp_invoice()->domain) ?>&nbsp;%</span>

        <span class="row_total"><?php _e("Total", ud_get_wp_invoice()->domain) ?></span>

      </span>

    </li>

    <?php

    if( empty($this_invoice->data['itemized_list']) || !is_array($this_invoice->data['itemized_list']))  {

      $blank_rows = (get_user_option("wpi_blank_item_rows") ? get_user_option("wpi_blank_item_rows") : 2);

      $x = 1;

      while($x <= $blank_rows) {

        $this_invoice->data['itemized_list'][$x++] = true;

      }

    }

    $counter = 1;

    foreach((array)$this_invoice->data['itemized_list'] as $itemized_item){ ?>

    <li class="wp_invoice_itemized_list_row clearfix" id="wp_invoice_itemized_list_row_<?php echo $counter; ?>">

      <span class="id hidden"><?php echo $counter; ?></span>

      <div class="flexible_width_holder">

        <div class="flexible_width_holder_content">

          <span class="row_delete">&nbsp;</span>

          <input class="item_name input_field" name="wpi_invoice[itemized_list][<?php echo $counter; ?>][name]" value="<?php echo stripslashes($itemized_item['name']); ?>" />

          <span class="wpi_add_description_text">&nbsp;<span class="content"><?php _e('Toggle Description', ud_get_wp_invoice()->domain) ?></span></span>

        </div>

      </div>

      <span class="fixed_width_holder">

        <span class="row_quantity"><input autocomplete="off"  value="<?php echo stripslashes($itemized_item['quantity']); ?>" name="wpi_invoice[itemized_list][<?php echo $counter; ?>][quantity]" id="qty_item_<?php echo $counter; ?>"  class="item_quantity input_field"></span>

        <span class="row_price"><input autocomplete="off" value="<?php echo stripslashes($itemized_item['price']); ?>"  name="wpi_invoice[itemized_list][<?php echo $counter; ?>][price]" id="price_item_<?php echo $counter; ?>"  class="item_price input_field"></span>

        <span class="row_tax column-invoice-details-itemized-list-tax"><input autocomplete="off" value="<?php echo stripslashes($itemized_item['tax_rate']); ?>" class="line_tax_item input_field"  name="wpi_invoice[itemized_list][<?php echo $counter; ?>][tax]"></span>

        <span class="row_total" id="total_item_<?php echo $counter; ?>" ></span>

      </span>

      <?php

      if(empty($itemized_item['description'])) {

        $item_description_style = 'none';

      } else {

        $item_description_style = 'block';

      }

      ?>

      <textarea style="display: <?php echo $item_description_style; ?>" name="wpi_invoice[itemized_list][<?php echo $counter; ?>][description]" class="item_description"><?php echo stripslashes($itemized_item['description']); ?></textarea>

    </li>

    <?php $counter++;

    }

    // Setup discounts, adding one blank one if none exist

    if( empty($this_invoice->data['discount']) || !is_array($this_invoice->data['discount']))  {

      $no_discounts = true;

      $this_invoice->data['discount'][1] = true;

    }

    $discount_types = serialize(array('amount' => __('Amount Discount', ud_get_wp_invoice()->domain), 'percent' => __('Percent Discount', ud_get_wp_invoice()->domain)));

    ?>

    <?php $counter = 1;

    foreach($this_invoice->data['discount'] as $key => $discount_item){ ?>

        <li style="<?php if($no_discounts) echo "display:none"; ?>" class="wp_invoice_discount_row clearfix" id="wp_invoice_discount_row_<?php echo $counter; ?>">

          <span class="id hidden"><?php echo $counter; ?></span>

          <div class="flexible_width_holder">

            <div class="flexible_width_holder_content" style="margin-right:250px;">

              <span class="row_delete">&nbsp;</span>

              <?php echo WPI_UI::input("class=item_name&name=wpi_invoice[meta][discount][$counter][name]&value={$discount_item['name']}")?>

            </div>

          </div>

          <span class="fixed_width_holder">

            <span class="item_type"><?php echo WPI_UI::select("name=wpi_invoice[meta][discount][$counter][type]&values=$discount_types&current_value={$discount_item['type']}&class=item_type"); ?></span>

            <span class="item_price"><?php echo WPI_UI::input("name=wpi_invoice[meta][discount][$counter][amount]&value={$discount_item['amount']}&class=item_amount")?></span>

          <?php /**  <span class="row_total" id="total_item_<?php echo $counter; ?>" ></span> */ ?>

          </span>

        </li>

    <?php $counter++;

    } ?>

    <li class="wpi_invoice_totals clearfix">

      <dl>

        <dt class="hidden column-invoice-details-subtotal"><?php _e("Subtotal Excluding Tax:", ud_get_wp_invoice()->domain) ?></dt>

        <dd class="hidden column-invoice-details-subtotal"><input class="calculate_invoice_subtotal wpi_no_input" disabled='true' value="<?php echo !empty( $this_invoice->data['subtotal'] ) ? $this_invoice->data['subtotal'] : ''; ?>"/></dd>

        <dt class="hidden column-invoice-details-adjustments" ><?php _e("Adjustments:", ud_get_wp_invoice()->domain) ?></dt>

        <dd class="hidden column-invoice-details-adjustments" ><input class="calculate_invoice_adjustments wpi_no_input" disabled='true' value="<?php echo !empty( $this_invoice->data['adjustments'] ) ? $this_invoice->data['adjustments'] : '';?>"/></dd>

        <dt class="hidden column-invoice-details-discounts"><?php _e("Discount:", ud_get_wp_invoice()->domain) ?></dt>

        <dd class="hidden column-invoice-details-discounts"><input class="wpi_no_input calculate_discount_total" disabled='true' value="<?php echo !empty( $this_invoice->data['total_discount'] ) ? $this_invoice->data['total_discount'] : '';?>"/></dd>

        <dt class="hidden column-invoice-details-tax"><?php _e("Sales Tax:", ud_get_wp_invoice()->domain) ?></dt>

        <dd class="hidden column-invoice-details-tax"><input class="calculate_invoice_tax wpi_no_input" disabled='true' value="<?php echo !empty( $this_invoice->data['total_tax'] ) ? $this_invoice->data['total_tax'] : '';?>"/></dd>

        <dt><b><?php _e("Balance:", ud_get_wp_invoice()->domain) ?></b></dt>

        <dd><input class="calculate_invoice_total wpi_no_input" disabled='true' value="<?php echo !empty( $this_invoice->data['net'] ) ? $this_invoice->data['net'] : '';?>"/></dd>

      </dl>

    </li>

    <li class="footer clearfix">

      <input type="button"  class="button wpi_button" id="wpi_predefined_services_select" value="<?php esc_attr(_e("Add Line", ud_get_wp_invoice()->domain)) ?>"/>

    <?php if(is_array($wpi_settings['predefined_services'])) {

       // Convert predefined services into special array

      $services_array[""] = __("Insert a predefined line item", ud_get_wp_invoice()->domain);

      foreach($wpi_settings['predefined_services'] as $service) {

        // skip blanks

        if(empty($service['name'])) {

          continue;

        }

        $services_array["{$service['name']}|{$service['description']}|{$service['quantity']}|{$service['price']}|{$service['tax']}"] = $service['name'] . ": " . $service['quantity'] . " x ". $service['price'];

      }

      //** Make sure there are more services than the label */

      if(count($services_array) > 1){

        $services_string = serialize($services_array);

        $select_data = array(
            'id'            => 'wpi_predefined_services',
            'values'        => $services_string,
            'current_value' => ''
        );

        echo WPI_UI::select($select_data);

      }

    } ?>

      <input type="button" class="button wpi_button" id="wpi_add_discount" value="<?php esc_attr(_e("Add Discount", ud_get_wp_invoice()->domain)) ?>"/>

      <span id="wpi_discount_mismatch_error"></span>

    </li>

    </ul>

      <?php do_meta_boxes($screen_id, 'normal', $this_invoice->data); ?>

    </div>

    </div>

  </div> <!-- #poststuff -->

</form>

</div>