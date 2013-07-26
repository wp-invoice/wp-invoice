<?php
/**
 * Load all WP-Invoice settings from get_option( 'wpi_options' )
 * InitOptions are default settings, and loaded if wpi_options is not set.
 * All these settings are also stored in a global variable ($wpi_settings) for easy access
 */

class WPI_Settings {

  var $Core;
  var $data;

  /**
   * Cunstruct
   *
   * @param object $Core
   */
  function WPI_Settings(&$Core) {
    $this->Core = $Core;
    $this->LoadOptions();
  }

  /**
   * Initialize options
   *
   * @global bool $wp_invoice_debug
   */
  function InitOptions() {
    global $wp_invoice_debug;

    if(isset($Core) && $Core)
      $this->options['version'] = $this->Core->version;

      /** Default Invoice Types */
      $this->options['types'] = array(
        'invoice' => array('label'   => __('Invoice', WPI)),
        'recurring' => array('label' => __('Recurring', WPI))
      );

      $this->options['debug'] = $wp_invoice_debug;

      if($wp_invoice_debug) {
        $this->options['developer_mode'] = 'true';
      }

      /** Localization Labels */
      $this->options['custom_label_tax'] = __("Tax", WPI);

      /** WP-Invoice Lookup */
      $this->options['lookup_text']   = __("Pay Your Invoice", WPI);
      $this->options['lookup_submit'] = __("Lookup", WPI);

      /** Frontend Customization */
      $this->options['use_custom_templates'] = "false";
      $this->options['state_selection']      = __("Dropdown", WPI);

      $this->options['email_address']    = get_bloginfo('admin_email');
      $this->options['business_name']    = get_bloginfo('blogname');
      $this->options['business_address'] = '';
      $this->options['business_phone']   = '';

      $this->options['user_level'] = 8;

      $this->options['web_invoice_page'] = '';
      $this->options['where_to_display'] = 'overwrite';

      /** Advanced Settings */
      $this->options['allow_deposits'] = 'true';

      /** Payment */
      $this->options['client_change_payment_method'] = 'false';

      /** Basic Settings */
      $this->options['replace_page_title_with_subject']   = 'true';
      $this->options['using_godaddy']                     = 'no';
      $this->options['use_wp_users']                      = 'true';
      $this->options['first_time_setup_ran']              = 'false';
      $this->options['increment_invoice_id']              = 'false';
      $this->options['do_not_load_theme_specific_css']    = 'false';
      $this->options['cc_thank_you_email']                = 'false';
      $this->options['send_invoice_creator_email']        = 'false';
      $this->options['replace_page_heading_with_subject'] = 'false';
      $this->options['hide_page_title']                   = 'false';
      $this->options['terms_acceptance_required']         = 'false';

      $this->options['use_css']                = 'yes';
      $this->options['force_https']            = 'false';
      $this->options['send_thank_you_email']   = 'no';
      $this->options['show_recurring_billing'] = 'true';
      $this->options['global_tax']             = '0';

      $this->options['user_meta']['required']['first_name']  = __('First Name', WPI);
      $this->options['user_meta']['required']['last_name']   = __('Last Name', WPI);
      $this->options['user_meta']['custom']['company_name']  = __('Company Name', WPI);
      $this->options['user_meta']['custom']['phonenumber']   = __('Phone Number', WPI);
      $this->options['user_meta']['custom']['streetaddress'] = __('Street Address', WPI);
      $this->options['user_meta']['custom']['city']          = __('City', WPI);
      $this->options['user_meta']['custom']['state']         = __('State', WPI);
      $this->options['user_meta']['custom']['zip']           = __('ZIP', WPI);

      /** Invoice statuses. Filter: wpi_invoice_statuses */
      $this->options['invoice_statuses']['active']  = __("Active", WPI);
      $this->options['invoice_statuses']['archive'] = __("Archived", WPI);
      $this->options['invoice_statuses']['trash']   = __("Trashed", WPI);
      $this->options['invoice_statuses']['paid']    = __("Paid", WPI);

      $this->options['countries']['US'] = __("United States", WPI);
      $this->options['countries']['AL'] = __("Albania", WPI);
      $this->options['countries']['DZ'] = __("Algeria", WPI);
      $this->options['countries']['AD'] = __("Andorra", WPI);
      $this->options['countries']['AO'] = __("Angola", WPI);
      $this->options['countries']['AI'] = __("Anguilla", WPI);
      $this->options['countries']['AG'] = __("Antigua and Barbuda", WPI);
      $this->options['countries']['AR'] = __("Argentina", WPI);
      $this->options['countries']['AM'] = __("Armenia", WPI);
      $this->options['countries']['AW'] = __("Aruba", WPI);
      $this->options['countries']['AU'] = __("Australia", WPI);
      $this->options['countries']['AT'] = __("Austria", WPI);
      $this->options['countries']['AZ'] = __("Azerbaijan Republic", WPI);
      $this->options['countries']['BS'] = __("Bahamas", WPI);
      $this->options['countries']['BH'] = __("Bahrain", WPI);
      $this->options['countries']['BB'] = __("Barbados", WPI);
      $this->options['countries']['BE'] = __("Belgium", WPI);
      $this->options['countries']['BZ'] = __("Belize", WPI);
      $this->options['countries']['BJ'] = __("Benin", WPI);
      $this->options['countries']['BM'] = __("Bermuda", WPI);
      $this->options['countries']['BT'] = __("Bhutan", WPI);
      $this->options['countries']['BO'] = __("Bolivia", WPI);
      $this->options['countries']['BA'] = __("Bosnia and Herzegovina", WPI);
      $this->options['countries']['BW'] = __("Botswana", WPI);
      $this->options['countries']['BR'] = __("Brazil", WPI);
      $this->options['countries']['VG'] = __("British Virgin Islands", WPI);
      $this->options['countries']['BN'] = __("Brunei", WPI);
      $this->options['countries']['BG'] = __("Bulgaria", WPI);
      $this->options['countries']['BF'] = __("Burkina Faso", WPI);
      $this->options['countries']['BI'] = __("Burundi", WPI);
      $this->options['countries']['KH'] = __("Cambodia", WPI);
      $this->options['countries']['CA'] = __("Canada", WPI);
      $this->options['countries']['CV'] = __("Cape Verde", WPI);
      $this->options['countries']['KY'] = __("Cayman Islands", WPI);
      $this->options['countries']['TD'] = __("Chad", WPI);
      $this->options['countries']['CL'] = __("Chile", WPI);
      $this->options['countries']['C2'] = __("China", WPI);
      $this->options['countries']['CO'] = __("Colombia", WPI);
      $this->options['countries']['KM'] = __("Comoros", WPI);
      $this->options['countries']['CK'] = __("Cook Islands", WPI);
      $this->options['countries']['CR'] = __("Costa Rica", WPI);
      $this->options['countries']['HR'] = __("Croatia", WPI);
      $this->options['countries']['CY'] = __("Cyprus", WPI);
      $this->options['countries']['CZ'] = __("Czech Republic", WPI);
      $this->options['countries']['CD'] = __("Democratic Republic of the Congo", WPI);
      $this->options['countries']['DK'] = __("Denmark", WPI);
      $this->options['countries']['DJ'] = __("Djibouti", WPI);
      $this->options['countries']['DM'] = __("Dominica", WPI);
      $this->options['countries']['DO'] = __("Dominican Republic", WPI);
      $this->options['countries']['EC'] = __("Ecuador", WPI);
      $this->options['countries']['SV'] = __("El Salvador", WPI);
      $this->options['countries']['ER'] = __("Eritrea", WPI);
      $this->options['countries']['EE'] = __("Estonia", WPI);
      $this->options['countries']['ET'] = __("Ethiopia", WPI);
      $this->options['countries']['FK'] = __("Falkland Islands", WPI);
      $this->options['countries']['FO'] = __("Faroe Islands", WPI);
      $this->options['countries']['FM'] = __("Federated States of Micronesia", WPI);
      $this->options['countries']['FJ'] = __("Fiji", WPI);
      $this->options['countries']['FI'] = __("Finland", WPI);
      $this->options['countries']['FR'] = __("France", WPI);
      $this->options['countries']['GF'] = __("French Guiana", WPI);
      $this->options['countries']['PF'] = __("French Polynesia", WPI);
      $this->options['countries']['GA'] = __("Gabon Republic", WPI);
      $this->options['countries']['GM'] = __("Gambia", WPI);
      $this->options['countries']['DE'] = __("Germany", WPI);
      $this->options['countries']['GI'] = __("Gibraltar", WPI);
      $this->options['countries']['GR'] = __("Greece", WPI);
      $this->options['countries']['GL'] = __("Greenland", WPI);
      $this->options['countries']['GD'] = __("Grenada", WPI);
      $this->options['countries']['GP'] = __("Guadeloupe", WPI);
      $this->options['countries']['GT'] = __("Guatemala", WPI);
      $this->options['countries']['GN'] = __("Guinea", WPI);
      $this->options['countries']['GW'] = __("Guinea Bissau", WPI);
      $this->options['countries']['GY'] = __("Guyana", WPI);
      $this->options['countries']['HN'] = __("Honduras", WPI);
      $this->options['countries']['HK'] = __("Hong Kong", WPI);
      $this->options['countries']['HU'] = __("Hungary", WPI);
      $this->options['countries']['IS'] = __("Iceland", WPI);
      $this->options['countries']['IN'] = __("India", WPI);
      $this->options['countries']['ID'] = __("Indonesia", WPI);
      $this->options['countries']['IE'] = __("Ireland", WPI);
      $this->options['countries']['IL'] = __("Israel", WPI);
      $this->options['countries']['IT'] = __("Italy", WPI);
      $this->options['countries']['JM'] = __("Jamaica", WPI);
      $this->options['countries']['JP'] = __("Japan", WPI);
      $this->options['countries']['JO'] = __("Jordan", WPI);
      $this->options['countries']['KZ'] = __("Kazakhstan", WPI);
      $this->options['countries']['KE'] = __("Kenya", WPI);
      $this->options['countries']['KI'] = __("Kiribati", WPI);
      $this->options['countries']['KW'] = __("Kuwait", WPI);
      $this->options['countries']['KG'] = __("Kyrgyzstan", WPI);
      $this->options['countries']['LA'] = __("Laos", WPI);
      $this->options['countries']['LV'] = __("Latvia", WPI);
      $this->options['countries']['LS'] = __("Lesotho", WPI);
      $this->options['countries']['LI'] = __("Liechtenstein", WPI);
      $this->options['countries']['LT'] = __("Lithuania", WPI);
      $this->options['countries']['LU'] = __("Luxembourg", WPI);
      $this->options['countries']['MG'] = __("Madagascar", WPI);
      $this->options['countries']['MW'] = __("Malawi", WPI);
      $this->options['countries']['MY'] = __("Malaysia", WPI);
      $this->options['countries']['MV'] = __("Maldives", WPI);
      $this->options['countries']['ML'] = __("Mali", WPI);
      $this->options['countries']['MT'] = __("Malta", WPI);
      $this->options['countries']['MH'] = __("Marshall Islands", WPI);
      $this->options['countries']['MQ'] = __("Martinique", WPI);
      $this->options['countries']['MR'] = __("Mauritania", WPI);
      $this->options['countries']['MU'] = __("Mauritius", WPI);
      $this->options['countries']['YT'] = __("Mayotte", WPI);
      $this->options['countries']['MX'] = __("Mexico", WPI);
      $this->options['countries']['MN'] = __("Mongolia", WPI);
      $this->options['countries']['MS'] = __("Montserrat", WPI);
      $this->options['countries']['MA'] = __("Morocco", WPI);
      $this->options['countries']['MZ'] = __("Mozambique", WPI);
      $this->options['countries']['NA'] = __("Namibia", WPI);
      $this->options['countries']['NR'] = __("Nauru", WPI);
      $this->options['countries']['NP'] = __("Nepal", WPI);
      $this->options['countries']['NL'] = __("Netherlands", WPI);
      $this->options['countries']['AN'] = __("Netherlands Antilles", WPI);
      $this->options['countries']['NC'] = __("New Caledonia", WPI);
      $this->options['countries']['NZ'] = __("New Zealand", WPI);
      $this->options['countries']['NI'] = __("Nicaragua", WPI);
      $this->options['countries']['NE'] = __("Niger", WPI);
      $this->options['countries']['NU'] = __("Niue", WPI);
      $this->options['countries']['NF'] = __("Norfolk Island", WPI);
      $this->options['countries']['NO'] = __("Norway", WPI);
      $this->options['countries']['OM'] = __("Oman", WPI);
      $this->options['countries']['PW'] = __("Palau", WPI);
      $this->options['countries']['PA'] = __("Panama", WPI);
      $this->options['countries']['PG'] = __("Papua New Guinea", WPI);
      $this->options['countries']['PE'] = __("Peru", WPI);
      $this->options['countries']['PH'] = __("Philippines", WPI);
      $this->options['countries']['PN'] = __("Pitcairn Islands", WPI);
      $this->options['countries']['PL'] = __("Poland", WPI);
      $this->options['countries']['PT'] = __("Portugal", WPI);
      $this->options['countries']['QA'] = __("Qatar", WPI);
      $this->options['countries']['CG'] = __("Republic of the Congo", WPI);
      $this->options['countries']['RE'] = __("Reunion", WPI);
      $this->options['countries']['RO'] = __("Romania", WPI);
      $this->options['countries']['RU'] = __("Russia", WPI);
      $this->options['countries']['RW'] = __("Rwanda", WPI);
      $this->options['countries']['VC'] = __("Saint Vincent and the Grenadines", WPI);
      $this->options['countries']['WS'] = __("Samoa", WPI);
      $this->options['countries']['SM'] = __("San Marino", WPI);
      $this->options['countries']['ST'] = __("Sao Tome and Principe", WPI);
      $this->options['countries']['SA'] = __("Saudi Arabia", WPI);
      $this->options['countries']['SN'] = __("Senegal", WPI);
      $this->options['countries']['SC'] = __("Seychelles", WPI);
      $this->options['countries']['SL'] = __("Sierra Leone", WPI);
      $this->options['countries']['SG'] = __("Singapore", WPI);
      $this->options['countries']['SK'] = __("Slovakia", WPI);
      $this->options['countries']['SI'] = __("Slovenia", WPI);
      $this->options['countries']['SB'] = __("Solomon Islands", WPI);
      $this->options['countries']['SO'] = __("Somalia", WPI);
      $this->options['countries']['ZA'] = __("South Africa", WPI);
      $this->options['countries']['KR'] = __("South Korea", WPI);
      $this->options['countries']['ES'] = __("Spain", WPI);
      $this->options['countries']['LK'] = __("Sri Lanka", WPI);
      $this->options['countries']['SH'] = __("St. Helena", WPI);
      $this->options['countries']['KN'] = __("St. Kitts and Nevis", WPI);
      $this->options['countries']['LC'] = __("St. Lucia", WPI);
      $this->options['countries']['PM'] = __("St. Pierre and Miquelon", WPI);
      $this->options['countries']['SR'] = __("Suriname", WPI);
      $this->options['countries']['SJ'] = __("Svalbard and Jan Mayen Islands", WPI);
      $this->options['countries']['SZ'] = __("Swaziland", WPI);
      $this->options['countries']['SE'] = __("Sweden", WPI);
      $this->options['countries']['CH'] = __("Switzerland", WPI);
      $this->options['countries']['TW'] = __("Taiwan", WPI);
      $this->options['countries']['TJ'] = __("Tajikistan", WPI);
      $this->options['countries']['TZ'] = __("Tanzania", WPI);
      $this->options['countries']['TH'] = __("Thailand", WPI);
      $this->options['countries']['TG'] = __("Togo", WPI);
      $this->options['countries']['TO'] = __("Tonga", WPI);
      $this->options['countries']['TT'] = __("Trinidad and Tobago", WPI);
      $this->options['countries']['TN'] = __("Tunisia", WPI);
      $this->options['countries']['TR'] = __("Turkey", WPI);
      $this->options['countries']['TM'] = __("Turkmenistan", WPI);
      $this->options['countries']['TC'] = __("Turks and Caicos Islands", WPI);
      $this->options['countries']['TV'] = __("Tuvalu", WPI);
      $this->options['countries']['UG'] = __("Uganda", WPI);
      $this->options['countries']['UA'] = __("Ukraine", WPI);
      $this->options['countries']['AE'] = __("United Arab Emirates", WPI);
      $this->options['countries']['GB'] = __("United Kingdom", WPI);
      $this->options['countries']['UY'] = __("Uruguay", WPI);
      $this->options['countries']['VU'] = __("Vanuatu", WPI);
      $this->options['countries']['VA'] = __("Vatican City State", WPI);
      $this->options['countries']['VE'] = __("Venezuela", WPI);
      $this->options['countries']['VN'] = __("Vietnam", WPI);
      $this->options['countries']['WF'] = __("Wallis and Futuna Islands", WPI);
      $this->options['countries']['YE'] = __("Yemen", WPI);
      $this->options['countries']['ZM'] = __("Zambia", WPI);

      $this->options['states']['AL'] = __("Alabama", WPI);
      $this->options['states']['AK'] = __("Alaska", WPI);
      $this->options['states']['AS'] = __("American Samoa", WPI);
      $this->options['states']['AZ'] = __("Arizona", WPI);
      $this->options['states']['AR'] = __("Arkansas", WPI);
      $this->options['states']['CA'] = __("California", WPI);
      $this->options['states']['CO'] = __("Colorado", WPI);
      $this->options['states']['CT'] = __("Connecticut", WPI);
      $this->options['states']['DE'] = __("Delaware", WPI);
      $this->options['states']['DC'] = __("District of Columbia", WPI);
      $this->options['states']['FM'] = __("Federated States of Micronesia", WPI);
      $this->options['states']['FL'] = __("Florida", WPI);
      $this->options['states']['GA'] = __("Georgia", WPI);
      $this->options['states']['GU'] = __("Guam", WPI);
      $this->options['states']['HI'] = __("Hawaii", WPI);
      $this->options['states']['ID'] = __("Idaho", WPI);
      $this->options['states']['IL'] = __("Illinois", WPI);
      $this->options['states']['IN'] = __("Indiana", WPI);
      $this->options['states']['IA'] = __("Iowa", WPI);
      $this->options['states']['KS'] = __("Kansas", WPI);
      $this->options['states']['KY'] = __("Kentucky", WPI);
      $this->options['states']['LA'] = __("Louisiana", WPI);
      $this->options['states']['ME'] = __("Maine", WPI);
      $this->options['states']['MH'] = __("Marshall Islands", WPI);
      $this->options['states']['MD'] = __("Maryland", WPI);
      $this->options['states']['MA'] = __("Massachusetts", WPI);
      $this->options['states']['MI'] = __("Michigan", WPI);
      $this->options['states']['MN'] = __("Minnesota", WPI);
      $this->options['states']['MS'] = __("Mississippi", WPI);
      $this->options['states']['MO'] = __("Missouri", WPI);
      $this->options['states']['MT'] = __("Montana", WPI);
      $this->options['states']['NE'] = __("Nebraska", WPI);
      $this->options['states']['NV'] = __("Nevada", WPI);
      $this->options['states']['NH'] = __("New Hampshire", WPI);
      $this->options['states']['NJ'] = __("New Jersey", WPI);
      $this->options['states']['NM'] = __("New Mexico", WPI);
      $this->options['states']['NY'] = __("New York", WPI);
      $this->options['states']['NC'] = __("North Carolina", WPI);
      $this->options['states']['ND'] = __("North Dakota", WPI);
      $this->options['states']['MP'] = __("Northern Mariana Islands", WPI);
      $this->options['states']['OH'] = __("Ohio", WPI);
      $this->options['states']['OK'] = __("Oklahoma", WPI);
      $this->options['states']['OR'] = __("Oregon", WPI);
      $this->options['states']['PW'] = __("Palau", WPI);
      $this->options['states']['PA'] = __("Pennsylvania", WPI);
      $this->options['states']['PR'] = __("Puerto Rico", WPI);
      $this->options['states']['RI'] = __("Rhode Island", WPI);
      $this->options['states']['SC'] = __("South Carolina", WPI);
      $this->options['states']['SD'] = __("South Dakota", WPI);
      $this->options['states']['TN'] = __("Tennessee", WPI);
      $this->options['states']['TX'] = __("Texas", WPI);
      $this->options['states']['UT'] = __("Utah", WPI);
      $this->options['states']['VT'] = __("Vermont", WPI);
      $this->options['states']['VI'] = __("Virgin Islands", WPI);
      $this->options['states']['VA'] = __("Virginia", WPI);
      $this->options['states']['WA'] = __("Washington", WPI);
      $this->options['states']['WV'] = __("West Virginia", WPI);
      $this->options['states']['WI'] = __("Wisconsin", WPI);
      $this->options['states']['WY'] = __("Wyoming", WPI);
      $this->options['states']['AB'] = __("Alberta", WPI);
      $this->options['states']['BC'] = __("British Columbia", WPI);
      $this->options['states']['MB'] = __("Manitoba", WPI);
      $this->options['states']['NB'] = __("New Brunswick", WPI);
      $this->options['states']['NF'] = __("Newfoundland", WPI);
      $this->options['states']['NW'] = __("Northwest Territory", WPI);
      $this->options['states']['NS'] = __("Nova Scotia", WPI);
      $this->options['states']['ON'] = __("Ontario", WPI);
      $this->options['states']['PE'] = __("Prince Edward Island", WPI);
      $this->options['states']['QU'] = __("Quebec", WPI);
      $this->options['states']['SK'] = __("Saskatchewan", WPI);
      $this->options['states']['YT'] = __("Yukon Territory", WPI);

      $this->options['currency']['types']['AUD'] = __("Australian Dollars", WPI);
      $this->options['currency']['types']['CAD'] = __("Canadian Dollars", WPI);
      $this->options['currency']['types']['EUR'] = __("Euros", WPI);
      $this->options['currency']['types']['GBP'] = __("Pounds Sterling", WPI);
      $this->options['currency']['types']['JPY'] = __("Yen", WPI);
      $this->options['currency']['types']['USD'] = __("U.S. Dollars", WPI);
      $this->options['currency']['types']['NZD'] = __("New Zealand Dollar", WPI);
      $this->options['currency']['types']['CHF'] = __("Swiss Franc", WPI);
      $this->options['currency']['types']['HKD'] = __("Hong Kong Dollar", WPI);
      $this->options['currency']['types']['SGD'] = __("Singapore Dollar", WPI);
      $this->options['currency']['types']['SEK'] = __("Swedish Krona", WPI);
      $this->options['currency']['types']['DKK'] = __("Danish Krone", WPI);
      $this->options['currency']['types']['PLN'] = __("Polish Zloty", WPI);
      $this->options['currency']['types']['NOK'] = __("Norwegian Krone", WPI);
      $this->options['currency']['types']['HUF'] = __("Hungarian Forint", WPI);
      $this->options['currency']['types']['CZK'] = __("Czech Koruna", WPI);
      $this->options['currency']['types']['ILS'] = __("Israeli Shekel", WPI);
      $this->options['currency']['types']['MXN'] = __("Mexican Peso", WPI);
      $this->options['currency']['types']['ZAR'] = __("South African Rand", WPI);

      $this->options['currency']['symbol']['AUD'] = "JA==";
      $this->options['currency']['symbol']['CAD'] = "JA==";
      $this->options['currency']['symbol']['EUR'] = "4oKs";
      $this->options['currency']['symbol']['GBP'] = "wqM=";
      $this->options['currency']['symbol']['JPY'] = "wqU=";
      $this->options['currency']['symbol']['USD'] = "JA==";
      $this->options['currency']['symbol']['NZD'] = "JA==";
      $this->options['currency']['symbol']['CHF'] = "Q0hG";
      $this->options['currency']['symbol']['HKD'] = "JA==";
      $this->options['currency']['symbol']['SGD'] = "JA==";
      $this->options['currency']['symbol']['SEK'] = "a3I=";
      $this->options['currency']['symbol']['DKK'] = "a3Iu";
      $this->options['currency']['symbol']['PLN'] = "esWC";
      $this->options['currency']['symbol']['NOK'] = "a3I=";
      $this->options['currency']['symbol']['HUF'] = "RnQ=";
      $this->options['currency']['symbol']['CZK'] = "S8SN";
      $this->options['currency']['symbol']['ILS'] = "4oKq";
      $this->options['currency']['symbol']['MXN'] = "JA==";
      $this->options['currency']['symbol']['ZAR'] = "Ug==";

      foreach ($this->options['currency']['symbol'] as &$symbol){
        $symbol = base64_decode($symbol);
      }

      $this->options['currency']['default_currency_code']       = 'USD';
      $this->options['currency']['symbols_updated']             = true;
      $this->options['globals']['client_change_payment_method'] = 'true';
      $this->options['globals']['show_business_address']        = 'false';
      $this->options['globals']['show_quantities']              = 'false';

      /** Mail - Notification */
      $this->options['notification'][1]['name']    = __("New Invoice", WPI);
      $this->options['notification'][1]['subject'] = __("[New Invoice] %subject%", WPI);
      $this->options['notification'][1]['content'] = __("Dear %recipient%, \n\n%business_name% has sent you %recurring% invoice in the amount of %amount%. \n\n%description% \n\nYou may pay, view and print the invoice online by visiting the following link: \n%link% \n\nBest regards, \n%business_name% (%business_email%)", WPI);

      $this->options['notification'][2]['name']    = __("Reminder", WPI);
      $this->options['notification'][2]['subject'] = __("[Reminder] %subject%", WPI);
      $this->options['notification'][2]['content'] = __("Dear %recipient%, \n\n%business_name% has sent you a reminder for the %recurring% invoice in the amount of %amount%. \n\n%description% \n\nYou may pay, view and print the invoice online by visiting the following link: \n%link%. \n\nBest regards, \n%business_name% (%business_email%)", WPI);

      $this->options['notification'][3]['name']    = __('Send Receipt', WPI);
      $this->options['notification'][3]['subject'] = __("[Payment Received] %subject%", WPI);
      $this->options['notification'][3]['content'] = __("Dear %recipient%, \n\n%business_name% has received your payment for the %recurring% invoice in the amount of %amount%. \n\nThank you very much for your patronage. \n\nBest regards, \n%business_name% (%business_email%)", WPI);

    }

