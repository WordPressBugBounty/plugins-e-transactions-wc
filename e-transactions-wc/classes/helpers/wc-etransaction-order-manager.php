<?php

// Ensure not called directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optimized version of WC_Etransaction_Order_Manager
 *
 * Features:
 * - Smart caching system with 1 hour expiration
 * - Automatic cache invalidation on order updates
 * - Maintains same logic as original
 */
class WC_Etransaction_Order_Manager
{
    private $payment_method = 'etransactions_';
    private $order_status;
    private $per_page = 20;
    private $cache_group = 'wc_etransactions_orders';
    private $cache_expiry = HOUR_IN_SECONDS;

    private function get_cache_key($method, $params = [])
    {
        $params['payment_method'] = $this->payment_method;
        $params['status'] = $this->order_status;
        $params['per_page'] = $this->per_page;
        ksort($params);
        return $this->cache_group . '_' . $method . '_' . md5(serialize($params));
    }

    /**
     * Get cache value using wp_cache with transient fallback
     */
    private function get_cache($method, $params = [])
    {
        $cache_key = $this->get_cache_key($method, $params);

        $cached = wp_cache_get($cache_key, $this->cache_group);
        if ($cached !== false) {
            return $cached;
        }

        $transient_key = 'wc_etrans_' . $cache_key;
        $cached = get_transient($transient_key);
        if ($cached !== false) {
            wp_cache_set($cache_key, $cached, $this->cache_group, $this->cache_expiry);
            return $cached;
        }

        return false;
    }

    /**
     * Set cache value using wp_cache and transient as backup
     */
    private function set_cache($method, $params, $value)
    {
        $cache_key = $this->get_cache_key($method, $params);
        wp_cache_set($cache_key, $value, $this->cache_group, $this->cache_expiry);
        $transient_key = 'wc_etrans_' . $cache_key;
        set_transient($transient_key, $value, $this->cache_expiry);
    }

    private function get_cache_version()
    {
        $version = get_transient('wc_etransactions_cache_version');
        return $version !== false ? $version : 1;
    }

    /**
     * Get orders with corrected pagination
     * Uses get_orders_total() to get all filtered IDs, then paginates on that list
     */
    public function get_orders()
    {
        $current_page = $this->get_current_page();

        $cache_params = [
            'page' => $current_page,
            'version' => $this->get_cache_version()
        ];

        $cached = $this->get_cache('get_orders', $cache_params);
        if ($cached !== false) {
            return $cached;
        }

        $total_cache_params = [
            'version' => $this->get_cache_version()
        ];
        $all_ids = $this->get_cache('get_orders_total', $total_cache_params);

        if ($all_ids === false) {
            $all_ids = $this->get_orders_total();
        }

        if (empty($all_ids)) {
            $this->set_cache('get_orders', $cache_params, []);
            return [];
        }

        $offset = ($current_page - 1) * $this->per_page;
        $page_ids = array_slice($all_ids, $offset, $this->per_page);

        if (empty($page_ids)) {
            $this->set_cache('get_orders', $cache_params, []);
            return [];
        }

        $all_orders = [];
        foreach ($page_ids as $id) {
            $order = wc_get_order($id);
            if ($order) {
                $all_orders[] = $order;
            }
        }

        gc_collect_cycles();
        $this->set_cache('get_orders', $cache_params, $all_orders);

        return $all_orders;
    }

    public function get_orders_refund()
    {
        $cache_params = [
            'version' => $this->get_cache_version()
        ];

        $cached = $this->get_cache('get_orders_refund', $cache_params);
        if ($cached !== false) {
            return $cached;
        }

        $arg = [
            'limit' => -1,
            'type' => 'shop_order_refund',
            'order' => 'DESC'
        ];
        if (!empty($this->order_status)) {
            $arg['status'] = $this->order_status;
        }
        $refunds = wc_get_orders($arg);

        $refund_ids = [];
        if (empty($refunds)) {
            $this->set_cache('get_orders_refund', $cache_params, $refund_ids);
            return $refund_ids;
        }

        foreach ($refunds as $refund) {
            $parent_id = $refund->get_parent_id();
            if ($parent_id) {
                $parent_order = wc_get_order($parent_id);
                if ($parent_order && strpos($parent_order->get_payment_method(), $this->payment_method) !== false) {
                    $refund_ids[] = $parent_id;
                }
            }
        }

        $this->set_cache('get_orders_refund', $cache_params, $refund_ids);
        return $refund_ids;
    }

