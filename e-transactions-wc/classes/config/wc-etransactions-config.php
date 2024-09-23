<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for instalment configuration
 */
class WC_Etransactions_Config {

    const RESTRICTED_CURRENCIES = array('EUR');
    const FIRST_TIME_LOGIN      = 'start_login';
    const FIRST_TIME_DEMO       = 'start_demo';
    const REQUIRED_PARAMS       = ['M', 'R', 'T', 'E', 'P', 'C', 'S'];
    const NOT_REFUNDABLE        = ['CVCONNECT'];
    const ACQUEREURS            = ['CVCONNECT', 'SODEXO', 'UPCHEQUDEJ', 'LIMOCB'];
    const PBX_RETOUR_MAPPING    = array(
        'M' => 'amount',
        'R' => 'reference',
        'T' => 'call',
        'A' => 'authorization',
        'B' => 'subscription',
        'C' => 'cardType',
        'D' => 'validity',
        'E' => 'error',
        'F' => '3ds',
        'G' => '3dsWarranty',
        'H' => 'imprint',
        'I' => 'ip',
        'J' => 'lastNumbers',
        'K' => 'sign',
        'N' => 'firstNumbers',
        'O' => '3dsInlistment',
        'P' => 'paymentType',
        'Q' => 'time',
        'S' => 'transaction',
        'U' => 'token',
        'W' => 'date',
        'Y' => 'country',
        'Z' => 'paymentIndex',
        'v' => '3dsVersion',
        'o' => 'celetemType',
    );

    /**
     * Sanitize and Validate first time value
     */
    public function sanitize_validate_first_time( $value ) {

        $value = sanitize_text_field($value);

        if ( ! in_array( $value, array( self::FIRST_TIME_LOGIN, self::FIRST_TIME_DEMO ) ) ) {
            $value = self::FIRST_TIME_LOGIN;
        }

        return $value;
    }

    /**
     * Sanitize and Validate a toggle
     */
    public function sanitize_validate_toggle( $toggle ) {

        $toggle = sanitize_text_field($toggle);
        $toggle = $toggle === '1' ? '1' : '0';

        return $toggle;
    }

