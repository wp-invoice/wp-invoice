<?php

/**
 * Products Settings template
 * @since 3.09.5
 * @package wp-invoice
 */

echo '<pre>';
print_r( $wpi_settings['products'] );
echo '</pre>';

?>

<table class="form-table">
  
  <!-- Labels -->
  <tr>
    <th><?php _e("Labels", WPI) ?></th>
    <td>
      <ul class="wpi_something_advanced_wrapper">
        <li><?php echo WPI_UI::checkbox("name=change_default_labels&class=wpi_show_advanced&group=wpi_settings[products]&value=true&label=" . __('Change default labels', WPI), WPI_Functions::is_true($wpi_settings['products']['change_default_labels'])); ?></li>
        <li class="wpi_advanced_option">
          <table class="wpi_products_labels">
            <tr>
              <td>
                <?php _e("Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=name&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['name'] ) ? $wpi_settings['products']['labels']['name'] : __('Products', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Singular Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=singular_name&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['singular_name'] ) ? $wpi_settings['products']['labels']['singular_name'] : __('Product', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Menu Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=menu_name&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['menu_name'] ) ? $wpi_settings['products']['labels']['menu_name'] : __('Products', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Admin Bar Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=name_admin_bar&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['name_admin_bar'] ) ? $wpi_settings['products']['labels']['name_admin_bar'] : __('Product', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Add New", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=add_new&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['add_new'] ) ? $wpi_settings['products']['labels']['add_new'] : __('Add New', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Add New Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=add_new_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['add_new_item'] ) ? $wpi_settings['products']['labels']['add_new_item'] : __('Add New Product', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("New Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=new_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['new_item'] ) ? $wpi_settings['products']['labels']['new_item'] : __('New Product', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Edit Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=edit_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['edit_item'] ) ? $wpi_settings['products']['labels']['edit_item'] : __('Edit Product', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("View Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=view_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['view_item'] ) ? $wpi_settings['products']['labels']['view_item'] : __('View Product', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("All Items", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=all_items&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['all_items'] ) ? $wpi_settings['products']['labels']['all_items'] : __('All Products', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Search Items", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=search_items&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['search_items'] ) ? $wpi_settings['products']['labels']['search_items'] : __('Search Products', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Parent Item Colon", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=parent_item_colon&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['parent_item_colon'] ) ? $wpi_settings['products']['labels']['parent_item_colon'] : __('Parent Products:', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Not Found", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=not_found&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['not_found'] ) ? $wpi_settings['products']['labels']['not_found'] : __('No Products Found', WPI) ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Not Found in Trash", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=not_found_in_trash&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['not_found_in_trash'] ) ? $wpi_settings['products']['labels']['not_found_in_trash'] : __('No Products Found in Trash', WPI) ) ); ?>
              </td>
            </tr>
          </table>
        </li>
      </ul>
    </td>
  </tr>
  
  <!-- Objects Settings -->
  <tr>
    <th><?php _e("Objects Settings", WPI) ?></th>
    <td>
      <ul>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][public]&value=true&label=" . __('Make Products public', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['public'] ) ? $wpi_settings['products']['post_type']['public'] : true )); ?>
          <div class="description"><?php _e( 'Whether Products are intended to be used publicly either via the admin interface or by front-end users.', WPI ); ?></div>
        </li>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][exclude_from_search]&value=true&label=" . __('Exclude Products from search', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['exclude_from_search'] ) ? $wpi_settings['products']['post_type']['exclude_from_search'] : false )); ?>
          <div class="description"><?php _e( 'Whether to exclude Products from front end search results.', WPI ); ?></div>
        </li>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][hierarchical]&value=true&label=" . __('Hierarchical Products', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['hierarchical'] ) ? $wpi_settings['products']['post_type']['hierarchical'] : false )); ?>
          <div class="description"><?php _e( 'Whether Products are hierarchical.', WPI ); ?></div>
        </li>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][has_archive]&value=true&label=" . __('Products have archives', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['has_archive'] ) ? $wpi_settings['products']['post_type']['has_archive'] : true )); ?>
          <div class="description"><?php _e( 'Enables Products archives.', WPI ); ?></div>
        </li>
        <li>
          <?php _e( 'Products Menu Position', WPI ); ?><br />
          <?php echo WPI_UI::input("type=text&name=menu_position&group=wpi_settings[products][post_type]&value=".( !empty( $wpi_settings['products']['post_type']['menu_position'] ) ? $wpi_settings['products']['post_type']['menu_position'] : 80 ) ); ?>
          <div class="description"><?php _e( 'The position in the menu order Products should appear.', WPI ); ?></div>
        </li>
        <li>
          <?php _e( 'Rewrite Slug', WPI ); ?><br />
          <?php echo WPI_UI::input("type=text&name=slug&group=wpi_settings[products][post_type][rewrite]&value=".( !empty( $wpi_settings['products']['post_type']['rewrite']['slug'] ) ? $wpi_settings['products']['post_type']['rewrite']['slug'] : 'product' ) ); ?>
          <div class="description"><?php _e( 'Customize the permalink structure slug. Leave blank to disable rewrites for Products.', WPI ); ?></div>
        </li>
      </ul>
    </td>
  </tr>
  
</table>