<?php
/**
 * The admin settings page header
 */
?>

<div class="wc-etransactions__header">

    <div class="wc-etransactions__header__logo">
        <img src="<?php echo esc_url(WC_ETRANSACTIONS_PLUGIN_URL . 'assets/svg/logo.svg'); ?>"/>
    </div>
    
    <div class="wc-etransactions__header__support">

        <div class="wc-etransactions__header__support__contact">

            <div class="wc-etransactions__header__support__contact__icon">
                <img src="<?php echo esc_url(WC_ETRANSACTIONS_PLUGIN_URL . 'assets/svg/question-circle.svg'); ?>"/>            </div>

            <div class="wc-etransactions__header__support__contact__text">
                <p><b><?php esc_html_e( 'Do you have a question?', 'wc-etransactions' ); ?></b></p>
                <p>
                    <?php esc_html_e( 'Contact us using', 'wc-etransactions' ); ?>
                    <a id="JS-WCE-header-support-open" href="javascript:void(0);">
                        <?php esc_html_e( 'this link', 'wc-etransactions' ); ?>
                    </a>
                </p>
            </div>
            
        </div>

        <div class="wc-etransactions__header__support__buttons">
            <a class="wc-etransactions__header__support__buttons__download" href="<?php echo esc_url($header_download_pdf); ?>" download>
                <?php esc_html_e( 'User guide', 'wc-etransactions' ); ?>
            </a>
            <a class="wc-etransactions__header__support__buttons__download" id="JS-WCE-header-config-open" href="javascript:void(0);" >
                <?php esc_html_e( 'Check my configuration', 'wc-etransactions' ); ?>
            </a>
        </div>

    </div>

    <?php
        require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/popup/support.php';
        require WC_ETRANSACTIONS_PLUGIN_PATH . 'templates/admin/page-settings/popup/config.php';
    ?>

</div>