    /**
     * Saves passed settings
     *
     * @global array $wpi_settings
     * @param array $new_settings
     */
    function SaveSettings($new_settings) {
      global $wpi_settings;

      //** Set 'first_time_setup_ran' as 'true' to avoid loading First Time Setup Page in future */
      $new_settings['first_time_setup_ran'] = 'true';

      $this->options = WPI_Functions::array_merge_recursive_distinct($this->options, $new_settings);
      /** just fo now we use the merged options array and overwrite two brances with new values. It is the custom solution to be able detete currency. odokienko@UD */
      if(isset($new_settings['currency']) && $new_settings['currency']){
        $this->options['currency']['symbol'] = $new_settings['currency']['symbol'];
        $this->options['currency']['types'] = $new_settings['currency']['types'];
      }

      //** Process Special Settings */
      //** Default predefined services */
      $this->options['predefined_services'][0]['name']     = __("Web Design Services", WPI);
      $this->options['predefined_services'][0]['quantity'] = 1;
      $this->options['predefined_services'][0]['price']    = 30;
      $this->options['predefined_services'][1]['name']     = __("Web Development Services", WPI);
      $this->options['predefined_services'][1]['quantity'] = 1;
      $this->options['predefined_services'][1]['price']    = 30;

      $this->options['predefined_services'] = ( isset($new_settings['predefined_services']) ? $new_settings['predefined_services'] : $this->options['predefined_services'] );

      //** E-Mail Templates */
      if(isset($new_settings['notification'])) {
        $this->options['notification'] = $new_settings['notification'];
      }

      //** Process Special Settings */

      //** fix checkboxes */
      foreach($this->options['billing'] as $key => $value) {
        if(!isset($new_settings['billing'][$key]['allow'])) unset($this->options['billing'][$key]['allow']);
      }

      $checkbox_array = array('increment_invoice_id', 'send_thank_you_email', 'cc_thank_you_email', 'force_https', 'show_recurring_billing', 'send_invoice_creator_email');
      foreach($checkbox_array as $checkbox_name) {
        if(!isset($new_settings[$checkbox_name])) unset($this->options[$checkbox_name]);
      }

      $this->CommitUpdates();

      //** Update global variable */
      $wpi_settings = WPI_Functions::array_merge_recursive_distinct($wpi_settings, $this->options);
      //** Fix Predefined Services */
      $wpi_settings['predefined_services'] = $this->options['predefined_services'];
      //** Fix E-Mail Templates */
      $wpi_settings['notification'] = $this->options['notification'];
      wpi_gateway_base::sync_billing_objects();
    }

