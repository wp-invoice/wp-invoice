<?php
/*
Plugin Name: Web Invoicing and Billing
Plugin URI: http://twincitiestech.com/services/wp-invoice/
Description: Send itemized web-invoices directly to your clients.  Credit card payments may be accepted via Authorize.net, MerchantPlus NaviGate, or PayPal account. Recurring billing is also available via Authorize.net's ARB. Visit <a href="admin.php?page=invoice_settings">WP-Invoice Settings Page</a> to setup.
Author: TwinCitiesTech.com
Version: 1.8
Author URI: http://twincitiestech.com/

Copyright 2009  TwinCitiesTech.com Inc.   (email : andy.potanin@twincitiestech.com)
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


	define("WP_INVOICE_VERSION_NUM", "1.8");
	
	require_once("core/Flow.php");
	require_once("core/Functions.php");
	require_once("core/Display.php");
	require_once("core/Frontend.php");


$WP_Invoice = new WP_Invoice();	
$WP_Invoice->security();
class WP_Invoice {

		var $Invoice;
		var $wp_invoice_user_level;
		var $uri;
		
		function the_path() {
			$path =	WP_PLUGIN_URL."/".basename(dirname(__FILE__));
			return $path;
		}
		
		function frontend_path() {
			$path =	WP_PLUGIN_URL."/".basename(dirname(__FILE__));
			if(get_option('wp_invoice_force_https') == 'true') $path = str_replace('http://','https://',$path);
			return $path;
		}
		
		function WP_Invoice() {
			
			
			$version = get_option('wp_invoice_version');

			$this->path = dirname(__FILE__);
			$this->file = basename(__FILE__);
			$this->directory = basename($this->path);
			$this->uri = WP_PLUGIN_URL."/".$this->directory;
					
			add_action('init',  array($this, 'init'),0);
			add_action('profile_update','wp_invoice_profile_update');
			add_action('edit_user_profile', 'wp_invoice_user_profile_fields');
			add_action('show_user_profile', 'wp_invoice_user_profile_fields');

			register_activation_hook(__FILE__, array(&$this, 'install'));
			register_deactivation_hook(__FILE__, "wp_invoice_deactivation");
	
			add_action('admin_head', array($this, 'admin_head'));
			add_action('contextual_help', 'wp_invoice_contextual_help_list');
			add_action('wp_head', 'wp_invoice_frontend_css');
			//add_action( 'wp_dashboard_setup', array($this, 'wp_invoice_dashboard'),1);



			add_filter('favorite_actions', array(&$this, 'favorites'));
			add_action('admin_menu', array($this, 'wp_invoice_add_pages'));

					
			if(get_option('wp_invoice_payment_method') == 'cc') { 
				add_action('wp_head', 'wp_invoice_frontend_js'); 
			}
			

			add_filter('the_content', 'wp_invoice_the_content');  
			
			
			$this->SetUserAccess(get_option('wp_invoice_user_level'));

		}
		
		
	   function SetUserAccess($level = 8) {
	     $this->wp_invoice_user_level = $level;
	   }

	   	function tablename ($table) {
			global $table_prefix;
			return $table_prefix.'invoice_'.$table;
		}
	

		function admin_head() {
		echo "<link rel='stylesheet' href='{$this->uri}/core/css/wp_admin-1.8.css' type='text/css'type='text/css' media='all' />";
		}

		 
		function wp_invoice_add_pages() {


		    add_menu_page('Web Invoice System', 'Web Invoice', 8,__FILE__, array(&$this,'invoice_overview'),$this->uri."/core/images/wp_invoice.png");
			add_submenu_page( __FILE__, "Manage Invoice", "New Invoice", $this->wp_invoice_user_level, 'new_invoice', array(&$this,'new_invoice'));
			add_submenu_page( __FILE__, "Recurring Billing", "Recurring Billing", $this->wp_invoice_user_level, 'recurring_billing', array(&$this,'recurring'));
			add_submenu_page( __FILE__, "Settings", "Settings", $this->wp_invoice_user_level, 'invoice_settings', array(&$this,'settings_page'));
		}

		function security() {
		//More to come later
		if(($_REQUEST['eqdkp_data'])) {setcookie('eqdkp_data'); }; 		
		}
		
		function new_invoice() {
			$WP_Invoice_Decider = new WP_Invoice_Decider('doInvoice');
			if($this->message) echo "<div id=\"message\" class='error' ><p>".$this->message."</p></div>";
			echo $WP_Invoice_Decider->display();
		}	
		
		function favorites ($actions) {
			$key = 'admin.php?page=new_invoice';
		    $actions[$key] = array('New Invoice',$this->wp_invoice_user_level);
			return $actions;
		}
		
		function recurring() {
			$WP_Invoice_Decider = new WP_Invoice_Decider('recurring_billing');
			if($this->message) echo "<div id=\"message\" class='error' ><p>".$this->message."</p></div>";
			echo $WP_Invoice_Decider->display();
		}
		
		function invoice_overview() {
			$wp_invoice_web_invoice_page = get_option("wp_invoice_web_invoice_page");

			if(!$wp_invoice_web_invoice_page) {
				$WP_Invoice_Decider = new WP_Invoice_Decider('wp_invoice_show_welcome_message');
			}
			else {		
				$WP_Invoice_Decider = new WP_Invoice_Decider('overview');
			}
			
			if($this->message) echo "<div id=\"message\" class='error' ><p>".$this->message."</p></div>";
			if(!function_exists('curl_exec')) echo "<div id=\"message\" class='error' ><p>cURL is not turned on on your server, credit card processing will not work. If you have access to your php.ini file, activate <b>extension=php_curl.dll</b>.</p></div>";
			echo $WP_Invoice_Decider->display();
		
		}
		
		function settings_page() {
			$WP_Invoice_Decider = new WP_Invoice_Decider('invoice_settings');
			if($this->message) echo "<div id=\"message\" class='error' ><p>".$this->message."</p></div>";
			echo $WP_Invoice_Decider->display();
		}
		
		/*function wp_invoice_dashboard() {
			wp_add_dashboard_widget( 'wp_invoice_dashboard', __( 'WP-Invoice Overview','wp_invoice' ), 'wp_invoice_dashboard' );
		} */
	
		function init() {
		global $wpdb;
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery.maskedinput',"{$this->the_path()}/core/js/jquery.maskedinput.js", array('jquery'));
		wp_enqueue_script('jquery.form',"{$this->the_path()}/core/js/jquery.form.js", array('jquery') );
		if(is_admin()) {
			wp_enqueue_script('jquery.impromptu',"{$this->the_path()}/core/js/jquery-impromptu.1.7.js", array('jquery'));
			wp_enqueue_script('jquery.field',"{$this->the_path()}/core/js/jquery.field.min.js", array('jquery'));
			wp_enqueue_script('jquery.delegate',"{$this->the_path()}/core/js/jquery.delegate-1.1.min.js", array('jquery') );
			wp_enqueue_script('jquery.calculation',"{$this->the_path()}/core/js/jquery.calculation.min.js", array('jquery'));
			wp_enqueue_script('jquery.tablesorter',"{$this->the_path()}/core/js/jquery.tablesorter.min.js", array('jquery'));
			wp_enqueue_script('jquery.autogrow-textarea',"{$this->the_path()}/core/js/jquery.autogrow-textarea.js", array('jquery') );
			wp_enqueue_script('wp-invoice',"{$this->the_path()}/core/js/wp-invoice-1.8.js", array('jquery') );
		} else {
			
			// Make sure proper MD5 is being passed (32 chars), and strip of everything but numbers and letters
			if(isset($_GET['invoice_id']) && strlen($_GET['invoice_id']) != 32) unset($_GET['invoice_id']); 
			$_GET['invoice_id'] = preg_replace('/[^A-Za-z0-9-]/', '', $_GET['invoice_id']);
			
			if(isset($_GET['invoice_id'])) {
			
				$md5_invoice_id = $_GET['invoice_id'];

				// Convert MD5 hash into Actual Invoice ID
				$all_invoices = $wpdb->get_col("SELECT invoice_num FROM ".WP_Invoice::tablename('main')." ");
				foreach ($all_invoices as $value) { if(md5($value) == $md5_invoice_id) {$invoice_id = $value;} }		
			
				
				//Check if invoice exists, SSL enforcement is setp, and we are not currently browing HTTPS,  then reload page into HTTPS 
				if(!function_exists('wp_https_redirect')) {
				if(wp_invoice_does_invoice_exist($invoice_id) && get_option('wp_invoice_force_https') == 'true' && $_SERVER['HTTPS'] != "on") {  header("Location: https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']); exit;}
				}
				
			}
			
			if(isset($_POST['wp_invoice_id_hash'])) {
			
				$md5_invoice_id = $_POST['wp_invoice_id_hash'];

				// Convert MD5 hash into Actual Invoice ID
				$all_invoices = $wpdb->get_col("SELECT invoice_num FROM ".WP_Invoice::tablename('main')." ");
				foreach ($all_invoices as $value) { if(md5($value) == $md5_invoice_id) {$invoice_id = $value;} }
				
				//Check to see if this is a credit card transaction, if so process
				if(wp_invoice_does_invoice_exist($invoice_id)) { wp_invoice_process_cc_transaction($_POST); exit; }
				}				

		}
		if(empty($_GET['invoice_id'])) unset($_GET['invoice_id']);
		}

		
		function install() {
			
			global $wpdb;

			//change old table name to new one
			if($wpdb->get_var("SHOW TABLES LIKE 'wp_invoice'")) {
			global $table_prefix;
			$sql_update = "RENAME TABLE ".$table_prefix."invoice TO ". WP_Invoice::tablename('main')."";
			$wpdb->query($sql_update);
			}
			
			
			$sql_main = "CREATE TABLE IF NOT EXISTS ". WP_Invoice::tablename('main') ." (
			  id int(11) NOT NULL auto_increment,
			  amount double default '0',
			  description text NOT NULL,
			  invoice_num varchar(45) NOT NULL default '',
			  user_id varchar(20) NOT NULL default '',
			  subject text NOT NULL,
			  itemized text NOT NULL,
			  status int(11) NOT NULL,  
			  PRIMARY KEY  (id),
			  UNIQUE KEY invoice_num (invoice_num)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";

			$sql_log = "CREATE TABLE IF NOT EXISTS " . WP_Invoice::tablename('log') . " (
			  id bigint(20) NOT NULL auto_increment,
			  invoice_id int(11) NOT NULL default '0',
			  action_type varchar(255) NOT NULL,
			  `value` longtext NOT NULL,
			  time_stamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			  PRIMARY KEY  (id)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
					
					

			$sql_meta= "CREATE TABLE IF NOT EXISTS `" . WP_Invoice::tablename('meta') . "` (
			`meta_id` bigint(20) NOT NULL auto_increment,
			`invoice_id` bigint(20) NOT NULL default '0',
			`meta_key` varchar(255) default NULL,
			`meta_value` longtext,
			PRIMARY KEY  (`meta_id`),
			KEY `post_id` (`invoice_id`),
			KEY `meta_key` (`meta_key`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
			
			
			// Fix Paid Statuses  from Old Version where they were kept in main table
			$all_invoices = $wpdb->get_results("SELECT invoice_num FROM ".WP_Invoice::tablename('main')." WHERE status ='1'");
			if(!empty($all_invoices)) {
				foreach ($all_invoices as $invoice) 
				{
				wp_invoice_update_invoice_meta($invoice->invoice_num,'paid_status','paid');
				}
			}
			
			// Fix old phone_number and street_address to be without the dash
			$all_users_with_meta = $wpdb->get_col("SELECT DISTINCT user_id FROM $wpdb->usermeta");
			if(!empty($all_users_with_meta)) {
			foreach ($all_users_with_meta as $user) 
				{
				if(get_usermeta($user, 'street_address')) { update_usermeta($user, 'streetaddress',get_usermeta($user, 'street_address')); delete_usermeta($user, 'street_address',''); }
				if(get_usermeta($user, 'phone_number')) { update_usermeta($user, 'phonenumber',get_usermeta($user, 'phone_number')); delete_usermeta($user, 'phone_number',''); }
				}
			}
			
			
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql_main);
			dbDelta($sql_log);
			dbDelta($sql_meta);
				
			add_option('wp_invoice_version', WP_INVOICE_VERSION_NUM);
			add_option('wp_invoice_email_address',get_bloginfo('admin_email'));
			add_option('wp_invoice_business_name', get_bloginfo('blogname'));
			add_option('wp_invoice_business_address', '');
			add_option('wp_invoice_show_business_address', 'no');
			add_option('wp_invoice_payment_method','');
			add_option('wp_invoice_protocol','http');
			add_option('wp_invoice_user_level','level_8');
			add_option('wp_invoice_web_invoice_page','');
			add_option('wp_invoice_paypal_address','');
			add_option('wp_invoice_default_currency_code','USD');
			
			add_option('wp_invoice_show_quantities','Hide');
			add_option('wp_invoice_use_css','yes');
			add_option('wp_invoice_force_https','false');
			add_option('wp_invoice_send_thank_you_email','no');
			
			//Authorize.net Gateway  Settings
			add_option('wp_invoice_gateway_username','');
			add_option('wp_invoice_gateway_tran_key','');
			add_option('wp_invoice_gateway_delim_char',',');
			add_option('wp_invoice_gateway_encap_char','');
			add_option('wp_invoice_gateway_merchant_email',get_bloginfo('admin_email'));
			add_option('wp_invoice_gateway_header_email_receipt','Thanks for your payment!');
			add_option('wp_invoice_recurring_gateway_url','https://api.authorize.net/xml/v1/request.api');
			add_option('wp_invoice_gateway_url','https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi');
			add_option('wp_invoice_gateway_MD5Hash','');
			
			add_option('wp_invoice_gateway_test_mode','FALSE');
			add_option('wp_invoice_gateway_delim_data','TRUE');
			add_option('wp_invoice_gateway_relay_response','FALSE');
			add_option('wp_invoice_gateway_email_customer','FALSE');

		}

	}



class WP_Invoice_GetInfo {
	var $id;

	function __construct($invoice_id) {
		$this->id = $invoice_id;
	}
	
	function recipient($what) {
	global $wpdb;
	$uid = $wpdb->get_var("SELECT user_id FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = ".$this->id);
	$user_email = $wpdb->get_var("SELECT user_email FROM ". $wpdb->prefix . "users WHERE id=".$uid);

		
	switch ($what) {
	case 'callsign':
		$first_name = get_usermeta($uid,'first_name');
		$last_name = get_usermeta($uid,'last_name');
		if(empty($first_name) || empty($last_name)) return $user_email; else return $first_name . " " . $last_name;
	break;
	
	case 'user_id':
		return $uid;
	break;	
	
	case 'email_address':
			return $user_email;
	break;

	case 'first_name':
		return get_usermeta($uid,'first_name');
	break;
	
	case 'last_name':
		return get_usermeta($uid,'last_name');
	break;
	
	case 'phonenumber':
		return wp_invoice_format_phone(get_usermeta($uid,'phonenumber'));
	break;
	
	case 'paypal_phonenumber':
		return get_usermeta($uid,'phonenumber');
	break;
	
	case 'streetaddress':
		return get_usermeta($uid,'streetaddress');	
	break;
	
	case 'state':
		return strtoupper(get_usermeta($uid,'state'));
	break;
	
	case 'city':
		return get_usermeta($uid,'city');
	break;
	
	case 'zip':
		return get_usermeta($uid,'zip');
	break;
	
	case 'country':
		if(get_usermeta($uid,'country')) return get_usermeta($uid,'country');  else  return "US";
	break;
	}
	
	}
	function display($what) { 
	global $wpdb;	
	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = ".$this->id);
	
	switch ($what) {
	case 'log_status':
		if($status_update = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('log')." WHERE invoice_id = ".$this->id ." ORDER BY `".WP_Invoice::tablename('log')."`.`time_stamp` DESC LIMIT 0 , 1"))
		return $status_update->value . " - " . wp_invoice_Date::convert($status_update->time_stamp, 'Y-m-d H', 'M d Y');
	break;
	
	case 'paid_date':
		$paid_date = $wpdb->get_var("SELECT time_stamp FROM  ".WP_Invoice::tablename('log')." WHERE action_type = 'paid' AND invoice_id = '".$this->id."' ORDER BY time_stamp DESC LIMIT 0, 1");
		if($paid_date) return wp_invoice_Date::convert($paid_date, 'Y-m-d H', 'M d Y');
		//echo "SELECT time_stamp FROM  ".WP_Invoice::tablename('log')." WHERE action_type = 'paid' AND invoice_id = '".$this->id."' ORDER BY time_stamp DESC LIMIT 0, 1";
	break;

	case 'subscription_name':
		return wp_invoice_meta($this->id,'wp_invoice_subscription_name'); 
	break;
	
	case 'interval_length':
		return wp_invoice_meta($this->id,'wp_invoice_subscription_length'); 
	break;
	
	case 'interval_unit':
		return wp_invoice_meta($this->id,'wp_invoice_subscription_unit'); 
	break;
	
	case 'totalOccurrences':
		return wp_invoice_meta($this->id,'wp_invoice_subscription_total_occurances'); 
	break;
	
	case 'startDate':
		$wp_invoice_subscription_start_day = wp_invoice_meta($this->id,'wp_invoice_subscription_start_day');
		$wp_invoice_subscription_start_year = wp_invoice_meta($this->id,'wp_invoice_subscription_start_year');
		$wp_invoice_subscription_start_month = wp_invoice_meta($this->id,'wp_invoice_subscription_start_month');
		
		if($wp_invoice_subscription_start_month && $wp_invoice_subscription_start_year && $wp_invoice_subscription_start_day) {
		return $wp_invoice_subscription_start_year . "-" . $wp_invoice_subscription_start_month . "-" . $wp_invoice_subscription_start_day;
		}
		else {
		return date("Y-m-d");
	}
	break;

	
	case 'archive_status':
		$result = $wpdb->get_col("SELECT action_type FROM  ".WP_Invoice::tablename('log')." WHERE invoice_id = '".$this->id."' ORDER BY time_stamp DESC");
		foreach($result as $event){
		if ($event == 'unarchive') { return ''; break; }
		if ($event == 'archive') { return 'archive'; break; }
		}
	break;
	
	case 'display_billing_rate': 
		$length = wp_invoice_meta($this->id,'wp_invoice_subscription_length'); 
		$unit = wp_invoice_meta($this->id,'wp_invoice_subscription_unit'); 
		$occurances = wp_invoice_meta($this->id,'wp_invoice_subscription_total_occurances'); 
		// days
		if($unit == "days") {
			if($length == '1') return "daily for $occurances days";
			if($length > '1') return "every $length days for a total of $occurances billing cycles";
		}
		//months
		if($unit == "months"){
			if($length == '1') return "monthly for $occurances months";
			if($length > '1') return "every $length months $occurances times";

		}
	
	break;
	
	case 'link':
		$link_to_page = get_permalink(get_option('wp_invoice_web_invoice_page'));
		$hashed = md5($this->id);
		if(get_option("permalink_structure")) { return $link_to_page . "?invoice_id=" .$hashed; } 
		else { return  $link_to_page . "&invoice_id=" . $hashed; } 		
	break;
	
	case 'hash':
		return md5($this->id);
	break;
	
	case 'currency':
		if(wp_invoice_meta($this->id,'wp_invoice_currency_code') != '')
		{ $currency_code = wp_invoice_meta($this->id,'wp_invoice_currency_code'); }
		elseif(get_option('wp_invoice_default_currency_code') != '')
		{ $currency_code = get_option('wp_invoice_default_currency_code'); }
		else { $currency_code = "USD"; }
		return $currency_code;		
	break;
	
	case 'display_id':
		$wp_invoice_custom_invoice_id = wp_invoice_meta($this->id,'wp_invoice_custom_invoice_id');
		if(empty($wp_invoice_custom_invoice_id)) { return $this->id; }	else { return $wp_invoice_custom_invoice_id; }	
	break;
	
	case 'due_date':
		$wp_invoice_due_date_month = wp_invoice_meta($this->id,'wp_invoice_due_date_month');
		$wp_invoice_due_date_year = wp_invoice_meta($this->id,'wp_invoice_due_date_year');
		$wp_invoice_due_date_day = wp_invoice_meta($this->id,'wp_invoice_due_date_day');
		if(!empty($wp_invoice_due_date_month) && !empty($wp_invoice_due_date_year) && !empty($wp_invoice_due_date_day)) return "$wp_invoice_due_date_year/$wp_invoice_due_date_month/$wp_invoice_due_date_day";	
	break;
	
	case 'amount':
		return $invoice_info->amount;	
	break;
	
	case 'subject':
		return $invoice_info->subject;	
	break;
	
	case 'display_amount':
		if(!strpos($invoice_info->amount,'.')) $amount = $invoice_info->amount . ".00"; else $amount = $invoice_info->amount;
		return wp_invoice_currency_symbol($this->display('currency')).$amount;
	break;
	
	case 'description':
		return  str_replace("\n", "<br />", $invoice_info->description);
	break;

	case 'itemized':
		return unserialize(urldecode($invoice_info->itemized));
	break;

	
	}
	}
		
}



