<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for refund request
 */
class WC_Etransactions_Refund_Request extends WC_Etransactions_Abstract_Request {

    private $order;
    private $params;

    public function set_order( $order ) {
        $this->order = $order;
    }

    public function set_param( $key, $param ) {
        $this->params[$key] = $param;
    }

    public function get_param( $key ) {
        return $this->params[$key];
    }

    public function get_params() {
        return $this->params;
    }

    /**
     * Check if payment is configured
     */
    public function send_request( $amount = null ) {

        $config_class               = new WC_Etransactions_Config();
        $account_credentials        = $config_class->get_account_credentials();
        $currency_iso_code          = wc_etransactions_get_currency_iso_code(get_woocommerce_currency());
        $order_data                 = $this->order->get_meta( 'wc-etransactions-data', true );
        $order_data                 = $order_data[0] ?? array();

        $montant = $order_data['amount'] ?? 0;
        if ( $amount ) {
            $montant = $amount;
        }

        $this->set_param( 'VERSION', '00104' );
        $this->set_param( 'TYPE', '00014' );
        $this->set_param( 'SITE', $account_credentials['account_site_number'] );
        $this->set_param( 'RANG', $account_credentials['account_rank'] );
        $this->set_param( 'NUMQUESTION', time() );
        $this->set_param( 'HASH', 'SHA512' );
        $this->set_param( 'DATEQ', date('dmYHis') );
        $this->set_param( 'DEVISE', $currency_iso_code );
        $this->set_param( 'MONTANT', $montant );
        $this->set_param( 'NUMAPPEL', $order_data['call'] ?? '' );
        $this->set_param( 'NUMTRANS', $order_data['transaction'] ?? '' );
        $this->set_param( 'REFERENCE', sprintf( 'dx%d-%d', $this->order->get_id(), $this->order->get_customer_id() ) );

        $card_type = $order_data['cardType'] ?? '';
        if ( in_array( $card_type, WC_Etransactions_Config::ACQUEREURS ) ){
            $this->set_param( 'ACQUEREUR', $card_type );
        }

        $hmac = hash_hmac( 'sha512', trim(wc_etransactions_stringfy($this->params)), pack('H*', $account_credentials['account_hmac']) );
        $this->set_param( 'HMAC', $hmac );

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        );

        $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": REQUEST_HEADER: %s, REQUEST_BODY: %s", json_encode($headers), json_encode($this->params) );
		wc_etransactions_add_log( $message );

        $response = wp_remote_post( $this->get_endpoint(), array(
            'headers'   => $headers,
            'body'      => http_build_query( $this->params, '', '&' )
        ));

        if ( is_wp_error( $response ) ) {

            $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": WP_Error: %s", json_encode($response) );
            wc_etransactions_add_log( $message, 'error' );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( $response_code != 200 ) {

            $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": RESPONSE_CODE: %s, RESPONSE_BODY %s", $response_code, $response_body );
            wc_etransactions_add_log( $message, 'error' );
            return false;
        }

        $response_body = str_replace( array("\r", "\n"), '', $response_body );
        $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": RESPONSE_CODE: %s, RESPONSE_BODY %s", $response_code, $response_body );
        wc_etransactions_add_log( $message );

        return $response_body;
    }

}