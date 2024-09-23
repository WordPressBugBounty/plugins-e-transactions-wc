<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * class responsible for instalment configuration
 */
class WC_Etransactions_Order {

    public function __construct() {

		add_filter('wc_order_statuses', array($this, 'register_order_status'));
		add_filter('woocommerce_register_shop_order_post_statuses', array( $this, 'register_order_post_status'));
        add_filter('woocommerce_analytics_excluded_order_statuses', array( $this, 'append_draft_order_post_status'));
		add_filter('woocommerce_valid_order_statuses_for_payment', array( $this, 'append_draft_order_post_status'));
		add_filter('woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'append_draft_order_post_status'));
		add_action('woocommerce_order_status_changed', array($this, 'status_changed'), 10, 3);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action( 'admin_init', array( $this, 'submit_forms' ) );
    }

    /**
     * Register custom order statuses
     */
    public function register_order_status( array $statuses ) {

		$statuses['wc-e-capture']           = __( 'Capture', 'wc-etransactions' );
		$statuses['wc-e-deferred']          = __( 'Deferred payment', 'wc-etransactions' );
		$statuses['wc-e-partial-refund']    = __( 'Partially refunded', 'wc-etransactions' );

		return $statuses;
	}

    /**
     * Register custom order statuses
     */
    public function register_order_post_status( array $statuses ) {

		$statuses['wc-e-capture'] = array(
			'label'                     => __( 'Capture', 'wc-etransactions' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Capture <span class="count">(%s)</span>', 'Capture <span class="count">(%s)</span>' )
		);
		$statuses['wc-e-deferred'] = array(
			'label'                     => __( 'Deferred payment', 'wc-etransactions' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Deferred payment <span class="count">(%s)</span>', 'Deferred payment <span class="count">(%s)</span>' )
		);
		$statuses['wc-e-partial-refund'] = array(
			'label'                     => __( 'Partially refunded', 'wc-etransactions' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'Partially refunded <span class="count">(%s)</span>', 'Partially refunded <span class="count">(%s)</span>' )
		);

		return $statuses;
	}

    /**
     * Append order status to a list of statuses.
     */
    public function append_draft_order_post_status( $statuses ) {

        $statuses[] = 'e-capture';
        $statuses[] = 'e-deferred';
        $statuses[] = 'e-partial-refund';

        return $statuses;
    }

    /**
	 * Validate the payment if the order has the right status
	 */
	public function status_changed( $order_id, $old_status, $new_status ) {

        $order = wc_get_order( $order_id );
		if ( !$order ) {
            return;
		}

        $payment_method = $order->get_payment_method();
		if ( strpos( $payment_method, 'etransactions' ) === false ) {
            return;
		}

        $already_validate = $order->get_meta( 'wc-etransactions-already-validate', true );
		if ( $already_validate === '1' ) {
            return;
		}

        $capture_status_ids = $order->get_meta( 'wc-etransactions-status', true );
		if ( !is_array($capture_status_ids) || !in_array( 'wc-' . $new_status, $capture_status_ids ) ) {
            return;
		}

        $capture_class = new WC_Etransactions_Capture_Request();
        $capture_class->set_order( $order );
        $response = $capture_class->send_request();
        
        if ( $response ) {

            parse_str( $response, $response_array );
            $params = $capture_class->get_params();

            $response_code = $response_array['CODEREPONSE'] ?? '';
            $operations = $order->get_meta( 'wc-etransactions-operations', true );

            if ( !is_array($operations) ) { $operations = array(); }
            $operations[] = array(
                'id_order'  => $order_id,
                'type'      => 'capture',
                'amount'    => $params['MONTANT'] / 100,
                'date'      => date('Y-m-d H:i:s'),
                'result'    => $response_code,
                'success'   => $response_code === '00000' ? 'success' : 'error',
                'numTrans'  => $response_array['NUMTRANS'] ?? '',
            );

            $order->update_meta_data( 'wc-etransactions-operations', $operations );
            $order->update_meta_data( 'wc-etransactions-already-validate', '1' );
            $order->save();
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {

        $screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' )
        && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

        add_meta_box(
            'wc-etransactions-payment-info',
            __( 'Up2pay e-Transactions Crédit Agricole', 'wc-etransactions' ),
            array($this, 'render_meta_box_payment_info'),
            $screen,
            'normal',
            'high'
        );
    }

    /**
     * Submit forms
     */
    public function submit_forms() {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $submit = isset($_POST['wc-etransactions-order-submit']) ? sanitize_text_field($_POST['wc-etransactions-order-submit']) : '';

        switch ($submit) {
            case 'capture':
                $this->capture_funds();
            break;
            case 'refund':
                $this->refund_funds();
            break;
        }
    }

    /**
     * Render meta box payment info
     */
    public function render_meta_box_payment_info( $post_or_order_object ) {

        $order          = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
        $payment_method = $order->get_payment_method();

        if ( strpos( $payment_method, 'etransactions' ) !== false ) {

            $order_data = $order->get_meta('wc-etransactions-data', true);
            
            if ( !is_array($order_data) ) {
                return;
            }
            
            $order_account          = $order->get_meta('wc-etransactions-account', true);
            $operations             = $order->get_meta('wc-etransactions-operations', true);
            $deadlines              = $order->get_meta('wc-etransactions-deadlines', true);
            $transactions           = $order->get_meta('wc-etransactions-transactions', true);
            $order_refunded_amount  = $this->get_refunded_amount( $operations );
            $x3captured_amount      = $this->get_captured_amount( $deadlines );
            $is_instalement         = empty($deadlines) ? false : true;
            $is_contract_access     = isset($order_account['account_contract_access']) && $order_account['account_contract_access'] == WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS;
            
            $admin_single_order_assets = include( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/build/admin-single-order.asset.php' );
            wp_enqueue_style( 'wc_etransactions_admin_single_order', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/admin-single-order.css', array(), $admin_single_order_assets['version'], 'all' );
            wp_enqueue_script( 'wc_etransactions_admin_single_order', WC_ETRANSACTIONS_PLUGIN_URL . 'assets/build/admin-single-order.js', $admin_single_order_assets['dependencies'], $admin_single_order_assets['version'], true );

            include( WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/order/payment-info.php' );
        }
    }

    /**
     * Get refunded amount
     */
    private function get_refunded_amount( $operations ) {

        $refunded_amount = 0;

        if ( is_array( $operations ) ) {
            foreach ( $operations as $operation) {
                if ( $operation['type'] == 'refund' && $operation['success'] == 'success' ) {
                    $refunded_amount += $operation['amount'];
                }
            }
        }

        return $refunded_amount;
    }

    /**
     * Get captured amount
     */
    private function get_captured_amount( $deadlines ) {

        $captured_amount = 0;

        if ( is_array( $deadlines ) ) {
            foreach ( $deadlines as $deadline ) {
                if ( $deadline['captured'] == '1' ) {
                    $captured_amount += $deadline['amount'];
                }
            }
        }

        return $captured_amount;
    }

    /**
     * Capture funds
     */
    private function capture_funds() {

        $nonce = isset($_POST['wc-etransactions-order-action-nonce']) ? sanitize_text_field($_POST['wc-etransactions-order-action-nonce']) : '';

        if ( !wp_verify_nonce( $nonce, 'wc-etransactions-order-action' ) ) {
            return;
        }

        $capture_data = isset($_POST['wc-etransactions-capture']) ? wc_clean( $_POST['wc-etransactions-capture'] ) : array();

        if ( empty($capture_data) ) {
            return;
        }

        $order_id           = isset( $capture_data['id_order'] ) ? sanitize_text_field( $capture_data['id_order']) : '';
        $amount_to_capture  = isset( $capture_data['amount_to_capture'] ) ? sanitize_text_field( $capture_data['amount_to_capture'] ) : '';
        $numappel           = isset( $capture_data['numappel'] ) ? sanitize_text_field( $capture_data['numappel'] ) : '';

        if ( empty($order_id) || empty($amount_to_capture) || empty($numappel) ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $capture_class = new WC_Etransactions_Capture_Request();
        $capture_class->set_order( $order );
        $response = $capture_class->send_request( $amount_to_capture * 100 );

        if ( $response ) {

            parse_str( $response, $response_array );
            $params = $capture_class->get_params();

            $response_code = $response_array['CODEREPONSE'] ?? '';
            $operations = $order->get_meta( 'wc-etransactions-operations', true );

            if ( !is_array($operations) ) { $operations = array(); }
            $operations[] = array(
                'id_order'  => $order_id,
                'type'      => 'capture',
                'amount'    => $params['MONTANT'] / 100,
                'date'      => date('Y-m-d H:i:s'),
                'result'    => $response_code,
                'success'   => $response_code === '00000' ? 'success' : 'error',
                'numTrans'  => $response_array['NUMTRANS'] ?? '',
            );

            $order->update_meta_data( 'wc-etransactions-operations', $operations );

            if ( $response_code === '00000' ) {

                $transactions = $order->get_meta('wc-etransactions-transactions', true);

                if ( ! is_array( $transactions ) ) {
                    $transactions = array();
                }

                foreach ( $transactions as $key => $transaction ) {
                    if ( $transaction['numappel'] === $numappel ) {
                        $transactions[$key]['captured'] = '1';
                        $transactions[$key]['amount_captured'] = $params['MONTANT'] / 100;
                        $transactions[$key]['numtrans'] = $transaction['auth_numtrans'];
                    }
                }

                $order->update_meta_data( 'wc-etransactions-transactions', $transactions );
				$order->set_status( apply_filters( 'woocommerce_payment_complete_order_status', $order->needs_processing() ? 'processing' : 'completed', $order->get_id(), $order ) );
            }

            $order->save();
        }
    }

    /**
     * Refund funds
     */
    private function refund_funds() {

        $nonce = isset($_POST['wc-etransactions-order-action-nonce']) ? sanitize_text_field($_POST['wc-etransactions-order-action-nonce']) : '';

        if ( !wp_verify_nonce( $nonce, 'wc-etransactions-order-action' ) ) {
            return;
        }

        $refund_data = isset($_POST['wc-etransactions-refund']) ? wc_clean( $_POST['wc-etransactions-refund'] ) : array();

        if ( empty($refund_data) ) {
            return;
        }

        $order_id           = $refund_data['id_order'] ?? '';
        $amount_to_refund   = $refund_data['amount_to_refund'] ?? '';

        if ( empty($order_id) || empty($amount_to_refund) ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $refund_class = new WC_Etransactions_Refund_Request();
        $refund_class->set_order( $order );
        $response = $refund_class->send_request( $amount_to_refund * 100 );

        if ( $response ) {

            parse_str( $response, $response_array );
            $params = $refund_class->get_params();

            $response_code = $response_array['CODEREPONSE'] ?? '';
            $operations = $order->get_meta( 'wc-etransactions-operations', true );

            if ( !is_array($operations) ) { $operations = array(); }
            $operations[] = array(
                'id_order'  => $order_id,
                'type'      => 'refund',
                'amount'    => $params['MONTANT'] / 100,
                'date'      => date('Y-m-d H:i:s'),
                'result'    => $response_code,
                'success'   => $response_code === '00000' ? 'success' : 'error',
                'numTrans'  => $response_array['NUMTRANS'] ?? '',
            );

            if ( $response_code === '00000' ) {

                wc_create_refund( array(
                    'amount'    => $amount_to_refund,
                    'reason'    => 'Refund via e-Transactions',
                    'order_id'  => $order_id,
                ));

                if ( $amount_to_refund < $order->get_total() ) {
                    $order->set_status( 'e-partial-refund' );
                } else {
                    $order->set_status( 'refunded' );
                }
            }

            $order->update_meta_data( 'wc-etransactions-operations', $operations );
            $order->save();
        }
    }

}