<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for account configuration
 */
class WC_Etransactions_Account extends WC_Etransactions_Config {

    const ACCOUNT_ENVIRONMENT_TEST          = 'test';
    const ACCOUNT_ENVIRONMENT_PRODUCTION    = 'production';
    const ACCOUNT_CONTRACT_ACCESS_ACCESS    = 'access';
    const ACCOUNT_CONTRACT_ACCESS_PREMIUM   = 'premium';
    const ACCOUNT_MAX_AMOUNT3DS_MAX         = 30;

    /**
     * Sanitize and Validate the demo mode
     */
    public function sanitize_validate_account_demo_mode( $value ) {

        if ( empty($value) ) {
            return wc_etransactions_get_default_value('account_demo_mode');
        }

        return $this->sanitize_validate_toggle($value);
    }

    /**
     * Sanitize and Validate the account environment
     */
    public function sanitize_validate_account_environment( $value ) {

        $value = sanitize_text_field($value);

        if ( !in_array($value, array(self::ACCOUNT_ENVIRONMENT_TEST, self::ACCOUNT_ENVIRONMENT_PRODUCTION)) ) {
            $value = wc_etransactions_get_default_value('account_environment');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account site number
     */
    public function sanitize_validate_account_site_number( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {
            $value = wc_etransactions_get_default_value('account_site_number');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account rank
     */
    public function sanitize_validate_account_rank( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {
            $value = wc_etransactions_get_default_value('account_rank');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account Id
     */
    public function sanitize_validate_account_id( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {
            $value = wc_etransactions_get_default_value('account_id');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account hmac test
     */
    public function sanitize_validate_account_hmac_test( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {
            $value = wc_etransactions_get_default_value('account_hmac_test');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account hmac prod
     */
    public function sanitize_validate_account_hmac_prod( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {
            $value = wc_etransactions_get_default_value('account_hmac_prod');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account contract access
     */
    public function sanitize_validate_account_contract_access( $value ) {

        $value = sanitize_text_field($value);

        if ( !in_array($value, array(self::ACCOUNT_CONTRACT_ACCESS_ACCESS, self::ACCOUNT_CONTRACT_ACCESS_PREMIUM)) ) {
            $value = wc_etransactions_get_default_value('account_contract_access');
        }

        return $value;
    }

    /**
     * Sanitize and Validate the account exemption3DS
     */
    public function sanitize_validate_account_exemption3DS( $value ) {

        if ( empty($value) ) {
            return wc_etransactions_get_default_value('account_exemption3DS');
        }

        return $this->sanitize_validate_toggle($value);
    }

    /**
     * Sanitize and Validate the account max amount3DS
     */
    public function sanitize_validate_account_max_amount3DS( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {

            $value = wc_etransactions_get_default_value('account_max_amount3DS');

        } else {

            $value = intval($value);

            if ( $value <= 0 || $value > self::ACCOUNT_MAX_AMOUNT3DS_MAX ) {
                $value = self::ACCOUNT_MAX_AMOUNT3DS_MAX;
            }
        }

        return $value;
    }

}