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
	$query = "SELECT COUNT(*) FROM ".WP_Invoice::tablename('main')."";
	$count = $wpdb->get_var($query);
	return $count;
}

function wp_invoice_does_invoice_exist($invoice_id) {
	global $wpdb;
	return $wpdb->get_var("SELECT * FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = $invoice_id");
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
	




function wp_invoice_update_log($invoice_id,$action_type,$value) 
{
	global $wpdb;
	if(isset($invoice_id))
	{
	$wpdb->query("INSERT INTO ".WP_Invoice::tablename('log')." 
	(invoice_id , action_type , value)
	VALUES ('$invoice_id', '$action_type', '$value' );");
	}
}

function wp_invoice_query_log($invoice_id,$action_type) {
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM ".WP_Invoice::tablename('log')." WHERE invoice_id = '$invoice_id' AND action_type = '$action_type' ORDER BY 'time_stamp' DESC");
}

function wp_invoice_meta($invoice_id,$meta_key)
{
	global $wpdb;
	return $wpdb->get_var("SELECT meta_value FROM `".WP_Invoice::tablename('meta')."` WHERE meta_key = '$meta_key' AND invoice_id = '$invoice_id'");
}

function wp_invoice_update_invoice_meta($invoice_id,$meta_key,$meta_value)
{

	global $wpdb;
	if(empty($meta_value)) {
		// Dlete meta_key if no value is set
		$wpdb->query("DELETE FROM ".WP_Invoice::tablename('meta')." WHERE  invoice_id = '$invoice_id' AND meta_key = '$meta_key'"); 
	}
	else
	{
		// Check if meta key already exists, then we replace it WP_Invoice::tablename('meta')
		if($wpdb->get_var("SELECT meta_key 	FROM `".WP_Invoice::tablename('meta')."` WHERE meta_key = '$meta_key' AND invoice_id = '$invoice_id'"))
		{ $wpdb->query("UPDATE `".WP_Invoice::tablename('meta')."` SET meta_value = '$meta_value' WHERE meta_key = '$meta_key' AND invoice_id = '$invoice_id'"); }
		else
		{ $wpdb->query("INSERT INTO `".WP_Invoice::tablename('meta')."` (invoice_id, meta_key, meta_value) VALUES ('$invoice_id','$meta_key','$meta_value')"); }
	}
}

function wp_invoice_delete_invoice_meta($invoice_id,$meta_key='')
{
	global $wpdb;
	if(empty($meta_key)) 
	{ $wpdb->query("DELETE FROM `".WP_Invoice::tablename('meta')."` WHERE invoice_id = '$invoice_id' ");}
	else
	{ $wpdb->query("DELETE FROM `".WP_Invoice::tablename('meta')."` WHERE invoice_id = '$invoice_id' AND meta_key = '$meta_key'");}

}


function wp_invoice_delete($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
		$counter++;
		$wpdb->query("DELETE FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = '$single_invoice_id'");

		wp_invoice_update_log($single_invoice_id, "deleted", "Deleted on ");
		
		// Get all meta keys for this invoice, then delete them
		
		$all_invoice_meta_values = $wpdb->get_col("SELECT invoice_id FROM ".WP_Invoice::tablename('meta')." WHERE invoice_id = '$single_invoice_id'");

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
	$wpdb->query("DELETE FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = '$invoice_id'");
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
	return $counter . " invoice(s) archived.";

}
else
{
	wp_invoice_update_invoice_meta($invoice_id, "archive_status", "archived");
	return "Invoice successfully archived.";
}
}

function wp_invoice_mark_as_paid($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;
	wp_invoice_update_invoice_meta($single_invoice_id,'paid_status','paid');
 	wp_invoice_update_log($single_invoice_id,'paid',"Invoice marked as paid");
	if(get_option('wp_invoice_send_thank_you_email') == 'yes') wp_invoice_send_email_reciept($single_invoice_id);
	}
	
	if(get_option('wp_invoice_send_thank_you_email') == 'yes') {
	return $counter . " invoice(s) marked as paid, and thank you email sent to customer.";
	}
	else{
	return $counter . " invoice(s) marked as paid.";
	}
}
else
{
	wp_invoice_update_invoice_meta($invoice_id,'paid_status','paid');
 	wp_invoice_update_log($invoice_id,'paid',"Invoice marked as paid");
	if(get_option('wp_invoice_send_thank_you_email') == 'yes') wp_invoice_send_email_reciept($single_invoice_id);

	if(get_option('wp_invoice_send_thank_you_email') == 'yes') {
	return $counter . " invoice marked as paid, and thank you email sent to customer.";
	}
	else{
	return $counter . " invoice marked as paid.";
	}}
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
	return $counter . " invoice(s) unarchived.";

}
else
{
	wp_invoice_delete_invoice_meta($invoice_id, "archive_status");
	return "Invoice successfully unarchived.";
}
}

function wp_invoice_mark_as_sent($invoice_id) {
global $wpdb;

// Check to see if array is passed or single.
if(is_array($invoice_id))
{
	$counter=0;
	foreach ($invoice_id as $single_invoice_id) {
	$counter++;
	wp_invoice_update_invoice_meta($single_invoice_id, "sent_date", date("Y-m-d", time()));
	wp_invoice_update_log($single_invoice_id,'contact','Invoice Maked as eMailed'); //make sent entry
	
	}
	return $counter . " invoice(s) marked as sent.";

}
else
{
	wp_invoice_update_invoice_meta($invoice_id, "sent_date", date("Y-m-d", time()));
	wp_invoice_update_log($invoice_id,'contact','Invoice Maked as eMailed'); //make sent entry
	
	return "Invoice market as sent.";
}
}

