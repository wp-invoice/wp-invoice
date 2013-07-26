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


		
function wp_invoice_default($message='')
{
	global $wpdb;
	//Make sure tables exist
	


	// The error takes precedence over others being that nothing can be done w/o tables
	if(!$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_MAIN."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_LOG."';")) { $warning_message = ""; }
	
	if($warning_message) echo "<div id=\"message\" class='error' ><p>$warning_message</p></div>";
	if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";
	
	$all_invoices = $wpdb->get_results("SELECT * FROM ".WP_INVOICE_TABLE_MAIN);

?>
	
	
	
	<div class="wrap">
	
	<form id="invoices-filter" action="" method="post" >
	<h2>Invoices Overview</h2>
	<div class="tablenav clearfix">
	
	<div class="alignleft">
	<select name="action">
		<option value="-1" selected="selected">-- Actions --</option>
		<option value="Send Invoice" name="sendit" >Send Invoice(s)</option>
		<option value="Archive Invoice" name="archive" >Archive Invoice(s)</option>
		<option value="Un-Archive Invoice" name="unarchive" >Un-Archive Invoice(s)</option>
		<?php /*<option value="make_template" name="unarchive" >Make Template</option>
		<option value="unmake_template" name="unarchive" >Unmake Template</option>*/ ?>
		<option  value="Delete" name="deleteit" >Delete</option>
	</select>
	<input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action" />
	</div>

	<div class="alignright">
		<ul class="subsubsub" style="margin:0;">
		<li>Filter:</li>
		<li><a href='#' class="" id="">All Invoices</a> |</li>
		<li><a href='#'  class="paid" id="">Paid</a> |</li>
		<li><a href='#'  class="sent" id="">Unpaid</a> |</li>
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
	<tbody>
	<?php
	
	if(!wp_invoice_number_of_invoices()) { ?>
	<tr><td colspan="6" align="center"><div style="padding: 20px;">You have not created any invoices yet, <a href="admin.php?page=new_invoice">create one now.</a></div></td></tr>
	<?php }
	
	$wp_invoice_payment_link = get_option("wp_invoice_payment_link");
	if(!empty($wp_invoice_payment_link)) { if(strpos('?',$wp_invoice_payment_link)) { $wp_invoice_payment_link = $wp_invoice_payment_link . "&";} else {$wp_invoice_payment_link = $wp_invoice_payment_link . "?";} }

	foreach ($all_invoices as $invoice) {
		$profileuser = get_user_to_edit($invoice->user_id);
		echo "<tr class='"; if(wp_invoice_meta($invoice->invoice_num,'paid_status')) {echo "alternate "; } if(wp_invoice_meta($invoice->invoice_num,'archive_status') == 'archived') { echo " wp_invoice_archived_invoices "; }  echo "'>
		<th class=\"check-column\"><input type=\"checkbox\" name=\"multiple_invoices[]\" value=\"$invoice->invoice_num\"  /></th>
		<td><a href=\"admin.php?page=new_invoice&tctiaction=editInvoice&invoice_id=".$invoice->invoice_num."\">". $invoice->invoice_num ."</a></td>
		<td><a href=\"admin.php?page=new_invoice&tctiaction=editInvoice&invoice_id=".$invoice->invoice_num."\">". $invoice->subject ."</a></td>
		<td>$". $invoice->amount ."</td>
		<td>";
			// Days Since Sent
			if(wp_invoice_meta($invoice->invoice_num,'paid_status')) {
			echo "<span style='display:none;'>-1</span> Paid"; }
			else {
			if(wp_invoice_meta($invoice->invoice_num,'sent_date')) {
			
$date1 = wp_invoice_meta($invoice->invoice_num,'sent_date');
$date2 = date("Y-m-d", time());
$difference = abs(strtotime($date2) - strtotime($date1));
$days = round(((($difference/60)/60)/24), 0);
if($days == 0) { echo "<span style='display:none;'>$days</span>Sent Today. "; }
elseif($days == 1) { echo "<span style='display:none;'>$days</span>Sent Yesterday. "; }
elseif($days > 1) { echo "<span style='display:none;'>$days</span>Sent $days days ago. "; }

			} else {
			echo "<span style='display:none;'>999</span>Not Sent";
			} }
	
		echo "</td>
		<td> <a href=\"user-edit.php?user_id=" . $invoice->user_id . "\">". $profileuser->first_name ." ". $profileuser->last_name. "</a></td>
		<td><a href=\"" . wp_invoice_build_invoice_link($invoice->invoice_num)."\">View Web Invoice</a></td>
	</tr>\n"; 
		
		}
		?>
	</tbody>
	</table>
		<?php if($wpdb->query("SELECT meta_value FROM `".WP_INVOICE_TABLE_META."` WHERE meta_value = 'archived'")) { ?><a href="" id="wp_invoice_show_archived">Show / Hide Archived</a><?php }?>
		</form> </div>
		
		<?php
	
	

	// wp_invoice_options_manageInvoice();
	wp_invoice_cc_setup();

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
	$wp_invoice_tax = $_REQUEST['wp_invoice_tax'];
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
			// update invoice
			
			wp_invoice_update_invoice_meta($new_invoice_id, "tax_value", $wp_invoice_tax);
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'subject') != $subject) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET subject = '$subject' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Subject Updated '); $message .= "Subject updated. ";}
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'description') != $description) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET description = '$description' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Description Updated '); $message .= "Description updated. ";}
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'amount') != $amount) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET amount = '$amount' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Amount Updated '); $message .= "Amount updated.";}
			if(wp_invoice_get_invoice_attrib($new_invoice_id,'itemized') != $itemized) { $wpdb->query("UPDATE ".WP_INVOICE_TABLE_MAIN." SET itemized = '$itemized' WHERE invoice_num = $new_invoice_id"); 			wp_invoice_update_log($new_invoice_id, 'updated', ' Itemized List Updated '); $message .= "Itemized List updated.";}

		}
		else
		{
			// new invoice
			wp_invoice_update_invoice_meta($new_invoice_id, "tax_value", $wp_invoice_tax);			
			if($wpdb->query("INSERT INTO ".WP_INVOICE_TABLE_MAIN."
			(amount,description,invoice_num,user_id,subject,itemized,status)
			VALUES ('$amount','$description','$new_invoice_id','$user_id','$subject','$itemized','0')")) 
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

	<p>This is what your invoice will appear like in the email message. They will see the itemized list after following the link to your website.</p>

	<div id="invoice_preview">
	<?php wp_invoice_show_invoice($new_invoice_id); ?>
	</div>

	<div class="invoice_horizontal_buttons">
		<form method="post" action="admin.php?page=wp-invoice/invoice_plugin.php">
		<input type="hidden" value="<?php echo $new_invoice_id; ?>" name="invoice_id" >
		<input type="submit" value="Continue Editing" name="modify" class="button-secondary" />
		<input type="submit" value="Save for Later" name="save" class="button-secondary" />
		<input type="submit" value="Email To Client" name="send_now" class="button-secondary" />
		</form>
	</div>
	Do not use the back button or you could have duplicates.
</div>
<?php

}


function wp_invoice_options_manageInvoice($invoice_id = '',$message='')
{
	global $wpdb;
	if(!empty($_REQUEST['user_id'])) $user_id = $_REQUEST['user_id'];

	// Need to unset these values 
	if(empty($_POST['copy_from_template'])) {unset($_POST['copy_from_template']);}
	if($invoice_id == '') {unset($invoice_id);}
			
			
	// New Invoice From Template
	if(isset($_POST['copy_from_template']) && $_POST['user_id']) {
		$template_invoice_id = $_POST['copy_from_template'];
		$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$template_invoice_id."'");
		$user_id = $_REQUEST['user_id'];
		$amount = $invoice_info->amount;
		$subject = $invoice_info->subject;
		$description = $invoice_info->description;
		$itemized = $invoice_info->itemized;
		$profileuser = get_user_to_edit($_POST['user_id']);
		$itemized_array = unserialize(urldecode($itemized)); 
		$wp_invoice_tax = wp_invoice_meta($template_invoice_id,'tax_value');
	}
		
	
	// Invoice Exists, we are modifying it
	if(isset($invoice_id)) {
		$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
		$user_id = $invoice_info->user_id;
		$amount = $invoice_info->amount;
		$subject = $invoice_info->subject;
		$description = $invoice_info->description;
		$itemized = $invoice_info->itemized;
		$profileuser = get_user_to_edit($invoice_info->user_id);
		$itemized_array = unserialize(urldecode($itemized)); 
		$wp_invoice_tax = wp_invoice_meta($invoice_id,'tax_value');

	}

	// Crreae two blank arrays for itemized list if none is set
	if(count($itemized_array) == 0) {
	$itemized_array[1] = "";
	$itemized_array[2] = "";	
	}
	
	if(get_option("wp_invoice_web_invoice_page") == '') { $warning_message .= "Invoice page not selected. "; }
	if(get_option("wp_invoice_payment_method") == '') { $warning_message .= "Payment method not set. "; }
	if(get_option("wp_invoice_payment_method") == '' || get_option("wp_invoice_web_invoice_page") == '') { $warning_message .= "Visit <a href='admin.php?page=invoice_settings'>settings page</a> to configure."; }
	
	if(!$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_META."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_MAIN."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_LOG."';")) { $warning_message = "The plugin database tables are gone, deactivate and reactivate plugin to re-create them."; }
	
	if($warning_message) echo "<div id=\"message\" class='error' ><p>$warning_message</p></div>";
	if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";


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

	if(isset($_REQUEST['user_id'])) {							$profileuser = get_user_to_edit($_REQUEST['user_id']); }
	
	?> 
	
	<tr><th><?php _e("Email Address") ?></th><td><?php echo $profileuser->user_email; ?></td>
	<tr><th><?php _e("Billing Information") ?></th>
	<td><?php if(!empty($profileuser->first_name) || !empty($profileuser->last_name)) { echo "<a href=\"user-edit.php?user_id=" . $profileuser->ID . "#billing_info\">" . $profileuser->first_name . " " . $profileuser->last_name . "</a><br />"; }
	if(isset($profileuser->streetaddress)) echo $profileuser->streetaddress . "<br />";
	if(isset($profileuser->city)) echo $profileuser->city . "" ;
	if(isset($profileuser->state)) echo " " . $profileuser->state;
	if(isset($profileuser->zip)) echo " " . $profileuser->zip . "<br />";
	if(empty($profileuser->first_name) || empty($profileuser->last_name) || empty($profileuser->streetaddress) || empty($profileuser->city) ||  empty($profileuser->state) ||  empty($profileuser->zip))  {
	echo "<span class=\"error\"><a style='text-decoration:none;' href=\"user-edit.php?user_id=" . $profileuser->ID . "#billing_info\">Visit user's profile to prefill billing information.</a></span>";
	}
?>
	</td>
	
	<tr><th>Invoice ID </th><td style="font-size: 1.1em; padding-top:7px;"><?php if(isset($invoice_id)) { echo $invoice_id; } else { echo rand(10000000, 90000000);}  ?></td></tr>
	<tr class="invoice_main"><th>Subject</th><td><input  id="invoice_subject" class="subject"  name='subject' value='<?php echo $subject; ?>'></input></td></tr>
	<tr class="invoice_main"><th>Description / PO</th><td><textarea class="invoice_description_box" name='description' value=''><?php echo $description; ?></textarea></td></tr>
	
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
			<td valign="top" class="quantity"><input autocomplete="off"  value="<?php echo stripslashes($itemized_item[quantity]); ?>" name="itemized_list[<?php echo $counter; ?>][quantity]" id="qty_item_<?php echo $counter; ?>"  class="item_quantity"></td>
			<td valign="top" class="price">$<input autocomplete="off" value="<?php echo stripslashes($itemized_item[price]); ?>"  name="itemized_list[<?php echo $counter; ?>][price]" id="price_item_<?php echo $counter; ?>"  class="item_price"></td>
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
			<td colspan="2" align="right"> Tax <input style="width: 20px;"  name="wp_invoice_tax" id="wp_invoice_tax" autocomplete="off" value="<?php echo $wp_invoice_tax ?>">%</input></td>
			</tr>
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
				<?php $wp_invoice_web_invoice_page = get_option("wp_invoice_web_invoice_page"); if(empty($wp_invoice_web_invoice_page)) { ?><span class="error" style="padding: 5px; font-color: red;">Be advised - invoice link sent to customer will be incomplete since the <a href='admin.php?page=invoice_settings' alt="Settings Page">invoice page is not set</a>.</span> <?php } ?>


	
			</p>
		</td>
	</tr>

	<?php if(wp_invoice_get_invoice_status($invoice_id,'100')) { ?>		
	<tr>
		<td colspan="2">
			<h3>This Invoice's History (<a href="admin.php?page=new_invoice&invoice_id=<?php echo $invoice_id; ?>&tctiaction=clear_log">Clear Log</a>)</h3>
			<ul id="invoice_history_log">
			<?php echo wp_invoice_get_invoice_status($invoice_id,'100'); ?>
			</ul>
		</td>
	</tr>
	<?php } ?>
	</table>
	<tr>

	</form>

	<?php } ?>
	</div>
