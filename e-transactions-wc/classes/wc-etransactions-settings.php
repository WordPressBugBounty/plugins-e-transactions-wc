<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Class responsible for the plugin settings page
 *
 */
class WC_Etransactions_Settings {

    /**
     * The class constructor.
     */
    public function __construct() {

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
        add_action('admin_menu', array($this, 'add_settings_menu') );
        add_action('admin_init', array($this, 'save_settings_menu') );
		add_action('wp_ajax_wc_etransactions_get_test_request', array($this, 'get_test_request') );
		add_action('wp_ajax_wc_etransactions_get_log_file_content', array($this, 'get_log_file_content') );
	 	add_action('admin_init', array($this, 'maybe_redirect_to_onboarding'), 11);
		add_action( 'admin_footer', array( $this, 'render_deactivate_popup' ), 99 );
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts( $hook_suffix ) {

        if ( $hook_suffix === 'toplevel_page_credit-agricole-settings' ) {
            wp_enqueue_media();
            $adminAsset = include( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/build/admin-settings.asset.php' );
            wp_enqueue_style( 'wce-admin-settings-style', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/admin-settings.css', array(), $adminAsset['version'], 'all' );
            wp_enqueue_script( 'wce-admin-settings-script', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/admin-settings.js', $adminAsset['dependencies'], $adminAsset['version'], true );
            wp_set_script_translations( 'wce-admin-settings-script', 'wc-etransactions', WC_ETRANSACTIONS_PLUGIN_PATH . 'languages' );
            wp_localize_script( 'wce-admin-settings-script', 'WC_ETRANSACTION_ADMIN_DATA', array(
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'wc_etransaction_ajax_nonce' ),
				'i18n' => array(
					'select'    => __( 'Select Some Options', 'wc-etransactions' ),
					'noResults' => __( 'No results found', 'wc-etransactions' ),
				),
            ));

        } elseif ( $hook_suffix === 'plugins.php' ) {
            $deactivate_popup = include( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/build/deactivate-popup.asset.php' );
            wp_enqueue_style( 'wce-deactivate-popup', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/deactivate-popup.css', array(), $deactivate_popup['version'], 'all' );
            wp_enqueue_script( 'wce-deactivate-popup', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/deactivate-popup.js', $deactivate_popup['dependencies'], $deactivate_popup['version'], true );
        }

    }

    /**
     * Add settings menu
     */
    public function add_settings_menu() {

        if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
            $orders_slug = '/admin.php?page=wc-orders';
        } else {
            $orders_slug = '/edit.php?post_type=shop_order';
        }

        add_menu_page(
			__("Up2pay Settings", 'wc-etransactions'),
			__("Up2pay", 'wc-etransactions'),
			'manage_options',
			'credit-agricole-settings',
			array( $this, 'render_settings_page' ),
			WC_ETRANSACTIONS_PLUGIN_URL . 'assets/img/menu-logo.png',
			56
		);

        add_submenu_page(
            'credit-agricole-settings',
            __( 'Settings', 'wc-etransactions' ),
            __( 'Settings', 'wc-etransactions' ),
            'manage_options',
            'credit-agricole-settings',
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'credit-agricole-settings',
            __( 'Transactions', 'wc-etransactions' ),
            __( 'Transactions', 'wc-etransactions' ),
            'manage_options',
            $orders_slug,
            ''
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {

        $config_class = new WC_Etransactions_Config();

		$current_language               = substr( get_locale(), 0, 2 );
		$header_download_pdf            = file_exists( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/pdf/readme_'.$current_language.'.pdf' ) ? WC_ETRANSACTIONS_PLUGIN_URL . 'assets/pdf/readme_'.$current_language.'.pdf' : WC_ETRANSACTIONS_PLUGIN_URL . 'assets/pdf/readme_en.pdf';
        $use_secondary_gateway          = wc_etransactions_get_option( 'use_secondary_gateway' );
        $enable_logs                    = wc_etransactions_get_option( 'enable_logs' );
        $config_requirements            = $this->get_config_requirements();
        // $test_payment_request           = $config_class->test_payment_request();
        $woocommerce_currency           = get_woocommerce_currency();
        $wc_order_statuses              = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : array();
		$payment_methods	            = wc_etransactions_get_payment_methods();
        $instalments                    = wc_etransactions_get_instalments();
        $first_time                     = wc_etransactions_get_option( 'first_time' );
        $intro_show_again               = wc_etransactions_get_option( 'intro_show_again' );
        $account_demo_mode              = wc_etransactions_get_option( 'account_demo_mode' );
        $account_environment            = wc_etransactions_get_option( 'account_environment' );
        $account_site_number            = wc_etransactions_get_option( 'account_site_number' );
        $account_rank                   = wc_etransactions_get_option( 'account_rank' );
        $account_id                     = wc_etransactions_get_option( 'account_id' );
        $account_hmac_test              = wc_etransactions_get_option( 'account_hmac_test' );
        $account_hmac_prod              = wc_etransactions_get_option( 'account_hmac_prod' );
        $account_site_number_demo       = wc_etransactions_get_option( 'account_site_number_demo' );
        $account_rank_demo              = wc_etransactions_get_option( 'account_rank_demo' );
        $account_id_demo                = wc_etransactions_get_option( 'account_id_demo' );
        $account_hmac_demo              = wc_etransactions_get_option( 'account_hmac_demo' );
        $account_contract_access        = wc_etransactions_get_option( 'account_contract_access' );
        $account_exemption3DS           = wc_etransactions_get_option( 'account_exemption3DS' );
        $account_max_amount3DS          = wc_etransactions_get_option( 'account_max_amount3DS' );
        $payment_display                = wc_etransactions_get_option( 'payment_display' );
        $payment_display_title          = wc_etransactions_get_option( 'payment_display_title' );
        $payment_display_logo           = wc_etransactions_get_option( 'payment_display_logo' );
        $payment_debit_type             = wc_etransactions_get_option( 'payment_debit_type' );
        $payment_capture_event          = wc_etransactions_get_option( 'payment_capture_event' );
        $payment_deferred_days          = wc_etransactions_get_option( 'payment_deferred_days' );
        $payment_capture_status         = wc_etransactions_get_option( 'payment_capture_status' );
        $payment_methods_settings       = wc_etransactions_get_option( 'payment_methods_settings' );
        $instalment_enabled             = wc_etransactions_get_option( 'instalment_enabled' );
        $instalment_settings            = wc_etransactions_get_option( 'instalment_settings' );

        require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings.php';
    }

    /**
     * Save settings menu
     */
    public function save_settings_menu() {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( isset( $_POST['wc_etransactions_admin_nonce'] ) ) {

            $nonce = sanitize_text_field( wp_unslash( $_POST['wc_etransactions_admin_nonce'] ) );

            if ( wp_verify_nonce( $nonce, 'wc_etransactions_admin_action' ) ) {

                if ( isset($_POST['wc_etransactions_settings_account']) ) {

                    $account_class = new WC_Etransactions_Account();

                    $account_demo_mode          = $account_class->sanitize_validate_account_demo_mode( $_POST[wc_etransactions_add_prefix('account_demo_mode')] ?? null );
                    $account_environment        = $account_class->sanitize_validate_account_environment( $_POST[wc_etransactions_add_prefix('account_environment')] ?? null );
                    $account_site_number        = $account_class->sanitize_validate_account_site_number( $_POST[wc_etransactions_add_prefix('account_site_number')] ?? null );
                    $account_rank               = $account_class->sanitize_validate_account_rank( $_POST[wc_etransactions_add_prefix('account_rank')] ?? null );
                    $account_id                 = $account_class->sanitize_validate_account_id( $_POST[wc_etransactions_add_prefix('account_id')] ?? null );
                    $account_hmac_test          = $account_class->sanitize_validate_account_hmac_test( $_POST[wc_etransactions_add_prefix('account_hmac_test')] ?? null );
                    $account_hmac_prod          = $account_class->sanitize_validate_account_hmac_prod( $_POST[wc_etransactions_add_prefix('account_hmac_prod')] ?? null );
                    $account_contract_access    = $account_class->sanitize_validate_account_contract_access( $_POST[wc_etransactions_add_prefix('account_contract_access')] ?? null );
                    $account_exemption3DS       = $account_class->sanitize_validate_account_exemption3DS( $_POST[wc_etransactions_add_prefix('account_exemption3DS')] ?? null );
                    $account_max_amount3DS      = $account_class->sanitize_validate_account_max_amount3DS( $_POST[wc_etransactions_add_prefix('account_max_amount3DS')] ?? null );

                    $hmac = '';
                    if ( $account_environment === WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST ) {
                        $hmac = $account_hmac_test;
                    } elseif ( $account_environment === WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_PRODUCTION ) {
                        $hmac = $account_hmac_prod;
                    }

                    if ( empty( $account_site_number ) || empty( $account_rank ) || empty( $account_id ) || empty( $hmac ) ) {
                        add_action( 'admin_notices', function(){
                            echo '<div class="error"><p><strong>' . __( 'Please fill in all the required fields.', 'wc-etransactions' ) . '</strong></p></div>';
                        });
                        return;
                    }

                    wc_etransactions_update_option( 'account_demo_mode', $account_demo_mode );
                    wc_etransactions_update_option( 'account_environment', $account_environment );
                    wc_etransactions_update_option( 'account_site_number', $account_site_number );
                    wc_etransactions_update_option( 'account_rank', $account_rank );
                    wc_etransactions_update_option( 'account_id', $account_id );
                    wc_etransactions_update_option( 'account_hmac_test', $account_hmac_test );
                    wc_etransactions_update_option( 'account_hmac_prod', $account_hmac_prod );
                    wc_etransactions_update_option( 'account_contract_access', $account_contract_access );
                    wc_etransactions_update_option( 'account_exemption3DS', $account_exemption3DS );
                    wc_etransactions_update_option( 'account_max_amount3DS', $account_max_amount3DS );
                    wc_etransactions_update_option( 'first_time', WC_Etransactions_Config::FIRST_TIME_LOGIN );

                } elseif ( isset($_POST['wc_etransactions_settings_payment']) ) {

                    $payment_class = new WC_Etransactions_Payment();

                    $payment_display            = $payment_class->sanitize_validate_payment_display( $_POST[wc_etransactions_add_prefix('payment_display')] ?? null );
                    $payment_display_title      = $payment_class->sanitize_validate_payment_display_title( $_POST[wc_etransactions_add_prefix('payment_display_title')] ?? null );
                    $payment_display_logo       = $payment_class->sanitize_validate_payment_display_logo( $_POST[wc_etransactions_add_prefix('payment_display_logo')] ?? null );
                    $payment_debit_type         = $payment_class->sanitize_validate_payment_debit_type( $_POST[wc_etransactions_add_prefix('payment_debit_type')] ?? null );
                    $payment_capture_event      = $payment_class->sanitize_validate_payment_capture_event( $_POST[wc_etransactions_add_prefix('payment_capture_event')] ?? null );
                    $payment_deferred_days      = $payment_class->sanitize_validate_payment_deferred_days( $_POST[wc_etransactions_add_prefix('payment_deferred_days')] ?? null );
                    $payment_capture_status     = $payment_class->sanitize_validate_payment_capture_status( $_POST[wc_etransactions_add_prefix('payment_capture_status')] ?? null );
                    $payment_methods_settings   = $payment_class->sanitize_validate_payment_methods_settings( $_POST[wc_etransactions_add_prefix('payment_methods_settings')] ?? null );

                    wc_etransactions_update_option( 'payment_display', $payment_display );
                    wc_etransactions_update_option( 'payment_display_title', $payment_display_title );
                    wc_etransactions_update_option( 'payment_display_logo', $payment_display_logo );
                    wc_etransactions_update_option( 'payment_debit_type', $payment_debit_type );
                    wc_etransactions_update_option( 'payment_capture_event', $payment_capture_event );
                    wc_etransactions_update_option( 'payment_deferred_days', $payment_deferred_days );
                    wc_etransactions_update_option( 'payment_capture_status', $payment_capture_status );
                    wc_etransactions_update_option( 'payment_methods_settings', $payment_methods_settings );

                } elseif ( isset($_POST['wc_etransactions_settings_instalment']) ) {

                    $instalment_class = new WC_Etransactions_Instalment();

                    $instalment_enabled     = $instalment_class->sanitize_validate_instalment_enabled( $_POST[wc_etransactions_add_prefix('instalment_enabled')] ?? null );
                    $instalment_settings    = $instalment_class->sanitize_validate_instalment_settings( $_POST[wc_etransactions_add_prefix('instalment_settings')] ?? null );

                    wc_etransactions_update_option( 'instalment_enabled', $instalment_enabled );
                    wc_etransactions_update_option( 'instalment_settings', $instalment_settings );

                } elseif ( isset($_POST['wc_etransactions_settings_popup_config']) ) {

                    $config_class = new WC_Etransactions_Config();

                    $use_secondary_gateway  = $config_class->sanitize_validate_toggle( $_POST[wc_etransactions_add_prefix('use_secondary_gateway')] ?? null );
                    $enable_logs            = $config_class->sanitize_validate_toggle( $_POST[wc_etransactions_add_prefix('enable_logs')] ?? null );

                    wc_etransactions_update_option( 'use_secondary_gateway', $use_secondary_gateway );
                    wc_etransactions_update_option( 'enable_logs', $enable_logs );

                } elseif ( isset( $_POST['wc_etransactions_dont_show_again'] ) ) {
        
                    wc_etransactions_update_option( 'intro_show_again', '0' );

                } elseif ( isset( $_POST['wc_etransactions_first_time'] ) ) {

                    $config_class = new WC_Etransactions_Config();

                    $first_time = $config_class->sanitize_validate_first_time( $_POST[wc_etransactions_add_prefix('first_time')] );

                    if ( $first_time === WC_Etransactions_Config::FIRST_TIME_DEMO ) {
                        wc_etransactions_update_option( 'first_time', $first_time );
                        wc_etransactions_update_option( 'account_demo_mode', '1' );
                    }

                } elseif ( isset($_POST['wc_etransactions_deactivate_popup']) ) {
                    $this->reset_and_deactivate();
                }

            } else {
                add_action( 'admin_notices', function(){
					echo '<div class="error"><p><strong>' . __( 'Please try again.', 'wc-etransactions' ) . '</strong></p></div>';
				});
            }
        }
    }

    /**
     * Get support info
     */
    public function get_support_info() {

        if ( function_exists('curl_version') ) {
            $curl_info      = curl_version();
            $curl_version   = $curl_info['version'];
            $ssl_version    = $curl_info['ssl_version'];
		} elseif ( extension_loaded( 'curl' ) ) {
            $curl_version   = __( 'cURL installed but unable to retrieve version.', 'wc-etransactions' );
            $ssl_version    = __( 'cURL installed but unable to retrieve version.', 'wc-etransactions' );
        }else {
            $curl_version   = __( 'cURL not installed.', 'wc-etransactions' );
            $ssl_version    = __( 'cURL not installed.', 'wc-etransactions' );
        }

        $info = "\n=================================================";
        $info .= "\n\t" . __( "WordPress & server configuration", 'wc-etransactions' );
        $info .= "\n=================================================";
        $info .= "\n" . __( "WordPress version", 'wc-etransactions' ) . " : " . get_bloginfo('version');
        $info .= "\n" . __( "PHP version", 'wc-etransactions' ) . " : " . phpversion();
        $info .= "\n" . __( "Server info", 'wc-etransactions' ) . " : " . ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '' );
        $info .= "\n" . __( "Plugin version", 'wc-etransactions' ) . " : " . WC_ETRANSACTIONS_VERSION;
        $info .= "\n" . __( "cURL version", 'wc-etransactions' ) . " : " . $curl_version;
        $info .= "\n" . __( "OpenSSL version", 'wc-etransactions' ) . " : " . $ssl_version;
        $info .= "\n" . __( "WordPress multisite enabled", 'wc-etransactions' ) . " : " . ( is_multisite() ? __( 'Yes', 'wc-etransactions' ) : __( 'No', 'wc-etransactions' ) );
        $info .= "\n" . __( "WordPress debug mode enabled", 'wc-etransactions' ) . " : " . ( defined( 'WP_DEBUG' ) && WP_DEBUG ? __( 'Yes', 'wc-etransactions' ) : __( 'No', 'wc-etransactions' ) );

        $info .= "\n\n\n=================================================";
        $info .= "\n\t" . __( "Plugin configuration", 'wc-etransactions' );
        $info .= "\n=================================================";
        $info .= "\n" . __( "Main configuration", 'wc-etransactions' );
        $info .= "\n" . "{";
        $info .= "\n\t \"environment\" : " . wc_etransactions_get_option( 'account_environment' );
        $info .= "\n\t \"demoMode\" : " . (wc_etransactions_get_option( 'account_demo_mode' ) === '1' ? 'true' : 'false');
        $info .= "\n\t \"logsEnabled\" : " . (wc_etransactions_get_option( 'enable_logs' ) === '1' ? 'true' : 'false');
        $info .= "\n\t \"useSecondaryGateway\" : " . (wc_etransactions_get_option( 'use_secondary_gateway' ) === '1' ? 'true' : 'false');
        $info .= "\n\t \"contract\" : " . wc_etransactions_get_option( 'account_contract_access');
        $info .= "\n\t \"exemption3DS\" : " . (wc_etransactions_get_option( 'account_exemption3DS' ) === '1' ? 'true' : 'false');
        $info .= "\n\t \"maxAmount3DS\" : " . wc_etransactions_get_option( 'account_max_amount3DS');
        $info .= "\n" . "}";

        $info .= "\n\n" . __( "Contract configuration", 'wc-etransactions' );
        $info .= "\n" . "{";
        $info .= "\n\t \"siteNumber\" : " . wc_etransactions_get_option( 'account_site_number');
        $info .= "\n\t \"rank\" : " . wc_etransactions_get_option( 'account_rank');
        $info .= "\n\t \"id\" : " . wc_etransactions_get_option( 'account_id');
        $info .= "\n\t \"hmacTest\" : " . "****";
        $info .= "\n\t \"hmacProd\" : " . "****";
        $info .= "\n" . "}";

        $info .= "\n\n" . __( "Payment setup", 'wc-etransactions' );
        $info .= "\n" . "{";
        $info .= "\n\t \"display\" : " . wc_etransactions_get_option( 'payment_display');
        $info .= "\n\t \"debitType\" : " . wc_etransactions_get_option( 'payment_debit_type');
        $info .= "\n\t \"captureEvent\" : " . wc_etransactions_get_option( 'payment_capture_event');
        $info .= "\n\t \"deferredDays\" : " . wc_etransactions_get_option( 'payment_deferred_days');
        $info .= "\n\t \"captureStatuses\" : " . json_encode( wc_etransactions_get_option( 'payment_capture_status'), JSON_PRETTY_PRINT);
        $info .= "\n\t \"displayTitle\" : " . wc_etransactions_get_option( 'payment_display_title');
        $info .= "\n" . "}";

        $info .= "\n\n" . __( "Setting up payment methods", 'wc-etransactions' );
        $info .= "\n" . json_encode(wc_etransactions_get_option( 'payment_methods_settings'), JSON_PRETTY_PRINT);
        
        $info .= "\n\n" . __( "Configuring payment in X times", 'wc-etransactions' );
        $info .= "\n" . json_encode(wc_etransactions_get_option( 'instalment_settings'), JSON_PRETTY_PRINT);

        $info .= "\n\n\n=================================================";
        $info .= "\n\t" . __( "Active plugins", 'wc-etransactions' );
        $info .= "\n=================================================";
        $info .= print_r( $this->get_active_plugins(), true );

        $info .= "\n\n\n=================================================";
        $info .= "\n\t" . __( "Inactive plugins", 'wc-etransactions' );
        $info .= "\n=================================================";
        $info .= print_r( $this->get_inactive_plugins(), true );

        return $info;
    }

    /**
     * Get active plugins
     */
    public function get_active_plugins() {
		$active_plugins_data = get_transient( 'wc_etransaction_active_plugins' );

        if ( false === $active_plugins_data ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( ! function_exists( 'get_plugin_data' ) ) {
				return array();
			}

			$active_plugins = (array) get_option( 'active_plugins', array() );
			if ( is_multisite() ) {
				$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
				$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
			}

			$active_plugins_data = '';
			foreach ( $active_plugins as $plugin ) {
				$data                   = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$active_plugins_data   .= "\n" . $data['Name'] . ' - v' . $data['Version'];
			}

			set_transient( 'wc_etransaction_active_plugins', $active_plugins_data, HOUR_IN_SECONDS );
		}

        return $active_plugins_data;
    }

    /**
     * Get inactive plugins
     */
    public function get_inactive_plugins() {

		$plugins_data = get_transient( 'wc_etransaction_inactive_plugins' );

        if ( false === $plugins_data ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( ! function_exists( 'get_plugins' ) ) {
				return array();
			}

			$plugins        = get_plugins();
			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
				$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
			}

			$plugins_data = '';

			foreach ( $plugins as $plugin => $data ) {
				if ( in_array( $plugin, $active_plugins, true ) ) {
					continue;
				}
				$plugins_data .= "\n" . $data['Name'] . ' - v' . $data['Version'];
			}

			set_transient( 'wc_etransaction_inactive_plugins', $plugins_data, HOUR_IN_SECONDS );
		}

        return $plugins_data;
    }

    /**
     * Get config requirements
     */
    public function get_config_requirements() {

        $config_class = new WC_Etransactions_Config();

        return array(
            array(
                'text'  => __( "PHP version", 'wc-etransactions' ),
                'pass'  => version_compare( phpversion(), '5.6', '>=' )
            ),
            array(
                'text'  => __( "EURO currency installed", 'wc-etransactions' ),
                'pass'  => get_woocommerce_currency() === 'EUR'
            ),
            array(
                'text'  => __( "Up2pay account configured", 'wc-etransactions' ),
                'pass'  => $config_class->is_account_configured()
            )
        );
    }

    /**
     * Get test request
     */
    public function get_test_request() {

        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'wc_etransaction_ajax_nonce' ) ) {
			wp_send_json_error();
		}

        $config_class = new WC_Etransactions_Config();
        $test_request = $config_class->test_payment_request();

        if ( $test_request ) {
            wp_send_json_success();
        }

        wp_send_json_error();
    }

    /**
     * Get log file content
     */
    public function get_log_file_content() {

        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
		if( !wp_verify_nonce( $nonce, 'wc_etransaction_ajax_nonce' ) ) {
			wp_send_json_error();
		}

        $files = $this->get_log_files();
        if ( empty( $files ) ) {
			wp_send_json_error( __( 'No log files found!', 'wc-etransactions' ) );
		}

        $files = array_filter( $files, function( $file_name ) {
			return strpos( $file_name, WC_ETRANSACTIONS_PLUGIN .'-' . wp_date( 'Y-m' ) ) !== false;
		});

        $content = '';
        if ( !empty( $files ) && is_array($files) ) {

			$files = array_reverse( $files );
	
			foreach ( $files as $file_name ) {
	
                $upload_dir = wp_upload_dir();
                $log_dir    = $upload_dir['basedir'] . '/wc-logs/';
				$file_path	= $log_dir . $file_name;
				$file_exist	= file_exists( $file_path );
	
				if ( $file_exist ) {
					$content .= file_get_contents( $file_path );
				}
			}
		}

        $data = array(
            'fileName'  => WC_ETRANSACTIONS_PLUGIN . '-' . wp_date( 'Y-m' ) . '.log',
            'content'   => $content,
        );
        wp_send_json_success( $data );
    }

    /**
     * Get log files
     */
    public function get_log_files() {

        $upload_dir = wp_upload_dir();
        $log_dir    = $upload_dir['basedir'] . '/wc-logs/';
        $files      = @scandir( $log_dir );
        $result     = array();

        if ( !empty( $files ) ) {

            rsort( $files );

            foreach ( $files as $file ) {

                if ( strstr( $file, WC_ETRANSACTIONS_PLUGIN ) ) {
                    array_push( $result, $file );
                }
            }
        }
        
        return $result;
    }

    /**
     * Redirect from WC payment page to custom settings page
     */
    public function maybe_redirect_to_onboarding() {

        if ( wp_doing_ajax() || ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}

        $get_page 		= isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$get_tab  		= isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
		$get_section	= isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';

        if ( $get_page == 'wc-settings' && $get_tab == 'checkout' && strpos( $get_section, 'etransactions') !== false ) {
			
			wp_safe_redirect( admin_url( add_query_arg( ['page' => 'credit-agricole-settings'], 'admin.php' ) ) );
			exit;
		}
    }

    /**
     * Deactivate popup
     */
    public function render_deactivate_popup() {

        $screen = get_current_screen();

        if ( ! $screen || $screen->base !== 'plugins' ) {
            return;
        }

        include WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/deactivate-popup.php';
    }

    /**
     * Reset and deactivate the plugin
     */
    public function reset_and_deactivate() {

        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $default_values = wc_etransactions_get_default_value();

        foreach ( $default_values as $key => $value ) {
            wc_etransactions_update_option( $key, $value );
        }

        deactivate_plugins( WC_ETRANSACTIONS_PLUGIN_BASENAME, true );
        wp_safe_redirect( admin_url( 'plugins.php' ) );
        exit;
    }
}
