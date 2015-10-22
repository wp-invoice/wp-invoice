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
    <h3 class="wpi_greeting"><?php _e('Payment Receipt', ud_get_wp_invoice()->domain) ?></h3>

    <div class="invoice_description">
      <div class="invoice_top_message">

        <?php if(is_invoice()) : ?>
          <p><?php _e('We have sent you invoice', ud_get_wp_invoice()->domain) ?> <?php invoice_id(); ?>. <?php paid_amount(); ?> <?php _e('was paid.', ud_get_wp_invoice()->domain) ?></p>
        <?php endif; ?>

      </div>
      <div class="invoice_description_custom">
        <?php the_description(); ?>
      </div>

      <?php if(is_payment_made()): ?>
        <?php _e("You've made payments, but still owe:", ud_get_wp_invoice()->domain) ?> <?php balance_due(); ?>
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
</body>

<?php wp_footer(); ?>

</html>