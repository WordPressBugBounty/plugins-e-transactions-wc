<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for simple payment request
 */
class WC_Etransactions_Instalment_Payment_Request extends WC_Etransactions_Abstract_Request {

    private $gateway_class;
    private $gateway_params;
    private $order;
    private $params;

    public function set_gateway_class( $gateway_class ) {
        $this->gateway_class = $gateway_class;
    }

    public function set_gateway_params( $gateway_params ) {
        $this->gateway_params = $gateway_params;
    }

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
    public function send_request() {

        $config_class               = new WC_Etransactions_Config();
        $account_credentials        = $config_class->get_account_credentials();
        $xml_fields                 = $config_class->get_xml_fields($this->order);
        $currency_iso_code          = wc_etransactions_get_currency_iso_code(get_woocommerce_currency());
        $order_amount               = floatval($this->order->get_total());
        $amount_scale               = pow(10, wc_etransactions_get_currency_decimals($currency_iso_code));
        $total_instalment           = 0;
        $order_total                = round($order_amount * $amount_scale);
        $partial_payments           = (int)$this->gateway_params['partial_payments'];
        $percents                   = $this->gateway_params['percents'];
        $days_between_payments      = (int)$this->gateway_params['days_between_payments'];

		$this->set_param( 'PBX_ACTION', $this->get_form_action() );
        $this->set_param( 'PBX_ANNULE', add_query_arg( array('action' => 'cancel', 'order' => $this->order->get_id(), 'partial' => $partial_payments), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_BILLING', $xml_fields['PBX_BILLING'] );
        $this->set_param( 'PBX_DEVISE', $currency_iso_code );
        $this->set_param( 'PBX_EFFECTUE', add_query_arg( array('action' => 'success', 'order' => $this->order->get_id(), 'partial' => $partial_payments), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_HASH', 'SHA512' );
        $this->set_param( 'PBX_IDENTIFIANT', $account_credentials['account_id'] );
        $this->set_param( 'PBX_LANGUE', wc_etransactions_get_language_Iso6393_code() );
        $this->set_param( 'PBX_PORTEUR', $this->order->get_billing_email() );
        $this->set_param( 'PBX_RANG', $account_credentials['account_rank'] );
        $this->set_param( 'PBX_REFUSE', add_query_arg( array('action' => 'failed', 'order' => $this->order->get_id(), 'partial' => $partial_payments), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_REPONDRE_A', add_query_arg( array('action' => 'ipn', 'order' => $this->order->get_id(), 'partial' => $partial_payments), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_SHOPPINGCART', $xml_fields['PBX_SHOPPINGCART'] );
        $this->set_param( 'PBX_SITE', $account_credentials['account_site_number'] );
        $this->set_param( 'PBX_TIME', date('c') );
        $this->set_param( 'PBX_VERSION', WC_ETRANSACTIONS_PLUGIN . "-" . WC_ETRANSACTIONS_VERSION . "_WP" . get_bloginfo('version') . "_WC" . WC()->version );
        $this->set_param( 'PBX_SOUHAITAUTHENT', $config_class->order_needs_3ds_exemption($this->order) ? "02" : "01" );
        $this->set_param( 'PBX_RETOUR', self::PBX_RETOUR );
        $this->set_param( 'PBX_SOURCE', 'RWD' );

        for ( $i = 0; $i < ($partial_payments - 1); $i++ ) {

            $amount = round($order_total * ($percents[$i] / 100), 2);
            $total_instalment += $amount;

            if ( $i == 0 ) {
                $this->set_param( 'PBX_TOTAL', sprintf('%03d', $amount) );
            } else {
                $this->set_param( 'PBX_2MONT' . $i, sprintf('%03d', $amount) );
                $this->set_param( 'PBX_DATE' . $i, wp_date( 'd/m/Y', strtotime('+' . ($days_between_payments * $i) . ' day' ) ) );
            }
        }

        $remaining_amount = $order_total - $total_instalment;
        $this->set_param( 'PBX_2MONT' . ($partial_payments - 1), sprintf('%03d', $remaining_amount) );
        $this->set_param( 'PBX_DATE' . ($partial_payments - 1), wp_date( 'd/m/Y', strtotime('+' . ($days_between_payments * ($partial_payments - 1)) . ' day' ) ) );
        $this->set_param( 'PBX_CMD', $partial_payments . 'x' . $this->order->get_id() . '_' . trim(str_replace('&', '_', preg_replace("/[^A-Za-z0-9+_]/", '', remove_accents($this->order->get_billing_first_name() . '_' . $this->order->get_billing_last_name())))) . '_' . wp_date('mdHi') );

        $hmac = hash_hmac( 'sha512', trim(wc_etransactions_stringfy($this->params)), pack('H*', $account_credentials['account_hmac']) );
        $this->set_param( 'PBX_HMAC', $hmac );

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        );

        $message = sprintf( __CLASS__ . ':' . __FUNCTION__ . ": REQUEST_HEADER: %s, REQUEST_BODY: %s", json_encode($headers), json_encode($this->params) );
		wc_etransactions_add_log( $message );

        $response = wp_remote_post( $this->get_gateway(), array(
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

        return true;
    }

}