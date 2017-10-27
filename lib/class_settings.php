<?php
/**
 * Load all WP-Invoice settings from get_option( 'wpi_options' )
 * InitOptions are default settings, and loaded if wpi_options is not set.
 * All these settings are also stored in a global variable ($wpi_settings) for easy access
 */
class WPI_Settings {

  /**
   * Core
   * @var type 
   */
  var $Core;
  
  /**
   * Data
   * @var type 
   */
  var $data;

  /**
   * Construct
   *
   * @param object $Core
   */
  function __construct( &$Core ) {
    $this->Core = $Core;
    $this->LoadOptions();
  }

  /**
   * Initialize options
   */
  function InitOptions() {

    if ( isset( $Core ) && $Core ) {
      $this->options[ 'version' ] = $this->Core->version;
    }

    //** Default Invoice Types */
    $this->options[ 'types' ] = array(
      'invoice' => array( 'label' => __( 'Invoice', ud_get_wp_invoice()->domain ) ),
      'recurring' => array( 'label' => __( 'Recurring', ud_get_wp_invoice()->domain ) )
    );

    //** Localization Labels */
    $this->options[ 'custom_label_tax' ] = __( "Tax", ud_get_wp_invoice()->domain );

    //** WP-Invoice Lookup */
    $this->options[ 'lookup_text' ] = __( "Pay Your Invoice", ud_get_wp_invoice()->domain );
    $this->options[ 'lookup_submit' ] = __( "Lookup", ud_get_wp_invoice()->domain );

    //** Frontend Customization */
    $this->options[ 'use_custom_templates' ] = "false";
    $this->options[ 'state_selection' ] = __( "Dropdown", ud_get_wp_invoice()->domain );

    $this->options[ 'email_address' ] = get_bloginfo( 'admin_email' );
    $this->options[ 'business_name' ] = get_bloginfo( 'blogname' );
    $this->options[ 'business_address' ] = '';
    $this->options[ 'business_phone' ] = '';

    $this->options[ 'user_level' ] = 8;

    $this->options[ 'web_invoice_page' ] = '';
    $this->options[ 'where_to_display' ] = 'overwrite';

    //** Advanced Settings */
    $this->options[ 'allow_deposits' ] = 'true';

    //** Payment */
    $this->options[ 'client_change_payment_method' ] = 'false';

    //** Basic Settings */
    $this->options[ 'replace_page_title_with_subject' ] = 'true';
    $this->options[ 'using_godaddy' ] = 'no';
    $this->options[ 'use_wp_users' ] = 'true';
    $this->options[ 'first_time_setup_ran' ] = 'false';
    $this->options[ 'increment_invoice_id' ] = 'false';
    $this->options[ 'do_not_load_theme_specific_css' ] = 'false';
    $this->options[ 'cc_thank_you_email' ] = 'false';
    $this->options[ 'send_invoice_creator_email' ] = 'false';
    $this->options[ 'replace_page_heading_with_subject' ] = 'false';
    $this->options[ 'hide_page_title' ] = 'false';
    $this->options[ 'terms_acceptance_required' ] = 'false';

    $this->options[ 'use_css' ] = 'yes';
    $this->options[ 'force_https' ] = 'false';
    $this->options[ 'send_thank_you_email' ] = 'no';
    $this->options[ 'show_recurring_billing' ] = 'true';
    $this->options[ 'global_tax' ] = '0';

    $this->options[ 'user_meta' ][ 'required' ][ 'first_name' ] = __( 'First Name', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'required' ][ 'last_name' ] = __( 'Last Name', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'company_name' ] = __( 'Company Name', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'phonenumber' ] = __( 'Phone Number', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'streetaddress' ] = __( 'Street Address', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'city' ] = __( 'City', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'state' ] = __( 'State', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'zip' ] = __( 'ZIP', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'country' ] = __( 'Country', ud_get_wp_invoice()->domain );

    //** Invoice statuses. Filter: wpi_invoice_statuses */
    $this->options[ 'invoice_statuses' ][ 'active' ] = __( "Active", ud_get_wp_invoice()->domain );
    $this->options[ 'invoice_statuses' ][ 'archive' ] = __( "Archived", ud_get_wp_invoice()->domain );
    $this->options[ 'invoice_statuses' ][ 'trash' ] = __( "Trashed", ud_get_wp_invoice()->domain );
    $this->options[ 'invoice_statuses' ][ 'paid' ] = __( "Paid", ud_get_wp_invoice()->domain );

    $this->options[ 'countries' ][ 'US' ] = __( "United States", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AL' ] = __( "Albania", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'DZ' ] = __( "Algeria", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AD' ] = __( "Andorra", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AO' ] = __( "Angola", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AI' ] = __( "Anguilla", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AG' ] = __( "Antigua and Barbuda", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AR' ] = __( "Argentina", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AM' ] = __( "Armenia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AW' ] = __( "Aruba", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AU' ] = __( "Australia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AT' ] = __( "Austria", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AZ' ] = __( "Azerbaijan Republic", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BS' ] = __( "Bahamas", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BH' ] = __( "Bahrain", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BB' ] = __( "Barbados", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BE' ] = __( "Belgium", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BZ' ] = __( "Belize", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BJ' ] = __( "Benin", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BM' ] = __( "Bermuda", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BT' ] = __( "Bhutan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BO' ] = __( "Bolivia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BA' ] = __( "Bosnia and Herzegovina", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BW' ] = __( "Botswana", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BR' ] = __( "Brazil", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'VG' ] = __( "British Virgin Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BN' ] = __( "Brunei", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BG' ] = __( "Bulgaria", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BF' ] = __( "Burkina Faso", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'BI' ] = __( "Burundi", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KH' ] = __( "Cambodia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CA' ] = __( "Canada", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CV' ] = __( "Cape Verde", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KY' ] = __( "Cayman Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TD' ] = __( "Chad", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CL' ] = __( "Chile", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'C2' ] = __( "China", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CO' ] = __( "Colombia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KM' ] = __( "Comoros", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CK' ] = __( "Cook Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CR' ] = __( "Costa Rica", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'HR' ] = __( "Croatia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CY' ] = __( "Cyprus", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CZ' ] = __( "Czech Republic", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CD' ] = __( "Democratic Republic of the Congo", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'DK' ] = __( "Denmark", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'DJ' ] = __( "Djibouti", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'DM' ] = __( "Dominica", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'DO' ] = __( "Dominican Republic", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'EC' ] = __( "Ecuador", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SV' ] = __( "El Salvador", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ER' ] = __( "Eritrea", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'EE' ] = __( "Estonia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ET' ] = __( "Ethiopia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'FK' ] = __( "Falkland Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'FO' ] = __( "Faroe Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'FM' ] = __( "Federated States of Micronesia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'FJ' ] = __( "Fiji", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'FI' ] = __( "Finland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'FR' ] = __( "France", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GF' ] = __( "French Guiana", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PF' ] = __( "French Polynesia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GA' ] = __( "Gabon Republic", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GM' ] = __( "Gambia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'DE' ] = __( "Germany", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GI' ] = __( "Gibraltar", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GR' ] = __( "Greece", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GL' ] = __( "Greenland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GD' ] = __( "Grenada", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GP' ] = __( "Guadeloupe", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GT' ] = __( "Guatemala", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GN' ] = __( "Guinea", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GW' ] = __( "Guinea Bissau", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GY' ] = __( "Guyana", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'HN' ] = __( "Honduras", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'HK' ] = __( "Hong Kong", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'HU' ] = __( "Hungary", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'IS' ] = __( "Iceland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'IN' ] = __( "India", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ID' ] = __( "Indonesia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'IE' ] = __( "Ireland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'IL' ] = __( "Israel", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'IT' ] = __( "Italy", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'JM' ] = __( "Jamaica", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'JP' ] = __( "Japan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'JO' ] = __( "Jordan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KZ' ] = __( "Kazakhstan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KE' ] = __( "Kenya", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KI' ] = __( "Kiribati", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KW' ] = __( "Kuwait", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KG' ] = __( "Kyrgyzstan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LA' ] = __( "Laos", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LV' ] = __( "Latvia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LS' ] = __( "Lesotho", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LI' ] = __( "Liechtenstein", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LT' ] = __( "Lithuania", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LU' ] = __( "Luxembourg", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MG' ] = __( "Madagascar", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MW' ] = __( "Malawi", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MY' ] = __( "Malaysia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MV' ] = __( "Maldives", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ML' ] = __( "Mali", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MT' ] = __( "Malta", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MH' ] = __( "Marshall Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MQ' ] = __( "Martinique", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MR' ] = __( "Mauritania", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MU' ] = __( "Mauritius", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'YT' ] = __( "Mayotte", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MX' ] = __( "Mexico", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MN' ] = __( "Mongolia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MS' ] = __( "Montserrat", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MA' ] = __( "Morocco", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'MZ' ] = __( "Mozambique", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NA' ] = __( "Namibia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NR' ] = __( "Nauru", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NP' ] = __( "Nepal", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NL' ] = __( "Netherlands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AN' ] = __( "Netherlands Antilles", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NC' ] = __( "New Caledonia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NZ' ] = __( "New Zealand", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NI' ] = __( "Nicaragua", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NE' ] = __( "Niger", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NU' ] = __( "Niue", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NF' ] = __( "Norfolk Island", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'NO' ] = __( "Norway", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'OM' ] = __( "Oman", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PW' ] = __( "Palau", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PA' ] = __( "Panama", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PG' ] = __( "Papua New Guinea", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PE' ] = __( "Peru", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PH' ] = __( "Philippines", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PN' ] = __( "Pitcairn Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PL' ] = __( "Poland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PT' ] = __( "Portugal", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'QA' ] = __( "Qatar", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CG' ] = __( "Republic of the Congo", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'RE' ] = __( "Reunion", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'RO' ] = __( "Romania", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'RU' ] = __( "Russia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'RW' ] = __( "Rwanda", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'VC' ] = __( "Saint Vincent and the Grenadines", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'WS' ] = __( "Samoa", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SM' ] = __( "San Marino", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ST' ] = __( "Sao Tome and Principe", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SA' ] = __( "Saudi Arabia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SN' ] = __( "Senegal", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SC' ] = __( "Seychelles", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SL' ] = __( "Sierra Leone", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SG' ] = __( "Singapore", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SK' ] = __( "Slovakia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SI' ] = __( "Slovenia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SB' ] = __( "Solomon Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SO' ] = __( "Somalia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ZA' ] = __( "South Africa", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KR' ] = __( "South Korea", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ES' ] = __( "Spain", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LK' ] = __( "Sri Lanka", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SH' ] = __( "St. Helena", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'KN' ] = __( "St. Kitts and Nevis", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'LC' ] = __( "St. Lucia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'PM' ] = __( "St. Pierre and Miquelon", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SR' ] = __( "Suriname", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SJ' ] = __( "Svalbard and Jan Mayen Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SZ' ] = __( "Swaziland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'SE' ] = __( "Sweden", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'CH' ] = __( "Switzerland", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TW' ] = __( "Taiwan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TJ' ] = __( "Tajikistan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TZ' ] = __( "Tanzania", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TH' ] = __( "Thailand", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TG' ] = __( "Togo", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TO' ] = __( "Tonga", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TT' ] = __( "Trinidad and Tobago", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TN' ] = __( "Tunisia", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TR' ] = __( "Turkey", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TM' ] = __( "Turkmenistan", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TC' ] = __( "Turks and Caicos Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'TV' ] = __( "Tuvalu", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'UG' ] = __( "Uganda", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'UA' ] = __( "Ukraine", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'AE' ] = __( "United Arab Emirates", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'GB' ] = __( "United Kingdom", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'UY' ] = __( "Uruguay", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'VU' ] = __( "Vanuatu", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'VA' ] = __( "Vatican City State", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'VE' ] = __( "Venezuela", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'VN' ] = __( "Vietnam", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'WF' ] = __( "Wallis and Futuna Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'YE' ] = __( "Yemen", ud_get_wp_invoice()->domain );
    $this->options[ 'countries' ][ 'ZM' ] = __( "Zambia", ud_get_wp_invoice()->domain );

    $this->options[ 'states' ][ 'AL' ] = __( "Alabama", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'AK' ] = __( "Alaska", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'AS' ] = __( "American Samoa", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'AZ' ] = __( "Arizona", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'AR' ] = __( "Arkansas", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'CA' ] = __( "California", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'CO' ] = __( "Colorado", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'CT' ] = __( "Connecticut", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'DE' ] = __( "Delaware", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'DC' ] = __( "District of Columbia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'FM' ] = __( "Federated States of Micronesia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'FL' ] = __( "Florida", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'GA' ] = __( "Georgia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'GU' ] = __( "Guam", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'HI' ] = __( "Hawaii", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'ID' ] = __( "Idaho", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'IL' ] = __( "Illinois", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'IN' ] = __( "Indiana", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'IA' ] = __( "Iowa", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'KS' ] = __( "Kansas", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'KY' ] = __( "Kentucky", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'LA' ] = __( "Louisiana", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'ME' ] = __( "Maine", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MH' ] = __( "Marshall Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MD' ] = __( "Maryland", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MA' ] = __( "Massachusetts", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MI' ] = __( "Michigan", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MN' ] = __( "Minnesota", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MS' ] = __( "Mississippi", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MO' ] = __( "Missouri", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MT' ] = __( "Montana", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NE' ] = __( "Nebraska", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NV' ] = __( "Nevada", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NH' ] = __( "New Hampshire", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NJ' ] = __( "New Jersey", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NM' ] = __( "New Mexico", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NY' ] = __( "New York", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NC' ] = __( "North Carolina", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'ND' ] = __( "North Dakota", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MP' ] = __( "Northern Mariana Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'OH' ] = __( "Ohio", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'OK' ] = __( "Oklahoma", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'OR' ] = __( "Oregon", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'PW' ] = __( "Palau", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'PA' ] = __( "Pennsylvania", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'PR' ] = __( "Puerto Rico", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'RI' ] = __( "Rhode Island", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'SC' ] = __( "South Carolina", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'SD' ] = __( "South Dakota", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'TN' ] = __( "Tennessee", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'TX' ] = __( "Texas", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'UT' ] = __( "Utah", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'VT' ] = __( "Vermont", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'VI' ] = __( "Virgin Islands", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'VA' ] = __( "Virginia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'WA' ] = __( "Washington", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'WV' ] = __( "West Virginia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'WI' ] = __( "Wisconsin", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'WY' ] = __( "Wyoming", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'AB' ] = __( "Alberta", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'BC' ] = __( "British Columbia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'MB' ] = __( "Manitoba", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NB' ] = __( "New Brunswick", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NF' ] = __( "Newfoundland", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NW' ] = __( "Northwest Territory", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'NS' ] = __( "Nova Scotia", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'ON' ] = __( "Ontario", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'PE' ] = __( "Prince Edward Island", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'QU' ] = __( "Quebec", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'SK' ] = __( "Saskatchewan", ud_get_wp_invoice()->domain );
    $this->options[ 'states' ][ 'YT' ] = __( "Yukon Territory", ud_get_wp_invoice()->domain );

    $this->options[ 'currency' ][ 'types' ][ 'AUD' ] = __( "Australian Dollars", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'CAD' ] = __( "Canadian Dollars", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'EUR' ] = __( "Euro", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'GBP' ] = __( "Pounds Sterling", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'JPY' ] = __( "Yen", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'USD' ] = __( "U.S. Dollars", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'NZD' ] = __( "New Zealand Dollar", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'CHF' ] = __( "Swiss Franc", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'HKD' ] = __( "Hong Kong Dollar", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'SGD' ] = __( "Singapore Dollar", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'SEK' ] = __( "Swedish Krona", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'DKK' ] = __( "Danish Krone", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'PLN' ] = __( "Polish Zloty", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'NOK' ] = __( "Norwegian Krone", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'HUF' ] = __( "Hungarian Forint", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'CZK' ] = __( "Czech Koruna", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'ILS' ] = __( "Israeli Shekel", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'MXN' ] = __( "Mexican Peso", ud_get_wp_invoice()->domain );
    $this->options[ 'currency' ][ 'types' ][ 'ZAR' ] = __( "South African Rand", ud_get_wp_invoice()->domain );

    $this->options[ 'currency' ][ 'symbol' ][ 'AUD' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'CAD' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'EUR' ] = "4oKs";
    $this->options[ 'currency' ][ 'symbol' ][ 'GBP' ] = "wqM=";
    $this->options[ 'currency' ][ 'symbol' ][ 'JPY' ] = "wqU=";
    $this->options[ 'currency' ][ 'symbol' ][ 'USD' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'NZD' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'CHF' ] = "Q0hG";
    $this->options[ 'currency' ][ 'symbol' ][ 'HKD' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'SGD' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'SEK' ] = "a3I=";
    $this->options[ 'currency' ][ 'symbol' ][ 'DKK' ] = "a3Iu";
    $this->options[ 'currency' ][ 'symbol' ][ 'PLN' ] = "esWC";
    $this->options[ 'currency' ][ 'symbol' ][ 'NOK' ] = "a3I=";
    $this->options[ 'currency' ][ 'symbol' ][ 'HUF' ] = "RnQ=";
    $this->options[ 'currency' ][ 'symbol' ][ 'CZK' ] = "S8SN";
    $this->options[ 'currency' ][ 'symbol' ][ 'ILS' ] = "4oKq";
    $this->options[ 'currency' ][ 'symbol' ][ 'MXN' ] = "JA==";
    $this->options[ 'currency' ][ 'symbol' ][ 'ZAR' ] = "Ug==";

    foreach ( $this->options[ 'currency' ][ 'symbol' ] as &$symbol ) {
      $symbol = base64_decode( $symbol );
    }

    $this->options[ 'currency' ][ 'default_currency_code' ] = 'USD';
    $this->options[ 'currency' ][ 'symbols_updated' ] = true;
    $this->options[ 'globals' ][ 'client_change_payment_method' ] = 'true';
    $this->options[ 'globals' ][ 'show_business_address' ] = 'false';
    $this->options[ 'globals' ][ 'show_quantities' ] = 'false';

    //** Mail - Notification */
    $this->options[ 'notification' ][ 1 ][ 'name' ] = __( "New Invoice", ud_get_wp_invoice()->domain );
    $this->options[ 'notification' ][ 1 ][ 'subject' ] = __( "[New Invoice] %subject%", ud_get_wp_invoice()->domain );
    $this->options[ 'notification' ][ 1 ][ 'content' ] = __( "Dear %recipient%, \n\n%business_name% has sent you the %type% in the amount of %amount%. \n\n%description% \n\nYou may pay, view and print the invoice online by visiting the following link: \n%link% \n\nBest regards, \n%business_name% (%business_email%)", ud_get_wp_invoice()->domain );

    $this->options[ 'notification' ][ 2 ][ 'name' ] = __( "Reminder", ud_get_wp_invoice()->domain );
    $this->options[ 'notification' ][ 2 ][ 'subject' ] = __( "[Reminder] %subject%", ud_get_wp_invoice()->domain );
    $this->options[ 'notification' ][ 2 ][ 'content' ] = __( "Dear %recipient%, \n\n%business_name% has sent you a reminder for the %type% in the amount of %amount%. \n\n%description% \n\nYou may pay, view and print the invoice online by visiting the following link: \n%link%. \n\nBest regards, \n%business_name% (%business_email%)", ud_get_wp_invoice()->domain );

    $this->options[ 'notification' ][ 3 ][ 'name' ] = __( 'Send Receipt', ud_get_wp_invoice()->domain );
    $this->options[ 'notification' ][ 3 ][ 'subject' ] = __( "[Payment Received] %subject%", ud_get_wp_invoice()->domain );
    $this->options[ 'notification' ][ 3 ][ 'content' ] = __( "Dear %recipient%, \n\n%business_name% has received your payment for the %type% in the amount of %amount%. \n\nThank you very much for your patronage. \n\nBest regards, \n%business_name% (%business_email%)", ud_get_wp_invoice()->domain );

  }

  /**
   * Saves passed settings
   *
   * @global array $wpi_settings
   *
   * @param array $new_settings
   */
  function SaveSettings( $new_settings ) {
    global $wpi_settings;

    //** Set 'first_time_setup_ran' as 'true' to avoid loading First Time Setup Page in future */
    $new_settings[ 'first_time_setup_ran' ] = 'true';

    $this->options = WPI_Functions::array_merge_recursive_distinct( $this->options, $new_settings );
    //** just fo now we use the merged options array and overwrite two brances with new values. It is the custom solution to be able detete currency. odokienko@UD */
    if ( isset( $new_settings[ 'currency' ] ) && $new_settings[ 'currency' ] ) {
      $this->options[ 'currency' ][ 'symbol' ] = $new_settings[ 'currency' ][ 'symbol' ];
      $this->options[ 'currency' ][ 'types' ] = $new_settings[ 'currency' ][ 'types' ];
    }

    //** Process Special Settings */
    //** Default predefined services */
    $this->options[ 'predefined_services' ][ 0 ][ 'name' ] = __( "Web Design Services", ud_get_wp_invoice()->domain );
    $this->options[ 'predefined_services' ][ 0 ][ 'quantity' ] = 1;
    $this->options[ 'predefined_services' ][ 0 ][ 'price' ] = 30;
    $this->options[ 'predefined_services' ][ 1 ][ 'name' ] = __( "Web Development Services", ud_get_wp_invoice()->domain );
    $this->options[ 'predefined_services' ][ 1 ][ 'quantity' ] = 1;
    $this->options[ 'predefined_services' ][ 1 ][ 'price' ] = 30;

    $this->options[ 'predefined_services' ] = ( isset( $new_settings[ 'predefined_services' ] ) ? $new_settings[ 'predefined_services' ] : $this->options[ 'predefined_services' ] );

    $this->options[ 'user_meta' ][ 'custom' ][ 'company_name' ] = __( 'Company Name', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'phonenumber' ] = __( 'Phone Number', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'streetaddress' ] = __( 'Street Address', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'city' ] = __( 'City', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'state' ] = __( 'State', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'zip' ] = __( 'ZIP', ud_get_wp_invoice()->domain );
    $this->options[ 'user_meta' ][ 'custom' ][ 'country' ] = __( 'Country', ud_get_wp_invoice()->domain );

    //** E-Mail Templates */
    if ( isset( $new_settings[ 'notification' ] ) ) {
      $this->options[ 'notification' ] = $new_settings[ 'notification' ];
    }

    //** fix checkboxes */
    foreach ( $this->options[ 'billing' ] as $key => $value ) {
      if ( !isset( $new_settings[ 'billing' ][ $key ][ 'allow' ] ) ) unset( $this->options[ 'billing' ][ $key ][ 'allow' ] );
    }

    $checkbox_array = array( 'increment_invoice_id', 'send_thank_you_email', 'cc_thank_you_email', 'force_https', 'show_recurring_billing', 'send_invoice_creator_email' );
    foreach ( $checkbox_array as $checkbox_name ) {
      if ( !isset( $new_settings[ $checkbox_name ] ) ) unset( $this->options[ $checkbox_name ] );
    }

    $this->options = apply_filters( 'wpi_options_pre_commit_updates', $this->options, $new_settings );

    $this->CommitUpdates();

    //** Update global variable */
    $wpi_settings = WPI_Functions::array_merge_recursive_distinct( $wpi_settings, $this->options );
    //** Fix Predefined Services */
    $wpi_settings[ 'predefined_services' ] = $this->options[ 'predefined_services' ];
    //** Fix E-Mail Templates */
    $wpi_settings[ 'notification' ] = $this->options[ 'notification' ];
    wpi_gateway_base::sync_billing_objects();
  }

  /**
   * Load options from DB or from initial array
   */
  function LoadOptions() {
    $this->InitOptions();
    $storedoptions = get_option( 'wpi_options' );

    $currency = $this->options[ 'currency' ];
    if ( $storedoptions && is_array( $storedoptions ) ) {
      foreach ( $storedoptions as $key => $value ) {
        $this->options[ $key ] = $value;
      }
      if ( empty( $storedoptions[ 'currency' ][ 'symbols_updated' ] ) ) {
        $this->options[ 'currency' ] = $currency;
        $this->options[ 'currency' ][ 'symbols_updated' ] = true;
      }

    } else {
      update_option( 'wpi_options', $this->options );
    }
  }

  /**
   * Get an option value from options array.
   *
   * @param string $key
   *
   * @return string|null
   */
  function GetOption( $key ) {
    if ( array_key_exists( $key, $this->options ) ) {
      return $this->options[ $key ];
    } else return null;
  }

  /**
   * Set an option value into DB
   *
   * @global array $wpi_settings
   *
   * @param string $key
   * @param string $value
   * @param bool $group
   *
   * @return bool
   */
  function setOption( $key, $value, $group = false ) {
    global $wpi_settings;

    if ( isset( $this ) ) {
      $this->options[ $key ] = $value;
    } else {
      //** Handle option settings when not handled as object */

      if ( !$value ) {
        if ( $group ) {
          unset( $wpi_settings[ $group ][ $key ] );
        } else {
          unset( $wpi_settings[ $key ] );
        }
      } else {
        if ( $group ) {
          $wpi_settings[ $group ][ $key ] = $value;
        } else {
          $wpi_settings[ $key ] = $value;
        }
      }

      $settings = $wpi_settings;

      //** This element of array contain objects and should not be stored in DB */
      if ( isset( $settings[ 'installed_gateways' ] ) ) {
        unset( $settings[ 'installed_gateways' ] );
      }

      if ( update_option( 'wpi_options', $settings ) ) {
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
    if ( $oldvalue == $this->options ) {
      return false;
    } else {
      return update_option( 'wpi_options', $this->options );
    }
  }

  /**
   * Converts old options to new.
   *
   * @global object $wpdb
   */
  function ConvertPre20Options() {
    global $wpdb;
    $load_all_options = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE 'wp_invoice%'" );

    if ( is_array( $load_all_options ) ) {
      $counter = 0;
      while ( list( $key, $entry ) = each( $load_all_options ) ) {
        $this->setOption( str_replace( "wp_invoice_", "", $entry->option_name ), $entry->option_value );
        delete_option( $entry->option_name );
        $counter++;
      }
      echo "$counter " . __( 'old options found, converted into new format, and deleted.', ud_get_wp_invoice()->domain );
      $this->SaveOptions;
    }
  }
}