function wp_invoice_get_invoice_attrib($invoice_id,$attribute) 
{
	global $wpdb;
	$query = "SELECT $attribute FROM ".WP_Invoice::tablename('main')." WHERE invoice_num=".$invoice_id."";
	return $wpdb->get_var($query);
}

function wp_invoice_get_invoice_status($invoice_id,$count='1') 
{
	global $wpdb;
	$query = "
	SELECT *
	FROM ".WP_Invoice::tablename('log')."
	WHERE invoice_id = $invoice_id
	ORDER BY `".WP_Invoice::tablename('log')."`.`time_stamp` DESC
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
	if($wpdb->query("DELETE FROM ".WP_Invoice::tablename('log')." WHERE invoice_id = $invoice_id"))
	return "Logs for invoice #$invoice_id cleared.";
	}
}

function wp_invoice_get_single_invoice_status($invoice_id) 
{
	// in class
	global $wpdb;
	if($status_update = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('log')." WHERE invoice_id = $invoice_id ORDER BY `".WP_Invoice::tablename('log')."`.`time_stamp` DESC LIMIT 0 , 1"))
	return $status_update->value . " - " . wp_invoice_Date::convert($status_update->time_stamp, 'Y-m-d H', 'M d Y');
}


function wp_invoice_currency_format($amount) {
	return number_format($amount, 2, '.', ',');
}

function wp_invoice_paid($invoice_id) {
	global $wpdb;
	$wpdb->query("UPDATE  ".WP_Invoice::tablename('main')." SET status = 1 WHERE  invoice_num = '$invoice_id'");
	wp_invoice_update_invoice_meta($invoice_id,'paid_status','paid');
 	wp_invoice_update_log($invoice_id,'paid',"Invoice successfully processed by ". $_SERVER['REMOTE_ADDR']);	
}

function wp_invoice_recurring($invoice_id) {
	global $wpdb;
	if(wp_invoice_meta($invoice_id,'recurring_billing')) return true;
}

function wp_invoice_recurring_started($invoice_id) {
	global $wpdb;
	if(wp_invoice_meta($invoice_id,'subscription_id')) return true;
}

function wp_invoice_paid_status($invoice_id) {
	//Merged with paid_status in class
	global $wpdb;
	if(wp_invoice_meta($invoice_id,'paid_status') || $wpdb->get_var("SELECT status FROM  ".WP_Invoice::tablename('main')." WHERE invoice_num = '$invoice_id'")) return true;
}

function wp_invoice_paid_date($invoice_id) {
	// in invoice class
	global $wpdb;
	return $wpdb->get_var("SELECT time_stamp FROM  ".WP_Invoice::tablename('log')." WHERE action_type = 'paid' AND invoice_id = '".$invoice_id."' ORDER BY time_stamp DESC LIMIT 0, 1");
	
}


function wp_invoice_build_invoice_link($invoice_id) {
	// in invoice class
	global $wpdb;
	
	$link_to_page = get_permalink(get_option('wp_invoice_web_invoice_page'));


	$hashed_invoice_id = md5($invoice_id);
	if(get_option("permalink_structure")) { $link = $link_to_page . "?invoice_id=" .$hashed_invoice_id; } 
	else { $link =  $link_to_page . "&invoice_id=" . $hashed_invoice_id; } 

	return $link;
}


function wp_invoice_draw_inputfield($name,$value,$special = '') {
	
	return "<input id='$name' class='$name'  name='$name' value='$value' $special>";
}

function wp_invoice_draw_select($name,$values,$current_value = '') {
	
	$output = "<select id='$name' name='$name' class='$name'>";
	$output .= "<option></option>";
	foreach($values as $key => $value) {
	$output .=  "<option value='$key'";
	if($key == $current_value) $output .= " selected";	
	$output .= ">$value</option>";
	}
	$output .= "</select>";

	return $output;
}

