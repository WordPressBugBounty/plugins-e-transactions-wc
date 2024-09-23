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

    /* @var string $version */
    public $version;

	/* @var array $crons_queue */
	public $crons_queue;

	/* @var array $crons_list */
	public $crons_list;

    /**
     * Constructor
     */
    public function __construct() {
        $this->version     = wc_etransactions_get_option('version');
		$this->crons_queue = array();
		$this->crons_list  = array();

        $updated = version_compare( $this->version, WC_ETRANSACTIONS_VERSION, '<' );
        if ( $updated ) {
			add_filter( 'cron_schedules', array( $this, 'crons_registrations' ) );
			add_action( 'wp', array( $this, 'cron_events') );
            add_action( 'init', array( $this, 'update' ) );
        }
    }

	/**
	 * Register the cron events
	 * 
	 * @return array
	 */
	public function crons_registrations( $schedules ) {
		foreach ( $this->crons_list as $cron_settings ) {
			$cron_name = $cron_settings['callback'];
			$schedules[ $cron_name ] = array(
				'interval' => $cron_settings['interval'],
				'display'  => $cron_settings['display'],
			);
		}

		return $schedules;
    }

	/**
	 * Schedule cron events
	 *
	 * @return void
	 */
	public function cron_events() {
		foreach ( $this->crons_list as $cron_settings ) {
			$cron_name = $cron_settings['callback'];
			if ( ! wp_next_scheduled( 'wc_etransactions_' . $cron_name . '_hook', array( $cron_name ) ) ) {
				wp_schedule_event( time(), $cron_name, 'wc_etransactions_' . $cron_name . '_hook', array( $cron_name ) );
			}
		}
    }

	/**
	 * Add actions to the crons
	 * 
	 * @return void
	 */
	public function add_crons_actions() {
		foreach ( $this->crons_queue as $version => $callback ) {
			$this->crons_list[ $version ] = array(
				'callback' => $callback,
				'interval' => MINUTE_IN_SECONDS,
				'display'  => sprintf( esc_html__( 'Up2pay updater version %s', 'wc-etransactions' ), $version ), 
			);
			add_action( 'wc_etransactions_' . $callback . '_hook', array( $this, $callback ) );
		}
	}

	/**
	 * Update the version and remove the cron if exist
	 * 
	 * @return void
	 */
	public function version_completed( $version ) {
		wc_etransactions_update_option( 'version', $version );
		$current_version = $this->crons_list[ $version ] ?? array();
		if ( ! empty( $current_version ) ) {
			wp_unschedule_hook( 'wc_etransactions_' . $current_version['callback'] . '_hook' );
		}
	}

    /**
     * Launch the update process
     *
     * @return void
     */
    public function update() {
		switch ( true ) {
			case version_compare( $this->version, '3.0.3', '<' ):
				$this->minus_3_0_3();
			break;
			case version_compare( $this->version, '3.0.5', '<' ):
                wc_etransactions_add_log( 'Update to 3.0.5');
				$this->crons_queue['3.0.5'] = 'minus_3_0_5';
			break;
			default:
				$this->version_completed( WC_ETRANSACTIONS_VERSION );
			break;
		}

		$this->add_crons_actions();
    }
       
    /**
     * Minus 3.0.3
     * 
     * Migrate old version of the plugin from 2.X.X, 3.0.1, 3.0.2 to 3.0.3
     *
     * @return void
     */
    public function minus_3_0_3() {
        require_once( WC_ETRANSACTIONS_PLUGIN_PATH . 'classes/wc-etransactions-legacy-encrypt.php' );
        $encrypt = new WC_Etransactions_Legacy_Encrypt();

		try {
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
			} else {
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

			$this->version_completed( '3.0.3' );
        } catch ( Exception $e ){
            wc_etransactions_add_log( 'Error in ' . __METHOD__ . ': ' . $e->getMessage() );
        }
    }
        
    /**
     * Minus 3.0.5
     * 
     * Change status of orders with status wc-e-capture to processing
     *
     * @return void
     */
    public function minus_3_0_5() {
		try {
			$reach_the_end = false;
			$orders        = wc_get_orders( array(
				'type'   => 'shop_order',
				'limit'  => 20,
				'status' => 'wc-e-capture',
			) );

			if ( empty( $orders ) ) {
                wc_etransactions_add_log( 'Orders is empty');
				$reach_the_end = true;
			}

			$no_wc_e_capture_status = true;
			foreach ( $orders as $order ) {
				if ( is_a( $order, 'WC_Order' ) ){
                    wc_etransactions_add_log( 'status :'. $order->get_status());
                    wc_etransactions_add_log( 'status :'. $order->get_order_number());
					if ( $order->get_status() === 'e-capture' ) {
						$no_wc_e_capture_status = false;
						$order->update_status( 'processing', '', true );
                        wc_etransactions_add_log( 'Order update');
                    }

                }

            }

			if ( $no_wc_e_capture_status ) {
				$reach_the_end = true;
			}

			if ( $reach_the_end ) {
				$this->version_completed( '3.0.5' );
			}
		} catch ( Exception $e ){
            wc_etransactions_add_log( 'Error in ' . __METHOD__ . ': ' . $e->getMessage() );
        }
    }
}
