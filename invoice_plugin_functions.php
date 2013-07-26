<?php 
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

setlocale(LC_MONETARY, 'en_US'); 

function wp_invoice_number_of_invoices()
{
	global $wpdb;
	$query = "SELECT COUNT(*) FROM ".WP_INVOICE_TABLE_MAIN."";
	$count = $wpdb->get_var($query);
	return $count;
}

function wp_invoice_draw_user_selection_form($user_id) {
	global $wpdb;
?>
	<form action="admin.php?page=new_invoice" method='POST'>
		<table class="form-table" id="get_user_info">
			<tr class="invoice_main">
				<th><?php if(isset($user_id)) { ?>Start New Invoice: <?php } else { ?>Get User Information<?php } ?></th>
				<td> 
					<select name='user_id' class='user_selection' >
					<option ></option>
					<?php
					$get_all_users = $wpdb->get_results("SELECT * FROM wp_users LEFT JOIN wp_usermeta on wp_users.id=wp_usermeta.user_id and wp_usermeta.meta_key='last_name' ORDER BY wp_usermeta.meta_value");
					foreach ($get_all_users as $user)
					{ 
					$profileuser = get_user_to_edit($user->ID);
					echo "<option ";
					if(isset($user_id) && $user_id == $user->ID) echo " SELECTED ";
					if(!empty($profileuser->last_name) && !empty($profileuser->first_name)) { echo " value=\"".$user->ID."\">". $profileuser->last_name. " " . $profileuser->first_name . " (".$profileuser->user_email.")</option>\n";  }
					else 
					{
					echo " value=\"".$user->ID."\">". $profileuser->user_login. " (".$profileuser->user_email.")</option>\n"; 
					}
					}
					?>
					</select>
					<input type='submit' class='button' value='Go'>
					<br />
					<?php if(!isset($user_id)) { ?>User must have an account to receive invoices. <a href="users.php">Create a new user account.</a><?php } ?>
				</td>
			</tr>
		</table>
	</form>
	

	<?php
	}

function wp_invoice_update_log($invoice_id,$action_type,$value) 
{
	global $wpdb;
	if(isset($invoice_id))
	{
	$wpdb->query("INSERT INTO `".WP_INVOICE_TABLE_LOG."` 
	( `id` , `invoice_id` , `action_type` , `value` )
	VALUES ( NULL , '$invoice_id', '$action_type', '$value' );");
	}
}


function wp_invoice_delete($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;
	$wpdb->query("DELETE FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '$single_invoice_id'");
	// Make log entry
	
	wp_invoice_update_log($single_invoice_id, "deleted", "Deleted on ");
		}
	return $counter . " invoice(s) uccessfully deleted.";

}
else
{
	// Delete Single
	$wpdb->query("DELETE FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '$invoice_id'");
	// Make log entry
	wp_invoice_update_log($invoice_id, "deleted", "Deleted on ");
	return "Invoice successfully deleted.";
}
}


function wp_invoice_get_invoice_attrib($invoice_id,$attribute) 
{
	global $wpdb;
	$query = "SELECT $attribute FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num=".$invoice_id."";
	return $wpdb->get_var($query);
}

function wp_invoice_get_invoice_status($invoice_id,$count='1') 
{
	global $wpdb;
	$query = "
	SELECT *
	FROM ".WP_INVOICE_TABLE_LOG."
	WHERE invoice_id = $invoice_id
	ORDER BY `".WP_INVOICE_TABLE_LOG."`.`time_stamp` DESC
	LIMIT 0 , $count";

	$status_update = $wpdb->get_results($query);

	foreach ($status_update as $single_status)
	{
		$message .= "<li>" . $single_status->value . " on " . $single_status->time_stamp . "</li>";
	}

	return $message;
}

function wp_invoice_get_single_invoice_status($invoice_id) 
{
	global $wpdb;
	if($status_update = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_LOG." WHERE invoice_id = $invoice_id ORDER BY `".WP_INVOICE_TABLE_LOG."`.`time_stamp` DESC LIMIT 0 , 1"))
	return $status_update->value . " - " . wp_invoice_Date::convert($status_update->time_stamp, 'Y-m-d H', 'M d Y');
}


function wp_invoice_show_invoice($invoice_id) {
	global $wpdb;

	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = $invoice_id");
	$profileuser = get_user_to_edit($invoice_info->user_id);

	if(!empty($invoice_info->subject)) 
	{ // No Subject For Invoice
	echo "<div class=\"subject\">Subject: <strong>" . $invoice_info->subject. "</strong></div>";
	}
	echo "<div class=\"main_content\">";
	echo str_replace("\n", "<br />", wp_invoice_show_email_invoice($_POST['new_invoice_id']));
	echo "</div>";	
}

