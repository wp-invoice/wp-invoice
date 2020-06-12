<?php

/**
 * PayPal NVP Transactions, DoDirectPayment Method
 * @author korotkov@ud
 */

namespace UsabilityDynamics\WPI_PPP {

  if (!class_exists('UsabilityDynamics\WPI_PPP\PayPalDirectPayment')) {

    class PayPalDirectPayment extends PayPalNVP {

      protected $METHOD = 'DoDirectPayment';
      
      public $AuthorizeOnly = false;
      public $Amount;
      public $CardType;
      public $CardNumber;
      public $CardCVV2;
      public $CardExpiration;
      public $FirstName;
      public $LastName;
      public $Address;
      public $City;
      public $State;
      public $Zip;
      public $Country;
      public $Currency;
      public $Email;
      public $Description;
      public $InvoiceNumber;
      
      public $TransactionID;
      public $CVV2Response;
      public $AVSResponse;
      
      private $REQUIRED = array(
        'Amount',
        'CardType',
        'CardNumber',
        'CardCVV2',
        'CardExpiration',
        'FirstName',
        'LastName',
        'Email'
      );

      /**
       * Do send
       * @return type
       * @throws PayPalInvalidValueException
       */
      function Send() {
        
        if ($this->AuthorizeOnly) {
          $this['PAYMENTACTION'] = 'Authorization';
        } else {
          $this['PAYMENTACTION'] = 'Sale';
        }

        foreach ($this->REQUIRED as $field) {
          if (!$this->$field) {
            throw new PayPalInvalidValueException("No value supplied for $field");
          }
        }

        //** Charge Amount */
        $this['AMT'] = number_format($this->Amount, 2, '.', '');

        //** Credit Card Type */
        $ccType = $this->CardType;
        
        if ($ccType) {
          $this['CREDITCARDTYPE'] = $ccType;
        } else {
          throw new PayPalInvalidValueException("Invalid Credit Card Type: {$this->CardType}");
        }

        //** Credit Card Number */
        $this['ACCT'] = $this->CardNumber;
        
        $ccExp = $this->CardExpiration;
            
        if ( empty($ccExp) ) {
          throw new PayPalInvalidValueException("Invalid Credit Card Expiration Date: {$this->CardExpiration}");
        }
        
        $this['EXPDATE'] = $ccExp;

        //** CVV Code */
        $this['CVV2'] = $this->CardCVV2;

        $this['FIRSTNAME'] = $this->FirstName;
        $this['LASTNAME']  = $this->LastName;
        $this['EMAIL']     = $this->Email;

        if ( !isset( $this->Country ) ) {
          throw new PayPalInvalidValueException("Country Code is not one of the allowed values: {$this->Country}");
        }
        $this['COUNTRYCODE'] = $this->Country;

        if ( !empty($this->State) && !isset(HelperCodes::$states[$this->Country][$this->State]) ) {
          throw new PayPalInvalidValueException("State Code is not one of the allowed values: {$this->State}");
        }

        $this['STREET']       = $this->Address;
        $this['CITY']         = $this->City;
        $this['STATE']        = $this->State;
        $this['ZIP']          = $this->Zip;
        $this['CURRENCYCODE'] = $this->Currency;
        $this['DESC']         = $this->Description;
        $this['INVNUM']       = $this->InvoiceNumber;

        return parent::Send();
      }

      /**
       * 
       */
      protected function OnSuccess() {
        $this->TransactionID = $this->Response['TRANSACTIONID'];
        $this->CVV2Response = HelperCodes::$CvvResponse[$this->Response['CVV2MATCH']];
        $this->AVSResponse = HelperCodes::$AvsResponse[$this->Response['AVSCODE']];
      }

    }

  }
}