<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Ensure woocommerce is active
 */
function  wc_etransactions_woocommerce_active() {
    
    if ( !class_exists('WC_Payment_Gateway') ) {
        return false;
    }
    return true;
}

/**
 * Add a message to the log file
 */
function wc_etransactions_add_log( $message ) {

    $enable_logs = wc_etransactions_get_option( 'enable_logs' );

    if ( $enable_logs === '1' ) {

        $logger = wc_get_logger();
        $logger->debug( $message, array('source' => WC_ETRANSACTIONS_PLUGIN) );
    }
}

/**
 * Get the default value for a specific option
 * @return mixed
 */
function wc_etransactions_get_default_value( $value_id = null ) {

    $default_values_array = array(
        'use_secondary_gateway'     => '0',
        'enable_logs'               => '0',
        'first_time'                => '',
        'intro_show_again'          => '1',
        'account_demo_mode'         => '0',
        'account_environment'       => WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST,
        'account_site_number'       => '',
        'account_rank'              => '',
        'account_id'                => '',
        'account_hmac_test'         => '',
        'account_hmac_prod'         => '',
        'account_site_number_demo'  => '1999888',
        'account_rank_demo'         => '32',
        'account_id_demo'           => '2',
        'account_hmac_demo'         => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
        'account_contract_access'   => WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS,
        'account_exemption3DS'      => '0',
        'account_max_amount3DS'     => WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX,
        'payment_display'           => WC_Etransactions_Payment::PAYMENT_DISPLAY_SIMPLE,
        'payment_display_title'     => '',
        'payment_display_logo'      => '',
        'payment_debit_type'        => WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_IMMEDIATE,
        'payment_capture_event'     => WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_DAYS,
        'payment_deferred_days'     => '2',
        'payment_capture_status'    => array(),
        'payment_methods_settings'  => wc_etransactions_get_payment_methods_settings(),
        'instalment_enabled'        => '0',
        'instalment_settings'       => wc_etransactions_get_instalments_settings(),
        'version'                   => "0.0.0",
    );

    if ( $value_id === null ) {
        return $default_values_array;
    }

    return $default_values_array[$value_id] ?? '';
}

/**
 * Get an option value
 */
function wc_etransactions_get_option( $option_id ) {

    $option_id_prefixed = wc_etransactions_add_prefix( $option_id );

   return get_option( $option_id_prefixed, wc_etransactions_get_default_value( $option_id ) );
}

/**
 * Update an option value
 */
function wc_etransactions_update_option( $option_id, $value ) {

    $option_id_prefixed = wc_etransactions_add_prefix( $option_id );

    return update_option( $option_id_prefixed, $value );
}

/**
 * Add prefix to an option id
 */
function wc_etransactions_add_prefix( $option_id ) {

    $prefix = 'wc_etransactions_';

    return $prefix . $option_id;
}

/**
 * Array of the payment methods
 */
