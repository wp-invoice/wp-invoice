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

		
function wp_invoice_frontend($content)
{
$wp_invoice_web_invoice_page = get_option('wp_invoice_web_invoice_page');
if(!empty($wp_invoice_web_invoice_page) && is_page(get_option('wp_invoice_web_invoice_page'))) {
	global $wpdb;
	$md5_invoice_id = $_GET['invoice_id'];

	// Convert MD5 into Actual Invoice ID
	$all_invoices = $wpdb->get_col("SELECT invoice_num FROM ".WP_INVOICE_TABLE_MAIN." ");
	foreach ($all_invoices as $value) 
	{
    if(md5($value) == $md5_invoice_id) {$invoice_id = $value;}
	}
	// Convert MD5 into Actual Invoice ID
	
	//Check if invoice exists, if not, show content
	if(!wp_invoice_does_invoice_exist($invoice_id)) return $content;

	$invoice_info = $wpdb->get_row("SELECT * FROM ".WP_INVOICE_TABLE_MAIN." WHERE invoice_num = '".$invoice_id."'");
	
	$user_id = $invoice_info->user_id;
	$amount = $invoice_info->amount;
	$subject = $invoice_info->subject;
	$description = $invoice_info->description;
	$itemized = $invoice_info->itemized;
	
	$last_name = get_usermeta($invoice_info->user_id,'last_name');
	$first_name = get_usermeta($invoice_info->user_id,'first_name');
	$phone_number = get_usermeta($invoice_info->user_id,'phonenumber');
	$street_address = get_usermeta($invoice_info->user_id,'streetaddress');
	$state = get_usermeta($invoice_info->user_id,'state');
	$city = get_usermeta($invoice_info->user_id,'city');
	$zip = get_usermeta($invoice_info->user_id,'zip');

	$email_address = $wpdb->get_var("SELECT user_email FROM wp_users WHERE id=".$invoice_info->user_id."");
	$ip=$_SERVER['REMOTE_ADDR'];

	if(!strpos($amount,'.')) $amount = $amount . ".00";

	//Convert phone number into paypal format
	//Remove 1 if exists in begining
	list($day_phone_a, $day_phone_b, $day_phone_c) = split('[/.-]', $phone_number);
	
	if($_REQUEST['action'] == 'done') {
 	wp_invoice_paid($invoice_id);
 	wp_invoice_update_log($invoice_id,'paid',"Invoice paid by $ip");
?>
<div id="invoice_page" class="clearfix">
<div id="invoice_overview" clas="cleafix">
	<h2 class="invoice_page_subheading"><?php echo $first_name . " " . $last_name; ?>, thank you for your payment!</h2>
	<p><strong>Invoice #<?php echo $invoice_id; ?> with a total amount of $<?php echo $amount; ?> has been paid.</strong></p>
</div>
</div>	
<?php

 }
 
 else 
 
 {
 	wp_invoice_update_log($invoice_id,'visited',"Viewed by $ip");

 ?>
<div id="invoice_page" class="clearfix">
<div id="invoice_overview" class="clearfix">
	<?php if(isset($invoice_id)) { ?>
	<h2 class="invoice_page_subheading">Welcome, <?php echo $first_name . " " . $last_name; ?>!</h2>
	<p>We have sent you invoice #<?php echo $invoice_id; ?> with a total amount of $<?php echo $amount; ?>.  If you have any questions please feel free to contact us at any time.</p>
	<p><?php echo str_replace("\n", "<br />", $description);  ?></p>

	<?php echo wp_invoice_draw_itemized_table($invoice_id); ?> 
	<?php } ?>
</div>

<div id="billing_overview" class="clearfix">
<form action="https://www.paypal.com/us/cgi-bin/webscr" method="post">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="cmd" value="_ext-enter">
	<input type="hidden" name="upload" value="1">
	<input type="hidden" name="business" value="<?php echo get_option('wp_invoice_paypal_address'); ?>">
	<input type="hidden" name="return" value="<?php echo wp_invoice_build_invoice_link($invoice_id); ?>&action=done">
<?php if(isset($invoice_id)) { ?>
	<input name="amount" type="hidden" value="<?php echo $amount; ?>">
	<input name="invoice_num" type="hidden" id="invoice_num"  value="<?php echo  $invoice_id; ?>">

<?php  }
	
	// Convert Itemized List into PayPal Item List 
	$itemized = $invoice_info->itemized;
	$itemized_array = unserialize(urldecode($itemized)); 

	if(is_array($itemized_array)) {
?>
	<input type="hidden" name="redirect_cmd" value="_cart">
<?php
	$counter = 1;
	foreach($itemized_array as $itemized_item)
		{
?>
		<input type="hidden" name="item_name<?php echo "_" . $counter; ?>" value="<?php echo $itemized_item[name] ?>">
		<input type="hidden" name="amount<?php echo "_" . $counter; ?>" value="<?php echo $itemized_item[price] * $itemized_item[quantity]; ?>">
<?php 
		echo "\n";
		$counter++;
		}
	}
?>


	<h2 class="invoice_page_subheading">Billing Information</h2>
	<fieldset>
	<ul>
	<li>
	<label for="email">Email Address:</label>
	<input name="email" type="text"  size="40" maxlength="248" value="<?php echo $email_address; ?>">
	</li>
	<li>
	<label for="day_phone_a">Phone Number:</label>
	<input name="night_phone_a" style="width: 25px;" type="text"  size="3" maxlength="3" value="<?php echo $day_phone_a; ?>">-
	<input name="night_phone_b" style="width: 25px;"  type="text"  size="3" maxlength="3" value="<?php echo $day_phone_b; ?>">-
	<input name="night_phone_c" style="width: 30px;"  type="text"  size="4" maxlength="4" value="<?php echo $day_phone_c; ?>">
	</li>

	<li>
	<label for="first_name">First Name:</label>
	<input name="first_name" type="text"  size="20" maxlength="25" value="<?php echo $first_name; ?>">
	</li>
	<li>
	<label for="last_name">Last Name:</label>
	<input name="last_name" type="text"  size="20" maxlength="25" value="<?php echo $last_name; ?>">
	</li>

	<li>
	<label for="address1">Address:</label>
	<input name="address1" type="text"  size="20" maxlength="25" value="<?php echo $street_address; ?>">
	</li>

	<li>
	<label for="city">City:</label>
	<input name="city" type="text"  size="20" maxlength="25" value="<?php echo $city; ?>">
	</li>

	<li>
	<label for="state">State:</label>
	<input name="state" type="text"  size="20" maxlength="25" value="<?php echo $state; ?>">
	</li>

	<li>
	<label for="zip">Zip:</label>
	<input name="zip" type="text"  size="20" maxlength="25" value="<?php echo $zip; ?>">
	</li>

	<li>
	<label for="zip">Country:</label>
	<select id="country_code" name="country" class=""><option value="">-- Choose a Country --</option>
	<option value="US" selected>United States</option>
	<option value="AL">Albania</option>
	<option value="DZ">Algeria</option>

	<option value="AD">Andorra</option>
	<option value="AO">Angola</option>
	<option value="AI">Anguilla</option>
	<option value="AG">Antigua and Barbuda</option>
	<option value="AR">Argentina</option>
	<option value="AM">Armenia</option>
	<option value="AW">Aruba</option>
	<option value="AU">Australia</option>
	<option value="AT">Austria</option>

	<option value="AZ">Azerbaijan Republic</option>
	<option value="BS">Bahamas</option>
	<option value="BH">Bahrain</option>
	<option value="BB">Barbados</option>
	<option value="BE">Belgium</option>
	<option value="BZ">Belize</option>
	<option value="BJ">Benin</option>
	<option value="BM">Bermuda</option>
	<option value="BT">Bhutan</option>

	<option value="BO">Bolivia</option>
	<option value="BA">Bosnia and Herzegovina</option>
	<option value="BW">Botswana</option>
	<option value="BR">Brazil</option>
	<option value="VG">British Virgin Islands</option>
	<option value="BN">Brunei</option>
	<option value="BG">Bulgaria</option>
	<option value="BF">Burkina Faso</option>
	<option value="BI">Burundi</option>

	<option value="KH">Cambodia</option>
	<option value="CA">Canada</option>
	<option value="CV">Cape Verde</option>
	<option value="KY">Cayman Islands</option>
	<option value="TD">Chad</option>
	<option value="CL">Chile</option>
	<option value="C2">China</option>
	<option value="CO">Colombia</option>
	<option value="KM">Comoros</option>

	<option value="CK">Cook Islands</option>
	<option value="CR">Costa Rica</option>
	<option value="HR">Croatia</option>
	<option value="CY">Cyprus</option>
	<option value="CZ">Czech Republic</option>
	<option value="CD">Democratic Republic of the Congo</option>
	<option value="DK">Denmark</option>
	<option value="DJ">Djibouti</option>
	<option value="DM">Dominica</option>

	<option value="DO">Dominican Republic</option>
	<option value="EC">Ecuador</option>
	<option value="SV">El Salvador</option>
	<option value="ER">Eritrea</option>
	<option value="EE">Estonia</option>
	<option value="ET">Ethiopia</option>
	<option value="FK">Falkland Islands</option>
	<option value="FO">Faroe Islands</option>
	<option value="FM">Federated States of Micronesia</option>

	<option value="FJ">Fiji</option>
	<option value="FI">Finland</option>
	<option value="FR">France</option>
	<option value="GF">French Guiana</option>
	<option value="PF">French Polynesia</option>
	<option value="GA">Gabon Republic</option>
	<option value="GM">Gambia</option>
	<option value="DE">Germany</option>
	<option value="GI">Gibraltar</option>

	<option value="GR">Greece</option>
	<option value="GL">Greenland</option>
	<option value="GD">Grenada</option>
	<option value="GP">Guadeloupe</option>
	<option value="GT">Guatemala</option>
	<option value="GN">Guinea</option>
	<option value="GW">Guinea Bissau</option>
	<option value="GY">Guyana</option>
	<option value="HN">Honduras</option>

	<option value="HK">Hong Kong</option>
	<option value="HU">Hungary</option>
	<option value="IS">Iceland</option>
	<option value="IN">India</option>
	<option value="ID">Indonesia</option>
	<option value="IE">Ireland</option>
	<option value="IL">Israel</option>
	<option value="IT">Italy</option>
	<option value="JM">Jamaica</option>

	<option value="JP">Japan</option>
	<option value="JO">Jordan</option>
	<option value="KZ">Kazakhstan</option>
	<option value="KE">Kenya</option>
	<option value="KI">Kiribati</option>
	<option value="KW">Kuwait</option>
	<option value="KG">Kyrgyzstan</option>
	<option value="LA">Laos</option>
	<option value="LV">Latvia</option>

	<option value="LS">Lesotho</option>
	<option value="LI">Liechtenstein</option>
	<option value="LT">Lithuania</option>
	<option value="LU">Luxembourg</option>
	<option value="MG">Madagascar</option>
	<option value="MW">Malawi</option>
	<option value="MY">Malaysia</option>
	<option value="MV">Maldives</option>
	<option value="ML">Mali</option>

	<option value="MT">Malta</option>
	<option value="MH">Marshall Islands</option>
	<option value="MQ">Martinique</option>
	<option value="MR">Mauritania</option>
	<option value="MU">Mauritius</option>
	<option value="YT">Mayotte</option>
	<option value="MX">Mexico</option>
	<option value="MN">Mongolia</option>
	<option value="MS">Montserrat</option>

	<option value="MA">Morocco</option>
	<option value="MZ">Mozambique</option>
	<option value="NA">Namibia</option>
	<option value="NR">Nauru</option>
	<option value="NP">Nepal</option>
	<option value="NL">Netherlands</option>
	<option value="AN">Netherlands Antilles</option>
	<option value="NC">New Caledonia</option>
	<option value="NZ">New Zealand</option>

	<option value="NI">Nicaragua</option>
	<option value="NE">Niger</option>
	<option value="NU">Niue</option>
	<option value="NF">Norfolk Island</option>
	<option value="NO">Norway</option>
	<option value="OM">Oman</option>
	<option value="PW">Palau</option>
	<option value="PA">Panama</option>
	<option value="PG">Papua New Guinea</option>

	<option value="PE">Peru</option>
	<option value="PH">Philippines</option>
	<option value="PN">Pitcairn Islands</option>
	<option value="PL">Poland</option>
	<option value="PT">Portugal</option>
	<option value="QA">Qatar</option>
	<option value="CG">Republic of the Congo</option>
	<option value="RE">Reunion</option>
	<option value="RO">Romania</option>

	<option value="RU">Russia</option>
	<option value="RW">Rwanda</option>
	<option value="VC">Saint Vincent and the Grenadines</option>
	<option value="WS">Samoa</option>
	<option value="SM">San Marino</option>
	<option value="ST">São Tomé and Príncipe</option>
	<option value="SA">Saudi Arabia</option>
	<option value="SN">Senegal</option>
	<option value="SC">Seychelles</option>

	<option value="SL">Sierra Leone</option>
	<option value="SG">Singapore</option>
	<option value="SK">Slovakia</option>
	<option value="SI">Slovenia</option>
	<option value="SB">Solomon Islands</option>
	<option value="SO">Somalia</option>
	<option value="ZA">South Africa</option>
	<option value="KR">South Korea</option>
	<option value="ES">Spain</option>

	<option value="LK">Sri Lanka</option>
	<option value="SH">St. Helena</option>
	<option value="KN">St. Kitts and Nevis</option>
	<option value="LC">St. Lucia</option>
	<option value="PM">St. Pierre and Miquelon</option>
	<option value="SR">Suriname</option>
	<option value="SJ">Svalbard and Jan Mayen Islands</option>
	<option value="SZ">Swaziland</option>
	<option value="SE">Sweden</option>

	<option value="CH">Switzerland</option>
	<option value="TW">Taiwan</option>
	<option value="TJ">Tajikistan</option>
	<option value="TZ">Tanzania</option>
	<option value="TH">Thailand</option>
	<option value="TG">Togo</option>
	<option value="TO">Tonga</option>
	<option value="TT">Trinidad and Tobago</option>
	<option value="TN">Tunisia</option>

	<option value="TR">Turkey</option>
	<option value="TM">Turkmenistan</option>
	<option value="TC">Turks and Caicos Islands</option>
	<option value="TV">Tuvalu</option>
	<option value="UG">Uganda</option>
	<option value="UA">Ukraine</option>
	<option value="AE">United Arab Emirates</option>
	<option value="GB">United Kingdom</option>
	<option value="UY">Uruguay</option>

	<option value="VU">Vanuatu</option>
	<option value="VA">Vatican City State</option>
	<option value="VE">Venezuela</option>
	<option value="VN">Vietnam</option>
	<option value="WF">Wallis and Futuna Islands</option>
	<option value="YE">Yemen</option>
	<option value="ZM">Zambia</option></select>

	</li>

	
	<?php if($amount < 1) { ?>	
	<li>
	<label for="amount">Amount:</label>
	$<input name="amount" class="no_set_amount" type="input" value="">
	</li>
	<?php } ?>
	
	<li>
	<label for="submit">&nbsp;</label>
	<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_paynow_LG.gif" style="border:0; width:107px; height:26px;" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
	</li>

	</ul>
	</fieldset>

	</form>

</div>

</div>
<?php		
} 

//MerchantPlus option for later.

}
else return $content;
}

