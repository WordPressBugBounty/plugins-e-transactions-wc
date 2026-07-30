<?php
/**
 * The admin settings page changelog
 */
?>

<div class="wc-etransactions__changelog">

    <div class="wc-etransactions__changelog__text">
        <div class="wc-etransactions__changelog__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/info-circle.svg') ;?></div>
        <div class="wc-etransactions__changelog__title"><?php echo sprintf( esc_html__( "Module version v%s", 'wc-etransactions' ), esc_html(WC_ETRANSACTIONS_VERSION) ); ?> -</div>
        <a class="wc-etransactions__changelog__btn" id="JS-WCE-changelog-open" href="javascript:void(0);" ><?php esc_html_e( "What's new?", 'wc-etransactions' ); ?></a>
    </div>

    <div class="wc-etransactions__changelog__popup" id="JS-WCE-changelog-popup">
        <div class="wc-etransactions__changelog__popup__overlay" id="JS-WCE-changelog-overlay"></div>
        <div class="wc-etransactions__changelog__popup__content">
            <div class="wc-etransactions__changelog__popup__content__header">
                <div class="wc-etransactions__changelog__popup__content__header__title"><?php esc_html_e( "What's new in the version", 'wc-etransactions' ); ?></div>
                <div class="wc-etransactions__changelog__popup__content__header__close" id="JS-WCE-changelog-close"><?php echo wp_kses(file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/times.svg'),allowed_tag_svg()); ?></div>
            </div>
            <div class="wc-etransactions__changelog__popup__content__body">
                <h3><?php echo sprintf( esc_html__( "What's new in version %s", 'wc-etransactions' ), esc_html(WC_ETRANSACTIONS_VERSION) ); ?></h3>
                <ul>
                    <li><?php esc_html_e( 'Fixed number days between installments payment', 'wc-etransactions' ) ?></li>
                    <li><?php esc_html_e( 'Updated credit card logo', 'wc-etransactions' ) ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>