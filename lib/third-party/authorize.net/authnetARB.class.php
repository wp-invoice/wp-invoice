<?php

class AuthnetARBException extends Exception {}

class WP_Invoice_AuthnetARB {
  private $login;
  private $transkey;

  private $params = array();
  private $success = false;
  private $error = true;

  var $xml;
  var $response;
  private $resultCode;
  private $code;
  private $text;
  private $subscrId;

  public function __construct( $invoice ) {
    $this->url = stripslashes( $invoice[ 'billing' ][ 'wpi_authorize' ][ 'settings' ][ 'recurring_gateway_url' ][ 'value' ] );
    $this->login = stripslashes( $invoice[ 'billing' ][ 'wpi_authorize' ][ 'settings' ][ 'gateway_username' ][ 'value' ] );
    $this->transkey = stripslashes( $invoice[ 'billing' ][ 'wpi_authorize' ][ 'settings' ][ 'gateway_tran_key' ][ 'value' ] );
  }

  private function process( $retries = 1 ) {
    global $wpi_settings;
    $count = 0;
    while ( $count < $retries ) {
      // Init cURL
      $ch = curl_init();

      //required for GoDaddy
      if ( $wpi_settings[ 'using_godaddy' ] == 'yes' ) {
        curl_setopt( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
        curl_setopt( $ch, CURLOPT_PROXY, "http://proxy.shr.secureserver.net:3128" );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
      }

      // cURL options
      curl_setopt( $ch, CURLOPT_URL, $this->url );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, Array( "Content-Type: text/xml" ) );
      curl_setopt( $ch, CURLOPT_HEADER, 1 );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->xml );
      curl_setopt( $ch, CURLOPT_POST, 1 );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

      // Execute cURL
      $this->response = curl_exec( $ch );
      // Get results
      $this->parseResults();

      if ( $this->resultCode === "Ok" ) {
        $this->success = true;
        $this->error = false;
        break;
      } else {
        $this->success = false;
        $this->error = true;
        break;
      }
      $count++;
    }