function wp_invoice_send_email_reciept($invoice_id) {
	global $wpdb;

	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = $invoice_id");


	$email_address = $wpdb->get_var("SELECT user_email FROM ". $wpdb->prefix . "users WHERE id=".$invoice_info->user_id."");
	$first_name = $wpdb->get_var("SELECT meta_value FROM ". $wpdb->prefix . "users LEFT JOIN ". $wpdb->prefix . "usermeta on ". $wpdb->prefix . "users.id =". $wpdb->prefix . "_usermeta.user_id WHERE ". $wpdb->prefix . "users.id =".$invoice_info->user_id." and meta_key='first_name'");
	$last_name = $wpdb->get_var("SELECT meta_value FROM ". $wpdb->prefix . "users LEFT JOIN ". $wpdb->prefix . "usermeta on ". $wpdb->prefix . "users.id =". $wpdb->prefix . "_usermeta.user_id WHERE ". $wpdb->prefix . "users.id =".$invoice_info->user_id." and meta_key='last_name'");

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



function wp_invoice_deactivation($confirm=false) 
{
	global $wpdb;

}


function wp_invoice_complete_removal() 
{
	// Run regular deactivation, but also delete the main table - all invoice data is gone
	global $wpdb;
	wp_invoice_deactivation() ;;
	$wpdb->query("DROP TABLE " . WP_Invoice::tablename('log') .";");
	$wpdb->query("DROP TABLE " . WP_Invoice::tablename('main') .";");
	$wpdb->query("DROP TABLE " . WP_Invoice::tablename('meta') .";");
	
	delete_option('wp_invoice_version');
	delete_option('wp_invoice_payment_link');
	delete_option('wp_invoice_payment_method');
	delete_option('wp_invoice_protocol');
	delete_option('wp_invoice_email_address');
	delete_option('wp_invoice_business_name');
	delete_option('wp_invoice_business_address');
	delete_option('wp_invoice_business_phone');
	delete_option('wp_invoice_paypal_address');
	delete_option('wp_invoice_default_currency_code');
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
	delete_option('wp_invoice_recurring_gateway_url');
	delete_option('wp_invoice_gateway_MD5Hash');
	delete_option('wp_invoice_gateway_test_mode');
	delete_option('wp_invoice_gateway_delim_data');
	delete_option('wp_invoice_gateway_relay_response');
	delete_option('wp_invoice_gateway_email_customer');
	
	return "All settings and databased removed.";
}

function get_invoice_user_id($invoice_id) {
	// in class
	global $wpdb;
	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = '".$invoice_id."'");
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

		$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = '".$invoice_id."'");

		$profileuser = get_user_to_edit($invoice_info->user_id);
		$subject = $invoice_info->subject;
		$message = wp_invoice_show_email($invoice_id);

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
		$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_Invoice::tablename('main')." WHERE invoice_num = '".$invoice_array."'");
		
		$profileuser = get_user_to_edit($invoice_info->user_id);
		$subject = $invoice_info->subject;
		$message = wp_invoice_show_email($invoice_id);
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


	if(isset($_POST['streetaddress'])) update_usermeta($user_id, 'streetaddress', $_POST['streetaddress']);
	if(isset($_POST['zip']))  update_usermeta($user_id, 'zip', $_POST['zip']);
	if(isset($_POST['state'])) update_usermeta($user_id, 'state', $_POST['state']);
	if(isset($_POST['city'])) update_usermeta($user_id, 'city', $_POST['city']);
	if(isset($_POST['phonenumber'])) update_usermeta($user_id, 'phonenumber', $_POST['phonenumber']);

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



function wp_invoice_state_array($sel='')
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

  return($StateProvinceTwoToFull);
}

		
function wp_invoice_country_array() {
	return array("US"=> "United States","AL"=> "Albania","DZ"=> "Algeria","AD"=> "Andorra","AO"=> "Angola","AI"=> "Anguilla","AG"=> "Antigua and Barbuda","AR"=> "Argentina","AM"=> "Armenia","AW"=> "Aruba","AU"=> "Australia","AT"=> "Austria","AZ"=> "Azerbaijan Republic","BS"=> "Bahamas","BH"=> "Bahrain","BB"=> "Barbados","BE"=> "Belgium","BZ"=> "Belize","BJ"=> "Benin","BM"=> "Bermuda","BT"=> "Bhutan","BO"=> "Bolivia","BA"=> "Bosnia and Herzegovina","BW"=> "Botswana","BR"=> "Brazil","VG"=> "British Virgin Islands","BN"=> "Brunei","BG"=> "Bulgaria","BF"=> "Burkina Faso","BI"=> "Burundi","KH"=> "Cambodia","CA"=> "Canada","CV"=> "Cape Verde","KY"=> "Cayman Islands","TD"=> "Chad","CL"=> "Chile","C2"=> "China","CO"=> "Colombia","KM"=> "Comoros","CK"=> "Cook Islands","CR"=> "Costa Rica","HR"=> "Croatia","CY"=> "Cyprus","CZ"=> "Czech Republic","CD"=> "Democratic Republic of the Congo","DK"=> "Denmark","DJ"=> "Djibouti","DM"=> "Dominica","DO"=> "Dominican Republic","EC"=> "Ecuador","SV"=> "El Salvador","ER"=> "Eritrea","EE"=> "Estonia","ET"=> "Ethiopia","FK"=> "Falkland Islands","FO"=> "Faroe Islands","FM"=> "Federated States of Micronesia","FJ"=> "Fiji","FI"=> "Finland","FR"=> "France","GF"=> "French Guiana","PF"=> "French Polynesia","GA"=> "Gabon Republic","GM"=> "Gambia","DE"=> "Germany","GI"=> "Gibraltar","GR"=> "Greece","GL"=> "Greenland","GD"=> "Grenada","GP"=> "Guadeloupe","GT"=> "Guatemala","GN"=> "Guinea","GW"=> "Guinea Bissau","GY"=> "Guyana","HN"=> "Honduras","HK"=> "Hong Kong","HU"=> "Hungary","IS"=> "Iceland","IN"=> "India","ID"=> "Indonesia","IE"=> "Ireland","IL"=> "Israel","IT"=> "Italy","JM"=> "Jamaica","JP"=> "Japan","JO"=> "Jordan","KZ"=> "Kazakhstan","KE"=> "Kenya","KI"=> "Kiribati","KW"=> "Kuwait","KG"=> "Kyrgyzstan","LA"=> "Laos","LV"=> "Latvia","LS"=> "Lesotho","LI"=> "Liechtenstein","LT"=> "Lithuania","LU"=> "Luxembourg","MG"=> "Madagascar","MW"=> "Malawi","MY"=> "Malaysia","MV"=> "Maldives","ML"=> "Mali","MT"=> "Malta","MH"=> "Marshall Islands","MQ"=> "Martinique","MR"=> "Mauritania","MU"=> "Mauritius","YT"=> "Mayotte","MX"=> "Mexico","MN"=> "Mongolia","MS"=> "Montserrat","MA"=> "Morocco","MZ"=> "Mozambique","NA"=> "Namibia","NR"=> "Nauru","NP"=> "Nepal","NL"=> "Netherlands","AN"=> "Netherlands Antilles","NC"=> "New Caledonia","NZ"=> "New Zealand","NI"=> "Nicaragua","NE"=> "Niger","NU"=> "Niue","NF"=> "Norfolk Island","NO"=> "Norway","OM"=> "Oman","PW"=> "Palau","PA"=> "Panama","PG"=> "Papua New Guinea","PE"=> "Peru","PH"=> "Philippines","PN"=> "Pitcairn Islands","PL"=> "Poland","PT"=> "Portugal","QA"=> "Qatar","CG"=> "Republic of the Congo","RE"=> "Reunion","RO"=> "Romania","RU"=> "Russia","RW"=> "Rwanda","VC"=> "Saint Vincent and the Grenadines","WS"=> "Samoa","SM"=> "San Marino","ST"=> "São Tomé and Príncipe","SA"=> "Saudi Arabia","SN"=> "Senegal","SC"=> "Seychelles","SL"=> "Sierra Leone","SG"=> "Singapore","SK"=> "Slovakia","SI"=> "Slovenia","SB"=> "Solomon Islands","SO"=> "Somalia","ZA"=> "South Africa","KR"=> "South Korea","ES"=> "Spain","LK"=> "Sri Lanka","SH"=> "St. Helena","KN"=> "St. Kitts and Nevis","LC"=> "St. Lucia","PM"=> "St. Pierre and Miquelon","SR"=> "Suriname","SJ"=> "Svalbard and Jan Mayen Islands","SZ"=> "Swaziland","SE"=> "Sweden","CH"=> "Switzerland","TW"=> "Taiwan","TJ"=> "Tajikistan","TZ"=> "Tanzania","TH"=> "Thailand","TG"=> "Togo","TO"=> "Tonga","TT"=> "Trinidad and Tobago","TN"=> "Tunisia","TR"=> "Turkey","TM"=> "Turkmenistan","TC"=> "Turks and Caicos Islands","TV"=> "Tuvalu","UG"=> "Uganda","UA"=> "Ukraine","AE"=> "United Arab Emirates","GB"=> "United Kingdom","UY"=> "Uruguay","VU"=> "Vanuatu","VA"=> "Vatican City State","VE"=> "Venezuela","VN"=> "Vietnam","WF"=> "Wallis and Futuna Islands","YE"=> "Yemen","ZM"=> "Zambia");
}


