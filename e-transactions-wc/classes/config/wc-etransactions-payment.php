<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for payment configuration
 */
class WC_Etransactions_Payment extends WC_Etransactions_Config {

    const PAYMENT_CONTRACTS_ACCESS      = ['PAYPAL', 'CB VISA MASTERCARD'];
    const PAYMENT_DISPLAY_SIMPLE        = 'simple';
    const PAYMENT_DISPLAY_DETAILED      = 'detailed';
    const PAYMENT_DISPLAY_TITLE_DEFAULT = 'Secure payment with Crédit Agricole';
    const PAYMENT_DISPLAY_LOGO_DEFAULT  = WC_ETRANSACTIONS_PLUGIN_URL . 'assets/svg/payment-methods/CB_VISA_MC.svg';
    const PAYMENT_DEBIT_TYPE_IMMEDIATE  = 'immediate';
    const PAYMENT_DEBIT_TYPE_DEFERRED   = 'deferred';
    const PAYMENT_CAPTURE_EVENT_DAYS    = 'days';
    const PAYMENT_CAPTURE_EVENT_STATUS  = 'status';
    const PAYMENT_DEFERRED_DAYS_MIN      = 1;
    const PAYMENT_DEFERRED_DAYS_MAX      = 7;

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_display( $value ) {
        
        $value = sanitize_text_field($value);

        if ( !in_array($value, array(self::PAYMENT_DISPLAY_SIMPLE, self::PAYMENT_DISPLAY_DETAILED)) ) {
            $value = wc_etransactions_get_default_value('payment_display');
        }

        return $value;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_display_title( $value ) {

        $value = sanitize_text_field($value);

        if ( empty($value) ) {
            $value = wc_etransactions_get_default_value('payment_display_title');
        }

        return $value;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_display_logo( $value ) {

        if ( $value == self::PAYMENT_DISPLAY_LOGO_DEFAULT || empty($value) ) {
            $value = '';
        } else {
            $value = esc_url_raw( $value );
        }

        return $value;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_debit_type( $value ) {

        $value = sanitize_text_field($value);

        if ( !in_array($value, array(self::PAYMENT_DEBIT_TYPE_IMMEDIATE, self::PAYMENT_DEBIT_TYPE_DEFERRED)) ) {
            $value = wc_etransactions_get_default_value('payment_debit_type');
        }

        return $value;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_capture_event( $value ) {

        $value = sanitize_text_field($value);

        if ( !in_array($value, array(self::PAYMENT_CAPTURE_EVENT_DAYS, self::PAYMENT_CAPTURE_EVENT_STATUS)) ) {
            $value = wc_etransactions_get_default_value('payment_capture_event');
        }

        return $value;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_deferred_days( $value ) {

        $value = sanitize_text_field($value);
        $value = min(self::PAYMENT_DEFERRED_DAYS_MAX, max(self::PAYMENT_DEFERRED_DAYS_MIN, $value));

        return $value;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_capture_status( $value ) {

        if ( ! is_array($value) ) {
            return array();
        }

        $wc_order_statuses  = function_exists('wc_get_order_statuses') ? array_keys(wc_get_order_statuses()) : array();

        $result = array();
        foreach ( $value as $status ) {
            if ( in_array($status, $wc_order_statuses ) ) {
                $result[] = $status;
            }
        }

        return $result;
    }

    /**
     * Sanitize and Validate
     */
    public function sanitize_validate_payment_methods_settings( $value ) {

        $payment_methods_array      = wc_etransactions_get_payment_methods();
        $default_payment_methods    = wc_etransactions_get_default_value('payment_methods_settings');

        if ( !is_array($value) || empty($value) ) {
            return $default_payment_methods;
        }

        $sanitized_data = array();

        foreach ( $default_payment_methods as $payment_method_key => $payment_method_data ) {
            foreach ( $payment_method_data as $data_key => $data_value ) {

                if ( isset( $value[$payment_method_key][$data_key] ) ) {

                    $value_value = null;
                    switch ($data_key) {
                        case 'enabled':
                        case 'isSelectable':
                        case 'oneClickEnabled':
                            $value_value = $this->sanitize_validate_toggle( $value[$payment_method_key][$data_key] );
                        break;
                        case 'logoUrl':
                            if ( $value[$payment_method_key][$data_key] == $payment_methods_array[$payment_method_key][$data_key] ) {
                                $value_value = '';
                            } else {
                                $value_value = esc_url_raw( $value[$payment_method_key][$data_key] );
                            }
                        break;
                        default:
                            $value_value = sanitize_text_field( $value[$payment_method_key][$data_key] );
                        break;
                    }

                    $sanitized_data[$payment_method_key][$data_key] = $value_value;
                    
                } else {

                    $sanitized_data[$payment_method_key][$data_key] = $data_value;
                }
            }

            if ( $sanitized_data[$payment_method_key]['isSelectable'] === '1' ) {
                $sanitized_data[$payment_method_key]['enabled'] = '0';
                $sanitized_data[$payment_method_key]['oneClickEnabled'] = '0';
            }

            if ( $payment_method_key == 'UPI' ) {

                $payment_debit_type = $this->sanitize_validate_payment_debit_type( $_POST[wc_etransactions_add_prefix('payment_debit_type')] ?? null );
                if ( $payment_debit_type !== self::PAYMENT_DEBIT_TYPE_IMMEDIATE ) {
                    $sanitized_data[$payment_method_key]['enabled'] = '0';
                }
            }

        }

        return $sanitized_data;
    }
}
