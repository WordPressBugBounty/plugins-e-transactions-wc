<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-Transactions - Payment Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class   WC_Etransactions_Abstract_Gateway
 * @extends WC_Payment_Gateway
 */
abstract class WC_Etransactions_Abstract_Gateway extends WC_Payment_Gateway {

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

        $supports = array();
        if ( $this->params['one_click_enabled'] === '1' && ! is_account_page() ) {
            $supports[] = 'tokenization';
        }

        $this->config_class             = new WC_Etransactions_Config();
        $this->payment_request_class    = new WC_Etransactions_Simple_Payment_Request();
        $this->signature_class          = new WC_Etransactions_Signature();

        $this->account_credentials  = $this->config_class->get_account_credentials();
		$this->supports           	= $supports;

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

		$order          = wc_get_order( $order_id );
        $payment_method	= isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : $this->id;
        $is_token		= isset( $_POST[ 'wc-' . strtolower($payment_method) . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . strtolower($payment_method) . '-payment-token' ];
		
		if ( $is_token ) {
			$token_id      = isset( $_POST['wc-etransactions_std_card_cb-payment-token'] ) ? sanitize_text_field( $_POST['wc-etransactions_std_card_cb-payment-token'] ) : '';
			require_once WC_ETRANSACTIONS_PLUGIN_PATH . '/classes/helpers/wc-etransaction-payment-token.php';
			$payment_token = new WC_Etransactions_Payment_Token( $token_id );
			$phone_number  = $payment_token->get_phone_number();
			$phone_country = $payment_token->get_phone_country();
		} else {
			$phone_number  = isset( $_POST['wce_up2pay_phone_number'] ) ? sanitize_text_field( $_POST['wce_up2pay_phone_number'] ) : '';
			$phone_country = isset( $_POST['wce_up2pay_phone_country'] ) ? sanitize_text_field( $_POST['wce_up2pay_phone_country'] ) : '';
		}

		if ( empty( $phone_number ) || empty( $phone_country ) ) {
			wc_add_notice( __( 'Please fill a valid number', 'wc-etransactions' ), 'error' );
			return array( 'result' => 'failure', 'redirect' => wc_get_checkout_url() );
		}

        $one_click  = isset($_POST['wce_one_click']) ? sanitize_text_field($_POST['wce_one_click']) : '0';
        $order->update_meta_data( wc_etransactions_add_prefix('one_click_enabled'), $one_click );

        $order->update_meta_data( wc_etransactions_add_prefix('wce_phone_number'), $phone_number );
        $order->update_meta_data( wc_etransactions_add_prefix('wce_phone_country'), $phone_country );

		if ( $is_token || !empty($this->params['token']) ) {
            if ( !empty($this->params['token']) ) {
				$token_id = $this->params['token'];
			} else {
				$token_id = wc_clean( wp_unslash( $_POST[ 'wc-' . strtolower($payment_method) . '-payment-token' ] ) );
			}

			$order->update_meta_data( wc_etransactions_add_prefix('token_id'), $token_id );
        } else {
            $order->update_meta_data( wc_etransactions_add_prefix('token_id'), null );
        }

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

        $iframe = isset($_GET['iframe']) ? sanitize_text_field($_GET['iframe']) : false;
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        if ( $iframe ) {
            $this->redirect_from_iframe();
            die;
        }

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

        if ($this->params['iframe'] === '1') {

            $url = $this->payment_request_class->get_iframe_form_action();

            ?>
                <iframe id="pbx-seamless-iframe" src="<?php echo esc_url($url) . '?' . http_build_query($params); ?>" style="border: none; width: 100%; height: 590px;"></iframe>
            <?php

        } else {

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
        }

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
     * Redirect from iframe
     */
    private function redirect_from_iframe() {

        $redirect_url   = trailingslashit(site_url('wc-api/' . get_class($this)));
        foreach ( $_GET as $key => $value ) {
            if ($key === 'iframe') {
                continue;
            }
            $redirect_url = add_query_arg( $key, $value, $redirect_url );
        }

        ?>
            <form method="post" id="JS-WCE-form-iframe-redirect" action="<?php echo esc_url( $redirect_url ); ?>" target="_parent"></form>
            <script> document.getElementById('JS-WCE-form-iframe-redirect').submit(); </script>
        <?php
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

        $deferred = false;

        if ( wc_etransactions_get_option( 'payment_debit_type' ) === WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_DEFERRED
            && wc_etransactions_get_option( 'payment_capture_event' ) === WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_STATUS
        ) {

            if ( wc_etransactions_get_option( 'payment_capture_event' ) === WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_STATUS ) {
                $order->update_meta_data('wc-etransactions-status', wc_etransactions_get_option('payment_capture_status'));
            }
            
            $order->set_status( 'wc-e-deferred' );
            $deferred = true;

        } else {

            $order->payment_complete( $params['transaction'] );
        }

        $this->set_order_data( $order, $params );
        $this->set_order_account( $order );
        $this->set_order_transactions( $order, $params, $deferred );
        $this->save_cart_token( $order, $params );

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

        $data = $order->get_meta( 'wc-etransactions-data', true );
        if ( empty($data) ) {
            $order->set_status( 'wc-e-capture' );
            $order->save();
        }

        WC()->cart->empty_cart();
		wp_redirect( $order->get_checkout_order_received_url() );
        exit;
    }

    /**
     * Save a cart as a WC payment token
     */
    private function save_cart_token( $order, $params ) {

        if ( !isset($params['token']) ) {
            return;
        }

        $contract_access = wc_etransactions_get_option('account_contract_access');
        if ( $contract_access !== WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM ) {
            return;
        }

        $user_id = $order->get_customer_id();
		if ( !$user_id ) {
			return;
		}

        $gateway_id         = $params['gateway_id'];
        $existing_tokens	= WC_Payment_Tokens::get_tokens(array(
			'user_id' 		=> $user_id,
			'gateway_id'	=> $gateway_id
		));

        $current_card_type      = $params['cardType'];
        $current_token_data     = explode(' ', $params['token']);
        $current_token          = trim($current_token_data[0]);
        $current_card_date      = $params['validity'];
        $current_expiry_month   = substr($current_card_date, 2, 2);
        $current_expiry_year    = '20' . substr($current_card_date, 0, 2);
        $current_last4          = substr($params['firstNumbers'], 0, 4) . '****' . $params['lastNumbers'];
        
        $token_already_exists = false;
        foreach ( $existing_tokens as $existing_token ) {

            $token_data			= $existing_token->get_data();
            $token_card_type    = $token_data['card_type'] ?? '';
            $token_expiry_month	= $token_data['expiry_month'] ?? '';
            $token_expiry_year	= $token_data['expiry_year'] ?? '';

            if ( $token_card_type == $current_card_type && $token_expiry_month == $current_expiry_month && $token_expiry_year == $current_expiry_year ) {
                $token_already_exists = true;
                break;
            }
        }

        if ( $token_already_exists ) {
            return;
        }

		$phone_number  = $order->get_meta( wc_etransactions_add_prefix('wce_phone_number'), true );
        $phone_country = $order->get_meta( wc_etransactions_add_prefix('wce_phone_country'), true );

		require_once WC_ETRANSACTIONS_PLUGIN_PATH . '/classes/helpers/wc-etransaction-payment-token.php';
		$payment_token = new WC_Etransactions_Payment_Token();
		$payment_token->set_token( $current_token );
		$payment_token->set_gateway_id( $gateway_id );
		$payment_token->set_card_type( $current_card_type );
		$payment_token->set_last4( $current_last4 );
		$payment_token->set_expiry_month( $current_expiry_month );
		$payment_token->set_expiry_year( $current_expiry_year );
		$payment_token->set_user_id( $user_id );
		$payment_token->set_phone_number( $phone_number  );
		$payment_token->set_phone_country( $phone_country );
		$payment_token->save();

        // $payment_token = new WC_Payment_Token_CC();
		// $payment_token->set_token($current_token);
		// $payment_token->set_gateway_id( $gateway_id );
		// $payment_token->set_card_type($current_card_type);
		// $payment_token->set_last4($current_last4);
		// $payment_token->set_expiry_month($current_expiry_month);
		// $payment_token->set_expiry_year($current_expiry_year);
		// $payment_token->set_user_id($user_id);
		// $payment_token->save();
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
    private function set_order_transactions( $order, $params, $deferred ) {

        $transactions = $order->get_meta( 'wc-etransactions-transactions', true );

        if ( !is_array($transactions) ) {
            $transactions = array();
        }

        $transaction    = array();
        $total_paid     = $params['amount'] / 100;
        $guarantee_3ds  = $params['3dsWarranty'] == '0' ? 1 : 0;

        $transaction['id_order']        = $params['order'];
        $transaction['amount']          = $total_paid;
        $transaction['ipn']             = $params['error'];
        $transaction['numappel']        = $params['call'];
        $transaction['guarantee_3ds']   = $guarantee_3ds;
        $transaction['card_type']       = $params['cardType'];

        if ( $deferred ) {
            $transaction['amount_captured'] = 0;
            $transaction['auth_numtrans']   = $params['transaction'];
            $transaction['numtrans']        = null;
            $transaction['captured']        = 0;
        } else {
            $transaction['amount_captured'] = $total_paid;
            $transaction['auth_numtrans']   = null;
            $transaction['numtrans']        = $params['transaction'];
            $transaction['captured']        = 1;
        }

        if ( ! empty( $transaction ) ) {
            $transactions[] = $transaction;
        }

        $order->update_meta_data( 'wc-etransactions-transactions', $transactions );
    }

}
