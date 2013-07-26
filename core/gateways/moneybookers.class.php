<?php

class WP_Invoice_Moneybookers {

	var $invoice;

	var $ip;
	var $int_ip;

	var $pay_to_email;
	var $pay_from_email;
	var $merchant_id;
	var $mb_transaction_id;
	var $transaction_id;

	var $mb_amount;
	var $mb_currency;
	var $status;
	var $md5sig;
	var $amount;
	var $currency;
	
	var $recurring_payment_type;
	var $recurring_payment_id;

    function WP_Invoice_Moneybookers($invoice_id) {
    	$this->invoice = new wp_invoice_GetInfo($invoice_id);
    }

    function _logFailure($ref) {
		wp_invoice_update_log($this->invoice->id,'mb_api_fail',"Failed Moneybookers API request from {$this->ip}. REF: {$ref}. Serialized object ".serialize($this));
    }

    function _logSuccess($ref) {
		wp_invoice_update_log($this->invoice->id,'mb_api_success',"Successful Moneybookers API request from {$this->ip}. REF: {$ref}");
    }

    function _quadIpToInt($ip) {
		$ip_parts = split('\.', $ip);
        $numeric_ip = 0;

        foreach ($ip_parts as $ip_part) {
        	$numeric_ip=($numeric_ip*256)+intval($ip_part);
        }

        return $numeric_ip;
    }

    function _allowedIp() {
    	$allowed_ips = get_option('wp_invoice_moneybookers_ip');
    	$this->int_ip = $this->_quadIpToInt($this->ip);

    	$ip_ranges = split(',', $allowed_ips);

    	foreach ($ip_ranges as $ip_range) {
    		list($start_ips,$end_ips) = split('-', $ip_range);

    		$start_ip = $this->_quadIpToInt($start_ips);
    		$end_ip = $this->_quadIpToInt($end_ips);

    		if (($this->int_ip >= $start_ip) && ($end_ip >= $this->int_ip)) {
    			if ($end_ip == 0 && $start_ip == $this->int_ip) continue;

    			return true;
    		}
    	}

    	return false;
    }

    function processRequest($ip, $request) {

    	$this->ip = $ip;

		$this->pay_to_email = $request['pay_to_email'];
		$this->pay_from_email = $request['pay_from_email'];
		$this->merchant_id = $request['merchant_id'];
		$this->mb_transaction_id = $request['mb_transaction_id'];
		$this->transaction_id = $request['transaction_id'];

		$this->mb_amount = $request['mb_amount'];
		$this->mb_currency = $request['mb_currency'];
		$this->status = $request['status'];
		$this->md5sig = $request['md5sig'];
		$this->amount = $request['amount'];
		$this->currency = $request['currency'];
		
		if (isset($request['rec_payment_id'])) {
			$this->recurring_payment_id = $request['rec_payment_id'];
		}
		
    	if (isset($request['rec_payment_type'])) {
			$this->recurring_payment_type = $request['rec_payment_type'];
		}

		if (!$this->_allowedIp()) {
			$this->_logFailure('Invalid IP');

			header('HTTP/1.0 403 Forbidden');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were unable to authenticate the request';
			exit(0);
		}

    	if (!$this->invoice->id) {
    		$this->_logFailure('Invoice not found');

			header('HTTP/1.0 404 Not Found');
			header('Content-type: text/plain; charset=UTF-8');
			print 'Invoice not found';
			exit(0);
		}

		if (($this->currency != wp_invoice_meta($this->invoice->id, 'wp_invoice_currency_code'))) {
			$this->_logFailure('Invalid currency');

			header('HTTP/1.0 400 Bad Request');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were not expecting you. REF: MB0';
			exit(0);
		}
		if (($this->amount != $this->invoice->display('amount'))) {
			$this->_logFailure('Invalid amount');

			header('HTTP/1.0 400 Bad Request');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were not expecting you. REF: MB1';
			exit(0);
		}
		if (($this->pay_to_email != get_option('wp_invoice_moneybookers_address'))) {
			$this->_logFailure('Invalid pay_to_email');

			header('HTTP/1.0 400 Bad Request');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were not expecting you. REF: MB2';
			exit(0);
		}

		$secret_word = strtoupper(md5(get_option('wp_invoice_moneybookers_secret')));
		$our_signature =  strtoupper(md5("{$this->merchant_id}{$this->transaction_id}{$secret_word}{$this->mb_amount}{$this->mb_currency}{$this->status}"));

		if ($this->md5sig != $our_signature) {
			$this->_logFailure('Invalid signature, we calculated '.$our_signature);

			header('HTTP/1.0 403 Forbidden');
			header('Content-type: text/plain; charset=UTF-8');
			print 'We were unable to authenticate the request';
			exit(0);
		}

		if ($this->status != 2) {
			if ($this->status == -2) {
				$this->_logSuccess('Payment failed (status)');
			}
			if ($this->status == -1) {
				$this->_logSuccess('Payment cancelled (status)');
			}
			if ($this->status == 0) {
				$this->_logSuccess('Payment pending (status)');
			}

			header('HTTP/1.0 200 OK');
			header('Content-type: text/plain; charset=UTF-8');
			print 'Thank you very much for letting us know. REF: Pending';
			exit(0);
		}

		$this->_logSuccess('Paid');

		wp_invoice_mark_as_paid($this->invoice->id);

		header('HTTP/1.0 200 OK');
		header('Content-type: text/plain; charset=UTF-8');
		print 'Thank you very much for letting us know';
		exit(0);
    }
}