    /**
     * Load options from DB or from initial array
     */
    function LoadOptions() {
      //** Options concept taken from Theme My Login (http://webdesign.jaedub.com) */
      $this->InitOptions();
      $storedoptions = get_option( 'wpi_options' );

      $currency = $this->options['currency'];
      if ( $storedoptions && is_array( $storedoptions ) ) {
        foreach ( $storedoptions as $key => $value ) {
          $this->options[$key] = $value;
        }
        if (empty($storedoptions['currency']['symbols_updated'])){
          $this->options['currency'] = $currency;
          $this->options['currency']['symbols_updated']=true;
        }

      } else {
        update_option( 'wpi_options', $this->options);
      }

    }

    /**
     * Get an option value from options array.
     *
     * @param string $key
     * @return string|null
     */
    function GetOption( $key ) {
      if ( array_key_exists( $key, $this->options ) ) {
        return $this->options[$key];
      } else return null;
    }

    /**
     * Set an option value into DB
     *
     * @global array $wpi_settings
     * @param string $key
     * @param string $value
     * @param bool $group
     * @return bool
     */
    function setOption( $key, $value, $group = false) {
      global $wpi_settings;

      if(isset($this)) {
        $this->options[$key] = $value;
      } else {
        //** Handle option settings when not handled as object */

        if(!$value) {
          if($group) {
            unset($wpi_settings[$group][$key]);
          } else {
            unset($wpi_settings[$key]);
          }
        } else {
          if($group) {
            $wpi_settings[$group][$key] = $value;
          } else {
            $wpi_settings[$key] = $value;
          }
        }

        $settings = $wpi_settings;

        /* This element of array contain objects and should not be stored in DB */
        if(isset($settings['installed_gateways'])) {
          unset($settings['installed_gateways']);
        }

        if(update_option( 'wpi_options', $settings)) {
          return true;
        }
      }
    }

    /**
     * Commits options.
     *
     * @return bool
     */
    function CommitUpdates() {
      $oldvalue = get_option( 'wpi_options' );
      if( $oldvalue == $this->options )
        return false;
      else
        return update_option( 'wpi_options', $this->options );
    }

    /**
     * Converts old options to new.
     *
     * @global object $wpdb
     */
    function ConvertPre20Options() {
      global $wpdb;

      // Take all old wp_invoice options and convert them put them into a single option
      // DOESN"T WORK WITH BILLING OPTIONS SINCE THEY ARE NOW HELD IN MULTIMENSIONAL ARRAY
      $load_all_options = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE 'wp_invoice%'");

      if(is_array($load_all_options)) {
        $counter = 0;
        while(list($key,$entry) = each($load_all_options)) {
          $this->setOption(str_replace("wp_invoice_", "", $entry->option_name), $entry->option_value);
          delete_option($entry->option_name);
          $counter++;
        }
        echo "$counter ".__('old options found, converted into new format, and deleted.', WPI);
        $this->SaveOptions;
      }
    }
}