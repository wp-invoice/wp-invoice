<?php

/**
 * Products Settings template
 * @since 3.09.5
 * @package wp-invoice
 */

?>

<table class="form-table">
  
  <!-- Labels -->
  <tr>
    <th><?php _e("Labels", WPI) ?></th>
    <td>
      <ul class="wpi_something_advanced_wrapper">
        <li><?php echo WPI_UI::checkbox("name=change_default_labels&class=wpi_show_advanced&group=wpi_settings[products]&value=true&label=" . __('Change default labels', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['change_default_labels'] ) ? $wpi_settings['products']['change_default_labels'] : self::$defaults['change_default_labels'] )); ?></li>
        <li class="wpi_advanced_option">
          <table class="wpi_products_labels">
            <tr>
              <td>
                <?php _e("Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=name&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['name'] ) ? $wpi_settings['products']['labels']['name'] : self::$defaults['labels']['name'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Singular Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=singular_name&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['singular_name'] ) ? $wpi_settings['products']['labels']['singular_name'] : self::$defaults['labels']['singular_name'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Menu Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=menu_name&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['menu_name'] ) ? $wpi_settings['products']['labels']['menu_name'] : self::$defaults['labels']['menu_name'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Admin Bar Name", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=name_admin_bar&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['name_admin_bar'] ) ? $wpi_settings['products']['labels']['name_admin_bar'] : self::$defaults['labels']['name_admin_bar'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Add New", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=add_new&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['add_new'] ) ? $wpi_settings['products']['labels']['add_new'] : self::$defaults['labels']['add_new'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Add New Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=add_new_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['add_new_item'] ) ? $wpi_settings['products']['labels']['add_new_item'] : self::$defaults['labels']['add_new_item'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("New Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=new_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['new_item'] ) ? $wpi_settings['products']['labels']['new_item'] : self::$defaults['labels']['new_item'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Edit Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=edit_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['edit_item'] ) ? $wpi_settings['products']['labels']['edit_item'] : self::$defaults['labels']['edit_item'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("View Item", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=view_item&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['view_item'] ) ? $wpi_settings['products']['labels']['view_item'] : self::$defaults['labels']['view_item'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("All Items", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=all_items&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['all_items'] ) ? $wpi_settings['products']['labels']['all_items'] : self::$defaults['labels']['all_items'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Search Items", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=search_items&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['search_items'] ) ? $wpi_settings['products']['labels']['search_items'] : self::$defaults['labels']['search_items'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Parent Item Colon", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=parent_item_colon&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['parent_item_colon'] ) ? $wpi_settings['products']['labels']['parent_item_colon'] : self::$defaults['labels']['parent_item_colon'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Not Found", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=not_found&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['not_found'] ) ? $wpi_settings['products']['labels']['not_found'] : self::$defaults['labels']['not_found'] ) ); ?>
              </td>
            </tr>
            <tr>
              <td>
                <?php _e("Not Found in Trash", WPI); ?>
              </td>
              <td>
                <?php echo WPI_UI::input("type=text&name=not_found_in_trash&group=wpi_settings[products][labels]&value=".( !empty( $wpi_settings['products']['labels']['not_found_in_trash'] ) ? $wpi_settings['products']['labels']['not_found_in_trash'] : self::$defaults['labels']['not_found_in_trash'] ) ); ?>
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
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][public]&value=true&label=" . __('Make Products public', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['public'] ) ? $wpi_settings['products']['post_type']['public'] : self::$defaults['post_type']['public'] )); ?>
          <div class="description"><?php _e( 'Whether Products are intended to be used publicly either via the admin interface or by front-end users.', WPI ); ?></div>
        </li>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][exclude_from_search]&value=true&label=" . __('Exclude Products from search', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['exclude_from_search'] ) ? $wpi_settings['products']['post_type']['exclude_from_search'] : self::$defaults['post_type']['exclude_from_search'] )); ?>
          <div class="description"><?php _e( 'Whether to exclude Products from front end search results.', WPI ); ?></div>
        </li>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][hierarchical]&value=true&label=" . __('Hierarchical Products', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['hierarchical'] ) ? $wpi_settings['products']['post_type']['hierarchical'] : self::$defaults['post_type']['hierarchical'] )); ?>
          <div class="description"><?php _e( 'Whether Products are hierarchical.', WPI ); ?></div>
        </li>
        <li>
          <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][has_archive]&value=true&label=" . __('Products have archives', WPI), WPI_Functions::is_true( !empty( $wpi_settings['products']['post_type']['has_archive'] ) ? $wpi_settings['products']['post_type']['has_archive'] : self::$defaults['post_type']['has_archive'] )); ?>
          <div class="description"><?php _e( 'Enables Products archives.', WPI ); ?></div>
        </li>
        <li>
          <?php _e( 'Products Menu Position', WPI ); ?><br />
          <?php echo WPI_UI::input("type=text&name=menu_position&group=wpi_settings[products][post_type]&value=".( !empty( $wpi_settings['products']['post_type']['menu_position'] ) ? $wpi_settings['products']['post_type']['menu_position'] : self::$defaults['post_type']['menu_position'] ) ); ?>
          <div class="description"><?php _e( 'The position in the menu order Products should appear.', WPI ); ?></div>
        </li>
        <li>
          <?php _e( 'Rewrite Slug', WPI ); ?><br />
          <?php echo WPI_UI::input("type=text&name=slug&group=wpi_settings[products][post_type][rewrite]&value=".( !empty( $wpi_settings['products']['post_type']['rewrite']['slug'] ) ? $wpi_settings['products']['post_type']['rewrite']['slug'] : self::$defaults['post_type']['rewrite']['slug'] ) ); ?>
          <div class="description"><?php _e( 'Customize the permalink structure slug. Leave blank to disable rewrites for Products.', WPI ); ?></div>
        </li>
        <li>
          <?php _e( 'Product should support', WPI ); ?><br />
          
          <ul>
            <li>
              <label for="wpi_post_type_support_title">
                <input id="wpi_post_type_support_title" type="checkbox" checked="checked" disabled="disabled" />
                <?php _e( 'Title', WPI ); ?>
              </label>
            </li>
            <li>
              <label for="wpi_post_type_support_content">
                <input id="wpi_post_type_support_content" type="checkbox" checked="checked" disabled="disabled" />
                <?php _e( 'Content', WPI ); ?>
              </label>
            </li>
            <li>
              <label for="wpi_post_type_support_custom_fields">
                <input id="wpi_post_type_support_content" type="checkbox" checked="checked" disabled="disabled" />
                <?php _e( 'Custom Fields', WPI ); ?>
              </label>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][supports][0]&value=thumbnail&label=".__( 'Thumbnails', WPI ), !empty( $wpi_settings['products']['post_type']['supports'] ) ? in_array( 'thumbnail', $wpi_settings['products']['post_type']['supports'] ) : in_array( 'thumbnail', self::$defaults['post_type']['supports'] ) ); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][supports][1]&value=excerpt&label=".__( 'Excerpt', WPI ), !empty( $wpi_settings['products']['post_type']['supports'] ) ? in_array( 'excerpt', $wpi_settings['products']['post_type']['supports'] ) : in_array( 'excerpt', self::$defaults['post_type']['supports'] ) ); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][supports][2]&value=trackbacks&label=".__( 'Trackbacks', WPI ), !empty( $wpi_settings['products']['post_type']['supports'] ) ? in_array( 'trackbacks', $wpi_settings['products']['post_type']['supports'] ) : in_array( 'trackbacks', self::$defaults['post_type']['supports'] ) ); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][supports][3]&value=comments&label=".__( 'Comments', WPI ), !empty( $wpi_settings['products']['post_type']['supports'] ) ? in_array( 'comments', $wpi_settings['products']['post_type']['supports'] ) : in_array( 'comments', self::$defaults['post_type']['supports'] ) ); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][supports][4]&value=page-attributes&label=".__( 'Page Attributes', WPI ), !empty( $wpi_settings['products']['post_type']['supports'] ) ? in_array( 'page-attributes', $wpi_settings['products']['post_type']['supports'] ) : in_array( 'page-attributes', self::$defaults['post_type']['supports'] ) ); ?>
            </li>
            <li>
              <?php echo WPI_UI::checkbox("name=wpi_settings[products][post_type][supports][5]&value=post-formats&label=".__( 'Post Formats', WPI ), !empty( $wpi_settings['products']['post_type']['supports'] ) ? in_array( 'post-formats', $wpi_settings['products']['post_type']['supports'] ) : in_array( 'post-formats', self::$defaults['post_type']['supports'] ) ); ?>
            </li>
          </ul>

        </li>
      </ul>
    </td>
  </tr>
  
</table>