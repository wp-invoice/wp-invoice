<?php

/**
 * PayPal NVP Transactions, Core Communication Class
 * This class should be extended by the individual methods you wish to perform
 * author korotkov@ud
 */

namespace UsabilityDynamics\WPI_PPP {

  if (!class_exists('UsabilityDynamics\WPI_PPP\PayPalNVP')) {

    class PayPalNVP implements \ArrayAccess {

      //** Constant options */
      const API_VERSION = '65.1';
      const API_ENDPOINT = 'https://api-3t.paypal.com/nvp';
      const API_SANDBOX_ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';

      protected $ENDPOINT = '';
      protected $METHOD = '';
      protected $data;
      public $Success = false;

      /**
       * 
       * @param type $offset
       * @return type
       */
      function offsetExists($offset) {
        return array_key_exists($offset, $this->data);
      }

      /**
       * 
       * @param type $offset
       * @return type
       */
      function offsetGet($offset) {
        return $this->data[$offset];
      }

      /**
       * 
       * @param type $offset
       * @param type $value
       */
      function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
      }

      /**
       * 
       * @param type $offset
       */
      function offsetUnset($offset) {
        unset($this->data[$offset]);
      }

      /**
       * 
       * @param type $ppUser
       * @param type $ppPass
       * @param type $ppSig
       */
      function __construct($ppUser = '', $ppPass = '', $ppSig = '', $sandbox = false) {

        if ($this->METHOD) {
          $this['METHOD'] = $this->METHOD;
        }

        $this->ENDPOINT = $sandbox ? self::API_SANDBOX_ENDPOINT : self::API_ENDPOINT;
        
        $this['USER'] = $ppUser;
        $this['PWD'] = $ppPass;
        $this['SIGNATURE'] = $ppSig;
        $this['VERSION'] = self::API_VERSION;
        $this['IPADDRESS'] = $_SERVER['REMOTE_ADDR'];
      }

      /**
       * 
       * @param type $sandbox
       * @return type
       * @throws PayPalUndefinedMethodException
       */
      function Send() {
        if (!$this['METHOD']) {
          throw new PayPalUndefinedMethodException('No NVP Method was defined.');
        }

        //** Combine all request arguments and URL encode */
        $postfields = array();
        foreach ($this->data as $field => $value) {
          $postfields[] = strtoupper($field) . '=' . urlencode($value);
        }
        
        $postfields = implode('&', $postfields);

        //** Begin CURL Process */
        if (!function_exists('curl_init')) {
          throw new \Exception('No cURL found on the server.');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ENDPOINT);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        //** turning off the server and peer verification(TrustManager Concept). */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
          $this->Response = array(
            'TIMESTAMP' => date("Y-m-d H:i:s"),
            'ACK' => 'Failure',
            'L_ERRORCODE0' => curl_errno($ch),
            'L_SHORTMESSAGE0' => 'CURL Error, see long message for details',
            'L_LONGMESSAGE0' => curl_error($ch)
          );
          $this->Success = false;
        } else {
          curl_close($ch);

          $this->Response = self::deformatNVP($response);

          if ($this->Response['ACK'] == 'Success') {
            $this->Success = true;
            $this->OnSuccess();
          } else {
            $this->Success = false;
            $this->OnFailure();
          }
        }

        return $this->Success;
      }

      /**
       * Internal function for unwrapping the NVP response
       */
      protected static function deformatNVP($nvpstr) {
        $intial = 0;
        $nvpArray = array();

        while (strlen($nvpstr)) {
          $keypos = strpos($nvpstr, '=');
          $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
          $keyval = substr($nvpstr, $intial, $keypos);
          $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
          $nvpArray[urldecode($keyval)] = urldecode($valval);
          $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }

        return $nvpArray;
      }
      
      /**
       * Response Processing Functions, meant to be overloaded by subclasses.
       */
      protected function OnSuccess() {
        
      }

      protected function OnFailure() {
        
      }

    }

  }

  if (!class_exists('UsabilityDynamics\WPI_PPP\PayPalUndefinedMethodException')) {

    class PayPalUndefinedMethodException extends \Exception {
      
    }

  }

  if (!class_exists('UsabilityDynamics\WPI_PPP\PayPalInvalidValueException')) {

    class PayPalInvalidValueException extends \Exception {
      
    }

  }
}