<?php
}

function wp_invoice_show_welcome_message() {

global $wpdb; ?>
<div class="wrap">
<h2>WP-Invoice Setup Steps</h2>

	<ol style="list-style-type:decimal;padding-left: 20px;" id="wp_invoice_first_time_setup">
<?php 
	$wp_invoice_web_invoice_page = get_option("wp_invoice_web_invoice_page");
	$wp_invoice_paypal_address = get_option("wp_invoice_paypal_address");
	$wp_invoice_gateway_username = get_option("wp_invoice_gateway_username");
	$wp_invoice_payment_method = get_option("wp_invoice_payment_method");

?>
	<form action="admin.php?page=new_invoice" method='POST'>
	<input type="hidden" name="tctiaction" value="first_setup">
<?php if(empty($wp_invoice_web_invoice_page) ) { ?>
	<li><a class="wp_invoice_tooltip"  title="Your clients will have to follow their secure link to this page to see their invoice. Opening this page without following a link will result in the standard page content begin shown.">Select a page to display your web invoices</a>:  
		<select name='wp_invoice_web_invoice_page'>
		<option></option>
		<?php $list_pages = $wpdb->get_results("SELECT ID, post_title, post_name, guid FROM ". $wpdb->prefix ."posts WHERE post_status = 'publish' AND post_type = 'page' ORDER BY post_title");
		foreach ($list_pages as $page)
		{ 
		echo "<option  style='padding-right: 10px;'";
		if(isset($wp_invoice_web_invoice_page) && $wp_invoice_web_invoice_page == $page->ID) echo " SELECTED ";
		echo " value=\"".$page->ID."\">". $page->post_title . "</option>\n"; 
		} ?>
		</select>
	</li>
<?php } ?>
	
<?php if(empty($wp_invoice_payment_method)) { ?>
	<li>Select how you want to accept money: 
		<select id="wp_invoice_payment_method" name="wp_invoice_payment_method">
		<option></option>
		<option value="paypal" style="padding-right: 10px;"<?php if(get_option('wp_invoice_payment_method') == 'paypal') echo 'selected="yes"';?>>PayPal</option>
		<option value="cc" style="padding-right: 10px;"<?php if(get_option('wp_invoice_payment_method') == 'cc') echo 'selected="yes"';?>>Credit Card</option>
		</select> 

		<li class="paypal_info">Your PayPal username: <input id='wp_invoice_paypal_address' name="wp_invoice_paypal_address" class="search-input input_field"  type="text" value="<?php echo stripslashes(get_option('wp_invoice_paypal_address')); ?>"></li>
				
		<li class="gateway_info">
		<a class="wp_invoice_tooltip"  title="Your credit card processor will provide you with a gateway username.">Gateway Username</a>
		<input AUTOCOMPLETE="off" name="wp_invoice_gateway_username" class="input_field search-input" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_username')); ?>">
		 <span id="wp_invoice_need_mm" class="wp_invoice_click_me">Do you need a merchant account?</span>
		</li>
				
		<li class="gateway_info">
		<a class="wp_invoice_tooltip"  title="You will be able to generate this in our credit card processor's control panel.">Gateway Transaction Key</a>
		<input AUTOCOMPLETE="off" name="wp_invoice_gateway_tran_key" class="input_field search-input" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_tran_key')); ?>">
		</li>

		<li class="gateway_info">
		Gateway URL	
		<input name="wp_invoice_gateway_url" class="input_field search-input" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_url')); ?>">
		</li>

<?php } ?>

	<li>Send an invoice:
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
	</li>
	</ol>
	
	<input type='submit' class='button' value='Save Settings and Create Invoice'>
	</form>
	<?php echo 	wp_invoice_cc_setup(false); ?>
	</div>
<?php
}

