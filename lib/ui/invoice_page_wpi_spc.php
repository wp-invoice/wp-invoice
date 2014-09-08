<?php

/*
Page Hook / Hook Suffix: invoice_page_wpi_spc
Pre-header Action Hook: load-invoice_page_wpi_spc 
WPI Page Var: $wpi_settings['pages']['spc']

*/
 
include 'class-wpi-object-list-table.php';

$post_type = 'wpi_object';

$post_type_object = get_post_type_object( $post_type );

if ( !current_user_can($post_type_object->cap->edit_posts) )
  wp_die(__('Cheatin&#8217; uh?'));

$wp_list_table = new WPI_Object_List_Table();
$pagenum = $wp_list_table->get_pagenum();

$parent_file = "admin.php?page=wpi_main";
$submenu_file = "admin.php?page=wpi_main";
$post_new_file = "admin.php?page=wpi_page_manage_invoice";

$doaction = $wp_list_table->current_action();

$wp_list_table->prepare_items();

$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
if ( $pagenum > $total_pages && $total_pages > 0 ) {
  wp_redirect( add_query_arg( 'paged', $total_pages ) );
  exit;
}

$title = $post_type_object->labels->name;
  
?>
 <div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php _e('Sales Log', WPI); ?></h2>
 
    <?php WPI_Functions::print_messages(); ?>

    <?php $wp_list_table->views(); ?>

    <form id="posts-filter" action="" method="get">
      <?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
      <input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
      <input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />
      <?php if ( ! empty( $_REQUEST['show_sticky'] ) ) { ?>
        <input type="hidden" name="show_sticky" value="1" />
      <?php } ?>
      <?php $wp_list_table->display(); ?>
    </form>
  
  <div id="ajax-response"></div>
  <br class="clear" />
</div>