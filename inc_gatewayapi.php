<?php
//error_reporting(E_ALL);										//uncomment this to show more errors

$GatewaySettings['username']				= stripslashes(get_option("wp_invoice_gateway_username")); 	// 	Gateway Username
$GatewaySettings['tran_key']				= stripslashes(get_option("wp_invoice_gateway_tran_key")); 	//	Gateway Transaction Key - obtain it from the Gateway web interface

$GatewaySettings['AllowMC'] 				= TRUE;
$GatewaySettings['AllowVisa']				= TRUE;
$GatewaySettings['AllowAmex']				= TRUE;
$GatewaySettings['AllowDiscover']			= TRUE;
$GatewaySettings['AllowJCB']				= FALSE;
$GatewaySettings['AllowDiners']				= FALSE;
$GatewaySettings['AllowCarteBlanche']		= FALSE;
$GatewaySettings['AllowEnRoute']			= FALSE;
$GatewaySettings['AllowEChecks']			= FALSE;
$GatewaySettings['AllowInternational']		= FALSE;

// Gateway Configuration 
$GatewaySettings['version']					= "3.1"; 
$GatewaySettings['test_mode']				= stripslashes(get_option("wp_invoice_gateway_test_mode"));		// "TRUE"/"FALSE"
$GatewaySettings['AllowTestOverride']		=  FALSE; 		// allow transaction post data to set the test_mode value
$GatewaySettings['delim_data']				=  stripslashes(get_option("wp_invoice_gateway_delim_char")); 		// delimit the resopnse; configure this through the gateway settings interface
$GatewaySettings['delim_char']				=  stripslashes(get_option("wp_invoice_gateway_delim_char")); 		// set the delimiter you've configured through the gateway settings interface ,
$GatewaySettings['encap_char']				=  stripslashes(get_option("wp_invoice_gateway_encap_char")); 		// set the encapsulator you've configured through the gateway settings interface
$GatewaySettings['relay_response']			=  stripslashes(get_option("wp_invoice_gateway_relay_response"));  	// set the encapsulator you've configured through the gateway settings interface

// Email Settings
$GatewaySettings['email_customer']			= stripslashes(get_option("wp_invoice_gateway_email_customer")); 	// "TRUE"/"FALSE"
$GatewaySettings['merchant_email']			= stripslashes(get_option("wp_invoice_gateway_merchant_email"));			// Your Email address to send copy of receipts to, if you want them.
$GatewaySettings['header_email_receipt']	= "";
$GatewaySettings['footer_email_receipt']	= "";