function wp_invoice_frontend_css()
{
if(is_page(get_option('wp_invoice_web_invoice_page')))  {
?>
<meta name="robots" content="noindex, nofollow" />

<?php
if(get_option('wp_invoice_use_css') == 'yes') {
?>
<style type="text/css" media="print">
.noprint {display:none; visibility: hidden; }
#invoice_page #invoice_overview {width: 100% !important;}
</style>
<style type="text/css" media="screen">
#invoice_page {padding: 10px; text-align: left;}
#invoice_page input.error, select.error  {border: 1px solid red !important;}
#invoice_page p.error {border: 1; color: red; font-weight:  bold;}
#invoice_page input {width: 200px; border:1px solid #86A9C7; }
#invoice_page .invoice_page_subheading {text-align:left;}
#invoice_page .invoice_page_subheading_gray {text-align:left; color: #ebebeb}

#invoice_page #invoice_overview {width: 350px; float:left; padding-right: 15px;  border-right:1px solid #DFDFDF}
#invoice_page #itemized_table {width: 100%;}
#invoice_page #itemized_table .alt_row {background: #EFEFEF}
#invoice_page #itemized_table .grand_total {font-weight:bold;}
#invoice_page #itemized_table .description_text {color: #9F9F9F}
#invoice_page #itemized_table th {background: #DFDFDF}
#invoice_page #itemized_table td, #itemized_table th {padding: 5px; text-align: left;}
#invoice_page #itemized_table .grand_total {border:  1px  #DFDFDF solid;}

#invoice_page #billing_overview {width: 390px; float:left; padding-left: 15px;}
#invoice_page #billing_overview .submit_button {}
#invoice_page #billing_overview p {padding: 0;}
#invoice_page #select_state {width: 200px; border:1px solid #86A9C7; }
#invoice_page legend span {  margin-top: 1.25em; }

#invoice_page pre {font-size: 12px; font-face:arial; width: 200px; }
#invoice_page #submit_button { border: 0;}
#invoice_page fieldset { position: relative;  float: left;  clear: both;  width: 100%;  margin: 5px 0 10px 0;  padding: 0 0 1em 0;  border-style: none;   } 
#invoice_page legend span {  position: absolute;  left: 0.74em;  top: 0;  margin-top: 0.5em;  font-size: 135%; }
#invoice_page .no_set_amount { width: 50px;}

#invoice_page fieldset ul {  padding:0;list-style-type: none !important; margin: 0; list-style-image }
#invoice_page fieldset li {  margin:0; padding-bottom: 0; float: left;   list-style: none; text-align:left; clear: left;   width: 100%;  }
#invoice_page fieldset label {  float: left;  width: 110px;  padding-top: 3px;margin-right: 15px;  text-align: right;padding-bottom: 10px;}
#invoice_page fieldset .submit {  float: none;  width: auto;  border-style: none;  padding-left: 12em;  background-color: transparent;  background-image: none;}
</style>
<?php } ?>
<?php
}
}

?>