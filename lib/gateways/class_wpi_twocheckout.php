<?php

/**
  Name: 2Checkout
  Class: wpi_twocheckout
  Internal Slug: wpi_twocheckout
  JS Slug: wpi_twocheckout
  Version: 1.0
  Description: Provides the 2Checkout for payment options
 */

class wpi_twocheckout extends wpi_gateway_base {
  
  static $_options = array();
  public $_country_arr, $_us_states_arr, $_canada_states_arr;
  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();
    
    $this->options = array(
        'name' => __( '2Checkout', ud_get_wp_invoice()->domain ),
        'allow' => '',
        'default_option' => '',
        'settings' => array(
            'twocheckout_sid' => array(
                'label' => __( "2Checkout Seller ID", ud_get_wp_invoice()->domain ),
                'value' => ''
            ),
            'twocheckout_secret' => array(
                'label' => __( "2Checkout Secret Word", ud_get_wp_invoice()->domain ),
                'value' => ''
            ),
            'test_mode' => array(
                'label' => __( "Demo Mode", ud_get_wp_invoice()->domain ),
                'description' => __( "Use 2Checkout Demo Mode", ud_get_wp_invoice()->domain ),
                'type' => 'select',
                'value' => 'N',
                'data' => array(
                    'N' => __( "No", ud_get_wp_invoice()->domain ),
                    'Y' => __( "Yes", ud_get_wp_invoice()->domain )
                )
            ),
            'passback' => array(
                'label' => __( "2Checkout Approved URL/INS URL", ud_get_wp_invoice()->domain ),
                'type' => "readonly",
                'description' => __( "Set this URL as your Approved URL in your 2Checkout Site Management page and Notification URL under your 2Checkout Notification page.", ud_get_wp_invoice()->domain )
            )
        )
    );
    
