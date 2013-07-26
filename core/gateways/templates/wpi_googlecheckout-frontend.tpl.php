<?php include_once WPI_Path.'/core/wpi_template_functions.php'; ?>
<form action="<?php echo $invoice['billing']['wpi_googlecheckout']['settings']['test_mode']['value']; ?><?php echo $invoice['billing']['wpi_googlecheckout']['settings']['merchant_id']['value'] ?>" method="post" name="online_payment_form" id="online_payment_form-<?php echo $this->type; ?>" class="wpi_checkout online_payment_form <?php echo $this->type; ?> clearfix">
    <input type="hidden" id="wpi_action" name="wpi_action" value="wpi_gateway_process_payment" />
    <input type="hidden" id="wpi_form_type" name="type" value="<?php echo $this->type; ?>" />
    <input type="hidden" id="wpi_form_invoice_id" name="invoice_id" value="<?php echo $invoice['invoice_id']; ?>" />

    <?php if ( !is_recurring() ): ?>
    <!-- Line items -->
    <?php
      $i = 1;
      if ( !empty( $invoice['itemized_list'] ) )
        foreach( $invoice['itemized_list'] as $list_item ) :
    ?>
          <input name="item_name_<?php echo $i; ?>" type="hidden" value="<?php echo $list_item['name']; ?>"/>
          <input name="item_description_<?php echo $i; ?>" type="hidden" value="<?php echo $list_item['description']; ?>"/>
          <input name="item_quantity_<?php echo $i; ?>" type="hidden" value="<?php echo $list_item['quantity']; ?>"/>
          <input name="item_price_<?php echo $i; ?>" type="hidden" value="<?php echo $list_item['price']; ?>"/>
          <input name="item_currency_<?php echo $i; ?>" type="hidden" value="<?php echo $invoice['default_currency_code']; ?>"/>
    <?php
          $i++;
        endforeach;
    ?>
    <!-- /Line items -->

    <!-- Line Charges -->
    <?php
      if ( !empty( $invoice['itemized_charges'] ) )
        foreach( $invoice['itemized_charges'] as $list_item ) :
    ?>
          <input name="item_name_<?php echo $i; ?>" type="hidden" value="<?php echo $list_item['name']; ?>"/>
          <input name="item_description_<?php echo $i; ?>" type="hidden" value=""/>
          <input name="item_quantity_<?php echo $i; ?>" type="hidden" value="1"/>
          <input name="item_price_<?php echo $i; ?>" type="hidden" value="<?php echo $list_item['amount']; ?>"/>
          <input name="item_currency_<?php echo $i; ?>" type="hidden" value="<?php echo $invoice['default_currency_code']; ?>"/>
    <?php
          $i++;
        endforeach;
    ?>
    <!-- /Line Charges -->

    <!-- Tax -->
    <?php
      if ( !empty( $invoice['total_tax'] ) ) :
    ?>
        <input name="item_name_<?php echo $i; ?>" type="hidden" value="<?php _e( 'Tax', WPI ); ?>"/>
        <input name="item_description_<?php echo $i; ?>" type="hidden" value=""/>
        <input name="item_quantity_<?php echo $i; ?>" type="hidden" value="1"/>
        <input name="item_price_<?php echo $i; ?>" type="hidden" value="<?php echo $invoice['total_tax']; ?>"/>
        <input name="item_currency_<?php echo $i; ?>" type="hidden" value="<?php echo $invoice['default_currency_code']; ?>"/>
    <?php
        $i++;
      endif;
    ?>
    <!-- /Tax -->

    <!-- Discount -->
    <?php
      if ( !empty( $invoice['total_discount'] ) ) :
    ?>
        <input name="item_name_<?php echo $i; ?>" type="hidden" value="<?php _e( 'Total Discount', WPI ); ?>"/>
        <input name="item_description_<?php echo $i; ?>" type="hidden" value=""/>
        <input name="item_quantity_<?php echo $i; ?>" type="hidden" value="1"/>
        <input name="item_price_<?php echo $i; ?>" type="hidden" value="-<?php echo $invoice['total_discount']; ?>"/>
        <input name="item_currency_<?php echo $i; ?>" type="hidden" value="<?php echo $invoice['default_currency_code']; ?>"/>
    <?php
        $i++;
      endif;
    ?>
    <!-- /Discount -->

    <?php else: ?>

      <!-- Recurring -->

      <input type="hidden" name="shopping-cart.items.item-1.item-name" value="<?php echo $invoice['post_title']; ?>"/>
      <input type="hidden" name="shopping-cart.items.item-1.item-description" value="<?php echo strip_tags($invoice['post_content']); ?>"/>
      <input type="hidden" name="shopping-cart.items.item-1.unit-price.currency" value="<?php echo $invoice['default_currency_code']; ?>"/>
      <input type="hidden" name="shopping-cart.items.item-1.unit-price" value="0.00"/>
      <input type="hidden" name="shopping-cart.items.item-1.quantity" value="1"/>

      <?php if ( !empty( $invoice['recurring'] ) && $invoice['recurring']['send_invoice_automatically'] == 'off' ): ?>
        <input type="hidden" name="shopping-cart.items.item-1.subscription.start-date" value="<?php echo $invoice['recurring']['start_date']['year'].'-'.$invoice['recurring']['start_date']['month'].'-'.$invoice['recurring']['start_date']['day']; ?>"/>
      <?php endif; ?>

      <?php if ( !empty( $invoice['recurring'] ) &&
                 !empty( $invoice['recurring']['google_no_charge_after']['month'] ) &&
                 !empty( $invoice['recurring']['google_no_charge_after']['day'] ) &&
                 !empty( $invoice['recurring']['google_no_charge_after']['year'] ) ): ?>
        <input type="hidden" name="shopping-cart.items.item-1.subscription.no-charge-after" value="<?php echo $invoice['recurring']['google_no_charge_after']['year'].'-'.$invoice['recurring']['google_no_charge_after']['month'].'-'.$invoice['recurring']['google_no_charge_after']['day']; ?>"/>
      <?php endif; ?>

      <input type="hidden" name="shopping-cart.items.item-1.subscription.type" value="google"/>
      <input type="hidden" name="shopping-cart.items.item-1.subscription.period" value="<?php echo $invoice['recurring']['google_billing_period']; ?>"/>
      <input type="hidden" name="shopping-cart.items.item-1.subscription.payments.subscription-payment-1.times" value="<?php echo $invoice['recurring']['cycles']; ?>">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.payments.subscription-payment-1.maximum-charge" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.payments.subscription-payment-1.maximum-charge.currency" value="<?php echo $invoice['default_currency_code']; ?>">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.recurrent-item.item-name" value="<?php echo $invoice['post_title']; ?>">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.recurrent-item.item-description" value="<?php echo $invoice['post_title']; ?>">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.recurrent-item.quantity" value="1">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.recurrent-item.unit-price" value="<?php echo number_format( (float)$invoice['net'], 2, '.', '' ); ?>">
      <input type="hidden" name="shopping-cart.items.item-1.subscription.recurrent-item.unit-price.currency" value="<?php echo $invoice['default_currency_code']; ?>">

    <?php endif; ?>

    <input name="shopping-cart.buyer-messages.special-instructions-1" type="hidden" value="<?php echo $invoice['invoice_id']; ?>"/>
    <input name="_charset_" type="hidden" value="utf-8"/>

    <div id="credit_card_information">

      <?php do_action('wpi_payment_fields_googlecheckout', $invoice); ?>

      <ul id="wp_invoice_process_wait">
        <li>
          <div class="wpi-control-group">
            <div class="controls">
              <button type="submit" id="cc_pay_button" class="hide_after_success submit_button"><?php echo sprintf(__('Process Payment of %s', WPI), (!empty($wpi_settings['currency']['symbol'][$invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$invoice['default_currency_code']] : "$")); ?><span id="pay_button_value"><?php echo WPI_Functions::money_format($invoice['net']); ?></span></button>
            </div>
            <img style="display: none;" class="loader-img" src="<?php echo WPI_URL; ?>/core/css/images/processing-ajax.gif" alt="" />
          </div>
        </li>
      </ul>
      <br class="cb" />
    </div>