function wp_invoice_go_secure($destination) {
    $reload = 'Location: ' . $destination;
    header($reload);
} 



function wp_invoice_process_cc_transaction($cc_data) {



$errors = array ();
$errors_msg = null;
$_POST['processing_problem'] = '';
unset($stop_transaction);
$invoice_id = preg_replace("/[^0-9]/","", $_POST['invoice_num']); /* this is the real invoice id */

if(wp_invoice_recurring($invoice_id)) $recurring = true;

$invoice = new WP_Invoice_GetInfo($invoice_id);



// Accomodate Custom Invoice IDs by changing the post value, this is passed to Authorize.net account
$wp_invoice_custom_invoice_id = wp_invoice_meta($invoice_id,'wp_invoice_custom_invoice_id');
// If there is a custom invoice id, we're setting the $_POST['invoice_num'] to the custom id, because that is what's getting passed to authorize.net
if($wp_invoice_custom_invoice_id) { $_POST['invoice_num'] = $wp_invoice_custom_invoice_id; }
 
$wp_users_id = get_invoice_user_id($invoice_id);

if(empty($_POST['first_name'])){$errors [ 'first_name' ] [] = "Please enter your first name.\n";$stop_transaction = true;}
if(empty($_POST['last_name'])){$errors [ 'last_name' ] [] = "Please enter your last name. \n";$stop_transaction = true;}
if(empty($_POST['email_address'])){$errors [ 'email_address' ] [] = "Please provide an email address.";$stop_transaction = true;}
if(empty($_POST['phonenumber'])){$errors [ 'phonenumber' ] [] = "Please enter your phone number.\n";$stop_transaction = true;}
if(empty($_POST['address'])){$errors [ 'address' ] [] = "Please enter your address.\n";$stop_transaction = true;}
if(empty($_POST['city'])){$errors [ 'city' ] [] = "Please enter your city.\n";$stop_transaction = true;}
if(empty($_POST['state'])){$errors [ 'state' ] [] = "Please select your state.\n";$stop_transaction = true;}
if(empty($_POST['zip'])){$errors [ 'zip' ] [] = "Please enter your ZIP code.\n";$stop_transaction = true;}
if(empty($_POST['country'])){$errors [ 'country' ] [] = "Please enter your country.";$stop_transaction = true;}
if(empty($_POST['card_num'])) {	$errors [ 'card_num' ] []  = "Please enter your credit card number.\n";	$stop_transaction = true;} else { if (!wp_invoice_validate_cc_number($_POST['card_num'])){$errors [ 'card_num' ] [] = "Please enter a valid credit card number.\n"; $stop_transaction = true; } }
if(empty($_POST['exp_month'])){$errors [ 'exp_month' ] [] = "Please enter your credit card's expiration month.";$stop_transaction = true;}
if(empty($_POST['exp_year'])){$errors [ 'exp_year' ] [] = "Please enter your credit card's expiration year.";$stop_transaction = true;}
if(empty($_POST['card_code'])){$errors [ 'card_code' ] [] = "The <b>Security Code</b> is the code on the back of your card.";$stop_transaction = true;}

// Charge Card
if(!$stop_transaction) {

	require_once('gateways/authnet.class.php');
	require_once('gateways/authnetARB.class.php');

	$payment = new WP_Invoice_Authnet(true); 
	$payment->transaction($_POST['card_num']); 
	
	// Billing Info
	$payment->setParameter("x_card_code", $_POST['card_code']);
	$payment->setParameter("x_exp_date ", $_POST['exp_month'] . $_POST['exp_year']);
	$payment->setParameter("x_amount", $invoice->display('amount'));
	if($recurring) $payment->setParameter("x_recurring_billing", true);
	
	// Order Info
	$payment->setParameter("x_description", $invoice->display('subject'));
	$payment->setParameter("x_invoice_num",  $invoice->display('display_id'));
	$payment->setParameter("x_test_request", false);
	$payment->setParameter("x_duplicate_window", 30);
	
	//Customer Info
	$payment->setParameter("x_first_name", $_POST['first_name']);
	$payment->setParameter("x_last_name", $_POST['last_name']);
	$payment->setParameter("x_address", $_POST['address']);
	$payment->setParameter("x_city", $_POST['city']);
	$payment->setParameter("x_state", $_POST['state']);
	$payment->setParameter("x_country", $_POST['country']);
	$payment->setParameter("x_zip", $_POST['zip']);
	$payment->setParameter("x_phone", $_POST['phonenumber']);
	$payment->setParameter("x_email", $_POST['email_address']);
	$payment->setParameter("x_cust_id", "WP User - " . $invoice->recipient('user_id'));
	$payment->setParameter("x_customer_ip ", $_SERVER['REMOTE_ADDR']);
	
	$payment->process(); 
 
	if($payment->isApproved()) {
	echo "Transaction okay.";

	update_usermeta($wp_users_id,'last_name',$_POST['last_name']);
	update_usermeta($wp_users_id,'last_name',$_POST['last_name']);
	update_usermeta($wp_users_id,'first_name',$_POST['first_name']);
	update_usermeta($wp_users_id,'city',$_POST['city']);
	update_usermeta($wp_users_id,'state',$_POST['state']);
	update_usermeta($wp_users_id,'zip',$_POST['zip']);
	update_usermeta($wp_users_id,'streetaddress',$_POST['address']);
	update_usermeta($wp_users_id,'phonenumber',$_POST['phonenumber']);

	//Mark invoice as paid
	wp_invoice_paid($invoice_id);
	if(get_option('wp_invoice_send_thank_you_email') == 'yes') wp_invoice_send_email_reciept($invoice_id);

	if($recurring) {
   
		$arb = new WP_Invoice_AuthnetARB(); 
		// Customer Info
		$arb->setParameter('customerId', "WP User - " . $invoice->recipient('user_id')); 
		$arb->setParameter('firstName', $_POST['first_name']); 
		$arb->setParameter('lastName', $_POST['last_name']); 
		$arb->setParameter('address', $_POST['address']); 
		$arb->setParameter('city', $_POST['city']); 
		$arb->setParameter('state', $_POST['state']); 
		$arb->setParameter('zip', $_POST['zip']); 
		$arb->setParameter('country', $_POST['country']); 
		$arb->setParameter('customerEmail', $_POST['email_address']); 
		$arb->setParameter('customerPhoneNumber', $_POST['phonenumber']); 
		
		// Billing Info
		$arb->setParameter('amount', $invoice->display('amount')); 
		$arb->setParameter('cardNumber', $_POST['card_num']); 
		$arb->setParameter('expirationDate', $_POST['exp_month'].$_POST['exp_year']); 
		
		//Subscription Info
		$arb->setParameter('refID',  $invoice->display('display_id')); 
		$arb->setParameter('subscrName', $invoice->display('subscription_name')); 
		$arb->setParameter('interval_length', $invoice->display('interval_length')); 
		$arb->setParameter('interval_unit', $invoice->display('interval_unit')); 
		$arb->setParameter('startDate', $invoice->display('startDate')); 
		$arb->setParameter('totalOccurrences', $invoice->display('totalOccurrences')); 
		
		// First billing cycle is taken care off with initial payment
		$arb->setParameter('trialOccurrences', '1'); 
		$arb->setParameter('trialAmount', '0.00'); 
		
		$arb->setParameter('orderInvoiceNumber',  $invoice->display('display_id')); 
		$arb->setParameter('orderDescription', $invoice->display('subject')); 
		
		$arb->createAccount();
		
		if ($arb->isSuccessful()) { 
		wp_invoice_update_invoice_meta($invoice_id, 'subscription_id',$arb->getSubscriberID());
		wp_invoice_update_log($invoice_id, 'subscription', ' Subscription initiated, Subcription ID - ' . $arb->getSubscriberID());
		}
		
		if($arb->isError()) {
		$errors [ 'processing_problem' ] [] .=  "One-time credit card payment is processed successfully.  However, recurring billing setup failed." . $arb->getResponse(); $stop_transaction = true;;
		wp_invoice_update_log($invoice_id, 'subscription_error', 'Response Code: ' . $arb->getResponseCode() . ' | Subscription error - ' . $arb->getResponse());

		}
		
	}
   

 } else {
$errors [ 'processing_problem' ] [] .= $payment->getResponseText();$stop_transaction = true;

 }
// Uncomment these to troubleshoot.  You will need FireBug to view the response of the AJAX post. 
//echo $arb->xml;
//echo $arb->response;
//echo $arb->getResponse();

//echo $payment->getResponseText();
//echo $payment->getTransactionID();
//echo $payment->getAVSResponse();
//echo $payment->getAuthCode();



}


if ($stop_transaction && is_array($_POST))
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

function wp_invoice_currency_array() {
	$currency_list = array(
	"AUD"=> "Australian Dollars",
	"CAD"=> "Canadian Dollars",
	"EUR"=> "Euros",
	"GBP"=> "Pounds Sterling",
	"JPY"=> "Yen",
	"USD"=> "U.S. Dollars",
	"NZD"=> "New Zealand Dollar",
	"CHF"=> "Swiss Franc",
	"HKD"=> "Hong Kong Dollar",
	"SGD"=> "Singapore Dollar",
	"SEK"=> "Swedish Krona",
	"DKK"=> "Danish Krone",
	"PLN"=> "Polish Zloty",
	"NOK"=> "Norwegian Krone",
	"HUF"=> "Hungarian Forint",
	"CZK"=> "Czech Koruna",
	"ILS"=> "Israeli Shekel",
	"MXN"=> "Mexican Peso");
	
	return $currency_list;
}

function wp_invoice_currency_symbol($currency = "USD" )
{
	$currency_list = array(
	'CAD'=> '$',
	'EUR'=> '&#8364;',
	'GBP'=> '&pound;',
	'JPY'=> '&yen;',
	'USD'=> '$');


foreach($currency_list as $value => $display)
{
    if($currency == $value) { return $display; $success = true; break;}
}
if(!$success) return $currency;
	
	
	
}




function wp_invoice_contextual_help_list($content) {
// Will add help and FAQ here eventually
return $content;
}

function wp_invoice_process_invoice_update($invoice_id) {

	global $wpdb;
	$profileuser = get_user_to_edit($_POST['user_id']);
	$description = $_REQUEST['description'];
	$subject = $_REQUEST['subject'];
	$amount = $_REQUEST['amount'];
	$user_id = $_REQUEST['user_id'];
	$wp_invoice_tax = $_REQUEST['wp_invoice_tax'];
	$itemized_list = $_REQUEST['itemized_list'];
	$wp_invoice_custom_invoice_id = $_REQUEST['wp_invoice_custom_invoice_id'];
	$wp_invoice_due_date_month = $_REQUEST['wp_invoice_due_date_month'];
	$wp_invoice_due_date_day = $_REQUEST['wp_invoice_due_date_day'];
	$wp_invoice_due_date_year = $_REQUEST['wp_invoice_due_date_year'];

	$wp_invoice_first_name = $_REQUEST['wp_invoice_first_name'];
	$wp_invoice_last_name = $_REQUEST['wp_invoice_last_name'];
	$wp_invoice_streetaddress = $_REQUEST['wp_invoice_streetaddress'];
	$wp_invoice_city = $_REQUEST['wp_invoice_city'];
	$wp_invoice_state = $_REQUEST['wp_invoice_state'];
	$wp_invoice_zip = $_REQUEST['wp_invoice_zip'];
	
	$wp_invoice_currency_code = $_REQUEST['wp_invoice_currency_code'];
	
	$wp_invoice_subscription_name = $_REQUEST['wp_invoice_subscription_name'];
	$wp_invoice_subscription_unit = $_REQUEST['wp_invoice_subscription_unit'];
	$wp_invoice_subscription_length = $_REQUEST['wp_invoice_subscription_length'];
	$wp_invoice_subscription_start_month = $_REQUEST['wp_invoice_subscription_start_month'];
	$wp_invoice_subscription_start_day = $_REQUEST['wp_invoice_subscription_start_day'];
	$wp_invoice_subscription_start_year = $_REQUEST['wp_invoice_subscription_start_year'];
	$wp_invoice_subscription_total_occurances = $_REQUEST['wp_invoice_subscription_total_occurances'];

	
	//remove items from itemized list that are missing a title, they are most likely deleted
	if(is_array($itemized_list)) {
		$counter = 1;
		foreach($itemized_list as $itemized_item){
			if(empty($itemized_item[name])) {
				unset($itemized_list[$counter]); 
			}
		$counter++;
		}
	array_values($itemized_list);
	}
	$itemized = urlencode(serialize($itemized_list));

	
	// Check if this is new invoice creation, or an update

	if(wp_invoice_does_invoice_exist($invoice_id)) {
		// Updating Old Invoice

		if(wp_invoice_get_invoice_attrib($invoice_id,'subject') != $subject) { $wpdb->query("UPDATE ".WP_Invoice::tablename('main')." SET subject = '$subject' WHERE invoice_num = $invoice_id"); 			wp_invoice_update_log($invoice_id, 'updated', ' Subject Updated '); $message .= "Subject updated. ";}
		if(wp_invoice_get_invoice_attrib($invoice_id,'description') != $description) { $wpdb->query("UPDATE ".WP_Invoice::tablename('main')." SET description = '$description' WHERE invoice_num = $invoice_id"); 			wp_invoice_update_log($invoice_id, 'updated', ' Description Updated '); $message .= "Description updated. ";}
		if(wp_invoice_get_invoice_attrib($invoice_id,'amount') != $amount) { $wpdb->query("UPDATE ".WP_Invoice::tablename('main')." SET amount = '$amount' WHERE invoice_num = $invoice_id"); 			wp_invoice_update_log($invoice_id, 'updated', ' Amount Updated '); $message .= "Amount updated. ";}
		if(wp_invoice_get_invoice_attrib($invoice_id,'itemized') != $itemized) { $wpdb->query("UPDATE ".WP_Invoice::tablename('main')." SET itemized = '$itemized' WHERE invoice_num = $invoice_id"); 			wp_invoice_update_log($invoice_id, 'updated', ' Itemized List Updated '); $message .= "Itemized List updated. ";}
	}
	else {
		// Create New Invoice

		if($wpdb->query("INSERT INTO ".WP_Invoice::tablename('main')." (amount,description,invoice_num,user_id,subject,itemized,status)	VALUES ('$amount','$description','$invoice_id','$user_id','$subject','$itemized','0')")) {
			$message = "New Invoice saved.";
			wp_invoice_update_log($invoice_id, 'created', ' Created ');;
		} 
		else { 
			$error = true; $message = "There was a problem saving invoice.  Try deactivating and reactivating plugin."; 
		}
	}
		
	// See if invoice is recurring
	if(!empty($wp_invoice_subscription_name) &&	!empty($wp_invoice_subscription_unit) && !empty($wp_invoice_subscription_total_occurances)) {
		$wp_invoice_recurring_status = true;
		wp_invoice_update_invoice_meta($invoice_id, "recurring_billing", true);
		$message .= " Recurring invoice saved.  This invoice may be viewed under \"Recurring Billing\". ";
		
	}
	
	// See if invoice is recurring
	if(empty($wp_invoice_subscription_name) &&	empty($wp_invoice_subscription_unit) && empty($wp_invoice_subscription_total_occurances)) {
		$wp_invoice_recurring_status = false;
		wp_invoice_update_invoice_meta($invoice_id, "recurring_billing", false);
	
		
	}
		
			
	// Update Invoice Meta
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_custom_invoice_id", $wp_invoice_custom_invoice_id);			
	wp_invoice_update_invoice_meta($invoice_id, "tax_value", $wp_invoice_tax);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_currency_code", $wp_invoice_currency_code);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_due_date_day", $wp_invoice_due_date_day);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_due_date_month", $wp_invoice_due_date_month);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_due_date_year", $wp_invoice_due_date_year);		
	
	// Update Invoice Recurring Meta
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_name", $wp_invoice_subscription_name);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_unit", $wp_invoice_subscription_unit);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_length", $wp_invoice_subscription_length);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_start_month", $wp_invoice_subscription_start_month);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_start_day", $wp_invoice_subscription_start_day);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_start_year", $wp_invoice_subscription_start_year);
	wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_subscription_total_occurances", $wp_invoice_subscription_total_occurances);

	//Update User Information
	if(!empty($wp_invoice_first_name)) update_usermeta($user_id, 'first_name', $wp_invoice_first_name);
	if(!empty($wp_invoice_last_name)) update_usermeta($user_id, 'last_name', $wp_invoice_last_name);
	if(!empty($wp_invoice_streetaddress)) update_usermeta($user_id, 'streetaddress', $wp_invoice_streetaddress);
	if(!empty($wp_invoice_city)) update_usermeta($user_id, 'city', $wp_invoice_city);
	if(!empty($wp_invoice_state)) update_usermeta($user_id, 'state', $wp_invoice_state);
	if(!empty($wp_invoice_zip)) update_usermeta($user_id, 'zip', $wp_invoice_zip);
		
	//If there is a message, append it with the web invoice link
	if($message && $invoice_id) {
	$invoice_info = new WP_Invoice_GetInfo($invoice_id); 
	$message .= " <a href='".$invoice_info->display('link')."'>View Web Invoice</a>.";
	}
	
	
	if(!$error) return $message;
	if($error) return "An error occured: $message.";
	


}

