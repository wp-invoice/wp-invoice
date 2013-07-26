<?php 
/*
	Created by TwinCitiesTech.com
	(website: twincitiestech.com       email : support@twincitiestech.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 3 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

	
function wp_invoice_the_content($content) {
	$ip=$_SERVER['REMOTE_ADDR']; 
	// check if wp_invoice_web_invoice_page is set, and that this it matches the current page, and the invoice_id is valid
	if(get_option('wp_invoice_web_invoice_page') != '' && is_page(get_option('wp_invoice_web_invoice_page'))) {

	// Check to see a proper invoice id is used, or show regular content
	if(!wp_invoice_md5_to_invoice($_GET['invoice_id'])) return  $content; else $invoice_id = wp_invoice_md5_to_invoice($_GET['invoice_id']);

	$result .= "<div id=\"invoice_page\" class=\"clearfix\">";

		
		//If already paid, show thank you message
		if(wp_invoice_paid_status($invoice_id)) { $result .= wp_invoice_show_already_paid($invoice_id); $stop = true; }

		// Show reciept if coming back from PayPal
		if(isset($_REQUEST['receipt_id'])) { $result .= wp_invoice_show_paypal_reciept($invoice_id) ;  $stop = true; }

		if(!$stop) { // The following is only displayed if the invoice has not been paid
		
		
			ob_start();	

			// Invoice viewed, update log
			wp_invoice_update_log($invoice_id,'visited',"Viewed by $ip");


			//If this is not recurring invoice, show regular message
			if(!wp_invoice_recurring($invoice_id))  wp_invoice_show_invoice_overview($invoice_id);

			// Show this if recurring
			if(wp_invoice_recurring($invoice_id))  wp_invoice_show_recurring_info($invoice_id);

			//Billing Business Address
			if(get_option('wp_invoice_show_business_address') == 'yes') wp_invoice_show_business_address();

			//Show Billing Information
			wp_invoice_show_billing_information($invoice_id);

			$result .= ob_get_contents();
			ob_end_clean();

		} 
	$result .= "</div>";
	
	if(get_option('wp_invoice_where_to_display') == 'overwrite' || get_option('wp_invoice_where_to_display') == '' || get_option('wp_invoice_where_to_display') == 'replace_tag') return $result;
	if(get_option('wp_invoice_where_to_display') == 'bellow_content') return $content . $result;
	if(get_option('wp_invoice_where_to_display') == 'above_content') return $result . $content;

	} else return $content;

}
	
	

function wp_invoice_frontend_header() {
if(wp_invoice_md5_to_invoice($_GET['invoice_id']) && get_option('wp_invoice_web_invoice_page') != '' && is_page(get_option('wp_invoice_web_invoice_page')))  {

$invoice_id = wp_invoice_md5_to_invoice($_GET['invoice_id']);
?>

			
<script type="text/javascript"> 


	function changePaymentOption(){
		var dropdown = document.getElementById("wp_invoice_select_payment_method_selector");
		var index = dropdown.selectedIndex;
		var ddVal = dropdown.options[index].value;
		var ddText = dropdown.options[index].text;

		if(ddVal == 'PayPal') {
			jQuery(".payment_info").hide();
			jQuery(".paypal_ui").show();
		}		
		
		if(ddVal == 'Credit Card') {
			jQuery(".payment_info").hide();
			jQuery(".cc_ui").show();
		}
		
	}
	

function cc_card_pick(){
	numLength = jQuery('#card_num').val().length;
	number = jQuery('#card_num').val();
	if(numLength > 10)
	{
		if((number.charAt(0) == '4') && ((numLength == 13)||(numLength==16))) { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('visa_card'); }
		else if((number.charAt(0) == '5' && ((number.charAt(1) >= '1') && (number.charAt(1) <= '5'))) && (numLength==16)) { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('mastercard'); }
		else if(number.substring(0,4) == "6011" && (numLength==16)) 	{ jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('amex'); }
		else if((number.charAt(0) == '3' && ((number.charAt(1) == '4') || (number.charAt(1) == '7'))) && (numLength==15)) { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('discover_card'); }
		else { jQuery('#cardimage').removeClass(); jQuery('#cardimage').addClass('nocard'); }

	}
}

<?php
function wp_invoice_curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
?>
	
function process_cc_checkout(){

jQuery('#wp_invoice_process_wait span').html('<img src="<?php echo WP_Invoice::frontend_path(); ?>/core/images/processing-ajax.gif">');

site_url = '<?php echo wp_invoice_curPageURL(); ?>';
link_id = 'wp_cc_response';
	var req = jQuery.post ( site_url, jQuery('#checkout_form').serialize(), function(html){
			
			var explode = html.toString().split('\n');
			var shown = false;
			var msg = '<b><?php _e('There are problems with your transaction:', WP_INVOICE_TRANS_DOMAIN); ?></b><ol>';
			

			for ( var i in explode )
			{
				var explode_again = explode[i].toString().split('|');
				if (explode_again[0]=='error')
				{
					if ( ! shown ) {
						jQuery('#' + link_id).fadeIn("slow");
					}
					shown = true;
					add_remove_class('ok','error',explode_again[1]);
					/*jQuery('#err_' + explode_again[1]).html(explode_again[2]); */
					msg += "<li>" + explode_again[2] + "</li>";
				}
				else if (explode_again[0]=='ok') {
					add_remove_class('error','ok',explode_again[1]);
					/*jQuery('#err_' + explode_again[1]).hide(); */
				}
			}
			
			if ( ! shown )
			{
			if(html == 'Transaction okay.') {
				
				jQuery('.online_payment_form').fadeOut("slow");
				jQuery('#wp_cc_response').fadeIn("slow");
				jQuery('#wp_cc_response').html("<?php _e('Thank you! <br />Payment processed successfully!', WP_INVOICE_TRANS_DOMAIN); ?>");
				jQuery("#credit_card_information").hide(); 
				
				jQuery("#welcome_message").html('<?php _e('Invoice Paid!', WP_INVOICE_TRANS_DOMAIN); ?>');
				jQuery('#' + link_id).show();
				}
			}
			else {
				add_remove_class('success','error',link_id);
				jQuery('#' + link_id).html(msg + "</ol>");
			}
			jQuery('#wp_invoice_process_wait span').html('');
			req = null;
		}
	);
}

