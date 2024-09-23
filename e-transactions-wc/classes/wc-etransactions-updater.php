<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Class responsible for the updater
 * @since 3.0.3
 */
class WC_Etransactions_Updater {

    /**
     * Constructor
     */
    public function __construct() {
        $this->version = wc_etransactions_get_option('version');
        $updated = version_compare( $this->version, WC_ETRANSACTIONS_VERSION, '<' );
        if( $updated ) {
            add_action('plugins_loaded', array( $this, 'update' ) );
        }
    }

    /**
     * Launch the update process
     *
     * @return void
     */
    public function update() {
        /**
         * < 3.0.3
         */
        if( version_compare( $this->version, '3.0.3', '<' ) ) {
            $this->minus_3_0_3();
        }

        wc_etransactions_update_option( 'version', WC_ETRANSACTIONS_VERSION );
    }
       
    /**
     * wc_etransactions_migrate_old_data_from_v2
     *
     * @return void
     */
    public function minus_3_0_3() {

        require_once( WC_ETRANSACTIONS_PLUGIN_PATH . 'classes/wc-etransactions-legacy-encrypt.php' );
        $encrypt = new WC_Etransactions_Legacy_Encrypt();

        $new_data = get_option( 'wc_etransactions_account_environment', false );
        if ( $new_data ) {
            /**
             * Between 3.0.0 and 3.0.3
             */

            // Patch HMACKeys if needed
            $hmac_prod = wc_etransactions_get_option( 'account_hmac_prod' );
            $hmac_test = wc_etransactions_get_option( 'account_hmac_test' );

            if ( !empty( $hmac_prod ) && strlen( $hmac_prod ) > 128 ) {
                $hmac_prod = $encrypt->decrypt($hmac_prod);
                wc_etransactions_update_option( 'account_hmac_prod', $hmac_prod );
            }

            if ( !empty( $hmac_test ) && strlen( $hmac_test ) > 128 ) {
                $hmac_test = $encrypt->decrypt($hmac_test);
                wc_etransactions_update_option( 'account_hmac_test', $hmac_test );
            }

            return;
        }

        $old_env = get_option( 'woocommerce_etransactions_std_env', false );

        if ( $old_env === 'PRODUCTION' ) {
            $env = WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_PRODUCTION;
        } else {
            $env = WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST;
        }
        wc_etransactions_update_option( 'account_environment', $env );

        $old_settings      = get_option( 'woocommerce_etransactions_std_settings', false );
        $old_test_settings = get_option( 'woocommerce_etransactions_std_test_settings', false );

        if ( is_array( $old_test_settings ) ) {
            foreach ( $old_test_settings as $key => $value ) {
                switch ( $key ) {
                    case 'site':
                        $old_site_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'account_site_number', $old_site_value );
                        break;
                    case 'rank':
                        $old_rank_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'account_rank', $old_rank_value );
                        break;
                    case 'identifier':
                        $old_id_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'account_id', $old_id_value );
                        break;
                    case 'hmackey':
                        $old_hmac_value = sanitize_text_field( $value );
                        $old_hmac_value = $encrypt->decrypt($value);
                        wc_etransactions_update_option( 'account_hmac_test', $old_hmac_value );
                        break;
                    case 'subscription':
                        $old_access_value = sanitize_text_field( $value );
                        $old_access_value = $old_access_value === '2' ? WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM : WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS;
                        wc_etransactions_update_option( 'account_contract_access', $old_access_value );
                        break;
                    case '3ds_exemption_max_amount':
                        $old_3ds_value = sanitize_text_field( $value );
                        $old_3ds_value = is_numeric( $old_3ds_value ) ? (int)$old_3ds_value : WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX;
                        $old_3ds_value = max( 1, min( $old_3ds_value, WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX ) );
                        $old_3ds_active = $old_3ds_value > 0 ? '1' : '0';
                        wc_etransactions_update_option( 'account_exemption3DS', $old_3ds_active );
                        wc_etransactions_update_option( 'account_max_amount3DS', $old_3ds_value );
                        break;
                    case 'display_generic_method':
                        $old_display_value = sanitize_text_field( $value );
                        $old_display_value = $old_display_value === 'yes' ? WC_Etransactions_Payment::PAYMENT_DISPLAY_SIMPLE : WC_Etransactions_Payment::PAYMENT_DISPLAY_DETAILED;
                        wc_etransactions_update_option( 'payment_display', $old_display_value );
                        break;
                    case 'title':
                        $old_title_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'payment_display_title', $old_title_value );
                        break;
                    case 'delay':
                        $old_debit_value        = sanitize_text_field( $value );
                        $old_debit_type_value   = $old_debit_value === '0' ? WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_IMMEDIATE : WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_DEFERRED;
                        $old_deferred_days      = min(WC_Etransactions_Payment::PAYMENT_DEFERRED_DAYS_MAX, max(WC_Etransactions_Payment::PAYMENT_DEFERRED_DAYS_MIN, $old_debit_type_value));
                        wc_etransactions_update_option( 'payment_debit_type', $old_debit_type_value );
                        wc_etransactions_update_option( 'payment_deferred_days', $old_deferred_days );
                        break;
                    case 'capture_order_status':
                        $old_capture_status = is_array( $value ) ? $value : array( sanitize_text_field($value));
                        wc_etransactions_update_option( 'payment_capture_status', $old_capture_status );
                        break;
                    default:
                        break;
                }
            }

            wc_etransactions_update_option( 'first_time', WC_Etransactions_Config::FIRST_TIME_LOGIN );
        }

        if ( is_array( $old_settings ) ) {
            foreach ( $old_settings as $key => $value ) {
                switch ( $key ) {
                    case 'site':
                        $old_site_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'account_site_number', $old_site_value );
                        break;
                    case 'rank':
                        $old_rank_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'account_rank', $old_rank_value );
                        break;
                    case 'identifier':
                        $old_id_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'account_id', $old_id_value );
                        break;
                    case 'hmackey':
                        $old_hmac_value = sanitize_text_field( $value );
                        $old_hmac_value = $encrypt->decrypt($value);
                        wc_etransactions_update_option( 'account_hmac_prod', $old_hmac_value );
                        break;
                    case 'subscription':
                        $old_access_value = sanitize_text_field( $value );
                        $old_access_value = $old_access_value === '2' ? WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM : WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS;
                        wc_etransactions_update_option( 'account_contract_access', $old_access_value );
                        break;
                    case '3ds_exemption_max_amount':
                        $old_3ds_value = sanitize_text_field( $value );
                        $old_3ds_value = is_numeric( $old_3ds_value ) ? (int)$old_3ds_value : WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX;
                        $old_3ds_value = max( 1, min( $old_3ds_value, WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX ) );
                        $old_3ds_active = $old_3ds_value > 0 ? '1' : '0';
                        wc_etransactions_update_option( 'account_exemption3DS', $old_3ds_active );
                        wc_etransactions_update_option( 'account_max_amount3DS', $old_3ds_value );
                        break;
                    case 'display_generic_method':
                        $old_display_value = sanitize_text_field( $value );
                        $old_display_value = $old_display_value === 'yes' ? WC_Etransactions_Payment::PAYMENT_DISPLAY_SIMPLE : WC_Etransactions_Payment::PAYMENT_DISPLAY_DETAILED;
                        wc_etransactions_update_option( 'payment_display', $old_display_value );
                        break;
                    case 'title':
                        $old_title_value = sanitize_text_field( $value );
                        wc_etransactions_update_option( 'payment_display_title', $old_title_value );
                        break;
                    case 'delay':
                        $old_debit_value        = sanitize_text_field( $value );
                        $old_debit_type_value   = $old_debit_value === '0' ? WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_IMMEDIATE : WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_DEFERRED;
                        $old_deferred_days      = min(WC_Etransactions_Payment::PAYMENT_DEFERRED_DAYS_MAX, max(WC_Etransactions_Payment::PAYMENT_DEFERRED_DAYS_MIN, $old_debit_type_value));
                        wc_etransactions_update_option( 'payment_debit_type', $old_debit_type_value );
                        wc_etransactions_update_option( 'payment_deferred_days', $old_deferred_days );
                        break;
                    case 'capture_order_status':
                        $old_capture_status = is_array( $value ) ? $value : array( sanitize_text_field($value));
                        wc_etransactions_update_option( 'payment_capture_status', $old_capture_status );
                        break;
                    default:
                        break;
                }
            }

            wc_etransactions_update_option( 'first_time', WC_Etransactions_Config::FIRST_TIME_LOGIN );
        }
    }

}