function wc_etransactions_get_payment_methods() {

    return array(
        'CB' => array(
            "identifier"        => "CB VISA MASTERCARD",
            "cardType"          => "CB",
            "paymentType"       => "CARTE",
            "isSelectable"      => '0',
            "enabled"           => '1',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '1',
            "forceRedirect"     => '0',
            "displayType"       => "redirect",
            "title"             => __('Pay with credit card', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/CB_VISA_MC.svg",
            "minAmount"         => '0'
        ),
        'AMEX' => array(
            "identifier"        => "AMERICAN EXPRESS",
            "cardType"          => "AMEX",
            "paymentType"       => "CARTE",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '0',
            "displayType"       => "redirect",
            "title"             => __('Pay with AMEX', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/AMEX.svg",
            "minAmount"         => '0'
        ),
        'PAYPAL' => array(
            "identifier"        => "PAYPAL",
            "cardType"          => "PAYPAL",
            "paymentType"       => "PAYPAL",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '1',
            "displayType"       => "redirect",
            "title"             => __('Pay with Paypal', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/PAYPAL.svg",
            "minAmount"         => '0'
        ),
        'DINERS' => array(
            "identifier"        => "DINERS",
            "cardType"          => "DINERS",
            "paymentType"       => "CARTE",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '0',
            "displayType"       => "redirect",
            "title"             => __('Pay with Diners', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/DINERS.svg",
            "minAmount"         => '0'
        ),
        'JCB' => array(
            "identifier"        => "JCB",
            "cardType"          => "JCB",
            "paymentType"       => "CARTE",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '0',
            "displayType"       => "redirect",
            "title"             => __('Pay with JCB', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/JCB.svg",
            "minAmount"         => '0'
        ),
        'CVCONNECT' => array(
            "identifier"        => "CV CONNECT",
            "cardType"          => "CVCONNECT",
            "paymentType"       => "LIMONETIK",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '1',
            "displayType"       => "redirect",
            "title"             => __('Pay with CV Connect', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/CV_CONNECT.svg",
            "minAmount"         => '0'
        ),
        'PLUXEE' => array(
            "identifier"        => "PLUXEE",
            "cardType"          => "SODEXO",
            "paymentType"       => "LIMONETIK",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '1',
            "displayType"       => "redirect",
            "title"             => __('Pay with Pluxee', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/PLUXEE.svg",
            "minAmount"         => '0'
        ),
        'UPCHEQUDEJ' => array(
            "identifier"        => "UP DEJEUNER",
            "cardType"          => "UPCHEQUDEJ",
            "paymentType"       => "LIMONETIK",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '1',
            "displayType"       => "redirect",
            "title"             => __('Pay with Up Déjeuner', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/UPCHEQUDEJ.svg",
            "minAmount"         => '0'
        ),
        'UPI' => array(
            "identifier"        => "UPI",
            "cardType"          => "UPI",
            "paymentType"       => "CARTE",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '0',
            "displayType"       => "redirect",
            "title"             => __('Pay with UnionPay', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/UPI.svg",
            "minAmount"         => '0'
        ),
        'BIMPLY' => array(
            "identifier"        => "BIMPLY",
            "cardType"          => "APETIZ",
            "paymentType"       => "LIMONETIK",
            "isSelectable"      => '1',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '1',
            "displayType"       => "redirect",
            "title"             => __('Pay with Bimply', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/BIMPLY.svg",
            "minAmount"         => '0'
        ),
        'OTHER' => array(
            "identifier"        => "OTHER",
            "cardType"          => "",
            "paymentType"       => "",
            "isSelectable"      => '0',
            "enabled"           => '0',
            "oneClickEnabled"   => '0',
            "oneClickAvailable" => '0',
            "forceRedirect"     => '1',
            "displayType"       => "redirect",
            "title"             => __('Secured payment by Credit Agricole', 'wc-etransactions' ),
            "logoUrl"           => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/CB_VISA_MC.svg",
            "minAmount"         => '0'
        ),
    );
}

/**
 * Get the instalments
 */
function wc_etransactions_get_instalments() {

    return array(
        array(
            "partialPayments"       => '2',
            "enabled"               => '0',
            "daysBetweenPayments"   => '30',
            "percents"              => ['50', '50'],
            "title"                 => __('Pay in 2 instalments with credit card', 'wc-etransactions' ),
            "logoUrl"               => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/2x.svg",
            "minAmount"             => '150',
            "maxAmount"             => '2000'
        ),
        array(
            "partialPayments"       => '3',
            "enabled"               => '0',
            "daysBetweenPayments"   => '30',
            "percents"              => ['33', '33', '34'],
            "title"                 => __('Pay in 3 instalments with credit card', 'wc-etransactions' ),
            "logoUrl"               => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/3x.svg",
            "minAmount"             => '150',
            "maxAmount"             => '2000'
        ),
        array(
            "partialPayments"       => '4',
            "enabled"               => '0',
            "daysBetweenPayments"   => '30',
            "percents"              => ['25', '25', '25', '25'],
            "title"                 => __('Pay in 4 instalments with credit card', 'wc-etransactions' ),
            "logoUrl"               => WC_ETRANSACTIONS_PLUGIN_URL . "assets/svg/payment-methods/4x.svg",
            "minAmount"             => '150',
            "maxAmount"             => '2000'
        ),
    );
}

/**
 * Get the default values of payment methods settings
 */
function wc_etransactions_get_payment_methods_settings() {

    $payment_methods_settings   = array();
    $payment_methods            = wc_etransactions_get_payment_methods();

    foreach ( $payment_methods as $method_id => $method_data ) {

        $payment_methods_settings[$method_id] = array(
            'isSelectable'      => $method_data['isSelectable'],
            'enabled'           => $method_data['enabled'],
            'displayType'       => $method_data['displayType'],
            'oneClickEnabled'   => $method_data['oneClickEnabled'],
            'title'             => '',
            'logoUrl'           => '',
            'minAmount'         => $method_data['minAmount'],
        );
    }

    return $payment_methods_settings;
}

/**
 * Get the default values of instalments
 */
function wc_etransactions_get_instalments_settings() {

    $instalments_settings   = array();
    $instalments            = wc_etransactions_get_instalments();

    foreach ( $instalments as $instalment_id => $instalment_data ) {

        $instalments_settings[$instalment_id] = array(
            'enabled'               => $instalment_data['enabled'],
            'daysBetweenPayments'   => $instalment_data['daysBetweenPayments'],
            'percents'              => $instalment_data['percents'],
            'title'                 => '',
            'logoUrl'               => '',
            'minAmount'             => $instalment_data['minAmount'],
            'maxAmount'             => $instalment_data['maxAmount'],
        );
    }

    return $instalments_settings;
}

/**
 * Stringfy an array
 */
function wc_etransactions_stringfy( $array ) {

    $result = [];

    foreach ( $array as $key => $value ) {
        $result[] = sprintf( '%s=%s', $key, $value );
    }

    return implode('&', $result);
}

/**
 * Format a value to respect specific rules
 * 
 * @param string $value
 * @param string $type
 * AN : Alphanumerical without special characters
 * ANP : Alphanumerical with spaces and special characters
 * ANS : Alphanumerical with special characters
 * N : Numerical only
 * A : Alphabetic only
 * @param int $max_length
 * @return string
 */
function wc_etransactions_format_text_value( $value, $type, $max_length = null ) {

    switch ($type) {
        default:
        case 'AN':
            $value = remove_accents($value);
        break;
        case 'ANP':
            $value = remove_accents($value);
            $value = preg_replace('/[^-. a-zA-Z0-9]/', '', $value);
        break;
        case 'ANS':
			$value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
			$value = remove_accents($value);
            $value = preg_replace('/[^a-zA-Z0-9\\s]/', '', $value);
			$value = trim(preg_replace('/\\s+/', ' ', $value));
        break;
        case 'N':
            $value = preg_replace('/[^0-9.]/', '', $value);
        break;
        case 'A':
            $value = remove_accents($value);
            $value = preg_replace('/[^A-Za-z]/', '', $value);
        break;
    }

    $value = trim(preg_replace("/\r|\n/", '', $value));

    if (!empty($max_length) && is_numeric($max_length) && $max_length > 0) {

        if (function_exists('mb_strlen')) {
            if (mb_strlen($value) > $max_length) {
                $value = mb_substr($value, 0, $max_length);
            }
        } elseif (strlen($value) > $max_length) {
            $value = substr($value, 0, $max_length);
        }
    }

    return trim($value);
}

/**
 * Get the numeric code of a country
 * @return string
 */
function wc_etransactions_get_country_numeric_code( $code ) {

    $numeric_codes = array(
        'AD' => '020',
        'AE' => '784',
        'AF' => '004',
        'AG' => '028',
        'AI' => '660',
        'AL' => '008',
        'AM' => '051',
        'AO' => '024',
        'AQ' => '010',
        'AR' => '032',
        'AS' => '016',
        'AT' => '040',
        'AU' => '036',
        'AW' => '533',
        'AX' => '248',
        'AZ' => '031',
        'BA' => '070',
        'BB' => '052',
        'BD' => '050',
        'BE' => '056',
        'BF' => '854',
        'BG' => '100',
        'BH' => '048',
        'BI' => '108',
        'BJ' => '204',
        'BL' => '652',
        'BM' => '060',
        'BN' => '096',
        'BO' => '068',
        'BQ' => '535',
        'BR' => '076',
        'BS' => '044',
        'BT' => '064',
        'BV' => '074',
        'BW' => '072',
        'BY' => '112',
        'BZ' => '084',
        'CA' => '124',
        'CC' => '166',
        'CD' => '180',
        'CF' => '140',
        'CG' => '178',
        'CH' => '756',
        'CI' => '384',
        'CK' => '184',
        'CL' => '152',
        'CM' => '120',
        'CN' => '156',
        'CO' => '170',
        'CR' => '188',
        'CU' => '192',
        'CV' => '132',
        'CW' => '531',
        'CX' => '162',
        'CY' => '196',
        'CZ' => '203',
        'DE' => '276',
        'DJ' => '262',
        'DK' => '208',
        'DM' => '212',
        'DO' => '214',
        'DZ' => '012',
        'EC' => '218',
        'EE' => '233',
        'EG' => '818',
        'EH' => '732',
        'ER' => '232',
        'ES' => '724',
        'ET' => '231',
        'FI' => '246',
        'FJ' => '242',
        'FK' => '238',
        'FM' => '583',
        'FO' => '234',
        'FR' => '250',
        'GA' => '266',
        'GB' => '826',
        'GD' => '308',
        'GE' => '268',
        'GF' => '254',
        'GG' => '831',
        'GH' => '288',
        'GI' => '292',
        'GL' => '304',
        'GM' => '270',
        'GN' => '324',
        'GP' => '312',
        'GQ' => '226',
        'GR' => '300',
        'GS' => '239',
        'GT' => '320',
        'GU' => '316',
        'GW' => '624',
        'GY' => '328',
        'HK' => '344',
        'HM' => '334',
        'HN' => '340',
        'HR' => '191',
        'HT' => '332',
        'HU' => '348',
        'ID' => '360',
        'IE' => '372',
        'IL' => '376',
        'IM' => '833',
        'IN' => '356',
        'IO' => '086',
        'IQ' => '368',
        'IR' => '364',
        'IS' => '352',
        'IT' => '380',
        'JE' => '832',
        'JM' => '388',
        'JO' => '400',
        'JP' => '392',
        'KE' => '404',
        'KG' => '417',
        'KH' => '116',
        'KI' => '296',
        'KM' => '174',
        'KN' => '659',
        'KP' => '408',
        'KR' => '410',
        'KW' => '414',
        'KY' => '136',
        'KZ' => '398',
        'LA' => '418',
        'LB' => '422',
        'LC' => '662',
        'LI' => '438',
        'LK' => '144',
        'LR' => '430',
        'LS' => '426',
        'LT' => '440',
        'LU' => '442',
        'LV' => '428',
        'LY' => '434',
        'MA' => '504',
        'MC' => '492',
        'MD' => '498',
        'ME' => '499',
        'MF' => '663',
        'MG' => '450',
        'MH' => '584',
        'MK' => '807',
        'ML' => '466',
        'MM' => '104',
        'MN' => '496',
        'MO' => '446',
        'MP' => '580',
        'MQ' => '474',
        'MR' => '478',
        'MS' => '500',
        'MT' => '470',
        'MU' => '480',
        'MV' => '462',
        'MW' => '454',
        'MX' => '484',
        'MY' => '458',
        'MZ' => '508',
        'NA' => '516',
        'NC' => '540',
        'NE' => '562',
        'NF' => '574',
        'NG' => '566',
        'NI' => '558',
        'NL' => '528',
        'NO' => '578',
        'NP' => '524',
        'NR' => '520',
        'NU' => '570',
        'NZ' => '554',
        'OM' => '512',
        'PA' => '591',
        'PE' => '604',
        'PF' => '258',
        'PG' => '598',
        'PH' => '608',
        'PK' => '586',
        'PL' => '616',
        'PM' => '666',
        'PN' => '612',
        'PR' => '630',
        'PS' => '275',
        'PT' => '620',
        'PW' => '585',
        'PY' => '600',
        'QA' => '634',
        'RE' => '638',
        'RO' => '642',
        'RS' => '688',
        'RU' => '643',
        'RW' => '646',
        'SA' => '682',
        'SB' => '090',
        'SC' => '690',
        'SD' => '729',
        'SE' => '752',
        'SG' => '702',
        'SH' => '654',
        'SI' => '705',
        'SJ' => '744',
        'SK' => '703',
        'SL' => '694',
        'SM' => '674',
        'SN' => '686',
        'SO' => '706',
        'SR' => '740',
        'SS' => '728',
        'ST' => '678',
        'SV' => '222',
        'SX' => '534',
        'SY' => '760',
        'SZ' => '748',
        'TC' => '796',
        'TD' => '148',
        'TF' => '260',
        'TG' => '768',
        'TH' => '764',
        'TJ' => '762',
        'TK' => '772',
        'TL' => '626',
        'TM' => '795',
        'TN' => '788',
        'TO' => '776',
        'TR' => '792',
        'TT' => '780',
        'TV' => '798',
        'TW' => '158',
        'TZ' => '834',
        'UA' => '804',
        'UG' => '800',
        'UM' => '581',
        'US' => '840',
        'UY' => '858',
        'UZ' => '860',
        'VA' => '336',
        'VC' => '670',
        'VE' => '862',
        'VG' => '092',
        'VI' => '850',
        'VN' => '704',
        'VU' => '548',
        'WF' => '876',
        'WS' => '882',
        'YE' => '887',
        'YT' => '175',
        'ZA' => '710',
        'ZM' => '894',
        'ZW' => '716',
    );

    return $numeric_codes[$code] ?? $numeric_codes['FR'];
}

/**
 * Get the ISO code of a currency
 * @return string
 */
function wc_etransactions_get_currency_iso_code( $code ) {

    $iso_codes = array(
        'AED' => '784',
        'AFN' => '971',
        'ALL' => '008',
        'AMD' => '051',
        'ANG' => '532',
        'AOA' => '973',
        'ARS' => '032',
        'AUD' => '036',
        'AWG' => '533',
        'AZN' => '944',
        'BAM' => '977',
        'BBD' => '052',
        'BDT' => '050',
        'BGN' => '975',
        'BHD' => '048',
        'BIF' => '108',
        'BMD' => '060',
        'BND' => '096',
        'BOB' => '068',
        'BOV' => '984',
        'BRL' => '986',
        'BSD' => '044',
        'BTN' => '064',
        'BWP' => '072',
        'BYR' => '974',
        'BZD' => '084',
        'CAD' => '124',
        'CDF' => '976',
        'CHE' => '947',
        'CHF' => '756',
        'CHW' => '948',
        'CLF' => '990',
        'CLP' => '152',
        'CNY' => '156',
        'COP' => '170',
        'COU' => '970',
        'CRC' => '188',
        'CUC' => '931',
        'CUP' => '192',
        'CVE' => '132',
        'CZK' => '203',
        'DJF' => '262',
        'DKK' => '208',
        'DOP' => '214',
        'DZD' => '012',
        'EEK' => '233',
        'EGP' => '818',
        'ERN' => '232',
        'ETB' => '230',
        'EUR' => '978',
        'FJD' => '242',
        'FKP' => '238',
        'GBP' => '826',
        'GEL' => '981',
        'GHS' => '936',
        'GIP' => '292',
        'GMD' => '270',
        'GNF' => '324',
        'GTQ' => '320',
        'GYD' => '328',
        'HKD' => '344',
        'HNL' => '340',
        'HRK' => '191',
        'HTG' => '332',
        'HUF' => '348',
        'IDR' => '360',
        'ILS' => '376',
        'INR' => '356',
        'IQD' => '368',
        'IRR' => '364',
        'ISK' => '352',
        'JMD' => '388',
        'JOD' => '400',
        'JPY' => '392',
        'KES' => '404',
        'KGS' => '417',
        'KHR' => '116',
        'KMF' => '174',
        'KPW' => '408',
        'KRW' => '410',
        'KWD' => '414',
        'KYD' => '136',
        'KZT' => '398',
        'LAK' => '418',
        'LBP' => '422',
        'LKR' => '144',
        'LRD' => '430',
        'LSL' => '426',
        'LTL' => '440',
        'LVL' => '428',
        'LYD' => '434',
        'MAD' => '504',
        'MDL' => '498',
        'MGA' => '969',
        'MKD' => '807',
        'MMK' => '104',
        'MNT' => '496',
        'MOP' => '446',
        'MRO' => '478',
        'MUR' => '480',
        'MVR' => '462',
        'MWK' => '454',
        'MXN' => '484',
        'MXV' => '979',
        'MYR' => '458',
        'MZN' => '943',
        'NAD' => '516',
        'NGN' => '566',
        'NIO' => '558',
        'NOK' => '578',
        'NPR' => '524',
        'NZD' => '554',
        'OMR' => '512',
        'PAB' => '590',
        'PEN' => '604',
        'PGK' => '598',
        'PHP' => '608',
        'PKR' => '586',
        'PLN' => '985',
        'PYG' => '600',
        'QAR' => '634',
        'RON' => '946',
        'RSD' => '941',
        'RUB' => '643',
        'RWF' => '646',
        'SAR' => '682',
        'SBD' => '090',
        'SCR' => '690',
        'SDG' => '938',
        'SEK' => '752',
        'SGD' => '702',
        'SHP' => '654',
        'SLL' => '694',
        'SOS' => '706',
        'SRD' => '968',
        'STD' => '678',
        'SYP' => '760',
        'SZL' => '748',
        'THB' => '764',
        'TJS' => '972',
        'TMT' => '934',
        'TND' => '788',
        'TOP' => '776',
        'TRY' => '949',
        'TTD' => '780',
        'TWD' => '901',
        'TZS' => '834',
        'UAH' => '980',
        'UGX' => '800',
        'USD' => '840',
        'USN' => '997',
        'USS' => '998',
        'UYU' => '858',
        'UZS' => '860',
        'VEF' => '937',
        'VND' => '704',
        'VUV' => '548',
        'WST' => '882',
        'XAF' => '950',
        'XAG' => '961',
        'XAU' => '959',
        'XBA' => '955',
        'XBB' => '956',
        'XBC' => '957',
        'XBD' => '958',
        'XCD' => '951',
        'XDR' => '960',
        'XOF' => '952',
        'XPD' => '964',
        'XPF' => '953',
        'XPT' => '962',
        'XTS' => '963',
        'XXX' => '999',
        'YER' => '886',
        'ZAR' => '710',
        'ZMK' => '894',
        'ZWL' => '932',
    );

    return $iso_codes[$code] ?? $iso_codes['EUR'];
}

/**
 * Get the curenncy decimals
 * @return int
 */
function wc_etransactions_get_currency_decimals( $code ) {

    $currency_decimals = array(
        '008' => 2,
        '012' => 2,
        '032' => 2,
        '036' => 2,
        '044' => 2,
        '048' => 3,
        '050' => 2,
        '051' => 2,
        '052' => 2,
        '060' => 2,
        '064' => 2,
        '068' => 2,
        '072' => 2,
        '084' => 2,
        '090' => 2,
        '096' => 2,
        '104' => 2,
        '108' => 0,
        '116' => 2,
        '124' => 2,
        '132' => 2,
        '136' => 2,
        '144' => 2,
        '152' => 0,
        '156' => 2,
        '170' => 2,
        '174' => 0,
        '188' => 2,
        '191' => 2,
        '192' => 2,
        '203' => 2,
        '208' => 2,
        '214' => 2,
        '222' => 2,
        '230' => 2,
        '232' => 2,
        '238' => 2,
        '242' => 2,
        '262' => 0,
        '270' => 2,
        '292' => 2,
        '320' => 2,
        '324' => 0,
        '328' => 2,
        '332' => 2,
        '340' => 2,
        '344' => 2,
        '348' => 2,
        '352' => 0,
        '356' => 2,
        '360' => 2,
        '364' => 2,
        '368' => 3,
        '376' => 2,
        '388' => 2,
        '392' => 0,
        '398' => 2,
        '400' => 3,
        '404' => 2,
        '408' => 2,
        '410' => 0,
        '414' => 3,
        '417' => 2,
        '418' => 2,
        '422' => 2,
        '426' => 2,
        '428' => 2,
        '430' => 2,
        '434' => 3,
        '440' => 2,
        '446' => 2,
        '454' => 2,
        '458' => 2,
        '462' => 2,
        '478' => 2,
        '480' => 2,
        '484' => 2,
        '496' => 2,
        '498' => 2,
        '504' => 2,
        '504' => 2,
        '512' => 3,
        '516' => 2,
        '524' => 2,
        '532' => 2,
        '532' => 2,
        '533' => 2,
        '548' => 0,
        '554' => 2,
        '558' => 2,
        '566' => 2,
        '578' => 2,
        '586' => 2,
        '590' => 2,
        '598' => 2,
        '600' => 0,
        '604' => 2,
        '608' => 2,
        '634' => 2,
        '643' => 2,
        '646' => 0,
        '654' => 2,
        '678' => 2,
        '682' => 2,
        '690' => 2,
        '694' => 2,
        '702' => 2,
        '704' => 0,
        '706' => 2,
        '710' => 2,
        '728' => 2,
        '748' => 2,
        '752' => 2,
        '756' => 2,
        '760' => 2,
        '764' => 2,
        '776' => 2,
        '780' => 2,
        '784' => 2,
        '788' => 3,
        '800' => 2,
        '807' => 2,
        '818' => 2,
        '826' => 2,
        '834' => 2,
        '840' => 2,
        '858' => 2,
        '860' => 2,
        '882' => 2,
        '886' => 2,
        '901' => 2,
        '931' => 2,
        '932' => 2,
        '934' => 2,
        '936' => 2,
        '937' => 2,
        '938' => 2,
        '940' => 0,
        '941' => 2,
        '943' => 2,
        '944' => 2,
        '946' => 2,
        '947' => 2,
        '948' => 2,
        '949' => 2,
        '950' => 0,
        '951' => 2,
        '952' => 0,
        '953' => 0,
        '967' => 2,
        '968' => 2,
        '969' => 2,
        '970' => 2,
        '971' => 2,
        '972' => 2,
        '973' => 2,
        '974' => 0,
        '975' => 2,
        '976' => 2,
        '977' => 2,
        '978' => 2,
        '979' => 2,
        '980' => 2,
        '981' => 2,
        '984' => 2,
        '985' => 2,
        '986' => 2,
        '990' => 0,
        '997' => 2,
        '998' => 2,
    );

    return $currency_decimals[$code] ?? 2;
}

/**
 * Get the ISO code of a language
 * @return string
 */
function wc_etransactions_get_language_Iso6393_code() {

    $lang = get_locale();
    if (!empty($lang)) {
        $lang = preg_replace('#_.*$#', '', $lang);
    }

    $iso_codes = array(
        'fr'        => 'FRA',
        'es'        => 'ESP',
        'it'        => 'ITA',
        'de'        => 'DEU',
        'nl'        => 'NLD',
        'sv'        => 'SWE',
        'pt'        => 'PRT',
        'default'   => 'GBR',
    );

    return $iso_codes[$lang] ?? $iso_codes['default'];
}