function wp_invoice_show_message($content,$type="updated fade") {
if($content) echo "<div id=\"message\" class='$type' ><p>".$content."</p></div>";
}



function wp_invoice_process_settings() {
	global $wpdb;

	// Save General Settings
	if(isset($_POST['wp_invoice_business_name'])) { update_option('wp_invoice_business_name', $_POST['wp_invoice_business_name']); }
	if(isset($_POST['wp_invoice_business_phone'])) update_option('wp_invoice_business_phone', $_POST['wp_invoice_business_phone']);
	if(isset($_POST['wp_invoice_business_address'])) update_option('wp_invoice_business_address', $_POST['wp_invoice_business_address']);
	if(isset($_POST['wp_invoice_default_currency_code'])) update_option('wp_invoice_default_currency_code', $_POST['wp_invoice_default_currency_code']);
	if(isset($_POST['wp_invoice_using_godaddy'])) update_option('wp_invoice_using_godaddy', $_POST['wp_invoice_using_godaddy']);
	if(isset($_POST['wp_invoice_email_address'])) update_option('wp_invoice_email_address', $_POST['wp_invoice_email_address']);
	if(isset($_POST['wp_invoice_force_https'])) update_option('wp_invoice_force_https', $_POST['wp_invoice_force_https']);
	if(isset($_POST['wp_invoice_paypal_address'])) update_option('wp_invoice_paypal_address', $_POST['wp_invoice_paypal_address']);
	if(isset($_POST['wp_invoice_payment_link'])) update_option('wp_invoice_payment_link', $_POST['wp_invoice_payment_link']);
	if(isset($_POST['wp_invoice_payment_method'])) update_option('wp_invoice_payment_method', $_POST['wp_invoice_payment_method']);
	if(isset($_POST['wp_invoice_protocol'])) update_option('wp_invoice_protocol', $_POST['wp_invoice_protocol']);
	if(isset($_POST['wp_invoice_send_thank_you_email'])) update_option('wp_invoice_send_thank_you_email', $_POST['wp_invoice_send_thank_you_email']);
	if(isset($_POST['wp_invoice_show_business_address'])) update_option('wp_invoice_show_business_address', $_POST['wp_invoice_show_business_address']);
	if(isset($_POST['wp_invoice_show_quantities'])) update_option('wp_invoice_show_quantities', $_POST['wp_invoice_show_quantities']);
	if(isset($_POST['wp_invoice_use_css'])) update_option('wp_invoice_use_css', $_POST['wp_invoice_use_css']);
	if(isset($_POST['wp_invoice_user_level'])) update_option('wp_invoice_user_level', $_POST['wp_invoice_user_level']);
	if(isset($_POST['wp_invoice_web_invoice_page'])) update_option('wp_invoice_web_invoice_page', $_POST['wp_invoice_web_invoice_page']);

	if(isset($_POST['wp_invoice_business_name']) || $_POST['wp_invoice_business_address']|| $_POST['wp_invoice_email_address'] || isset($_POST['wp_invoice_business_phone']) || isset($_POST['wp_invoice_payment_link'])) $message = "Information saved.";

	// Save Gateway Settings
	if(isset($_POST['wp_invoice_recurring_gateway_url'])) update_option('wp_invoice_recurring_gateway_url', $_POST['wp_invoice_recurring_gateway_url']);
	if(isset($_POST['wp_invoice_gateway_url'])) update_option('wp_invoice_gateway_url', $_POST['wp_invoice_gateway_url']);
	if(isset($_POST['wp_invoice_gateway_username'])) update_option('wp_invoice_gateway_username', $_POST['wp_invoice_gateway_username']);
	if(isset($_POST['wp_invoice_gateway_tran_key'])) update_option('wp_invoice_gateway_tran_key', $_POST['wp_invoice_gateway_tran_key']);
	if(isset($_POST['wp_invoice_gateway_merchant_email'])) update_option('wp_invoice_gateway_merchant_email', $_POST['wp_invoice_gateway_merchant_email']);
	if(isset($_POST['wp_invoice_gateway_delim_data'])) update_option('wp_invoice_gateway_delim_data', $_POST['wp_invoice_gateway_delim_data']);
	if(isset($_POST['wp_invoice_gateway_delim_char'])) update_option('wp_invoice_gateway_delim_char', $_POST['wp_invoice_gateway_delim_char']);
	if(isset($_POST['wp_invoice_gateway_encap_char'])) update_option('wp_invoice_gateway_encap_char', $_POST['wp_invoice_gateway_encap_char']);
	if(isset($_POST['wp_invoice_gateway_header_email_receipt'])) update_option('wp_invoice_gateway_header_email_receipt', $_POST['wp_invoice_gateway_header_email_receipt']);
	if(isset($_POST['wp_invoice_gateway_MD5Hash'])) update_option('wp_invoice_gateway_MD5Hash', $_POST['wp_invoice_gateway_MD5Hash']);
	if(isset($_POST['wp_invoice_gateway_test_mode'])) update_option('wp_invoice_gateway_test_mode', $_POST['wp_invoice_gateway_test_mode']);
	if(isset($_POST['wp_invoice_gateway_relay_response'])) update_option('wp_invoice_gateway_relay_response', $_POST['wp_invoice_gateway_relay_response']);
	if(isset($_POST['wp_invoice_gateway_email_customer'])) update_option('wp_invoice_gateway_email_customer', $_POST['wp_invoice_gateway_email_customer']);


}

