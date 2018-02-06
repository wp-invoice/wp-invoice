<?php

  global $wpi_settings, $wpdb;
  $invoice_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpi_object' AND post_title != ''");

?>

<div class="wrap">
  <h2>&nbsp;</h2>
  <div class="postbox" id="wp_new_invoice_div">
    <div class="inside">
      <form action="<?php echo $wpi_settings['links']['manage_invoice']; ?>" method='POST' id="wpi_new_invoice_form">
        <?php echo WPI_UI::input("name=wpi[new_invoice][invoice_id]&value=".rand(1000, 90000000)."&type=hidden"); ?>
        <table class="form-table" id="get_user_info">
          <tr class="invoice_main">
            <th><label for="wp_invoice_userlookup"><?php _e('E-mail Address:', ud_get_wp_invoice()->domain); ?></label></th>
            <td>
              <?php WPI_UI::draw_user_auto_complete_field(); ?>
              <input type="submit" class="button" id="wp_invoice_create_new_invoice" value="<?php esc_attr(_e('Create New', ud_get_wp_invoice()->domain)); ?>">
              <?php if($invoice_count) : ?>
              <span id="wp_invoice_copy_invoice" class="wp_invoice_click_me"><?php _e( 'copy from another', ud_get_wp_invoice()->domain ) ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php if($invoice_count) : ?>
          <tr class="wp_invoice_copy_invoice invoice_main">
            <th><label for="wpi_template_lookup"><?php _e('Existing Invoice:', ud_get_wp_invoice()->domain); ?></label></th>
            <td>
              <?php WPI_UI::draw_template_auto_complete_field(); ?>
              <input type="submit" class="button" value="<?php esc_attr(_e('New Invoice from Template', ud_get_wp_invoice()->domain)) ?>">
              <span id="wp_invoice_copy_invoice_cancel" class="wp_invoice_click_me"><?php _e( 'cancel', ud_get_wp_invoice()->domain ) ?></span>
            </td>
          </tr>
          <?php endif; ?>
        </table>
      </form>
    </div>
  </div>
</div>