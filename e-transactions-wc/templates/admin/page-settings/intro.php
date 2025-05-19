<?php
/**
 * The admin settings intro section
 */
?>

<section class="wc-etransactions__section--intro">

    <div class="wc-etransactions__section--intro__header">

        <div class="wc-etransactions__section--intro__header__left">
            <p class="wc-etransactions__section--intro__header__title">
                <span class="main"><?php esc_html_e( "The ideal solution", 'wc-etransactions' ); ?></span>
                <span class="inverse"><?php esc_html_e( "to accept online payments!", 'wc-etransactions' ); ?></span>
            </p>
        </div>

        <?php if ( ! empty( $first_time ) ) : ?>
        <div class="wc-etransactions__section--intro__header__right">
            <form method="post" class="wc-etransactions__section--intro__header__form">
                <button type="submit" name="wc_etransactions_dont_show_again">
                    <?php esc_html_e( "Don't show this again", 'wc-etransactions' ); ?>
                    <?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/eye-hide.svg' ); ?>
                </button>
                <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
            </form>
        </div>
        <?php endif; ?>

    </div>

    <div class="wc-etransactions__section--intro__content">

        <div class="wc-etransactions__section--intro__content__left">

            <div class="wc-etransactions__section--intro__content__left__top">

                <ul class="wc-etransactions__section--intro__content__list">
                    <li><?php esc_html_e( "Choose a solution already used by thousands of merchants.", 'wc-etransactions' ); ?></li>
                    <li><?php esc_html_e( "Offer your customer a shop integrated payment form or a hosted payment page.", 'wc-etransactions' ); ?></li>
                    <li><?php esc_html_e( "Secure your transactions with 3D-Secure v2 (PSD2 compliant).", 'wc-etransactions' ); ?></li>
                    <li><?php esc_html_e( "Easily manage your order from you Prestashop Back-Office.", 'wc-etransactions' ); ?></li>
                    <li><?php esc_html_e( "Your bank account credited at D+1.", 'wc-etransactions' ); ?></li>
                    <li><?php esc_html_e( "Take advantage of support provided by e-Commerce experts.", 'wc-etransactions' ); ?></li>
                </ul>

            </div>

            <div class="wc-etransactions__section--intro__content__left__bottom">

                <div class="wc-etransactions__section--intro__content__block">
                    <div class="wc-etransactions__section--intro__content__block__title"><?php esc_html_e( "Access Offer", 'wc-etransactions' ); ?></div>
                    <div class="wc-etransactions__section--intro__content__block__desc"><?php esc_html_e( "A simple offer with the basics to accept online payments", 'wc-etransactions' ); ?></div>
                    <ul class="wc-etransactions__section--intro__content__block__list">
                        <li><?php echo sprintf( esc_html__( "%sAccept CB, VISA, Mastercard cards and also Paypal.%s", 'wc-etransactions' ), '<strong>', '</strong>' ); ?></li>
                        <li><?php echo sprintf( esc_html__( "%sAvailable features:%s immediate or deferred payments.", 'wc-etransactions' ), '<strong>', '</strong>' ); ?></li>
                    </ul>
                </div>

                <div class="wc-etransactions__section--intro__content__block inverse">
                    <div class="wc-etransactions__section--intro__content__block__title"><?php esc_html_e( "Premium Offer", 'wc-etransactions' ); ?></div>
                    <div class="wc-etransactions__section--intro__content__block__desc"><?php esc_html_e( "A complete offer to optimize your activity and improve customer experience", 'wc-etransactions' ); ?></div>
                    <ul class="wc-etransactions__section--intro__content__block__list">
                        <li><?php echo sprintf( esc_html__( "%sAccept more payment methods%s as Amex, Titres Restaurant, e-Chèques Vacances (ANCV).", 'wc-etransactions' ), '<strong>', '</strong>' ); ?></li>
                        <li><?php echo sprintf( esc_html__( "%sActivate more features:%s One-click payment, subscription, instalment payment, capture on shipment and refund from PrestaShop Back-Office.", 'wc-etransactions' ), '<strong>', '</strong>' ); ?></li>
                        <li><?php echo sprintf( esc_html__( "%sCustomize%s your payment page using your graphic charter.", 'wc-etransactions' ), '<strong>', '</strong>' ); ?></li>
                    </ul>
                </div>

            </div>

        </div>

        <div class="wc-etransactions__section--intro__content__right">

            <img src="<?php echo esc_url(WC_ETRANSACTIONS_PLUGIN_URL . 'assets/img/intro-visuel.jpg'); ?>" alt="up2pay">

            <div class="wc-etransactions__section--intro__content__right__actions">

            <?php if ( empty( $first_time ) ) : ?>
                <form method="post">
                    <button type="submit" name="wc_etransactions_first_time" value="<?php echo esc_attr( WC_Etransactions_Config::FIRST_TIME_LOGIN ); ?>" ><?php esc_html_e( "I already have an account", 'wc-etransactions' ); ?></button>
                    <button type="submit" name="wc_etransactions_first_time" value="<?php echo esc_attr( WC_Etransactions_Config::FIRST_TIME_DEMO ); ?>" ><?php esc_html_e( "Test using demo account", 'wc-etransactions' ); ?></button>
                    <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
                </form>
            <?php endif; ?>

                <a href="https://www.ca-moncommerce.com/credit-agricole/up2pay-e-transactions-pour-prestashop/" target="_blank"><?php esc_html_e( "Sign-up", 'wc-etransactions' ); ?> >></a>
            </div>

        </div>

    </div>

</section>