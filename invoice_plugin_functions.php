<?php 
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

setlocale(LC_MONETARY, 'en_US'); 

function wp_invoice_number_of_invoices()
{
	global $wpdb;
	$query = "SELECT COUNT(*) FROM ".WP_INVOICE_TABLE_MAIN."";
	$count = $wpdb->get_var($query);
	return $count;
}

function wp_invoice_does_invoice_exist($invoice_id) {
global $wpdb;
 return $wpdb->get_var("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = $invoice_id");
}

function wp_invoice_validate_cc_number($cc_number) {
   /* Validate; return value is card type if valid. */
   $false = false;
   $card_type = "";
   $card_regexes = array(
      "/^4\d{12}(\d\d\d){0,1}$/" => "visa",
      "/^5[12345]\d{14}$/"       => "mastercard",
      "/^3[47]\d{13}$/"          => "amex",
      "/^6011\d{12}$/"           => "discover",
      "/^30[012345]\d{11}$/"     => "diners",
      "/^3[68]\d{12}$/"          => "diners",
   );

   foreach ($card_regexes as $regex => $type) {
       if (preg_match($regex, $cc_number)) {
           $card_type = $type;
           break;
       }
   }

   if (!$card_type) {
       return $false;
   }

   /*  mod 10 checksum algorithm  */
   $revcode = strrev($cc_number);
   $checksum = 0;

   for ($i = 0; $i < strlen($revcode); $i++) {
       $current_num = intval($revcode[$i]);
       if($i & 1) {  /* Odd  position */
          $current_num *= 2;
       }
       /* Split digits and add. */
           $checksum += $current_num % 10; if
       ($current_num >  9) {
           $checksum += 1;
       }
   }

   if ($checksum % 10 == 0) {
       return $card_type;
   } else {
       return $false;
   }
}
	


function wp_invoice_draw_user_selection_form($user_id) {
	global $wpdb;
?>
	<form action="admin.php?page=new_invoice" method='POST'>
		<table class="form-table" id="get_user_info">
			<tr class="invoice_main">
				<th><?php if(isset($user_id)) { ?>Start New Invoice For: <?php } else { ?>Create New Invoice For:<?php } ?></th>
				<td> 
					<select name='user_id' class='user_selection'>
					<option ></option>
					<?php
					$get_all_users = $wpdb->get_results("SELECT * FROM wp_users LEFT JOIN wp_usermeta on wp_users.id=wp_usermeta.user_id and wp_usermeta.meta_key='last_name' ORDER BY wp_usermeta.meta_value");
					foreach ($get_all_users as $user)
					{ 
					$profileuser = get_user_to_edit($user->ID);
					echo "<option ";
					if(isset($user_id) && $user_id == $user->ID) echo " SELECTED ";
					if(!empty($profileuser->last_name) && !empty($profileuser->first_name)) { echo " value=\"".$user->ID."\">". $profileuser->last_name. ", " . $profileuser->first_name . " (".$profileuser->user_email.")</option>\n";  }
					else 
					{
					echo " value=\"".$user->ID."\">". $profileuser->user_login. " (".$profileuser->user_email.")</option>\n"; 
					}
					}
					?>
					</select>
					<input type='submit' class='button' id="wp_invoice_create_new_invoice" value='Create New Invoice'> 
					
					
					<?php if(wp_invoice_number_of_invoices() > 0) { ?><span id="wp_invoice_copy_invoice" class="wp_invoice_click_me">copy from another</span>
					<br />
					<?php if(!isset($user_id)) { ?>User must have a profile to receive invoices. 

					<?php if($GLOBALS['wp_version'] < '2.7') { echo "<a href=\"users.php\">Create a new user account.</a>";  } 
					else { echo "<a href=\"user-new.php\">Create a new user account.</a>"; } }	 ?>

			<div class="wp_invoice_copy_invoice">
			<?php 	$all_invoices = $wpdb->get_results("SELECT * FROM ".WP_INVOICE_TABLE_MAIN); ?>
			<select name="copy_from_template">
<option SELECTED value=""></option>
		<?php 	foreach ($all_invoices as $invoice) { 
		$profileuser = get_user_to_edit($invoice->user_id);
		?>
		
		<option value="<?php echo $invoice->invoice_num; ?>"> <?php echo $invoice->subject . " - $" .$invoice->amount; ?></option>
		
		<?php } ?>
		
		</select><input type='submit' class='button' value='New Invoice from Template'> <span id="wp_invoice_copy_invoice_cancel" class="wp_invoice_click_me">cancel</span>
			</div>
<?php } ?>			
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
	$wpdb->query("INSERT INTO ".WP_INVOICE_TABLE_LOG." 
	(invoice_id , action_type , value)
	VALUES ('$invoice_id', '$action_type', '$value' );");
	}
}

