<?php

// Ensure not called directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fix pickup point addresses after payment processing
 * Ensures pickup point addresses are correctly applied after payment without interfering with payment logic
 */
class WC_Etransactions_Pickup_Fix
{

    public function __construct()
    {
        // Hook after successful payment to ensure pickup point address is correct
        add_action('woocommerce_payment_complete', array($this, 'fix_pickup_address_after_payment'), 10, 1);
        add_action('woocommerce_order_status_processing', array($this, 'fix_pickup_address_after_payment'), 10, 1);
    }

    /**
     * Fix shipping address after payment for pickup points
     *
     * @param int $order_id Order ID
     */
    public function fix_pickup_address_after_payment($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Check if it's an e-transactions order
        $payment_method = $order->get_payment_method();
        if (strpos($payment_method, 'etransactions') === false) {
            return;
        }

        // Check if it's a pickup point and fix address if necessary
        $this->fix_mondial_relay_address($order);
        $this->fix_chronopost_address($order);
        $this->fix_chronopost_plugin_address($order);
        $this->fix_gls_address($order);
    }

    /**
     * Fix address for Mondial Relay
     */
    private function fix_mondial_relay_address($order)
    {
        // Check if it's a Mondial Relay method
        $shipping_methods = $order->get_shipping_methods();
        $is_mondial_relay = false;

        foreach ($shipping_methods as $shipping_method) {
            $method_id = strtolower($shipping_method->get_method_id());
            if (strpos($method_id, 'mondial_relay') !== false) {
                $is_mondial_relay = true;
                break;
            }
        }

        if (!$is_mondial_relay) {
            return;
        }

        // Get pickup point information from metadata
        $pickup_info = $order->get_meta('_wms_mondial_relay_pickup_info', true);

        if (empty($pickup_info)) {
            return;
        }

        // Apply pickup point address
        $this->apply_pickup_address_to_order($order, $pickup_info);
    }

    /**
     * Fix address for Chronopost
     */
    private function fix_chronopost_address($order)
    {
        // Check if it's a Chronopost method
        $shipping_methods = $order->get_shipping_methods();
        $is_chronopost = false;

        foreach ($shipping_methods as $shipping_method) {
            $method_id = strtolower($shipping_method->get_method_id());
            if (strpos($method_id, 'chronopost') !== false) {
                $is_chronopost = true;
                break;
            }
        }

        if (!$is_chronopost) {
            return;
        }

        // Get pickup point information from metadata
        $pickup_info = $order->get_meta('_wms_chronopost_pickup_info', true);

        if (empty($pickup_info)) {
            return;
        }

        // Apply pickup point address
        $this->apply_pickup_address_to_order($order, $pickup_info);
    }

    /**
     * Fix address for Chronopost (Standalone Plugin)
     */
    private function fix_chronopost_plugin_address($order)
    {
        // Check if it's a Chronopost standalone plugin method (chronorelais, chronorelaiseurope, chronorelaisdom, etc.)
        $shipping_methods = $order->get_shipping_methods();
        $is_chronopost_plugin = false;

        foreach ($shipping_methods as $shipping_method) {
            $method_id = strtolower($shipping_method->get_method_id());
            // Check for standalone Chronopost plugin methods (chronorelais, chronorelaiseurope, chronorelaisdom, chronotoshopdirect, chronotoshopeurope)
            if ($method_id === 'chronorelais' ||
                $method_id === 'chronorelaiseurope' ||
                $method_id === 'chronorelaisdom' ||
                $method_id === 'chronotoshopdirect' ||
                $method_id === 'chronotoshopeurope') {
                $is_chronopost_plugin = true;
                break;
            }
        }

        if (!$is_chronopost_plugin) {
            return;
        }

        // Get pickup point information from metadata (Chronopost standalone plugin format)
        $pickup_info = $order->get_meta('_shipping_method_chronorelais', true);

        if (empty($pickup_info)) {
            return;
        }

        // Apply pickup point address
        $this->apply_pickup_address_to_order($order, $pickup_info);
    }

    /**
     * Fix address for GLS
     */
    private function fix_gls_address($order)
    {
        // Check if it's a GLS method
        $shipping_methods = $order->get_shipping_methods();
        $is_gls = false;

        foreach ($shipping_methods as $shipping_method) {
            $method_id = strtolower($shipping_method->get_method_id());
            if (strpos($method_id, 'gls_relais') !== false) {
                $is_gls = true;
                break;
            }
        }

        if (!$is_gls) {
            return;
        }

        // For GLS, check specific metadata
        $gls_pickup_info = $order->get_meta('_gls_pickup_info', true);

        if (empty($gls_pickup_info)) {
            return;
        }

        // Apply pickup point address
        $this->apply_pickup_address_to_order($order, $gls_pickup_info);
    }

    /**
     * Apply pickup point address to order
     */
    private function apply_pickup_address_to_order($order, $pickup_info)
    {
        if (empty($pickup_info) || !is_array($pickup_info)) {
            return;
        }

        // Map fields according to expected format (different formats possible)
        $pickup_name = $pickup_info['pickup_name'] ?? $pickup_info['name'] ?? $pickup_info['LgAdr1'] ?? '';
        $pickup_address = $pickup_info['pickup_address'] ?? $pickup_info['address'] ?? $pickup_info['LgAdr3'] ?? '';
        $pickup_city = $pickup_info['pickup_city'] ?? $pickup_info['city'] ?? $pickup_info['Ville'] ?? '';
        $pickup_zipcode = $pickup_info['pickup_zipcode'] ?? $pickup_info['zip_code'] ?? $pickup_info['CP'] ?? '';
        $pickup_country = $pickup_info['pickup_country'] ?? $pickup_info['country'] ?? $pickup_info['Pays'] ?? 'FR';

        // Clean data
        $pickup_name = trim($pickup_name);
        $pickup_address = trim($pickup_address);
        $pickup_city = trim($pickup_city);
        $pickup_zipcode = trim($pickup_zipcode);
        $pickup_country = trim($pickup_country);

        // Check that we have at least essential information
        if (empty($pickup_name) || empty($pickup_address) || empty($pickup_city)) {
            return;
        }

        // Check if current address is already the pickup point address
        $current_address = $order->get_address('shipping');

        if ($current_address['company'] === $pickup_name &&
            $current_address['address_1'] === $pickup_address &&
            $current_address['city'] === $pickup_city &&
            $current_address['postcode'] === $pickup_zipcode) {
            // Address is already correct, no need to modify
            return;
        }

        // Apply pickup point address
        $order->set_shipping_company($pickup_name);
        $order->set_shipping_address_1($pickup_address);
        $order->set_shipping_address_2('');
        $order->set_shipping_city($pickup_city);
        $order->set_shipping_postcode($pickup_zipcode);
        $order->set_shipping_country($pickup_country);


        $order->save();
    }
}