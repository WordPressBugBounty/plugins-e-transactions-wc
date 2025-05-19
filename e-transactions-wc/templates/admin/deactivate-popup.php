<?php
/**
 * The Deactivate Popup template
 */
?>

<div class="wc-etransactions__deactivate__popup" id="JS-WCE-deactivate-popup">
    <div class="wc-etransactions__deactivate__popup__overlay" id="JS-WCE-deactivate-popup-overlay"></div>

    <div class="wc-etransactions__deactivate__popup__content">
        <div class="wc-etransactions__deactivate__popup__content__header">
            <div class="wc-etransactions__deactivate__popup__content__header__close" id="JS-WCE-deactivate-popup-close"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/times.svg'); ?></div>
        </div>
        <div class="wc-etransactions__deactivate__popup__content__body">
            <h4><?php esc_html_e( 'To deactivate the plugin correctly, please select if you want to:', 'wc-etransactions' ) ?></h4>
            <ul>
                <li><?php esc_html_e( 'Deactivate', 'wc-etransactions' ) ?></li>
                <li><?php esc_html_e( 'Deactivate, and remove all data', 'wc-etransactions' ); ?></li>
            </ul>
        </div>
        <div class="wc-etransactions__deactivate__popup__content__footer">
            <a class="button button-default" href="#" id="JS-WCE-deactivate-popup-cancel"><?php esc_html_e( 'Cancel', 'wc-etransactions' ) ?></a>
            <a class="button button-primary" href="#" id="JS-WCE-deactivate-popup-deactivate"><?php esc_html_e( 'Deactivate', 'wc-etransactions' ) ?></a>
            <form method="post" action="">
                <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
                <button type="submit" class="button button-primary" name="wc_etransactions_deactivate_popup"><?php esc_html_e( 'Deactivate and Reset', 'wc-etransactions' ) ?></button>
            </form>
        </div>
    </div>
</div>