function wp_invoice_options_settings()
{
global $wpdb;
echo $_POST['show_wp_invoiceshow_quantities'];
echo $_POST['hide_wp_invoiceshow_quantities'];

if(isset($_POST['wp_invoice_business_name'])) { update_option('wp_invoice_business_name', $_POST['wp_invoice_business_name']); }
if(isset($_POST['wp_invoice_business_phone'])) update_option('wp_invoice_business_phone', $_POST['wp_invoice_business_phone']);
if(isset($_POST['wp_invoice_business_address'])) update_option('wp_invoice_business_address', $_POST['wp_invoice_business_address']);
if(isset($_POST['wp_invoice_show_business_address'])) update_option('wp_invoice_show_business_address', $_POST['wp_invoice_show_business_address']);
if(isset($_POST['wp_invoice_paypal_address'])) update_option('wp_invoice_paypal_address', $_POST['wp_invoice_paypal_address']);
if(isset($_POST['wp_invoice_web_invoice_page'])) update_option('wp_invoice_web_invoice_page', $_POST['wp_invoice_web_invoice_page']);
if(isset($_POST['wp_invoice_payment_link'])) update_option('wp_invoice_payment_link', $_POST['wp_invoice_payment_link']);
if(isset($_POST['wp_invoice_use_css'])) update_option('wp_invoice_use_css', $_POST['wp_invoice_use_css']);
if(isset($_POST['wp_invoice_payment_method'])) update_option('wp_invoice_payment_method', $_POST['wp_invoice_payment_method']);
if(isset($_POST['wp_invoice_send_thank_you_email'])) update_option('wp_invoice_send_thank_you_email', $_POST['wp_invoice_send_thank_you_email']);
if(isset($_POST['wp_invoice_show_quantities'])) update_option('wp_invoice_show_quantities', $_POST['wp_invoice_show_quantities']);
if(isset($_POST['wp_invoice_protocol'])) update_option('wp_invoice_protocol', $_POST['wp_invoice_protocol']);
if(isset($_POST['wp_invoice_force_https'])) update_option('wp_invoice_force_https', $_POST['wp_invoice_force_https']);
if(isset($_POST['wp_invoice_email_address'])) update_option('wp_invoice_email_address', $_POST['wp_invoice_email_address']);
if(isset($_POST['wp_invoice_business_name']) || $_POST['wp_invoice_business_address']|| $_POST['wp_invoice_email_address'] || isset($_POST['wp_invoice_business_phone']) || isset($_POST['wp_invoice_payment_link'])) $message = "Information saved.";

// Gateway Settings



if(isset($_POST['wp_invoice_gateway_username'])) update_option('wp_invoice_gateway_username', $_POST['wp_invoice_gateway_username']);
if(isset($_POST['wp_invoice_gateway_tran_key'])) update_option('wp_invoice_gateway_tran_key', $_POST['wp_invoice_gateway_tran_key']);
if(isset($_POST['wp_invoice_gateway_merchant_email'])) update_option('wp_invoice_gateway_merchant_email', $_POST['wp_invoice_gateway_merchant_email']);
if(isset($_POST['wp_invoice_gateway_delim_data'])) update_option('wp_invoice_gateway_delim_data', $_POST['wp_invoice_gateway_delim_data']);
if(isset($_POST['wp_invoice_gateway_delim_char'])) update_option('wp_invoice_gateway_delim_char', $_POST['wp_invoice_gateway_delim_char']);
if(isset($_POST['wp_invoice_gateway_encap_char'])) update_option('wp_invoice_gateway_encap_char', $_POST['wp_invoice_gateway_encap_char']);
if(isset($_POST['wp_invoice_gateway_header_email_receipt'])) update_option('wp_invoice_gateway_header_email_receipt', $_POST['wp_invoice_gateway_header_email_receipt']);
if(isset($_POST['wp_invoice_gateway_url'])) update_option('wp_invoice_gateway_url', $_POST['wp_invoice_gateway_url']);
if(isset($_POST['wp_invoice_gateway_MD5Hash'])) update_option('wp_invoice_gateway_MD5Hash', $_POST['wp_invoice_gateway_MD5Hash']);
if(isset($_POST['wp_invoice_gateway_test_mode'])) update_option('wp_invoice_gateway_test_mode', $_POST['wp_invoice_gateway_test_mode']);
if(isset($_POST['wp_invoice_gateway_relay_response'])) update_option('wp_invoice_gateway_relay_response', $_POST['wp_invoice_gateway_relay_response']);
if(isset($_POST['wp_invoice_gateway_email_customer'])) update_option('wp_invoice_gateway_email_customer', $_POST['wp_invoice_gateway_email_customer']);



if(isset($_POST['wp_invoice_billing_meta'])) {

$wp_invoice_billing_meta = explode('
',$_POST['wp_invoice_billing_meta']);
$wp_invoice_billing_meta = wp_invoice_fix_billing_meta_array($wp_invoice_billing_meta);
update_option('wp_invoice_billing_meta', urlencode(serialize($wp_invoice_billing_meta)));
}

if(get_option('wp_invoice_billing_meta') != '') $wp_invoice_billing_meta = unserialize(urldecode(get_option('wp_invoice_billing_meta')));



if(!$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_META."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_MAIN."';") || !$wpdb->query("SHOW TABLES LIKE '".WP_INVOICE_TABLE_LOG."';")) { $warning_message = "The plugin database tables are gone, deactivate and reactivate plugin to re-create them."; }if($warning_message) echo "<div id=\"message\" class='error' ><p>$warning_message</p></div>";





?>
<div class="wrap">
<?php if($message) echo "<div id=\"message\" class='updated fade' ><p>$message</p></div>";?>
<form method='POST'>
<h2>Invoice Settings</h2>
<table class="form-table" id="settings_page_table" >

<tr class="invoice_main">
	<th class="wp_invoice_tooltip"><a class="wp_invoice_tooltip"  title="Select the page where your invoices will be displayed. Clients must follow their secured link, simply opening the page will not show any invoices.">Page to Display Invoices</a>:</th>
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
	echo "</select>";?>
	</td>
</tr>

<tr>
	<th><a class="wp_invoice_tooltip"  title="If your website has an SSL certificate and you want to use it, the link to the invoice will be formatted for https.">Protocol to Use for Invoice URLs</a>:</th>
	<td>
	<select  name="wp_invoice_protocol">
	<option></option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_protocol') == 'https') echo 'selected="yes"';?>>https</option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_protocol') == 'http') echo 'selected="yes"';?>>http</option>
	</select> 
	</td>
</tr>
<tr>
	<th> <a class="wp_invoice_tooltip"  title="If enforced, WordPress will automatically reload the invoice page into HTTPS mode even if the user attemps to open it in non-secure mode.">Enforce HTTPS</a>:</th>
	<td>
	<select  name="wp_invoice_force_https">
	<option></option>
	<option value="true" style="padding-right: 10px;"<?php if(get_option('wp_invoice_force_https') == 'true') echo 'selected="yes"';?>>Yes</option>
	<option value="false" style="padding-right: 10px;"<?php if(get_option('wp_invoice_force_https') == 'false') echo 'selected="yes"';?>>No</option>
	</select> <a href="http://www.dpbolvw.net/click-2456790-10379064" alt="GoDaddy.com" class="wp_invoice_click_me">Do you need an SSL Certificate?</a>
	</td>
</tr>

<tr>
	<th width="200">Business Name:</th>
	<td>
	<input name="wp_invoice_business_name" type="text" class="input_field" value="<?php echo stripslashes(get_option('wp_invoice_business_name')); ?>">
	</td>
</tr>
<tr>
	<th width="200"><a class="wp_invoice_tooltip"  title="This will display on the invoice page when printed for clients' records.">Business Address</a>:</th>
	<td>
	<textarea name="wp_invoice_business_address" ><?php echo stripslashes(get_option('wp_invoice_business_address')); ?></textarea>
	</td>
</tr>

<tr>
	<th width="200">Business Phone</th>
	<td>
	<input name="wp_invoice_business_phone" type="text"  class="input_field" value="<?php echo stripslashes(get_option('wp_invoice_business_phone')); ?>">
	</td>
</tr>

<tr>
	<th><a class="wp_invoice_tooltip"  title="Address used to send out e-mail to client with web invoice link.">Return eMail Address</a>:</th>
	<td>
	<input name="wp_invoice_email_address" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_email_address')); ?>">
	</td>
</tr>

<tr>
	<th><a class="wp_invoice_tooltip"  title="An email will be sent automatically to client thanking them for their payment.">Send Payment Confirmation</a>:</th>
	<td>
	<select name="wp_invoice_send_thank_you_email">
	<option></option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_send_thank_you_email') == 'yes') echo 'selected="yes"';?>>yes</option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_send_thank_you_email') == 'no') echo 'selected="yes"';?>>no</option>
	</select> 
	</td>
