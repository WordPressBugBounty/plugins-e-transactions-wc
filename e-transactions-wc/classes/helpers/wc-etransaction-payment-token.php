<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * Extended the Woocommerce payment tokens.
 *
 * @class   WC_Etransactions_Payment_Token
 * @extends WC_Payment_Token
 */
class WC_Etransactions_Payment_Token extends WC_Payment_Token_CC {
	
	protected $extra_data = array(
		'last4'         => '',
		'expiry_year'   => '',
		'expiry_month'  => '',
		'card_type'     => '',
		'phone_number'  => '',
		'phone_country' => '',
	);

	public function validate() {

		if ( false === parent::validate() ) {
			return false;
		}

		if ( ! $this->get_phone_number( 'edit' ) ) {
			return false;
		}

		if ( ! $this->get_phone_country( 'edit' ) ) {
			return false;
		}

		return true;
	}

	public function get_phone_number( $context = 'view' ) {
		return $this->get_prop( 'phone_number', $context );
	}

	public function set_phone_number( $type ) {
		$this->set_prop( 'phone_number', $type );
	}

	public function get_phone_country( $context = 'view' ) {
		return $this->get_prop( 'phone_country', $context );
	}

	public function set_phone_country( $type ) {
		$this->set_prop( 'phone_country', $type );
	}
}