    curl_close( $ch );
  }

  public function createAccount() {
    $this->xml = "<?xml version='1.0' encoding='utf-8'?>
                  <ARBCreateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                    <merchantAuthentication>
                      <name>" . $this->login . "</name>
                      <transactionKey>" . $this->transkey . "</transactionKey>
                    </merchantAuthentication>
                    <refId>" . $this->params[ 'refID' ] . "</refId>
                    <subscription>
                      <name>" . $this->params[ 'subscrName' ] . "</name>
                      <paymentSchedule>
                        <interval>
                          <length>" . $this->params[ 'interval_length' ] . "</length>
                          <unit>" . $this->params[ 'interval_unit' ] . "</unit>
                        </interval>
                        <startDate>" . $this->params[ 'startDate' ] . "</startDate>
                        <totalOccurrences>" . $this->params[ 'totalOccurrences' ] . "</totalOccurrences>
                        <trialOccurrences>" . $this->params[ 'trialOccurrences' ] . "</trialOccurrences>
                      </paymentSchedule>
                      <amount>" . $this->params[ 'amount' ] . "</amount>
                      <trialAmount>" . $this->params[ 'trialAmount' ] . "</trialAmount>
                      <payment>
                        <creditCard>
                          <cardNumber>" . $this->params[ 'cardNumber' ] . "</cardNumber>
                          <expirationDate>" . $this->params[ 'expirationDate' ] . "</expirationDate>
                        </creditCard>
                      </payment>
                      <order>
                        <invoiceNumber>" . $this->params[ 'orderInvoiceNumber' ] . "</invoiceNumber>
                        <description>" . $this->params[ 'orderDescription' ] . "</description>
                      </order>
                      <customer>
                        <id>" . $this->params[ 'customerId' ] . "</id>
                        <email>" . $this->params[ 'customerEmail' ] . "</email>
                        <phoneNumber>" . $this->params[ 'customerPhoneNumber' ] . "</phoneNumber>
                        <faxNumber>" . $this->params[ 'customerFaxNumber' ] . "</faxNumber>
                      </customer>
                      <billTo>
                        <firstName>" . $this->params[ 'firstName' ] . "</firstName>
                        <lastName>" . $this->params[ 'lastName' ] . "</lastName>
                        <company>" . $this->params[ 'company' ] . "</company>
                        <address>" . $this->params[ 'address' ] . "</address>
                        <city>" . $this->params[ 'city' ] . "</city>
                        <state>" . $this->params[ 'state' ] . "</state>
                        <zip>" . $this->params[ 'zip' ] . "</zip>
                      </billTo>
                      <shipTo>
                        <firstName>" . $this->params[ 'shipFirstName' ] . "</firstName>
                        <lastName>" . $this->params[ 'shipLastName' ] . "</lastName>
                        <company>" . $this->params[ 'shipCompany' ] . "</company>
                        <address>" . $this->params[ 'shipAddress' ] . "</address>
                        <city>" . $this->params[ 'shipCity' ] . "</city>
                        <state>" . $this->params[ 'shipState' ] . "</state>
                        <zip>" . $this->params[ 'shipZip' ] . "</zip>
                      </shipTo>
                    </subscription>
                  </ARBCreateSubscriptionRequest>";
    $this->process();
  }

  public function updateAccount() {
    $this->xml = "<?xml version='1.0' encoding='utf-8'?>
                  <ARBUpdateSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                    <merchantAuthentication>
                      <name>" . $this->url . "</name>
                      <transactionKey>" . $this->transkey . "</transactionKey>
                    </merchantAuthentication>
                    <refId>" . $this->params[ 'refID' ] . "</refId>
                    <subscriptionId>" . $this->params[ 'subscrId' ] . "</subscriptionId>
                    <subscription>
                      <name>" . $this->params[ 'subscrName' ] . "</name>
                      <amount>" . $this->params[ 'amount' ] . "</amount>
                      <trialAmount>" . $this->params[ 'trialAmount' ] . "</trialAmount>
                      <payment>
                        <creditCard>
                          <cardNumber>" . $this->params[ 'cardNumber' ] . "</cardNumber>
                          <expirationDate>" . $this->params[ 'expirationDate' ] . "</expirationDate>
                        </creditCard>
                      </payment>
                      <billTo>
                        <firstName>" . $this->params[ 'firstName' ] . "</firstName>
                        <lastName>" . $this->params[ 'lastName' ] . "</lastName>
                        <address>" . $this->params[ 'address' ] . "</address>
                        <city>" . $this->params[ 'city' ] . "</city>
                        <state>" . $this->params[ 'state' ] . "</state>
                        <zip>" . $this->params[ 'zip' ] . "</zip>
                        <country>" . $this->params[ 'country' ] . "</country>
                      </billTo>
                    </subscription>
                  </ARBUpdateSubscriptionRequest>";
    $this->process();
  }

  public function deleteAccount() {
    $this->xml = "<?xml version='1.0' encoding='utf-8'?>
                  <ARBCancelSubscriptionRequest xmlns='AnetApi/xml/v1/schema/AnetApiSchema.xsd'>
                    <merchantAuthentication>
                      <name>" . $this->url . "</name>
                      <transactionKey>" . $this->transkey . "</transactionKey>
                    </merchantAuthentication>
                    <refId>" . $this->params[ 'refID' ] . "</refId>
                    <subscriptionId>" . $this->params[ 'subscrId' ] . "</subscriptionId>
                  </ARBCancelSubscriptionRequest>";
    $this->process();
  }

  private function parseResults() {
    $this->resultCode = $this->parseXML( '<resultCode>', '</resultCode>' );
    $this->code = $this->parseXML( '<code>', '</code>' );
    $this->text = $this->parseXML( '<text>', '</text>' );
    $this->subscrId = $this->parseXML( '<subscriptionId>', '</subscriptionId>' );

    /*
    echo '$this->resultCode = '.$this->resultCode.'<br />';
    echo '$this->code = '.$this->code.'<br />';
    echo '$this->text = '.$this->text.'<br />';
    echo '$this->subscrId = '.$this->subscrId.'<br />';
     */
  }

  private function parseXML( $start, $end ) {
    return preg_replace( '|^.*?' . $start . '(.*?)' . $end . '.*?$|i', '$1', substr( $this->response, 334 ) );
  }

  public function setParameter( $field = "", $value = null ) {
    $field = ( is_string( $field ) ) ? trim( $field ) : $field;
    $value = ( is_string( $value ) ) ? trim( $value ) : $value;
    if ( !is_string( $field ) ) {
      throw new AuthnetARBException( "setParameter() arg 1 must be a string or integer: " . gettype( $field ) . " given." );
    }
    if ( !is_string( $value ) && !is_numeric( $value ) && !is_bool( $value ) ) {
      throw new AuthnetARBException( "setParameter() arg 2 must be a string, integer, or boolean value: " . gettype( $value ) . " given." );
    }
    if ( empty( $field ) ) {
      throw new AuthnetARBException( "setParameter() requires a parameter field to be named." );
    }
    if ( $value === "" ) {
      throw new AuthnetARBException( "setParameter() requires a parameter value to be assigned: $field" );
    }
    $this->params[ $field ] = $value;
  }

  public function isSuccessful() {
    return $this->success;
  }

  public function isError() {
    return $this->error;
  }

  public function getResponse() {
    return strip_tags( $this->text );
  }

  public function getResponseCode() {
    return $this->code;
  }

  public function getSubscriberID() {
    return $this->subscrId;
  }
}
