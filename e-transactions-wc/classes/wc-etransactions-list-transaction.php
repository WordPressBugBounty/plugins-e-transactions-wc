<?php

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}


class WC_Etransactions_List_Transaction
{
    private $order_manager;

    public function __construct() {
        $this->order_manager = new WC_Etransaction_Order_Manager();
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) ); // Ajout de l'enqueue des styles
        add_action('admin_post_reset_filters', [$this, 'reset_filters']);


    }

    public function add_menu_page() {
        add_submenu_page(
            'credit-agricole-settings',
            esc_html__( 'Transactions', 'wc-etransactions' ),
            esc_html__( 'Transactions', 'wc-etransactions' ),
            'manage_options',
            'credit-agricole-transactions',
            array( $this, 'render_orders_list' )
        );
    }

    public function render_orders_list() {
        $this->order_manager->handle_filters();
        $orders = $this->order_manager->get_filtered_orders();

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">'.esc_html__( 'Transactions Up2Pay list', 'wc-etransactions' ).'</h1>';
        echo  '<hr class="wp-header-end">';
        echo  '<div class="tablenav top">';
        $this->order_manager->render_filters();
        echo '</div>';
        $this->order_manager->display_orders_table( $orders );
        echo '</div>';
    }

    public function enqueue_styles() {
       wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );
    }

    public function reset_filters() {
        // Redirection vers la même page sans paramètres de filtre
        wp_redirect(admin_url('admin.php?page=credit-agricole-transactions'));
        exit;
    }

}