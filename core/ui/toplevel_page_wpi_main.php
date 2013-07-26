<?php

if(isset($_REQUEST['message']) && $_REQUEST['message'] == 'user_deleted') {
  //WP_wpi_F::add_message(__('User has been deleted.'));
}

include WPI_Path . '/core/ui/class_wpi_object_list_table.php';

$wp_list_table = new WPI_Object_List_Table("per_page=25");

$wp_list_table->prepare_items();

$wp_list_table->data_tables_script();

?>
<div class="wp_wpi_overview_wrapper wrap">
  <?php do_action( 'wpi_before_overview' ); ?>
  <?php screen_icon(); ?>
  <h2><?php _e('Overview', WPI); ?> <a href="<?php echo admin_url('admin.php?page=wpi_page_manage_invoice'); ?>" class="button add-new-h2"><?php _e('Add New', WPI); ?></a></h2>
  <?php WPI_Functions::print_messages(); ?>

  <form id="<?php echo $wp_list_table->table_scope; ?>-filter" action="#" method="POST">
    <?php if(!WPI_UD_F::is_older_wp_version('3.4')) : ?>
    <div id="poststuff" class="wpi-wp-v34">
      <div id="post-body" class="metabox-holder <?php echo 2 == $screen_layout_columns ? 'columns-2' : 'columns-1'; ?>">
        <div id="post-body-content">
          <?php $wp_list_table->display(); ?>
        </div>
        <div id="postbox-container-1" class="postbox-container">
          <div id="side-sortables" class="meta-box-sortables ui-sortable">
            <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
          </div>
        </div>
      </div>
    </div><!-- /poststuff -->
    <?php else : ?>
    <div id="poststuff" class="<?php echo $current_screen->id; ?>_table metabox-holder <?php echo 2 == $screen_layout_columns ? 'has-right-sidebar' : ''; ?>">
    <div class="wp_wpi_sidebar inner-sidebar">
      <div class="meta-box-sortables ui-sortable">
        <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
      </div>
    </div>
    <div id="post-body">
      <div id="post-body-content">
        <?php $wp_list_table->display(); ?>
      </div><!-- /.post-body-content -->
    </div><!-- /.post-body -->
    <br class="clear" />
    </div><!-- /#poststuff -->
    <?php endif; ?>
  </form>
</div> <?php /* .wp_wpi_overview_wrapper */ ?>
