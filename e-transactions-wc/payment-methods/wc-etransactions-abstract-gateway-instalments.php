<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-Transactions - Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class   WC_Etransactions_Abstract_Gateway_Instalments
 * @extends WC_Payment_Gateway
 */
abstract class WC_Etransactions_Abstract_Gateway_Instalments extends WC_Payment_Gateway {

	public $params;
    protected $account_credentials;
    protected $config_class;
    protected $payment_request_class;
    protected $signature_class;

    /**
     * The class constructor
     */
    public function __construct() {
        global $wp;

        $this->config_class             = new WC_Etransactions_Config();
        $this->payment_request_class    = new WC_Etransactions_Instalment_Payment_Request();
        $this->signature_class          = new WC_Etransactions_Signature();

        $this->account_credentials  = $this->config_class->get_account_credentials();
		$this->supports           	= array();

        $order_id	= isset($wp->query_vars) && is_array($wp->query_vars) && isset($wp->query_vars['order-received']) ? absint($wp->query_vars['order-received']) : 0;
		if ( !empty($order_id) && isset($_GET['key']) && !empty($_GET['key']) ) {

			$order_key	= wp_unslash($_GET['key']);
            $order		= wc_get_order($order_id);

			if ( $order && $order_id === $order->get_id() && hash_equals($order->get_order_key(), $order_key) && $order->needs_payment() && $order->get_payment_method() == $this->id ) {
                remove_action('get_header', 'wc_clear_cart_after_payment');
                remove_action('template_redirect', 'wc_clear_cart_after_payment', 20);
            }
		}

        $this->init_hooks();
    }

