<?php

class WP_Invoice_AlertPay {

	var $invoice;

	var $ip;

	var $ap_custemailaddress;
	var $ap_custfirstname;
	var $ap_custlastname;
	var $ap_custaddress;
	var $ap_custcity;
	var $ap_custstate;
	var $ap_custcountry;
	var $ap_custzip;

	var $ap_merchant;
	var $ap_referencenumber;
	var $ap_referencenumber;

	var $ap_totalamount;
	var $ap_currency;
	var $ap_status;
	var $ap_securitycode;
	var $ap_amount;
	var $ap_test;


    function wp_invoice_AlertPay($invoice_id) {
    	$this->invoice = new wp_invoice_GetInfo($invoice_id);
    }

    function _logFailure($ref) {
		wp_invoice_update_log($this->invoice->id,'alertpay_api_fail',"Failed AlertPay API request from {$this->ip}. REF: {$ref}. Serialized object ".serialize($this));
    }

    function _logSuccess($ref) {
		wp_invoice_update_log($this->invoice->id,'alertpay_api_success',"Successful AlertPay API request from {$this->ip}. REF: {$ref}");
    }

    function updateContactInfo() {
    	$user_id = $this->invoice->recipient('user_id');
		$updated = false;

		if (!empty($this->ap_custaddress)) {
			update_usermeta($user_id, 'first_name', $this->ap_custaddress);
			$updated = true;
		}
		if (!empty($this->ap_custfirstname)) {
			update_usermeta($user_id, 'last_name', $this->ap_custfirstname);
			$updated = true;
		}
		if (!empty($this->ap_custlastname)) {
			update_usermeta($user_id, 'streetaddress', $this->ap_custlastname);
			$updated = true;
		}
		if (!empty($this->ap_custzip)) {
			update_usermeta($user_id, 'zip', $this->ap_custzip);
			$updated = true;
		}
		if (!empty($this->ap_custstate)) {
			update_usermeta($user_id, 'state', $this->ap_custstate);
			$updated = true;
		}
		if (!empty($this->ap_custcity)) {
			update_usermeta($user_id, 'city', $this->ap_custcity);
			$updated = true;
		}
		if (!empty($this->ap_custcountry)) {
			update_usermeta($user_id, 'country', $this->ap_custcountry);
			$updated = true;
		}

		if ($updated) {
			$this->_logSuccess('Updated user information with details from AlertPay');
		}
    }

    function processRequest($ip, $request) {

    	$this->ip = $ip;

		$this->ap_custemailaddress = $request['ap_custemailaddress'];
		$this->ap_custfirstname = $request['ap_custfirstname'];
		$this->ap_custlastname = $request['ap_custlastname'];
		$this->ap_custaddress = $request['ap_custaddress'];
		$this->ap_custcity = $request['ap_custcity'];
		$this->ap_custstate = $request['ap_custstate'];
		$this->ap_custcountry = $request['ap_custcountry'];
		$this->ap_custzip = $request['ap_custzip'];

		$this->ap_merchant = $request['ap_merchant'];
		$this->ap_referencenumber = $request['ap_referencenumber'];
		$this->ap_totalamount = $request['ap_totalamount'];
		$this->ap_currency = $request['ap_currency'];

		$this->ap_amount = $request['ap_amount'];
		$this->ap_itemname = $request['ap_itemname'];

		$this->ap_securitycode = $request['ap_securitycode'];
		$this->ap_status = $request['ap_status'];
		$this->ap_test = $request['ap_test'];

    	if (!$this->invoice->id) {
    		$this->_logFailure('Invoice not found');

			header('HTTP/1.0 404 Not Found');
			header('Content-type: text/plain; charset=UTF-8');
			print 'Invoice not found';
			exit(0);
		}

		if (($this->ap_currency != wp_invoice_meta($this->invoice->id, 'wp_invoice_currency_code'))) {
			$this->_logFailure('Invalid currency');

			header('HTTP/1.0 400 Bad Request');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were not expecting you. REF: MB0';
			exit(0);
		}
		if (($this->ap_totalamount != $this->invoice->display('amount'))) {
			$this->_logFailure('Invalid amount');

			header('HTTP/1.0 400 Bad Request');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were not expecting you. REF: MB1';
			exit(0);
		}
		if (($this->ap_merchant != get_option('wp_invoice_moneybookers_address'))) {
			$this->_logFailure('Invalid pay_to_email');

			header('HTTP/1.0 400 Bad Request');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were not expecting you. REF: MB2';
			exit(0);
		}

		if ($this->ap_securitycode != get_option('wp_invoice_moneybookers_secret')) {
			$this->_logFailure('Invalid security code');

			header('HTTP/1.0 403 Forbidden');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were unable to authenticate the request';
			exit(0);
		}

		if (strtolower($this->ap_status) != "success") {
			$this->_logSuccess('Payment failed (status)');

			header('HTTP/1.0 200 OK');
			header('Content-type: text/plain; charset=UTF-8');
			print 'Thank you very much for letting us know. REF: Not success';
			exit(0);
		}

		if ($this->ap_test == 1) {
			if (get_option('wp_invoice_gateway_test_mode') == 'TRUE') {
				$this->_logFailure('Test payment');
				$this->updateContactInfo();
			}
		} else {
			$this->updateContactInfo();
			wp_invoice_mark_as_paid($this->invoice->id);
		}

		header('HTTP/1.0 200 OK');
		header('Content-type: text/plain; charset=UTF-8');
		print 'Thank you very much for letting us know';
		exit(0);
    }
}
