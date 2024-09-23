<div class="wc-etransactions__header__support__popup" id="JS-WCE-header-support-popup">
    <div class="wc-etransactions__header__support__popup__overlay" id="JS-WCE-header-support-overlay"></div>
    <div class="wc-etransactions__header__support__popup__content">
        <div class="wc-etransactions__header__support__popup__content__header">
            <div class="wc-etransactions__header__support__popup__content__header__title"><?php _e( "Contact us", 'wc-etransactions' ); ?></div>
            <div class="wc-etransactions__header__support__popup__content__header__close" id="JS-WCE-header-support-close"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/times.svg'); ?></div>
        </div>
        <div class="wc-etransactions__header__support__popup__content__body">

            <h3><?php _e( "Availability of services", 'wc-etransactions' ); ?></h3>

            <div class="wc-etransactions__info">
                <div class="wc-etransactions__info__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/question-circle.svg'); ?></div>
                <div class="wc-etransactions__info__text">
                    <p><?php _e( "Before contacting support, please check service availability via this link:", 'wc-etransactions' ); ?></p>
                    <p><a href="https://www.ca-moncommerce.com/espace-client-mon-commerce/up2pay-e-transactions/disponibilite-de-service/" target="_blank" >https://www.ca-moncommerce.com/espace-client-mon-commerce/up2pay-e-transactions/disponibilite-de-service/</a></p>
                </div>
            </div>

            <div class="wc-etransactions__info">
                <div class="wc-etransactions__info__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/question-circle.svg'); ?></div>
                <div class="wc-etransactions__info__text">
                    <p><?php _e( "When you contact support and in order to provide as much information as possible on the settings of your store and your plugin, please copy the information below and paste it into your message to support.", 'wc-etransactions' ); ?></p>
                </div>
            </div>

            <p class="description"><?php _e( "(click inside the box to automatically copy the information to the clipboard)", 'wc-etransactions' ); ?></p>

            <div class="wc-etransactions__code">
                <textarea id="JS-WCE-header-textarea-code" readonly>
                    <?php echo esc_html( $this->get_support_info() ); ?>
                </textarea>

                <span class="message"><?php _e('Copied', 'wc-etransactions' ); ?></span>
            </div>

        </div>
        <div class="wc-etransactions__header__support__popup__content__footer">
            <!-- TODO: Add mailto -->
            <a class="wc-etransactions__header__support__popup__content__footer__btn" href="mailto:" >
                <?php _e( 'Contact support', 'wc-etransactions' ); ?>
            </a>
        </div>
    </div>
</div>