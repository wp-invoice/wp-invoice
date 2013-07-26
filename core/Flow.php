<?php

class WP_Invoice_Decider {

	var $message;
	var $ouput;
	
	function WP_Invoice_Decider($wp_invoice_action = null) {
	
	global $wpdb;	
	
	$wp_invoice_action = (!empty($_REQUEST['wp_invoice_action']) ? $_REQUEST['wp_invoice_action'] : $wp_invoice_action);
	$invoice_id = $_REQUEST['invoice_id'];
	$wp_invoice_recurring_billing = $_REQUEST['recurring_billing'];
		
	// Convert some things
	if($wp_invoice_action == __('Continue Editing', WP_INVOICE_TRANS_DOMAIN)) $wp_invoice_action = 'doInvoice';
	if($wp_invoice_action ==  __('Email to Client', WP_INVOICE_TRANS_DOMAIN)) $wp_invoice_action = 'send_now';
	if($wp_invoice_action == __('Save for Later', WP_INVOICE_TRANS_DOMAIN)) $wp_invoice_action = 'save_not_send';
		
	echo "<div class='wrap'>";
	switch($wp_invoice_action) 
	{
		case "save_and_preview":
		if(empty($invoice_id)) { wp_invoice_show_message("Error - invoice id was not passed."); } 
		else {
		wp_invoice_show_message(wp_invoice_process_invoice_update($invoice_id),'updated fade');
		wp_invoice_saved_preview($invoice_id); 
		}
		break;

		case "clear_log":
		wp_invoice_show_message(wp_invoice_clear_invoice_status($invoice_id),'updated fade');
		wp_invoice_options_manageInvoice($invoice_id);
		break;
		
		case "complete_removal":
		wp_invoice_complete_removal();
		wp_invoice_show_settings();
		break;
		
		case "doInvoice" :
		// Process the message to be sent in email		
		if(isset($_REQUEST['wp_invoice_email_message_content']) && isset($invoice_id)) wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_email_message_content",$_REQUEST['wp_invoice_email_message_content'] );
		if(isset($invoice_id)) { wp_invoice_options_manageInvoice($invoice_id); }
		else {	wp_invoice_options_manageInvoice();	}
		break;
			
		case "overview":
		wp_invoice_default();
		break;
		
		case "wp_invoice_show_welcome_message":
		wp_invoice_show_welcome_message();
		break;
		
		case "wp_invoice_recurring_billing":
		wp_invoice_recurring_overview();
		break;
			
		case "send_now":
		// Process the message to be sent in email
		if(isset($_REQUEST['wp_invoice_email_message_content']) && isset($invoice_id)) wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_email_message_content",$_REQUEST['wp_invoice_email_message_content'] );
		wp_invoice_show_message(wp_invoice_send_email($invoice_id));
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;

		case "first_setup":
		if(isset($_POST['wp_invoice_web_invoice_page'])) update_option('wp_invoice_web_invoice_page', $_POST['wp_invoice_web_invoice_page']);
		if(isset($_POST['wp_invoice_paypal_address'])) update_option('wp_invoice_paypal_address', $_POST['wp_invoice_paypal_address']);
		if(isset($_POST['wp_invoice_payment_method'])) update_option('wp_invoice_payment_method', $_POST['wp_invoice_payment_method']);
		if(isset($_POST['wp_invoice_gateway_username'])) update_option('wp_invoice_gateway_username', $_POST['wp_invoice_gateway_username']);
		if(isset($_POST['wp_invoice_gateway_tran_key'])) update_option('wp_invoice_gateway_tran_key', $_POST['wp_invoice_gateway_tran_key']);
		if(isset($_POST['wp_invoice_gateway_merchant_email'])) update_option('wp_invoice_gateway_merchant_email', $_POST['wp_invoice_gateway_merchant_email']);				
		// Moneybookers
		if(isset($_POST['wp_invoice_moneybookers_address'])) update_option('wp_invoice_moneybookers_address', $_POST['wp_invoice_moneybookers_address']);
		if(isset($_POST['wp_invoice_moneybookers_merchant'])) update_option('wp_invoice_moneybookers_merchant', $_POST['wp_invoice_moneybookers_merchant']);
		if(isset($_POST['wp_invoice_moneybookers_secret'])) update_option('wp_invoice_moneybookers_secret', $_POST['wp_invoice_moneybookers_secret']);
		if(isset($_POST['wp_invoice_moneybookers_ip'])) update_option('wp_invoice_moneybookers_ip', $_POST['wp_invoice_moneybookers_ip']);
		// AlertPay
		if(isset($_POST['wp_invoice_alertpay_address'])) update_option('wp_invoice_alertpay_address', $_POST['wp_invoice_alertpay_address']);
		if(isset($_POST['wp_invoice_alertpay_merchant'])) update_option('wp_invoice_alertpay_merchant', $_POST['wp_invoice_alertpay_merchant']);
		if(isset($_POST['wp_invoice_alertpay_secret'])) update_option('wp_invoice_alertpay_secret', $_POST['wp_invoice_alertpay_secret']);

		wp_invoice_options_manageInvoice();
		break;				
		
		case "invoice_settings":
		wp_invoice_process_settings();
		wp_invoice_show_settings();
		break;
		
		case "delete_invoice":
		wp_invoice_show_message(wp_invoice_delete($_REQUEST['multiple_invoices']));
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;
		
		case "send_invoice":
		if(empty($_REQUEST['multiple_invoices'])) { wp_invoice_show_message("No invoices selected, nothing sent."); }
		else { wp_invoice_show_message(wp_invoice_send_email($_REQUEST['multiple_invoices']), 'updated fade'); }
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;
		
		case "send_reminder":
		if(empty($_REQUEST['multiple_invoices'])) { wp_invoice_show_message("No invoices selected, no reminder sent."); }
		else { wp_invoice_show_message(wp_invoice_send_email($_REQUEST['multiple_invoices'], 'reminder'), 'updated fade'); }
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;
		
		case "archive_invoice":
		if(empty($_REQUEST['multiple_invoices'])) { wp_invoice_show_message("No invoices selected, nothing archived."); }
		else { wp_invoice_show_message(wp_invoice_archive($_REQUEST['multiple_invoices']), 'updated fade'); }
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;
	
		case "unrachive_invoice":
		if(empty($_REQUEST['multiple_invoices'])) { wp_invoice_show_message("No invoices selected, nothing un-archived."); }
		else { wp_invoice_show_message(wp_invoice_unarchive($_REQUEST['multiple_invoices']), 'updated fade'); }
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;
		
		case "mark_as_paid":
		if(empty($_REQUEST['multiple_invoices'])) { wp_invoice_show_message("No invoices selected, nothing marked as paid."); }
		else { wp_invoice_show_message(wp_invoice_mark_as_paid($_REQUEST['multiple_invoices']), 'updated fade'); }
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;
		
		case "mark_as_sent":
		if(empty($_REQUEST['multiple_invoices'])) { wp_invoice_show_message("No invoices selected, nothing marked as sent.."); }
		else { wp_invoice_show_message(wp_invoice_mark_as_sent($_REQUEST['multiple_invoices']), 'updated fade'); }
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
		break;

		case "save_not_send":
		// Process the message to be sent in email
		if(isset($_REQUEST['wp_invoice_email_message_content']) && isset($invoice_id)) wp_invoice_update_invoice_meta($invoice_id, "wp_invoice_email_message_content",$_REQUEST['wp_invoice_email_message_content']);
		// Already saved, this just shows a message
		$wp_invoice_custom_invoice_id = wp_invoice_meta($invoice_id, 'wp_invoice_custom_invoice_id');
		
		if($wp_invoice_custom_invoice_id) {$message =  "Invoice <b>$wp_invoice_custom_invoice_id</b> saved.";}
		else { 	$message =  "Invoice <b>#" . $invoice_id . "</b> saved.";	}
		$message .= " <a href=".wp_invoice_build_invoice_link($invoice_id) .">View Web Invoice</a>";
		
		wp_invoice_show_message($message,' updated fade');
		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}
				
		break;
		
		
		
		
		
		default:

		if($wp_invoice_recurring_billing) { wp_invoice_recurring_overview(); } else { wp_invoice_default();}

		break;
	}
	echo "</div>";

	}
	
	function display() {
		echo "<div class=\"wrap\">";
		if($this->message) echo "<div id=\"message\" class='error' ><p>".$this->message."</p></div>";
		echo $this->output;
		echo "</div>";
	}

}

?>