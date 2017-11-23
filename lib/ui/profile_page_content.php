<?php
  global $wpi_settings;
  $user_information = apply_filters('wpi_user_information', $wpi_settings['user_meta']['custom']);
  $email = isset($wpi_settings['email_address']) ? "&email=".$wpi_settings['email_address']:'';
?>
<table class="form-table" ><tr>
      <th><h3><?php _e('Billing / Invoicing Info', ud_get_wp_invoice()->domain) ?></h3></th>
      <td>
        <?php if ( current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ): ?>
          <input type="button" onclick="window.location.href='<?php echo $wpi_settings['links']['manage_invoice'].$email; ?>'" class="button" value="<?php echo  __('Send New Invoice', ud_get_wp_invoice()->domain) ?>" id="crm_new_invioce"/>
        <?php endif; ?>
      </td>
    </tr>
</table>
<a name="billing_info"></a>
<table class="form-table" >

  <?php foreach ($user_information as $field_id => $field_name) { ?>
    <tr>
      <th><?php _e($field_name, ud_get_wp_invoice()->domain) ?></th>
      <td>
        <?php
        echo WPI_UI::input(array(
            'type' => 'text',
            'class' => 'regular-text',
            'name' => $field_id,
            'value' => get_user_meta($user_id, $field_id, true)
        ));
        ?>
      </td>
    </tr>
  <?php } ?>
  <tr>
    <th></th>
    <td></td>
  </tr>
</table>