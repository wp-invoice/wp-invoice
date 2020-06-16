<?php
/**
 * Template Name: Modern
 * Thumbnail: static/images/modern.jpg
 */
?>

<html DOCTYPE="html">
<head>
  <style type="text/css">
    body {
      font-family: Comfortaa, "DejaVu Sans";
      font-size: 16px;
    }
    #title {
      margin-bottom: 10px;
    }
    #totals {
      margin-top: 30px;
      text-align: right;
    }
    #totals td {
      font-weight: bold;
    }
    #totals td span {
      color:#198556;
    }
    .amount-due span {
      color: #bc4873 !important;
    }
  </style>
</head>
<body>
<table border="0" cellspacing="0" cellpadding="10" width="100%">
  <tr>
    <td>
      <table border="0" cellspacing="0" cellpadding="3" width="100%">
        <tr>
          <?php if ($template->has_logo()) : ?>
          <td valign="top" rowspan="2" width="30%">
            <img src="<?php echo $template->get_logo_url(); ?>" style="width: 200px;" />
          </td>
          <td valign="top" style="border-bottom: 1px solid #cdcdcd;" align="center" width="70%">
            <?php else: ?>
          <td valign="top" style="border-bottom: 1px solid #cdcdcd;" align="center" width="100%">
            <?php endif; ?>
            <?php echo $template->get_business_name(); ?>
            <?php if ($template->has_business_address()) : ?>
              <div style="color: #7a7a7a"><?php echo $template->get_business_address(); ?></div>
            <?php endif; ?>
            <?php if ($template->has_business_phone()) : ?>
              <?php echo $template->get_business_phone(); ?>
            <?php endif; ?>
            <?php if ($template->has_business_email()) : ?>
              <br/>
              <?php echo $template->get_business_email(); ?>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td align="center">
            <strong><?php _e( strtoupper($template->get_type()), ud_get_wp_invoice_pdf()->domain ); ?></strong>
            <span style="color: #3e8eaf">#<?php echo $template->get_ID(); ?></span> *
            <i><?php echo $template->get_issue_date(); ?></i></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="border-top: 1px solid #cdcdcd; border-bottom: 1px solid #cdcdcd;">
      <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
          <td width="49%" style="background-color:#f0f0f0;">
            <table border="0" cellspacing="5" cellpadding="0" width="100%">
              <?php if ($template->has_due_date()): ?>
                <tr>
                  <td align="right" width="50%">
                    <strong><?php _e('DUE DATE:', ud_get_wp_invoice_pdf()->domain); ?></strong></td>
                  <td align="left" width="50%"><?php echo $template->get_due_date(); ?></td>
                </tr>
              <?php endif; ?>
              <tr>
                <td align="right" width="50%">
                  <strong><?php _e('AMOUNT DUE:', ud_get_wp_invoice_pdf()->domain); ?></strong></td>
                <td align="left" width="50%"><span
                      style="color: #198153"><?php echo $template->get_amount_due($template->get_currency_sign()); ?></span>
                </td>
              </tr>
              <tr>
                <td align="right" width="50%"><strong><?php _e('ATTN:', ud_get_wp_invoice_pdf()->domain); ?></strong>
                </td>
                <td align="left" width="50%"><?php echo $template->get_recepient_name(); ?></td>
              </tr>
            </table>
          </td>
          <td width="2%">&nbsp;</td>
          <td width="49%" style="background-color: #f0f0f0;">
            <table border="0" cellspacing="5" cellpadding="0" align="center" width="100%">
              <tr>
                <td align="right" width="50%"><strong><?php _e('BILL TO:', ud_get_wp_invoice_pdf()->domain); ?></strong>
                </td>
                <td align="left" width="50%"><span
                      style="color: #3e8eaf;"><?php echo $template->get_recepient_name(); ?></span></td>
              </tr>
              <?php if ($template->display_address()): ?>
                <tr>
                  <td align="left" colspan="2"><?php echo $template->get_company_address(); ?></td>
                </tr>
              <?php endif; ?>
              <tr>
                <td align="right" width="50%"><strong><?php _e('PHONE:', ud_get_wp_invoice_pdf()->domain); ?></strong>
                </td>
                <td align="left" width="50%"><?php echo $template->get_recipient_phone(); ?></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="border-bottom:1px solid #cdcdcd;">
      <div id="title"><?php echo $template->get_title(); ?></div>
      <?php if ($template->has_description()): ?>
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
          <tr>
            <td style="color: #474747; font-size: 0.8em;"><?php echo $template->get_description(); ?></td>
          </tr>
        </table>
      <?php endif; ?>
    </td>
  </tr>
