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

      <div class="row top-nav-links">

        <div class="col-xs-6">
          <a href="#" class="btn btn-back"> <?php _e( 'My dashboard', ud_get_wp_invoice()->domain ); ?></a>
        </div>

        <div class="col-xs-6 text-right">
          <div class="btn-group" role="group" aria-label="...">
            <?php if ( wpi_invoice_has_pdf() ): ?>
              <a href="<?php invoice_pdf_link(); ?>" target="_blank" class="btn btn-default"><?php _e('PDF', ud_get_wp_invoice()->domain); ?></a>
            <?php endif; ?>
          </div>

          <a href="javascript:void(0);" id="close-payment-form" class="btn btn-pay"><?php _e('Go Back', ud_get_wp_invoice()->domain); ?></a>
          <a href="javascript:void(0);" id="open-payment-form" class="btn btn-pay"><?php _e('Make Payment', ud_get_wp_invoice()->domain); ?></a>

        </div>
      </div>

    </div><!--end /container-->

  </header><!--end /pageheader-->

  <div class="page-content">

    <div class="container" id="invoice-data-container">

      <div class="box-content">
        <div class="head-title">
          <h2><?php echo wpi_get_invoice_type(); ?></h2>
        </div>

        <div class="box-inner-content">
          <div class="row invoice-head">
            <div class="col-sm-8">
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
                      <?php echo $line_item->get_name(); ?>
                      <?php if ( $_description = $line_item->get_description() ): ?>
                        / <?php echo $_description; ?>
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

      <?php if ( $history = wpi_get_invoice_log(array(
          'refund' => __('Refund', ud_get_wp_invoice()->domain),
          'notification' => __('Email', ud_get_wp_invoice()->domain),
          'add_charge' => __('Charge', ud_get_wp_invoice()->domain),
          'add_payment' => __('Paid', ud_get_wp_invoice()->domain),
          'do_adjustment' => __('Adjustment', ud_get_wp_invoice()->domain),
          'create' => __('Create', ud_get_wp_invoice()->domain))) ): ?>
      <div class="invoice-history">
        <h4>Invoice History</h4>

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

    <div id="payment-form-container" class="container invoice-payment">

      <div class="box-content">

        <div class="box-inner">
          <form action="#" method="post">
            <div class="form-box-wrap">
              <div class="row">
                <div class="col-sm-5">
                  <label>
                    <p>Select your payment mode</p>
                    <span>This invoice allows partial payments please select the amount you would like to pay.  </span>
                  </label>
                </div>

                <div class="col-sm-7">
                  <ul class="payment-mode">
                    <li><label><input type="radio" name="payment-mode" value="Minimum payment due" /> Minimum payment due: <i>$3,000.00</i></label></li>

                    <li><label><input type="radio" name="payment-mode" value="Statement balance" /> Statement balance: <i>$5,604.80</i></label></li>

                    <li><label><input type="radio" name="payment-mode" value="Other" /> Other</label>
                      <div class="form-group">
                        <input type="text" id="other" class="form-control" />
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div><!--end /form-box-wrap-->

            <div class="form-box-wrap">
              <div class="row">
                <div class="col-sm-5">
                  <label>
                    <p>Customer Information</p>
                    <span>Enter the information of client you are sending payment to.</span>
                  </label>
                </div>

                <div class="col-sm-7">
                  <div class="form-group clearfix">
                    <div class="col-xs-6">
                      <span class="error-label">Please check the name</span>
                      <input type="text" id="first-name" class="form-control error" placeholder="First Name" />
                    </div>

                    <div class="col-xs-6">
                      <input type="text" id="last-name" class="form-control" placeholder="Last Name" />
                    </div>
                  </div>

                  <div class="form-group">
                    <input type="email" id="email" class="form-control" placeholder="Email" />
                  </div>

                  <div class="form-group">
                    <input type="text" id="phone-number" class="form-control" placeholder="Phone Number" />
                  </div>


                  <div class="form-group">
                    <input type="text" id="address" class="form-control" placeholder="Address" />
                  </div>

                  <div class="form-group">
                    <select id="country" class="form-control">
                      <option>Select country</option>
                      <option>Canada</option>
                      <option>USA</option>
                      <option>UK</option>
                    </select>
                  </div>

                  <div class="form-group clearfix">
                    <div class="col-xs-4">
                      <input type="text" id="city" class="form-control" placeholder="City" />
                    </div>

                    <div class="col-xs-4">
                      <input type="text" id="state" class="form-control" placeholder="State" />
                    </div>

                    <div class="col-xs-4">
                      <input type="text" id="zipcode" class="form-control" placeholder="Zip" />
                    </div>
                  </div>
                </div>
              </div>
            </div><!--end /form-box-wrap-->

            <div class="form-box-wrap">
              <div class="row">
                <div class="col-sm-5">
                  <label>
                    <p>Payment Details</p>
                    <span>Enter the information of client you are sending payment to.</span>
                  </label>
                </div>

                <div class="col-sm-7">
                  <div class="form-group">
                    <input type="text" id="card-name" class="form-control" placeholder="Name on card" />
                  </div>

                  <div class="form-group">
                    <input type="text" id="card-number" class="form-control" placeholder="Card number" />
                  </div>

                  <div class="form-group">
                    <div class="col-xs-4">
                      <input type="text" id="month" class="form-control" placeholder="Month" />
                    </div>

                    <div class="col-xs-4">
                      <input type="text" id="year" class="form-control" placeholder="Year" />
                    </div>

                    <div class="col-xs-4">
                      <input type="text" id="cv" class="form-control" placeholder="CV" />
                    </div>
                  </div>
                </div>
              </div>
            </div><!--end /form-box-wrap-->
            <div class="bottom-box">
              <input type="submit" id="paynow" class="btn btn-paynow" value="Pay Now" />
              <input type="reset" id="cancel" class="btn btn-cancel" value="Cancel" />
            </div>
          </form>
        </div><!--end /box-inner-content-->
      </div>

    </div><!--end /container-->

  </div><!--end /page-content-->

  <footer class="pagefooter">
    <div class="container">
      <p>Powered by <span><img src="<?php echo ud_get_wp_invoice()->path( 'static/img/wp-invoice.png', 'url' ); ?>" alt="WP-Invoice" /></span> WP-Invoice</p>
    </div><!--end /container-->
  </footer><!--end /pagefooter-->

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

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