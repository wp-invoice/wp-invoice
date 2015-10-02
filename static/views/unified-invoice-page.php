<?php
/**
 * Unified Invoice Page template
 *
 * Displays Single invoice page
 */
global $invoice, $wpi_settings;
?><!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) & !(IE 7) & !(IE 8)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
  <meta charset="<?php bloginfo('charset'); ?>"/>
  <meta name="viewport" content="width=device-width"/>
  <title><?php
    // Print the <title> tag based on what is being viewed.
    global $page, $paged;

    wp_title('|', true, 'right');

    // Add the blog name.
    bloginfo('name');

    // Add the blog description for the home/front page.
    $site_description = get_bloginfo('description', 'display');
    if ($site_description && (is_home() || is_front_page()))
      echo " | $site_description";

    // Add a page number if necessary:
    if (($paged >= 2 || $page >= 2) && !is_404())
      echo esc_html(' | ' . sprintf(__('Page %s', 'twentyeleven'), max($paged, $page)));

    ?></title>
  <?php wp_head(); ?>
</head>

<body>

  <div id="invoice_page" class="wpi_invoice_form wpi_payment_form clearfix">
    <div class="wpi_left_col">
      <h3 class="wpi_greeting"><?php echo sprintf(__('Welcome, %s!', ud_get_wp_invoice()->domain), recipients_name(array('return' => true))) ?></h3>

      <div class="invoice_description">
        <div class="invoice_top_message">
          <?php if (is_quote()) : ?>
            <p><?php echo sprintf(__('We have sent you a quote in the amount of %s.', ud_get_wp_invoice()->domain), balance_due(array('return' => true))) ?></p>
          <?php endif; ?>

          <?php if (!is_quote()) : ?>
            <p><?php echo sprintf(__('We have sent you invoice %1s with a balance of %2s.', ud_get_wp_invoice()->domain), invoice_id(array('return' => true)), balance_due(array('return' => true))); ?></p>
          <?php endif; ?>

          <p><?php wpi_invoice_due_date(); ?></p>

          <?php if (is_recurring()): ?>
            <p><?php _e('This is a recurring bill.', ud_get_wp_invoice()->domain) ?></p>
          <?php endif; ?>

        </div>
        <div class="invoice_description_custom">
          <?php the_description(); ?>
        </div>

        <?php if (is_payment_made()): ?>
          <?php _e("You've made payments, but still owe:", ud_get_wp_invoice()->domain) ?> <?php balance_due(); ?>
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
            <p><strong><?php _e('Manual Payment Information', ud_get_wp_invoice()->domain); ?></strong></p>
            <p><?php echo !empty($wpi_settings['manual_payment_info']) ? $wpi_settings['manual_payment_info'] : __('Contact site Administrator for payment information please.', ud_get_wp_invoice()->domain); ?></p>
          <?php
          } else {
            if (!empty($wpi_settings['installed_gateways'][$method])) {
              $wpi_settings['installed_gateways'][$method]['object']->frontend_display($invoice);
            } else {
              _e('Sorry, there is no payment method available. Please contact Administrator.', ud_get_wp_invoice()->domain);
            }
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
</body>

<?php wp_footer(); ?>

</html>