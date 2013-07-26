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

		
function wp_invoice_default()
{
	global $wpdb;
	//Make sure tables exist
	
	

	$wp_invoice_web_invoice_page = get_option("wp_invoice_web_invoice_page");
	$wp_invoice_paypal_address = get_option("wp_invoice_paypal_address");
	if(empty($wp_invoice_web_invoice_page)) { $warning_message .= "Incoming Web Invoice link is not set. "; }
	if(empty($wp_invoice_paypal_address)) { $warning_message .= "PayPal address is not set. "; }
	if(empty($wp_invoice_paypal_address) || empty($wp_invoice_web_invoice_page)) { $warning_message .= "Please visit the <a href='admin.php?page=invoice_settings'>Settings page</a>."; }
	
	// The error takes precedence over others being that nothing can be done w/o tables
	if(!$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_MAIN."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_LOG."';")) { $warning_message = ""; }
	
	if($warning_message) echo "<div id=\"message\" class='error' ><p>$warning_message</p></div>";
	if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";
	
	$all_invoices = $wpdb->get_results("SELECT * FROM ".WP_INVOICE_TABLE_MAIN);

	if(wp_invoice_number_of_invoices())
		{
		
?>
	
	
	
	<div class="wrap">
	
	<form id="invoices-filter" action="" method="post" >
	<h2>Invoices Overview</h2>
	
	<div class="tablenav clearfix">
	
	<div class="alignleft">
	<select name="action">
		<option value="-1" selected="selected">Bulk Actions</option>
		<option value="Send Invoice" name="sendit" >Send Invoice(s)</option>
		<option  value="Delete" name="deleteit" >Delete</option>
	</select>
	<input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action" />
	</div>

	<div class="alignright">
		<ul class="subsubsub" style="margin:0;">
		<li>Filter:</li>
		<li><a href='#' class="" id="">All Invoices</a> |</li>
		<li><a href='#'  class="paid" id="">Paid</a> |</li>
		<li><a href='#'  class="viewed" id="">Viewed</a> |</li>
		<li>Custom: <input type="text" id="FilterTextBox" class="search-input" name="FilterTextBox" /> </li>
		</ul>
	</div>
	</div>
	<br class="clear" />


	
	<table class="widefat" id="invoice_sorter_table">
	<thead>
	<tr>
		<th class="check-column"><input type="checkbox" id="CheckAll" /></th>
		<th class="invoice_id_col">Invoice Id</th>
		<th>Subject</th>
		<th>Amount</th>
		<th>Status</th>
		<th>User</th>
		<th></th>
	</tr>
	</thead>
	
	<?php
	$wp_invoice_payment_link = get_option("wp_invoice_payment_link");
	if(!empty($wp_invoice_payment_link)) { if(strpos('?',$wp_invoice_payment_link)) { $wp_invoice_payment_link = $wp_invoice_payment_link . "&";} else {$wp_invoice_payment_link = $wp_invoice_payment_link . "?";} }

	foreach ($all_invoices as $invoice) {
		$profileuser = get_user_to_edit($invoice->user_id);
		echo "<tr "; if(wp_invoice_paid_status($invoice->invoice_num)) {echo " class='alternate' "; } echo ">";	
		echo "<th class=\"check-column\"><input type=\"checkbox\" name=\"multiple_invoices[]\" value=\"$invoice->invoice_num\"  /></td>
		<td><a href=\"admin.php?page=new_invoice&tctiaction=editInvoice&invoice_id=".$invoice->invoice_num."\">". $invoice->invoice_num ."</a></td>
		<td><a href=\"admin.php?page=new_invoice&tctiaction=editInvoice&invoice_id=".$invoice->invoice_num."\">". $invoice->subject ."</a></td>
		<td>$". $invoice->amount ."</td>
		<td>";
		
		if(wp_invoice_paid_status($invoice->invoice_num)) { echo "Paid on " . wp_invoice_Date::convert(wp_invoice_paid_status($invoice->invoice_num), 'Y-m-d H', 'M d Y'); } else 
		{ echo wp_invoice_get_single_invoice_status($invoice->invoice_num); } 
		echo "</td>
		<td> <a href=\"user-edit.php?user_id=" . $invoice->user_id . "\">". $profileuser->first_name ." ". $profileuser->last_name. "</a></td>
		<td><a href=\"" . wp_invoice_build_invoice_link($invoice->invoice_num)."\">Web Invoice</a></td></tr>"
		; }
		echo "</table></form></div>";
	
	}

	wp_invoice_options_manageInvoice();
	wp_invoice_donate_button();

}

function wp_invoice_options_saveandpreview()
{ 
	global $wpdb;
	
	$profileuser = get_user_to_edit($_POST['user_id']);
	$new_invoice_id = $_REQUEST['new_invoice_id'];
	$description = $_REQUEST['description'];
	$subject = $_REQUEST['subject'];
	$amount = $_REQUEST['amount'];
	$user_id = $_REQUEST['user_id'];
	$itemized_array = $_REQUEST[itemized_list];
	
	//remove items from itemized list that are missing a title, they are most likely deleted
	if(is_array($itemized_array)) {
		$counter = 1;
		foreach($itemized_array as $itemized_item){
			if(empty($itemized_item[name])) {
				unset($itemized_array[$counter]); 
			}
		$counter++;
		}
	array_values($itemized_array);
	}

	
	$itemized = urlencode(serialize($itemized_array));
	
	if(isset($_REQUEST['new_invoice_id']))
	{
		$invoice_exists = $wpdb->get_var("SELECT COUNT(*) FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num=".$_REQUEST['new_invoice_id'].";");
		if($invoice_exists > 0)
		{ 
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'subject') != $subject) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET subject = '$subject' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Subject Updated '); $message .= "Subject updated. ";}
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'description') != $description) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET description = '$description' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Description Updated '); $message .= "Description updated. ";}
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'amount') != $amount) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET amount = '$amount' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Amount Updated '); $message .= "Amount updated.";}
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'itemized') != $itemized) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET itemized = '$itemized' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Itemized List Updated '); $message .= "Itemized List updated.";}

		}
		else
		{
			if($wpdb->query("INSERT INTO ".WP_INVOICE_TABLE_MAIN."
			(id,amount,description,invoice_num,user_id,subject,itemized,status)
			VALUES ('','$amount','$description','$new_invoice_id','$user_id','$subject','$itemized','0')")) 
			{
			$message = "New Invoice saved.";
			wp_invoice_update_log($new_invoice_id, 'created', ' Created ');
			} else 	{ $message = "There was a problem saving invoice.  Try deactivating and reactivating plugin."; }
		}
	}
	else
	{
	$message = "An error has occured. Did you tried refreshing the page? Tech note: new_invoice_id was not passed.";
	}

if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";	
//print_r($_REQUEST);?>

<div class="wrap">
	<h2>Save and Preview</h2>

	<p>This is what your invoice will appear like in the email message. The version on your website will have the itemized list as well.</p>

	<div id="invoice_preview">
	<?php wp_invoice_show_invoice($new_invoice_id); ?>
	</div>

	<div class="invoice_horizontal_buttons">
		<form method="post" action="admin.php?page=wp-invoice/invoice_plugin.php">
		<input type="hidden" value="<?php echo $new_invoice_id; ?>" name="invoice_id" >
		<input type="submit" value="Continue Editing" name="modify" class="button-secondary" />
		<input type="submit" value="Save for Later" name="save" class="button-secondary" />
		<input type="submit" value="Send Now" name="send_now" class="button-secondary" />
		</form>
	</div>
	Do not use the back button or you could have duplicates.
</div>
<?php

}