</tr>

<tr>
<td colspan="2"><h2>Invoice Page Display Settings</h2></td>
</tr>
<tr>
	<th><a class="wp_invoice_tooltip"  title="Disable this if you want to use your own stylesheet.">Use CSS</a>:</th>
	<td>
	<select name="wp_invoice_use_css">
	<option></option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_use_css') == 'yes') echo 'selected="yes"';?>>yes</option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_use_css') == 'no') echo 'selected="yes"';?>>no</option>
	</select> 
	</td>
</tr>




<tr>
	<th><a class="wp_invoice_tooltip"  title="Show your business name and address on invoice.">Show Address on Invoice:</a>:</th>
	<td>
	<select name="wp_invoice_show_business_address">
	<option></option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_show_business_address') == 'yes') echo 'selected="yes"';?>>yes</option>
	<option style="padding-right: 10px;"<?php if(get_option('wp_invoice_show_business_address') == 'no') echo 'selected="yes"';?>>no</option>
	</select> 
	</td>
</tr>


<tr>
	<th width="200"><a class="wp_invoice_tooltip"  title="Show quantity breakdowns in the itemized list on the front-end.">Quantities on Front End</a></th><td>
	<select  name="wp_invoice_show_quantities">
		<option  <?php if(get_option('wp_invoice_show_quantities') == 'Show') echo 'selected="yes"';?>>Show</option>
		<option <?php if(get_option('wp_invoice_show_quantities') == 'Hide') echo 'selected="yes"';?>>Hide</option>
	</select>
	</td>
