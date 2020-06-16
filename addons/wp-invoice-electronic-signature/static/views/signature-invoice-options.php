<?php
/**
 * Invoice Options template
 */
?>

<table class="form-table">
  <tr>
    <th><?php _e("Signature", ud_get_wp_invoice()->domain) ?></th>
    <td>
      <?php echo WPI_UI::checkbox("name=wpi_invoice[require_signature]&id=wpi_invoice_require_signature&value=true&label=".__('Require Signature Prior to Payment'), (!empty($this_invoice['require_signature']) && $this_invoice['require_signature'] == 'on') ? true : false) ?>
    </td>
  </tr>
</table>