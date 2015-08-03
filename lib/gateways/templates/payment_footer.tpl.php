
<?php do_action( 'wpi_payment_form_end' ); ?>

</form>
<?php if(!$from_ajax): ?>
</div><?php /** end .online_payment_form_wrapper */ ?>
<div class="clear"></div>
<div id="wpi_gateway_form_errors"></div>
<?php endif; ?>