<?php
  global $wpi_settings;
  $user_information = apply_filters('wpi_user_information', $wpi_settings['user_meta']['custom']);
  //$user_information = array_merge($wpi_settings['user_meta']['required'], $custom_user_information);

 ?>



  <h3><?php _e('Billing / Invoicing Info', WPI) ?></h3>
  <a name="billing_info"></a>
  <table class="form-table" >

  <?php foreach ($user_information as $field_id => $field_name) { ?>
  <tr>
        <th><?php _e($field_name, WPI) ?></th>
        <td><?php echo WPI_UI::input("type=text&class=regular-text&name=$field_id&value=".get_user_meta($user_id, $field_id, true)); ?></td>
    </tr>
  <?php } ?>



  <tr>
  <th></th>
  <td>


  </td>
  </tr>


</table>