function wp_invoice_show_email_invoice($invoice_id) {
	global $wpdb;

	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = $invoice_id");
	$profileuser = get_user_to_edit($invoice_info->user_id);

	if(empty($profileuser->first_name) && empty($profileuser->last_name)) { $client_name = $profileuser->user_nicename; } else {  $client_name = $profileuser->first_name . " " . $profileuser->last_name; }
	
	$message = "Dear ". $client_name . ", \n\n";
 	if(empty($invoice_info->description)) { $message .= get_option("wp_invoice_business_name") . " has sent you a web invoice totaling $". number_format($invoice_info->amount, 2, '.', ',') . ". \n\n"; } 
	else { $message .= get_option("wp_invoice_business_name") . " has sent you a web invoice totaling $".number_format($invoice_info->amount, 2, '.', ',') . ". \n\n$invoice_info->description \n\n"; }

	$message .= "You may pay the invoice online by visiting the following link: \n";
	$message .= wp_invoice_build_invoice_link($invoice_id) . "\n\n";
	$message .= "Best regards,\n";
	$message .= get_option("wp_invoice_business_name") . "(" .  get_option("wp_invoice_email_address")  . ")";
	
	return $message;
}

function wp_invoice_paid($invoice_id) {
	global $wpdb;
	$wpdb->query("UPDATE  ".WP_INVOICE_TABLE_MAIN." SET status = 1 WHERE  invoice_num = '$invoice_id'");
}

function wp_invoice_paid_status($invoice_id) {
	global $wpdb;
	return $wpdb->get_var("SELECT status FROM  ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '$invoice_id'");
}


function wp_invoice_build_invoice_link($invoice_id) {
	// Determine if we are using rewrites, different links must be used
	global $wpdb;
	
	$link_to_page = get_permalink(get_option('wp_invoice_web_invoice_page'));

	if(get_option('wp_invoice_protocol') == 'https') { $link_to_page = str_replace('http', 'https', $link_to_page);  }

	$hashed_invoice_id = md5($invoice_id);
	if(get_option("permalink_structure")) { $link = $link_to_page . "?invoice_id=" .$hashed_invoice_id; } 
	else { $link =  $link_to_page . "&invoice_id=" . $hashed_invoice_id; } 

	return $link;
}


function wp_invoice_draw_itemized_table($invoice_id) {
	global $wpdb;
	
	
	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
	$itemized = $invoice_info->itemized;
	$amount = $invoice_info->amount;
	if(!strpos($amount,'.')) $amount = $amount . ".00";
	$itemized_array = unserialize(urldecode($itemized)); 
	

	if(is_array($itemized_array)) {
		$response .= "<table id=\"itemized_table\">
		<tr>
		<th>Item</th>";
		if(get_option('wp_invoice_show_quantities') == "Show") { $response .= '<th style=\"width: 70px; text-align: right;\">Quantity</th>'; }
		$response .="<th style=\"width: 70px; text-align: right;\">Cost</th>
		</tr> ";
		$i = 1;
		foreach($itemized_array as $itemized_item){
			if(!empty($itemized_item[name])) {
			if(!strpos($itemized_item[price],'.')) $itemized_item[price] = $itemized_item[price] . ".00";
		
		if($i % 2) { $response .= "<tr>"; } 
		else { $response .= "<tr  class='alt_row'>"; } 
		
		$response .= "<td>" . stripslashes($itemized_item[name]) . " <br /><span class='description_text'>" . stripslashes($itemized_item[description]) . "</span></td>";

		if(get_option('wp_invoice_show_quantities') == "Show") { 
		$response .= "<td style=\"width: 70px; text-align: right;\">" . $itemized_item[quantity] . "</td>
		<td style=\"width: 70px; text-align: right;\">$" . number_format($itemized_item[price], 2, '.', ',') . "</td>"; }
		
		else {
		 $response .= "<td style=\"width: 70px; text-align: right;\">$" .  number_format($itemized_item[quantity] * $itemized_item[price], 2, '.', ',') . "</td>"; 
		 }
		
		
		
		$response .="</tr>";
		$i++;
		}
		
		}
		$response .="<tr>
		<td colspan=\"3\"><br /></td>
		</tr>
		
		<tr>
		<td align=\"right\">Total Due:</td>
		<td  colspan=\"2\" style=\"text-align: right;\" class=\"grand_total\">";

		$response .= "$". number_format($amount, 2, '.', ',');
		$response .= "</td></table>";

		return $response;
	}

}

