<?php
/**
 * Signature UI markup
 */
?>
<div class="sigPad">
  <span class="sigTitle"><?php _e( 'Signature required', ud_get_wp_invoice_electronic_signature()->domain ); ?></span>
  <label for="name"><?php _e( 'Print your name', ud_get_wp_invoice_electronic_signature()->domain ); ?></label>
  <input type="text" name="signature[name]" id="name" class="name">
  <p class="drawItDesc"><?php _e( 'Draw your signature', ud_get_wp_invoice_electronic_signature()->domain ); ?></p>
  <ul class="sigNav">
    <li class="clearButton"><a href="#clear"><?php _e( 'Clear', ud_get_wp_invoice_electronic_signature()->domain ); ?></a></li>
  </ul>
  <div class="sig sigWrapper">
    <div class="typed"></div>
    <canvas class="pad" width="370" height="130"></canvas>
    <input type="hidden" name="signature[output]" class="output">
  </div>
</div>