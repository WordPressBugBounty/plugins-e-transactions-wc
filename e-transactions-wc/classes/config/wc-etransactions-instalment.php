<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for instalment configuration
 */
class WC_Etransactions_Instalment extends WC_Etransactions_Config {

    const DAYS_BETWEEN_PAYMENTS_MIN = 1;
    const DAYS_BETWEEN_PAYMENTS_MAX = 90;

    /**
     * Sanitize and Validate the enabled option
     */
    public function sanitize_validate_instalment_enabled( $enabled ) {

        if ( empty($enabled) ) {
            return wc_etransactions_get_default_value('instalment_enabled');
        }

        return $this->sanitize_validate_toggle($enabled);
    }

    /**
     * Sanitize and Validate the settings option
     */
    public function sanitize_validate_instalment_settings( $settings ) {

        $instalments_array      = wc_etransactions_get_instalments();
        $default_instalments    = wc_etransactions_get_default_value('instalment_settings');

        if ( !is_array($settings) || empty($settings) ) {
            return $default_instalments;
        }

        $sanitized_data = array();

        foreach ( $default_instalments as $instalment_key => $instalment_data ) {
            foreach ( $instalment_data as $data_key => $data_value ) {

                if ( isset( $settings[$instalment_key][$data_key] ) ) {

                    $settings_value = null;
                    switch ($data_key) {
                        case 'enabled':
                            $settings_value = $this->sanitize_validate_toggle( $settings[$instalment_key][$data_key] );
                        break;
                        case 'percents':

                            $use_default = false;
                            if ( is_array($data_value) && count($data_value) > 1 ) {
                                $total = array_sum($data_value);
                                if ( $total != 100 ) {
                                    $use_default = true;
                                }
                            } else {
                                $use_default = true;
                            }

                            foreach ( $data_value as $percents_key => $percents_value) {
                                if ( $use_default ) {
                                    $settings_value[$percents_key] = $percents_value;
                                } else {
                                    $settings_value[$percents_key] = isset( $settings[$instalment_key][$data_key][$percents_key] ) ? sanitize_text_field( $settings[$instalment_key][$data_key][$percents_key] ) : $percents_value; 
                                }
                            }
                        break;
                        case 'daysBetweenPayments':
                            $settings_value = sanitize_text_field( $settings[$instalment_key][$data_key] );
                            $settings_value = min(self::DAYS_BETWEEN_PAYMENTS_MAX, max(self::DAYS_BETWEEN_PAYMENTS_MIN, $settings_value));
                        break;
                        case 'logoUrl':
                            if ( $settings[$instalment_key][$data_key] == $instalments_array[$instalment_key][$data_key] ) {
                                $settings_value = '';
                            } else {
                                $settings_value = esc_url_raw( $settings[$instalment_key][$data_key] );
                            }
                        break;
                        default:
                            $settings_value = sanitize_text_field( $settings[$instalment_key][$data_key] );
                        break;
                    }

                    $sanitized_data[$instalment_key][$data_key] = $settings_value;

                } else {

                    $sanitized_data[$instalment_key][$data_key] = $data_value;
                }
            }
        }

        return $sanitized_data;
    }

}