function wp_invoice_meta($invoice_id,$meta_key)
{
	global $wpdb;
	return $wpdb->get_var("SELECT meta_value FROM `".WP_INVOICE_TABLE_META."` WHERE meta_key = '$meta_key' AND invoice_id = '$invoice_id'");
}

function wp_invoice_update_invoice_meta($invoice_id,$meta_key,$meta_value)
{

	global $wpdb;
	if(empty($meta_value)) {
		// Dlete meta_key if no value is set
		$wpdb->query("DELETE FROM ".WP_INVOICE_TABLE_META." WHERE  invoice_id = '$invoice_id' AND meta_key = '$meta_key'"); 
	}
	else
	{
		// Check if meta key already exists, then we replace it WP_INVOICE_TABLE_META
		if($wpdb->get_var("SELECT meta_key 	FROM `".WP_INVOICE_TABLE_META."` WHERE meta_key = '$meta_key' AND invoice_id = '$invoice_id'"))
		{ $wpdb->query("UPDATE `".WP_INVOICE_TABLE_META."` SET meta_value = '$meta_value' WHERE meta_key = '$meta_key' AND invoice_id = '$invoice_id'"); }
		else
		{ $wpdb->query("INSERT INTO `".WP_INVOICE_TABLE_META."` (invoice_id, meta_key, meta_value) VALUES ('$invoice_id','$meta_key','$meta_value')"); }
	}
}

function wp_invoice_delete_invoice_meta($invoice_id,$meta_key='')
{
	global $wpdb;
	if(empty($meta_key)) 
	{ $wpdb->query("DELETE FROM `".WP_INVOICE_TABLE_META."` WHERE invoice_id = '$invoice_id' ");}
	else
	{ $wpdb->query("DELETE FROM `".WP_INVOICE_TABLE_META."` WHERE invoice_id = '$invoice_id' AND meta_key = '$meta_key'");}

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

		wp_invoice_update_log($single_invoice_id, "deleted", "Deleted on ");
		
		// Get all meta keys for this invoice, then delete them
		
		$all_invoice_meta_values = $wpdb->get_col("SELECT invoice_id FROM ".WP_INVOICE_TABLE_META." WHERE invoice_id = '$single_invoice_id'");

		//print_r($all_invoice_meta_values);
		foreach ($all_invoice_meta_values as $meta_key) {
			wp_invoice_delete_invoice_meta($single_invoice_id);

		}
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

function wp_invoice_archive($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;
	wp_invoice_update_invoice_meta($single_invoice_id, "archive_status", "archived");
	}
	return $counter . " invoices archived.";

}
else
{
	wp_invoice_update_invoice_meta($invoice_id, "archive_status", "archived");
	return "Invoice successfully archived.";
}
}

function wp_invoice_unarchive($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;
	wp_invoice_delete_invoice_meta($single_invoice_id, "archive_status");
	}
	return $counter . " invoices unarchived.";

}
else
{
	wp_invoice_delete_invoice_meta($invoice_id, "archive_status");
	return "Invoice successfully unarchived.";
}
}

function wp_invoice_make_template($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;	
	wp_invoice_update_invoice_meta($single_invoice_id, "template_status", true);
	}
	return $counter . " invoices' template status updated.";

}
else
{
	wp_invoice_update_invoice_meta($invoice_id, "template_status", true);
	return "Invoice template status updated.";
}
}

