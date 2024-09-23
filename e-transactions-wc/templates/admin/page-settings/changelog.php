<?php
/**
 * The admin settings page changelog
 */
?>

<div class="wc-etransactions__changelog">

    <div class="wc-etransactions__changelog__text">
        <div class="wc-etransactions__changelog__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/info-circle.svg') ;?></div>
        <div class="wc-etransactions__changelog__title"><?php echo sprintf( __( "Module version v%s", 'wc-etransactions' ), WC_ETRANSACTIONS_VERSION ); ?> -</div>
        <a class="wc-etransactions__changelog__btn" id="JS-WCE-changelog-open" href="javascript:void(0);" ><?php _e( "What's new?", 'wc-etransactions' ); ?></a>
    </div>

    <div class="wc-etransactions__changelog__popup" id="JS-WCE-changelog-popup">
        <div class="wc-etransactions__changelog__popup__overlay" id="JS-WCE-changelog-overlay"></div>
        <div class="wc-etransactions__changelog__popup__content">
            <div class="wc-etransactions__changelog__popup__content__header">
                <div class="wc-etransactions__changelog__popup__content__header__title"><?php _e( "What's new in the version", 'wc-etransactions' ); ?></div>
                <div class="wc-etransactions__changelog__popup__content__header__close" id="JS-WCE-changelog-close"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/times.svg'); ?></div>
            </div>
            <div class="wc-etransactions__changelog__popup__content__body">
                <h3><?php echo sprintf( __( "What's new in version %s", 'wc-etransactions' ), WC_ETRANSACTIONS_VERSION ); ?></h3>
                <?php
                    // $changelog_text = file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/txt/changelog.txt' );
                    // echo wp_kses_post( wpautop( wptexturize( $changelog_text ) ) );
                ?>
                <ul>
                    <li><?php _e( 'Fixed : V2 to V3 data migration, problem with the HMAC key', 'wc-etransactions' ) ?></li>
                    <li><?php _e( 'Fixed : Restored the original HMAC key from V2 if the new one is empty or invalid', 'wc-etransactions' ) ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>