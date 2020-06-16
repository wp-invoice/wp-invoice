<?php
/**
 * Output signature template
 */
?>

<script>
  var sig = <?php echo $signature['data']; ?>;

  jQuery(document).ready(function () {
    jQuery('.signedPad').signaturePad({displayOnly:true}).regenerate(sig);
  });
</script>

<section class="invoice_signature">
  <h4><?php _e('Recipient Signature'); ?></h4>
  <p class="name"><?php echo $signature['name']; ?></p>
  <div class="signedPad signed">
    <div class="sigWrapper">
      <canvas class="pad" width="370" height="130"></canvas>
    </div>
  </div>
</section>