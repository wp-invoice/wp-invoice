<?php
/**
 * Template Name: Traditional
 * Thumbnail: static/images/traditional.jpg
 */
?>

<html>
  <head>
    <link href='https://fonts.googleapis.com/css?family=Oxygen:400,300,700' rel='stylesheet' type='text/css'>
    <style type="text/css">
      body {
        font-family: Roboto, "DejaVu Sans";
        font-size: 16px;
        color: #5c5b5d;
        font-style: italic;
      }
      #company-info {
        border-bottom: 1px solid #eee;
        font-size: 14px;
      }
      .business_email {
        color: #777;
        font-size: 12px;
      }
      .invoice-id {
        color: #5f80a2;
      }
      #invoice-info .label {
        color: #5f80a2;
      }
      #title {
        font-size: 20px;
        margin-top: 10px;
      }
      #description {
        font-size: 12px;
      }
      #items {
        border-top: 1px solid #eee;
        margin-top: 10px;
        margin-bottom: 20px;
      }
      #items td {
        border-bottom: 1px solid #eee;
        font-size: 12px;
        padding: 5px 10px;
      }
      #items .th {
        color: #5f80a2;
      }
      #items .th td {
        background: #f8f8f8;
      }
      #totals {
        font-size: 16px;
        text-align: right;
      }
      .refund {
        color: #cf0404;
      }
      #notes span, #terms span {
        color: #5f80a2;
      }
      #terms p, #notes p {
        font-size: 12px;
      }
    </style>
  </head>
  <body>
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td>
          <table border="0" cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td rowspan="2" valign="top">
                <?php if ( $template->has_logo() ) : ?>
                  <div id="logo">
                    <img width="150" src="<?php echo $template->get_logo_url(); ?>" />
                  </div>
                <?php endif; ?>
              </td>
              <td id="company-info" align="center" valign="top">
                <?php echo $template->get_business_name(); ?>
                <?php if ( $template->has_business_address() ) : ?>
                  / <?php echo $template->get_business_address(); ?>
                <?php endif; ?>
                <?php if ( $template->has_business_phone() ) : ?>
                  / <?php echo $template->get_business_phone(); ?>
                <?php endif; ?>
                <?php if ( $template->has_business_email() ) : ?>
                  <br /><span class="business_email"><?php echo $template->get_business_email(); ?></span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <td align="center"><?php _e( ucfirst($template->get_type()), ud_get_wp_invoice_pdf()->domain ); ?> <span class="invoice-id">#<?php echo $template->get_ID(); ?></span> / <?php echo $template->get_issue_date(); ?></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table id="invoice-info" border="0" cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td valign="top" style="border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                <table border="0" cellspacing="0" cellpadding="5">
                  <?php if ( $template->has_due_date() ): ?>
                    <tr>
                      <td class="label" align="right" width="50%">
                        <?php _e('Due Date:', ud_get_wp_invoice_pdf()->domain); ?>
                      </td>
                      <td align="left" width="50%"><?php echo $template->get_due_date(); ?></td>
                    </tr>
                  <?php endif; ?>
                  <tr>
                    <td class="label" align="right" width="50%">
                      <?php _e('Amount Due:', ud_get_wp_invoice_pdf()->domain); ?>
                    </td>
                    <td align="left" width="50%"><?php echo $template->get_amount_due( $template->get_currency_sign() ); ?></td>
                  </tr>
                  <tr>
                    <td class="label" align="right" width="50%">
                      <?php _e('Attn:', ud_get_wp_invoice_pdf()->domain); ?>
                    </td>
                    <td align="left" width="50%"><?php echo $template->get_recepient_name(); ?></td>
                  </tr>
                </table>
              </td>
              <td width="49%" style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; ">
                <table border="0" cellspacing="0" cellpadding="5">
                  <tr>
                    <td class="label" align="right" width="25%" valign="top">
                      <?php _e('Bill To:', ud_get_wp_invoice_pdf()->domain); ?>
                    </td>
                    <td align="left" width="75%">
                      <span class="recipient-name"><?php echo $template->get_recepient_name(); ?></span>
                      <?php if ( $template->display_address() ): ?>
                        / <span><?php echo $template->get_company_address(); ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>

                  <tr>
                    <td class="label" align="right" width="25%" valign="top">
                      <?php _e('Phone:', ud_get_wp_invoice_pdf()->domain); ?>
                    </td>
                    <td align="left" width="75%">
                      <?php echo $template->get_recipient_phone(); ?>
                    </td>
                  </tr>

                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Invoice Content -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
        <td>
          <div id="title"><?php echo $template->get_title(); ?></div>
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