function wp_invoice_draw_itemized_table_plaintext($invoice_id) {
	global $wpdb;
	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
	$itemized = $invoice_info->itemized;
	$amount = $invoice_info->amount;
	if(!strpos($amount,'.')) $amount = $amount . ".00";
	
	$itemized_array = unserialize(urldecode($itemized)); 

	if(is_array($itemized_array)) {


		foreach($itemized_array as $itemized_item){
			if(!empty($itemized_item[name])) {
			$item_cost = $itemized_item[price] * $itemized_item[quantity];
			if(!strpos($item_cost,'.')) $item_cost = $item_cost . ".00";

		$response .= " $" . $item_cost . " \t - \t " . stripslashes($itemized_item[name]) . "\n";
		

		}
		}

		return $response;
	}

}



function wp_invoice_send_email_reciept($invoice_id) {
	global $wpdb;

	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = $invoice_id");


	$email_address = $wpdb->get_var("SELECT user_email FROM wp_users WHERE id=".$invoice_info->user_id."");
	$first_name = $wpdb->get_var("SELECT meta_value FROM wp_users LEFT JOIN wp_usermeta on wp_users.id=wp_usermeta.user_id WHERE wp_users.id=".$invoice_info->user_id." and meta_key='first_name'");
	$last_name = $wpdb->get_var("SELECT meta_value FROM wp_users LEFT JOIN wp_usermeta on wp_users.id=wp_usermeta.user_id WHERE wp_users.id=".$invoice_info->user_id." and meta_key='last_name'");

	$message = "Dear ". $first_name . " " . $last_name . ", \n\n";


 	if(empty($invoice_info->description)) {
 	$message .= "This is your reciept for the payment of $". $invoice_info->amount . ". \n\n ";
	} 
	else { 
 	$message .= "This is your reciept for the payment of $". $invoice_info->amount . ". \n\n ";
	$message .= wp_invoice_draw_itemized_table_plaintext($invoice_id);
	}

	//$message .= "\n". $invoice_info->description;

	$message .= "\n\n";
	$message .= "Best regards,\n";
	$message .= get_option("wp_invoice_business_name") . "(" .  get_option("wp_invoice_email_address")  . ")";
	
	
	$from = get_option("wp_invoice_email_address");
	$headers = "From: $from";

	if(mail($email_address, "Reciept", $message, $headers)) 
	{ wp_invoice_update_log($invoice_id,'contact','Reciept eMailed'); }
		
	return $message;
}

function wp_invoice_format_phone($phone)
{
	$phone = preg_replace("/[^0-9]/", "", $phone);

	if(strlen($phone) == 7)
		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
	elseif(strlen($phone) == 10)
		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
	else
		return $phone;
}

function wp_invoice_user_datea($user_id) {
	global $wpdb;
	$profileuser = get_user_to_edit(user_id);
	return $profileuser;
}

function wp_invoice_donate_button() {
	$total_collected = wp_invoice_total_collected(); 
	if($total_collected < 500) { $donation = 5; } else  { $donation = $total_collected / 100; }?>

	<div id="paypal_donate" class="clearfix" style="margin-top: 30px; ">
	<form action="https://www.paypal.com/us/cgi-bin/webscr" method="post" style="float: left;">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="amount" value="<?php echo $donation; ?>">
	<input type="hidden" name="business" value="andy.potanin@gmail.com">
	<input type="hidden" name="item_name" value="WP-Invoice Development">
	<input type="hidden" name="hosted_button_id" value="2100005">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	<div style="padding-top:5px;">Help Support Further Development<?php if($total_collected > 500) { echo ", donate 1% of your earnings"; }?>. | <a href="http://twincitiestech.com/services/wp-invoice/">Upgrade to Credit Card Processing.</a></div>
	</div>
	<?php
}

function wp_invoice_total_collected() {
	global $wpdb;
	return $wpdb->get_var("SELECT SUM(amount) FROM " . WP_INVOICE_TABLE_MAIN . " WHERE status=1");
}