    public function get_orders_total()
    {
        $cache_params = [
            'version' => $this->get_cache_version()
        ];

        $cached = $this->get_cache('get_orders_total', $cache_params);
        if ($cached !== false) {
            return $cached;
        }

        $arg = [
            'orderby' => 'date',
            'order' => 'DESC',
            'limit' => '-1',
            'return' => 'ids',
            'type' => 'shop_order',
        ];

        if (!empty($this->order_status)) {
            $arg['status'] = $this->order_status;
        }

        $order_ids = wc_get_orders($arg);

        $order_list_ids = [];
        if (!empty($order_ids)) {
            foreach ($order_ids as $id) {
                $order = wc_get_order($id);
                if ($order && strpos($order->get_payment_method(), $this->payment_method) !== false) {
                    $order_list_ids[] = $id;
                }
            }
        }

        gc_collect_cycles();
        $refund_ids = $this->get_orders_refund();
        $all_ids = array_unique(array_merge($order_list_ids, $refund_ids));
        rsort($all_ids);

        $this->set_cache('get_orders_total', $cache_params, $all_ids);
        return $all_ids;
    }

    public function get_orders_display(array $all_ids)
    {
        $orders_to_display = [];
        foreach ($all_ids as $id) {
            $orders_to_display[] = wc_get_order($id);
        }
        return $orders_to_display;
    }

    public function display_orders_table($orders)
    {
        echo '<table class="wp-list-table widefat fixed striped orders">';
        echo wp_kses_post($this->get_table_headers());
        echo '<tbody>';

        foreach ($orders as $order) {
            $this->display_order_row($order);
        }

        echo '</tbody>';
        echo '</table>';

        echo '<div class="tablenav bottom">';
        echo '<div class="tablenav-pages">';
        $this->display_pagination();
        echo '</div>';
        echo '</div>';
    }

    private function get_table_headers()
    {
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'ID';
        $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';

        return '<thead>
        <tr>
        <th scope="col" class="manage-column column-order-number">' . esc_html__('Order', 'wc-etransactions') . ' </th>
        <th scope="col" class="manage-column column-transaction">' . esc_html__('Transaction', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-status">' . esc_html__('Status', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-payment">' . esc_html__('Payment method', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-amount">' . esc_html__('Amount', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-ipn">' . esc_html__('IPN', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-date">' . esc_html__('Date', 'wc-etransactions') . '</th>
       </tr>
       </thead>';
    }

    private function display_order_row($order_data)
    {
        $operations = $order_data->get_meta('wc-etransactions-operations', true);
        $transactions = $order_data->get_meta('wc-etransactions-transactions', true);
        $transaction_number = null;
        $transaction_date = $order_data->get_date_created();
        $ipn = null;
        if (!empty($operations)) {
            $last_operation = end($operations);
            $ipn = $last_operation['result'];
            $transaction_number = $last_operation['numTrans'];
            $transaction_date = $last_operation['date'];
        }
        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                if (empty($transaction_number)) {
                    $transaction_number = $transaction['captured'] ? $transaction['numtrans'] : $transaction['auth_numtrans'];
                }
                if (empty($ipn)) {
                    $ipn = $transaction['ipn'];
                }
            }
        }

        $client_name = esc_html($order_data->get_billing_first_name() . ' ' . $order_data->get_billing_last_name());

        echo '<tr class="order-' . esc_attr($order_data->get_id()) . ' type-shop_order  status-' . esc_attr($order_data->get_status()) . '">';
        echo '<td class="column-order-number"><strong><a href="' . esc_url(admin_url('post.php?post=' . $order_data->get_id() . '&action=edit')) . '" target="_blank">' . esc_html($order_data->get_order_number()) . ' -' . $client_name . ' </a></strong></td>';
        if (!empty($transaction_number)) {
            echo '<td class="column-transaction">' . esc_html($transaction_number) . '</td>';
        } else {
            echo '<td class="column-transaction">' . esc_html__('Transactions not found', 'wc-etransactions') . '</td>';
        }
        echo '<td class="order_status column-order_status">';
        echo '<mark class="order-status status-' . esc_attr($order_data->get_status()) . ' tips"> <span>' . esc_html(wc_get_order_status_name($order_data->get_status())) . '</span></mark>';
        echo '</td>';
        echo '<td class="column-payment">' . esc_html($order_data->get_payment_method_title()) . '</td>';
        echo '<td class="column-amount">' . wc_price($order_data->get_total()) . '</td>';
        if (!empty($ipn)) {
            echo '<td class="column-ipn">' . esc_html($ipn) . '</td>';
        } else {
            echo '<td class="column-ipn">' . esc_html__('--', 'wc-etransactions') . '</td>';
        }
        echo '<td class="column-date"><time datetime="' . esc_attr($transaction_date) . '">' . esc_html(date_i18n('Y-m-d H:i', strtotime($transaction_date))) . '</time></td>';
        echo '</tr>';
    }

