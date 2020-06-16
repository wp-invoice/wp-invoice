<?php

/**
 * PayPal NVP Transactions, Codes and Convenience Functions
 * @author korotkov@ud
 */

namespace UsabilityDynamics\WPI_PPP {

  if (!class_exists('UsabilityDynamics\WPI_PPP\HelperCodes')) {

    class HelperCodes {

      static $AvsResponse = array(
        'A' => array('message' => 'Address', 'details' => 'Address Only (no ZIP)'),
        'B' => array('message' => 'International "A"', 'details' => 'Address Only (no ZIP)'),
        'C' => array('message' => 'International "N"', 'details' => 'None - The transaction is declined.'),
        'D' => array('message' => 'International "X"', 'details' => 'Address and Postal Code'),
        'E' => array('message' => 'Not Allowed for MOTO (Internet/Phone) transactions', 'details' => 'Not applicable - The transaction is declined.'),
        'F' => array('message' => 'UK-Specific "X"', 'details' => 'Address and Postal Code'),
        'G' => array('message' => 'Global Unavailable', 'details' => 'Not applicable'),
        'I' => array('message' => 'International Unavailable', 'details' => 'Not applicable'),
        'N' => array('message' => 'No', 'details' => 'None - The transaction is declined.'),
        'P' => array('message' => 'Postal (International "Z")', 'details' => 'Postal Code only (no Address)'),
        'R' => array('message' => 'Retry', 'details' => 'Not Applicable'),
        'S' => array('message' => 'Service Not Supported', 'details' => 'Not Applicable'),
        'U' => array('message' => 'Unavailable', 'details' => 'Not Applicable'),
        'W' => array('message' => 'Whole ZIP', 'details' => 'Nine-digit ZIP code (no Address)'),
        'X' => array('message' => 'Exact Match', 'details' => 'Address and nine-digit ZIP code'),
        'Y' => array('message' => 'Yes', 'details' => 'Address and five-digit ZIP'),
        'Z' => array('message' => 'ZIP', 'details' => 'Five-digit ZIP code (no Address)'),
        '' => array('message' => 'Error', 'details' => 'Not Applicable')
      ); //$AvsResponse
      static $CvvResponse = array(
        'M' => array('message' => 'Match', 'details' => 'CVV2'),
        'N' => array('message' => 'No Match', 'details' => 'None'),
        'P' => array('message' => 'Not Processed', 'details' => 'Not Applicable'),
        'S' => array('message' => 'Service not supported', 'details' => 'Not Applicable'),
        'U' => array('message' => 'Service not available', 'details' => 'Not Applicable'),
        'X' => array('message' => 'No response', 'details' => 'Not Applicable')
      ); //$CvvResponse
      static $countries = array(
        "US" => "United States", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AI" => "Anguilla", "AG" => "Antigua and Barbuda",
        "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan Republic", "BS" => "Bahamas", "BH" => "Bahrain",
        "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina", "BW" => "Botswana", "BR" => "Brazil", "VG" => "British Virgin Islands", "BN" => "Brunei", "BG" => "Bulgaria", "BF" => "Burkina Faso",
        "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CL" => "Chile", "C2" => "China", "CO" => "Colombia",
        "CK" => "Cook Islands", "CR" => "Costa Rica", "CI" => "Cote D'Ivoire", "HR" => "Croatia", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti",
        "DM" => "Dominica", "DO" => "Dominican Republic", "TP" => "East Timor", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "EE" => "Estonia",
        "FM" => "Federated States of Micronesia", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "GA" => "Gabon Republic",
        "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala",
        "GN" => "Guinea", "GY" => "Guyana", "HT" => "Haiti", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia",
        "IE" => "Ireland", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KW" => "Kuwait", "LA" => "Laos",
        "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macau", "MK" => "Macedonia", "MG" => "Madagascar", "MY" => "Malaysia",
        "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MU" => "Mauritius", "MX" => "Mexico", "MD" => "Moldova", "MN" => "Mongolia",
        "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "NA" => "Namibia", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NZ" => "New Zealand",
        "NI" => "Nicaragua", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestine", "PA" => "Panama",
        "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar",
        "RO" => "Romania", "RU" => "Russia", "RW" => "Rwanda", "VC" => "Saint Vincent and the Grenadines", "WS" => "Samoa", "SA" => "Saudi Arabia", "SN" => "Senegal",
        "CS" => "Serbia and Montenegro", "SC" => "Seychelles", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "ZA" => "South Africa",
        "KR" => "South Korea", "ES" => "Spain", "LK" => "Sri Lanka", "KN" => "St. Kitts and Nevis", "LC" => "St. Lucia", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland",
        "TW" => "Taiwan", "TZ" => "Tanzania", "TH" => "Thailand", "TG" => "Togo", "TO" => "Tonga", "TT" => "Trinidad and Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands", "UG" => "Uganda", "UA" => "Ukraine", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "UY" => "Uruguay", "UZ" => "Uzbekistan",
        "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Vietnam", "VI" => "Virgin Islands (USA)", "YE" => "Yemen", "ZM" => "Zambia"
      ); //$countries
      static $states = array(
        'US' => array(
          'AL' => "Alabama", 'AK' => "Alaska", 'AZ' => "Arizona", 'AR' => "Arkansas", 'CA' => "California", 'CO' => "Colorado", 'CT' => "Connecticut", 'DE' => "Delaware",
          'DC' => "District Of Columbia", 'FL' => "Florida", 'GA' => "Georgia", 'HI' => "Hawaii", 'ID' => "Idaho", 'IL' => "Illinois", 'IN' => "Indiana", 'IA' => "Iowa",
          'KS' => "Kansas", 'KY' => "Kentucky", 'LA' => "Louisiana", 'ME' => "Maine", 'MD' => "Maryland", 'MA' => "Massachusetts", 'MI' => "Michigan", 'MN' => "Minnesota",
          'MS' => "Mississippi", 'MO' => "Missouri", 'MT' => "Montana", 'NE' => "Nebraska", 'NV' => "Nevada", 'NH' => "New Hampshire", 'NJ' => "New Jersey", 'NM' => "New Mexico",
          'NY' => "New York", 'NC' => "North Carolina", 'ND' => "North Dakota", 'OH' => "Ohio", 'OK' => "Oklahoma", 'OR' => "Oregon", 'PA' => "Pennsylvania", 'RI' => "Rhode Island",
          'SC' => "South Carolina", 'SD' => "South Dakota", 'TN' => "Tennessee", 'TX' => "Texas", 'UT' => "Utah", 'VT' => "Vermont", 'VA' => "Virginia", 'WA' => "Washington",
          'WV' => "West Virginia", 'WI' => "Wisconsin", 'WY' => "Wyoming", "AA" => "AA", "AE" => "AE", "AP" => "AP", "AS" => "AS", "FM" => "FM", "GU" => "GU", "MH" => "MH", "MP" => "MP", "PR" => "PR",
          "PW" => "PW", "VI" => "US Virgin Islands"
        ),
        'CA' => array(
          "AB" => "Alberta", "BC" => "British Columbia", "MB" => "Manitoba", "NB" => "New Brunswick", "NL" => "Newfoundland", "NS" => "Nova Scotia",
          "NU" => "Nunavut", "NT" => "Northwest Territories", "ON" => "Ontario", "PE" => "Prince Edward Island", "QC" => "Quebec", "SK" => "Saskatchewan",
          "YT" => "Yukon"
        ),
        'AU' => array(
          "Australian Capital Territory" => "Australian Capital Territory", "New South Wales" => "New South Wales", "Northern Territory" => "Northern Territory",
          "Queensland" => "Queensland", "South Australia" => "South Australia", "Tasmania" => "Tasmania", "Victoria" => "Victoria", "Western Australia" => "Western Australia"
        ),
        'GB' => array(
          "Aberdeen City" => "Aberdeen City", "Aberdeenshire" => "Aberdeenshire", "Angus" => "Angus", "Antrim" => "Antrim", "Argyll and Bute" => "Argyll and Bute",
          "Armagh" => "Armagh", "Avon" => "Avon", "Bedfordshire" => "Bedfordshire", "Berkshire" => "Berkshire", "Blaenau Gwent" => "Blaenau Gwent", "Borders" => "Borders",
          "Bridgend" => "Bridgend", "Bristol" => "Bristol", "Buckinghamshire" => "Buckinghamshire", "Caerphilly" => "Caerphilly", "Cambridgeshire" => "Cambridgeshire",
          "Cardiff" => "Cardiff", "Carmarthenshire" => "Carmarthenshire", "Ceredigion" => "Ceredigion", "Channel Islands" => "Channel Islands", "Cheshire" => "Cheshire",
          "Clackmannan" => "Clackmannan", "Cleveland" => "Cleveland", "Conwy" => "Conwy", "Cornwall" => "Cornwall", "Cumbria" => "Cumbria", "Denbighshire" => "Denbighshire",
          "Derbyshire" => "Derbyshire", "Devon" => "Devon", "Dorset" => "Dorset", "Down" => "Down", "Dumfries and Galloway" => "Dumfries and Galloway", "Durham" => "Durham",
          "East Ayrshire" => "East Ayrshire", "East Dunbartonshire" => "East Dunbartonshire", "East Lothian" => "East Lothian", "East Renfrewshire" => "East Renfrewshire",
          "East Riding of Yorkshire" => "East Riding of Yorkshire", "East Sussex" => "East Sussex", "Edinburgh City" => "Edinburgh City", "Essex" => "Essex",
          "Falkirk" => "Falkirk", "Fermanagh" => "Fermanagh", "Fife" => "Fife", "Flintshire" => "Flintshire", "Glasgow" => "Glasgow", "Gloucestershire" => "Gloucestershire",
          "Greater Manchester" => "Greater Manchester", "Gwynedd" => "Gwynedd", "Hampshire" => "Hampshire", "Herefordshire" => "Herefordshire",
          "Hertfordshire" => "Hertfordshire", "Highland" => "Highland", "Humberside" => "Humberside", "Inverclyde" => "Inverclyde", "Isle of Anglesey" => "Isle of Anglesey",
          "Isle of Man" => "Isle of Man", "Isle of Wight" => "Isle of Wight", "Isles of Scilly" => "Isles of Scilly", "Kent" => "Kent", "Lancashire" => "Lancashire",
          "Leicestershire" => "Leicestershire", "Lincolnshire" => "Lincolnshire", "London" => "London", "Londonderry" => "Londonderry", "Merseyside" => "Merseyside",
          "Merthyr Tydfil" => "Merthyr Tydfil", "Middlesex" => "Middlesex", "Midlothian" => "Midlothian", "Monmouthshire" => "Monmouthshire", "Moray" => "Moray",
          "Neath Port Talbot" => "Neath Port Talbot", "Newport" => "Newport", "Norfolk" => "Norfolk", "North Ayrshire" => "North Ayrshire",
          "North Lanarkshire" => "North Lanarkshire", "North Yorkshire" => "North Yorkshire", "Northamptonshire" => "Northamptonshire", "Northumberland" => "Northumberland",
          "Nottinghamshire" => "Nottinghamshire", "Orkney" => "Orkney", "Oxfordshire" => "Oxfordshire", "Pembrokeshire" => "Pembrokeshire",
          "Perthshire and Kinross" => "Perthshire and Kinross", "Powys" => "Powys", "Renfrewshire" => "Renfrewshire", "Rhondda Cynon Taff" => "Rhondda Cynon Taff",
          "Rutland" => "Rutland", "Shetland" => "Shetland", "Shropshire" => "Shropshire", "Somerset" => "Somerset", "South Ayrshire" => "South Ayrshire",
          "South Lanarkshire" => "South Lanarkshire", "South Yorkshire" => "South Yorkshire", "Staffordshire" => "Staffordshire", "Stirling" => "Stirling",
          "Suffolk" => "Suffolk", "Surrey" => "Surrey", "Swansea" => "Swansea", "The Vale of Glamorgan" => "The Vale of Glamorgan", "Tofaen" => "Tofaen",
          "Tyne and Wear" => "Tyne and Wear", "Tyrone" => "Tyrone", "Warwickshire" => "Warwickshire", "West Dunbartonshire" => "West Dunbartonshire",
          "West Lothian" => "West Lothian", "West Midlands" => "West Midlands", "West Sussex" => "West Sussex", "West Yorkshire" => "West Yorkshire",
          "Western Isles" => "Western Isles", "Wiltshire" => "Wiltshire", "Worcestershire" => "Worcestershire", "Wrexham" => "Wrexham"
        ),
        'ES' => array(
          "Alava" => "Alava", "Albacete" => "Albacete", "Alicante" => "Alicante", "Almeria" => "Almeria", "Asturias" => "Asturias",
          "Avila" => "Avila", "Badajoz" => "Badajoz", "Barcelona" => "Barcelona", "Burgos" => "Burgos", "Caceres" => "Caceres",
          "Cadiz" => "Cadiz", "Cantabria" => "Cantabria", "Castellon" => "Castellon", "Ceuta" => "Ceuta", "Ciudad Real" => "Ciudad Real",
          "Cordoba" => "Cordoba", "Cuenca" => "Cuenca", "Guadalajara" => "Guadalajara", "Gerona" => "Gerona", "Granada" => "Granada",
          "Guipuzcoa" => "Guipuzcoa", "Huelva" => "Huelva", "Huesca" => "Huesca", "Islas Baleares" => "Islas Baleares", "Jaen" => "Jaen",
          "La Coruna" => "La Coruna", "Las Palmas" => "Las Palmas", "La Rioja" => "La Rioja", "Leon" => "Leon", "Lerida" => "Lerida",
          "Lugo" => "Lugo", "Madrid" => "Madrid", "Malaga" => "Malaga", "Melilla" => "Melilla", "Murcia" => "Murcia", "Navarra" => "Navarra",
          "Orense" => "Orense", "Palencia" => "Palencia", "Pontevedra" => "Pontevedra", "Salamanca" => "Salamanca",
          "Santa Cruz de Tenerife" => "Santa Cruz de Tenerife", "Segovia" => "Segovia", "Sevilla" => "Sevilla", "Soria" => "Soria",
          "Tarragona" => "Tarragona", "Teruel" => "Teruel", "Toledo" => "Toledo", "Valencia" => "Valencia", "Valladolid" => "Valladolid",
          "Vizcaya" => "Vizcaya", "Zamora" => "Zamora", "Zaragoza" => "Zaragoza"
        )
      );

    }
  }
}