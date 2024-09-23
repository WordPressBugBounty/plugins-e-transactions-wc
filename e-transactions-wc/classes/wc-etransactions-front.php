<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for the front end
 *
 */
class WC_Etransactions_Front {

    /**
     * The class constructor.
     */
    public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_items' ) );
    }

    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts_styles() {

        if ( is_checkout() ) {

            $checkout_page_assets = include( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/build/checkout-page.asset.php' );
            wp_enqueue_style( 'wc-etransactions-checkout-page', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/checkout-page.css', array(), $checkout_page_assets['version'], 'all' );
            wp_enqueue_script( 'wc-etransactions-checkout-page', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/checkout-page.js', $checkout_page_assets['dependencies'], $checkout_page_assets['version'], true );
            wp_localize_script( 'wc-etransactions-checkout-page', 'wc_etransactions', array(
                'plugin_url'          => WC_ETRANSACTIONS_PLUGIN_URL,
                'utils_path'          => WC_ETRANSACTIONS_PLUGIN_URL . 'assets/libs/utils.js',
                'account_demo_mode'   => wc_etransactions_get_option( 'account_demo_mode' ),
                'account_environment' => wc_etransactions_get_option( 'account_environment' ),
				'i18n' => array(
					'environment' => __( "You are using Up2Pay %s environment", 'wc-etransactions' ),
					'enterNumber' => __( 'You must enter a valid phone number to place an order', 'wc-etransactions' ),
					'validNumber' => __( 'Please fill a valid number', 'wc-etransactions' ),
					'oneClick'    => __( 'Store my credit card details for future payments.', 'wc-etransactions' ),
				),
            ));
        }
    }

	/**
	 * Add account menu items
	 */
	public function add_account_menu_items( $items ) {

        $payment_methods_settings = wc_etransactions_get_option( 'payment_methods_settings' );
		$cb_one_click_enabled     = $payment_methods_settings['CB']['oneClickEnabled'] ?? '0';

		if ( '1' != $cb_one_click_enabled ) {
			return $items;
		}

		$new_items = array();
		foreach ( $items as $item_key => $item_value ) {
			
			$new_items[ $item_key ] = $item_value;
			if ( 'edit-address' == $item_key && ! isset( $items['payment-methods'] ) ) {
				$new_items['payment-methods'] = __( 'Payment methods', 'woocommerce' );
			}
		}

		return $new_items;
	}
}