</tr>

<tr>
<td colspan="2"><h2>Payment Settings</h2></td>
</tr>

<tr>
	<th>Payment Method:</th>
	<td>
	<select id="wp_invoice_payment_method" name="wp_invoice_payment_method">
	<option value="paypal" style="padding-right: 10px;"<?php if(get_option('wp_invoice_payment_method') == 'paypal') echo 'selected="yes"';?>>PayPal</option>
	<option value="cc" style="padding-right: 10px;"<?php if(get_option('wp_invoice_payment_method') == 'cc') echo 'selected="yes"';?>>Credit Card</option>
	</select> 
	</td>
</tr>

<tr class="paypal_info">
	<th width="200">PayPal Username</th>
	<td><input id='wp_invoice_paypal_address' name="wp_invoice_paypal_address" class="input_field"  type="text" value="<?php echo stripslashes(get_option('wp_invoice_paypal_address')); ?>">
	</td>
</tr>

<tr>
	<th colspan="2">
	<?php wp_invoice_cc_setup(false); ?>
	</td>
</tr>

<tr class="gateway_info">
	<th width="200"><a class="wp_invoice_tooltip"  title="Your credit card processor will provide you with a gateway username.">Gateway Username</a></th>
	<td>
	<input AUTOCOMPLETE="off" name="wp_invoice_gateway_username" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_username')); ?>"> <span id="wp_invoice_need_mm" class="wp_invoice_click_me">Do you need a merchant account?</span>
	</td>