    //** Fields for front-end. */
	$this->_us_states_arr = array(
		'' => 'Select one',
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AS' => 'American Samoa',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'AA' => 'Armed Forces Americas',
		'AE' => 'Armed Forces Europe, Middle East and Canada',
		'AP' => 'Armed Forces Pacific',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FM' => 'Federated States of Micronesia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'GU' => 'Guam',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MH' => 'Marshall Islands',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'MP' => 'Northern Mariana Islands',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PW' => 'Palau',
		'PA' => 'Pennsylvania',
		'PR' => 'Puerto Rico',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VI' => 'Virgin Islands',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia'
      );
    $this->_canada_states_arr = array(
		'' => 'Select one', 'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NB' => 'New Brunswick', 'NF' => 'Newfoundland', 'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories', 'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec', 'SK' => 'Saskatchewan', 'YT' => 'Yukon Territory'
      );
    $this->_country_arr = array(
      ''=>'Choose Country',
        'IND'=>'India',
        'USA' => 'United States',
        'GBR' => 'United Kingdom',
        'CAN' => 'Canada',
        'AUS' => 'Australia',
        'ALA' => 'Åland Islands',
        'AFG' => 'Afghanistan',
        'ALB' => 'Albania',
        'DZA' => 'Algeria',
        'ASM' => 'American Samoa',
        'AND' => 'Andorra',
        'AGO' => 'Angola',
        'AIA' => 'Anguilla',
        'ATA' => 'Antarctica',
        'ATG' => 'Antigua and Barbuda',
        'ARG' => 'Argentina',
        'ARM' => 'Armenia',
        'ABW' => 'Aruba',
        'AUS' => 'Australia',
        'AUT' => 'Austria',
        'AZE' => 'Azerbaijan',
        'BHS' => 'Bahamas',
        'BHR' => 'Bahrain',
        'BGD' => 'Bangladesh',
        'BRB' => 'Barbados',
        'BLR' => 'Belarus',
        'BEL' => 'Belgium',
        'BLZ' => 'Belize',
        'BEN' => 'Benin',
        'BMU' => 'Bermuda',
        'BTN' => 'Bhutan',
        'BOL' => 'Bolivia',
        'BES' => 'Bonaire, Sint Eustatius and Saba',
        'BIH' => 'Bosnia and Herzegovina',
        'BWA' => 'Botswana',
        'BVT' => 'Bouvet Island',
        'BRA' => 'Brazil',
        'IOT' => 'British Indian Ocean Territory',
        'BRN' => 'Brunei Darussalam',
        'BGR' => 'Bulgaria',
        'BFA' => 'Burkina Faso',
        'BDI' => 'Burundi',
        'KHM' => 'Cambodia',
        'CMR' => 'Cameroon',
        'CAN' => 'Canada',
        'CPV' => 'Cape Verde',
        'CYM' => 'Cayman Islands',
        'CAF' => 'Central African Republic',
        'TCD' => 'Chad',
        'CHL' => 'Chile',
        'CHN' => 'China',
        'CXR' => 'Christmas Island',
        'CCK' => 'Cocos (Keeling) Islands',
        'COL' => 'Colombia',
        'COM' => 'Comoros',
        'COG' => 'Congo',
        'COD' => 'Congo, the Democratic Republic of the',
        'COK' => 'Cook Islands',
        'CRI' => 'Costa Rica',
        'CIV' => 'Cote D\'ivoire',
        'HRV' => 'Croatia (Hrvatska)',
        'CYP' => 'Cyprus',
        'CZE' => 'Czech Republic',
        'DNK' => 'Denmark',
        'DJI' => 'Djibouti',
        'DMA' => 'Dominica',
        'DOM' => 'Dominican Republic',
        'ECU' => 'Ecuador',
        'EGY' => 'Egypt',
        'SLV' => 'El Salvador',
        'GNQ' => 'Equatorial Guinea',
        'ERI' => 'Eritrea',
        'EST' => 'Estonia',
        'ETH' => 'Ethiopia',
        'FLK' => 'Falkland Islands (Malvinas)',
        'FRO' => 'Faroe Islands',
        'FJI' => 'Fiji',
        'FIN' => 'Finland',
        'FRA' => 'France',
        'FXX' => 'France, Metropolitan',
        'GUF' => 'French Guiana',
        'PYF' => 'French Polynesia',
        'ATF' => 'French Southern Territories',
        'GAB' => 'Gabon',
        'GMB' => 'Gambia',
        'GEO' => 'Georgia',
        'DEU' => 'Germany',
        'GHA' => 'Ghana',
        'GIB' => 'Gibraltar',
        'GRC' => 'Greece',
        'GRL' => 'Greenland',
        'GRD' => 'Grenada',
        'GLP' => 'Guadeloupe',
        'GUM' => 'Guam',
        'GTM' => 'Guatemala',
        'GGY' => 'Guernsey',
        'GIN' => 'Guinea',
        'GNB' => 'Guinea-Bissau',
        'GUY' => 'Guyana',
        'HTI' => 'Haiti',
        'HMD' => 'Heard Island and Mcdonald Islands',
        'HND' => 'Honduras',
        'HKG' => 'Hong Kong',
        'HUN' => 'Hungary',
        'ISL' => 'Iceland',
        'IND' => 'India',
        'IDN' => 'Indonesia',
        'IRQ' => 'Iraq',
        'IRL' => 'Ireland',
        'IMN' => 'Isle of Man',
        'ISR' => 'Israel',
        'ITA' => 'Italy',
        'JAM' => 'Jamaica',
        'JPN' => 'Japan',
        'JEY' => 'Jersey',
        'JOR' => 'Jordan',
        'KAZ' => 'Kazakhstan',
        'KEN' => 'Kenya',
        'KIR' => 'Kiribati',
        'KOR' => 'Korea, Republic of',
        'KWT' => 'Kuwait',
        'KGZ' => 'Kyrgyzstan',
        'LAO' => 'Lao People\'s Democratic Republic',
        'LVA' => 'Latvia',
        'LBN' => 'Lebanon',
        'LSO' => 'Lesotho',
        'LBR' => 'Liberia',
        'LBY' => 'Libyan Arab Jamahiriya',
        'LIE' => 'Liechtenstein',
        'LTU' => 'Lithuania',
        'LUX' => 'Luxembourg',
        'MAC' => 'Macao',
        'MKD' => 'Macedonia',
        'MDG' => 'Madagascar',
        'MWI' => 'Malawi',
        'MYS' => 'Malaysia',
        'MDV' => 'Maldives',
        'MLI' => 'Mali',
        'MLT' => 'Malta',
        'MHL' => 'Marshall Islands',
        'MTQ' => 'Martinique',
        'MRT' => 'Mauritania',
        'MUS' => 'Mauritius',
        'MYT' => 'Mayotte',
        'MEX' => 'Mexico',
        'FSM' => 'Micronesia, Federated States of',
        'MDA' => 'Moldova, Republic of',
        'MCO' => 'Monaco',
        'MNG' => 'Mongolia',
        'MNE' => 'Montenegro',
        'MSR' => 'Montserrat',
        'MAR' => 'Morocco',
        'MOZ' => 'Mozambique',
        'MMR' => 'Myanmar',
        'NAM' => 'Namibia',
        'NRU' => 'Nauru',
        'NPL' => 'Nepal',
        'NLD' => 'Netherlands',
        'ANT' => 'Netherlands Antilles',
        'NCL' => 'New Caledonia',
        'NZL' => 'New Zealand',
        'NIC' => 'Nicaragua',
        'NER' => 'Niger',
        'NGA' => 'Nigeria',
        'NIU' => 'Niue',
        'NFK' => 'Norfolk Island',
        'MNP' => 'Northern Mariana Islands',
        'NOR' => 'Norway',
        'OMN' => 'Oman',
        'PAK' => 'Pakistan',
        'PLW' => 'Palau',
        'PSE' => 'Palestinian Territory, Occupied',
        'PAN' => 'Panama',
        'PNG' => 'Papua New Guinea',
        'PRY' => 'Paraguay',
        'PER' => 'Peru',
        'PHL' => 'Philippines',
        'PCN' => 'Pitcairn',
        'POL' => 'Poland',
        'PRT' => 'Portugal',
        'PRI' => 'Puerto Rico',
        'QAT' => 'Qatar',
        'REU' => 'Reunion',
        'ROU' => 'Romania',
        'RUS' => 'Russian Federation',
        'RWA' => 'Rwanda',
        'SHN' => 'Saint Helena',
        'KNA' => 'Saint Kitts and Nevis',
        'LCA' => 'Saint Lucia',
        'SPM' => 'Saint Pierre and Miquelon',
        'VCT' => 'Saint Vincent and the Grenadines',
        'WSM' => 'Samoa',
        'SMR' => 'San Marino',
        'STP' => 'Sao Tome and Principe',
        'SAU' => 'Saudi Arabia',
        'SEN' => 'Senegal',
        'SRB' => 'Serbia',
        'SCG' => 'Serbia and Montenegro',
        'SYC' => 'Seychelles',
        'SLE' => 'Sierra Leone',
        'SGP' => 'Singapore',
        'SVK' => 'Slovakia',
        'SVN' => 'Slovenia',
        'SLB' => 'Solomon Islands',
        'SOM' => 'Somalia',
        'ZAF' => 'South Africa',
        'SGS' => 'South Georgia and the South Sandwich Islands',
        'ESP' => 'Spain',
        'LKA' => 'Sri Lanka',
        'SUR' => 'Suriname',
        'SJM' => 'Svalbard and Jan Mayen Islands',
        'SWZ' => 'Swaziland',
        'SWE' => 'Sweden',
        'CHE' => 'Switzerland',
        'TWN' => 'Taiwan',
        'TJK' => 'Tajikistan',
        'TZA' => 'Tanzania, United Republic of',
        'THA' => 'Thailand',
        'TLS' => 'Timor-Leste',
        'TGO' => 'Togo',
        'TKL' => 'Tokelau',
        'TON' => 'Tonga',
        'TTO' => 'Trinidad and Tobago',
        'TUN' => 'Tunisia',
        'TUR' => 'Turkey',
        'TKM' => 'Turkmenistan',
        'TCA' => 'Turks and Caicos Islands',
        'TUV' => 'Tuvalu',
        'UGA' => 'Uganda',
        'UKR' => 'Ukraine',
        'ARE' => 'United Arab Emirates',
        'GBR' => 'United Kingdom',
        'USA' => 'United States',
        'UMI' => 'United States Minor Outlying Islands',
        'URY' => 'Uruguay',
        'UZB' => 'Uzbekistan',
        'VUT' => 'Vanuatu',
        'VAT' => 'Vatican City State (Holy See)',
        'VEN' => 'Venezuela',
        'VNM' => 'Viet Nam',
        'VGB' => 'Virgin Islands, British',
        'VIR' => 'Virgin Islands, U.S.',
        'WLF' => 'Wallis and Futuna Islands',
        'ESH' => 'Western Sahara',
        'YEM' => 'Yemen',
        'YUG' => 'Yugoslavia',
        'ZAR' => 'Zaire',
        'ZMB' => 'Zambia',
        'ZWE'=>'Zimbabwe');
    $this->front_end_fields = array(
        'customer_information' => array(
            'first_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'first_name',
                'label' => __('First Name', ud_get_wp_invoice()->domain)
            ),
            'last_name' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'last_name',
                'label' => __('Last Name', ud_get_wp_invoice()->domain)
            ),
            'user_email' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'email',
                'label' => __('Email Address', ud_get_wp_invoice()->domain)
            ),
            'phonenumber' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'phone',
                'label' => __('Phone', ud_get_wp_invoice()->domain)
            ),
            'streetaddress' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'street_address',
                'label' => __('Address Line 1', ud_get_wp_invoice()->domain)
            ),
            'streetaddress2' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'street_address2',
                'label' => __('Address Line 2', ud_get_wp_invoice()->domain)
            ),
            'country' => array(
                'type' => 'select',
                'class' => 'text-input',
                'name' => 'country',
				'values'=> serialize($this->_country_arr),
                'label' => __('Country', ud_get_wp_invoice()->domain)
            ),
            'city' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'city',
                'label' => __('City', ud_get_wp_invoice()->domain)
            ),
            'state' => array(
                'type' => 'text',
                'class' => 'text-input hidden state-input',
                'liclass' => 'li-state',
                'name' => 'state',
                'label' => __('State/Province', ud_get_wp_invoice()->domain)
            ),
            'can_state' => array(
				'type' => 'select',
				'class' => 'text-input hidden state-input',
				'liclass' => 'li-state',
				'name' => 'state',
				'values'=> serialize($this->_canada_states_arr),
				'label' => __('State/Province', ud_get_wp_invoice()->domain)
            ),
            'usa_state' => array(
				'type' => 'select',
				'class' => 'text-input state-input',
				'liclass' => 'li-state',
				'name' => 'state',
				'values'=> serialize($this->_us_states_arr),
				'label' => __('State/Province', ud_get_wp_invoice()->domain)
            ),
            'zip' => array(
                'type' => 'text',
                'class' => 'text-input',
                'name' => 'zip',
                'label' => __('Zip/Postal Code', ud_get_wp_invoice()->domain)
            )
        )
    );
    
    $this->options['settings']['passback']['value'] = admin_url('admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_twocheckout');
  
    self::$_options = $this->options;
  }

  /**
   * Recurring settings UI
   * @param type $this_invoice
   */
  function recurring_settings($this_invoice) {
    ?>
    <h4><?php _e('2Checkout Recurring Billing', ud_get_wp_invoice()->domain); ?></h4>
    <table class="wpi_recurring_bill_settings">
        <tr>
            <th style="cursor:help;" title="<?php _e('Specifies billing frequency.', ud_get_wp_invoice()->domain); ?>"><?php _e('Interval', ud_get_wp_invoice()->domain); ?></th>
            <td>
              <?php echo WPI_UI::input("id=2co_recurrence_interval&name=wpi_invoice[recurring][".$this->type."][recurrence_interval]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['recurrence_interval'] : '') . "&special=size='2' maxlength='4' autocomplete='off'"); ?>
              <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][recurrence_period]&values=" . serialize(apply_filters('wpi_2co_recurrence_period', array("Week" => __("Week", ud_get_wp_invoice()->domain), "Month" => __("Month", ud_get_wp_invoice()->domain), "Year" => __("Year", ud_get_wp_invoice()->domain)))) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['recurrence_period'] : '')); ?>
            </td>
        </tr>
        <tr>
            <th style="cursor:help;" title="<?php _e('Specifies billing duration.', ud_get_wp_invoice()->domain); ?>"><?php _e('Duration', ud_get_wp_invoice()->domain); ?></th>
            <td>
              <?php echo WPI_UI::input("id=2co_duration_interval&name=wpi_invoice[recurring][".$this->type."][duration_interval]&value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['duration_interval'] : '') . "&special=size='2' maxlength='4' autocomplete='off'"); ?>
              <?php echo WPI_UI::select("name=wpi_invoice[recurring][".$this->type."][duration_period]&values=" . serialize(apply_filters('wpi_2co_duration_period', array("Week" => __("Week", ud_get_wp_invoice()->domain), "Month" => __("Month", ud_get_wp_invoice()->domain), "Year" => __("Year", ud_get_wp_invoice()->domain)))) . "&current_value=" . (!empty($this_invoice['recurring'][$this->type]) ? $this_invoice['recurring'][$this->type]['duration_period'] : '')); ?>
            </td>
        </tr>
    </table>
    <?php
  }
  
  /**
   * Get recurrence
   * @param type $invoice
   */
  public function get_recurrence( $invoice ) {
    return $invoice['recurring']['wpi_twocheckout']['recurrence_interval'].' '.$invoice['recurring']['wpi_twocheckout']['recurrence_period'];
  }
  
  /**
   * Get duration
   * @param type $invoice
   */
  public function get_duration( $invoice ) {
    return $invoice['recurring']['wpi_twocheckout']['duration_interval'].' '.$invoice['recurring']['wpi_twocheckout']['duration_period'];
  }

  /**
   * Overrided payment process for 2Checkout
   *
   * @global type $invoice
   * @global type $wpi_settings
   */
  static function process_payment() {
    global $invoice;
	
    $wp_users_id = $invoice['user_data']['ID'];

    // update user data
    update_user_meta($wp_users_id, 'last_name', !empty($_REQUEST['last_name'])?$_REQUEST['last_name']:'' );
    update_user_meta($wp_users_id, 'first_name', !empty($_REQUEST['first_name'])?$_REQUEST['first_name']:'' );
    update_user_meta($wp_users_id, 'city', !empty($_REQUEST['city'])?$_REQUEST['city']:'' );
    update_user_meta($wp_users_id, 'state', !empty($_REQUEST['state'])?$_REQUEST['state']:'' );
    update_user_meta($wp_users_id, 'zip', !empty($_REQUEST['zip'])?$_REQUEST['zip']:'' );
    update_user_meta($wp_users_id, 'streetaddress', !empty($_REQUEST['street_address'])?$_REQUEST['street_address']:'' );
	update_user_meta($wp_users_id, 'streetaddress2', !empty($_REQUEST['street_address2'])?$_REQUEST['street_address2']:'' );
    update_user_meta($wp_users_id, 'phonenumber', !empty($_REQUEST['phone'])?$_REQUEST['phone']:'' );
    update_user_meta($wp_users_id, 'country', !empty($_REQUEST['country'])?$_REQUEST['country']:'' );

    if ( !empty( $_REQUEST['crm_data'] ) ) {
      self::user_meta_updated( $_REQUEST['crm_data'] );
    }

    $invoice_obj = new WPI_Invoice();
    $invoice_obj->load_invoice("id={$invoice['invoice_id']}");

    parent::successful_payment($invoice_obj);

    echo json_encode(
      array('success' => 1)
    );
  }

  /**
   * Fields renderer
   * @param type $invoice
   */
  public function wpi_payment_fields($invoice) {
	  
    $this->front_end_fields = apply_filters('wpi_crm_custom_fields', $this->front_end_fields, 'crm_data');

    if (!empty($this->front_end_fields)) {
      //** For each section */
      foreach ($this->front_end_fields as $key => $value) {
        //** If section is not empty */
        if (!empty($this->front_end_fields[$key])) {
          $html = '';
          ob_start();
          ?>
          <ul class="wpi_checkout_block">
            <li class="section_title"><?php _e(ucwords(str_replace('_', ' ', $key)), ud_get_wp_invoice()->domain); ?></li>
            <?php
            $html = ob_get_clean();
            echo $html;
            //** For each field */
            foreach ($value as $field_slug => $field_data) {
              //** Change field properties if we need */
              $field_data = apply_filters('wpi_payment_form_styles', $field_data, $field_slug, 'wpi_twocheckout');
              $html = '';

              ob_start();

              switch ($field_data['type']) {
                case self::TEXT_INPUT_TYPE:
                  ?>

                  <li id="li_<?php echo  esc_attr($field_slug);?>"  class="wpi_checkout_row <?php echo ( $field_data['liclass']!=''?$field_data['liclass']:'' );?>">
                    <div class="control-group">
                      <label class="control-label" for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
                      <div class="controls">
                        <input type="<?php echo esc_attr($field_data['type']); ?>" class="<?php echo esc_attr($field_data['class']); ?>"  name="<?php echo esc_attr($field_data['name']); ?>" value="<?php echo isset($field_data['value'])?$field_data['value']:(!empty($invoice['user_data'][$field_slug])?$invoice['user_data'][$field_slug]:get_user_meta($invoice['user_data']['ID'],$field_slug,true));?>" />
                      </div>
                    </div>
                  </li>

                  <?php
                  $html = ob_get_clean();

                  break;

                case self::SELECT_INPUT_TYPE:
                  ?>

                  <li id="li_<?php echo  esc_attr($field_slug);?>"  class="wpi_checkout_row <?php echo ( $field_data['liclass']!=''?$field_data['liclass']:'' );?>">
                    <label for="<?php echo esc_attr($field_slug); ?>"><?php _e($field_data['label'], ud_get_wp_invoice()->domain); ?></label>
                    <?php echo WPI_UI::select("name={$field_data['name']}&values={$field_data['values']}&id={$field_slug}&class={$field_data['class']}"); ?>
                  </li>

                  <?php
                  $html = ob_get_clean();

                  break;

                case self::RECAPTCHA_INPUT_TYPE:
                  $this->display_recaptcha($field_data);

                break;

                default:
                  break;
              }

              echo $html;
            }
            echo '</ul>';
          }
        }
      }
    }

    /**
     * Handler for 2Checkout Callback
     * @author Craig Christenson
     * Full callback URL: http://domain/wp-admin/admin-ajax.php?action=wpi_gateway_server_callback&type=wpi_twocheckout
     */
    static function server_callback() {

      if (empty($_REQUEST)) {
        die(__('Direct access not allowed', ud_get_wp_invoice()->domain));
      }

      $invoice = new WPI_Invoice();
      $invoice->load_invoice("id={$_REQUEST['merchant_order_id']}");

      /** Verify callback request */
      if ( self::_ipn_verified($invoice) ) {
        if ($_REQUEST['key']) {
          $event_note = sprintf(__('%s paid via 2Checkout', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_REQUEST['total']), $_REQUEST['merchant_order_id']));
          $event_amount = (float) $_REQUEST['total'];
          $event_type = 'add_payment';
          /** Log balance changes */
          $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
          /** Log payer email */
          $payer_email = sprintf(__("2Checkout buyer email: %s", ud_get_wp_invoice()->domain), $_REQUEST['email']);
          $invoice->add_entry("attribute=invoice&note=$payer_email&type=update");
          $invoice->save_invoice();
          /** ... and mark invoice as paid */
          wp_invoice_mark_as_paid($_REQUEST['invoice_id'], $check = true);
          parent::successful_payment( $invoice );
          parent::successful_payment_webhook( $invoice );
          send_notification($invoice->data);
          echo '<script type="text/javascript">window.location="' . get_invoice_permalink($invoice->data['ID']) . '";</script>';

          /** Handle INS messages */
        } elseif ($_POST['md5_hash']) {

          switch ($_POST['message_type']) {

            case 'FRAUD_STATUS_CHANGED':
              if ($_POST['fraud_status'] == 'pass') {
                WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Passed 2Checkout fraud review.', ud_get_wp_invoice()->domain));
              } elseif (condition) {
                WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Failed 2Checkout fraud review.', ud_get_wp_invoice()->domain));
                wp_invoice_mark_as_pending($_POST['vendor_order_id']);
              }
              break;

            case 'RECURRING_STOPPED':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring billing stopped.', ud_get_wp_invoice()->domain));
              break;

            case 'RECURRING_INSTALLMENT_FAILED':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring installment failed.', ud_get_wp_invoice()->domain));
              break;

            case 'RECURRING_INSTALLMENT_SUCCESS':
              $event_note = sprintf(__('%1s paid for subscription %2s', ud_get_wp_invoice()->domain), WPI_Functions::currency_format(abs($_POST['item_rec_list_amount_1']), $_POST['vendor_order_id']), $_POST['sale_id']);
              $event_amount = (float) $_POST['item_rec_list_amount_1'];
              $event_type = 'add_payment';
              $invoice->add_entry("attribute=balance&note=$event_note&amount=$event_amount&type=$event_type");
              $invoice->save_invoice();
              send_notification($invoice->data);
              break;

            case 'RECURRING_COMPLETE':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring installments completed.', ud_get_wp_invoice()->domain));
              wp_invoice_mark_as_paid($_POST['invoice'], $check = false);
              break;

            case 'RECURRING_RESTARTED':
              WPI_Functions::log_event(wpi_invoice_id_to_post_id($_POST['vendor_order_id']), 'invoice', 'update', '', __('Recurring sale restarted.', ud_get_wp_invoice()->domain));
              break;

            default:
              break;
          }
        }
      }
    }
    
    /**
     * Get proper api url
     * @filters wpi_2co_live_url, wpi_2co_demo_url
     * @param type $invoice
     * @return type
     */
    public function get_api_url( $invoice ) {
      return $invoice['billing']['wpi_twocheckout']['settings']['test_mode']['value'] == 'N' ? apply_filters( 'wpi_2co_live_url', 'https://www.2checkout.com/checkout/purchase' ) : apply_filters( 'wpi_2co_demo_url', 'https://sandbox.2checkout.com/checkout/purchase' );
    }
    
    /**
     * Get SID
     * @param type $invoice
     * @return type
     */
    public function get_sid( $invoice ) {
      return $invoice['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value'];
    }
    
    /**
     * 
     * @param type $invoice
     * @return \type
     */
    public function get_callback_url( $invoice ) {
      return $invoice['billing']['wpi_twocheckout']['settings']['passback']['value'];
    }

   /**
    * Verify return/notification and return TRUE or FALSE
    * @author Craig Christenson
    **/
    private static function _ipn_verified($invoice = false) {

      if ($_REQUEST['key']) {
        $transaction_id = $_REQUEST['order_number'];
        
        $compare_string = $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_secret']['value'] .
                $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value'] . $transaction_id .
                $_REQUEST['total'];
        
        $compare_hash1 = strtoupper(md5($compare_string));
        $compare_hash2 = $_REQUEST['key'];

        if ($compare_hash1 != $compare_hash2) {
          die("MD5 HASH Mismatch! Make sure your demo settings are correct.");
        } else {
          return TRUE;
        }
      } elseif ($_POST['md5_hash']) {
        $compare_string = $_POST['sale_id'] . $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_sid']['value'] .
                $_POST['invoice_id'] . $invoice->data['billing']['wpi_twocheckout']['settings']['twocheckout_secret']['value'];
        $compare_hash1 = strtoupper(md5($compare_string));
        $compare_hash2 = $_POST['md5_hash'];
        if ($compare_hash1 != $compare_hash2) {
          die("MD5 HASH Mismatch!");
        } else {
          return TRUE;
        }
      }
    }

  }
  