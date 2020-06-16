<?php
/**
 * Template Name: Remastered
 * Thumbnail: static/images/remastered.jpg
 */
?>

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
      body {
        font-family: "Questrial", "DejaVu Sans";
        color: #414141;
      }
      #heading td {
        background-color:#e6e6e6;
        text-align:center;
        padding:10px;
        font-size: 20px;
        color: #414141;
      }
      #invoice-information {
        padding-top: 20px;
      }
      #invoice-information li {
        list-style: none;
        margin-bottom: 15px;
      }
      #invoice-information li span {
        font-size: 12px;
        color: #414141;
      }
      #invoice-information li span.recipient-name {
        font-size: 14px;
        margin-top: 5px;
        margin-bottom: 5px;
        display: block;
      }
      #invoice-information li .label {
        font-size: 11px;
        color: #a7a9ac;
        text-transform: uppercase;
      }
      #business {
        padding-top: 40px;
      }
      #logo {
        margin-bottom: 10px;
      }
      #business .company-name {
        font-size: 20px;
        margin-bottom: 5px;
      }
      #business .label {
        color: #a7a9ac;
      }
      #business .company-address, #business .company-phone, #business .company-email {
        font-size: 13px;
        margin-bottom: 7px;
      }
      #title {
        font-size: 16px;
        font-weight: normal;
      }
      #description {
        font-size: 12px;
      }
      #items {
        margin-top: 15px;
        margin-bottom: 20px;
      }
      #items .th {
        background-color: #f9f9f9;
      }
      #items .right {
        text-align: right;
      }
      #items .th td {
        border-bottom: 1px solid #e6e6e6;
        font-size: 12px;
        text-transform: uppercase;
        color: #808285;
        padding: 10px 15px;
      }
      #items .item td {
        border-bottom: 1px solid #e6e6e6;
        font-size: 12px;
        padding: 20px 15px;
      }
      #totals td {
        padding-right: 15px;
        text-align: right;
      }
      #notes span, #terms span {
        color: #a7a9ac;
        font-size: 12px;
        text-transform: uppercase;
      }
      #notes p, #terms p {
        font-size: 12px;
      }
      .refund {
        color: #830C0C;
      }
    </style>
  </head>
  <body>

    <!-- Top Heading -->
    <table id="heading" border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td>
          <?php _e( strtoupper($template->get_type()), ud_get_wp_invoice_pdf()->domain ); ?>
        </td>
      </tr>
    </table>

    <!-- Invoice Header -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td id="business" width="65%" valign="top">

          <?php if ( $template->has_logo() ) : ?>
          <div id="logo">
            <img height="80" src="<?php echo $template->get_logo_url(); ?>" />
          </div>
          <?php endif; ?>

          <div class="company-name"><?php echo $template->get_business_name(); ?></div>

          <?php if ( $template->has_business_address() ) : ?>
            <div class="company-address"><?php echo $template->get_business_address(); ?></div>
          <?php endif; ?>

          <?php if ( $template->has_business_email() ) : ?>
            <div class="company-email"><span class="label"><?php _e('Email', ud_get_wp_invoice_pdf()->domain); ?>:</span> <?php echo $template->get_business_email(); ?></div>
          <?php endif; ?>

          <?php if ( $template->has_business_phone() ) : ?>
            <div class="company-phone"><span class="label"><?php _e('Phone', ud_get_wp_invoice_pdf()->domain); ?>:</span> <?php echo $template->get_business_phone(); ?></div>
          <?php endif; ?>

        </td>
        <td width="35%" valign="top">
          <ul id="invoice-information">
            <li>
              <div class="label"><?php _e( 'Invoice ID', ud_get_wp_invoice_pdf()->domain ); ?></div>
              <span><?php echo $template->get_ID(); ?></span>
            </li>
            <li>
              <div class="label"><?php _e( 'Issue Date', ud_get_wp_invoice_pdf()->domain ); ?></div>
              <span><?php echo $template->get_issue_date(); ?></span>
            </li>
            <?php if ( $template->has_due_date() ): ?>
              <li>
                <div class="label"><?php _e( 'Due Date', ud_get_wp_invoice_pdf()->domain ); ?></div>
                <span><?php echo $template->get_due_date(); ?></span>
              </li>
            <?php endif; ?>
            <?php if ( $template->display_address() ): ?>
              <li style="width: 150px;">
                <div class="label"><?php _e( 'Invoice For', ud_get_wp_invoice_pdf()->domain ); ?></div>
                <span class="recipient-name"><?php echo $template->get_recepient_name(); ?></span>
                <span style="width: 100%;"><?php echo $template->get_company_address(); ?></span>
              </li>
            <?php endif; ?>
          </ul>
        </td>
      </tr>
    </table>

    <!-- Invoice Content -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td>
          <h1 id="title"><?php echo $template->get_title(); ?></h1>
          <?php if ( $template->has_description() ): ?>
            <p id="description"><?php echo $template->get_description(); ?></p>
          <?php endif; ?>
        </td>
      </tr>
    </table>

    <!-- Items -->
    <?php if ( $template->has_items() ): ?>
      <table id="items" border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr class="th">
          <td><?php _e( 'Description', ud_get_wp_invoice_pdf()->domain ); ?></td>
          <td class="right" width="12%"><?php _e( 'Quantity', ud_get_wp_invoice_pdf()->domain ); ?></td>
          <td class="right" width="11%"><?php _e( 'Price', ud_get_wp_invoice_pdf()->domain ); ?></td>
          <td class="right" width="13%"><?php _e( 'Amount', ud_get_wp_invoice_pdf()->domain ); ?></td>
          <?php if ( $template->get_total_tax() ): ?>
            <td class="right" width="10%"><?php _e( 'Tax', ud_get_wp_invoice_pdf()->domain ); ?></td>
          <?php endif; ?>
        </tr>
      <?php while( $item = $template->get_item() ): ?>
        <tr class="item">
          <td>
            <?php echo $item->get_name(); ?>
            <?php if ( $description = $item->get_description() ) : ?>
            / <?php echo $description; ?>
            <?php endif; ?>
          </td>
          <td class="right">
            <?php echo $item->get_quantity(); ?>
          </td>
          <td class="right">
            <?php echo $item->get_price( $template->get_currency_sign() ); ?>
          </td>
          <td class="right">
            <?php echo $item->get_amount( $template->get_currency_sign() ); ?>
          </td>
          <?php if ( $template->get_total_tax() ): ?>
            <td class="right">
              <?php echo $item->get_tax( $template->get_currency_sign() ); ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endwhile; ?>
      </table>
    <?php endif; ?>

    <!-- Charges -->
    <?php if ( $template->has_charges() ): ?>
    <table id="items" border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr class="th">
        <td><?php _e( 'Charge', ud_get_wp_invoice_pdf()->domain ); ?></td>
        <td class="right" width="13%"><?php _e( 'Amount', ud_get_wp_invoice_pdf()->domain ); ?></td>
      </tr>
      <?php while( $charge = $template->get_charge() ): ?>
        <tr class="item">
          <td>
            <?php echo $charge->get_name(); ?>
          </td>
          <td class="right">
            <?php echo $charge->get_amount( $template->get_currency_sign() ); ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
    <?php endif; ?>

    <!-- Totals -->
    <table id="totals" border="0" cellspacing="0" cellpadding="0" width="100%">
      <?php if ( $template->get_total_tax() ): ?>
        <tr>
          <td><?php _e('Total:', ud_get_wp_invoice_pdf()->domain); ?></td>
          <td width="10%" class="total" align="right">
            <span><?php echo $template->get_total( $template->get_currency_sign() ); ?></span>
          </td>
        </tr>
        <tr>
          <td><?php _e('Total Tax:', ud_get_wp_invoice_pdf()->domain); ?></td>
          <td class="total-tax" align="right">
            <span><?php echo $template->get_total_tax( $template->get_currency_sign() ); ?></span>
          </td>
        </tr>
      <?php endif; ?>
      <?php if ( $template->get_discount() ): ?>
        <tr>
          <td><?php _e('Discount:', ud_get_wp_invoice_pdf()->domain); ?></td>
          <td width="10%" class="discount" align="right">
            <span><?php echo $template->get_discount( $template->get_currency_sign() ); ?></span>
          </td>
        </tr>
      <?php endif; ?>
      <?php if ( $template->get_adjustments() ): ?>
        <tr>
          <td><?php _e('Other Adjustments:', ud_get_wp_invoice_pdf()->domain); ?></td>
          <td width="10%" class="other-adjustments" align="right">
            <span><?php echo $template->get_adjustments( $template->get_currency_sign() ); ?></span>
          </td>
        </tr>
      <?php endif; ?>
      <?php if ( $template->get_total_payments() ): ?>
        <tr>
          <td><?php _e('Total Payments:', ud_get_wp_invoice_pdf()->domain); ?></td>
          <td width="10%" class="total-payments" align="right">
            <span><?php echo $template->get_total_payments( $template->get_currency_sign() ); ?></span>
          </td>
        </tr>
      <?php endif; ?>
      <tr>
        <td><?php _e('Amount Due:', ud_get_wp_invoice_pdf()->domain); ?></td>
        <td width="10%" class="amount-due" align="right">
          <span><?php echo $template->get_amount_due( $template->get_currency_sign() ); ?></span>
        </td>
      </tr>
      <?php if ( $template->is_refunded() ): ?>
        <tr>
          <td class="refund" colspan="2">
            <?php _e( 'Invoice Refunded', ud_get_wp_invoice_pdf()->domain ); ?>
          </td>
        </tr>
      <?php endif; ?>
    </table>

    <!-- Notes -->
    <?php if ( $template->has_notes() ) : ?>
    <table id="notes" border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td>
          <span><?php _e( 'Notes', ud_get_wp_invoice_pdf()->domain ); ?></span>
          <p><?php echo $template->get_notes(); ?></p>
        </td>
      </tr>
    </table>
    <?php endif; ?>

    <!-- Terms -->
    <?php if ( $template->has_terms() ) : ?>
      <table id="terms" border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
          <td>
            <span><?php _e( 'Terms &amp; Conditions', ud_get_wp_invoice_pdf()->domain ); ?></span>
            <p><?php echo $template->get_terms(); ?></p>
          </td>
        </tr>
      </table>
    <?php endif; ?>

  </body>
</html>