    public function handle_filters()
    {
        if (isset($_GET['order_status'])) {
            $this->order_status = sanitize_text_field($_GET['order_status']);
        } else {
            $this->order_status = null;
        }
    }

    public function render_filters()
    {
        $statuses = wc_get_order_statuses();
        $selected_status = isset($_GET['order_status']) ? sanitize_text_field($_GET['order_status']) : '';

        echo '<form method="GET" action="" class="search-form">';
        echo '<input type="hidden" name="page" value="credit-agricole-transactions">';
        echo '<div class="alignleft actions">';
        echo '<select name="order_status" id="order_status">';
        echo '<option value="">' . esc_html__('All status', 'wc-etransactions') . '</option>';

        foreach ($statuses as $slug => $name) {
            $selected = selected($selected_status, $slug, false);
            echo '<option value="' . esc_attr($slug) . '" ' . $selected . '>' . esc_html($name) . '</option>';
        }

        echo '</select>';
        echo '<input type="submit" class="button action" value="' . esc_html__('Filter', 'wc-etransactions') . '">';
        echo '</div>';
        echo '<div class="alignleft actions"><a href="' . esc_url(remove_query_arg(['order_status', 'paged'])) . '" class="button action">' . esc_html__('Reset filter', 'wc-etransactions') . '</a></div>';
        echo '</form>';
    }

    public function get_filtered_orders($total = false)
    {
        $orders = null;
        if ($total) {
            $ids_orders = $this->get_orders_total();
            $orders = $this->get_orders_display($ids_orders);
        } else {
            $orders = $this->get_orders();
        }
        return $orders;
    }

    /**
     * Use cached get_orders_total() instead of loading all orders
     */
    private function display_pagination()
    {
        $all_ids = $this->get_orders_total();
        $total_count = count($all_ids);
        $total_pages = ceil($total_count / $this->per_page);

        if ($total_pages > 1) {
            $current_page = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;

            echo '<span class="displaying-num">' . esc_html(sprintf(__('%d elements', 'wc-etransactions'), $total_count)) . '</span>';
            echo '<span class="pagination-links">';

            if ($current_page > 1) {
                echo '<a class="first-page button" href="' . esc_url(remove_query_arg('paged')) . '"><span aria-hidden="true">«</span></a>';
                echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $current_page - 1)) . '"><span aria-hidden="true">‹</span></a>';
            } else {
                echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<span id="table-paging" class="paging-input">';
                    echo '<span class="tablenav-paging-text">' . esc_html(sprintf(__('%d on', 'wc-etransactions'), $i));
                    echo '<span class="total-pages"> ' . $total_pages . '</span></span></span>';
                }
            }

            if ($current_page < $total_pages) {
                echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $current_page + 1)) . '"><span aria-hidden="true">›</span></a>';
                echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages)) . '"><span aria-hidden="true">»</span></a>';
            }

            echo '</span>';
        }
    }

    private function get_current_page()
    {
        return isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    }
}

/**
 * Auto-invalidate cache on e-transactions order creation/update
 */
add_action('woocommerce_new_order', function ($order_id) {
    $order = wc_get_order($order_id);
    if ($order && strpos($order->get_payment_method(), 'etransactions_') === 0) {
        $cache_version = get_transient('wc_etransactions_cache_version');
        if ($cache_version === false) {
            $cache_version = 1;
        } else {
            $cache_version++;
        }
        set_transient('wc_etransactions_cache_version', $cache_version, HOUR_IN_SECONDS);
    }
}, 10, 1);

add_action('woocommerce_update_order', function ($order_id) {
    $order = wc_get_order($order_id);
    if ($order && strpos($order->get_payment_method(), 'etransactions_') === 0) {
        $cache_version = get_transient('wc_etransactions_cache_version');
        if ($cache_version === false) {
            $cache_version = 1;
        } else {
            $cache_version++;
        }
        set_transient('wc_etransactions_cache_version', $cache_version, HOUR_IN_SECONDS);
    }
}, 10, 1);

add_action('woocommerce_order_status_changed', function ($order_id, $old_status, $new_status) {
    $order = wc_get_order($order_id);
    if ($order && strpos($order->get_payment_method(), 'etransactions_') === 0) {
        $cache_version = get_transient('wc_etransactions_cache_version');
        if ($cache_version === false) {
            $cache_version = 1;
        } else {
            $cache_version++;
        }
        set_transient('wc_etransactions_cache_version', $cache_version, HOUR_IN_SECONDS);
    }
}, 10, 3);
