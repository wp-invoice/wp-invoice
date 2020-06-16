<?php if ( empty( $this->locations ) ): ?>
  <p><?php printf( __( 'You do not have Business Locations yet. Create one <a href="%s">here.</a>', ud_get_wp_invoice_business_locations()->domain ), admin_url('edit.php?post_type=wpi_bl') ); ?></p>
<?php else: ?>
  <ul>
  <?php foreach( $this->locations as $bl ): ?>
    <li>
      <label>
        <input <?php checked( $bl->ID, !empty($invoice['business_location'])?$invoice['business_location']:'-1' ); ?> type="radio" value="<?php echo $bl->ID; ?>" name="wpi_invoice[business_location]" />
        <?php echo $bl->post_title; ?>
      </label>
    </li>
  <?php endforeach; ?>
    <li>
      <label>
        <input <?php checked( '-1', !empty($invoice['business_location'])?$invoice['business_location']:'-1' ); ?> type="radio" value="-1" name="wpi_invoice[business_location]" />
        <?php _e('Default'); ?>
      </label>
    </li>
  </ul>
<?php endif; ?>