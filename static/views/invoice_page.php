<div id="invoice_page" class="wpi_invoice_form wpi_payment_form clearfix">
  <div class="wpi_left_col">
    <h3 class="wpi_greeting"><?php echo sprintf(__('Welcome, %s!', WPI), recipients_name(array('return' => true))) ?></h3>

    <div class="invoice_description">
      <div class="invoice_top_message">
        <?php if (is_quote()) : ?>
          <p><?php echo sprintf(__('We have sent you a quote in the amount of %s.', WPI), balance_due(array('return' => true))) ?></p>
        <?php endif; ?>

        <?php if (!is_quote()) : ?>
          <p><?php echo sprintf(__('We have sent you invoice %1s with a balance of %2s.', WPI), invoice_id(array('return' => true)), balance_due(array('return' => true))); ?></p>
        <?php endif; ?>

        <p><?php wpi_invoice_due_date(); ?></p>

        <?php if (is_recurring()): ?>
          <p><?php _e('This is a recurring bill.', WPI) ?></p>
        <?php endif; ?>

      </div>
      <div class="invoice_description_custom">
        <?php the_description(); ?>
      </div>

      <?php if (is_payment_made()): ?>
        <?php _e("You've made payments, but still owe:", WPI) ?> <?php balance_due(); ?>
      <?php endif; ?>
    </div>

    <div class="wpi_itemized_table">
      <?php show_itemized_table(); ?>
    </div>

    <?php do_action('wpi_front_end_left_col_bottom'); ?>
  </div>

  <div class="wpi_right_col">

    <?php if (show_business_info()) { ?>
      <?php wp_invoice_show_business_information(); ?>
    <?php } ?>

    <?php if (!is_quote()) { ?>
      <div class="wpi_checkout">
        <?php if (allow_partial_payments()): ?>
          <?php show_partial_payments(); ?>
        <?php endif; ?>

        <?php show_payment_selection(); ?>

        <?php
        $method = !empty($invoice['default_payment_method']) ? $invoice['default_payment_method'] : 'manual';
        if ($method == 'manual') {
          ?>
          <p><strong><?php _e('Manual Payment Information', WPI); ?></strong></p>
          <p><?php echo!empty($wpi_settings['manual_payment_info']) ? $wpi_settings['manual_payment_info'] : __('Contact site Administrator for payment information please.', WPI); ?></p>
          <?php
        } else {
          $wpi_settings['installed_gateways'][$method]['object']->frontend_display($invoice);
        }
        apply_filters("wpi_closed_comments", $invoice);
        ?>
      </div>
    <?php } ?>
    <div class="clear"></div>
    <div class="wpi_front_end_right_col_bottom">
      <?php do_action('wpi_front_end_right_col_bottom'); ?>
    </div>

  </div>
</div>