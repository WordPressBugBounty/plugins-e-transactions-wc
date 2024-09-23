<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for simple payment request
 */
class WC_Etransactions_Simple_Payment_Request extends WC_Etransactions_Abstract_Request {

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
		$token_id					= $this->order->get_meta( wc_etransactions_add_prefix('token_id'), true );
        $is_token                   = !empty($token_id);
        $one_click_enabled_checkbox = $this->order->get_meta( wc_etransactions_add_prefix('one_click_enabled'), true );

        $this->set_param( 'PBX_ANNULE', add_query_arg( array('action' => 'cancel', 'order' => $this->order->get_id(), 'gateway_id' => $this->gateway_params['gateway_id']), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_BILLING', $xml_fields['PBX_BILLING'] );
        $this->set_param( 'PBX_CMD', $this->order->get_id() . '_' . trim(str_replace('&', '_', preg_replace("/[^A-Za-z0-9+_]/", '', remove_accents($this->order->get_billing_first_name() . '_' . $this->order->get_billing_last_name())))) . '_' . wp_date('mdHi') );
        $this->set_param( 'PBX_DEVISE', $currency_iso_code );
        $this->set_param( 'PBX_EFFECTUE', add_query_arg( array('action' => 'success', 'order' => $this->order->get_id(), 'gateway_id' => $this->gateway_params['gateway_id']), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_HASH', 'SHA512' );
        $this->set_param( 'PBX_IDENTIFIANT', $account_credentials['account_id'] );
        $this->set_param( 'PBX_LANGUE', wc_etransactions_get_language_Iso6393_code() );
        $this->set_param( 'PBX_PORTEUR', $this->order->get_billing_email() );
        $this->set_param( 'PBX_RANG', $account_credentials['account_rank'] );
        $this->set_param( 'PBX_REFUSE', add_query_arg( array('action' => 'failed', 'order' => $this->order->get_id(), 'gateway_id' => $this->gateway_params['gateway_id']), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_REPONDRE_A', add_query_arg( array('action' => 'ipn', 'order' => $this->order->get_id(), 'gateway_id' => $this->gateway_params['gateway_id']), trailingslashit(site_url('wc-api/' . $this->gateway_class))) );
        $this->set_param( 'PBX_SHOPPINGCART', $xml_fields['PBX_SHOPPINGCART'] );
        $this->set_param( 'PBX_SITE', $account_credentials['account_site_number'] );
        $this->set_param( 'PBX_TIME', date('c') );
        $this->set_param( 'PBX_TOTAL', sprintf('%03d', round($order_amount * $amount_scale)) );
        $this->set_param( 'PBX_VERSION', WC_ETRANSACTIONS_PLUGIN . "-" . WC_ETRANSACTIONS_VERSION . "_WP" . get_bloginfo('version') . "_WC" . WC()->version );
        $this->set_param( 'PBX_SOUHAITAUTHENT', $config_class->order_needs_3ds_exemption($this->order) ? "02" : "01" );
        $this->set_param( 'PBX_RETOUR', self::PBX_RETOUR );
        $this->set_param( 'PBX_SOURCE', 'RWD' );

        if ( wc_etransactions_get_option('payment_debit_type') === WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_DEFERRED ) {
            if ( wc_etransactions_get_option('payment_capture_event') === WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_DAYS ) {
                $this->set_param( 'PBX_DIFF', wc_etransactions_get_option('payment_deferred_days') );
            } else {
                $this->set_param( 'PBX_AUTOSEULE', 'O' );
            }
        }

        if ( $this->gateway_params['iframe'] === '1' ) {
            $this->set_param( 'PBX_ANNULE', $this->get_param('PBX_ANNULE') . '&iframe=1' );
            $this->set_param( 'PBX_EFFECTUE', $this->get_param('PBX_EFFECTUE') . '&iframe=1' );
            $this->set_param( 'PBX_REFUSE', $this->get_param('PBX_REFUSE') . '&iframe=1' );
            $this->set_param( 'PBX_THEME_CSS', 'frame-puma.css' );
        }

        if ( !$is_token && !empty($this->gateway_params['card_type']) && !empty($this->gateway_params['paiment_type']) ) {
            $this->set_param( 'PBX_TYPECARTE', $this->gateway_params['card_type'] );
            $this->set_param( 'PBX_TYPEPAIEMENT', $this->gateway_params['paiment_type'] );
        }

        if ( !$is_token && $this->gateway_params['one_click_enabled'] === '1' && $one_click_enabled_checkbox === '1' && is_user_logged_in() ) {
            $this->set_param( 'PBX_REFABONNE', sanitize_title(get_bloginfo('name')) . $this->order->get_customer_id() . str_replace('@', '', $this->order->get_billing_email()) );
            $this->set_param( 'PBX_RETOUR', self::PBX_RETOUR_TOKEN );
        }

        if ( $is_token ) {

            $payment_token = WC_Payment_Tokens::get( $token_id );
			$token_data = $payment_token->get_data();
            $token_expiry_month = $token_data['expiry_month'];
            $token_expiry_year  = $token_data['expiry_year'];
            $token_hash         = $token_data['token'];

            $this->set_param( 'PBX_REFABONNE', sanitize_title(get_bloginfo('name')) . $this->order->get_customer_id() . str_replace('@', '', $this->order->get_billing_email()) );
            $this->set_param( 'PBX_DATEVAL', $token_expiry_month . substr($token_expiry_year,2,2) );
            $this->set_param( 'PBX_TOKEN', $token_hash );
        }

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