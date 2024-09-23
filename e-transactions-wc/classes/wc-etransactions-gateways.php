<?php

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for the gateways
 *
 */
class WC_Etransactions_Gateways {

    /**
     * The class constructor.
     */
    public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'payment_gateway_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
        add_action( 'woocommerce_blocks_loaded', array( $this, 'blocks_support' ) );

    }

    /**
     * Init the payment gateways
     */
    public function payment_gateway_init() {

        if ( ! wc_etransactions_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

        $methods_path = WC_ETRANSACTIONS_PLUGIN_PATH . 'payment-methods/';

		require_once $methods_path . 'wc-etransactions-abstract-gateway.php';
		require_once $methods_path . 'wc-etransactions-abstract-gateway-instalments.php';
		require_once $methods_path . 'wc-etransactions-standard-gateway.php';
		require_once $methods_path . 'wc-etransactions-threetime-gateway.php';

		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts_styles() {
        $style = '<style>';
		$style .= 'tr[data-gateway_id="etransactions_std"] > .status > .wc-payment-gateway-method-toggle-enabled { display: none !important; }';
		$style .= 'tr[data-gateway_id="etransactions_std"] > .name > .wc-payment-gateway-method-name { display: none !important; }';
		$style .= '</style>';

		echo $style;
    }

    /**
     * Add support for WooCommerce Blocks
     */
    public function blocks_support() {

		if( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

        require_once( WC_ETRANSACTIONS_PLUGIN_PATH . '/classes/wc-etransactions-gateways-blocks-support.php' );

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register(new WC_Etransactions_Gateways_Block_Support);
			}
		);
	}

    /**
     * Add custom payment gateways
     */
    function add_gateways( $methods ) {

        $config_class  = new WC_Etransactions_Config();
        $is_configured = $config_class->is_account_configured();

        if ( ! $is_configured ) {
            return $methods;
        }

        $wce_payment_tokens = array();
        $wce_methods        = array();
        $cart_total 	    = 0;
        $cart			    = WC()->cart;
        if ( $cart ) {
            $cart_total = (int) $cart->total;
        }

        $checkout_page_id	= wc_get_page_id( 'checkout' );
        $current_page_id	= isset($_GET['post']) ? $_GET['post'] : 0;
        $params = array(
            'page'	=> 'wc-settings',
            'tab' 	=> 'checkout'
        );
        $is_payment_methods_tab		= count( $params ) === count( array_intersect_assoc( $_GET, $params ) );
        $is_admin_checkout_page		= (int)$checkout_page_id === (int)$current_page_id;
        $payment_display_mode       = wc_etransactions_get_option('payment_display');
        $account_contract_access    = wc_etransactions_get_option('account_contract_access');
        $is_account_contract_access = $account_contract_access === WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS;

        if ( $payment_display_mode === WC_Etransactions_Payment::PAYMENT_DISPLAY_SIMPLE ) {

            $payment_display_title  = wc_etransactions_get_option('payment_display_title');
            $payment_display_logo   = wc_etransactions_get_option('payment_display_logo');

            $params = array(
                'title'         => !empty($payment_display_title) ? $payment_display_title : __(WC_Etransactions_Payment::PAYMENT_DISPLAY_TITLE_DEFAULT, 'wc-etransactions'),
                'description'   => $this->get_description(),
                'icon'          => !empty($payment_display_logo) ? $payment_display_logo : WC_Etransactions_Payment::PAYMENT_DISPLAY_LOGO_DEFAULT,
            );
            $wce_methods[] = new WC_EStd_Gw($params);

        } else {

            if ( ! $is_payment_methods_tab ) {

                $payment_methods            = wc_etransactions_get_payment_methods();
                $payment_methods_settings   = wc_etransactions_get_option('payment_methods_settings');

                foreach ( $payment_methods as $method_id => $default_data ) {

                    $method_data_in_db      = $payment_methods_settings[$method_id] ?? array();
                    $identifier             = $default_data['identifier'];
                    $enabled                = isset($method_data_in_db['enabled']) ? $method_data_in_db['enabled'] : $default_data['enabled'];
                    $min_amount             = isset($method_data_in_db['minAmount']) ? $method_data_in_db['minAmount'] : $default_data['minAmount'];
                    $title                  = isset($method_data_in_db['title']) ? $method_data_in_db['title'] : $default_data['title'];
                    $default_title          = $default_data['title'];
                    $logo_url               = isset($method_data_in_db['logoUrl']) ? $method_data_in_db['logoUrl'] : '';
                    $default_logo_url       = $default_data['logoUrl'];
                    $one_click_enabled      = isset($method_data_in_db['oneClickEnabled']) ? $method_data_in_db['oneClickEnabled'] : $default_data['oneClickEnabled'];
                    $force_redirect         = $default_data['forceRedirect'] === '1';
                    $display_type           = isset($method_data_in_db['displayType']) ? $method_data_in_db['displayType'] : $default_data['displayType'];

                    if ( $enabled !== '1' ) {
                        continue;
                    }

                    if ( $cart_total < $min_amount ) {
                        continue;
                    }

                    if ( $is_account_contract_access ) {
                        if ( !in_array($identifier,WC_Etransactions_Payment::PAYMENT_CONTRACTS_ACCESS) ) {
                            continue;
                        }
                    }

					if ( $one_click_enabled === '1' && !$is_admin_checkout_page ) {

                        $gateway_id         = 'etransactions_std_card_' . $method_id;
                        $existing_tokens    = WC_Payment_Tokens::get_tokens(array(
							'user_id' 		=> get_current_user_id(),
							'gateway_id'	=> $gateway_id
						));

                        foreach ( $existing_tokens as $id_token => $token ) {

                            $token_data     = $token->get_data();
                            $card_type      = $token_data['card_type'] ?? '';
                            $last4          = $token_data['last4'] ?? '';
                            $expiry_month   = $token_data['expiry_month'] ?? '';
                            $expiry_year    = $token_data['expiry_year'] ?? '';
                            $token_u_id     = $card_type . $expiry_month . $expiry_year;

							$params		= array(
								'id' 			        => $method_id,
								'sub_id' 		        => '_token_' . $method_id . $token_u_id,
                                'title'                 => sprintf( __( "Pay with my previously stored card - %s - %s/%s", 'wc-etransactions' ), $last4, $expiry_month, substr($expiry_year,2,2) ),
                                'description'           => $this->get_description('0'),
                                'icon'                  => file_exists(WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/payment-methods/' . $card_type . '.svg') ? WC_ETRANSACTIONS_PLUGIN_URL . 'assets/svg/payment-methods/' . $card_type . '.svg' : WC_ETRANSACTIONS_PLUGIN_URL . 'assets/svg/payment-methods/CB_VISA_MC.svg',
                                'one_click_enabled'     => '0',
                                'iframe'                => $force_redirect || $display_type !== 'iframe' ? '0' : '1',
                                'card_type'             => $default_data['cardType'],
                                'paiment_type'          => $default_data['paymentType'],
								'type'			        => 'token',
								'token'			        => $id_token,
							);

							$wce_payment_tokens[$gateway_id . $token_u_id]	= new WC_EStd_Gw($params);
						}
                    }

                    $params = array(
                        'id'                => $method_id,
                        'sub_id'            => '_card_' . $method_id,
                        'title'             => !empty($title) ? $title : $default_title,
                        'description'       => $this->get_description($one_click_enabled),
                        'icon'              => !empty($logo_url) ? $logo_url : $default_logo_url,
                        'one_click_enabled' => $one_click_enabled,
                        'iframe'            => $force_redirect || $display_type !== 'iframe' ? '0' : '1',
                        'card_type'         => $default_data['cardType'],
                        'paiment_type'      => $default_data['paymentType'],
                    );
                    $wce_methods[] = new WC_EStd_Gw($params);
                }
                
            } else {

                $wce_methods[] = new WC_EStd_Gw();
            }
        }

        $instalments_enabled = wc_etransactions_get_option('instalment_enabled');
        if ( $instalments_enabled === '1' && !$is_payment_methods_tab && !$is_account_contract_access ) {

            $instalments            = wc_etransactions_get_instalments();
            $instalments_settings   = wc_etransactions_get_option('instalment_settings');

            foreach ( $instalments as $instalment_key => $default_data ) {
                
                $instalment_data_in_db  = $instalments_settings[$instalment_key] ?? array();
                $enabled                = isset($instalment_data_in_db['enabled']) ? $instalment_data_in_db['enabled'] : $default_data['enabled'];

                if ( $enabled !== '1' ) {
                    continue;
                }

                $min_amount = isset($instalment_data_in_db['minAmount']) ? $instalment_data_in_db['minAmount'] : $default_data['minAmount'];
                $max_amount = isset($instalment_data_in_db['maxAmount']) ? $instalment_data_in_db['maxAmount'] : $default_data['maxAmount'];
                $is_min     = $min_amount != 0 ? $cart_total >= $min_amount : true;
				$is_max     = $max_amount != 0 ? $cart_total <= $max_amount : true;

                if ( is_checkout() && ( !$is_min || !$is_max ) ) {
                    continue;
                }

                $partial_payments       = $default_data['partialPayments'];
                $method_id              = $partial_payments . 'x';
                $title                  = isset($instalment_data_in_db['title']) ? $instalment_data_in_db['title'] : $default_data['title'];
                $default_title          = $default_data['title'];
                $logo_url               = isset($instalment_data_in_db['logoUrl']) ? $instalment_data_in_db['logoUrl'] : '';
                $default_logo_url       = $default_data['logoUrl'];

                $params = array(
                    'id'                    => $method_id,
                    'sub_id'                => '_instalment_' . $method_id,
                    'title'                 => !empty($title) ? $title : $default_title,
                    'description'           => $this->get_description(),
                    'icon'                  => !empty($logo_url) ? $logo_url : $default_logo_url,
                    'days_between_payments' => isset($instalment_data_in_db['daysBetweenPayments']) ? $instalment_data_in_db['daysBetweenPayments'] : $default_data['daysBetweenPayments'],
                    'percents'              => isset($instalment_data_in_db['percents']) ? $instalment_data_in_db['percents'] : $default_data['percents'],
                    'partial_payments'      => $partial_payments,
                );
                $wce_methods[] = new WC_E3_Gw($params);
            }
        }

        global $wce_payment_methods;
		$wce_payment_methods = $wce_methods;

        return array_merge( $methods, $wce_payment_tokens, $wce_methods );
    }

    /**
	 * Show a notice if the woocommerce plugin not exist or not activated
	 */
	public function woocommerce_missing_notice() {

        echo '<div class="error"><p><strong>Up2pay e-Transactions:</strong> ' . __('WooCommerce must be activated.', 'wc-etransactions') . '</p></div>';
	}

    /**
     * Get the front mode
     */
    public function get_description( $one_click_enabled = '0' ) {

        $account_demo_mode      = wc_etransactions_get_option( 'account_demo_mode' );
        $account_environment    = wc_etransactions_get_option( 'account_environment' );

        if ( $account_demo_mode === '0' ) {

            if ( $account_environment === WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_PRODUCTION ) {
                $mode = false;
            } else {
                $mode = 'TEST';
            }
        } else {
            $mode = 'DEMO';
        }

        $uniq_id = uniqid( 'wce_' );

        ob_start();
        ?>

            <?php if( $mode ) : ?>
                <div class="wce-description-notice wce-notice-warning wce-notice-padding">
                    <span><?php echo sprintf( __( "You are using Up2Pay %s environment", 'wc-etransactions' ), esc_html($mode) ); ?></span>
                </div>
            <?php endif; ?>

            <div class="wce-number">
                <div class="wce-number-notice wce-notice-warning wce-notice-padding">
                    <span><?php _e( 'You must enter a valid phone number to place an order', 'wc-etransactions' ); ?></span>
                </div>
                <div class="wce-number-input">
                    <input type="tel" id="<?php echo esc_attr( $uniq_id ); ?>">
                    <img class="wce_up2pay_phone_valid wce-hide" src="<?php echo esc_attr( WC_ETRANSACTIONS_PLUGIN_URL . 'assets/img/icons/icon_valid.png' ); ?>"/>
                    <span class="wce_up2pay_phone_error"><?php _e( 'Please fill a valid number', 'wc-etransactions' ); ?></span>
                </div>
                <script>
                    (function( d, w ) {
						var billingPhone = d.querySelector( '#billing_phone' );
                        var wceInput     = d.querySelector( '#<?php echo esc_attr( $uniq_id ); ?>' );
                        var wceInterval  = null;

                        function wceInitField( telInput ) {
                            if ( w.wceIntlTelInput !== undefined ) {
                                clearInterval( wceInterval );
                                wceIntlTelInput( telInput );

								if ( billingPhone ) {
									telInput.value = billingPhone.value;
									window.wceChangeTelInput( {}, telInput );
								}
                            } else {
                                wceInterval = setInterval(() => {
                                    wceInitField( telInput );
                                }, 100);
                            }
                        }
                        wceInitField( wceInput );

                        wceInput.addEventListener( 'change', function(e){ window.wceChangeTelInput( e, wceInput, false ) }.bind(this) );
                        wceInput.addEventListener( 'keyup', function(e){ window.wceChangeTelInput( e, wceInput, false ) }.bind(this) );
						if ( billingPhone ) {
							billingPhone.addEventListener( 'keyup', function(e){ 
								wceInput.value = e.target.value;
								window.wceChangeTelInput( {}, wceInput );
							}.bind(this) );
						}
                    })( document, window );
                </script>
            </div>

            <?php if( $one_click_enabled === '1' ) : ?>
                <div class="wce-one-click-notice wce-notice-padding">
                    <label for="wce_one_click"><input type="checkbox" name="wce_one_click" id="wce_one_click" value="1"/>
                        <span><?php _e( 'Store my credit card details for future payments.', 'wc-etransactions' ); ?></span>
                    </label>
                </div>
            <?php endif;

        return preg_replace('/\r|\n/', '', ob_get_clean());
    }

}