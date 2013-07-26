<?php
/*
Plugin Name:WP Web Invoice
Plugin URI: http://twincitiestech.com/services/wp-invoice/
Description: Send itemized web-invoices directly to your clients, and have they pay them pay using PayPal from your blog.
Author: TwinCitiesTech.com
Version: 1.1
Author URI: http://twincitiestech.com/


Copyright 2008   TwinCitiesTech.com Inc.   (email : andy.potanin@twincitiestech.com)
*/

/*
    WP-Invoice -  Online Invoicing for WordPress
    Copyright (C) <2008>  TwinCitiesTech.com Inc.


    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 3 of the License, with the
    exception of the JQuery JavaScript framework which is released
    under it's own license.  You may view the details of that license in
    the prototype.js file.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


	global $wpdb;
	define("WP_INVOICE_VERSION_NUM", "0.5");
	define("WP_INVOICE_TABLE_MAIN", $wpdb->prefix . "invoice");
	define("WP_INVOICE_TABLE_LOG", $wpdb->prefix . "invoice_log");
	define("WP_INVOICE_PLUGIN_PATH", str_replace("invoice_plugin.php", "", __FILE__));

	require_once("invoice_plugin_pages.php");
	require_once("invoice_plugin_functions.php");
	require_once("invoice_plugin_frontend.php");
	
	register_activation_hook(__FILE__, "wp_invoice_activation");
	register_deactivation_hook(__FILE__, "wp_invoice_deactivation");
	
	add_action('profile_update','wp_invoice_profile_update');
	add_action('edit_user_profile', 'user_profile_invoice_fields');
	add_action('show_user_profile', 'user_profile_invoice_fields');
	add_action('admin_menu', 'wp_invoice_add_pages');

	add_action('init', 'wp_invoice_init',0);

	add_action('wp_head', 'wp_invoice_frontend_css');
	add_action('admin_head', 'wp_invoice_head');
	
	add_filter('the_content', 'wp_invoice_frontend');  
	


function wp_invoice_init() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery.maskedinput',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.maskedinput.js', array('jquery'));
	wp_enqueue_script('jquery.form',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.form.js', array('jquery') );
if(is_admin()) {
	wp_enqueue_script('jquery.impromptu',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery-impromptu.1.7.js', array('jquery'));
	wp_enqueue_script('jquery.field',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.field.min.js', array('jquery'));
	wp_enqueue_script('jquery.delegate',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.delegate-1.1.min.js', array('jquery') );
	wp_enqueue_script('jquery.calculation',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.calculation.min.js', array('jquery'));
	wp_enqueue_script('jquery.tablesorter',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.tablesorter.min.js', array('jquery'));
	wp_enqueue_script('jquery.autogrow-textarea',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/jquery.autogrow-textarea.js', array('jquery') );
	wp_enqueue_script('wp-invoice',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/wp-invoice.js', array('jquery') );
}
}

	function wp_invoice_options_page($action=""){
	
		global $wpdb;
		$version = get_option('wp_invoice_version');

		if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(__('Cheatin&#8217; uh?'));
		if (!user_can_access_admin_page()) wp_die( __('You do not have sufficient permissions to access this page.') );



				
	switch($_GET['page']) 
	{
		case "new_invoice":

			switch($_GET['tctiaction']) {

				case "save_and_preview":
				wp_invoice_options_saveandpreview();
				break;
				
				case "complete_removal":
				wp_invoice_complete_removal();
				wp_invoice_default();
				break;
				
				case "editInvoice":
				if(isset($_REQUEST['invoice_id'])) { wp_invoice_options_manageInvoice($_REQUEST['invoice_id']); }
				else {echo "error!!";} 
				
				break;
				
				default:
				wp_invoice_options_manageInvoice();
				break;
			}
			
		break;
		
		case "invoice_settings":
		wp_invoice_options_settings();
		break;
		

		
		default: // If we are passing a invoice_id, what do we do with it?
		
			if(isset($_REQUEST['invoice_id']) && $_REQUEST['action'] == 'Delete')	
			{
			$message .= wp_invoice_delete($_REQUEST['invoice_id']);
			}
			
			// Delete Multiple Invoices
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'Delete')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_delete($inArr);
			}
			
				// Send Invoices
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'Send Invoice')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_send_email($inArr);
			}
		
			elseif(isset($_REQUEST['invoice_id']) && $_REQUEST['save'])	
			{
				// Already saved
				$message =  "Invoice #" . $_REQUEST['invoice_id'] . " saved.";
			}

			elseif(isset($_REQUEST['invoice_id']) && $_REQUEST['send_now'])	
			{
				$message = wp_invoice_send_email($_REQUEST['invoice_id']);
				
			}
			elseif(isset($_REQUEST['invoice_id']) && $_REQUEST['modify'])	
			{
				wp_invoice_options_manageInvoice($_REQUEST['invoice_id']);				
				break; // We do not want to show the invoice overview
			}
			if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";
			
			wp_invoice_default();
			
		break;
	}
}
	







function wp_invoice_head()
{?>
<link rel='stylesheet' href='<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-invoice/css/wp_admin.css' type='text/css' media='all' />

<script>
</script>


<?php
}


 
function wp_invoice_add_pages() {

    add_menu_page('Web Invoice System', 'Web Invoice', 8, __FILE__, 'wp_invoice_options_page');
	add_submenu_page( __FILE__, "New Invoice", "New Invoice", 8, 'new_invoice', 'wp_invoice_options_page');
	add_submenu_page( __FILE__, "Settings", "Settings", 8, 'invoice_settings', 'wp_invoice_options_page');
}



?>