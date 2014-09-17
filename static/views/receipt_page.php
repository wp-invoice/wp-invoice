<div id="invoice_page" class="wpi_invoice_form wpi_payment_form clearfix">
  <div class="wpi_left_col">
    <h3 class="wpi_greeting"><?php _e('Payment Receipt', WPI) ?></h3>

    <div class="invoice_description">
      <div class="invoice_top_message">

        <?php if(is_invoice()) : ?>
          <p><?php _e('We have sent you invoice', WPI) ?> <?php invoice_id(); ?>. <?php paid_amount(); ?> <?php _e('was paid.', WPI) ?></p>
        <?php endif; ?>

        </div>
        <div class="invoice_description_custom">
        <?php the_description(); ?>
        </div>

        <?php if(is_payment_made()): ?>
            <?php _e("You've made payments, but still owe:", WPI) ?> <?php balance_due(); ?>
        <?php endif; ?>
    </div>

  <div class="wpi_itemized_table">
      <?php show_itemized_table(); ?>
  </div>

  <?php do_action('wpi_front_end_left_col_bottom'); ?>
  </div>

  <div class="wpi_right_col">
    <?php show_invoice_history(); ?>
    <?php do_action('wpi_front_end_right_col_bottom'); ?>
    <?php apply_filters("wpi_closed_comments", $invoice); ?>
  </div>

</div>