function wp_invoice_activation() {

	global $wpdb;

	$sql_WP_INVOICE_TABLE_MAIN = "CREATE TABLE IF NOT EXISTS " . WP_INVOICE_TABLE_MAIN . " (
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

	$sql_WP_INVOICE_TABLE_LOG = "CREATE TABLE IF NOT EXISTS " . WP_INVOICE_TABLE_LOG . " (
	  id bigint(20) NOT NULL auto_increment,
	  invoice_id int(11) NOT NULL default '0',
	  action_type varchar(255) NOT NULL,
	  `value` longtext NOT NULL,
	  time_stamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	  PRIMARY KEY  (id)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
			
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_WP_INVOICE_TABLE_MAIN);
	dbDelta($sql_WP_INVOICE_TABLE_LOG);


	$wp_invoice_billing_meta[1] = 'Street Address';
	$wp_invoice_billing_meta[2] = 'City';
	$wp_invoice_billing_meta[3] = 'State';
	$wp_invoice_billing_meta[4] = 'Zip';
	$wp_invoice_billing_meta[5] = 'Phone Number';

	$wp_invoice_billing_meta = urlencode(serialize($wp_invoice_billing_meta));

		
	add_option('wp_invoice_version', WP_INVOICE_VERSION_NUM);
	add_option('wp_invoice_email_address',get_bloginfo('admin_email'));
	add_option('wp_invoice_business_name', get_bloginfo('blogname'));
	add_option('wp_invoice_payment_link','');
	add_option('wp_invoice_protocol','http');
	add_option('wp_invoice_web_invoice_page','');
	add_option('wp_invoice_paypal_address','');
	add_option('wp_invoice_billing_meta',$wp_invoice_billing_meta);
	add_option('wp_invoice_show_quantities','Hide');
	add_option('wp_invoice_use_css','yes');
}


function wp_invoice_deactivation($confirm=false) 
{
	global $wpdb;
	delete_option('wp_invoice_version');
	delete_option('wp_invoice_payment_link');
	delete_option('wp_invoice_protocol');
	delete_option('wp_invoice_email_address');
	delete_option('wp_invoice_business_name');
	delete_option('wp_invoice_business_phone');
	delete_option('wp_invoice_paypal_address');
	delete_option('wp_invoice_web_invoice_page');
	delete_option('wp_invoice_billing_meta');
	delete_option('wp_invoice_show_quantities');
	delete_option('wp_invoice_use_css');
	//Clean Up Log (We Keep Invoice Data on File just in Case)
	$wpdb->query("DROP TABLE " . WP_INVOICE_TABLE_LOG .";");	
}


function wp_invoice_complete_removal() 
{
	// Delete all tables and options.
	global $wpdb;
	delete_option('wp_invoice_version');
	delete_option('wp_invoice_payment_link');
	delete_option('wp_invoice_protocol');
	delete_option('wp_invoice_email_address');
	delete_option('wp_invoice_business_name');
	delete_option('wp_invoice_business_phone');
	delete_option('wp_invoice_paypal_address');
	delete_option('wp_invoice_web_invoice_page');
	delete_option('wp_invoice_billing_meta');
	delete_option('wp_invoice_show_quantities');
	delete_option('wp_invoice_use_css');
	$wpdb->query("DROP TABLE " . WP_INVOICE_TABLE_LOG .";");
	$wpdb->query("DROP TABLE " . WP_INVOICE_TABLE_MAIN .";");
}

function get_invoice_user_id($invoice_id) {
	global $wpdb;
	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
	return $invoice_info->user_id;
}

function wp_invoice_send_email($invoice_array)
{
	global $wpdb;
	
	if(is_array($invoice_array)) 
	{
		$counter=0;
		foreach ($invoice_array as $invoice_id)
		{
		$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
		
		$profileuser = get_user_to_edit($invoice_info->user_id);
		$subject = $invoice_info->subject;
		$message = wp_invoice_show_email_invoice($invoice_id);

		$from = get_option("wp_invoice_email_address");
		$headers = "From: $from";
		
		if(mail($profileuser->user_email, $subject, $message, $headers)) 
		{
			$counter++; // Success in sending quantified.
			wp_invoice_update_log($invoice_id,'contact','Invoice eMailed'); //make sent entry
		}
		}
		return "Successfully sent $counter Web Invoices(s).";
	}
	else
	{
		$invoice_id = $invoice_array;
		$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_array."'");
		
		$profileuser = get_user_to_edit($invoice_info->user_id);
		$subject = $invoice_info->subject;
		$message = wp_invoice_show_email_invoice($invoice_id);
		$from = get_option("wp_invoice_email_address");
		$headers = "From: $from";
		
		if(mail($profileuser->user_email, $subject, $message, $headers)) 
		{ wp_invoice_update_log($invoice_id,'contact','Invoice eMailed'); }
		return "Web invoice sent successfully.";

	}
}


	
function wp_invoice_array_stripslashes($slash_array = array())
{
	if($slash_array)
	{
		foreach($slash_array as $key=>$value)
		{
			if(is_array($value))
			{
				$slash_array[$key] = wp_invoice_array_stripslashes($value);
			}
			else
			{
				$slash_array[$key] = stripslashes($value);
			}
		}
	}
	return($slash_array);
}
	