function add_remove_class(search,replace,element_id)
{
	if (jQuery('#' + element_id).hasClass(search)){
		jQuery('#' + element_id).removeClass(search);
	}
	jQuery('#' + element_id).addClass(replace);
}

</script> 

<meta name="robots" content="noindex, nofollow" />

<style type="text/css" media="print">
.noprint {display:none; visibility: hidden; }
#invoice_page #invoice_overview {width: 100% !important;}
</style>
<style type="text/css" media="screen">

.payment_info {display:none;}

<?php // Cycle through payment array and display only the default payment
$payment_array = wp_invoice_accepted_payment($invoice_id);
foreach ($payment_array as $payment_option) { ?>
	<?php if($payment_option['default']) { ?> #billing_overview .<?php echo $payment_option['name']; ?>_ui {display: block;} <?php  } ?>
<?php } ?>




<?php if(get_option('wp_invoice_use_css') == 'yes') { ?>
#invoice_page {text-align: left; clear:both;}
#invoice_page #billing_overview #wp_invoice_select_payment_method {padding-bottom: 20px;}
#invoice_page #wp_cc_response{background:#FFFAE4 none repeat scroll 0 0;border-bottom:3px solid #FFE787;margin-bottom:10px;padding:6px;display:none; }
#invoice_page #wp_cc_response .wait{text-align: center; padding: 10px 0;}
#invoice_page #wp_cc_response ol{list-style: decimal inside;}
#invoice_page #wp_cc_response.success {background:#EEFFE6 none repeat scroll 0 0!important;border-bottom:3px solid #73FF2F!important;font-weight:bold}
#invoice_page input.error, #invoice_page select.error{border: 1px solid red !important; padding: 5px;}
#invoice_page p.error {border: 1; color: red; font-weight:  bold;}
#invoice_page input {width: 230px; border:0; background: #EFEFEF; padding: 5px;  -moz-border-radius:9px;  border-radius: 9px; }
#invoice_page select option {padding-left: 4px;}
#invoice_page #country {width: 235px; border:0; background: #EFEFEF; padding: 7px;  -moz-border-radius: 5px;  border-radius: 5px; }
#invoice_page #wp_invoice_select_payment_method select {width: 235px; border:0; background: #EFEFEF; padding: 7px;  -moz-border-radius: 5px;  border-radius: 5px; }
#invoice_page #cc_pay_button {width: 230px; font-size: 1.1em; color: #FFF; border:#CF7319 1px solid; background: #FFAA28; padding: 7px;  -moz-border-radius: 5px;  border-radius: 5px; }
#invoice_page #state {width: 235px; border:0; background: #EFEFEF; padding: 7px;  -moz-border-radius: 5px;  border-radius: 5px; }
#invoice_page #exp_month, #invoice_page #exp_year {width: 70px; border:0; background: #EFEFEF; padding: 7px;  -moz-border-radius: 5px;  border-radius: 5px; }
#invoice_page .invoice_page_subheading {text-align:left; margin:0;}
#invoice_page .invoice_page_subheading_gray {text-align:left; color: #ebebeb}
#invoice_page #wp_invoice_process_wait {height: 32px;}
#invoice_page #invoice_overview {width: 400px; float:left; padding-right: 10px; margin-right: 15px; position: relative;}
#invoice_page #wp_invoice_itemized_table {width: 100%; margin-bottom:10px;}
#invoice_page #wp_invoice_itemized_table .alt_row {background: #EFEFEF}
#invoice_page #wp_invoice_itemized_table .grand_total {font-weight:bold;}
#invoice_page #wp_invoice_itemized_table .description_text {color: #9F9F9F}
#invoice_page #wp_invoice_itemized_table th {background: #DFDFDF}
#invoice_page #wp_invoice_itemized_table td, #wp_invoice_itemized_table th {padding: 5px; text-align: left;}
#invoice_page #wp_invoice_itemized_table .wp_invoice_bottom_line td {border-top:1px solid #DFDFDF;}
#invoice_page #invoice_business_info {width: 400px; float:left; padding: 10px 10px 10px 0; margin-right: 10px; }
#invoice_page #recurring_info {position: relative;}
#invoice_page .wp_invoice_due_date {position: absolute; top:0;right:0;margin:0;padding:0}
#invoice_page #billing_overview {width: 400px; float:left;}
#invoice_page #billing_overview .submit_button {}
#invoice_page #billing_overview p {padding: 0;}
#invoice_page #select_state {width: 200px; border:1px solid #86A9C7; }
#invoice_page legend span {  margin-top: 1.25em; }
#invoice_page #cardimage {margin-bottom: 10px; height: 23px;}
#invoice_page .nocard {background-position: 150px 0px !important;}
#invoice_page .visa_card {background-position: 150px -23px !important;}
#invoice_page .mastercard {background-position:150px -46px !important; }
#invoice_page .discover_card { background-position: 150px -69px !important;}
#invoice_page .amex {background-position: 150px -92px !important;}
#invoice_page pre {font-size: 12px; font-face:arial; width: 200px; }
#invoice_page #submit_button { border: 0;}
#invoice_page fieldset { position: relative;  float: left;  clear: both;  width: 100%;  margin-top: 5px;  padding: 0 0 5px 0;  border-style: none;   } 
#invoice_page legend span {  position: absolute;  left: 0.74em;  top: 0;  margin-top: 0.5em;  font-size: 135%; }
#invoice_page .no_set_amount { width: 50px;}
#invoice_page fieldset ol {  padding:0;list-style-type: none !important; margin: 0; list-style-image }
#invoice_page fieldset li {  margin-bottom: 10px; padding-bottom: 0; float: left;   list-style: none; text-align:left; clear: left;   width: 100%;  }
#invoice_page fieldset label {  float: left;  width: 135px;  padding-top: 3px;margin-right: 15px;  text-align: right;padding-bottom: 10px;}
#invoice_page fieldset .submit {  float: none;  width: auto;  border-style: none;  padding-left: 12em;  background-color: transparent;  background-image: none;}
#invoice_page.clearfix:after {content: ".";display: block;clear: both;visibility: hidden;line-height: 0;height: 0;}
#invoice_page.clearfix {display: inline-block;}
html[xmlns] #invoice_page .clearfix {display: block;}
* html #invoice_page .clearfix {height: 1%;}
<?php } ?>
</style>

<?php } 

}

