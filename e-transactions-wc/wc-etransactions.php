<?php
/**
 * Plugin Name: Up2pay e-Transactions
 * Description: Up2pay e-Transactions gateway payment plugins for WooCommerce
 * Version: 3.0.5
 * Author: Up2pay e-Transactions
 * Author URI: https://www.ca-moncommerce.com/espace-client-mon-commerce/up2pay-e-transactions/
 * Text Domain: wc-etransactions
 *
 * @package WordPress
 * @since 0.9.0
 */

// Ensure not called directly
if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Check if the previous plugin exists
 */
$previousET = (in_array('woocommerce-etransactions/woocommerce-etransactions.php', apply_filters('active_plugins', get_option('active_plugins'))));
if (is_multisite()) {
    $previousET = (array_key_exists('woocommerce-etransactions/woocommerce-etransactions.php', apply_filters('active_plugins', get_site_option('active_sitewide_plugins'))));
}

if ( $previousET || defined('WC_ETRANSACTIONS_PLUGIN') ) {

    add_action('admin_notices', function(){
        echo '<div class="error"><p>' . __('Previous plugin already installed. deactivate the previous one first.', 'wc-etransactions') . '</p></div>';
    });

    add_action('admin_init', function(){
        deactivate_plugins(plugin_basename(__FILE__));
    });
}

// Define constants
defined('WC_ETRANSACTIONS_PLUGIN')          || define('WC_ETRANSACTIONS_PLUGIN', 'wc-etransactions');
defined('WC_ETRANSACTIONS_VERSION')         || define('WC_ETRANSACTIONS_VERSION', get_file_data(__FILE__, array('Version'), 'plugin')[0]);
defined('WC_ETRANSACTIONS_KEY_PATH')        || define('WC_ETRANSACTIONS_KEY_PATH', ABSPATH . '/kek.php');
defined('WC_ETRANSACTIONS_PLUGIN_URL')      || define('WC_ETRANSACTIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
defined('WC_ETRANSACTIONS_PLUGIN_PATH')     || define('WC_ETRANSACTIONS_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
defined('WC_ETRANSACTIONS_PLUGIN_BASENAME') || define('WC_ETRANSACTIONS_PLUGIN_BASENAME', plugin_basename( __FILE__ ));

require_once( dirname(__FILE__) . '/global-functions.php' );

require_once( dirname(__FILE__) . '/classes/config/wc-etransactions-config.php' );
require_once( dirname(__FILE__) . '/classes/config/wc-etransactions-account.php' );
require_once( dirname(__FILE__) . '/classes/config/wc-etransactions-payment.php' );
require_once( dirname(__FILE__) . '/classes/config/wc-etransactions-instalment.php' );

require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-abstract-request.php' );
require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-test-payment-request.php' );
require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-simple-payment-request.php' );
require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-instalment-payment-request.php' );
require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-capture-request.php' );
require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-refund-request.php' );
require_once( dirname(__FILE__) . '/classes/helpers/wc-etransaction-signature.php' );

require_once( dirname(__FILE__) . '/classes/wc-etransactions-settings.php' );
require_once( dirname(__FILE__) . '/classes/wc-etransactions-gateways.php' );
require_once( dirname(__FILE__) . '/classes/wc-etransactions-order.php' );
require_once( dirname(__FILE__) . '/classes/wc-etransactions-front.php' );
require_once( dirname(__FILE__) . '/classes/wc-etransactions-updater.php' );

new WC_Etransactions_Settings();
new WC_Etransactions_Gateways();
new WC_Etransactions_Order();
new WC_Etransactions_Front();
new WC_Etransactions_Updater();

function wc_etransactions_plugins_loaded() {
    load_plugin_textdomain( 'wc-etransactions', false, dirname(plugin_basename(__FILE__)) . '/languages/' );
}
add_action( 'plugins_loaded', 'wc_etransactions_plugins_loaded' );