</tr>

<tr class="gateway_info">
	<th width="200"><a class="wp_invoice_tooltip"  title="You will be able to generate this in your credit card processor's control panel.">Gateway Transaction Key</a></th>
	<td>
	<input AUTOCOMPLETE="off" name="wp_invoice_gateway_tran_key" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_tran_key')); ?>">
	</td>
</tr>


<tr class="gateway_info">
	<th width="200"><a class="wp_invoice_tooltip"  title="This is the URL provided to you by your credit card processing company.">Gateway URL</a></th>
	<td>
	<input name="wp_invoice_gateway_url" id="wp_invoice_gateway_url" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_url')); ?>">
	<br /><span class="wp_invoice_click_me" id="wp_invoice_merchantplus_prefill">MerchantPlus</span> 
	</td>
</tr>

<tr class="gateway_info">
	<th>Test / Live Mode:</th>
	<td>
	<select name="wp_invoice_gateway_test_mode">
	<option value="TRUE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_test_mode') == 'TRUE') echo 'selected="yes"';?>>Test - Do Not Process Transactions</option>
	<option value="FALSE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_test_mode') == 'FALSE') echo 'selected="yes"';?>>Live - Process Transactions</option>
	</select> 
	</td>
</tr>

<tr  class="gateway_info">
<td colspan="2"><h2>Advanced Gateway Settings</h2></td>
</tr>