	/**
	 * Return the gateway's description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

    /**
     * Process the payment
     */
    public function process_payment( $order_id ) {

		$phone_number  = isset( $_POST['wce_up2pay_phone_number'] ) ? sanitize_text_field( $_POST['wce_up2pay_phone_number'] ) : '';
        $phone_country = isset( $_POST['wce_up2pay_phone_country'] ) ? sanitize_text_field( $_POST['wce_up2pay_phone_country'] ) : '';

        if ( empty( $phone_number ) || empty( $phone_country ) ) {
            wc_add_notice( __( 'Please fill a valid number', 'wc-etransactions' ), 'error' );
            return array( 'result' => 'failure', 'redirect' => wc_get_checkout_url() );
        }

        $order = wc_get_order( $order_id );
		$order->update_meta_data( wc_etransactions_add_prefix('wce_phone_number'), $phone_number );
        $order->update_meta_data( wc_etransactions_add_prefix('wce_phone_country'), $phone_country );
		$order->save();
    
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url( true )
        );
    }

    /**
	 * Init hooks
	 */
	public function init_hooks() {
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        add_action( 'woocommerce_api_' . strtolower(get_class($this)), array( $this, 'api_call' ) );
	}

    /**
     * Build and send the request to get the form data
     */
    public function receipt_page( $order_id ) {

        if ( !$this->config_class->get_envirenment() ) {
            $message = __CLASS__ . ':' . __FUNCTION__ . ": envirenment not exist.";
		    wc_etransactions_add_log( $message );
            return;
        }

        if (!$this->config_class->is_account_configured()) {
            $message = __CLASS__ . ':' . __FUNCTION__ . ": account not configured.";
		    wc_etransactions_add_log( $message );
            return;
        }
        
        $order = wc_get_order( $order_id );

        if ( !in_array( $order->get_currency(), WC_Etransactions_Config::RESTRICTED_CURRENCIES ) ) {
            $message = __CLASS__ . ':' . __FUNCTION__ . ": current currency not accepted.";
		    wc_etransactions_add_log( $message );
            return;
        }

        $this->payment_request_class->set_gateway_class( get_class( $this ) );
        $this->payment_request_class->set_gateway_params( $this->params );
        $this->payment_request_class->set_order( $order );
        $response   = $this->payment_request_class->send_request();
        $params     = $this->payment_request_class->get_params();

        if ( $response ) {

            echo $this->generate_payment_form( $order, $params );

        } else {

            echo $this->generate_error_message();
        }

    }

    /**
     * Handle the callback
     */
    public function api_call() {

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        switch ( $action ) {
            case 'cancel':
                $this->payment_canceled();
            break;
            case 'failed':
                $this->payment_failed();
            break;
            case 'ipn':
                $this->on_ipn();
            break;
            case 'success':
                $this->payment_success();
            break;
        }

        die;
    }

    /**
     * Generate the payment form
     */
    private function generate_payment_form( $order, $params ) {

        ob_start();

            $url = $this->payment_request_class->get_form_action();

            ?>
                <form id="JS-WCE-form" method="post" action="<?php echo esc_url($url); ?>">

                    <p><?php echo __('You will be redirected to the E-Transactions payment page. If not, please use the button bellow.', 'wc-etransactions'); ?></p>

                    <?php foreach ($params as $name => $value) : ?>
                        <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
                    <?php endforeach; ?>

                    <center><button type="submit"><?php echo __('Continue...', 'wc-etransactions'); ?></button></center>
                </form>
                <script type="text/javascript">
                    window.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('JS-WCE-form').submit();
                    });
                </script>
            <?php

        return ob_get_clean();
    }

    /**
     * Generate the error message
     */
    private function generate_error_message() {

        ob_start();
        ?>
            <form action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="get">
                <center><button type="submit"><?php _e('Back...', 'wc-etransactions'); ?></button></center>
            </form>
        <?php

        return ob_get_clean();
    }

    /**
     * Handle the payment canceled
     */
    private function payment_canceled() {

        $order_id   = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 0;
        $message    = __CLASS__ . ':' . __FUNCTION__ . ": payment canceled for order(" . $order_id . ")";
        wc_etransactions_add_log($message);

        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }

    /**
     * Handle the payment failed
     */
    private function payment_failed() {

        $order_id   = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 0;
        $message    = __CLASS__ . ':' . __FUNCTION__ . ": payment failed for order(" . $order_id . ")";
        wc_etransactions_add_log($message);

        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }

    /**
     * Handle the ipn
     */
    private function on_ipn() {

        $order_id = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 0;
        $order    = wc_get_order( $order_id );

        if ( !is_object($order) ) {

            $message = __CLASS__ . ':' . __FUNCTION__ . ": order(" . $order_id . ") not exist.";
            wc_etransactions_add_log( $message );
            exit;
        }

        $values = $_GET;
        if (isset($values['action'])) {
            unset($values['action']);
        }
        if (isset($values['order'])) {
            unset($values['order']);
        }
        if (isset($values['gateway_id'])) {
            unset($values['gateway_id']);
        }
        $passed = $this->signature_class->verify_signature( $values, true );
        if ( !$passed ) {

            $message = __CLASS__ . ':' . __FUNCTION__ . ": signature not match for order(" . $order_id . ")";
            wc_etransactions_add_log( $message );
            exit;
        }

        $params = $this->config_class->get_params($_GET);
        if ( empty($params) ) {

            $message = __CLASS__ . ':' . __FUNCTION__ . ": empty params for order(" . $order_id . ")";
            wc_etransactions_add_log( $message );
            exit;
        }

        if ( $params['error'] !== '00000' ) {

            $message = __CLASS__ . ':' . __FUNCTION__ . ": payment failed for order(" . $order_id . "), error code: " . $params['error'];
            wc_etransactions_add_log( $message );
            exit;
        }

        $order->payment_complete( $params['transaction'] );

        $this->set_order_data( $order, $params );
        $this->set_order_account( $order );
        $this->set_order_transactions( $order, $params );
        $this->set_order_deadlines( $order, $params );

        $order->save();
        exit;
    }

    /**
     * Handle the payment success
     */
    private function payment_success() {

        $order_id = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 0;
        $order    = wc_get_order( $order_id );

        if ( !is_object($order) ) {

			wc_add_notice( __( 'Payment error: Please try again', 'wc-etransactions' ), 'error' );

            $message = __CLASS__ . ':' . __FUNCTION__ . ": order(" . $order_id . ") not exist.";
            wc_etransactions_add_log( $message );

            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $passed = $this->signature_class->verify_signature( $_GET, true );
        if ( !$passed ) {
                
            wc_add_notice( __( 'Payment error: Please try again', 'wc-etransactions' ), 'error' );

            $message = __CLASS__ . ':' . __FUNCTION__ . ": signature not match for order(" . $order_id . ")";
            wc_etransactions_add_log( $message );

            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        $params = $this->config_class->get_params($_GET);
        if ( empty($params) ) {
                
            wc_add_notice( __( 'Payment error: Please try again', 'wc-etransactions' ), 'error' );

            $message = __CLASS__ . ':' . __FUNCTION__ . ": empty params for order(" . $order_id . ")";
            wc_etransactions_add_log( $message );

            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        if ( $params['error'] !== '00000' ) {

            wc_add_notice( __( 'Payment error: Please try again', 'wc-etransactions' ), 'error' );

            $message = __CLASS__ . ':' . __FUNCTION__ . ": payment failed for order(" . $order_id . "), error code: " . $params['e'];
            wc_etransactions_add_log( $message );

            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }

        WC()->cart->empty_cart();
		wp_redirect( $order->get_checkout_order_received_url() );
        exit;
    }

    /**
     * Set the data to order
     */
    private function set_order_data( $order, $params ) {

        $order_data = $order->get_meta( 'wc-etransactions-data', true );

        if ( !is_array($order_data) ) {
            $order_data = array();
        }

        $order_data[] = $params;
        
        $order->update_meta_data( 'wc-etransactions-data', $order_data );
    }

    /**
     * Set the account to order
     */
    private function set_order_account( $order ) {

        $account = array(
            'account_environment'       => $this->config_class->get_envirenment(),
            'account_contract_access'   => wc_etransactions_get_option('account_contract_access'),
        );

        $order->update_meta_data( 'wc-etransactions-account', $account );
    }

    /**
     * Set the transactions to order
     */
    private function set_order_transactions( $order, $params ) {

        $transactions = $order->get_meta( 'wc-etransactions-transactions', true );

        if ( !is_array($transactions) ) {
            $transactions = array();
        }

        $transaction    = array();
        $total_paid     = $params['amount'] / 100;

        $transaction['id_order']        = $params['order'];
        $transaction['amount']          = $total_paid;
        $transaction['numappel']        = $params['call'];
        $transaction['guarantee_3ds']   = 0;
        $transaction['card_type']       = $params['cardType'];
        $transaction['ipn']             = '00000';
        $transaction['amount_captured'] = $total_paid;
        $transaction['auth_numtrans']   = null;
        $transaction['numtrans']        = $params['transaction'];
        $transaction['captured']        = 1;

        if ( ! empty( $transaction ) ) {
            $transactions[] = $transaction;
        }

        $order->update_meta_data( 'wc-etransactions-transactions', $transactions );
    }

    /**
     * Set the deadlines to order
     */
    private function set_order_deadlines( $order, $params ) {

        $deadlines = $order->get_meta( 'wc-etransactions-deadlines', true );

        if ( !is_array($deadlines) ) {
            $deadlines = array();
        }

        $instalments            = wc_etransactions_get_instalments();
        $instalment_settings    = wc_etransactions_get_option('instalment_settings');
        $instalment_key         = $params['partial'] - 2;
        $default_data           = $instalments[$instalment_key] ?? array();
        $instalment_data_in_db  = $instalment_settings[$instalment_key] ?? array();
        $partial_payments       = $default_data['partialPayments'];
        $percents               = isset($instalment_data_in_db['percents']) ? $instalment_data_in_db['percents'] : $default_data['percents'];
        $days_between_payments  = isset($instalment_data_in_db['daysBetweenPayments']) ? $instalment_data_in_db['daysBetweenPayments'] : $default_data['daysBetweenPayments'];

        if ( empty( $deadlines ) ) {

            for ( $i = 0; $i < $partial_payments; $i++ ) {
                $amount         = round($order->get_total() * ($percents[$i] / 100), 2);
                $deadlines[]    = array(
                    'id_order'          => $order->get_id(),
                    'amount'            => $amount,
                    'captured'          => $i == 0 ? 1 : 0,
                    'canceled'          => 0,
                    'date_intended'     => $i == 0 ? wp_date('d-m-y') : wp_date( 'd-m-y', strtotime('+' . ($days_between_payments * $i) . ' day' ) ),
                    'date_execution'    => $i == 0 ? wp_date('Y-m-d H:i:s') : '',
                    'transaction'       => $params['transaction'],
                );
            }

        } else {

            if ( $params['error'] == '00000' ) {
                
                $params_amount = $params['amount'] / 100;
                foreach ( $deadlines as &$deadline ) {

                    if ( $params_amount == $deadline['amount'] && $deadline['captured'] == 0 ) {
                        $deadline['captured']       = 1;
                        $deadline['date_execution'] = wp_date('Y-m-d H:i:s');
                        break;
                    }
                }

            } else {

                foreach ( $deadlines as &$deadline ) {
                    $deadline['canceled']       = 1;
                    $deadline['date_execution'] = wp_date('Y-m-d H:i:s');
                }
            }
        }
        
        $order->update_meta_data( 'wc-etransactions-deadlines', $deadlines );
    }

}
