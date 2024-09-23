<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for verifying the signature
 */
class WC_Etransactions_Signature {

    /**
     * Check if the signature is valid
     * @param array $params
     * @param bool $url
     */
    public function verify_signature($params, $url) {

        $key    = $this->load_key();
        $data   = '';
        $sig    = '';
        
        if (!$key) {
            return false;
        }

        if ($params['C'] == 'Visa Electron') {
            $params = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        } else {
            $params = http_build_query($params, '', '&');
        }

        $this->get_signed_data($params, $data, $sig, $url);

        $passed = openssl_verify($data, $sig, $key);

        if ($passed !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Get the signed key
     */
    private function load_key() {

        $key_file   = WC_ETRANSACTIONS_PLUGIN_PATH . 'pubkey.pem';

        if ( !file_exists($key_file) ) {
            return false;
        }

        $file_size  = filesize($key_file);

        if (!$file_size) {
            return false;
        }

        $fpk = fopen($key_file, 'r');
        if (!$fpk) {
            return false;
        }

        $file_data = fread($fpk, $file_size);
        fclose($fpk);

        if (!$file_data) {
            return false;
        }

        return openssl_pkey_get_public($file_data);
    }

    /**
     * Get the signed data
     */
    private function get_signed_data($query_string, &$data, &$sig, $url) {

        $pos    = strrpos($query_string, '&');
        $data   = substr($query_string, 0, $pos);
        $pos    = strpos($query_string, '=', $pos) + 1;
        $sig    = substr($query_string, $pos);
        
        if ($url) {
            $sig = urldecode($sig);
        }
        
        $sig = base64_decode($sig);
    }

}