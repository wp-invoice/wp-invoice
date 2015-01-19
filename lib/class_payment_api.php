<?php

/**
 * Class used to make payment requests
 */
class WPI_Payment_Api {

  /**
   * Methods
   */
  const WPI_METHOD_AUTHORIZE_NET   = 'wpi_authorize';
  const WPI_METHOD_PAYPAL   = 'wpi_paypal';

  /**
   * Statuses
   */
  const WPI_METHOD_STATUS_COMPLETE = 'Complete';
  const WPI_METHOD_STATUS_ERROR    = 'Error';

  /**
   * Method
   * @var type 
   */
  private $method   = array();
  
  /**
   * Name
   * @var type 
   */
  private $name     = '';
  
  /**
   * Settings
   * @var type 
   */
  private $settings = array();
  
  /**
   * URL
   * @var type 
   */
  private $url      = '';
  
  /**
   * Items
   * @var type 
   */
  private $items    = array();

  /**
   * Default params
   */
  private $defaults = array(

    'wpi_authorize' => array(
      //** Auth info */
      'x_login'       => '',
      'x_tran_key'    => '',
        
      //** Response delimeters */
      'x_delim_data'  => '',
      'x_delim_char'  => '',
      'x_encap_char'  => '',
        
      //** Test or not */
      'x_test_request'=> '',
        
      //** CC Info */
      'x_card_num'    => '',
      'x_card_code'   => '',
      'x_exp_date'    => '',
      'x_currency_code' => '',
        
      //** Invoice info */
      'x_description' => '',
        
      //** User info */
      'x_email'       => '',
      'x_first_name'  => '',
      'x_last_name'   => '',
      'x_amount'      => '',
      'x_company'     => '',
      'x_address'     => '',
      'x_city'        => '',
      'x_state'       => '',
      'x_zip'         => '',
      'x_country'     => '',
      'x_phone'       => '',
      'x_fax'         => ''
    ),

    'wpi_paypal' => array(true)
  );

  /**
   * Response
   * @var type 
   */
  private $response = array(
    'payment_status' => self::WPI_METHOD_STATUS_ERROR,
    'receiver_email' => null,
    'transaction_id' => null
  );
  
  /**
   * Construct
   */
  public function __construct() {
    
    /** Filter to make flexible */
    $this->defaults = apply_filters( 'wpi::payment_api::defaults', $this->defaults );
  }

  /**
   * Runs selected method proccess
   * @global array $wpi_settings
   * @param array $args
   */
  private function run_method( $args ) {
    global $wpi_settings;

    $this->name     = $args['venue'];
    $this->method   = array_key_exists( $args['venue'], $this->defaults )
                    ? $this->defaults[ $args['venue'] ]
                    : $this->defaults[ $this->name = key( $this->defaults ) ];
    $this->settings = $wpi_settings['billing'][$this->name]['settings'];
    $this->items    = !empty( $args['items'] ) ? $args['items'] : array();

    if ( !empty( $this->method ) && is_array( $this->method ) ) {
      switch ( $this->name ) {
        case self::WPI_METHOD_AUTHORIZE_NET:

          $this->url = $this->settings['gateway_url']['value'];
          $this->method['x_login']        = $this->settings['gateway_username']['value'];
          $this->method['x_tran_key']     = $this->settings['gateway_tran_key']['value'];
          $this->method['x_delim_data']   = $this->settings['gateway_delim_data']['value'];
          $this->method['x_delim_char']   = $this->settings['gateway_delim_char']['value'];
          $this->method['x_encap_char']   = $this->settings['gateway_encap_char']['value'];
          $this->method['x_test_request'] = $this->settings['gateway_test_mode']['value'];

          $this->method['x_card_num']   = !empty( $args['cc_number'] )
                             ? $args['cc_number']
                             : '';
          $this->method['x_card_code']  = !empty( $args['cc_code'] )
                             ? $args['cc_code']
                             : '';
          $this->method['x_exp_date']   = !empty( $args['cc_expiration'] )
                             ? $args['cc_expiration']
                             : '';
          $this->method['x_currency_code']   = !empty( $args['currency_code'] )
                             ? $args['currency_code']
                             : '';
          $this->method['x_email']      = !empty( $args['payer_email'] )
                             ? $args['payer_email']
                             : '';
          $this->method['x_description']= !empty( $args['description'] )
                             ? stripslashes($args['description'])
                             : '';
          $this->method['x_first_name'] = !empty( $args['payer_first_name'] )
                             ? stripslashes($args['payer_first_name'])
                             : '';
          $this->method['x_last_name']  = !empty( $args['payer_last_name'] )
                             ? stripslashes($args['payer_last_name'])
                             : '';
          $this->method['x_amount']     = !empty( $args['amount'] )
                             ? $args['amount']
                             : '0';
          $this->method['x_company']    = !empty( $args['company'] )
                             ? stripslashes($args['company'])
                             : '';
          $this->method['x_address']    = !empty( $args['address'] )
                             ? stripslashes($args['address'])
                             : '';
          $this->method['x_city']       = !empty( $args['city'] )
                             ? stripslashes($args['city'])
                             : '';
          $this->method['x_state']      = !empty( $args['state'] )
                             ? $args['state']
                             : '';
          $this->method['x_zip']        = !empty( $args['zip'] )
                             ? $args['zip']
                             : '';
          $this->method['x_country']    = !empty( $args['country'] )
                             ? stripslashes($args['country'])
                             : '';
          $this->method['x_phone']      = !empty( $args['phone'] )
                             ? $args['phone']
                             : '';
          $this->method['x_fax']        = !empty( $args['fax'] )
                             ? $args['fax']
                             : '';

          require_once( ud_get_wp_invoice()->path( 'lib/third-party/authorize.net/authnet.class.php', 'dir' ) );

          $transaction = new WP_Invoice_Authnet( $this->method );
          $transaction->setUrl( $this->url );
          $transaction->addItems( $this->items );
          $transaction->process();

          $this->response['payment_status'] = $transaction->isApproved()
            ? self::WPI_METHOD_STATUS_COMPLETE : self::WPI_METHOD_STATUS_ERROR;
          if ( !$transaction->isApproved() ) {
            $this->response['error_message'] = $transaction->getResponseText();
          }
          $this->response['receiver_email'] = $this->method['x_email'];
          $this->response['transaction_id'] = $transaction->getTransactionID();
          $this->response['payment_method'] = self::WPI_METHOD_AUTHORIZE_NET;

          break;
          
        case self::WPI_METHOD_PAYPAL:

          $this->response['payment_status'] = self::WPI_METHOD_STATUS_COMPLETE;
          $this->response['receiver_email'] = !empty( $args['payer_email'] ) ? $args['payer_email'] : '';
          $this->response['payment_method'] = self::WPI_METHOD_PAYPAL;

          break;

        default:
          $this->response = apply_filters( 'wpi::payment_api::custom_venue', $this->response, $args );
          break;
      }
    }
  }

  /**
   * Process initiator
   * @param array $data
   * @return array
   */
  public function process_transaction( $data ) {
    $this->run_method( $data );
    return $this->response;
  }

}