    /**
     * Check if account is configured
     */
    public function is_account_configured() {

        $result                 = false;
        $account_credentials    = $this->get_account_credentials();

        if ( !empty($account_credentials['account_site_number']) && !empty($account_credentials['account_rank']) && !empty($account_credentials['account_id']) && !empty($account_credentials['account_hmac']) ) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get the account credentials
     */
    public function get_account_credentials() {

        $account_credentials    = array();

        if ( $this->is_demo_mode() ) {

            $account_credentials['account_site_number'] = wc_etransactions_get_option( 'account_site_number_demo' );
            $account_credentials['account_rank']        = wc_etransactions_get_option( 'account_rank_demo' );
            $account_credentials['account_id']          = wc_etransactions_get_option( 'account_id_demo' );
            $account_credentials['account_hmac']        = wc_etransactions_get_option( 'account_hmac_demo' );

        } else {

            $account_credentials['account_site_number'] = wc_etransactions_get_option( 'account_site_number' );
            $account_credentials['account_rank']        = wc_etransactions_get_option( 'account_rank' );
            $account_credentials['account_id']          = wc_etransactions_get_option( 'account_id' );

            if ( wc_etransactions_get_option( 'account_environment' ) === WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST ) {
                $account_credentials['account_hmac']  = wc_etransactions_get_option( 'account_hmac_test' );
            } else {
                $account_credentials['account_hmac']  = wc_etransactions_get_option( 'account_hmac_prod' );
            }
        }

        return $account_credentials;
    }

    /**
     * Get the account environment
     */
    public function get_envirenment() {

        if ( $this->is_demo_mode() ) {
            $envirenment = 'demo';
        } else {
            $envirenment = wc_etransactions_get_option( 'account_environment' );
        }

        return $envirenment;
    }

    /**
     * Check if demo mode is enabled
     */
    public function is_demo_mode() {
            
        $account_demo_mode = wc_etransactions_get_option( 'account_demo_mode' );
    
        return $account_demo_mode === '1' ? true : false;
    }

    /**
     * Check if payment is configured
     */
    public function test_payment_request() {

        $test_payment_request_class = new WC_Etransactions_Test_Payment_Request();
        $account_credentials        = $this->get_account_credentials();

        $test_payment_request_class->set_account_id($account_credentials['account_id']);
        $test_payment_request_class->set_account_rank($account_credentials['account_rank']);
        $test_payment_request_class->set_account_site_number($account_credentials['account_site_number']);
        $test_payment_request_class->set_account_hmac($account_credentials['account_hmac']);

        return $test_payment_request_class->send_request();
    }

    /**
     * Get the 3DSv2 details
     */
    public function get_xml_fields( $order ) {

        $quantity = 0;
        foreach ($order->get_items() as $item) {
            $quantity += (int)$item->get_quantity();
        }

        $xml_shopping_cart = sprintf(
            '<?xml version="1.0" encoding="utf-8"?><shoppingcart><total><totalQuantity>%d</totalQuantity></total></shoppingcart>',
            $quantity
        );

        $wce_up2pay_phone_number  = $order->get_meta( wc_etransactions_add_prefix('wce_phone_number'), true );
        $wce_up2pay_phone_country = $order->get_meta( wc_etransactions_add_prefix('wce_phone_country'), true );

		$reg_exp = '/^(\+|00)*' . $wce_up2pay_phone_country . '/';
		preg_match( $reg_exp, $wce_up2pay_phone_number, $matches );

		if ( ! empty( $matches[0] ) ) {
			$wce_up2pay_phone_number = str_replace( $matches[0], '0', $wce_up2pay_phone_number );
		}

        $billing_details = array(
            wc_etransactions_format_text_value($order->get_billing_first_name(), 'ANS', 22),
            wc_etransactions_format_text_value($order->get_billing_last_name(), 'ANS', 22),
            wc_etransactions_format_text_value($order->get_billing_address_1(), 'ANS', 50),
            wc_etransactions_format_text_value($order->get_billing_postcode(), 'ANS', 16),
            wc_etransactions_format_text_value($order->get_billing_city(), 'ANS', 50),
            wc_etransactions_get_country_numeric_code($order->get_billing_country()),
            '+' . wc_etransactions_format_text_value( $wce_up2pay_phone_country, 'ANS', 16),
            wc_etransactions_format_text_value( $wce_up2pay_phone_number, 'ANS', 16),
        );

        $xml_billing = vsprintf(
            '<?xml version="1.0" encoding="utf-8"?><Billing><Address><FirstName>%s</FirstName><LastName>%s</LastName><Address1>%s</Address1><ZipCode>%s</ZipCode><City>%s</City><CountryCode>%s</CountryCode><CountryCodeMobilePhone>%s</CountryCodeMobilePhone><MobilePhone>%s</MobilePhone></Address></Billing>',
            $billing_details
        );

        return array(
            'PBX_SHOPPINGCART'  => $xml_shopping_cart,
            'PBX_BILLING'       => $xml_billing,
        );
    }

    /**
     * check if order needs 3DS exemption
     */
    public function order_needs_3ds_exemption( $order ) {

        $account_exemption3DS   = wc_etransactions_get_option('account_exemption3DS');
        $account_max_amount3DS  = wc_etransactions_get_option('account_max_amount3DS');

        if ( $account_exemption3DS === '1' && !empty($account_max_amount3DS) ) {

            $order_total    = floatval($order->get_total());
            $max_amount3DS  = floatval($account_max_amount3DS);

            if ( $order_total <= $max_amount3DS ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the params
     */
    public function get_params( $params ) {

        $converted_params   = array();
        $missing_params     = array_diff( self::REQUIRED_PARAMS, array_keys($params) );

        if ( empty($missing_params) ) {
            foreach ( $params  as $key => $value ) {
                if ( isset(self::PBX_RETOUR_MAPPING[$key]) ) {
                    $converted_params[self::PBX_RETOUR_MAPPING[$key]] = $value;
                } else {
                    $converted_params[$key] = $value;
                }
            }
        }

        return $converted_params;
    }

}