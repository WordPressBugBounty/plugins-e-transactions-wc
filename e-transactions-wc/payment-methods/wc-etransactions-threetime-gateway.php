<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-Transactions 3 times - Payment Gateway class.
 *
 * @class   WC_E3_Gw
 * @extends WC_Etransactions_Abstract_Gateway_Instalments
 */
class WC_E3_Gw extends WC_Etransactions_Abstract_Gateway_Instalments {

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
            'days_between_payments' => '30',
            'percents'              => ['50', '50'],
            'partial_payments'      => '2',
        ));

        $this->id                   = 'etransactions_3x' . $params['sub_id'];
        $this->method_title         = $params['method_title'];
        $this->method_description   = $params['method_description'];
        $this->title                = $params['title'];
        $this->description          = $params['description'];
        $this->icon                 = $params['icon'];
        $this->params               = $params;
        $this->has_fields           = false;

        parent::__construct();
    }

}
