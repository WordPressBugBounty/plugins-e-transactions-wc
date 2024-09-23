<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for the gateways
 *
 */
final class WC_Etransactions_Gateways_Block_Support extends AbstractPaymentMethodType {

    protected $name = 'wc_etransctions';

    /**
	 * Initializes the payment method type.
	 */
	public function initialize() {}

    /**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
        return true;
	}

    /**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {

        $checkout_blocks_assets = include( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/build/checkout-blocks.asset.php' );
        wp_register_script( WC_ETRANSACTIONS_PLUGIN . '_checkout_blocks', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/checkout-blocks.js', $checkout_blocks_assets['dependencies'], $checkout_blocks_assets['version'], true );

		return [
            WC_ETRANSACTIONS_PLUGIN . '_checkout_blocks',
        ];
	}

    /**
	 * Returns an array of localized strings for this payment method.
	 */
	public function get_payment_method_data() {

		global $wce_payment_methods;

		$methods = array();

		if ( isset( $wce_payment_methods ) && is_array( $wce_payment_methods ) ) {

			foreach ( $wce_payment_methods as $payment_method ) {
	
				$method_data = array(
					'name'	        => $payment_method->id,
					'label'	        => $payment_method->title,
					'icon'	        => $payment_method->icon,
					'description'   => $payment_method->description,
					'params'		=> $payment_method->params
				);
	
				$methods[] = $method_data;
			}
		}

		return [
			'methods' => $methods
		];
	}
}
