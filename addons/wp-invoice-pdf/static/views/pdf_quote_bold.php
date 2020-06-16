<?php
/**
 * Template Name: Bold
 * Thumbnail: static/images/bold.jpg
 */
?>

<html>
  <head>
    <style type="text/css">
      body {
        font-family: 'NotoSerif', "DejaVu Sans";
        font-size: 16px;
      }
      #title {
        margin-bottom: 10px;
      }
      #totals td {
        text-align: right;
        font-weight: 700;
        padding-left: 10px;
      }
      #totals td span {
        color: #bc4873;
      }
      .refund {
        color: #cf0404;
      }
    </style>
  </head>
  <body>
    <table border="0" cellspacing="5" cellpadding="5" width="100%">
      <tr>
        <td style="border-bottom: 1px solid #cdcdcd;">
          <table border="0" cellspacing="3" cellpadding="3" width="100%">
            <tr>
              <?php if ( $template->has_logo() ) : ?>
                <td width="30%"><img src="<?php echo $template->get_logo_url(); ?>" style="max-width: 200px;"/></td>
                <td align="center" width="70%">
              <?php else: ?>
                <td align="center">
              <?php endif; ?>
                  <small>
                    <strong>
                      <?php echo $template->get_business_name(); ?>
                      <?php if ( $template->has_business_address() ) : ?>
                        <div style="color: #7a7a7a"><?php echo $template->get_business_address(); ?></div>
                      <?php endif; ?>
                      <?php if ( $template->has_business_phone() ) : ?>
                        <?php echo $template->get_business_phone(); ?>
                      <?php endif; ?>
                      <?php if ( $template->has_business_email() ) : ?>
                        <br />
                        <?php echo $template->get_business_email(); ?>
                      <?php endif; ?>
                    </strong>
                  </small>
                </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table border="0" cellspacing="5" cellpadding="5" width="100%">
            <tr>
              <td align="center">
                <strong>
                  <span>
                    <?php _e( strtoupper($template->get_type()), ud_get_wp_invoice_pdf()->domain ); ?>
                    <span style="color: #50e6b1">#</span><span style="color: #3e8eaf"><?php echo $template->get_ID(); ?></span>
                  </span>
                  <br/>
                  <div style="color: #b8b8b8">
                    <?php _e( 'ISSUED:', ud_get_wp_invoice_pdf()->domain ); ?>
                    <?php echo $template->get_issue_date(); ?>
                  </div>
                  <?php if ( $template->has_due_date() ): ?>
                  <div style="color: #7a7a7a;">
                    <?php _e( 'DUE:', ud_get_wp_invoice_pdf()->domain ); ?>
                    <?php echo $template->get_due_date(); ?>
                  </div>
                  <?php endif; ?>
                  <div>
                    <?php _e('AMOUNT DUE:', ud_get_wp_invoice_pdf()->domain); ?>
                    <span style="color: #bc4873"><?php echo $template->get_amount_due( $template->get_currency_sign() ); ?></span>
                  </div>
                </strong>
              </td>
              <td width="50%" align="center">
                <span style="color: #22b273">
                  <b><?php echo $template->get_recepient_name(); ?></b>
                </span>
                <br />
                <?php if ( $template->display_address() ): ?>
                <span style="color: #7a7a7a"><b><?php echo $template->get_company_address(); ?></b></span>
                <br />
                <?php endif; ?>
                <strong><?php echo $template->get_recipient_phone(); ?></strong>
                <br />
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="border-top: 1px solid #cdcdcd;;color: #474747;">
          <div id="title"><?php echo $template->get_title(); ?></div>
          <?php if ( $template->has_description() ): ?>
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
      <div style="padding: 0 10px;font-weight: 700;"><?php _e( 'ITEMS', ud_get_wp_invoice_pdf()->domain ); ?></div>
      <table style="margin: 7px;" width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
          <td style="color: #d4d4d4;" width=""><strong><?php _e( 'DESCRIPTION', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
          <td style="color: #d4d4d4;" width=""><strong><?php _e( 'QTY', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
          <td style="color: #d4d4d4;" width=""><strong><?php _e( 'PRICE', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
          <td style="color: #d4d4d4;" width=""><strong><?php _e( 'SUM', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
          <?php if ( $template->get_total_tax() ): ?>
            <td style="color: #d4d4d4;" width=""><strong><?php _e( 'TAX', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
          <?php endif; ?>
        </tr>
        <?php while( $item = $template->get_item() ): ?>
          <tr>
            <td valign="top" bgcolor="#d4d4d4" style="text-transform: uppercase;">
              <b><?php echo $item->get_name(); ?></b>
              <?php if ( $description = $item->get_description() ) : ?>
                <br /><small style="text-transform: none;"><?php echo $description; ?></small>
              <?php endif; ?>
            </td>
            <td valign="top" bgcolor="#d4d4d4" style="text-transform: uppercase"><b><?php echo $item->get_quantity(); ?></b></td>
            <td valign="top" bgcolor="#d4d4d4" style="color: #3e8eaf; text-transform: uppercase"><b><?php echo $item->get_price( $template->get_currency_sign() ); ?></b></td>
            <td valign="top" bgcolor="#d4d4d4" style="color: #bc4873; text-transform: uppercase"><b><?php echo $item->get_amount( $template->get_currency_sign() ); ?></b></td>
            <?php if ( $template->get_total_tax() ): ?>
              <td valign="top" bgcolor="#d4d4d4" style="text-transform: uppercase; color: #3e8eaf;" width="">
                <b><?php echo $item->get_tax( $template->get_currency_sign() ); ?></b>
              </td>
            <?php endif; ?>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php endif; ?>

    <?php if ( $template->has_charges() ): ?>
      <div style="padding: 0 10px;font-weight: 700;"><?php _e( 'CHARGES', ud_get_wp_invoice_pdf()->domain ); ?></div>
      <table style="margin: 7px;" width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
          <td style="color: #d4d4d4;" width=""><strong><?php _e( 'DESCRIPTION', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
          <td style="color: #d4d4d4;" width=""><strong><?php _e( 'AMOUNT', ud_get_wp_invoice_pdf()->domain ); ?></strong></td>
        </tr>
        <?php while( $charge = $template->get_charge() ): ?>
          <tr>
            <td valign="top" bgcolor="#d4d4d4" style="text-transform: uppercase;">
              <b><?php echo $charge->get_name(); ?></b>
            </td>
            <td width="15%" valign="top" bgcolor="#d4d4d4" style="text-transform: uppercase;color: #bc4873;"><b><?php echo $charge->get_amount( $template->get_currency_sign() ); ?></b></td>
          </tr>
        <?php endwhile; ?>
      </table>
    <?php endif; ?>

    <hr/>

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
          <td style="border-bottom: 1px solid #474747;color: #474747;"><?php _e( 'TERMS &amp; CONDITIONS', ud_get_wp_invoice_pdf()->domain ); ?></td>
        </tr>
        <tr>
          <td style="color: #474747; font-size: 0.8em;"><?php echo $template->get_terms(); ?></td>
        </tr>
      </table>
    <?php endif; ?>

    <?php if ( $template->has_notes() ) : ?>
      <table border="0" cellspacing="5" cellpadding="5" width="100%">
        <tr>
          <td style="border-bottom: 1px solid #474747;color: #474747;"><?php _e('NOTES', ud_get_wp_invoice_pdf()->domain); ?></td>
        </tr>
        <tr>
          <td style="color: #474747; font-size: 0.8em;"><?php echo $template->get_notes(); ?></td>
        </tr>
      </table>
    <?php endif; ?>

  </body>
</html>