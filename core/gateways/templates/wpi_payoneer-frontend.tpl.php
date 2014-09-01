<?php include_once WPI_Path.'/core/wpi_template_functions.php'; ?>
<form action="" method="POST" name="online_payment_form" id="online_payment_form-<?php print $this->type; ?>" class="wpi_checkout online_payment_form <?php print $this->type; ?> clearfix">
    <?php if ( !is_recurring() ): ?>
      <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
      <input type="hidden" id="wpi_form_type" name="type" value="<?php print $this->type; ?>" />
      <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php print $invoice['invoice_id']; ?>" />

      <div id="credit_card_information">

          <?php do_action('wpi_payment_fields_'.$this->type, $invoice); ?>
        
          <ul class="wpi_checkout_block payment_details" style="display: none;">
            <li class="section_title"><?php _e( "Payment Details", WPI ); ?></li>
            
            <?php 
            
              switch( strtolower( $invoice['default_currency_code'] ) ) {
                
                case 'usd':
                  
                  ?>
                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label"><?php _e( "Bank Name", WPI ); ?>:</label>
                      <label class="controls">
                        <?php echo !empty($invoice['billing'][$this->type]['settings']['usd_bank_name']['value'])?$invoice['billing'][$this->type]['settings']['usd_bank_name']['value']:__('Not set', WPI); ?>
                      </label>
                    </div>
                  </li>
                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label"><?php _e( "Account Number", WPI ); ?>:</label>
                      <label class="controls">
                        <?php echo !empty($invoice['billing'][$this->type]['settings']['usd_account_number']['value'])?$invoice['billing'][$this->type]['settings']['usd_account_number']['value']:__('Not set', WPI); ?>
                      </label>
                    </div>
                  </li>
                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label"><?php _e( "ABA (Bank Routing Number)", WPI ); ?>:</label>
                      <label class="controls">
                        <?php echo !empty($invoice['billing'][$this->type]['settings']['usd_bank_routing_number']['value'])?$invoice['billing'][$this->type]['settings']['usd_bank_routing_number']['value']:__('Not set', WPI); ?>
                      </label>
                    </div>
                  </li>
                  <?php
                  
                  break;
                
                case 'eur':
                  
                  ?>
                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label"><?php _e( "Bank Name", WPI ); ?>:</label>
                      <label class="controls">
                        <?php echo !empty($invoice['billing'][$this->type]['settings']['euro_bank_name']['value'])?$invoice['billing'][$this->type]['settings']['euro_bank_name']['value']:__('Not set', WPI); ?>
                      </label>
                    </div>
                  </li>
                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label"><?php _e( "BIC", WPI ); ?>:</label>
                      <label class="controls">
                        <?php echo !empty($invoice['billing'][$this->type]['settings']['euro_bic']['value'])?$invoice['billing'][$this->type]['settings']['euro_bic']['value']:__('Not set', WPI); ?>
                      </label>
                    </div>
                  </li>
                  <li class="wpi_checkout_row">
                    <div class="control-group">
                      <label class="control-label"><?php _e( "IBAN", WPI ); ?>:</label>
                      <label class="controls">
                        <?php echo !empty($invoice['billing'][$this->type]['settings']['euro_iban']['value'])?$invoice['billing'][$this->type]['settings']['euro_iban']['value']:__('Not set', WPI); ?>
                      </label>
                    </div>
                  </li>
                  <?php
                  
                  break;
                
                default:
                  
                  ?>
                  <li class="wpi_checkout_row">
                    <?php echo sprintf( __( 'Sorry, no Payment Details for currency of %s. Please contact seller for the information.', WPI ), $invoice['default_currency_code'] ); ?>
                  </li>
                  <?php
                  
                  break;
                
              } 
            ?>
            
          </ul>

          <ul id="wp_invoice_process_wait">
              <li>
                  <div class="wpi-control-group">
                      <div class="controls">
                          <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php _e('Process Payer Information', WPI); ?></button>
                      </div>
                      <img style="display: none;" class="loader-img" src="<?php echo WPI_URL; ?>/core/css/images/processing-ajax.gif" alt="" />
                  </div>
              </li>
          </ul>

      </div>
    <?php else: ?>
      <p><?php _e( 'This payment gateway does not support Recurring Billing. Try another one or contact site Administrator.', WPI ); ?></p>
    <?php endif; ?>
