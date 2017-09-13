<?php
/**
 * Client Dashboard template
 */
global $invoice, $wpi_settings;
?><!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?> ng-app="wpiClientDashboard">
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?> ng-app="wpiClientDashboard">
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?> ng-app="wpiClientDashboard">
<![endif]-->
<!--[if !(IE 6) & !(IE 7) & !(IE 8)]><!-->
<html <?php language_attributes(); ?> ng-app="wpiClientDashboard">
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
    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
  </script>
  <script data-require="ui-bootstrap@*" data-semver="0.14.3" src="//angular-ui.github.io/bootstrap/ui-bootstrap-tpls-0.14.3.min.js"></script>
</head>
<body ng-controller="InvoiceList" id="client-dashboard">

<header class="pageheader" ng-init="init({wpi_user_id:'<?php echo $_GET['wpi_user_id'] ?>',wpi_token:'<?php echo $_GET['wpi_token'] ?>'})">

  <div class="container">

    <div class="row header-info">

      <?php if ( show_business_info() ) : ?>

      <div class="col-sm-4">
        <?php if ( $logo_url = wpi_get_business_logo_url() ): ?>
          <div class="logo"><a href="<?php echo home_url(); ?>" ><img style="max-width: 90px;" src="<?php echo $logo_url; ?>" alt="Logo" /></a></div>
        <?php endif; ?>
          <?php if ( $business_name = wpi_get_business_name() ): ?>
			<h1 class="wp-invoice-business-name"><a href="<?php echo home_url(); ?>"><?php echo $business_name; ?></a></h1>
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
            <p><span class="ico tel"></span>
              <a href="tel:<?php echo $business_phone; ?>"><?php echo $business_phone; ?></a></p>
          <?php endif; ?>
        </div>
      </div>

      <?php else: ?>

        <div class="col-m-12" style="height: 100px;"></div>

      <?php endif; ?>

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
<!--          <div class="success-buttons">
            <a href="<?php echo home_url(); ?>" class="btn btn-info"><?php _e( 'Back to website', ud_get_wp_invoice()->domain ); ?></a>
          </div>-->
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
<!--            <div class="col-sm-7 text-right">
              <div class="btn-group" role="group" aria-label="...">
                <a href="<?php echo home_url(); ?>" class="btn btn-back"> <?php _e( 'Back to website', ud_get_wp_invoice()->domain ); ?></a>
              </div>
            </div>-->
			<div class="col-sm-7 text-right">
              <div class="btn-group" role="group" aria-label="...">
				<a ng-click="setInvoiceType('paid')" id="btn-paid" href="javascript://" class="btn btn-back"> <?php _e( 'Paid Invoices', ud_get_wp_invoice()->domain ); ?></a>
                <a ng-click="setInvoiceType('other')" id="btn-other" href="javascript://" class="btn btn-back active"> <?php _e( 'Outstanding Invoices', ud_get_wp_invoice()->domain ); ?></a>
              </div>
            </div>
          </div>
        </div>

        <div class="invoices-lists">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 15%;"><?php _e( 'Status', ud_get_wp_invoice()->domain ); ?></th>
				  <th style="width: 12%;" ng-bind-html="invoice_date_title" ></th>
                  <th><?php _e( 'ID', ud_get_wp_invoice()->domain ); ?></th>
                  <th><?php _e( 'Summary', ud_get_wp_invoice()->domain ); ?></th>
                  <th style="width: 10%;"><?php _e( 'Amount', ud_get_wp_invoice()->domain ); ?></th>
                </tr>
              </thead>
              <tbody ng-if="isLoading">
                <tr>
                  <td colspan="5" style="text-align: center;">
                    <?php _e( 'Loading...', ud_get_wp_invoice()->domain ); ?>
                  </td>
                </tr>
              </tbody>
              <tbody ng-if="isError && !isLoading">
                <tr>
                  <td colspan="5" style="text-align: center;">
                    <?php _e('Something went wrong while loading invoices. Try refreshing the page.', ud_get_wp_invoice()->domain); ?>
                  </td>
                </tr>
              </tbody>
              <tbody ng-if="!isError && !isLoading && displayInvoices.length">
				<tr ng-repeat="invoice in displayInvoices" ng-click="goToInvoice(invoice.cd_permalink)" class="invoices {{invoice.cd_invoice_status}}" >
                  <td style="padding-right: 25px;"><span class="label label-{{invoice.post_status}}">{{invoice.cd_invoice_status}}</span></td>
                  <td ng-if="invoice.post_status!='paid'">{{invoice.cd_due_date}}</td>
                  <td ng-if="invoice.post_status=='paid'">{{invoice.cd_date_paid}}</td>
                  <td>{{invoice.cd_invoice_id}}</td>
                  <td ng-if="invoice.cd_invoice_type==='Invoice'"> <a href="{{invoice.cd_permalink}}">{{invoice.cd_invoice_title}}</a></td>
                  <td ng-if="invoice.cd_invoice_type!=='Invoice'" >[{{invoice.cd_invoice_type}}] <a href="{{invoice.cd_permalink}}">{{invoice.cd_invoice_title}}</a></td>
                  <td ng-bind-html="invoice.cd_invoice_total"></td>
                </tr>
              </tbody>
              <tbody ng-if="!isError && !isLoading && !displayInvoices.length">
                <tr>
                  <td colspan="5" style="text-align: center;">
                    <?php _e('No invoices found...', ud_get_wp_invoice()->domain); ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div><!--end /invoices-lists-->

        <div class="bottom-box">
          <div class="row">
            <div class="col-xs-4 col-xs-push-8 text-right total">
              <span><?php _e('Total:', ud_get_wp_invoice()->domain); ?></span> <?php echo wpi_get_default_currency_sign(); ?>{{totalAmount}}
            </div>

            <div class="col-xs-8 col-xs-pull-4">

              <uib-pagination previous-text="<?php _e( 'Previous', ud_get_wp_invoice()->domain ); ?>" next-text="<?php _e( 'Next', ud_get_wp_invoice()->domain ); ?>" last-text="<?php _e( 'Last', ud_get_wp_invoice()->domain ); ?>" first-text="<?php _e( 'First', ud_get_wp_invoice()->domain ); ?>" direction-links="false" boundary-links="true" items-per-page="perPage" max-size="maxSize" total-items="totalItems" ng-model="currentPage" ng-change="paginate()"></uib-pagination>

<!--              <div class="per_page_wrapper">
                <?php _e('Invoices Per Page:', ud_get_wp_invoice()->domain); ?>
                <select ng-model="perPage" ng-change="paginate()">
                  <option value="5">5</option>
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                  <option value="-1"><?php _e( 'All', ud_get_wp_invoice()->domain ); ?></option>
                </select>
              </div>-->

            </div>
          </div>
        </div>
      </div>

    </div><!--end /container-->

  </div><!--end /page-content-->
<?php endif; ?>

<footer class="pagefooter">
  <div class="container">
    <a href="https://www.usabilitydynamics.com/product/wp-invoice" target="_blank">
      <p><?php _e( 'Powered by', ud_get_wp_invoice()->domain ); ?> <span><img src="<?php echo ud_get_wp_invoice()->path( 'static/img/wp-invoice.png', 'url' ); ?>" alt="WP-Invoice" /></span> WP-Invoice</p>
    </a>
  </div><!--end /container-->
</footer><!--end /pagefooter-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>