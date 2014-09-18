<?php

class WP_Invoice_Authnet {

  private $url;
  private $params = array();
  private $results = array();
  private $items = array();

  private $approved = false;
  private $declined = false;
  private $error = true;

  private $fields;
  private $response;

  static $instances = 0;

  public function __construct( $data = null ) {
    // Singleton
    if ( self::$instances == 0 ) {
      global $invoice;

      // Current invoice's billing array
      $authorize_billing_settings = $invoice[ 'billing' ][ 'wpi_authorize' ][ 'settings' ];

      // Debug
      // WPI_Functions::qc( $authorize_billing_settings );

      // Authorize Request URL
      $this->url = stripslashes( $authorize_billing_settings[ 'gateway_url' ][ 'value' ] );

      // Request parameters
      $this->params[ 'x_delim_data' ] = stripslashes( $authorize_billing_settings[ 'gateway_delim_data' ][ 'value' ] );
      $this->params[ 'x_delim_char' ] = stripslashes( $authorize_billing_settings[ 'gateway_delim_char' ][ 'value' ] );
      $this->params[ 'x_encap_char' ] = stripslashes( $authorize_billing_settings[ 'gateway_encap_char' ][ 'value' ] );
      $this->params[ 'x_login' ] = stripslashes( $authorize_billing_settings[ 'gateway_username' ][ 'value' ] );
      $this->params[ 'x_tran_key' ] = stripslashes( $authorize_billing_settings[ 'gateway_tran_key' ][ 'value' ] );
      $this->params[ 'x_test_request' ] = stripslashes( $authorize_billing_settings[ 'gateway_test_mode' ][ 'value' ] );

      // If data passed then use it
      if ( !is_null( $data ) && is_array( $data ) ) {
        $this->params = $data;
      }

      $this->params[ 'x_relay_response' ] = "FALSE";
      $this->params[ 'x_url' ] = "FALSE";
      $this->params[ 'x_version' ] = "3.1";
      $this->params[ 'x_method' ] = "CC";
      $this->params[ 'x_type' ] = "AUTH_CAPTURE";
      // Singleton
      self::$instances++;
    } else {
      return false;
    }
  }

  /**
   * Set Card number
   *
   * @param unknown $cardnum
   */
  public function transaction( $cardnum ) {
    $this->params[ 'x_card_num' ] = trim( $cardnum );
  }

  public function setUrl( $url ) {
    $this->url = $url;
  }

  public function getResults() {
    return $this->results;
  }

  public function getAmount() {
    return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 9 ] );
  }

  /**
   *
   * @param array $items
   * <b>Example:<b><br>
   * <code>
   * array(
   *   array(
   *     'name'        => 'Item 1 name',
   *     'description' => 'Item 1',
   *     'quantity'    => 2,
   *     'price'       => '10.50'
   *   ),
   *   array(
   *     'name'        => 'Item 2 name',
   *     'description' => 'Item 2',
   *     'quantity'    => 1,
   *     'price'       => '2.55'
   *   ),
   *   array(
   *     'name'        => 'Item 3 name',
   *     'description' => 'Item 3',
   *     'quantity'    => 3,
   *     'price'       => '3.00'
   *   )
   * )
   * </code>
   */
  public function addItems( $items ) {
    $this->items = $items;
  }

  private function _prepareItems() {
    foreach ( $this->items as $key => $item ) {
      $i = $key + 1;
      $this->fields .= "x_line_item=item{$i}<|>{$item['name']}<|>{$item['description']}<|>{$item['quantity']}<|>{$item['price']}<|>Y&";
    }
  }

  /**
   * Run payment process
   *
   * @param int $retries
   */
  public function process( $retries = 1 ) {
    global $wpi_settings;
    // Generate parameters string $this->fields
    $this->_prepareParameters();
    // Prepare items
    $this->_prepareItems();
    //echo $this->fields;
    // Init cURL
    $ch = curl_init( $this->url );

    // Try process <$retries> times
    $count = 0;
    while ( $count < $retries ) {
      /**
       * If GoDaddy hosting
       */
      if ( $wpi_settings[ 'using_godaddy' ] == 'yes' ) {
        curl_setopt( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
        curl_setopt( $ch, CURLOPT_PROXY, "http://proxy.shr.secureserver.net:3128" );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
      }

      // Make sure if POST string is correct
      $post_string = rtrim( $this->fields, "& " );
      // Configure cURL request
      curl_setopt( $ch, CURLOPT_HEADER, 0 );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_string );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
      // Execute
      $this->response = curl_exec( $ch );

      $this->parseResults();

      if ( $this->getResultResponseFull() == "Approved" ) {
        $this->approved = true;
        $this->declined = false;
        $this->error = false;
        break;
      } else if ( $this->getResultResponseFull() == "Declined" ) {
        $this->approved = false;
        $this->declined = true;
        $this->error = false;
        break;
      }
      $count++;
    }

    // Close cURL
    curl_close( $ch );
  }

  /**
   * Get result information from response
   */
  function parseResults() {
    $this->results = explode( $this->params[ 'x_delim_char' ], $this->response );
  }

  /**
   * Set param field and its value into <$this->params>
   *
   * @param string $param
   * @param unknown $value
   */
  public function setParameter( $param, $value ) {
    $param = trim( $param );
    $value = trim( $value );
    $this->params[ $param ] = $value;
  }

  /**
   * Set transaction type
   *
   * @param string $type
   *
   * @TODO: Is it required?
   */
  public function setTransactionType( $type ) {
    $this->params[ 'x_type' ] = strtoupper( trim( $type ) );
  }

  /**
   * Generate POST string for cURL request
   */
  private function _prepareParameters() {
    foreach ( $this->params as $key => $value ) {
      $this->fields .= "$key=" . urlencode( $value ) . "&";
    }
  }

  /**
   * Get gateway response string
   *
   * @return string
   * @TODO Is it required?
   */
  public function getGatewayResponse() {
    return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 0 ] );
  }

  /**
   * Get response result string
   *
   * @return string
   */
  public function getResultResponseFull() {
    $response = array( "", "Approved", "Declined", "Error" );
    return $response[ str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 0 ] ) ];
  }

  /**
   * Returns true if transaction is approved
   *
   * @return bool
   */
  public function isApproved() {
    return $this->approved;
  }

  /**
   * Returns true if transaction is declined
   *
   * @return bool
   */
  public function isDeclined() {
    return $this->declined;
  }

  /**
   * Returns true if there was an error
   *
   * @return bool
   */
  public function isError() {
    return $this->error;
  }

  /**
   *
   * @return string
   */
  public function getResponseText() {
    //return $this->results[3];
    $strip = array( $this->params[ 'x_delim_char' ], $this->params[ 'x_encap_char' ], '|', ',' );
    return str_replace( $strip, '', $this->results[ 3 ] );
  }

  /**
   *
   * @return string
   */
  public function getAuthCode() {
    return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 4 ] );
  }

  /**
   *
   * @return string
   */
  public function getAVSResponse() {
    return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 5 ] );
  }

  /**
   *
   * @return string
   */
  public function getTransactionID() {
    return str_replace( $this->params[ 'x_encap_char' ], '', $this->results[ 6 ] );
  }
}