function wp_invoice_is_not_merchant() {
	if(get_option('wp_invoice_gateway_username') == '' || get_option('wp_invoice_gateway_tran_key') == '') return true;
}


function wp_invoice_determine_currency($invoice_id) {
	//in class
	if(wp_invoice_meta($invoice_id,'wp_invoice_currency_code') != '')
		{ $currency_code = wp_invoice_meta($invoice_id,'wp_invoice_currency_code'); }
		elseif(get_option('wp_invoice_default_currency_code') != '')
		{ $currency_code = get_option('wp_invoice_default_currency_code'); }
		else { $currency_code = "USD"; }
		return $currency_code;
}

function wp_invoice_md5_to_invoice($md5) {
	global $wpdb;
	$all_invoices = $wpdb->get_col("SELECT invoice_num FROM ".WP_Invoice::tablename('main')." ");
	foreach ($all_invoices as $value) { if(md5($value) == $md5) return $value; }
}

function wp_invoice_create_paypal_itemized_list($itemized_array,$invoice_id) {

	$output = '<input type="hidden" name="redirect_cmd" value="_cart">';

	$tax_free_sum = 0;
	$counter = 1;
	foreach($itemized_array as $itemized_item) {

		// If we have a negative item, PayPal will not accept, we must group everything into one amount
		if($itemized_item[price] * $itemized_item[quantity] < 0) {

		unset($output);
		unset($tax);

		// In case this isn't the first loop, unset anything we've done so far
		$output = "<input type='hidden' name='item_name_1' value='Reference Invoice #$invoice_id'> \n
		<input type='hidden' name='amount_1' value='$amount'>\n";

		break;
		}

		$output .= "<input type='hidden' name='item_name_$counter' value='".$itemized_item[name]."'>\n";
		$output .= "<input type='hidden' name='amount_$counter' value='".$itemized_item[price] * $itemized_item[quantity]."'>\n";

		$tax_free_sum = $tax_free_sum + $itemized_item[price] * $itemized_item[quantity];
		$counter++;
	}

	// Add tax onnly by using tax_free_sum (which is the sums of all the individual items * quantities. 
	if(!empty($tax)) { 
	$tax_cart = round($tax_free_sum * ($tax / 100),2);
	$output .= "<input type='hidden' name='tax_cart' value='". $tax_cart ."'>";	}

	return $output;
}
?>