function wp_invoice_unmake_template($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;	
	wp_invoice_delete_invoice_meta($single_invoice_id, "template_status");
	}
	return $counter . " invoices' template status updated.";

}
else
{
	wp_invoice_delete_invoice_meta($invoice_id, "template_status");
	return "Invoice template status updated.";
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

function wp_invoice_clear_invoice_status($invoice_id) 
{
	global $wpdb;
	if(isset($invoice_id)) {
	if($wpdb->query("DELETE FROM ".WP_INVOICE_TABLE_LOG." WHERE invoice_id = $invoice_id"))
	return "Logs for invoice #$invoice_id cleared.";
	}
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
 	if(empty($invoice_info->description)) { $message .= stripslashes(get_option("wp_invoice_business_name")) . " has sent you a web invoice totaling $". number_format($invoice_info->amount, 2, '.', ',') . ". \n\n"; } 
	else { $message .= stripslashes(get_option("wp_invoice_business_name")) . " has sent you a web invoice totaling $".number_format($invoice_info->amount, 2, '.', ',') . ". \n\n$invoice_info->description \n\n"; }

	$message .= "You may pay, view and print the invoice online by visiting the following link: \n";
	$message .= wp_invoice_build_invoice_link($invoice_id) . "\n\n";
	$message .= "Best regards,\n";
	$message .= stripslashes(get_option("wp_invoice_business_name")) . "(" .  get_option("wp_invoice_email_address")  . ")";
	
	return $message;
}

function wp_invoice_currency_format($amount) {
	return "$" . number_format($amount, 2, '.', ',');
}

function wp_invoice_paid($invoice_id) {
	global $wpdb;
	$wpdb->query("UPDATE  ".WP_INVOICE_TABLE_MAIN." SET status = 1 WHERE  invoice_num = '$invoice_id'");
}

function wp_invoice_paid_status($invoice_id) {
	global $wpdb;
	return $wpdb->get_var("SELECT status FROM  ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '$invoice_id'");
}

function wp_invoice_archive_status($invoice_id) {
	global $wpdb;
	$result = $wpdb->get_col("SELECT action_type FROM  ".WP_INVOICE_TABLE_LOG." WHERE invoice_id = '".$invoice_id."' ORDER BY time_stamp DESC");
	
	foreach($result as $event){
			if ($event == 'unarchive') { return ''; break; }
			if ($event == 'archive') { return 'archive'; break; }
			}
}

function wp_invoice_paid_date($invoice_id) {
	global $wpdb;
	return $wpdb->get_var("SELECT time_stamp FROM  ".WP_INVOICE_TABLE_LOG." WHERE action_type = 'paid' AND invoice_id = '".$invoice_id."' ORDER BY time_stamp DESC LIMIT 0, 1");
	
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
		if(get_option('wp_invoice_show_quantities') == "Show") { $response .= '<th style="width: 70px; text-align: right;">Quantity</th>'; }
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
		if(wp_invoice_meta($invoice_id,'tax_value')) {
		$tax_percent = wp_invoice_meta($invoice_id,'tax_value');

		$response .= "<tr><td>Included Tax</td><td style='text-align:right;' colspan='2'>". $tax_percent. "%</td></tr>";
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

 	$message .= "Thank you for your payment of $". $invoice_info->amount . " for invoice #$invoice_id. \n\n ";

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
			
			

	$sql_WP_INVOICE_TABLE_METADATA = "CREATE TABLE IF NOT EXISTS `" . WP_INVOICE_TABLE_META . "` (
  `meta_id` bigint(20) NOT NULL auto_increment,
  `invoice_id` bigint(20) NOT NULL default '0',
  `meta_key` varchar(255) default NULL,
  `meta_value` longtext,
  PRIMARY KEY  (`meta_id`),
  KEY `post_id` (`invoice_id`),
  KEY `meta_key` (`meta_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	
	
	// Fix Paid Statuses  from Old Version where they were kept in main table
	$all_invoices = $wpdb->get_results("SELECT invoice_num FROM ".WP_INVOICE_TABLE_MAIN." WHERE status ='1'");
	if(!empty($all_invoices)) {
		foreach ($all_invoices as $invoice) 
		{
		wp_invoice_update_invoice_meta($invoice->invoice_num,'paid_status','paid');
		}
	}
	
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_WP_INVOICE_TABLE_MAIN);
	dbDelta($sql_WP_INVOICE_TABLE_LOG);
	dbDelta($sql_WP_INVOICE_TABLE_METADATA);


	$wp_invoice_billing_meta[1] = 'Street Address';
	$wp_invoice_billing_meta[2] = 'City';
	$wp_invoice_billing_meta[3] = 'State';
	$wp_invoice_billing_meta[4] = 'Zip';
	$wp_invoice_billing_meta[5] = 'Phone Number';

	$wp_invoice_billing_meta = urlencode(serialize($wp_invoice_billing_meta));

		
	add_option('wp_invoice_version', WP_INVOICE_VERSION_NUM);
	add_option('wp_invoice_email_address',get_bloginfo('admin_email'));
	add_option('wp_invoice_business_name', get_bloginfo('blogname'));
	add_option('wp_invoice_business_address', get_bloginfo('blogname'));
	add_option('wp_invoice_show_business_address', 'no');
	add_option('wp_invoice_payment_method','');
	add_option('wp_invoice_protocol','http');
	add_option('wp_invoice_web_invoice_page','');
	add_option('wp_invoice_paypal_address','');
	add_option('wp_invoice_billing_meta',$wp_invoice_billing_meta);
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
	add_option('wp_invoice_gateway_url','https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi');
	add_option('wp_invoice_gateway_MD5Hash','');
	
	add_option('wp_invoice_gateway_test_mode','FALSE');
	add_option('wp_invoice_gateway_delim_data','TRUE');
	add_option('wp_invoice_gateway_relay_response','FALSE');
	add_option('wp_invoice_gateway_email_customer','FALSE');

}


function wp_invoice_deactivation($confirm=false) 
{
	global $wpdb;

	
	//Clean Up Log (We Keep Invoice Data on File just in Case)
	$wpdb->query("DROP TABLE " . WP_INVOICE_TABLE_LOG .";");	
}


function wp_invoice_complete_removal() 
{
	// Run regular deactivation, but also delete the main table - all invoice data is gone
	global $wpdb;
	wp_invoice_deactivation() ;;
	$wpdb->query("DROP TABLE " . WP_INVOICE_TABLE_MAIN .";");
	$wpdb->query("DROP TABLE " . WP_INVOICE_TABLE_META .";");
	
	delete_option('wp_invoice_version');
	delete_option('wp_invoice_payment_link');
	delete_option('wp_invoice_payment_method');
	delete_option('wp_invoice_protocol');
	delete_option('wp_invoice_email_address');
	delete_option('wp_invoice_business_name');
	delete_option('wp_invoice_business_address');
	delete_option('wp_invoice_business_phone');
	delete_option('wp_invoice_paypal_address');
	delete_option('wp_invoice_web_invoice_page');
	delete_option('wp_invoice_billing_meta');
	delete_option('wp_invoice_show_quantities');
	delete_option('wp_invoice_use_css');
	delete_option('wp_invoice_hide_page_title');
	delete_option('wp_invoice_send_thank_you_email');
	
	//Gateway Settings
	delete_option('wp_invoice_gateway_username');
	delete_option('wp_invoice_gateway_tran_key');
	delete_option('wp_invoice_gateway_delim_char');
	delete_option('wp_invoice_gateway_encap_char');
	delete_option('wp_invoice_gateway_merchant_email');
	delete_option('wp_invoice_gateway_header_email_receipt');
	delete_option('wp_invoice_gateway_url');
	delete_option('wp_invoice_gateway_MD5Hash');
	delete_option('wp_invoice_gateway_test_mode');
	delete_option('wp_invoice_gateway_delim_data');
	delete_option('wp_invoice_gateway_relay_response');
	delete_option('wp_invoice_gateway_email_customer');
	
	
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
			wp_invoice_update_invoice_meta($invoice_id, "sent_date", date("Y-m-d", time()));
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
		{ 
			wp_invoice_update_invoice_meta($invoice_id, "sent_date", date("Y-m-d", time()));
			wp_invoice_update_log($invoice_id,'contact','Invoice eMailed'); return "Web invoice sent successfully."; } 
		else
		{ return "There was a problem sending the invoice."; }
		

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


	if(isset($_POST['address'])) update_usermeta($user_id, 'streetaddress', $_POST['streetaddress']);
	if(isset($_POST['zip']))  update_usermeta($user_id, 'zip', $_POST['zip']);
	if(isset($_POST['state'])) update_usermeta($user_id, 'state', $_POST['state']);
	if(isset($_POST['city'])) update_usermeta($user_id, 'city', $_POST['city']);
	if(isset($_POST['phone_number'])) update_usermeta($user_id, 'phonenumber', $_POST['phonenumber']);

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
	$user_id =  $_REQUEST['user_id'];


	  
	$profileuser = get_user_to_edit($user_id);
	?>

	<h3>Billing / Invoicing Info</h3>
	<a name="billing_info"></a>
	<table class="form-table" >

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
	<td><input type="text" name="state" id="state" value="<?php echo get_usermeta($user_id,'state'); ?>" /><br />
	<p class="note">Use two-letter state codes for safe credit card processing.</p></td>
	</tr>
	
	<tr>
	<th><label for="streetaddress">ZIP Code</label></th>
	<td><input type="text" name="zip" id="zip" value="<?php echo get_usermeta($user_id,'zip'); ?>" /></td>
	</tr>

	<tr>
	<th><label for="phonenumber">Phone Number</label></th>
	<td><input type="text" name="phonenumber" id="phonenumber" value="<?php echo get_usermeta($user_id,'phonenumber'); ?>" />
	<p class="note">Enforce 555-555-5555 format if you are using PayPal.</p></td>
	</tr>
	
	<tr>
	<th></th>
	<td>

		<input type='button' onclick="window.location='admin.php?page=new_invoice&user_id=<?PHP echo $_REQUEST['user_id']; ?>';" class='button' value='Create New Invoice For This User'>

	</td>
	</tr>

	
</table>
<?php
}


function wp_invoice_printYearDropdown($sel='')
{
$localDate=getdate();
$minYear = $localDate["year"];
$maxYear = $minYear + 15;

  $output =  "<option value=''>--</option>";
  for($i=$minYear; $i<$maxYear; $i++) {
    $output .= "<option value='". substr($i, 2, 2) ."'".($sel==(substr($i, 2, 2))?' selected':'').
	">". $i ."</option>";
  }
  return($output);
}

function wp_invoice_printMonthDropdown($sel='')
{
  $output =  "<option value=''>--</option>";
  $output .=  "<option " . ($sel==1?' selected':'') . " value='01'>01 - Jan</option>";
  $output .=  "<option " . ($sel==2?' selected':'') . "  value='02'>02 - Feb</option>";
  $output .=  "<option " . ($sel==3?' selected':'') . "  value='03'>03 - Mar</option>";
  $output .=  "<option " . ($sel==4?' selected':'') . "  value='04'>04 - Apr</option>";
  $output .=  "<option " . ($sel==5?' selected':'') . "  value='05'>05 - May</option>";
  $output .=  "<option " . ($sel==6?' selected':'') . "  value='06'>06 - Jun</option>";
  $output .=  "<option " . ($sel==7?' selected':'') . "  value='07'>07 - Jul</option>";
  $output .=  "<option " . ($sel==8?' selected':'') . "  value='08'>08 - Aug</option>";
  $output .=  "<option " . ($sel==9?' selected':'') . "  value='09'>09 - Sep</option>";
  $output .=  "<option " . ($sel==10?' selected':'') . "  value='10'>10 - Oct</option>";
  $output .=  "<option " . ($sel==11?' selected':'') . "  value='11'>11 - Nov</option>";
  $output .=  "<option " . ($sel==12?' selected':'') . "  value='12'>12 - Doc</option>";

  return($output);
}



function wp_invoice_printStateDropdown($sel='')
{
$StateProvinceTwoToFull = array(
   'AL' => 'Alabama',
   'AK' => 'Alaska',
   'AS' => 'American Samoa',
   'AZ' => 'Arizona',
   'AR' => 'Arkansas',
   'CA' => 'California',
   'CO' => 'Colorado',
   'CT' => 'Connecticut',
   'DE' => 'Delaware',
   'DC' => 'District of Columbia',
   'FM' => 'Federated States of Micronesia',
   'FL' => 'Florida',
   'GA' => 'Georgia',
   'GU' => 'Guam',
   'HI' => 'Hawaii',
   'ID' => 'Idaho',
   'IL' => 'Illinois',
   'IN' => 'Indiana',
   'IA' => 'Iowa',
   'KS' => 'Kansas',
   'KY' => 'Kentucky',
   'LA' => 'Louisiana',
   'ME' => 'Maine',
   'MH' => 'Marshall Islands',
   'MD' => 'Maryland',
   'MA' => 'Massachusetts',
   'MI' => 'Michigan',
   'MN' => 'Minnesota',
   'MS' => 'Mississippi',
   'MO' => 'Missouri',
   'MT' => 'Montana',
   'NE' => 'Nebraska',
   'NV' => 'Nevada',
   'NH' => 'New Hampshire',
   'NJ' => 'New Jersey',
   'NM' => 'New Mexico',
   'NY' => 'New York',
   'NC' => 'North Carolina',
   'ND' => 'North Dakota',
   'MP' => 'Northern Mariana Islands',
   'OH' => 'Ohio',
   'OK' => 'Oklahoma',
   'OR' => 'Oregon',
   'PW' => 'Palau',
   'PA' => 'Pennsylvania',
   'PR' => 'Puerto Rico',
   'RI' => 'Rhode Island',
   'SC' => 'South Carolina',
   'SD' => 'South Dakota',
   'TN' => 'Tennessee',
   'TX' => 'Texas',
   'UT' => 'Utah',
   'VT' => 'Vermont',
   'VI' => 'Virgin Islands',
   'VA' => 'Virginia',
   'WA' => 'Washington',
   'WV' => 'West Virginia',
   'WI' => 'Wisconsin',
   'WY' => 'Wyoming',
   'AB' => 'Alberta',
   'BC' => 'British Columbia',
   'MB' => 'Manitoba',
   'NB' => 'New Brunswick',
   'NF' => 'Newfoundland',
   'NW' => 'Northwest Territory',
   'NS' => 'Nova Scotia',
   'ON' => 'Ontario',
   'PE' => 'Prince Edward Island',
   'QU' => 'Quebec',
   'SK' => 'Saskatchewan',
   'YT' => 'Yukon Territory',
	);

  $output =  "<option value=''>Select A State</option>";
  while(list($abbreviation, $name) = each($StateProvinceTwoToFull)) {
    $output .= "<option value='". $abbreviation ."'".($sel==$abbreviation?' selected':'').
	">". $name ."</option>";
  }
  reset($StateProvinceTwoToFull);
  return($output);
}

function StateProvinceGetFullName($code)
{
		global $StateProvinceTwoToFull;

		if(strlen($code) == 2) {
			return($StateProvinceTwoToFull[$code]);
		}
		else if( strlen($code) > 2)
		{
			return($code);
		}
		else
			return("");
}

		
		


function wp_invoice_check_plugin_version($plugin)
{
	global $wp_invoice_plugindir,$wp_invoice_localversion;
 	if( strpos($wp_invoice_plugindir.'/invoice_plugin.php',$plugin)!==false )
 	{
		$checkfile = "http://twincitiestech.com/files/wp-invoice.chk";
		
		$vcheck = wp_remote_fopen($checkfile);

		if($vcheck)
		{
			$version = $wp_invoice_localversion;
			$status = explode('@', $vcheck);
			$theVersion = $status[1];
			$theMessage = $status[3];

			if((version_compare(strval($theVersion), strval($version), '>') == 1))
			{
				$msg = __("Latest version available:", "sforum").' <strong>'.$theVersion.'</strong><br />'.$theMessage;
				$msg.= '<br /><a href="http://twincitiestech.com/services/wp-invoice/">WP-Invoice by TwinCitiesTech.com</a>';
				echo '<td colspan="5" class="plugin-update" style="line-height:1.2em;">'.$msg.'</td>';
			} else {
				return;
			}
		}
	}
}

function wp_invoice_go_secure($destination) {
    $reload = 'Location: ' . $destination;
    header($reload);
} 

function wp_invoice_process_cc_transaction($cc_data) {

$errors = array ();
$errors_msg = null;
$_POST["processing_problem"] = "";
$invoice_id = preg_replace("/[^0-9]/","", $_POST['invoice_num']);  
$wp_users_id = get_invoice_user_id($invoice_id);

if(empty($_POST['card_num'])) {	$errors [ 'card_num' ] []  = "Please enter your credit card number.\n";	$stop_transaction = true;} else { if (!wp_invoice_validate_cc_number($_POST['card_num'])){$errors [ 'card_num' ] [] = "Please enter a valid credit card number.\n"; $stop_transaction = true; } }
if(empty($_POST['first_name'])){$errors [ 'first_name' ] [] = "Please enter your first name.\n";$stop_transaction = true;}
if(empty($_POST['last_name'])){$errors [ 'last_name' ] [] = "Please enter your last name. \n";$stop_transaction = true;}
if(empty($_POST['address'])){$errors [ 'address' ] [] = "Please enter your address.\n";$stop_transaction = true;}
if(empty($_POST['city'])){$errors [ 'city' ] [] = "Please enter your city.\n";$stop_transaction = true;}
if(empty($_POST['state'])){$errors [ 'state' ] [] = "Please select your state.\n";$stop_transaction = true;}
if(empty($_POST['zip'])){$errors [ 'zip' ] [] = "Please enter your ZIP code.\n";$stop_transaction = true;}
if(empty($_POST['exp_month'])){$errors [ 'exp_month' ] [] = "Please enter your credit card's expiration month.";$stop_transaction = true;}
if(empty($_POST['exp_year'])){$errors [ 'exp_year' ] [] = "Please enter your credit card's expiration year.";$stop_transaction = true;}
if(empty($_POST['card_code'])){$errors [ 'card_code' ] [] = "The <b>Security Code</b> is the code on the back of your card.";$stop_transaction = true;}
$_REQUEST[trand_id] = rand(1000, 5000);

if(!$stop_transaction) {

	$transaction = new GatewayTransaction($_REQUEST, $_SERVER['REMOTE_ADDR']);
	if($transaction->ProcessTransaction($responseString, $errorCode))
	{
		$response = new GatewayResponse($responseString, get_option("wp_invoice_gateway_delim_char"), stripslashes(get_option("wp_invoice_gateway_encap_char")));

		if($GatewaySettings['MD5Hash'] && !$response->VerifyMD5Hash($GatewaySettings['MD5Hash'],$transaction->username, $transaction->amount)) { $errors [ 'processing_problem' ] [] = $transaction->GetErrorString("INVALID_MD5HASH") . " \n"; }

			if($response->IsApproved())
			{			
				update_usermeta($wp_users_id,'last_name',$_POST['last_name']);
				update_usermeta($wp_users_id,'first_name',$_POST['first_name']);
				update_usermeta($wp_users_id,'city',$_POST['city']);
				update_usermeta($wp_users_id,'state',$_POST['state']);
				update_usermeta($wp_users_id,'zip',$_POST['zip']);
				update_usermeta($wp_users_id,'streetaddress',$_POST['street_address']);
				update_usermeta($wp_users_id,'phonenumber',$_POST['phone_number']);

				wp_invoice_paid($invoice_id);
				if(get_option('wp_invoice_send_thank_you_email') == 'yes') wp_invoice_send_email_reciept($invoice_id);
				wp_invoice_update_invoice_meta($invoice_id,'paid_status','paid');				
			}
			else
			{
				$errors [ 'processing_problem' ] [] = $response->GetField("ResponseReasonText") . " \n";
			}
	}
	else
	{
	$errors [ 'processing_problem' ] [] = $transaction->GetErrorString($errorCode) . " \n";
	}
	
}

	
if ( is_array ( $_POST ) )
{
	foreach ( $_POST as $key => $value )
	{
		if ( array_key_exists ( $key, $errors ) )
		{
			foreach ( $errors [ $key ] as $k => $v )
			{
				$errors_msg .= "error|$key|$v\n";
			}
		}
		else {
			$errors_msg .= "ok|$key\n";
		}
	}
}

		
echo $errors_msg;
}


?>