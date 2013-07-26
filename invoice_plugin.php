<?php
/*
Plugin Name:WP Web Invoice
Plugin URI: http://twincitiestech.com/services/wp-invoice/
Description: Send itemized web-invoices directly to your clients, and have they pay them pay using PayPal or their credit card from your blog. Visit <a href="admin.php?page=invoice_settings">WP-Invoice Settings Page</a> to setup.
Author: TwinCitiesTech.com
Version: 1.7
Author URI: http://twincitiestech.com/


Copyright 2008   TwinCitiesTech.com Inc.   (email : andy.potanin@twincitiestech.com)
*/

/*
	Created by TwinCitiesTech.com
	(website: twincitiestech.com       email : support@twincitiestech.com)

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


	define("WP_INVOICE_VERSION_NUM", "1.7");
	define("WP_INVOICE_TABLE_MAIN", $wpdb->prefix . "invoice");
	define("WP_INVOICE_TABLE_META", $wpdb->prefix . "invoice_meta");
	define("WP_INVOICE_TABLE_LOG", $wpdb->prefix . "invoice_log");
	define("WP_INVOICE_PLUGIN_PATH", str_replace("invoice_plugin.php", "", __FILE__));

	load_plugin_textdomain('wp-invoice');
	$wp_invoice_localversion = WP_INVOICE_VERSION_NUM;
	$wp_invoice_plugindir   = basename(dirname(__FILE__));
	$wp_invoice_plugindir_root = get_option('siteurl') . '/wp-content/plugins/'.$wp_invoice_plugindir;

	require_once('inc_gatewayapi.php');
	require_once("invoice_plugin_pages.php");
	require_once("invoice_plugin_functions.php");
	require_once("invoice_plugin_frontend.php");

	
	register_activation_hook(__FILE__, "wp_invoice_activation");
	register_deactivation_hook(__FILE__, "wp_invoice_deactivation");
	
	add_action('init', 'wp_invoice_init',0);
	add_action('profile_update','wp_invoice_profile_update');
	add_action('edit_user_profile', 'user_profile_invoice_fields');
	add_action('show_user_profile', 'user_profile_invoice_fields');
	add_action('admin_menu', 'wp_invoice_add_pages');

	add_action('admin_head', 'wp_invoice_head');
	add_action('contextual_help', 'wp_invoice_contextual_help_list');

	add_action('wp_head', 'wp_invoice_frontend_css');

	### PLUGIN VERSION CHECK ON PLUGINS PAGE
	add_action( 'after_plugin_row', 'wp_invoice_check_plugin_version');

	if(get_option('wp_invoice_payment_method') == 'cc') { 
		add_action('wp_head', 'wp_invoice_frontend_cc_js'); 
		add_filter('the_content', 'wp_invoice_frontend_cc');  

	}
	
	if(get_option('wp_invoice_payment_method') == 'paypal') { 
		add_filter('the_content', 'wp_invoice_frontend_paypal');  
	}
	



function wp_invoice_contextual_help_list($content) {
// Will add help and FAQ here eventually
return $content;
}
function wp_invoice_init() {
global $wpdb;
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
		wp_enqueue_script('wp-invoice',get_bloginfo('wpurl'). '/wp-content/plugins/wp-invoice/js/wp-invoice-1.7.js', array('jquery') );
	} else {
		
		// Make sure proper MD5 is being passed (32 chars), and strip of everything but numbers and letters
		if(isset($_GET['invoice_id']) && strlen($_GET['invoice_id']) != 32) unset($_GET['invoice_id']); 
		$_GET['invoice_id'] = preg_replace('/[^A-Za-z0-9-]/', '', $_GET['invoice_id']);
		
		if(isset($_GET['invoice_id'])) {
		
			$md5_invoice_id = $_GET['invoice_id'];

			// Convert MD5 hash into Actual Invoice ID
			$all_invoices = $wpdb->get_col("SELECT invoice_num FROM ".WP_INVOICE_TABLE_MAIN." ");
			foreach ($all_invoices as $value) { if(md5($value) == $md5_invoice_id) {$invoice_id = $value;} }		
		
			
			//Check if invoice exists, SSL enforcement is setp, and we are not currently browing HTTPS,  then reload page into HTTPS 
			if(!function_exists('wp_https_redirect')) {
			if(wp_invoice_does_invoice_exist($invoice_id) && get_option('wp_invoice_force_https') == 'true' && $_SERVER['HTTPS'] != "on") {  header("Location: https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']); exit;}
			}
			
		}
		
		if(isset($_POST['wp_invoice_id_hash'])) {
		
			$md5_invoice_id = $_POST['wp_invoice_id_hash'];

			// Convert MD5 hash into Actual Invoice ID
			$all_invoices = $wpdb->get_col("SELECT invoice_num FROM ".WP_INVOICE_TABLE_MAIN." ");
			foreach ($all_invoices as $value) { if(md5($value) == $md5_invoice_id) {$invoice_id = $value;} }
			
			//Check to see if this is a credit card transaction
			if(wp_invoice_does_invoice_exist($invoice_id)) { wp_invoice_process_cc_transaction($_POST); exit; }
			}				

	}
	if(empty($_GET['invoice_id'])) unset($_GET['invoice_id']);
}

	function wp_invoice_options_page($action=""){
	
		global $wpdb;
		$version = get_option('wp_invoice_version');

		
		if (!user_can_access_admin_page()) wp_die( __('You do not have sufficient permissions to access this page.') );



				
	switch($_GET['page']) 
	{
		case "new_invoice":

			switch($_REQUEST['tctiaction']) {

				case "save_and_preview":
				wp_invoice_options_saveandpreview();
				break;

	
				case "clear_log":
				wp_invoice_options_manageInvoice($_REQUEST['invoice_id'],wp_invoice_clear_invoice_status($_REQUEST['invoice_id']));
				break;
				
				
				case "complete_removal":
				wp_invoice_complete_removal();
				wp_invoice_default();
				break;
				
				case "editInvoice":
				if(isset($_REQUEST['invoice_id'])) { wp_invoice_options_manageInvoice($_REQUEST['invoice_id']); }
				else {echo "error!!";} 
				
				break;
				
				case "first_setup":
				
				if(isset($_POST['wp_invoice_web_invoice_page'])) update_option('wp_invoice_web_invoice_page', $_POST['wp_invoice_web_invoice_page']);
				if(isset($_POST['wp_invoice_paypal_address'])) update_option('wp_invoice_paypal_address', $_POST['wp_invoice_paypal_address']);
				if(isset($_POST['wp_invoice_payment_method'])) update_option('wp_invoice_payment_method', $_POST['wp_invoice_payment_method']);
				if(isset($_POST['wp_invoice_gateway_username'])) update_option('wp_invoice_gateway_username', $_POST['wp_invoice_gateway_username']);
				if(isset($_POST['wp_invoice_gateway_tran_key'])) update_option('wp_invoice_gateway_tran_key', $_POST['wp_invoice_gateway_tran_key']);
				if(isset($_POST['wp_invoice_gateway_merchant_email'])) update_option('wp_invoice_gateway_merchant_email', $_POST['wp_invoice_gateway_merchant_email']);				
				wp_invoice_options_manageInvoice();
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
			// Archive Invoices
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'Archive Invoice')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_archive($inArr);
			}
			
			// Mark as Paid
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'Mark As Paid')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_mark_as_paid($inArr);
			}

			// Un Archive Invoices
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'Un-Archive Invoice')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_unarchive($inArr);
			}
							
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'Mark As Sent')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_mark_as_sent($inArr);
			}
					
			// Create Template
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'make_template')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_make_template($inArr);
			}
							
			// Delete Template
			if(is_array($_REQUEST['multiple_invoices']) && $_REQUEST['action'] == 'unmake_template')	
			{
				$inArr = array();
				if (isset($_REQUEST['multiple_invoices'])){
				$inArr = $_POST["multiple_invoices"];
				}
				
				$message .= wp_invoice_unmake_template($inArr);
			}
		
			elseif(isset($_REQUEST['invoice_id']) && $_REQUEST['save'])	
			{
				// Already saved
				$wp_invoice_custom_invoice_id = wp_invoice_meta($_REQUEST['invoice_id'], 'wp_invoice_custom_invoice_id');
				
				if($wp_invoice_custom_invoice_id) {$message =  "Invoice <b>$wp_invoice_custom_invoice_id</b> saved.";}
				else { 	$message =  "Invoice <b>#" . $_REQUEST['invoice_id'] . "</b> saved.";	}
				$message .= " <a href=".wp_invoice_build_invoice_link($_REQUEST['invoice_id']) .">View Web Invoice</a>";
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
			
			$wp_invoice_web_invoice_page = get_option("wp_invoice_web_invoice_page");
			$wp_invoice_payment_method = get_option("wp_invoice_payment_method");
			if(empty($wp_invoice_web_invoice_page) || empty($wp_invoice_payment_method)) { wp_invoice_show_welcome_message(); break; }
			
			wp_invoice_default();
			
		break;
	}
}
	







function wp_invoice_head()
{?>
<link rel='stylesheet' href='<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-invoice/css/wp_admin-1.7.css' type='text/css' media='all' />

<script>
</script>


<?php
}


 
function wp_invoice_add_pages() {

    add_menu_page('Web Invoice System', 'Web Invoice', 8, __FILE__, 'wp_invoice_options_page');
	add_submenu_page( __FILE__, "Manage Invoice", "New Invoice", 8, 'new_invoice', 'wp_invoice_options_page');
	add_submenu_page( __FILE__, "Settings", "Settings", 8, 'invoice_settings', 'wp_invoice_options_page');
}



?>