<?php
if ( !defined('ABSPATH') ) {
    exit;
}
/*
* Class WC_Etransactions_Legacy_Encrypt
* This class is used to decrypt old HMAC keys from the legacy version of the plugin (before 3.0.0)
*/
class WC_Etransactions_Legacy_Encrypt {

    public function __construct() {
        if (!defined('WC_ETRANSACTIONS_KEY_PATH')) {
            define('WC_ETRANSACTIONS_KEY_PATH', ABSPATH . '/kek.php');
        }
    }

    /**
     * IV generation
     */
    private function generateIv() {
        $len = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($len);
        return bin2hex($iv);
    }

    /**
     * Key generation
     */
    public function generateKey() {
        $key = openssl_random_pseudo_bytes(16);
        $iv = $this->generateIv();

        if (file_exists(WC_ETRANSACTIONS_KEY_PATH)) {
            unlink(WC_ETRANSACTIONS_KEY_PATH);
        }

        $key_hex = bin2hex($key);

        return file_put_contents(WC_ETRANSACTIONS_KEY_PATH, "<?php" . $key_hex . $iv);
    }

    /**
     * Get the key
     */
    private function getKey() {
        if (!file_exists(WC_ETRANSACTIONS_KEY_PATH)) {
            $this->generateKey();
            $_POST['KEY_ERROR'] = __("For some reason, the key has just been generated. please reenter the HMAC key to crypt it.", WC_ETRANSACTIONS_PLUGIN);
        }

        $key_content = file_get_contents(WC_ETRANSACTIONS_KEY_PATH);
        $key = substr($key_content, 5, 32); // Extract the key part
        return $key;
    }

    /**
     * Get the IV
     */
    private function getIv() {
        if (!file_exists(WC_ETRANSACTIONS_KEY_PATH)) {
            $this->generateKey();
            $_POST['KEY_ERROR'] = __("For some reason, the key has just been generated. please reenter the HMAC key to crypt it.", WC_ETRANSACTIONS_PLUGIN);
        }

        $iv_content = file_get_contents(WC_ETRANSACTIONS_KEY_PATH);
        $iv = substr($iv_content, 37, 16); // Extract the IV part
        return $iv;
    }

    /**
     * Encrypt data
     */
    private function encryptData($key, $iv, $data) {
        return base64_encode(openssl_encrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
    }

    /**
     * Encrypt $data using AES
     * @param string $data The data to encrypt
     * @return string The result of encryption
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $data = base64_encode($data);
        $key = $this->getKey();
        $key = substr($key, 0, 24);

        while (strlen($key) < 24) {
            $key .= substr($key, 0, 24 - strlen($key));
        }

        $iv = $this->getIv();

        return $this->encryptData($key, $iv, $data);
    }

    /**
     * Decrypt data
     */
    private function decryptData($key, $iv, $data) {
        $result = openssl_decrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);

        if ($result === false) {
            throw new Exception("Decryption failed. Please reinitialize the HMAC key.");
        }

        return base64_decode($result);
    }

    /**
     * Decrypt $data using AES
     * @param string $data The data to decrypt
     * @return string The result of decryption
     */
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }

        $data = base64_decode($data);

        $key = $this->getKey();
        $key = substr($key, 0, 24);

        while (strlen($key) < 24) {
            $key .= substr($key, 0, 24 - strlen($key));
        }

        $iv = $this->getIv();
        $result = $this->decryptData($key, $iv, $data);
        $result = rtrim($result, "\0");

        return $result;
    }
}