<tr class="gateway_info">
	<th width="200">Delim Char</th>
	<td>
	<input name="wp_invoice_gateway_delim_char" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_delim_char')); ?>">
	</td>
</tr>

<tr class="gateway_info">
	<th width="200">Encap Char</th>
	<td>
	<input name="wp_invoice_gateway_encap_char" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_encap_char')); ?>">
	</td>
</tr>

<tr class="gateway_info">
	<th width="200">Merchant Email</th>
	<td>
	<input name="wp_invoice_gateway_merchant_email" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_merchant_email')); ?>">
	</td>
</tr>



<tr class="gateway_info">
	<th>Email Customer (on success):</th>
	<td>
	<select name="wp_invoice_gateway_email_customer">
	<option value="TRUE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_email_customer') == 'TRUE') echo 'selected="yes"';?>>True</option>
	<option value="FALSE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_email_customer') == 'FALSE') echo 'selected="yes"';?>>False</option>
	</select> 
	</td>
</tr>

<tr class="gateway_info">
	<th width="200">Customer Reciept Email Header</th>
	<td>
	<input name="wp_invoice_gateway_header_email_receipt" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_header_email_receipt')); ?>">
	</td>
</tr>


<tr class="gateway_info">
	<th width="200">Security: MD5 Hash</th>
	<td>
	<input name="wp_invoice_gateway_MD5Hash" class="input_field" type="text" value="<?php echo stripslashes(get_option('wp_invoice_gateway_MD5Hash')); ?>">
	</td>
</tr>



<tr class="gateway_info">
	<th>Delim Data:</th>
	<td>
	<select name="wp_invoice_gateway_delim_data">
	<option value="TRUE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_delim_data') == 'TRUE') echo 'selected="yes"';?>>True</option>
	<option value="FALSE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_delim_data') == 'FALSE') echo 'selected="yes"';?>>False</option>
	</select> 
	</td>
</tr>

<tr class="gateway_info">
	<th>Relay Response:</th>
	<td>
	<select name="wp_invoice_gateway_relay_response">
	<option value="TRUE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_relay_response') == 'TRUE') echo 'selected="yes"';?>>True</option>
	<option value="FALSE" style="padding-right: 10px;"<?php if(get_option('wp_invoice_gateway_relay_response') == 'FALSE') echo 'selected="yes"';?>>False</option>
	</select> 
	</td>
</tr>






<tr class="invoice_main">
	<td></td>
	<td><input type="submit" value="update" class="button">
	</td>
</tr>
</table>

<table class="form-table" >

<td colspan="2"><a id="delete_all_databases" href="admin.php?page=new_invoice&tctiaction=complete_removal">Remove All WP-Invoice Databases</a> - Only do this if you want to completely remove the plugin.  All invoices and logs will be gone... forever.</td>
</table>
</form>
</div>
<?php
}



