<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for test payment request
 */
class WC_Etransactions_Test_Payment_Request extends WC_Etransactions_Abstract_Request {

    private $account_id;
    private $account_rank;
    private $account_site_number;
    private $account_hmac;

    public function set_account_id( $account_id ) {
        $this->account_id = $account_id;
    }

    public function set_account_rank( $account_rank ) {
        $this->account_rank = $account_rank;
    }

    public function set_account_site_number( $account_site_number ) {
        $this->account_site_number = $account_site_number;
    }

    public function set_account_hmac( $account_hmac ) {
        $this->account_hmac = $account_hmac;
    }

    /**
     * Check if payment is configured
     */
    public function send_request() {

        $parameters = array(
            'PBX_BILLING'       => '<?xml version="1.0" encoding="utf-8"?><Billing><Address><FirstName>John</FirstName><LastName>Doe</LastName><Address1>1 AVENUE DE L\'OPERA</Address1><ZipCode>75001</ZipCode><City>PARIS</City><CountryCode>250</CountryCode></Address></Billing>',
            'PBX_CMD'           => '1x123',
            'PBX_DEVISE'        => 978,
            'PBX_HASH'          => 'SHA512',
            'PBX_IDENTIFIANT'   => $this->account_id,
            'PBX_LANGUE'        => 'FRA',
            'PBX_PORTEUR'       => 'test@test.com',
            'PBX_RANG'          => $this->account_rank,
            'PBX_RETOUR'        => self::PBX_RETOUR,
            'PBX_SHOPPINGCART'  => '<?xml version="1.0" encoding="utf-8"?><shoppingcart><total><totalQuantity>1</totalQuantity></total></shoppingcart>',
            'PBX_SITE'          => $this->account_site_number,
            'PBX_TIME'          => wp_date('c'),
            'PBX_TOTAL'         => 1000,
        );

        $hmac = hash_hmac( 'sha512', trim(wc_etransactions_stringfy($parameters)), pack('H*', $this->account_hmac) );
        $parameters['PBX_HMAC'] = $hmac;

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        );

        $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": REQUEST_HEADER: %s, REQUEST_BODY: %s", json_encode($headers), json_encode($parameters) );
		wc_etransactions_add_log( $message );

        $response = wp_remote_post( $this->get_form_action(), array(
            'headers'   => $headers,
            'body'      => http_build_query( $parameters, '', '&' )
        ));

        if ( is_wp_error( $response ) ) {

            $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": WP_Error: %s", json_encode($response) );
            wc_etransactions_add_log( $message, 'error' );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );

        if ( $response_code != 200 ) {

            $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": RESPONSE_CODE: %s", $response_code );
            wc_etransactions_add_log( $message, 'error' );
            return false;
        }

        $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": RESPONSE_CODE: %s", $response_code );
        wc_etransactions_add_log( $message );

        return true;
    }

}