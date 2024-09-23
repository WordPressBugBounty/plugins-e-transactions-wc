<div class="wc-etransactions__header__config__popup" id="JS-WCE-header-config-popup">
    <div class="wc-etransactions__header__config__popup__overlay" id="JS-WCE-header-config-overlay"></div>
    <div class="wc-etransactions__header__config__popup__content">
        <div class="wc-etransactions__header__config__popup__content__header">
            <div class="wc-etransactions__header__config__popup__content__header__title"><?php _e( "Check my configuration", 'wc-etransactions' ); ?></div>
            <div class="wc-etransactions__header__config__popup__content__header__close" id="JS-WCE-header-config-close"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/times.svg'); ?></div>
        </div>
        <div class="wc-etransactions__header__config__popup__content__body">

            <h3><?php _e( "Prerequisites", 'wc-etransactions' ); ?></h3>

            <?php
            foreach ( $config_requirements as $requirement ):
                $check_icon = file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . "assets/svg/check.svg" );
                $times_icon = file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . "assets/svg/times.svg" );
            ?>
                <p class="requirement <?php echo $requirement['pass'] ? '': 'error'; ?>">
                    <?php echo $requirement['pass'] ? $check_icon : $times_icon; ?>
                    <?php echo esc_html($requirement['text']); ?>
                </p>
            <?php endforeach; ?>

            <hr>

            <h3><?php _e( "Contract configuration", 'wc-etransactions' ); ?></h3>

            <div class="wc-etransactions__header__config__popup__content__body__test-request">
                <div class="wc-etransactions__success success">
                    <div class="wc-etransactions__success__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/check-circle.svg'); ?></div>
                    <div class="wc-etransactions__success__text">
                        <p><?php _e( "Your plugin is correctly configured with your contract.", 'wc-etransactions' ); ?></p>
                    </div>
                </div>
                <div class="wc-etransactions__warning warning">
                    <div class="wc-etransactions__warning__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/warning-circle.svg'); ?></div>
                    <div class="wc-etransactions__warning__text">
                        <p><?php _e( "Error checking configuration.", 'wc-etransactions' ); ?></p>
                        <p><?php _e( "Your plugin is not correctly configured with your contract or an error has occurred. Please check your configuration and check again.", 'wc-etransactions' ); ?></p>
                        <p><?php _e( "If you have any problems, please do not hesitate to contact e-Transactions support.", 'wc-etransactions' ); ?></p>
                    </div>
                </div>
                <div class="loader"></div>
            </div>

            <form method="post" class="wc-etransactions__header__config__popup__content__body__form" >

                <table class="form-table">
                    <tr>
                        <th><?php _e( "Use another production platform", 'wc-etransactions' ); ?></th>
                        <td>
                            <label class="wc-etransactions__toggle">
                                <input type="hidden" name="wc_etransactions_use_secondary_gateway" value="0">
                                <input type="checkbox" name="wc_etransactions_use_secondary_gateway" value="1" <?php checked( $use_secondary_gateway, '1' ); ?>>
                                <span class="slider"></span>
                                <div class="text">
                                    <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                    <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                                </div>
                            </label>
                        </td>
                    </tr>
                </table>

                <h3><?php _e( "Logs", 'wc-etransactions' ); ?></h3>

                <div class="wc-etransactions__info">
                    <div class="wc-etransactions__info__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/question-circle.svg'); ?></div>
                    <div class="wc-etransactions__info__text">
                        <p><?php echo sprintf( __( "Click %shere to download%s the last log file.", 'wc-etransactions' ), '<a href="javascript:void(0);" id="WCE-JS-config-donload-log">', '</a>' ); ?></p>
                        <p><?php echo sprintf( __( "Older files can be accessed on your server, in %s directory.", 'wc-etransactions' ), '<code>/wp-content/uploads/wc-logs</code>' ); ?></p>
                    </div>
                </div>

                <table class="form-table">
                    <tr>
                        <th><?php _e( "Enable verbose logs", 'wc-etransactions' ); ?></th>
                        <td>
                            <label class="wc-etransactions__toggle">
                                <input type="hidden" name="wc_etransactions_enable_logs" value="0">
                                <input type="checkbox" name="wc_etransactions_enable_logs" value="1" <?php checked( $enable_logs, '1' ); ?>>
                                <span class="slider"></span>
                                <div class="text">
                                    <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                    <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                                </div>
                            </label>
                            <p><?php _e( "The minimum log level will be set to Debug.", 'wc-etransactions' ); ?></p>
                            <p><?php _e( "Enable this option only if the Support Team asks you to do it.", 'wc-etransactions' ); ?></p>
                        </td>
                    </tr>
                </table>

                <div class="wc-etransactions__header__config__popup__content__body__form__footer">
                    <button type="submit" name="wc_etransactions_settings_popup_config"><?php _e( "Save", 'wc-etransactions' ); ?></button>
                    <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
                </div>

            </form>

        </div>
    </div>
</div>