function wp_invoice_profile_update() {
	global $wpdb;
	$user_id =  $_REQUEST['user_id'];

	if(isset($_POST['streetaddress'])) update_usermeta($user_id, 'streetaddress', $_POST['streetaddress']);
	if(isset($_POST['zip']))  update_usermeta($user_id, 'zip', $_POST['zip']);
	if(isset($_POST['state'])) update_usermeta($user_id, 'state', $_POST['state']);
	if(isset($_POST['city'])) update_usermeta($user_id, 'city', $_POST['city']);
	if(isset($_POST['phonenumber'])) update_usermeta($user_id, 'phonenumber', $_POST['phonenumber']);

/* Hardcoding this for now (12/27/08)
	$wp_invoice_billing_meta = unserialize(urldecode(get_option('wp_invoice_billing_meta')));
  
	foreach ($wp_invoice_billing_meta as $value)
	{$converted_value =  strtolower(str_replace(" ", "",$value));
		
		if(!empty($_POST[$converted_value]))
		{
			$meta_value = $_POST[$converted_value];
			update_usermeta($user_id, $converted_value, $meta_value );
		}
	}
*/  	
}
	
class wp_invoice_Date 
{

	function convert($string, $from_mask, $to_mask='', $return_unix=false)
	{
		// define the valid values that we will use to check
		// value => length
		$all = array(
			's' => 'ss',
			'i' => 'ii',
			'H' => 'HH',
			'y' => 'yy',
			'Y' => 'YYYY', 
			'm' => 'mm', 
			'd' => 'dd'
		);

		// this will give us a mask with full length fields
		$from_mask = str_replace(array_keys($all), $all, $from_mask);

		$vals = array();
		foreach($all as $type => $chars)
		{
			// get the position of the current character
			if(($pos = strpos($from_mask, $chars)) === false)
				continue;

			// find the value in the original string
			$val = substr($string, $pos, strlen($chars));

			// store it for later processing
			$vals[$type] = $val;
		}

		foreach($vals as $type => $val)
		{
			switch($type)
			{
				case 's' :
					$seconds = $val;
				break;
				case 'i' :
					$minutes = $val;
				break;
				case 'H':
					$hours = $val;
				break;
				case 'y':
					$year = '20'.$val; // Year 3k bug right here
				break;
				case 'Y':
					$year = $val;
				break;
				case 'm':
					$month = $val;
				break;
				case 'd':
					$day = $val;
				break;
			}
		}

		$unix_time = mktime(
			(int)$hours, (int)$minutes, (int)$seconds, 
			(int)$month, (int)$day, (int)$year);
		
		if($return_unix)
			return $unix_time;

		return date($to_mask, $unix_time);
	}
}


function wp_invoice_fix_billing_meta_array($arr){
    $narr = array();
	$counter = 1;
    while(list($key, $val) = each($arr)){
        if (is_array($val)){
            $val = array_remove_empty($val);
            if (count($val)!=0){
                $narr[$counter] = $val;$counter++;
            }
        }
        else {
            if (trim($val) != ""){
                $narr[$counter] = $val;$counter++;
            }
        }
		
    }
    unset($arr);
    return $narr;
}


function user_profile_invoice_fields()
{
	global $wpdb;

	global $wpdb;
	$user_id =  $_REQUEST['user_id'];

	  
	$profileuser = get_user_to_edit($user_id);
	?>

	<h3>Billing / Invoicing Info</h3>
	<table class="form-table">

	<tr>
	<th><label for="streetaddress">Street Address</label></th>
	<td><input type="text" name="streetaddress" id="streetaddress" value="<?php echo get_usermeta($user_id,'streetaddress'); ?>" /></td>
	</tr>
	
	<tr>
	<th><label for="city">City</label></th>
	<td><input type="text" name="city" id="city" value="<?php echo get_usermeta($user_id,'city'); ?>" /></td>
	</tr>
	
	<tr>
	<th><label for="state">State</label></th>
	<td><input type="text" name="state" id="state" value="<?php echo get_usermeta($user_id,'state'); ?>" /></td>
	</tr>
	
	<tr>
	<th><label for="streetaddress">ZIP Code</label></th>
	<td><input type="text" name="zip" id="zip" value="<?php echo get_usermeta($user_id,'zip'); ?>" /></td>
	</tr>

	<tr>
	<th><label for="phonenumber">Phone Number</label></th>
	<td><input type="text" name="phonenumber" id="phonenumber" value="<?php echo get_usermeta($user_id,'phonenumber'); ?>" /></td>
	</tr>

	
	</table>
<?php
}




?>