</table>

<?php if ( $template->has_items() ): ?>
  <h4><?php _e( 'ITEMS', ud_get_wp_invoice_pdf()->domain ); ?></h4>
  <table style="margin-bottom: 30px;" border="0" cellspacing="0" cellpadding="3" width="100%">
    <tr>
      <td width="50%"><?php _e( 'DESCRIPTION', ud_get_wp_invoice_pdf()->domain ); ?></td>
      <td width=""><?php _e( 'QTY', ud_get_wp_invoice_pdf()->domain ); ?></td>
      <td width=""><?php _e( 'PRICE', ud_get_wp_invoice_pdf()->domain ); ?></td>
      <td width=""><?php _e( 'SUM', ud_get_wp_invoice_pdf()->domain ); ?></td>
      <?php if ( $template->get_total_tax() ): ?>
        <td width="10%"><?php _e( 'TAX', ud_get_wp_invoice_pdf()->domain ); ?></td>
      <?php endif; ?>
    </tr>
    <?php while( $item = $template->get_item() ): ?>
      <tr>
        <td valign="top" bgcolor="#f0f0f0"><b><?php echo $item->get_name(); ?></b>
          <?php if ( $description = $item->get_description() ) : ?>/ <small><?php echo $item->get_description(); ?></small>
          <?php endif; ?>
        </td>
        <td valign="top" bgcolor="#f0f0f0"><b><?php echo $item->get_quantity(); ?></b></td>
        <td valign="top" bgcolor="#f0f0f0"><b><span style="color: #1b8d5b"><?php echo $item->get_price( $template->get_currency_sign() ); ?></span></b></td>
        <td valign="top" bgcolor="#f0f0f0"><b><span style="color: #1b8d5b"><?php echo $item->get_amount( $template->get_currency_sign() ); ?></span></b></td>
        <?php if ( $template->get_total_tax() ): ?>
          <td valign="top" bgcolor="#f0f0f0"><b><span style="color: #1b8d5b"><?php echo $item->get_tax( $template->get_currency_sign() ); ?></span></b></td>
        <?php endif; ?>
      </tr>
    <?php endwhile; ?>
  </table>
<?php endif; ?>

<?php if ( $template->has_charges() ): ?>
  <h4><?php _e( 'CHARGES', ud_get_wp_invoice_pdf()->domain ); ?></h4>
  <table border="0" cellspacing="0" cellpadding="3" width="100%">
    <tr>
      <td width="70%"><?php _e( 'DESCRIPTION', ud_get_wp_invoice_pdf()->domain ); ?></td>
      <td width=""><?php _e( 'AMOUNT', ud_get_wp_invoice_pdf()->domain ); ?></td>
    </tr>
    <?php while( $charge = $template->get_charge() ): ?>
      <tr>
        <td valign="top">
          <b><?php echo $charge->get_name(); ?></b>
        </td>
        <td width="15%" valign="top">
          <b><?php echo $charge->get_amount( $template->get_currency_sign() ); ?></b>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php endif; ?>

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
      <td width="10%" class="total-tax" align="right">
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

<?php if ( $template->has_terms() ) : ?>
  <table border="0" cellspacing="5" cellpadding="5" width="100%">
    <tr>
      <td style="color: #474747;"><?php _e('TERMS &amp; CONDITIONS', ud_get_wp_invoice_pdf()->domain); ?></td>
    </tr>
    <tr>
      <td style="color: #474747; font-size: 0.8em;"><?php echo $template->get_terms(); ?></td>
    </tr>
  </table>
<?php endif; ?>

<?php if ( $template->has_notes() ) : ?>
  <table border="0" cellspacing="5" cellpadding="5" width="100%">
    <tr>
      <td style="color: #474747;"><?php _e('NOTES', ud_get_wp_invoice_pdf()->domain); ?></td>
    </tr>
    <tr>
      <td style="color: #474747; font-size: 0.8em;"><?php echo $template->get_notes(); ?></td>
    </tr>
  </table>
<?php endif; ?>

</body>
</html>