function wp_invoice_cc_setup($show_title = TRUE) {
if($show_title) { ?> 	<div id="wp_invoice_need_mm" style="border-top: 1px solid #DFDFDF; ">Do you need to accept credit cards?</div> <?php } ?>

<div class="wrap">
	<div class="wp_invoice_credit_card_processors">
		<div  style="line-height: 1.5em;width: 97%; margin: 0 auto; padding-bottom: 20px; font-size: 1.1em;">WP-Invoice users are eligible for below-market pricing from MerchantPlus and MerchantExpress. MerchantWarehouse was unable to offer us special rates due to their "unique" pricing sturcture.  They are on this list because of their solid reputation.</div>

<table id="merchant_table" style="width: 100%; margin: 0 auto;">
<thead>
	<tr>
	<th class="header" style="border-bottom: 1px solid #d6d6e4;width: 20%;"></th>
	<th onClick="window.location='http://twincitiestech.com/links/MerchantPlus.php'" class="header" style="cursor: pointer; border-left: 1px solid #d6d6e4; border-right: 1px solid #d6d6e4; border-bottom: 1px solid #d6d6e4; width: 20%; height: 80px;background: url(<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-invoice/images/mp.gif) center center no-repeat;"></th>
	<th onClick="window.location='http://twincitiestech.com/links/MerchantExpress.php'"class="header" style="cursor: pointer; border-right: 1px solid #d6d6e4; border-bottom: 1px solid #d6d6e4;width: 20%; height: 80px;background: url(<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-invoice/images/me.gif) center center no-repeat;"></th>
	<th onClick="window.location='http://twincitiestech.com/links/MerchantWarehouse.php'"class="header" style="cursor: pointer; border-bottom: 1px solid #d6d6e4;width: 20%; height: 80px;background: url(<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-invoice/images/mw.gif) center center no-repeat;"></th>

	</tr>
	</thead>
	<tbody>
	<tr>
		<th align="right"  style="padding:10px 10px 10px 0;">Transaction Rate</th>
		<td style="border-right: 1px solid #d6d6e4;border-left: 1px solid #d6d6e4; border-right: 1px solid #d6d6e4;text-align:center;"><span class="wp_invoice_standard_price">2.15%</span> <span class="wp_invoice_special_price">2.09%</span></td>
		<td style="border-right: 1px solid #d6d6e4;text-align:center;"><span class="wp_invoice_special_price">2.27%</span></td>
		<td  rowspan="3" style="text-align:center;"><div style="padding: 0 20px;">MW's rates are not published since they customize their rates to every customer's account.</div></td>
	</tr>					
	<tr>
		<th align="right" style="padding:10px 10px 10px 0;">Transaction Fee</th>
		<td style="border-left: 1px solid #d6d6e4; border-right: 1px solid #d6d6e4;text-align:center;"><span class="wp_invoice_special_price">$0.25</span></td>
		<td style="border-right: 1px solid #d6d6e4;text-align:center;"><span class="wp_invoice_special_price">$0.29</span></td>

	</tr>					
	<tr>
		<th align="right" style="padding:10px 10px 10px 0;">Monthly Fee</th>
		<td style="border-left: 1px solid #d6d6e4; border-right: 1px solid #d6d6e4;text-align:center;"><span class="wp_invoice_standard_price">$19.95</span> <span class="wp_invoice_special_price">$14.96</span></td>
		<td style="border-right: 1px solid #d6d6e4;text-align:center;"><span class="wp_invoice_standard_price">$30.00</span> <span class="wp_invoice_special_price">$25.00</span></td>

	</tr>	
	<tr class="wp_invoice_merchant_phones">
		<th align="right" style="padding:10px 10px 10px 0;">Phone Number</th>
		<td style="border-left: 1px solid #d6d6e4; border-right: 1px solid #d6d6e4;text-align:center;">(800)546-1997</td>
		<td style="border-right: 1px solid #d6d6e4;text-align:center;">(888)845-9457</td>
		<td style="text-align:center;">(866)345-5959</td>
	</tr>
	<tr>
		<td></td>	
		<td style="padding-top: 20px; text-align:center;"><input type="submit" value="MerchantPlus" class="button-secondary action" onClick="window.location='http://twincitiestech.com/links/MerchantPlus.php'" /></td>
		<td style="padding-top: 20px; text-align:center;"><input type="submit" value="MerchantExpress.com" class="button-secondary action" onclick="window.location='http://twincitiestech.com/links/MerchantExpress.php'" /></td>
		<td style="padding-top: 20px; text-align:center;"><input type="submit" value="MerchantWarehouse" class="button-secondary action" onclick="window.location='http://twincitiestech.com/links/MerchantWarehouse.php'" /></td>
	</tr>
	</tbody>
</table>
</div>
</div>
	<?php
}



	

?>