$GatewaySettings['url']			 		= stripslashes(get_option("wp_invoice_gateway_url")); // NaviGate Url
$GatewaySettings['curl_location']		= "/usr/bin/curl";  
$GatewaySettings['MD5Hash']	= stripslashes(get_option("wp_invoice_gateway_email_customer"));



	function StripNonNumeric($field)
	{
		return(preg_replace("/[^\d]/", "", $field));
	}

	function StripNonMoney($field)
	{
		return(preg_replace("/[^\d.]/", "", $field));
	}
	
	function TRUEorFALSE($field)
	{
		$s = strtoupper($field);
		
		if($s != "TRUE" && $s != "FALSE")
			$s = "";
			
		return($s);
	}
	
	 /**
	  * function that attempts to figure out the credit card type based off the card number
	 **/
	 
	$_EnumCreditCardTypes = array (
		"Visa"			=> 0,
		"Mastercard"	=> 1,
		"Discover"		=> 2,
		"Amex"			=> 3,
		"DinersClub"	=> 4,
		"CarteBlanche"	=> 5,
		"EnRoute"		=> 6,
		"JCB"			=> 7,
		"Unknown"		=> 8,		
	);

	function EnumCardTypes($type)
	{
		global $_EnumCreditCardTypes;
		
		if(isset($_EnumCreditCardTypes[$type]))
			return($_EnumCreditCardTypes[$type]);
		else
			return($_EnumCreditCardTypes["Unknown"]);
	}
	
	function GetCardType (&$cardnum) {
		 $cardnum = StripNonNumeric($cardnum);
		 $length = strlen($cardnum);
		 if ($length == 16 && substr($cardnum, 0, 2) >= 51 && substr($cardnum, 0, 2) <= 55)
				 return(EnumCardTypes("Mastercard"));
		 else if (($length == 16 || $length == 13) && substr($cardnum, 0, 1) == 4)
				 return(EnumCardTypes("Visa"));
		 else if ($length == 15 && (substr($cardnum, 0, 2) == 34 || substr($cardnum, 0, 2) == 37))
				 return(EnumCardTypes("Amex"));
		 else if ($length == 16 && substr($cardnum, 0, 4) == 6011)
				 return(EnumCardTypes("Discover"));
		 else if ($length == 14 && (substr($cardnum, 0, 2) == 36 || substr($cardnum, 0, 2) == 38 || (substr($cardnum, 0, 3) >= 300 || substr($cardnum, 0, 3) <= 305)))
				 return(EnumCardTypes("DinersClub"));
		 else if ($length == 16 && ($cardnum[0] == '3'))
				 return(EnumCardTypes("JCB"));
		 else if ($length == 15 && (substr($cardnum, 0, 4) == 1800 || substr($cardnum, 0, 4) == 2131))
				 return(EnumCardTypes("JCB")); 
		 else if ($length == 15 && (substr($cardnum, 0, 4) == 2014 || substr($cardnum, 0, 4) == 2149))
				 return(EnumCardTypes("EnRoute"));
		 else if ($length == 14 && ($cardnum[0] == '3' && $cardnum[1] == '8'))
				return(EnumCardTypes("CarteBlanche"));
		 else
				return(EnumCardTypes("Unknown"));
	}

	function CheckLuhn10 ($num) {
		$num = StripNonNumeric($num);
		$num = strrev($num);
		$total = 0;

		for ($x = 0; $x < strlen($num); $x++) {
			$digit = substr($num, $x, 1);
			if ($x / 2 != floor ($x / 2)) {
				$digit *= 2;
				if (strlen($digit) == 2 )
					$digit = substr($digit, 0 , 1) + substr($digit, 1 , 1);
			}
			$total += $digit;
		}

		return ($total % 10 == 0);
	}

	function CheckCardAcceptance($cardType)
	{
		global $GatewaySettings;

		switch($type)
		{
			case EnumCardTypes("Visa"):
				return($GatewaySettings['AllowVisa']);
			case EnumCardTypes("Mastercard"):
				return($GatewaySettings['AllowMC']);
			case EnumCardTypes("Discover"):
				return($GatewaySettings['AllowDiscover']);
			case EnumCardTypes("Amex"):
				return($GatewaySettings['AllowAmex']);
			case EnumCardTypes("DinersClub"):
				return($GatewaySettings['AllowDiners']);
			case EnumCardTypes("CarteBlanche"):
				return($GatewaySettings['AllowCarteBlanche']);
			case EnumCardTypes("EnRoute"):
				return($GatewaySettings['AllowEnRoute']);
			case EnumCardTypes("JCB"):
				return($GatewaySettings['AllowJCB']);
		};
		
		return(false);
	}

	function VerifyCCNumber(&$cardnum, &$error)
	{
		$error = "";		
		
		if($cardnum === "")
			$error = "MISSING_CCNUMBER";
		else
		{
			if(!CheckLuhn10($cardnum))
				$error = "INVALID_CCNUMBER";
			else
			{
				$type = GetCardType($cardnum);
				
				if($type == EnumCardTypes("Unknown"))
					$error = "UNACCEPTED_CARD";
				else		
					if(!CheckCardAcceptance($type))	
						$error = "UNACCEPTED_CARD";
			}
		}
		
		return($error == "");
	}
	
	function VerifyAmount(&$amount, &$error)
	{
		$error = "";
		
		$amount = StripNonMoney($amount);

		if($amount === "")
			$error = "MISSING_AMOUNT";
		
		if($amount < 1.0)
			$error = "AMOUNT_TOOLOW";
		else if($amount >= 100000.00)
			$error = "AMOUNT_TOOHIGH";

		return($error == "");
	}
	
	function CheckMonthIsNotPast($month, $year)
	{
		//
		// Make sure your computer's date/time are set!
		//
		$currentDate = localtime(time(), true);

		// php localtime returns the year as the number of years since 1900
		$currentYear = $currentDate['tm_year'] + 1900;
		
		// php localtime returns the month number starting at 0
		$currentMonth = $currentDate['tm_mon'] + 1;
		
		if(strlen($year) == 2)
			$testYear = 2000 + $year; // These dates are supposed to be in the future.
		else
			$testYear = $year;
			
		if($testYear < $currentYear)
			return(false);
		else if(($testYear == $currentYear) && ($month < $currentMonth))
			return(false);
		else
			return(true);
	}
	
	function VerifyExpirationDate(&$expMonth, &$expYear, &$error)
	{
		$error = "";
		
		$expMonth = StripNonNumeric($expMonth);
		$expYear = StripNonNumeric($expYear);
		
		if($expMonth === "" || $expYear === "") 
			$error = "MISSING_EXPDATE";
		else
		{
			$yearLength = strlen($expYear);
			if(($yearLength != 2 && $yearLength != 4) || ($expMonth > 12) || ($expMonth < 0))
				$error = "INVALID_EXPDATE";
			else if(!CheckMonthIsNotPast($expMonth, $expYear))
				$error = "EXPIRED_EXPDATE";
		}
			
		return($error == "");
	}
	
	function VerifyCVVCode(&$cvv, $cardnum, &$error)
	{
		$error = "";
		
		$cvv = StripNonNumeric($cvv);
		$type = GetCardType($cardnum);
		
		if($type == EnumCardTypes("Amex"))
		{
			if(strlen($cvv) != 4)
				$error = "INVALID_AMEXCVV";
		}
		else if(strlen($cvv) != 3)
			$error = "INVALID_CVV";
			
		return($error == "");
	}
	
	function CheckRoutingNumber($routingNum)
	{
		$routingNum = StripNonNumeric($routingNum);
		$length = strlen($routingNum);

		if($length != 9)
			return(false);

		$checkDigit = 0;
		for ($i = 0; $i < $length; $i += 3)
		{
			$checkDigit += $routingNum[$i] * 3
			  +  $routingNum[$i + 1] * 7
			  +  $routingNum[$i + 2];
		}
		
		// If the resulting sum is an even multiple of ten (but not zero),
		// the aba routing number is good.
		return (($checkDigit != 0) && (($checkDigit % 10) == 0));
	}	
	
	function VerifyRoutingNumber(&$routingNum, $error)
	{
		$error = "";
		
		$routingNum = StripNonNumeric($routingNum);
		
		if($routingNum === "")
			$error = "MISSING_ROUTINGNUM";
		else if(!CheckRoutingNumber($routingNum))
			$error = "INVALID_ROUTINGNUM";
			
		return($error == "");
	}
	
	function VerifyCheckingAccountNumber(&$accountNum, $error)
	{
		$error = "";
		
		$accountNum = StripNonNumeric($accountNum);
		
		if($accountNum === "")
			$error = "MISSING_ACCOUNTNUM";
		
		return($error == "");
	}
	
	function SendHTTPPostData($data, $url, &$response, &$error)
	{
		global $GatewaySettings;
		
		$error = "";
		
		if(!$url)
			$error = "MISSING_URL";
		else if(!$data)
			$error = "MISSING_DATA";
		else
		{
		
			if (!extension_loaded('curl')) 
			{
				if($GatewaySettings['curl_location'] && is_file($GatewaySettings['curl_location']))
				{
					$data = escapeshellarg($data);
					$url = escapeshellarg($url);

					exec($GatewaySettings['curl_location'] . " -k -d $data $url", $response);
					$response = implode($GatewaySettings['delim_char'], $response);
				}
				else
					$error = "MISSING_CURL";
			}
			else 
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
//				curl_setopt($ch, CURLOPT_PROXY,"http://xx.xx.xx.xx:xxxx"); // for hosts that need a curl proxy, replace the xx's
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  // To work for Windows 2003 
				$response = curl_exec($ch);
				curl_close($ch);
			}
		}
		
		return($error == "");
	}
	
	
	
	$GLOBALS['_transient']['static']['GatewayResponse']->FieldNames = array (
		"ResponseCode"				=> 1,
		"ResponseSubcode"			=> 2,
		"ResponseReasonCode"		=> 3,
		"ResponseReasonText"		=> 4,
		"ApprovalCode"				=> 5,
		"AVSResultCode"				=> 6,
		"TransactionId"				=> 7,
		"InvoiceNumber"				=> 8,
		"Description"				=> 9,
		"Amount"					=> 10,
		"Method"					=> 11,
		"TransactionType"			=> 12,
		"CustomerId"				=> 13,
		"BillingFirstName"			=> 14,
		"BillingLastName"			=> 15,
		"BillingCompany"			=> 16,
		"BillingAddress"			=> 17,
		"BillingCity"				=> 18,
		"BillingState"				=> 19,
		"BillingZip"				=> 20,
		"BillingCountry"			=> 21,
		"BillingPhone"				=> 22,
		"BillingFax"				=> 23,
		"BillingEmail"				=> 24,
		"ShippingFirstName"			=> 25,
		"ShippingLastName"			=> 26,
		"ShippingCompany"			=> 27,
		"ShippingAddress"			=> 28,
		"ShippingCity"				=> 29,
		"ShippingState"				=> 30,
		"ShippingZip"				=> 31,
		"ShippingCountry"			=> 32,
		"TaxAmount"					=> 33,
		"DutyAmount"				=> 34,
		"FreightAmount"				=> 35,
		"TaxExemptOption"			=> 36,
		"PurchaseOrderNumber"		=> 37,
		"MD5Hash"					=> 38,
		"CVVResponseCode"			=> 39,
		"CAVVResponseCode"			=> 40,
		// Add reserved or merchant-defined fields here.
	);


	class GatewayResponse
	{
		var $response_string;
		var $response_array;
		
		var $response_code;
		var $response_text;
		var $transaction_id;
		
		var $FieldNames;
		
		function GatewayResponse($responseString, $delimiter, $encap)
		{
			$this->FieldNames = & $GLOBALS['_transient']['static']['GatewayResponse']->FieldNames;			
			
			$this->response_string = $responseString;
			
			$this->response_array = explode($delimiter, $this->response_string);
			
			//I need to strip the surrounding chars out of everything in the response array
			foreach($this->response_array as $key=>$value)
			{
				//Beginning char
				if($value[0] == $encap) $this->response_array[$key] = substr($this->response_array[$key], 1);
				//End char
				if($value[count($value)-1] == $encap) $this->response_array[$key] = substr($this->response_array[$key], 0, -1);
			}
		}
		
		function GetField($index)
		{
			if(is_numeric($index))
				$i = $index;
			else
				$i = $this->FieldNames[$index];
				
			$i--;
			

			if(($i === "") || ($i < 0) || ($i > count($this->FieldNames)))
				return("");
			
			
			
			return($this->response_array[$i]);
		}
		
		function IsApproved()
		{
			return($this->GetField("ResponseCode") == "1");
		}
		
		function IsDenied()
		{
			return($this->GetField("ResponseCode") == "2");
		}
		
		function IsError()
		{
			return($this->GetField("ResponseCode") == "3");
		}
		
		function GetAVSResultString()
		{
			$avsCode = $this->GetField("AVSResultCode");
			
			switch($avsCode)
			{
				case "A":
					return("Address (Street) matches, ZIP does not.");
				case "B":
					return("Address information not provided for Address Verification Service.");
				case "E":
					return("Address Verification error.");
				case "G":
					return("Non-U.S. Card Issuing Bank.");
				case "N":
					return("No Match on Address (Street) or ZIP.");
				case "P":
					return("Address Verification not applicable for this transaction.");
				case "R":
					return("Retry - System was unavailable or timed out.");
				case "S":
					return("Address Verification Service not supported by card issuer.");
				case "U":
					return("Address information is not available.");
				case "W":
					return("9 digit ZIP matches, Address (Street) does not.");
				case "X":
					return("Address (Street) and 9 digit ZIP match.");
				case "Y":
					return("Address (Street) and 5 digit ZIP match.");
				case "Z":
					return("5 digit ZIP matches, Address (Street) does not.");
				default:
					return("No Address Verification Response.");
			}
		}
		
		function GetCVVResultString()
		{
			$cvvCode = $this->GetField("CVVResponseCode");
			
			switch($cvvCode)
			{
				case "M":
					return("Match.");
				case "N":
					return("No Match.");
				case "P":
					return("Not Processed");
				case "S":
					return("Should have been on card, but wasn't sent.");	
				case "U":
					return("Card Bank unable to perform Card Code check.");
				default:
					return("No Card Code Verification Response.");
			}
		}
		
		function GetCAVVResultString()
		{
			$cavvCode = $this->GetField("CAVVResponseCode");
			
			switch($cavvCode)
			{
				case "0":
					return("Invalid Cardholder Authentication Verification data was submitted.");					
				case "1":
				case "7":
				case "9":
					return("Cardholder Authentication Verification failed.");
				case "2":
				case "A":
				case "8":
					return("Cardholder Authentication Verification succeeded.");
				case "3":
				case "4":
					return("Cardholder Authentication Verification could not be run because of a problem at the bank that issued this credit card.");
				case "5":
				case "6":
					return(""); // Reserved for future use.
				case "B":
					return("Cardholder Authentication Verification succeeded, but with 'information only'. There was no liability shift for the merchant.");
				default:
					return("No Cardholder Authentication Verification Response");					
			}
		}
		
		function VerifyMD5Hash($hashKey, $username, $amount)
		{
			$formattedAmount = StripNonMoney($amount);
			$formattedAmount = number_format($formattedAmount, 2, '.', '');
		
			$encryptedString = $hashKey . $username 
										. $this->GetField("TransactionId")
										. $formattedAmount;
										
//			print "COMPARING: " . $this->GetField("MD5Hash") . " to " . $encryptedString . " to " . md5($encryptedString) . "<BR>";
										
			return(strtoupper(md5($encryptedString)) == strtoupper($this->GetField("MD5Hash")));
		}
	}
	
	
	
	// The scary looking words here just make it so we don't have to redefine these strings for
	// every GatewayTransaction instance.
	// Edit these error strings as you see fit.

	$GLOBALS['_transient']['static']['GatewayTransaction']->ErrorStrings = array (
			"MISSING_LOGIN"     	=>      "The Gateway login information for this store is not configured properly.",
			"MISSING_AMOUNT"    	=>      "This transaction requires an amount field.",
			"MISSING_CCNUMBER"		=>		"This transaction requires a credit card number.",
			"MISSING_EXPDATE"		=>		"This transaction requires a credit card expiration date.", 
			"MISSING_ROUTINGNUM"	=>		"This transaction requires a checking account routing number.",
			"MISSING_ACCOUNTNUM"	=>		"This transaction requires a checking account number.",
			"MISSING_TRANSACTIONID"	=>		"This transaction requires a Transaction Id to reference.",
			"MISSING_CURL"			=>		"The socket library for this store is missing or is not configured properly.",
			"MISSING_URL"			=>		"The Gateway url for this store is not configured properly.",
			"MISSING_DATA"			=>		"There seems to be a problem, no data was received for authorization. You have not been charged anything. Please try again or contact support for help.",
			"INVALID_CCNUMBER"  	=>      "The credit card number entered is not valid.",
			"INVALID_EXPDATE"		=>		"The credit card expiration date entered is not valid.",
			"INVALID_ROUTINGNUM"	=>		"The checking account routing number entered is not valid.",
			"INVALID_AMEXCVV"		=>		"The security code for American Express cards is found on the front of the card on the right side, and should be 4 digits.",
			"INVALID_CVV"			=>		"The security code for your credit card should be found in the signature area on the back of the card, and should be 3 digits.",
			"INVALID_MD5HASH"		=>		"The MD5 Authentication string returned by the Payment Gateway does not match what is configured for this store.",
			"INVALID_TYPE"			=> 		"This payment library does not recognize the type of transaction you are attempting. Please contact support for help.",
			"INVALID_METHOD"		=>		"This payment library does not recognize the method of payment you are attempting. Please contact support for help.",
			"UNACCEPTED_CARD"   	=>      "We are sorry, we cannot accept this type of card. Please use a different one.",
			"UNACCEPTED_ECHECKS"	=>		"We are sorry, we cannot currently accept electronic checks as payment. Please select a different payment option.",
			"AMOUNT_TOOLOW"    		=>      "We are sorry, we cannot accept transactions less than $1.00.",
			"AMOUNT_TOOHIGH"    	=>      "We are sorry, we cannot accept transactions of $100,000 or more. Please split up your payments, or contact support for help.",
			"EXPIRED_EXPDATE"    	=>     	"We are sorry,  this credit card has expired. Please use a different card or change the expiration date if you have received an updated card.",
			);

	$GLOBALS['_transient']['static']['GatewayTransaction']->GatewayFieldNames = array (
		/*	(Gateway Field Name,		Max Length,		Pre-Processing Callback Function, 	Field name to send to GatewayTransaction Class) */
		array ("x_customer_organization_type",	1,	NULL,					"organization_type"),
		array ("x_drivers_license_num",			50,		NULL,					"license_num"),
		array ("x_drivers_license_state",		2,		NULL,					"license_state"),
		array ("x_drivers_license_dob",			10,		NULL,					"license_dob"),	
		array ("x_login",						20,		NULL,					"username"),
		array ("x_tran_key",					20,		NULL,					"tran_key"),
		array ("x_password",					10,		NULL,					"password"),
		array ("x_type",						18,		NULL,					"type"),	
		array ("x_bank_aba_code",				9,		"StripNonNumeric",		"check_aba_code"),
		array ("x_bank_acct_num",				20,		"StripNonNumeric",		"check_acct_num"),
		array ("x_bank_acct_type",				8,		NULL,					"check_acct_type"),
		array ("x_bank_name",					50,		NULL,					"check_bank_name"),
		array ("x_bank_acct_name",				40,		NULL,					"check_acct_name"),
		array ("x_echeck_type",					3,		NULL,					"check_type"),	
		array ("x_trans_id",					10,		"StripNonNumeric",		"tran_id"),	
		array ("x_version",						3,		NULL,					"version"),
		array ("x_test_request",				5,		NULL,					"test_mode"),
		array ("x_first_name",					50,		NULL,					"first_name"),
		array ("x_last_name",					50,		NULL,					"last_name"),
		array ("x_company",						50,		NULL,					"company"),
		array ("x_address",						60,		NULL,					"address"),
		array ("x_city",						40,		NULL,					"city"),
		array ("x_state",						40,		NULL,					"state"),
		array ("x_zip",							20,		NULL,					"zip"),
		array ("x_country",						60,		NULL,					"country"),
		array ("x_phone",						25,		NULL,					"phone"),
		array ("x_fax",							25,		NULL,					"fax"),
		array ("x_cust_id",						20,		NULL,					"customer_id"),
		array ("x_customer_ip",					15,		NULL,					"customer_ip"),
		array ("x_customer_tax_id",				9,		"StripNonNumeric",		"customer_ssn"),
		array ("x_email",						248,	NULL,					"email"),
		array ("x_email_customer",				5,		"TRUEorFALSE",			"email_customer"),
		array ("x_merchant_email",				248,	NULL,					"merchant_email"),
		array ("x_header_email_receipt",		1024,	NULL,					"header_email_receipt"),
		array ("x_footer_email_receipt",		1024,	NULL,					"footer_email_receipt"),		
		array ("x_invoice_num",					20,		NULL,					"invoice_num"),
		array ("x_description",					248,	NULL,					"description"),
		array ("x_ship_to_first_name",			50,		NULL,					"shipping_first_name"),
		array ("x_ship_to_last_name",			50,		NULL,					"shipping_last_name"),
		array ("x_ship_to_company",				50,		NULL,					"shipping_company"),
		array ("x_ship_to_address",				60,		NULL,					"shipping_address"),
		array ("x_ship_to_city",				40,		NULL,					"shipping_city"),
		array ("x_ship_to_state",				40,		NULL,					"shipping_state"),
		array ("x_ship_to_zip",					20,		NULL,					"shipping_zip"),
		array ("x_ship_to_country",				60,		NULL,					"shipping_country"),
		array ("x_amount",						15,		"StripNonMoney",		"amount"),
		array ("x_currency_code",				3,		NULL,					"currency_code"),
		array ("x_method",						6,		NULL,					"method"),
		array ("x_recurring_billing",			3,		NULL,					"recurring_billing"),
		array ("x_card_num",					22,		"StripNonNumeric",		"card_num"),
		array ("x_exp_date",					10,		"StripNonNumeric",		"exp_date"),
		array ("x_card_code",					4,		"StripNonNumeric",		"card_code"),
		array ("x_po_num",						25,		NULL,					"purchase_order_num"),
		array ("x_tax",							15,		"StripNonMoney",		"tax"),
		array ("x_tax_exempt",					5,		"TRUEorFALSE",			"tax_exempt"),
		array ("x_freight",						10,		"StripNonMoney",		"freight"),
		array ("x_duty",						10,		"StripNonMoney",		"duty"),
		array ("x_delim_data",					5,		"TRUEorFALSE",			"delim_data"),
		array ("x_delim_char",					1,		NULL,					"delim_char"),
		array ("x_encap_char",					1,		NULL,					"encap_char"),
		array ("x_relay_response",				5,		"TRUEorFALSE",			"relay_response"),
	);
				
				
	class GatewayTransaction
	{
		// API settings;
		var $type;
		var $method;
		var $version;
		var $username;
		var $tran_key;
		var $password;
		var $test_mode;		// TRUE/FALSE
		
		// Email Settings
		var $email_customer;
		var $merchant_email;
		var $header_email_receipt;
		var $footer_email_receipt;
		
		// Customer Information
		var $first_name;
		var $last_name;
		var $phone;
		var $fax;
		var $address;
		var $city;
		var $state;
		var $zip;
		var $country;
		var $company;
		var $email;
		var $customer_ip;
		var $customer_id;
	
		// Shipping Information
		var $shipping_company;
		var $shipping_first_name;
		var $shipping_last_name;
		var $shipping_address;
		var $shipping_city;
		var $shipping_state;
		var $shipping_zip;
		var $shipping_country;
			
		// Transaction Information
		var $amount;
		var $exp_month;
		var $exp_year;
		var $exp_date;
		var $card_code;
		var $card_num;
		var $description;
		var $tran_id;					// Gateway Transaction to reference for credit, void or prior_auth_capture
		var $purchase_order_num;		// Your own reference number - supposed to be the one given to customers
		var $invoice_num;				// Your own reference number
		var $tax;						// rarely used - tax is added into the $amount field	
		var $tax_exempt;				// rarely used - TRUE/FALSE
		var $freight;					// rarely used
		var $duty;						// rarely used
		var $recurring_billing;			// rarely used - TRUE/FALSE
		var $currency_code;				// rarely used - 3-letter currency like "USD" for US Dollars

	
		// E-Check Information
		var $check_type;
		var $check_acct_name;
		var $check_bank_name;
		var $check_acct_type;
		var $check_acct_num;
		var $check_aba_code;
	
		// Extra stuff - you most likely should not send these
		var $organization_type;
		var $license_state;
		var $license_num;
		var $license_dob;
		var $customer_ssn; 	
		
		// The API sets these so it can interpret the results
		var $delim_data;
		var $delim_char;
		var $encap_char;
		var $relay_response;
		
		var $ErrorStrings;
		var $GatewayFieldNames;
	
		function GatewayTransaction($variables, $ipaddress)
		{
			global $GatewaySettings;
			
			$this->ErrorStrings = & $GLOBALS['_transient']['static']['GatewayTransaction']->ErrorStrings;		
			$this->GatewayFieldNames = & $GLOBALS['_transient']['static']['GatewayTransaction']->GatewayFieldNames;					
			
			// Gateway options
			$this->method = "CC";   
			if($variables['method'])
				$this->method = $variables['method'];
				
			$this->type = "AUTH_CAPTURE"; 		
			if($variables['type'])
				$this->type = $variables['type'];
		
		
			// API Settings
			$this->username		= $GatewaySettings['username'];
			$this->tran_key		= $GatewaySettings['tran_key'];
			if($variables['password'])
				$this->password = $variables['password'];
			$this->version		= $GatewaySettings['version'];
			$this->test_mode	= $GatewaySettings['test_mode'];
			if($variables['test_mode'] && $GatewaySettings['AllowTestOverride'] == true)
				$this->test_mode = $variables['test_mode'];
			
			
			// Email Settings
			$this->email_customer 	= $GatewaySettings['email_customer'];
			if($variables['email_customer'])
				$this->email_customer = $variables['email_customer'];
			
			$this->merchant_email	= $GatewaySettings['merchant_email'];
			if($variables['merchant_email'])
				$this->merchant_email = $variables['merchant_email'];
			
			$this->header_email_receipt = $GatewaySettings['header_email_receipt'];
			if($variables['header_email_receipt'])
				$this->header_email_receipt = $variables['header_email_receipt'];
				
			$this->footer_email_receipt = $GatewaySettings['footer_email_receipt'];
			if($variables['footer_email_receipt'])
				$this->footer_email_receipt = $variables['footer_email_receipt'];
			
			
			// Contact Information
			$this->first_name	= $variables['first_name']; 
			$this->last_name	= $variables['last_name'];
			$this->phone		= $variables['phone'];
			$this->fax			= $variables['fax'];
			$this->address		= $variables['address'];
			$this->city			= $variables['city']; 
			$this->state		= $variables['state'];
			$this->zip			= $variables['zip'];
			$this->country		= $variables['country'];
			$this->company		= $variables['company'];
			$this->email		= $variables['email'];
			$this->customer_ip	= $ipaddress;
			
			
			// Transaction Information 
			$this->card_num				= StripNonNumeric($variables['card_num']);
			$this->exp_month			= StripNonNumeric($variables['exp_month']);
			$this->exp_year 			= StripNonNumeric($variables['exp_year']);
			$this->card_code			= StripNonNumeric($variables['card_code']);
			$this->amount				= StripNonMoney($variables['amount']);
			$this->customer_id			= $variables['customer_id']; // For your reference; not used by authnet.
			$this->invoice_num			= $variables['invoice_num'];
			$this->description			= $variables['description'];
			$this->tran_id				= StripNonNumeric($variables['tran_id']);
			$this->recurring_billing 	= $variables['recurring_billing'];
			$this->currency_code		= $variables['currency_code'];
			
			
			// Shipping Information 
			$this->shipping_company 	= $variables['shipping_company'];
			$this->shipping_first_name 	= $variables['shipping_first_name'];
			$this->shipping_last_name	= $variables['shipping_last_name'];
			$this->shipping_address 	= $variables['shipping_address'];
			$this->shipping_city 		= $variables['shipping_city'];
			$this->shipping_state 		= $variables['shipping_state'];
			$this->shipping_zip 		= $variables['shipping_zip'];
			$this->shipping_country		= $variables['shipping_country']; 
			
			
			// E-Check Information 
			$this->check_aba_code	= StripNonNumeric($variables['check_aba_code']);  	// Customer Routing Number
			$this->check_acct_num	= StripNonNumeric($variables['check_acct_num']);  	// Customer Account Number
			$this->check_acct_type	= $variables['check_acct_type']; 	// Customer Account Type ("CHECKING" or "SAVINGS")
			$this->check_bank_name	= $variables['check_bank_name'];  	// Customer Bank Name
			$this->check_acct_name	= $variables['check_acct_name']; 	// Customer Bank Account Owner
			$this->check_type		= $variables['check_type'];   		// Must be "WEB", if anything.
			
			
			// Optional Extra Data - rarely used
			$this->purchase_order_num	= $variables['purchase_order_num']; 
			$this->tax_exempt			= $variables['tax_exempt'];
			
			// The dollar amounts below must already be included in the 'amount' field. You would only 
			// put them here if you wanted the transaction to specifically note the different dollar amounts 
			// or if you were required to do so by your processor.
			// These are rarely used and generally unnecessary.
			$this->tax				= $variables['tax'];
			$this->freight			= $variables['freight'];
			$this->duty				= $variables['duty'];		
			
			// Shouldn't be used unless directed to
			$this->organization_type	= $variables['organization_type'];
			$this->license_state		= $variables['license_state'];
			$this->license_num			= $variables['license_num'];
			$this->license_dob			= $variables['license_dob'];
			$this->customer_ssn			= $variables['customer_ssn']; 				
		}
			
			
		function VerifyFields(&$errorCode)
		{
			global $GatewaySettings;
				
			// Check for required settings
			if(!$this->username || (!$this->tran_key && !$this->password))
			{
				$errorCode = "MISSING_LOGIN";
				return(false);
			}	
				
			// Check for required Transaction data
			if($this->type == "AUTH_CAPTURE" 
				|| $this->type == "AUTH_ONLY"
				|| $this->type == "CREDIT")
			{
				// Check for an amount
				if(!VerifyAmount($this->amount, $errorCode))
					return(false);
				
				// Check for a payment method
				if($this->type != "CREDIT")
				{
					if($this->method == "CC")
					{
						if(!VerifyCCNumber($this->card_num, $errorCode))
							return(false);
							
						if(!VerifyExpirationDate($this->exp_month, $this->exp_year, $errorCode))
							return(false);
						else
							$this->exp_date = $this->exp_month . $this->exp_year;
							
						if($this->card_code)
							if(!VerifyCVVCode($this->card_code, $this->card_num, $errorCode))
								return(false);
					}
					else if($this->method == "ECHECK")
					{
						if(!$GatewaySettings['AllowEChecks'])
						{
							$error = "UNACCEPTED_ECHECKS";
							return(false);
						}
						
						if(!VerifyRoutingNumber($this->check_aba_code, $error))
							return(false);
							
						if(!VerifyCheckingAccountNumber($this->check_acct_num, $error))
							return(false);
					}
					else
					{
						$error = "INVALID_METHOD";
						return(false);
					}
				}
				else
				{
					if(!$this->tran_id)
					{
						$error = "MISSING_TRANSACTIONID";
						return(false);
					}
				}
			}
			else if($this->type == "VOID"
					|| $this->type == "PRIOR_AUTH_CAPTURE")
			{
					if(!$this->tran_id)
					{
						$error = "MISSING_TRANSACTIONID";
						return(false);
					}				
			}
			else
			{
				$error = "INVALID_TYPE";
				return(false);
			}
			
			return(true);
		}		
			
			
		function CreatePostString()
		{
			global $GatewaySettings;
			
			$postString = "";
			
			$numFields = count($this->GatewayFieldNames);
			for($iField = 0; $iField < $numFields; $iField++)
			{
				list($gField, $maxLength, $callback, $classField) = $this->GatewayFieldNames[$iField];
				
				// Run special formatting functions
				if($callback)
					$value = $callback($this->$classField);
				else
					$value = $this->$classField;
					
				if($value !== "")
				{	
					// Truncate to maximum length for field
					$value = substr($value, 0, $maxLength);
					
					// Append value to request
					$postString .= $gField . "=" . rawurlencode($value) . "&";
				}
			}
			
			return($postString);
		}
		
		
		function ProcessTransaction(&$response, &$errorCode)
		{
			global $GatewaySettings;
			
			if(!$this->VerifyFields($errorCode))
				return(false);
				
			$postString = $this->CreatePostString();
			
			if(!SendHTTPPostData($postString, $GatewaySettings['url'], $response, $errorCode))
				return(false);
				
			return(true);
		}
		
		
		function GetErrorString($errorCode)
		{
			return($this->ErrorStrings[$errorCode]);
		}
	};

	
	        $ISO3166TwoToThree = array(
            'AF' => 'AFG',
            'AL' => 'ALB',
            'DZ' => 'DZA',
            'AS' => 'ASM',
            'AD' => 'AND',
            'AO' => 'AGO',
            'AI' => 'AIA',
            'AQ' => 'ATA',
            'AG' => 'ATG',
            'AR' => 'ARG',
            'AM' => 'ARM',
            'AW' => 'ABW',
            'AU' => 'AUS',
            'AT' => 'AUT',
            'AZ' => 'AZE',
            'BS' => 'BHS',
            'BH' => 'BHR',
            'BD' => 'BGD',
            'BB' => 'BRB',
            'BY' => 'BLR',
            'BE' => 'BEL',
            'BZ' => 'BLZ',
            'BJ' => 'BEN',
            'BM' => 'BMU',
            'BT' => 'BTN',
            'BO' => 'BOL',
            'BA' => 'BIH',
            'BW' => 'BWA',
            'BV' => 'BVT',
            'BR' => 'BRA',
            'IO' => 'IOT',
            'VG' => 'VGB',
            'BN' => 'BRN',
            'BG' => 'BGR',
            'BF' => 'BFA',
            'BI' => 'BDI',
            'KH' => 'KHM',
            'CM' => 'CMR',
            'CA' => 'CAN',
            'CV' => 'CPV',
            'KY' => 'CYM',
            'CF' => 'CAF',
            'TD' => 'TCD',
            'CL' => 'CHL',
            'CN' => 'CHN',
            'CX' => 'CXR',
            'CC' => 'CCK',
            'CO' => 'COL',
            'KM' => 'COM',
            'CD' => 'COD',
            'CG' => 'COG',
            'CK' => 'COK',
            'CR' => 'CRI',
            'CI' => 'CIV',
            'CU' => 'CUB',
            'CY' => 'CYP',
            'CZ' => 'CZE',
            'DK' => 'DNK',
            'DJ' => 'DJI',
            'DM' => 'DMA',
            'DO' => 'DOM',
            'TL' => 'TLS',
            'EC' => 'ECU',
            'EG' => 'EGY',
            'SV' => 'SLV',
            'GQ' => 'GNQ',
            'ER' => 'ERI',
            'EE' => 'EST',
            'ET' => 'ETH',
            'FO' => 'FRO',
            'FK' => 'FLK',
            'FJ' => 'FJI',
            'FI' => 'FIN',
            'FR' => 'FRA',
            'GF' => 'GUF',
            'PF' => 'PYF',
            'TF' => 'ATF',
            'GA' => 'GAB',
            'GM' => 'GMB',
            'GE' => 'GEO',
            'DE' => 'DEU',
            'GH' => 'GHA',
            'GI' => 'GIB',
            'GR' => 'GRC',
            'GL' => 'GRL',
            'GD' => 'GRD',
            'GP' => 'GLP',
            'GU' => 'GUM',
            'GT' => 'GTM',
            'GN' => 'GIN',
            'GW' => 'GNB',
            'GY' => 'GUY',
            'HT' => 'HTI',
            'HM' => 'HMD',
            'VA' => 'VAT',
            'HN' => 'HND',
            'HK' => 'HKG',
            'HR' => 'HRV',
            'HU' => 'HUN',
            'IS' => 'ISL',
            'IN' => 'IND',
            'ID' => 'IDN',
            'IR' => 'IRN',
            'IQ' => 'IRQ',
            'IE' => 'IRL',
            'IL' => 'ISR',
            'IT' => 'ITA',
            'JM' => 'JAM',
            'JP' => 'JPN',
            'JO' => 'JOR',
            'KZ' => 'KAZ',
            'KE' => 'KEN',
            'KI' => 'KIR',
            'KP' => 'PRK',
            'KR' => 'KOR',
            'KW' => 'KWT',
            'KG' => 'KGZ',
            'LA' => 'LAO',
            'LV' => 'LVA',
            'LB' => 'LBN',
            'LS' => 'LSO',
            'LR' => 'LBR',
            'LY' => 'LBY',
            'LI' => 'LIE',
            'LT' => 'LTU',
            'LU' => 'LUX',
            'MO' => 'MAC',
            'MK' => 'MKD',
            'MG' => 'MDG',
            'MW' => 'MWI',
            'MY' => 'MYS',
            'MV' => 'MDV',
            'ML' => 'MLI',
            'MT' => 'MLT',
            'MH' => 'MHL',
            'MQ' => 'MTQ',
            'MR' => 'MRT',
            'MU' => 'MUS',
            'YT' => 'MYT',
            'MX' => 'MEX',
            'FM' => 'FSM',
            'MD' => 'MDA',
            'MC' => 'MCO',
            'MN' => 'MNG',
            'MS' => 'MSR',
            'MA' => 'MAR',
            'MZ' => 'MOZ',
            'MM' => 'MMR',
            'NA' => 'NAM',
            'NR' => 'NRU',
            'NP' => 'NPL',
            'AN' => 'ANT',
            'NL' => 'NLD',
            'NC' => 'NCL',
            'NZ' => 'NZL',
            'NI' => 'NIC',
            'NE' => 'NER',
            'NG' => 'NGA',
            'NU' => 'NIU',
            'NF' => 'NFK',
            'MP' => 'MNP',
            'NO' => 'NOR',
            'OM' => 'OMN',
            'PK' => 'PAK',
            'PW' => 'PLW',
            'PS' => 'PSE',
            'PA' => 'PAN',
            'PG' => 'PNG',
            'PY' => 'PRY',
            'PE' => 'PER',
            'PH' => 'PHL',
            'PN' => 'PCN',
            'PL' => 'POL',
            'PT' => 'PRT',
            'PR' => 'PRI',
            'QA' => 'QAT',
            'RE' => 'REU',
            'RO' => 'ROU',
            'RU' => 'RUS',
            'RW' => 'RWA',
            'SH' => 'SHN',
            'KN' => 'KNA',
            'LC' => 'LCA',
            'PM' => 'SPM',
            'VC' => 'VCT',
            'WS' => 'WSM',
            'SM' => 'SMR',
            'ST' => 'STP',
            'SA' => 'SAU',
            'SN' => 'SEN',
            'SC' => 'SYC',
            'SL' => 'SLE',
            'SG' => 'SGP',
            'SK' => 'SVK',
            'SI' => 'SVN',
            'SB' => 'SLB',
            'SO' => 'SOM',
            'ZA' => 'ZAF',
            'GS' => 'SGS',
            'ES' => 'ESP',
            'LK' => 'LKA',
            'SD' => 'SDN',
            'SR' => 'SUR',
            'SJ' => 'SJM',
            'SZ' => 'SWZ',
            'SE' => 'SWE',
            'CH' => 'CHE',
            'SY' => 'SYR',
            'TW' => 'TWN',
            'TJ' => 'TJK',
            'TZ' => 'TZA',
            'TH' => 'THA',
            'TG' => 'TGO',
            'TK' => 'TKL',
            'TO' => 'TON',
            'TT' => 'TTO',
            'TN' => 'TUN',
            'TR' => 'TUR',
            'TM' => 'TKM',
            'TC' => 'TCA',
            'TV' => 'TUV',
            'VI' => 'VIR',
            'UG' => 'UGA',
            'UA' => 'UKR',
            'AE' => 'ARE',
            'GB' => 'GBR',
            'UM' => 'UMI',
            'US' => 'USA',
            'UY' => 'URY',
            'UZ' => 'UZB',
            'VU' => 'VUT',
            'VE' => 'VEN',
            'VN' => 'VNM',
            'WF' => 'WLF',
            'EH' => 'ESH',
            'YE' => 'YEM',
            'YU' => 'YUG',
            'ZM' => 'ZMB',
            'ZW' => 'ZWE'
        );
        
        $ISO3166TwoToName = array(
            'US' => 'United States of America',
            'AF' => 'Afghanistan',
            'AL' => 'Albania, People\'s Socialist Republic of',
            'DZ' => 'Algeria, People\'s Democratic Republic of',
            'AS' => 'American Samoa',
            'AD' => 'Andorra, Principality of',
            'AO' => 'Angola, Republic of',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica (the territory South of 60 deg S)',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina, Argentine Republic',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia, Commonwealth of',
            'AT' => 'Austria, Republic of',
            'AZ' => 'Azerbaijan, Republic of',
            'BS' => 'Bahamas, Commonwealth of the',
            'BH' => 'Bahrain, Kingdom of',
            'BD' => 'Bangladesh, People\'s Republic of',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium, Kingdom of',
            'BZ' => 'Belize',
            'BJ' => 'Benin, People\'s Republic of',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan, Kingdom of',
            'BO' => 'Bolivia, Republic of',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana, Republic of',
            'BV' => 'Bouvet Island (Bouvetoya)',
            'BR' => 'Brazil, Federative Republic of',
            'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria, People\'s Republic of',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi, Republic of',
            'KH' => 'Cambodia, Kingdom of',
            'CM' => 'Cameroon, United Republic of',
            'CA' => 'Canada',
            'CV' => 'Cape Verde, Republic of',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad, Republic of',
            'CL' => 'Chile, Republic of',
            'CN' => 'China, People\'s Republic of',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia, Republic of',
            'KM' => 'Comoros, Federal and Islamic Republic of',
            'CD' => 'Congo, Democratic Republic of',
            'CG' => 'Congo, People\'s Republic of',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica, Republic of',
            'CI' => 'Cote D\'Ivoire, Ivory Coast, Republic of the',
            'CU' => 'Cuba, Republic of',
            'CY' => 'Cyprus, Republic of',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark, Kingdom of',
            'DJ' => 'Djibouti, Republic of',
            'DM' => 'Dominica, Commonwealth of',
            'DO' => 'Dominican Republic',
            'TL' => 'Timor-Leste',
            'EC' => 'Ecuador, Republic of',
            'EG' => 'Egypt, Arab Republic of',
            'SV' => 'El Salvador, Republic of',
            'GQ' => 'Equatorial Guinea, Republic of',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FO' => 'Faeroe Islands',
            'FK' => 'Falkland Islands (Malvinas)',
            'FJ' => 'Fiji, Republic of the Fiji Islands',
            'FI' => 'Finland, Republic of',
            'FR' => 'France, French Republic',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon, Gabonese Republic',
            'GM' => 'Gambia, Republic of the',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana, Republic of',
            'GI' => 'Gibraltar',
            'GR' => 'Greece, Hellenic Republic',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadaloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala, Republic of',
            'GN' => 'Guinea, Revolutionary People\'s Rep\'c of',
            'GW' => 'Guinea-Bissau, Republic of',
            'GY' => 'Guyana, Republic of',
            'HT' => 'Haiti, Republic of',
            'HM' => 'Heard and McDonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras, Republic of',
            'HK' => 'Hong Kong, Special Administrative Region of China',
            'HR' => 'Hrvatska (Croatia)',
            'HU' => 'Hungary, Hungarian People\'s Republic',
            'IS' => 'Iceland, Republic of',
            'IN' => 'India, Republic of',
            'ID' => 'Indonesia, Republic of',
            'IR' => 'Iran, Islamic Republic of',
            'IQ' => 'Iraq, Republic of',
            'IE' => 'Ireland',
            'IL' => 'Israel, State of',
            'IT' => 'Italy, Italian Republic',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JO' => 'Jordan, Hashemite Kingdom of',
            'KZ' => 'Kazakhstan, Republic of',
            'KE' => 'Kenya, Republic of',
            'KI' => 'Kiribati, Republic of',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of',
            'KW' => 'Kuwait, State of',
            'KG' => 'Kyrgyz Republic',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon, Lebanese Republic',
            'LS' => 'Lesotho, Kingdom of',
            'LR' => 'Liberia, Republic of',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein, Principality of',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg, Grand Duchy of',
            'MO' => 'Macao, Special Administrative Region of China',
            'MK' => 'Macedonia, the former Yugoslav Republic of',
            'MG' => 'Madagascar, Republic of',
            'MW' => 'Malawi, Republic of',
            'MY' => 'Malaysia',
            'MV' => 'Maldives, Republic of',
            'ML' => 'Mali, Republic of',
            'MT' => 'Malta, Republic of',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania, Islamic Republic of',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico, United Mexican States',
            'FM' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco, Principality of',
            'MN' => 'Mongolia, Mongolian People\'s Republic',
            'MS' => 'Montserrat',
            'MA' => 'Morocco, Kingdom of',
            'MZ' => 'Mozambique, People\'s Republic of',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru, Republic of',
            'NP' => 'Nepal, Kingdom of',
            'AN' => 'Netherlands Antilles',
            'NL' => 'Netherlands, Kingdom of the',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua, Republic of',
            'NE' => 'Niger, Republic of the',
            'NG' => 'Nigeria, Federal Republic of',
            'NU' => 'Niue, Republic of',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway, Kingdom of',
            'OM' => 'Oman, Sultanate of',
            'PK' => 'Pakistan, Islamic Republic of',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama, Republic of',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay, Republic of',
            'PE' => 'Peru, Republic of',
            'PH' => 'Philippines, Republic of the',
            'PN' => 'Pitcairn Island',
            'PL' => 'Poland, Polish People\'s Republic',
            'PT' => 'Portugal, Portuguese Republic',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar, State of',
            'RE' => 'Reunion',
            'RO' => 'Romania, Socialist Republic of',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda, Rwandese Republic',
            'SH' => 'St. Helena',
            'KN' => 'St. Kitts and Nevis',
            'LC' => 'St. Lucia',
            'PM' => 'St. Pierre and Miquelon',
            'VC' => 'St. Vincent and the Grenadines',
            'WS' => 'Samoa, Independent State of',
            'SM' => 'San Marino, Republic of',
            'ST' => 'Sao Tome and Principe, Democratic Republic of',
            'SA' => 'Saudi Arabia, Kingdom of',
            'SN' => 'Senegal, Republic of',
            'SC' => 'Seychelles, Republic of',
            'SL' => 'Sierra Leone, Republic of',
            'SG' => 'Singapore, Republic of',
            'SK' => 'Slovakia (Slovak Republic)',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia, Somali Republic',
            'ZA' => 'South Africa, Republic of',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain, Spanish State',
            'LK' => 'Sri Lanka, Democratic Socialist Republic of',
            'SD' => 'Sudan, Democratic Republic of the',
            'SR' => 'Suriname, Republic of',
            'SJ' => 'Svalbard & Jan Mayen Islands',
            'SZ' => 'Swaziland, Kingdom of',
            'SE' => 'Sweden, Kingdom of',
            'CH' => 'Switzerland, Swiss Confederation',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan, Province of China',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania, United Republic of',
            'TH' => 'Thailand, Kingdom of',
            'TG' => 'Togo, Togolese Republic',
            'TK' => 'Tokelau (Tokelau Islands)',
            'TO' => 'Tonga, Kingdom of',
            'TT' => 'Trinidad and Tobago, Republic of',
            'TN' => 'Tunisia, Republic of',
            'TR' => 'Turkey, Republic of',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'VI' => 'US Virgin Islands',
            'UG' => 'Uganda, Republic of',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'UM' => 'United States Minor Outlying Islands',
            'UY' => 'Uruguay, Eastern Republic of',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela, Bolivarian Republic of',
            'VN' => 'Viet Nam, Socialist Republic of',
            'WF' => 'Wallis and Futuna Islands',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'YU' => 'Yugoslavia, Socialist Federal Republic of',
            'ZM' => 'Zambia, Republic of',
            'ZW' => 'Zimbabwe'
        );

	$ISO3166NameToTwo = array(
		"United States of America" => "US",
		"United States" => "US",
		"Afghanistan" => "AF",
		"Albania, People's Socialist Republic of" => "AL",
		"Algeria, People's Democratic Republic of" => "DZ",
		"American Samoa" => "AS",
		"Andorra, Principality of" => "AD",
		"Angola, Republic of" => "AO",
		"Anguilla" => "AI",
		"Antarctica (the territory South of 60 deg S)" => "AQ",
		"Antigua and Barbuda" => "AG",
		"Argentina, Argentine Republic" => "AR",
		"Armenia" => "AM",
		"Aruba" => "AW",
		"Australia, Commonwealth of" => "AU",
		"Austria, Republic of" => "AT",
		"Azerbaijan, Republic of" => "AZ",
		"Bahamas, Commonwealth of the" => "BS",
		"Bahrain, Kingdom of" => "BH",
		"Bangladesh, People's Republic of" => "BD",
		"Barbados" => "BB",
		"Belarus" => "BY",
		"Belgium, Kingdom of" => "BE",
		"Belize" => "BZ",
		"Benin, People's Republic of" => "BJ",
		"Bermuda" => "BM",
		"Bhutan, Kingdom of" => "BT",
		"Bolivia, Republic of" => "BO",
		"Bosnia and Herzegovina" => "BA",
		"Botswana, Republic of" => "BW",
		"Bouvet Island (Bouvetoya)" => "BV",
		"Brazil, Federative Republic of" => "BR",
		"British Indian Ocean Territory (Chagos Archipelago)" => "IO",
		"British Virgin Islands" => "VG",
		"Brunei Darussalam" => "BN",
		"Bulgaria, People's Republic of" => "BG",
		"Burkina Faso" => "BF",
		"Burundi, Republic of" => "BI",
		"Cambodia, Kingdom of" => "KH",
		"Cameroon, United Republic of" => "CM",
		"Canada" => "CA",
		"Cape Verde, Republic of" => "CV",
		"Cayman Islands" => "KY",
		"Central African Republic" => "CF",
		"Chad, Republic of" => "TD",
		"Chile, Republic of" => "CL",
		"China, People's Republic of" => "CN",
		"Christmas Island" => "CX",
		"Cocos (Keeling) Islands" => "CC",
		"Colombia, Republic of" => "CO",
		"Comoros, Federal and Islamic Republic of" => "KM",
		"Congo, Democratic Republic of" => "CD",
		"Congo, People's Republic of" => "CG",
		"Cook Islands" => "CK",
		"Costa Rica, Republic of" => "CR",
		"Cote D'Ivoire, Ivory Coast, Republic of the" => "CI",
		"Cuba, Republic of" => "CU",
		"Cyprus, Republic of" => "CY",
		"Czech Republic" => "CZ",
		"Denmark, Kingdom of" => "DK",
		"Djibouti, Republic of" => "DJ",
		"Dominica, Commonwealth of" => "DM",
		"Dominican Republic" => "DO",
		"Timor-Leste" => "TL",
		"Ecuador, Republic of" => "EC",
		"Egypt, Arab Republic of" => "EG",
		"El Salvador, Republic of" => "SV",
		"Equatorial Guinea, Republic of" => "GQ",
		"Eritrea" => "ER",
		"Estonia" => "EE",
		"Ethiopia" => "ET",
		"Faeroe Islands" => "FO",
		"Falkland Islands (Malvinas)" => "FK",
		"Fiji, Republic of the Fiji Islands" => "FJ",
		"Finland, Republic of" => "FI",
		"France, French Republic" => "FR",
		"French Guiana" => "GF",
		"French Polynesia" => "PF",
		"French Southern Territories" => "TF",
		"Gabon, Gabonese Republic" => "GA",
		"Gambia, Republic of the" => "GM",
		"Georgia" => "GE",
		"Germany" => "DE",
		"Ghana, Republic of" => "GH",
		"Gibraltar" => "GI",
		"Greece, Hellenic Republic" => "GR",
		"Greenland" => "GL",
		"Grenada" => "GD",
		"Guadaloupe" => "GP",
		"Guam" => "GU",
		"Guatemala, Republic of" => "GT",
		"Guinea, Revolutionary People's Rep'c of" => "GN",
		"Guinea-Bissau, Republic of" => "GW",
		"Guyana, Republic of" => "GY",
		"Haiti, Republic of" => "HT",
		"Heard and McDonald Islands" => "HM",
		"Holy See (Vatican City State)" => "VA",
		"Honduras, Republic of" => "HN",
		"Hong Kong, Special Administrative Region of China" => "HK",
		"Hrvatska (Croatia)" => "HR",
		"Hungary, Hungarian People's Republic" => "HU",
		"Iceland, Republic of" => "IS",
		"India, Republic of" => "IN",
		"Indonesia, Republic of" => "ID",
		"Iran, Islamic Republic of" => "IR",
		"Iraq, Republic of" => "IQ",
		"Ireland" => "IE",
		"Israel, State of" => "IL",
		"Italy, Italian Republic" => "IT",
		"Jamaica" => "JM",
		"Japan" => "JP",
		"Jordan, Hashemite Kingdom of" => "JO",
		"Kazakhstan, Republic of" => "KZ",
		"Kenya, Republic of" => "KE",
		"Kiribati, Republic of" => "KI",
		"Korea, Democratic People's Republic of" => "KP",
		"Korea, Republic of" => "KR",
		"Kuwait, State of" => "KW",
		"Kyrgyz Republic" => "KG",
		"Lao People's Democratic Republic" => "LA",
		"Latvia" => "LV",
		"Lebanon, Lebanese Republic" => "LB",
		"Lesotho, Kingdom of" => "LS",
		"Liberia, Republic of" => "LR",
		"Libyan Arab Jamahiriya" => "LY",
		"Liechtenstein, Principality of" => "LI",
		"Lithuania" => "LT",
		"Luxembourg, Grand Duchy of" => "LU",
		"Macao, Special Administrative Region of China" => "MO",
		"Macedonia, the former Yugoslav Republic of" => "MK",
		"Madagascar, Republic of" => "MG",
		"Malawi, Republic of" => "MW",
		"Malaysia" => "MY",
		"Maldives, Republic of" => "MV",
		"Mali, Republic of" => "ML",
		"Malta, Republic of" => "MT",
		"Marshall Islands" => "MH",
		"Martinique" => "MQ",
		"Mauritania, Islamic Republic of" => "MR",
		"Mauritius" => "MU",
		"Mayotte" => "YT",
		"Mexico, United Mexican States" => "MX",
		"Micronesia, Federated States of" => "FM",
		"Moldova, Republic of" => "MD",
		"Monaco, Principality of" => "MC",
		"Mongolia, Mongolian People's Republic" => "MN",
		"Montserrat" => "MS",
		"Morocco, Kingdom of" => "MA",
		"Mozambique, People's Republic of" => "MZ",
		"Myanmar" => "MM",
		"Namibia" => "NA",
		"Nauru, Republic of" => "NR",
		"Nepal, Kingdom of" => "NP",
		"Netherlands Antilles" => "AN",
		"Netherlands, Kingdom of the" => "NL",
		"New Caledonia" => "NC",
		"New Zealand" => "NZ",
		"Nicaragua, Republic of" => "NI",
		"Niger, Republic of the" => "NE",
		"Nigeria, Federal Republic of" => "NG",
		"Niue, Republic of" => "NU",
		"Norfolk Island" => "NF",
		"Northern Mariana Islands" => "MP",
		"Norway, Kingdom of" => "NO",
		"Oman, Sultanate of" => "OM",
		"Pakistan, Islamic Republic of" => "PK",
		"Palau" => "PW",
		"Palestinian Territory, Occupied" => "PS",
		"Panama, Republic of" => "PA",
		"Papua New Guinea" => "PG",
		"Paraguay, Republic of" => "PY",
		"Peru, Republic of" => "PE",
		"Philippines, Republic of the" => "PH",
		"Pitcairn Island" => "PN",
		"Poland, Polish People's Republic" => "PL",
		"Portugal, Portuguese Republic" => "PT",
		"Puerto Rico" => "PR",
		"Qatar, State of" => "QA",
		"Reunion" => "RE",
		"Romania, Socialist Republic of" => "RO",
		"Russian Federation" => "RU",
		"Rwanda, Rwandese Republic" => "RW",
		"St. Helena" => "SH",
		"St. Kitts and Nevis" => "KN",
		"St. Lucia" => "LC",
		"St. Pierre and Miquelon" => "PM",
		"St. Vincent and the Grenadines" => "VC",
		"Samoa, Independent State of" => "WS",
		"San Marino, Republic of" => "SM",
		"Sao Tome and Principe, Democratic Republic of" => "ST",
		"Saudi Arabia, Kingdom of" => "SA",
		"Senegal, Republic of" => "SN",
		"Seychelles, Republic of" => "SC",
		"Sierra Leone, Republic of" => "SL",
		"Singapore, Republic of" => "SG",
		"Slovakia (Slovak Republic)" => "SK",
		"Slovenia" => "SI",
		"Solomon Islands" => "SB",
		"Somalia, Somali Republic" => "SO",
		"South Africa, Republic of" => "ZA",
		"South Georgia and the South Sandwich Islands" => "GS",
		"Spain, Spanish State" => "ES",
		"Sri Lanka, Democratic Socialist Republic of" => "LK",
		"Sudan, Democratic Republic of the" => "SD",
		"Suriname, Republic of" => "SR",
		"Svalbard & Jan Mayen Islands" => "SJ",
		"Swaziland, Kingdom of" => "SZ",
		"Sweden, Kingdom of" => "SE",
		"Switzerland, Swiss Confederation" => "CH",
		"Syrian Arab Republic" => "SY",
		"Taiwan, Province of China" => "TW",
		"Tajikistan" => "TJ",
		"Tanzania, United Republic of" => "TZ",
		"Thailand, Kingdom of" => "TH",
		"Togo, Togolese Republic" => "TG",
		"Tokelau (Tokelau Islands)" => "TK",
		"Tonga, Kingdom of" => "TO",
		"Trinidad and Tobago, Republic of" => "TT",
		"Tunisia, Republic of" => "TN",
		"Turkey, Republic of" => "TR",
		"Turkmenistan" => "TM",
		"Turks and Caicos Islands" => "TC",
		"Tuvalu" => "TV",
		"US Virgin Islands" => "VI",
		"Uganda, Republic of" => "UG",
		"Ukraine" => "UA",
		"United Arab Emirates" => "AE",
		"United Kingdom" => "GB",
		"United States Minor Outlying Islands" => "UM",
		"Uruguay, Eastern Republic of" => "UY",
		"Uzbekistan" => "UZ",
		"Vanuatu" => "VU",
		"Venezuela, Bolivarian Republic of" => "VE",
		"Viet Nam, Socialist Republic of" => "VN",
		"Wallis and Futuna Islands" => "WF",
		"Western Sahara" => "EH",
		"Yemen" => "YE",
		"Yugoslavia, Socialist Federal Republic of" => "YU",
		"Zambia, Republic of" => "ZM",
		"Zimbabwe" => "ZW"
	);

        $ISO3166TwoToNumber = array(
            'AF' => '004',
            'AL' => '008',
            'DZ' => '012',
            'AS' => '016',
            'AD' => '020',
            'AO' => '024',
            'AI' => '660',
            'AQ' => '010',
            'AG' => '028',
            'AR' => '032',
            'AM' => '051',
            'AW' => '533',
            'AU' => '036',
            'AT' => '040',
            'AZ' => '031',
            'BS' => '044',
            'BH' => '048',
            'BD' => '050',
            'BB' => '052',
            'BY' => '112',
            'BE' => '056',
            'BZ' => '084',
            'BJ' => '204',
            'BM' => '060',
            'BT' => '064',
            'BO' => '068',
            'BA' => '070',
            'BW' => '072',
            'BV' => '074',
            'BR' => '076',
            'IO' => '086',
            'VG' => '092',
            'BN' => '096',
            'BG' => '100',
            'BF' => '854',
            'BI' => '108',
            'KH' => '116',
            'CM' => '120',
            'CA' => '124',
            'CV' => '132',
            'KY' => '136',
            'CF' => '140',
            'TD' => '148',
            'CL' => '152',
            'CN' => '156',
            'CX' => '162',
            'CC' => '166',
            'CO' => '170',
            'KM' => '174',
            'CD' => '180',
            'CG' => '178',
            'CK' => '184',
            'CR' => '188',
            'CI' => '384',
            'CU' => '192',
            'CY' => '196',
            'CZ' => '203',
            'DK' => '208',
            'DJ' => '262',
            'DM' => '212',
            'DO' => '214',
            'TL' => '626',
            'EC' => '218',
            'EG' => '818',
            'SV' => '222',
            'GQ' => '226',
            'ER' => '232',
            'EE' => '233',
            'ET' => '231',
            'FO' => '234',
            'FK' => '238',
            'FJ' => '242',
            'FI' => '246',
            'FR' => '250',
            'GF' => '254',
            'PF' => '258',
            'TF' => '260',
            'GA' => '266',
            'GM' => '270',
            'GE' => '268',
            'DE' => '276',
            'GH' => '288',
            'GI' => '292',
            'GR' => '300',
            'GL' => '304',
            'GD' => '308',
            'GP' => '312',
            'GU' => '316',
            'GT' => '320',
            'GN' => '324',
            'GW' => '624',
            'GY' => '328',
            'HT' => '332',
            'HM' => '334',
            'VA' => '336',
            'HN' => '340',
            'HK' => '344',
            'HR' => '191',
            'HU' => '348',
            'IS' => '352',
            'IN' => '356',
            'ID' => '360',
            'IR' => '364',
            'IQ' => '368',
            'IE' => '372',
            'IL' => '376',
            'IT' => '380',
            'JM' => '388',
            'JP' => '392',
            'JO' => '400',
            'KZ' => '398',
            'KE' => '404',
            'KI' => '296',
            'KP' => '408',
            'KR' => '410',
            'KW' => '414',
            'KG' => '417',
            'LA' => '418',
            'LV' => '428',
            'LB' => '422',
            'LS' => '426',
            'LR' => '430',
            'LY' => '434',
            'LI' => '438',
            'LT' => '440',
            'LU' => '442',
            'MO' => '446',
            'MK' => '807',
            'MG' => '450',
            'MW' => '454',
            'MY' => '458',
            'MV' => '462',
            'ML' => '466',
            'MT' => '470',
            'MH' => '584',
            'MQ' => '474',
            'MR' => '478',
            'MU' => '480',
            'YT' => '175',
            'MX' => '484',
            'FM' => '583',
            'MD' => '498',
            'MC' => '492',
            'MN' => '496',
            'MS' => '500',
            'MA' => '504',
            'MZ' => '508',
            'MM' => '104',
            'NA' => '516',
            'NR' => '520',
            'NP' => '524',
            'AN' => '530',
            'NL' => '528',
            'NC' => '540',
            'NZ' => '554',
            'NI' => '558',
            'NE' => '562',
            'NG' => '566',
            'NU' => '570',
            'NF' => '574',
            'MP' => '580',
            'NO' => '578',
            'OM' => '512',
            'PK' => '586',
            'PW' => '585',
            'PS' => '275',
            'PA' => '591',
            'PG' => '598',
            'PY' => '600',
            'PE' => '604',
            'PH' => '608',
            'PN' => '612',
            'PL' => '616',
            'PT' => '620',
            'PR' => '630',
            'QA' => '634',
            'RE' => '638',
            'RO' => '642',
            'RU' => '643',
            'RW' => '646',
            'SH' => '654',
            'KN' => '659',
            'LC' => '662',
            'PM' => '666',
            'VC' => '670',
            'WS' => '882',
            'SM' => '674',
            'ST' => '678',
            'SA' => '682',
            'SN' => '686',
            'SC' => '690',
            'SL' => '694',
            'SG' => '702',
            'SK' => '703',
            'SI' => '705',
            'SB' => '090',
            'SO' => '706',
            'ZA' => '710',
            'GS' => '239',
            'ES' => '724',
            'LK' => '144',
            'SD' => '736',
            'SR' => '740',
            'SJ' => '744',
            'SZ' => '748',
            'SE' => '752',
            'CH' => '756',
            'SY' => '760',
            'TW' => '158',
            'TJ' => '762',
            'TZ' => '834',
            'TH' => '764',
            'TG' => '768',
            'TK' => '772',
            'TO' => '776',
            'TT' => '780',
            'TN' => '788',
            'TR' => '792',
            'TM' => '795',
            'TC' => '796',
            'TV' => '798',
            'VI' => '850',
            'UG' => '800',
            'UA' => '804',
            'AE' => '784',
            'GB' => '826',
            'UM' => '581',
            'US' => '840',
            'UY' => '858',
            'UZ' => '860',
            'VU' => '548',
            'VE' => '862',
            'VN' => '704',
            'WF' => '876',
            'EH' => '732',
            'YE' => '887',
            'YU' => '891',
            'ZM' => '894',
            'ZW' => '716'
        );


        $ISO3166NumberToTwo = array(
            '004' => 'AF',
            '008' => 'AL',
            '012' => 'DZ',
            '016' => 'AS',
            '020' => 'AD',
            '024' => 'AO',
            '660' => 'AI',
            '010' => 'AQ',
            '028' => 'AG',
            '032' => 'AR',
            '051' => 'AM',
            '533' => 'AW',
            '036' => 'AU',
            '040' => 'AT',
            '031' => 'AZ',
            '044' => 'BS',
            '048' => 'BH',
            '050' => 'BD',
            '052' => 'BB',
            '112' => 'BY',
            '056' => 'BE',
            '084' => 'BZ',
            '204' => 'BJ',
            '060' => 'BM',
            '064' => 'BT',
            '068' => 'BO',
            '070' => 'BA',
            '072' => 'BW',
            '074' => 'BV',
            '076' => 'BR',
            '086' => 'IO',
            '092' => 'VG',
            '096' => 'BN',
            '100' => 'BG',
            '854' => 'BF',
            '108' => 'BI',
            '116' => 'KH',
            '120' => 'CM',
            '124' => 'CA',
            '132' => 'CV',
            '136' => 'KY',
            '140' => 'CF',
            '148' => 'TD',
            '152' => 'CL',
            '156' => 'CN',
            '162' => 'CX',
            '166' => 'CC',
            '170' => 'CO',
            '174' => 'KM',
            '180' => 'CD',
            '178' => 'CG',
            '184' => 'CK',
            '188' => 'CR',
            '384' => 'CI',
            '192' => 'CU',
            '196' => 'CY',
            '203' => 'CZ',
            '208' => 'DK',
            '262' => 'DJ',
            '212' => 'DM',
            '214' => 'DO',
            '626' => 'TL',
            '218' => 'EC',
            '818' => 'EG',
            '222' => 'SV',
            '226' => 'GQ',
            '232' => 'ER',
            '233' => 'EE',
            '231' => 'ET',
            '234' => 'FO',
            '238' => 'FK',
            '242' => 'FJ',
            '246' => 'FI',
            '250' => 'FR',
            '254' => 'GF',
            '258' => 'PF',
            '260' => 'TF',
            '266' => 'GA',
            '270' => 'GM',
            '268' => 'GE',
            '276' => 'DE',
            '288' => 'GH',
            '292' => 'GI',
            '300' => 'GR',
            '304' => 'GL',
            '308' => 'GD',
            '312' => 'GP',
            '316' => 'GU',
            '320' => 'GT',
            '324' => 'GN',
            '624' => 'GW',
            '328' => 'GY',
            '332' => 'HT',
            '334' => 'HM',
            '336' => 'VA',
            '340' => 'HN',
            '344' => 'HK',
            '191' => 'HR',
            '348' => 'HU',
            '352' => 'IS',
            '356' => 'IN',
            '360' => 'ID',
            '364' => 'IR',
            '368' => 'IQ',
            '372' => 'IE',
            '376' => 'IL',
            '380' => 'IT',
            '388' => 'JM',
            '392' => 'JP',
            '400' => 'JO',
            '398' => 'KZ',
            '404' => 'KE',
            '296' => 'KI',
            '408' => 'KP',
            '410' => 'KR',
            '414' => 'KW',
            '417' => 'KG',
            '418' => 'LA',
            '428' => 'LV',
            '422' => 'LB',
            '426' => 'LS',
            '430' => 'LR',
            '434' => 'LY',
            '438' => 'LI',
            '440' => 'LT',
            '442' => 'LU',
            '446' => 'MO',
            '807' => 'MK',
            '450' => 'MG',
            '454' => 'MW',
            '458' => 'MY',
            '462' => 'MV',
            '466' => 'ML',
            '470' => 'MT',
            '584' => 'MH',
            '474' => 'MQ',
            '478' => 'MR',
            '480' => 'MU',
            '175' => 'YT',
            '484' => 'MX',
            '583' => 'FM',
            '498' => 'MD',
            '492' => 'MC',
            '496' => 'MN',
            '500' => 'MS',
            '504' => 'MA',
            '508' => 'MZ',
            '104' => 'MM',
            '516' => 'NA',
            '520' => 'NR',
            '524' => 'NP',
            '530' => 'AN',
            '528' => 'NL',
            '540' => 'NC',
            '554' => 'NZ',
            '558' => 'NI',
            '562' => 'NE',
            '566' => 'NG',
            '570' => 'NU',
            '574' => 'NF',
            '580' => 'MP',
            '578' => 'NO',
            '512' => 'OM',
            '586' => 'PK',
            '585' => 'PW',
            '275' => 'PS',
            '591' => 'PA',
            '598' => 'PG',
            '600' => 'PY',
            '604' => 'PE',
            '608' => 'PH',
            '612' => 'PN',
            '616' => 'PL',
            '620' => 'PT',
            '630' => 'PR',
            '634' => 'QA',
            '638' => 'RE',
            '642' => 'RO',
            '643' => 'RU',
            '646' => 'RW',
            '654' => 'SH',
            '659' => 'KN',
            '662' => 'LC',
            '666' => 'PM',
            '670' => 'VC',
            '882' => 'WS',
            '674' => 'SM',
            '678' => 'ST',
            '682' => 'SA',
            '686' => 'SN',
            '690' => 'SC',
            '694' => 'SL',
            '702' => 'SG',
            '703' => 'SK',
            '705' => 'SI',
            '090' => 'SB',
            '706' => 'SO',
            '710' => 'ZA',
            '239' => 'GS',
            '724' => 'ES',
            '144' => 'LK',
            '736' => 'SD',
            '740' => 'SR',
            '744' => 'SJ',
            '748' => 'SZ',
            '752' => 'SE',
            '756' => 'CH',
            '760' => 'SY',
            '158' => 'TW',
            '762' => 'TJ',
            '834' => 'TZ',
            '764' => 'TH',
            '768' => 'TG',
            '772' => 'TK',
            '776' => 'TO',
            '780' => 'TT',
            '788' => 'TN',
            '792' => 'TR',
            '795' => 'TM',
            '796' => 'TC',
            '798' => 'TV',
            '850' => 'VI',
            '800' => 'UG',
            '804' => 'UA',
            '784' => 'AE',
            '826' => 'GB',
            '581' => 'UM',
            '840' => 'US',
            '858' => 'UY',
            '860' => 'UZ',
            '548' => 'VU',
            '862' => 'VE',
            '704' => 'VN',
            '876' => 'WF',
            '732' => 'EH',
            '887' => 'YE',
            '891' => 'YU',
            '894' => 'ZM',
            '716' => 'ZW'
        );

        $ISO3166ThreeToTwo = array(
            'AFG' => 'AF',
            'ALB' => 'AL',
            'DZA' => 'DZ',
            'ASM' => 'AS',
            'AND' => 'AD',
            'AGO' => 'AO',
            'AIA' => 'AI',
            'ATA' => 'AQ',
            'ATG' => 'AG',
            'ARG' => 'AR',
            'ARM' => 'AM',
            'ABW' => 'AW',
            'AUS' => 'AU',
            'AUT' => 'AT',
            'AZE' => 'AZ',
            'BHS' => 'BS',
            'BHR' => 'BH',
            'BGD' => 'BD',
            'BRB' => 'BB',
            'BLR' => 'BY',
            'BEL' => 'BE',
            'BLZ' => 'BZ',
            'BEN' => 'BJ',
            'BMU' => 'BM',
            'BTN' => 'BT',
            'BOL' => 'BO',
            'BIH' => 'BA',
            'BWA' => 'BW',
            'BVT' => 'BV',
            'BRA' => 'BR',
            'IOT' => 'IO',
            'VGB' => 'VG',
            'BRN' => 'BN',
            'BGR' => 'BG',
            'BFA' => 'BF',
            'BDI' => 'BI',
            'KHM' => 'KH',
            'CMR' => 'CM',
            'CAN' => 'CA',
            'CPV' => 'CV',
            'CYM' => 'KY',
            'CAF' => 'CF',
            'TCD' => 'TD',
            'CHL' => 'CL',
            'CHN' => 'CN',
            'CXR' => 'CX',
            'CCK' => 'CC',
            'COL' => 'CO',
            'COM' => 'KM',
            'COD' => 'CD',
            'COG' => 'CG',
            'COK' => 'CK',
            'CRI' => 'CR',
            'CIV' => 'CI',
            'CUB' => 'CU',
            'CYP' => 'CY',
            'CZE' => 'CZ',
            'DNK' => 'DK',
            'DJI' => 'DJ',
            'DMA' => 'DM',
            'DOM' => 'DO',
            'TLS' => 'TL',
            'ECU' => 'EC',
            'EGY' => 'EG',
            'SLV' => 'SV',
            'GNQ' => 'GQ',
            'ERI' => 'ER',
            'EST' => 'EE',
            'ETH' => 'ET',
            'FRO' => 'FO',
            'FLK' => 'FK',
            'FJI' => 'FJ',
            'FIN' => 'FI',
            'FRA' => 'FR',
            'GUF' => 'GF',
            'PYF' => 'PF',
            'ATF' => 'TF',
            'GAB' => 'GA',
            'GMB' => 'GM',
            'GEO' => 'GE',
            'DEU' => 'DE',
            'GHA' => 'GH',
            'GIB' => 'GI',
            'GRC' => 'GR',
            'GRL' => 'GL',
            'GRD' => 'GD',
            'GLP' => 'GP',
            'GUM' => 'GU',
            'GTM' => 'GT',
            'GIN' => 'GN',
            'GNB' => 'GW',
            'GUY' => 'GY',
            'HTI' => 'HT',
            'HMD' => 'HM',
            'VAT' => 'VA',
            'HND' => 'HN',
            'HKG' => 'HK',
            'HRV' => 'HR',
            'HUN' => 'HU',
            'ISL' => 'IS',
            'IND' => 'IN',
            'IDN' => 'ID',
            'IRN' => 'IR',
            'IRQ' => 'IQ',
            'IRL' => 'IE',
            'ISR' => 'IL',
            'ITA' => 'IT',
            'JAM' => 'JM',
            'JPN' => 'JP',
            'JOR' => 'JO',
            'KAZ' => 'KZ',
            'KEN' => 'KE',
            'KIR' => 'KI',
            'PRK' => 'KP',
            'KOR' => 'KR',
            'KWT' => 'KW',
            'KGZ' => 'KG',
            'LAO' => 'LA',
            'LVA' => 'LV',
            'LBN' => 'LB',
            'LSO' => 'LS',
            'LBR' => 'LR',
            'LBY' => 'LY',
            'LIE' => 'LI',
            'LTU' => 'LT',
            'LUX' => 'LU',
            'MAC' => 'MO',
            'MKD' => 'MK',
            'MDG' => 'MG',
            'MWI' => 'MW',
            'MYS' => 'MY',
            'MDV' => 'MV',
            'MLI' => 'ML',
            'MLT' => 'MT',
            'MHL' => 'MH',
            'MTQ' => 'MQ',
            'MRT' => 'MR',
            'MUS' => 'MU',
            'MYT' => 'YT',
            'MEX' => 'MX',
            'FSM' => 'FM',
            'MDA' => 'MD',
            'MCO' => 'MC',
            'MNG' => 'MN',
            'MSR' => 'MS',
            'MAR' => 'MA',
            'MOZ' => 'MZ',
            'MMR' => 'MM',
            'NAM' => 'NA',
            'NRU' => 'NR',
            'NPL' => 'NP',
            'ANT' => 'AN',
            'NLD' => 'NL',
            'NCL' => 'NC',
            'NZL' => 'NZ',
            'NIC' => 'NI',
            'NER' => 'NE',
            'NGA' => 'NG',
            'NIU' => 'NU',
            'NFK' => 'NF',
            'MNP' => 'MP',
            'NOR' => 'NO',
            'OMN' => 'OM',
            'PAK' => 'PK',
            'PLW' => 'PW',
            'PSE' => 'PS',
            'PAN' => 'PA',
            'PNG' => 'PG',
            'PRY' => 'PY',
            'PER' => 'PE',
            'PHL' => 'PH',
            'PCN' => 'PN',
            'POL' => 'PL',
            'PRT' => 'PT',
            'PRI' => 'PR',
            'QAT' => 'QA',
            'REU' => 'RE',
            'ROU' => 'RO',
            'RUS' => 'RU',
            'RWA' => 'RW',
            'SHN' => 'SH',
            'KNA' => 'KN',
            'LCA' => 'LC',
            'SPM' => 'PM',
            'VCT' => 'VC',
            'WSM' => 'WS',
            'SMR' => 'SM',
            'STP' => 'ST',
            'SAU' => 'SA',
            'SEN' => 'SN',
            'SYC' => 'SC',
            'SLE' => 'SL',
            'SGP' => 'SG',
            'SVK' => 'SK',
            'SVN' => 'SI',
            'SLB' => 'SB',
            'SOM' => 'SO',
            'ZAF' => 'ZA',
            'SGS' => 'GS',
            'ESP' => 'ES',
            'LKA' => 'LK',
            'SDN' => 'SD',
            'SUR' => 'SR',
            'SJM' => 'SJ',
            'SWZ' => 'SZ',
            'SWE' => 'SE',
            'CHE' => 'CH',
            'SYR' => 'SY',
            'TWN' => 'TW',
            'TJK' => 'TJ',
            'TZA' => 'TZ',
            'THA' => 'TH',
            'TGO' => 'TG',
            'TKL' => 'TK',
            'TON' => 'TO',
            'TTO' => 'TT',
            'TUN' => 'TN',
            'TUR' => 'TR',
            'TKM' => 'TM',
            'TCA' => 'TC',
            'TUV' => 'TV',
            'VIR' => 'VI',
            'UGA' => 'UG',
            'UKR' => 'UA',
            'ARE' => 'AE',
            'GBR' => 'GB',
            'UMI' => 'UM',
            'USA' => 'US',
            'URY' => 'UY',
            'UZB' => 'UZ',
            'VUT' => 'VU',
            'VEN' => 'VE',
            'VNM' => 'VN',
            'WLF' => 'WF',
            'ESH' => 'EH',
            'YEM' => 'YE',
            'YUG' => 'YU',
            'ZMB' => 'ZM',
            'ZWE' => 'ZW',
        );


	function print_ISOSelectOptions($array, $reverse, $checked)
	{
	    while(list($key, $value) = each($array))
	    {
		$CHECKED="";
		if($reverse) {
		    if($key == $checked)
			$CHECKED = "SELECTED";
		    $output .= "<option value=\"$key\" $CHECKED>$value</option>";
		 } else {
		    if($value == $checked)
			$CHECKED = "SELECTED";
		    $output .= "<option value=\"$value\" $CHECKED>$key</option>";
 		 }
	    }

		print $output;
	} 

	function ISO3166GetFullName($code)
	{
		global $ISO3166TwoToName, $ISO3166NumberToTwo, $ISO3166ThreeToTwo;

		if(strlen($code) == 2) {
		    return($ISO3166TwoToName[$code]);
		}
		else if( strlen($code) == 3)
		{
		    if(is_numeric($code))
			return($ISO3166TwoToName[$ISO3166NumberToTwo[$code]]);
		    else
			return($ISO3166TwoToName[$ISO3166ThreeToTwo[$code]]);
		}
		else if( strlen($code) > 3)
		{
		    return($code);
		}
		else
		    return("");
	}

	function ISO3166GetNumeric($code)
	{
		global $ISO3166TwoToName, $ISO3166TwoToNumber, $ISO3166ThreeToTwo, $ISO3166NameToTwo;

		if(strlen($code) == 2) {
		    return($ISO3166TwoToNumber[$code]);
		}
		else if( strlen($code) == 3)
		{
		    if(is_numeric($code))
			return($code);
		    else
			return($ISO3166TwoToNumber[$ISO3166ThreeToTwo[$code]]);
		}
		else if( strlen($code) > 3)
		{
		    return($ISO3166TwoToNumber[$ISO3166NameToTwo[$code]]);
		}
		else 
		    return("");
	}

	function ISO3166GetTwo($code)
	{
		global $ISO3166TwoToName, $ISO3166NumberToTwo, $ISO3166ThreeToTwo, $ISO3166NameToTwo;

		if(strlen($code) == 2) {
		    return($code);
		}
		else if( strlen($code) == 3)
		{
		    if(is_numeric($code)) {
			return($ISO3166NumberToTwo[$code]);
		    }else
			return($ISO3166ThreeToTwo[$code]);
		}
		else if( strlen($code) > 3)
		{
		    return($ISO3166NameToTwo[$code]);
		}
		else 
		    return("");
	}




?>