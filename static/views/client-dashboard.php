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
</head>
<body id="client-dashboard">

<header class="pageheader">

  <div class="container">

    <div class="row header-info">

      <div class="col-sm-4">
        <?php if ( $logo_url = wpi_get_business_logo_url() ): ?>
          <div class="logo"><img style="max-width: 90px;" src="<?php echo $logo_url; ?>" alt="Logo" /></div>
        <?php endif; ?>
        <?php if ( show_business_info() ) : ?>
          <?php if ( $business_name = wpi_get_business_name() ): ?>
            <h1><?php echo $business_name; ?></h1>
          <?php endif; ?>
          <?php if ( $business_address = wpi_get_business_address() ): ?>
            <p><?php echo $business_address; ?></p>
          <?php endif; ?>
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

    </div>
  </div><!--end /container-->

</header><!--end /pageheader-->

<?php if ( !wpi_user_can_view_dashboard() ): ?>
  <div class="page-content thankyou">
    <div class="container">
      <div class="box-content">
        <div class="box-inner-content">
          <div class="payment-logo">
            <img src="<?php echo ud_get_wp_invoice()->path('static/img/key.png', 'url'); ?>" alt="" />
          </div>
          <h2><?php _e('Access Denied', ud_get_wp_invoice()->domain); ?></h2>
          <p><?php _e('If you see this message please be sure you followed by proper link from the invoice page or log in to see your dashboard.', ud_get_wp_invoice()->domain); ?></p>
          <div class="success-buttons">
            <a href="javascript:window.history.back();" class="btn btn-info"><?php _e( 'Go Back', ud_get_wp_invoice()->domain ); ?></a>
          </div>
        </div><!--end /box-inner-content-->
      </div>
    </div><!--end /container-->
  </div><!--end /page-content-->
<?php else: ?>
  <div class="page-content">

    <div class="container">

      <div class="box-content">
        <div class="head-title">
          <div class="row">
            <div class="col-sm-5">
              <h2><?php echo wpi_get_client_dashboard_company_name(); ?></h2>
            </div>
<!--            <div class="col-sm-7 text-right">-->
<!--              <div class="btn-group" role="group" aria-label="...">-->
<!--                <button type="button" class="btn btn-default">Outstanding Invoice</button>-->
<!--                <button type="button" class="btn btn-default active">Paid Invoice</button>-->
<!--              </div>-->
<!--            </div>-->
          </div>
        </div>

        <?php
          $invoices = ud_get_wp_invoice()->cd->get_current_user_invoices();
          $dashboard_total = 0;
          if ( !empty( $invoices ) ) :
        ?>

        <div class="invoices-lists">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 15%;"><?php _e( 'Status', ud_get_wp_invoice()->domain ); ?></th>
                  <th style="width: 12%;"><?php _e( 'Due Date', ud_get_wp_invoice()->domain ); ?></th>
                  <th><?php _e( 'ID', ud_get_wp_invoice()->domain ); ?></th>
                  <th><?php _e( 'Summary', ud_get_wp_invoice()->domain ); ?></th>
                  <th style="width: 10%;"><?php _e( 'Amount', ud_get_wp_invoice()->domain ); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php global $invoice; foreach( $invoices as $invoice ): $invoice = $invoice->data; ?>
                <tr onclick="window.location = '<?php echo get_invoice_permalink($invoice['ID']); ?>';">
                  <td style="padding-right: 25px;"><span class="label label-<?php echo $invoice['post_status']; ?>"><?php echo $invoice['post_status']; ?></span></td>
                  <td><?php echo wpi_get_invoice_due_date( 'm/d/Y' ); ?></td>
                  <td><?php invoice_id(); ?></td>
                  <td>[<?php echo wpi_get_invoice_type(); ?>] <a href="<?php echo get_invoice_permalink($invoice['ID']); ?>"><?php echo wpi_get_invoice_title(); ?></a></td>
                  <td>
                    <?php
                      if ( is_paid() ) {
                        echo wpi_get_total_payments( wpi_get_invoice_currency_sign() );
                        $dashboard_total += $invoice['total_payments'];
                      } else {
                        echo wpi_get_amount_due( wpi_get_invoice_currency_sign() );
                        $dashboard_total += $invoice['net'];
                      }
                    ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div><!--end /invoices-lists-->

        <?php endif; ?>

        <div class="bottom-box">
          <div class="row">
            <div class="col-xs-6 col-xs-push-6 text-right total">
              <span><?php _e('Total:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_default_currency_sign(); ?><?php echo wp_invoice_currency_format($dashboard_total); ?>
            </div>

<!--            <div class="col-xs-6 col-xs-pull-6">-->
<!--              <ul class="pagination">-->
<!--                <li class="prev active"><a href="#">Prev.</a></li>-->
<!--                <li><a href="#">1</a></li>-->
<!--                <li><a href="#">2</a></li>-->
<!--                <li><a href="#">3</a></li>-->
<!--                <li><a href="#">4</a></li>-->
<!--                <li><a href="#">5</a></li>-->
<!--                <li class="next"><a href="#">Next</a></li>-->
<!--              </ul>-->
<!--            </div>-->
          </div>
        </div>
      </div>

    </div><!--end /container-->

  </div><!--end /page-content-->
<?php endif; ?>

<footer class="pagefooter">
  <div class="container">
    <a href="https://www.usabilitydynamics.com/product/wp-invoice" target="_blank">
      <p>Powered by <span><img src="<?php echo ud_get_wp_invoice()->path( 'static/img/wp-invoice.png', 'url' ); ?>" alt="WP-Invoice" /></span> WP-Invoice</p>
    </a>
  </div><!--end /container-->
</footer><!--end /pagefooter-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>