function wp_invoice_options_manageInvoice($invoice_id = '')
{
	global $wpdb;
	if(isset($_REQUEST['user_id'])) $user_id = $_REQUEST['user_id'];

	if($invoice_id == '') {unset($invoice_id);}
	
	if(isset($invoice_id)) {
	// Invoice Exists, we are modifying it
	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
	$user_id = $invoice_info->user_id;
	$amount = $invoice_info->amount;
	$subject = $invoice_info->subject;
	$description = $invoice_info->description;
	$itemized = $invoice_info->itemized;
	$profileuser = get_user_to_edit($invoice_info->user_id);
	$itemized_array = unserialize(urldecode($itemized)); 
	
	}

	if(count($itemized_array) == 0) {
	$itemized_array[1] = "";
	$itemized_array[2] = "";	
	}
if(!$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_MAIN."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_LOG."';")) { $warning_message = "The plugin database tables are gone, deactivate and reactivate plugin to re-create them."; }
if($warning_message) echo "<div id=\"message\" class='error' ><p>$warning_message</p></div>";


	?>
	
	<div class="wrap">

	<?php if(!isset($invoice_id)) { ?> <h2>New Invoice</h2><?php  wp_invoice_draw_user_selection_form($user_id); } ?>
	<?php if(isset($user_id) && isset($invoice_id)) { ?><h2>Edit Invoice</h2><?php } ?>

	<?php if(isset($user_id)) { ?>
	<form id='new_invoice_form' action="admin.php?page=new_invoice&tctiaction=save_and_preview" method='POST'>

	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
	<input type="hidden" name="new_invoice_id" value="<?php if(isset($invoice_id)) { echo $invoice_id; } else { echo rand(10000000, 90000000);}  ?>">
	<input type="hidden" name="amount" id="total_amount" value="<?php echo $amount; ?>">

	<table class="form-table" id="add_new_invoice">
	
	<?php
	if(get_option('wp_invoice_business_name') == '') 		echo "<tr><th colspan=\"2\">Your business name isn't set, go to Settings page to set it.</a></th></tr>\n";

	if(isset($_POST['user_id'])) {							$profileuser = get_user_to_edit($_POST['user_id']); }
	
	?> 
	
	<tr><th><?php _e("Email Address") ?></th><td><?php echo $profileuser->user_email; ?></td>
	<tr><th><?php _e("Billing Information") ?></th>
	<td><?php if(!empty($profileuser->first_name) || !empty($profileuser->last_name)) { echo "<a href=\"user-edit.php?user_id=" . $profileuser->ID . "\">" . $profileuser->first_name . " " . $profileuser->last_name . "</a><br />"; }
	if(isset($profileuser->streetaddress)) echo $profileuser->streetaddress . "<br />";
	if(isset($profileuser->city)) echo $profileuser->city . "" ;
	if(isset($profileuser->state)) echo " " . $profileuser->state;
	if(isset($profileuser->zip)) echo " " . $profileuser->zip . "<br />";
	if(empty($profileuser->first_name) || empty($profileuser->last_name) || empty($profileuser->streetaddress) || empty($profileuser->city) ||  empty($profileuser->state) ||  empty($profileuser->zip))  {
	echo "<span class=\"error\"><a style='text-decoration:none;' href=\"user-edit.php?user_id=" . $profileuser->ID . "\">Visit user's profile to prefill billing information.</a></span>";
	}
?>
	</td>
	
	<tr><th>Invoice ID </th><td style="font-size: 1.1em; padding-top:7px;"><?php if(isset($invoice_id)) { echo $invoice_id; } else { echo rand(10000000, 90000000);}  ?></td></tr>
	<tr class="invoice_main"><th>Subject</th><td><input  id="invoice_subject" class="subject"  name='subject' value='<?php echo $subject; ?>'></input></td></tr>
	<tr class="invoice_main"><th>Description </th><td><textarea class="invoice_description_box" name='description' value=''><?php echo $description; ?></textarea></td></tr>
	
	<tr class="invoice_main">
		<th>Itemized List</th>
	<td>
		<table id="invoice_list" class="itemized_list">
		<tr>
		<th class="id">ID</th>
		<th class="name">Name</th>
		<th class="description">Description</th>
		<th class="quantity">Quantity</th>
		<th class="price">Unit Price</th>
		<th class="item_total">Total</th>
		</tr>

		<?php
		$counter = 1;
		foreach($itemized_array as $itemized_item){	 ?>
		
		<tr valign="top">
			<td valign="top" class="id"><?php echo $counter; ?></td>
			<td valign="top" class="name"><input class="item_name" name="itemized_list[<?php echo $counter; ?>][name]" value="<?php echo stripslashes($itemized_item[name]); ?>"></td>
			<td valign="top" class="description"><textarea style="height: 25px;" name="itemized_list[<?php echo $counter; ?>][description]" class="item_description autogrow"><?php echo stripslashes($itemized_item[description]); ?></textarea></td>
			<td valign="top" class="quantity"><input   value="<?php echo stripslashes($itemized_item[quantity]); ?>" name="itemized_list[<?php echo $counter; ?>][quantity]" id="qty_item_<?php echo $counter; ?>"  class="item_quantity"></td>
			<td valign="top" class="price">$<input value="<?php echo stripslashes($itemized_item[price]); ?>"  name="itemized_list[<?php echo $counter; ?>][price]" id="price_item_<?php echo $counter; ?>"  class="item_price"></td>
			<td valign="top" class="item_total" id="total_item_<?php echo $counter; ?>" ></td>
		</tr>

		
		<?php $counter++; } ?>
		</table>
	</td>
	</tr>

	<tr class="invoice_main">
		<th style='vertical-align:bottom;text-align:right;'><p><a href="#" id="add_itemized_item">Add Another Item</a><br /><span class='light_text'></span></p></th>
		<td>
			<table class="itemized_list">
			<tr>
			<td align="right">Grand Total:</td>
			<td class="item_total">$<span id='amount'></span></td>
			</tr>
			</table>
		</td>
	</tr>


	<tr>
		<td colspan="2">

			<p class="submit">
				<input type="submit" value="Save and Preview">
			</p>
		</td>
	</tr>

	<?php if(wp_invoice_get_invoice_status($invoice_id,'100')) { ?>		
	<tr>
		<td colspan="2">
			<h3>Invoice History Log</h3>
			<ul id="invoice_history_log">
			<?php echo wp_invoice_get_invoice_status($invoice_id,'100'); ?>
			</ul>
		</td>
	</tr>
	<?php } ?>
	</table>
	</form>

	<?php } ?>
	</div>
<?php
}


function wp_invoice_options_settings()
{
global $wpdb;
echo $_POST['show_wp_invoiceshow_quantities'];
echo $_POST['hide_wp_invoiceshow_quantities'];

if(isset($_POST['business_name'])) { update_option('wp_invoice_business_name', $_POST['business_name']); }
if(isset($_POST['business_phone'])) update_option('wp_invoice_business_phone', $_POST['business_phone']);
if(isset($_POST['wp_invoice_paypal_address'])) update_option('wp_invoice_paypal_address', $_POST['wp_invoice_paypal_address']);
if(isset($_POST['wp_invoice_web_invoice_page'])) update_option('wp_invoice_web_invoice_page', $_POST['wp_invoice_web_invoice_page']);
if(isset($_POST['wp_invoice_payment_link'])) update_option('wp_invoice_payment_link', $_POST['wp_invoice_payment_link']);
if(isset($_POST['wp_invoice_use_css'])) update_option('wp_invoice_use_css', $_POST['wp_invoice_use_css']);

if(isset($_POST['wp_invoice_show_quantities'])) update_option('wp_invoice_show_quantities', $_POST['wp_invoice_show_quantities']);

if(isset($_POST['wp_invoice_protocol'])) update_option('wp_invoice_protocol', $_POST['wp_invoice_protocol']);
if(isset($_POST['wp_invoice_email_address'])) update_option('wp_invoice_email_address', $_POST['wp_invoice_email_address']);
if(isset($_POST['business_name']) || $_POST['business_address']|| $_POST['wp_invoice_email_address'] || isset($_POST['business_phone']) || isset($_POST['invoice_url_page'])) $message = "Information saved.";





if(isset($_POST['wp_invoice_billing_meta'])) {

$wp_invoice_billing_meta = explode('
',$_POST['wp_invoice_billing_meta']);
$wp_invoice_billing_meta = wp_invoice_fix_billing_meta_array($wp_invoice_billing_meta);
update_option('wp_invoice_billing_meta', urlencode(serialize($wp_invoice_billing_meta)));
}

if(get_option('wp_invoice_billing_meta') != '') $wp_invoice_billing_meta = unserialize(urldecode(get_option('wp_invoice_billing_meta')));



if(!$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_MAIN."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_LOG."';")) { $warning_message = "The plugin database tables are gone, deactivate and reactivate plugin to re-create them."; }
if($warning_message) echo "<div id=\"message\" class='error' ><p>$warning_message</p></div>";





?>
<div class="wrap">
<?php if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";?>
<h2>Invoice Settings</h2>
<table class="form-table" id="settings_page_table">
<form method='POST'>

<tr class="invoice_main">
	<th>Page to Display Invoices:</th>
	<td>
	<select name='wp_invoice_web_invoice_page'>
	<option></option>
	<?php $list_pages = $wpdb->get_results("SELECT ID, post_title, post_name, guid FROM ". $wpdb->prefix ."posts WHERE post_status = 'publish' AND post_type = 'page' ORDER BY post_title");
	$wp_invoice_web_invoice_page = get_option('wp_invoice_web_invoice_page');
	foreach ($list_pages as $page)
	{ 
	echo "<option  style='padding-right: 10px;'";
	if(isset($wp_invoice_web_invoice_page) && $wp_invoice_web_invoice_page == $page->ID) echo " SELECTED ";
	echo " value=\"".$page->ID."\">". $page->post_title . "</option>\n"; 
	}
	echo "</select>";?><p>Changing this will prevent old invoice links from working.</p>
	</td>
</tr>

<?php echo "<tr class=\"invoice_main\"><th width=\"200\">PayPal Address</th><td><input id='wp_invoice_paypal_address' name=\"wp_invoice_paypal_address\" class=\"input_field\"  type=\"text\" value=\"".get_option('wp_invoice_paypal_address') . "\"></td></tr>"; ?>
<?php /* Will be used later for credit card processing
<tr>
	<th>Protocol to Use for Invoices:</th>
	<td>
	<select  name="wp_invoice_protocol">
	<option></option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_protocol') == 'https') echo 'selected="yes"';?>>https</option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_protocol') == 'http') echo 'selected="yes"';?>>http</option>
	</select> 
	</td>
</tr>
*/ ?>
<?php echo "<tr><th width=\"200\">Business Name</th><td><input name=\"business_name\" type=\"text\" class=\"input_field\" value=\"".get_option('wp_invoice_business_name') . "\"></td></tr>"; ?>
<?php echo "<tr><th width=\"200\">Business Phone</th><td><input name=\"business_phone\" type=\"text\"  class=\"input_field\" value=\"".get_option('wp_invoice_business_phone') . "\"></td></tr>"; ?>
<?php echo "<tr><th width=\"200\">Return eMail Address</th><td><input name=\"wp_invoice_email_address\"  class=\"input_field\" type=\"text\" value=\"".get_option('wp_invoice_email_address') . "\"></td></tr>"; ?>

<tr>
	<th>Use CSS:</th>
	<td>
	<select name="wp_invoice_use_css">
	<option></option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_use_css') == 'yes') echo 'selected="yes"';?>>yes</option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_use_css') == 'no') echo 'selected="yes"';?>>no</option>
	</select> 
	</td>
</tr>



<tr><th width="200">Quantities on Front End</th><td>
<select  name="wp_invoice_show_quantities">
	<option  <?php if(get_option('wp_invoice_show_quantities') == 'Show') echo 'selected="yes"';?>>Show</option>
	<option <?php if(get_option('wp_invoice_show_quantities') == 'Hide') echo 'selected="yes"';?>>Hide</option>
</select>
</td>
</tr>

<?php /* Hardcoding these for now, seem to not be working on some installs
<tr>
<th>Billing Meta<br /><span class="light_text">(Enter one per line)</span></th>
<td>
<textarea cols="30" name="wp_invoice_billing_meta" class="autogrow"><?php if(count($wp_invoice_billing_meta)) { foreach($wp_invoice_billing_meta as $single_meta){ echo $single_meta . "\n";} } ?></textarea>
<br />Do not delete or rename <b>Street Address</b>, <b>City</b>, <b>State</b>, and <b>ZIP</b>, it will interfere with billing.
</td></tr>
*/ ?>
<tr><td></td><td><input type="submit" value="update" class="button"></form></td></tr>



<td colspan="2"><a id="delete_all_databases" href="admin.php?page=new_invoice&tctiaction=complete_removal">Remove All WP-Invoice Databases</a> - Only do this if you want to completely remove the plugin.  All invoices and logs will be gone... forever.</td>

		
</table>
</div>
<?php
}








	

?>