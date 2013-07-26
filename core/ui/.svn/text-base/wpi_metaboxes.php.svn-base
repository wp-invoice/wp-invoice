<?php
/**
 * Metaboxes for the main overview page
 *
 * @since 3.0
 *
 */

class toplevel_page_wpi_main {

  /**
   * Actions metabox used for primary filtering purposes
   *
   *
   * @uses CRM_User_List_Table class
   * @since 0.01
   *
   */
  function filter($wp_list_table) {
    global $wpi_settings;
    ?>
    <div class="misc-pub-section">

      <?php $wp_list_table->search_box( 'Search', 'post' ); ?>

      <?php $filters = WPI_Functions::get_search_filters(); ?>

      <?php $users = wpi_invoice_users_dropdown('wpi_object', '', true); ?>

      <?php
      /**
       * Filter by Type
       */
      if ( !empty( $filters['type'] ) && is_array( $filters['type'] ) ) : ?>

        <ul class="wpi_overview_filters type">
          <li class="wpi_filter_section_title">Type<a class="wpi_filter_show">Show</a></li>
          <li class="all wpi_checkbox_filter">
            <ul>
              <?php foreach ( $filters['type'] as $item ) : ?>
                <li class="type">
                  <input type="radio" id="wpi_filter_type_<?php echo 'type_'. $item['key']; ?>" value="<?php echo ($item['key'] != 'all' ? $item['key'] : ''); ?>" name="wpi_search[type]" <?php echo ($item['key'] == 'all' ? 'checked="checked"' : ''); ?> /> <label for="wpi_filter_type_<?php echo 'type_'. $item['key']; ?>"><?php echo $item['label']; ?> <span class="count">(<?php echo $item['amount']; ?>)</span></label>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        </ul>

      <?php endif; ?>

      <?php
      /**
       * Filter by Status
       */
      if ( !empty( $filters['status'] ) && is_array( $filters['status'] ) ) : ?>

        <ul class="wpi_overview_filters status">
          <li class="wpi_filter_section_title"><?php _e('Status', WPI) ?><a class="wpi_filter_show"><?php _e('Hide', WPI) ?></a></li>
          <li class="all wpi_checkbox_filter" style="display:block;">
            <ul>
              <?php foreach ( $filters['status'] as $item ) : ?>
                <li class="status">
                  <input type="checkbox" <?php echo $item['key']=='active'?'checked="checked"':'' ?> id="wpi_filter_type_<?php echo 'status_'. $item['key']; ?>" value="<?php echo ($item['key'] != 'all' ? $item['key'] : ''); ?>" name="wpi_search[status][]"> <label for="wpi_filter_type_<?php echo 'status_'. $item['key']; ?>"><?php echo $item['label']; ?> <span class="count">(<?php echo $item['amount']; ?>)</span></label>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        </ul>

      <?php endif; ?>

      <?php if ( !empty( $users ) && is_array( $users ) ) : ?>

        <ul class="wpi_overview_filters users">
          <li class="wpi_filter_section_title"><?php _e('Recipient', WPI) ?><a class="wpi_filter_show"><?php _e('Show', WPI) ?></a></li>
          <li class="all wpi_checkbox_filter">
            <?php wpi_invoice_users_dropdown('wpi_object', 'wpi_search[recipient]'); ?>
          </li>
        </ul>

      <?php endif; ?>

      <?php /* Filter by Date */ ?>
      <?php $months_dropdown = $wp_list_table->months_dropdown('wpi_object', 'wpi_search[m]', true); ?>
      <?php if (!empty($months_dropdown)) : ?>
      <ul class="wpi_overview_filters month">
        <li class="wpi_filter_section_title"><?php _e('Date', WPI) ?><a class="wpi_filter_show"><?php _e('Show', WPI) ?></a></li>
        <li class="all wpi_checkbox_filter">
          <?php echo $months_dropdown; ?>
        </li>
      </ul>
      <?php endif; ?>

      <?php do_action('wpi_invoice_list_filter'); ?>

    </div>

    <div class="major-publishing-actions">
      <?php do_action( 'wpi_other_actions' ); ?>
      <div class="publishing-action">
        <?php submit_button( __('Filter Results', WPI), 'button', false, false, array('id' => 'search-submit') ); ?>
      </div>
      <br class='clear' />
    </div>
    <div class="wpi_other_actions">
      <?php do_action( 'wpi_after_actions' ); ?>
    </div>
    <?php
  }

}