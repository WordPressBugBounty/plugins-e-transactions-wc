<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for verifying the signature
 */
class WC_Etransactions_Signature {

    const PUBKEY = 'pubkey.pem';
    const PUBKEYSSL3 = 'pubkey_openssl3.pem';

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

        $passed = openssl_verify($data, $sig, $key, OPENSSL_ALGO_SHA256);

        if ($passed !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Get the signed key
     */
    private function load_key() {

        $openssl_version = $this->get_openssl_version();
        $key_filename = $this->get_key_filename($openssl_version);
        
        $message = __CLASS__ . ':' . __FUNCTION__ . ": Version OpenSSL détectée: " . $openssl_version . ", clé sélectionnée: " . $key_filename;
        wc_etransactions_add_log($message);
        
        $key_file = WC_ETRANSACTIONS_PLUGIN_PATH . $key_filename;
        $key = $this->load_key_from_file($key_file);
        
        if ($key) {
            $message = __CLASS__ . ':' . __FUNCTION__ . ": Clé chargée avec succès: " . $key_filename;
            wc_etransactions_add_log($message);
            return $key;
        }

        $message = __CLASS__ . ':' . __FUNCTION__ . ": Aucune clé PEM trouvée. Tenté: " . $key_file;
        wc_etransactions_add_log($message, 'error');
        return false;
    }
    
    /**
     * Load key from a specific file
     * @param string $key_file
     * @return resource|false
     */
    private function load_key_from_file($key_file) {
        if ( !file_exists($key_file) ) {
            return false;
        }

        $file_size = filesize($key_file);
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
     * Get OpenSSL version
     * @return string
     */
    private function get_openssl_version() {
        return OPENSSL_VERSION_TEXT;
    }

    /**
     * Determine which key file to use based on OpenSSL version
     * @param string $openssl_version
     * @return string
     */
    private function get_key_filename($openssl_version) {
        if (preg_match('/OpenSSL\s+(\d+)\.(\d+)/', $openssl_version, $matches)) {
            $major = (int)$matches[1];
            
            if ($major >= 3) {
                return self::PUBKEYSSL3;
            }
        }
        
        return self::PUBKEY;
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