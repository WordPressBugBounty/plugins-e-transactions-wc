<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-Transactions - Individual Payment Gateway class.
 *
 * @class   WC_EStd_Gw
 * @extends WC_Etransactions_Abstract_Gateway
 */
class WC_EStd_Gw extends WC_Etransactions_Abstract_Gateway {

    /**
     * The class constructor
     */
    public function __construct( $params = array() ) {

        $params = wp_parse_args($params, array(
            'id'                    => '',
            'sub_id'                => '',
            'method_title'          => __( 'Up2pay e-Transactions Crédit Agricole', 'wc-etransactions' ),
            'method_description'    => __( 'Up2pay e-Transactions est la solution de paiement à distance dans un environnement sécurisé du Crédit Agricole.', 'wc-etransactions' ),
            'title'                 => '',
            'description'           => '',
            'icon'                  => '',
            'one_click_enabled'     => '0',
            'iframe'                => '0',
            'card_type'             => '',
            'paiment_type'          => '',
            'type'                  => 'card',
            'token'                 => '',
        ));

        $this->id                   = 'etransactions_std' . $params['sub_id'];
        $this->method_title         = $params['method_title'];
        $this->method_description   = $params['method_description'];
        $this->title                = $params['title'];
        $this->description          = $params['description'];
        $this->icon                 = $params['icon'];
        $params['gateway_id']       = $this->id;
        $this->params               = $params;
        $this->has_fields           = false;

        parent::__construct();
    }

}
