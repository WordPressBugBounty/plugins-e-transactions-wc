<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for request
 */
class WC_Etransactions_Abstract_Request {

    const GATEWAY_TEST                  = 'https://recette-tpeweb.e-transactions.fr/';
    const GATEWAY_PROD                  = 'https://tpeweb.e-transactions.fr/';
    const DIRECT_GATEWAY_TEST           = 'https://preprod-ppps.e-transactions.fr/';
    const DIRECT_GATEWAY_PROD           = 'https://ppps.e-transactions.fr/';
    const SECONDARY_GATEWAY_TEST        = 'https://recette-tpeweb.e-transactions.fr/';
    const SECONDARY_GATEWAY_PROD        = 'https://tpeweb1.e-transactions.fr/';
    const SECONDARY_DIRECT_GATEWAY_TEST = 'https://preprod-ppps.e-transactions.fr/';
    const SECONDARY_DIRECT_GATEWAY_PROD = 'https://ppps1.e-transactions.fr/';
    const PBX_RETOUR                    = 'M:M;R:R;T:T;A:A;B:B;C:C;D:D;E:E;F:F;G:G;I:I;J:J;N:N;O:O;P:P;Q:Q;S:S;W:W;Y:Y;v:v;K:K';
    const PBX_RETOUR_TOKEN              = 'U:U;M:M;R:R;T:T;A:A;B:B;C:C;D:D;E:E;F:F;G:G;I:I;J:J;N:N;O:O;P:P;Q:Q;S:S;W:W;Y:Y;v:v;K:K';
    const REDIRECT_ENDPOINT             = 'php/';
    const IFRAME_ENDPOINT               = 'php/';
    const TRANSACTION_ENDPOINT          = 'PPPS.php';


    /**
     * Get the gateway
     */
    public function get_gateway() {

        $account_environment    = wc_etransactions_get_option('account_environment');
        $use_second_gateway     = wc_etransactions_get_option('use_second_gateway');

        if ( $account_environment === WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST ) {
            return ($use_second_gateway === '1' ? self::SECONDARY_GATEWAY_TEST : self::GATEWAY_TEST);
        } else {
            return ($use_second_gateway === '1' ? self::SECONDARY_GATEWAY_PROD : self::GATEWAY_PROD);
        }
    }

    /**
     * Get the direct gateway
     */
    public function get_endpoint() {

        $account_environment    = wc_etransactions_get_option('account_environment');
        $use_second_gateway     = wc_etransactions_get_option('use_second_gateway');

        if ( $account_environment === WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST ) {
            $gateway = ($use_second_gateway === '1' ? self::SECONDARY_DIRECT_GATEWAY_TEST : self::DIRECT_GATEWAY_TEST);
        } else {
            $gateway = ($use_second_gateway === '1' ? self::SECONDARY_DIRECT_GATEWAY_PROD : self::DIRECT_GATEWAY_PROD);
        }

        return $gateway . self::TRANSACTION_ENDPOINT;
    }

    /**
     * Get the redirect endpoint
     */
    public function get_form_action() {

        $endpoint = self::REDIRECT_ENDPOINT;

        return $this->get_gateway() . $endpoint;
    }

    /**
     * Get the iframe form endpoint
     */
    public function get_iframe_form_action() {

        $endpoint = self::IFRAME_ENDPOINT;

        return $this->get_gateway() . $endpoint;
    }

}