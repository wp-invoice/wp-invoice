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

    ?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
  <?php wp_head(); ?>
  <!--[if lt IE 9]>
  <script src="http://cdnjs.com/libraries/html5shiv"></script>
  <script src="https://cdnjs.com/libraries/respond.js"></script>
  <![endif]-->

  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery('body').unified_page_template();
    });
  </script>
</head>

<body class="inner-pages">

<header class="pageheader">

  <div class="container">

    <div class="row header-info">

      <?php if ( show_business_info() ) : ?>

        <div class="col-sm-4">
          <?php if ( $logo_url = wpi_get_business_logo_url() ): ?>
            <div class="logo"><img style="max-width: 90px;" src="<?php echo $logo_url; ?>" alt="Logo" /></div>
          <?php endif; ?>
          <?php if ( $business_name = wpi_get_business_name() ): ?>
            <h1><?php echo $business_name; ?></h1>
          <?php endif; ?>
          <?php if ( $business_address = wpi_get_business_address() ): ?>
            <p><?php echo $business_address; ?></p>
          <?php endif; ?>
        </div>

        <div class="col-sm-5 contacts">
          <div class="contact">
            <?php if ( $business_email = wpi_get_business_email() ): ?>
              <p><span class="ico mail"></span>
                <a href="mailto:<?php echo $business_email; ?>"><?php echo $business_email; ?></a></p>
            <?php endif; ?>
            <?php if ( $business_phone = wpi_get_business_phone() ): ?>
              <p><span class="ico tel"></span> <?php echo $business_phone; ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="col-m-12" style="height: 100px;"></div>
      <?php endif; ?>

    </div>

    <div class="row top-nav-links">

      <div class="col-xs-6">
        <?php if ( wpi_dashboard_is_active() ): ?>
          <a href="<?php echo wpi_get_dashboard_permalink( $invoice['ID'] ); ?>" class="btn btn-back"> <?php _e( 'My dashboard', ud_get_wp_invoice()->domain ); ?></a>
        <?php endif; ?>
      </div>

      <div class="col-xs-6 text-right">
        <div class="btn-group" role="group" aria-label="...">
          <?php if ( wpi_invoice_has_pdf() ): ?>
            <a href="<?php invoice_pdf_link(); ?>" target="_blank" class="btn btn-default"><?php _e('PDF', ud_get_wp_invoice()->domain); ?></a>
          <?php endif; ?>
        </div>

        <?php do_action('wpi_unified_template_top_navigation'); ?>

      </div>
    </div>

  </div><!--end /container-->

</header><!--end /pageheader-->

