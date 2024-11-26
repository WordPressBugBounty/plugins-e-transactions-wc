<?php

// Ensure not called directly
if (!defined('ABSPATH')) {
    exit;
}


class WC_Etransaction_Order_Manager
{
    private $payment_method = 'etransactions_';
    private $order_status;
    private $per_page = 20;

    public function get_orders()
    {
        $arg = [
            'order' => 'DESC',
            'limit' => $this->per_page,
            'paged' => $this->get_current_page()
        ];
        if(!empty($this->order_status)){
            $arg['status'] = $this->order_status;
        }
        return wc_get_orders($arg);
    }

    public function get_orders_total()
    {
        $arg = [
            'order' => 'DESC',
            'limit' => '-1',
        ];
        if(!empty($this->order_status)){
            $arg['status'] = $this->order_status;
        }
        return wc_get_orders($arg);
    }

    public function display_orders_table($orders)
    {
        echo '<table class="wp-list-table widefat fixed striped orders">';
        echo $this->get_table_headers();
        echo '<tbody>';

        foreach ($orders as $order) {
            $this->display_order_row($order);
        }

        echo '</tbody>';
        echo '</table>';

        // Ajout de la pagination
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

        $order_class = $order === 'asc' ? 'asc' : 'desc';

        return '<thead>
        <tr>
        <th scope="col" class="manage-column column-order-number">' . __('Order', 'wc-etransactions') . ' </th>
        <th scope="col" class="manage-column column-transaction">' . __('Transaction', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-status">' . __('Status', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-payment">' . __('Payment method', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-amount">' . __('Amount', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-ipn">' . __('IPN', 'wc-etransactions') . '</th>
        <th scope="col" class="manage-column column-date">' . __('Date', 'wc-etransactions') . '</th>
       </tr>
       </thead>';
    }

    private function display_order_row($order_data)
    {
        $operations = $order_data->get_meta('wc-etransactions-operations', true);
        $transactions = $order_data->get_meta('wc-etransactions-transactions', true);
        $transaction_number = null;
        $ipn = null;
        if (!empty($operations)) {
            foreach ($operations as $operation) {
                $ipn = $operation['result'];
            }
        }
        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $transaction_number = $transaction['captured'] ? $transaction['numtrans'] : $transaction['auth_numtrans'];
                if(empty($ipn)){
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
            echo '<td class="column-transaction">' . __('Transactions not found', 'wc-etransactions') . '</td>';
        }
        echo '<td class="order_status column-order_status">';
        echo '<mark class="order-status status-' . esc_attr($order_data->get_status()) . ' tips"> <span>' . esc_html(wc_get_order_status_name($order_data->get_status())) . '</span></mark>';
        echo '</td>';
        echo '<td class="column-payment">' . esc_html($order_data->get_payment_method_title()) . '</td>';
        echo '<td class="column-amount">' . wc_price($order_data->get_total()) . '</td>';
        if (!empty($ipn)) {
            echo '<td class="column-ipn">' . esc_html($ipn) . '</td>';
        } else {
            echo '<td class="column-ipn">' . __('--', 'wc-etransactions') . '</td>';

        }
        echo '<td class="column-date"><time datetime="' . esc_attr($order_data->get_date_created()->date('c')) . '">' . esc_html(date_i18n('Y-m-d H:i', strtotime($order_data->get_date_created()))) . '</time></td>';

        echo '</tr>';
    }

    //Filtre

    public function handle_filters()
    {
        if (isset($_GET['order_status'])) {
            $this->order_status = sanitize_text_field($_GET['order_status']);
        } else {
            $this->order_status = null;
        }
    }

    public function render_filters() {
        $statuses = wc_get_order_statuses();
        $selected_status = isset($_GET['order_status']) ? sanitize_text_field($_GET['order_status']) : '';

        echo '<form method="GET" action="" class="search-form">';
        echo '<input type="hidden" name="page" value="credit-agricole-transactions">';
        echo '<div class="alignleft actions">';
        echo '<select name="order_status" id="order_status">';
        echo '<option value="">'.__('All status','wc-etransactions').'</option>';

        foreach ($statuses as $slug => $name) {
            $selected = selected($selected_status, $slug, false);
            echo '<option value="' . esc_attr($slug) . '" ' . $selected . '>' . esc_html($name) . '</option>';
        }

        echo '</select>';
        echo '<input type="submit" class="button action" value="'.__('Filter', 'wc-etransactions').'">';
        echo '</div>';
        echo '<div class="alignleft actions"><a href="' . esc_url(remove_query_arg(['order_status','paged']) ). '" class="button action">'.__('Reset filter','wc-etransactions').'</a></div>';
        echo '</form>';
    }

    public function get_filtered_orders($total = false)
    {
        $orders = $total ? $this->get_orders_total() : $this->get_orders();
        $filtered_orders = $this->filter_orders_by_payment_method($orders);
        return $filtered_orders;
    }

    private function filter_orders_by_payment_method($orders)
    {
        return array_filter($orders, function ($order) {
            if (method_exists($order, 'get_payment_method')) {
                $payment_method_check = $order->get_payment_method() ?? null;
            } elseif (method_exists($order, 'get_refunded_payment')) {
                $payment_method_check = $order->get_refunded_payment() ?? null;
            } else {
                return false;
            }

            if (is_string($payment_method_check) &&
                strpos(strtolower($payment_method_check), $this->payment_method) !== false) {
                return true;
            }
            return false;
        });
    }

    // Pagination

    private function display_pagination() {
        $total_orders = $this->get_filtered_orders(true);
        $total_pages = ceil(count($total_orders) / $this->per_page);

        // Si une page existe, afficher la pagination
        if ($total_pages > 1) {
            $current_page = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;

            echo '<span class="displaying-num">'.__(sprintf('%d elements', $total_orders),'wc-etransactions').'</span>';
            echo '<span class="pagination-links">';

            // Lien vers la page précédente
            if ($current_page > 1) {
                echo '<a class="first-page button" href="' . esc_url(remove_query_arg('paged')) . '"><span aria-hidden="true">«</span></a>';
                echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $current_page - 1)) . '"><span aria-hidden="true">‹</span></a>';
            }else{
                echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
                echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
            }

            // Lien vers les pages numérotées
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<span id="table-paging" class="paging-input">';
                    echo '<span class="tablenav-paging-text">'.__(sprintf('%d on', $i),'wc-etransactions');
                    echo '<span class="total-pages"> '.$total_pages.'</span></span></span>';}
            }

            // Lien vers la page suivante
            if ($current_page < $total_pages) {
                echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $current_page + 1)) . '"><span aria-hidden="true">›</span></a>';
                echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages)) . '"><span aria-hidden="true">»</span></a>';
            }

            echo '</span>';
        }
    }

    private function get_current_page() {
        return isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    }


}