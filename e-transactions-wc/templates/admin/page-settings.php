<?php
/**
 * The admin settings page
 * @since      3.0.0
 */
?>

<div class="wrap">

    <?php
        settings_errors();

        require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/changelog.php';
        require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/header.php';
        if ( $intro_show_again === '1' ) {
            require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/intro.php';
        }

        if ( ! empty( $first_time ) ) {
            require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/demo-mode.php';
        }

        if ( ! empty( $first_time ) || ( isset( $_POST['wc_etransactions_first_time'] ) && $_POST['wc_etransactions_first_time'] === WC_Etransactions_Config::FIRST_TIME_LOGIN ) ) {
            require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/account.php';
        }

        if ( ! empty( $first_time ) ) {
            require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/payment.php';
            if ($account_contract_access === WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM ) {
                require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/instalment.php';
            }
        }
    ?>

</div>