<div class="page-content" id="invoice-page-content">

  <div class="container" id="invoice-data-container">

    <div class="box-content">
      <div class="head-title">
        <h2><?php _e('Receipt', ud_get_wp_invoice()->domain); ?></h2>
      </div>

      <div class="box-inner-content">
        <div class="row invoice-head">
          <div class="col-sm-8">
            <?php if ( $logo_url = wpi_get_business_logo_url() ): ?>
              <div class="logo"><img style="max-width: 90px;" src="<?php echo $logo_url; ?>" alt="Logo" /></div>
            <?php endif; ?>
            <?php if ( show_business_info() ) : ?>
              <?php if ( $business_name = wpi_get_business_name() ): ?>
                <h1 class="wp-invoice-business-name"><?php echo $business_name; ?></h1>
              <?php endif; ?>
              <?php if ( $business_address = wpi_get_business_address() ): ?>
                <p><?php echo $business_address; ?></p>
              <?php endif; ?>
            <?php endif; ?>
            <?php if ( $business_email = wpi_get_business_email() ): ?>
              <p><span><?php _e('Email:', ud_get_wp_invoice()->domain); ?></span> <a href="mailto:<?php echo $business_email; ?>"><?php echo $business_email; ?></a></p>
            <?php endif; ?>
            <?php if ( $business_phone = wpi_get_business_phone() ): ?>
              <p><span><?php _e('Phone:', ud_get_wp_invoice()->domain); ?></span> <?php echo $business_phone; ?></p>
            <?php endif; ?>
          </div>

          <div class="col-sm-4">
            <div class="invoice-info-details">
              <p><span><?php _e( 'Invoice ID', ud_get_wp_invoice()->domain ); ?></span>
                <?php invoice_id(); ?>
              </p>

              <p><span><?php _e( 'Issue Date', ud_get_wp_invoice()->domain ); ?></span>
                <?php echo wpi_get_invoice_issue_date(); ?>
              </p>

              <?php if ( wpi_invoice_has_due_date() ): ?>
                <p><span><?php _e('Due Date', ud_get_wp_invoice()->domain); ?></span>
                  <?php echo wpi_get_invoice_due_date(); ?>
                </p>
              <?php endif; ?>

              <p><span><?php _e('Invoice for', ud_get_wp_invoice()->domain); ?></span>
                <?php recipients_name(); ?><br />
                <?php echo wpi_get_company_address(); ?>
              </p>

              <?php do_action('wpi_unified_template_after_recipient'); ?>
            </div>
          </div>
        </div>

        <div class="invoice-desc">
          <h3><?php echo wpi_get_invoice_title(); ?></h3>
          <p><?php the_description(); ?></p>
        </div>

        <?php if ( wpi_invoice_has_items() ): ?>
          <div class="invoice-item-lists">
            <div class="table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th class="description"><?php _e( 'Description', ud_get_wp_invoice()->domain ); ?></th>
                  <?php if ( wpi_show_quantity_column() ): ?>
                    <th class="quantity"><?php _e( 'Quantity', ud_get_wp_invoice()->domain ); ?></th>
                  <?php endif; ?>
                  <th class="unit-price"><?php _e( 'Unit Price', ud_get_wp_invoice()->domain ); ?></th>
                  <th class="amount"><?php _e( 'Amount', ud_get_wp_invoice()->domain ); ?></th>
                  <?php if ( wpi_get_invoice_total_tax() ): ?>
                    <th class="tax" style="text-align: right;"><?php _e( 'Tax', ud_get_wp_invoice()->domain ); ?></th>
                  <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php $i = 0; while( $line_item = wpi_get_line_item( $i ) ) : ?>
                  <tr>
                    <td>
                      <b><?php echo $line_item->get_name(); ?></b>
                      <?php if ( $_description = $line_item->get_description() ): ?>
                        <div><?php echo $_description; ?></div>
                      <?php endif; ?>
                    </td>
                    <?php if ( wpi_show_quantity_column() ): ?>
                      <td><?php echo $line_item->get_quantity(); ?></td>
                    <?php endif; ?>
                    <td><?php echo $line_item->get_price( wpi_get_invoice_currency_sign() ); ?></td>
                    <td><?php echo $line_item->get_amount( wpi_get_invoice_currency_sign() ); ?></td>
                    <?php if ( wpi_get_invoice_total_tax() ): ?>
                      <td><?php echo $line_item->get_tax( wpi_get_invoice_currency_sign() ); ?></td>
                    <?php endif; ?>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <?php if ( wpi_invoice_has_charges() ): ?>
          <h4><?php _e( 'Additional Charges', ud_get_wp_invoice()->domain ); ?></h4>
          <div class="invoice-item-lists">
            <div class="table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th class="description"><?php _e( 'Description', ud_get_wp_invoice()->domain ); ?></th>
                  <th class="amount"><?php _e( 'Amount', ud_get_wp_invoice()->domain ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 0; while( $line_item = wpi_get_line_charge( $i ) ) : ?>
                  <tr>
                    <td><?php echo $line_item->get_name(); ?></td>
                    <td><?php echo $line_item->get_amount( wpi_get_invoice_currency_sign() ); ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <div class="invoice-item-lists">
          <div class="table-responsive">
            <table class="table">
              <?php if ( wpi_get_invoice_total_tax() ): ?>
                <tr class="total-row">
                  <td><span><?php _e('Total:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_total( wpi_get_invoice_currency_sign() ); ?></td>
                </tr>
                <tr class="total-row">
                  <td><span><?php _e('Total Tax:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_invoice_total_tax( wpi_get_invoice_currency_sign() ); ?></td>
                </tr>
              <?php endif; ?>
              <?php if ( wpi_get_discount() ): ?>
                <tr class="total-row">
                  <td><span><?php _e('Discount:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_discount( wpi_get_invoice_currency_sign() ); ?></td>
                </tr>
              <?php endif; ?>
              <?php if ( wpi_get_adjustments() ): ?>
                <tr class="total-row">
                  <td><span><?php _e('Other Adjustments:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_adjustments( wpi_get_invoice_currency_sign() ); ?></td>
                </tr>
              <?php endif; ?>
              <?php if ( wpi_get_total_payments() ): ?>
                <tr class="total-row">
                  <td><span><?php _e('Total Payments:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_total_payments( wpi_get_invoice_currency_sign() ); ?></td>
                </tr>
              <?php endif; ?>
              <tr class="total-row">
                <td><span><?php _e('Amount Due:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_amount_due( wpi_get_invoice_currency_sign() ); ?></td>
              </tr>
            </table>
          </div>
        </div>

      </div><!--end /box-inner-content-->
    </div>

    <?php ob_start(); comments_template(); ob_clean(); ?>

    <?php if ( ( is_quote() || is_invoice() ) && have_comments() ): ?>
      <div id="quote-responses">
        <h4><?php _e('Discussion Thread', ud_get_wp_invoice()->domain); ?></h4>

        <div id="comments" class="box-content">

          <div class="box-inner-content">
            <ul>
              <?php wp_list_comments(); ?>
            </ul>

            <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
              <nav id="comment-nav-above" class="comment-navigation">
                <div class="nav-previous"><?php previous_comments_link( __( '&larr; Previous Page', ud_get_wp_invoice()->domain ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( __( 'Next Page &rarr;', ud_get_wp_invoice()->domain ) ); ?></div>
                <div class="clearfix"></div>
              </nav><!-- #comment-nav-above -->
              <div class="clearfix"></div>
            <?php endif; // Check for comment navigation. ?>
          </div>

          <div class="clearfix"></div>

        </div>
      </div>
    <?php endif; ?>

    <?php do_action('wpi_unified_template_before_actions_history'); ?>

    <?php if ( $history = wpi_get_invoice_log(array(
        'refund' => __('Refund', ud_get_wp_invoice()->domain),
        'notification' => __('Email', ud_get_wp_invoice()->domain),
        'add_charge' => __('Charge', ud_get_wp_invoice()->domain),
        'add_payment' => __('Paid', ud_get_wp_invoice()->domain),
        'do_adjustment' => __('Adjustment', ud_get_wp_invoice()->domain),
        'create' => __('Create', ud_get_wp_invoice()->domain))) ): ?>
      <div class="invoice-history">
        <h4><?php _e('Invoice History', ud_get_wp_invoice()->domain); ?></h4>

        <div class="box-content">
          <div class="box-inner-content">
            <?php foreach( $history as $hitem ): ?>
              <div class="row">
                <div class="col-md-2 label-item"><span class="label label-<?php echo $hitem['action']; ?>"><?php echo $hitem['label']; ?></span></div>
                <div class="col-md-7 description"><?php echo $hitem['text']; ?></div>
                <div class="col-md-3 date"><?php echo $hitem['time']; ?></div>
              </div>
            <?php endforeach; ?>
          </div><!--end /box-inner-content-->
        </div>
      </div>
    <?php endif; ?>

  </div><!--end /container-->

</div><!--end /page-content-->

<footer class="pagefooter">
  <div class="container">
    <a href="https://www.usabilitydynamics.com/product/wp-invoice" target="_blank">
      <p>Powered by <span><img src="<?php echo ud_get_wp_invoice()->path( 'static/img/wp-invoice.png', 'url' ); ?>" alt="WP-Invoice" /></span> WP-Invoice</p>
    </a>
  </div><!--end /container-->
</footer><!--end /pagefooter-->

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>

<?php wp_footer(); ?>

</html>