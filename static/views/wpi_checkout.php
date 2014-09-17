<style type="text/css">
  li.wpi_checkout_row_purchase_hs {
    margin-bottom: 10px;
    overflow: auto;
    margin-top: 10px;
  }
  li.wpi_checkout_row_purchase_hs label {
      float: right;
      left: -16px;
      margin-bottom: 13px;
      width: 310px;
  }
  li.wpi_checkout_row_purchase_hs a {
      color: #F1DD32;
  }
  .wpi_spc_notice {
    font-size: 0.9em;
  }
  .wpi_spc_notice b {
    font-weight: bold;
    padding-right: 10px;
    text-decoration: underline;
  }
</style>

<?php /** If there are available gateways in WP-Invoice */ ?>
<?php if ( is_array( $available_gateways ) && !empty( $available_gateways ) ): ?>

  <?php /** If there are more then 1 gateway - show select */ ?>
  <?php if ( count( $available_gateways ) > 1 ): ?>
    <div class="wpi_checkout_payment_box">
      <ul class="wpi_checkout_block wpi_checkout_method_selection">
        <li class="section_title"><?php _e('Payment Method', WPI); ?></li>
        <li class="form-horizontal wpi_checkout_row">
          <div class="control-group">
            <label class="control-label"><?php _e( 'Method', WPI ); ?></label>
            <div class="controls">
              <select class="wpi_checkout_select_payment_method_dropdown">
              <?php
                foreach( $available_gateways as $key => $val ):
              ?>
                  <option <?php echo $val['default_option'] == 'true' ? 'selected="selected"' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php _e( $val['name'], WPI ); ?></option>
              <?php
                endforeach;
              ?>
              </select>
            </div>
          </div>
        </li>
      </ul>
    </div>
  <?php endif; ?>

  <?php
  /** For each available gateway - draw payment form for current gateway */
  foreach( $available_gateways as $gateway_key => $gateway ) {

    /**
     * If there is one gateway, display it, otherwise display default_option
     */
    $display = true;
    if ( count($available_gateways) > 1 ) {
     if ( $gateway['default_option'] == 'true' ) {
       $display = true;
     } else {
       $display = false;
     }
    } else {
     $display = true;
    }

    $template_found = UD_API::get_template_part( array(
      "{$gateway_key}-checkout-{$template}",
      "{$gateway_key}-checkout-{$template}.tpl",
      "{$gateway_key}-checkout",
      "{$gateway_key}-checkout.tpl",
    ), WPI_Gateways_Path . '/templates' );

    if( $template_found ) {
      include $template_found;
    }

	}
  ?>

<?php else: ?>
  <p class="wpi_checkout_gateways_error"><?php _e('Specified gateways are not available', WPI); ?></p>
<?php endif; ?>