<?php

  global $wpi_settings, $wpdb; 
 
?>

<div class="wrap">
  <h2>&nbsp;</h2>
  <div class="postbox" id="wp_new_invoice_div">
    <div class="inside">
      <form action="<?php echo $wpi_settings['links']['manage_invoice']; ?>" method='POST' id="wpi_new_invoice_form">
        <?php echo WPI_UI::input("name=wpi[new_invoice][invoice_id]&value=".rand(10000000, 90000000)."&type=hidden"); ?>
        <table class="form-table" id="get_user_info">
          <tr class="invoice_main">
            <th><label for="wp_invoice_userlookup"><?php _e('E-mail Address:', WPI); ?></label></th>
            <td>
              <?php WPI_UI::draw_user_auto_complete_field(); ?>
              <input type="submit" class="button" id="wp_invoice_create_new_invoice" value="<?php esc_attr(_e('Create New', WPI)); ?>">
              <?php if($wpi_settings['total_invoice_count']) { ?>
                <span id="wp_invoice_copy_invoice" class="wp_invoice_click_me">copy from another</span><br />
                <div class="wp_invoice_copy_invoice">
                  <?php $all_invoices = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post_type = 'wpi_object' AND post_title != ''"); ?>
                    <select name="wpi[new_invoice][template_copy]">
                    <option><?php _e('-- Select Invoice --', WPI) ?></option>
                    <?php foreach ($all_invoices as $invoice) {
                            $invoice_id = wpi_post_id_to_invoice_id($invoice->ID);
                            $invoice_obj = new WPI_Invoice();
                            $invoice_obj->load_invoice("id=".$invoice_id);
                            //print_r( $invoice_obj );
                            if ( $invoice_obj->data['type'] != 'single_payment' ) :
                    ?>
                    <option value="<?php echo $invoice_id; ?>">
                      <?php 
                        if( $invoice_obj->data['type'] == 'recurring' ) {
                      ?> 
                      <?php _e('[Recurring]', WPI) ?> 
                      <?php } ?> 
                      <?php 
                        echo $invoice_obj->data['post_title'] . " - " .$wpi_settings['currency']['symbol'][$invoice_obj->data['default_currency_code']] . wp_invoice_currency_format($invoice_obj->data['subtotal']); 
                      ?>
                      <?php 
                        if ( !empty( $invoice_obj->data['total_discount'] ) && $invoice_obj->data['total_discount'] > 0 ) {
                          echo " (".$wpi_settings['currency']['symbol'][$invoice_obj->data['default_currency_code']].$invoice_obj->data['total_discount']." of discount)";
                        } 
                      ?>
                    </option>
                    <?php endif; } ?>
                  </select>
                  <input type="submit" class="button" value="<?php esc_attr(_e('New Invoice from Template', WPI)) ?>">
                  <span id="wp_invoice_copy_invoice_cancel" class="wp_invoice_click_me">cancel</span>
                </div>
              <?php } ?>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>