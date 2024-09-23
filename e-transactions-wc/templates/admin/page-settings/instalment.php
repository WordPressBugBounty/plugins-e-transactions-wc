<?php
/**
 * The admin settings instalment section
 */
?>

<section class="wc-etransactions__section wc-etransactions__section--instalment">

    <form method="post">

        <div class="wc-etransactions__section__header">
            <?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/credit-card.svg' ); ?>
            <?php _e( "Instalment configuration", 'wc-etransactions' ); ?>
        </div>

        <div class="wc-etransactions__section__body">

            <table class="wc-etransactions__section__body__table form-table">

                <tr>
                    <th><?php _e( "Enable instalment", 'wc-etransactions' ); ?></th>
                    <td>
                        <label class="wc-etransactions__toggle">
                            <input type="hidden" name="wc_etransactions_instalment_enabled" value="0">
                            <input type="checkbox" name="wc_etransactions_instalment_enabled" id="WCE-JS-instalment-enabled" value="1" <?php checked( $instalment_enabled, '1' ); ?>>
                            <span class="slider"></span>
                            <div class="text">
                                <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                            </div>
                        </label>
                        <p class="description"><?php _e( "Propose payment of order with multiple instalment in 2, 3 or 4 times (future instalment are not garanteed in case of payment refused).", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

            </table>

            <div class="wc-etransactions__section__body__table <?php echo $instalment_enabled !== '1' ? 'hide' : ''; ?>" id="WCE-JS-instalment-table-tabs">

                <div class="wc-etransactions-tabs">

                    <div class="wc-etransactions-tabs__nav">
                        <?php foreach( $instalments as $k => $default_data ):
                            
                            $k_str                  = (string)$k;
                            $instalment_data_in_db  = $instalment_settings[$k] ?? array();
                            $enabled                = isset($instalment_data_in_db['enabled']) ? $instalment_data_in_db['enabled'] : $default_data['enabled'];
                            $svg_icon               = $enabled === '1' ? 'check.svg' : 'times.svg';
                            $partial_payments       = $default_data['partialPayments'];

                            ?>
                            <div class="wc-etransactions-tabs__nav__item <?php echo $k !== 0 ?: 'active'; ?> <?php echo $enabled === '1' ? 'enabled' : ''; ?>" data-id="<?php echo esc_attr($k); ?>">
                                <div class="wc-etransactions-tabs__nav__item__link">
                                    <?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . "assets/svg/$svg_icon" ); ?>
                                    <?php echo sprintf( __( "%sx payment", 'wc-etransactions' ), esc_html($partial_payments) ); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="wc-etransactions-tabs__content">

                        <?php foreach( $instalments as $k => $default_data ):
                            
                            $k_str                  = (string)$k;
                            $instalment_data_in_db  = $instalment_settings[$k] ?? array();
                            $instalment_name        = 'wc_etransactions_instalment_settings[' . $k_str . ']';
                            $enabled                = isset($instalment_data_in_db['enabled']) ? $instalment_data_in_db['enabled'] : $default_data['enabled'];
                            $partial_payments       = $default_data['partialPayments'];
                            $title                  = isset($instalment_data_in_db['title']) ? $instalment_data_in_db['title'] : $default_data['title'];
                            $default_title          = $default_data['title'];
                            $logo_url               = isset($instalment_data_in_db['logoUrl']) ? $instalment_data_in_db['logoUrl'] : '';
                            $default_logo_url       = $default_data['logoUrl'];
                            $days_between_payments  = isset($instalment_data_in_db['daysBetweenPayments']) ? $instalment_data_in_db['daysBetweenPayments'] : $default_data['daysBetweenPayments'];
                            $percents               = isset($instalment_data_in_db['percents']) ? $instalment_data_in_db['percents'] : $default_data['percents'];
                            $min_amount             = isset($instalment_data_in_db['minAmount']) ? $instalment_data_in_db['minAmount'] : $default_data['minAmount'];
                            $max_amount             = isset($instalment_data_in_db['maxAmount']) ? $instalment_data_in_db['maxAmount'] : $default_data['maxAmount'];
                            ?>
                        <div class="wc-etransactions-tabs__content__item <?php echo $k !== 0 ?: 'active'; ?>" data-id="<?php echo esc_attr($k); ?>">

                            <table class="wc-etransactions__section__body__table form-table">

                                <tr>
                                    <th><?php echo sprintf( __( "Enable %sx instalment payment", 'wc-etransactions' ), esc_html($partial_payments) ); ?></th>
                                    <td>
                                        <label class="wc-etransactions__toggle">
                                            <input type="hidden" name="<?php echo esc_attr($instalment_name); ?>[enabled]" value="0">
                                            <input type="checkbox" name="<?php echo esc_attr($instalment_name); ?>[enabled]" value="1" <?php checked( $enabled, '1' ); ?>>
                                            <span class="slider"></span>
                                            <div class="text">
                                                <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                                <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                                            </div>
                                        </label>
                                        <p class="description"><?php _e( "First instalment correspond to the day of the payment of order. You will be credited at every instalment.", 'wc-etransactions' ); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th><?php _e( "Title displayed on your payment page", 'wc-etransactions' ); ?></th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr($instalment_name); ?>[title]" value="<?php echo esc_attr($title); ?>" placeholder="<?php echo esc_attr($default_title); ?>" style="width:260px;">
                                        <p class="description"><?php _e( 'Title of instalment payment option displayed on your page with means of payment choices.', 'wc-etransactions' ); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th><?php _e( "Logo displayed on your payment page", 'wc-etransactions' ); ?></th>
                                    <td>
                                        <div class="wce-upload-image">
                                            <img class="wce-preview" src="<?php echo empty($logo_url) ? esc_url($default_logo_url) : esc_url($logo_url); ?>" alt="logo" data-default="<?php echo esc_attr($default_logo_url); ?>">
                                            <div class="wce-actions">
                                                <a class="wce-upload" href="javascript:void(0);"><?php _e( "Upload", 'wc-etransactions' ); ?></a>
                                                <a class="wce-reset <?php echo empty($logo_url) ? '' : 'show'; ?>" href="javascript:void(0);">X</a>
                                            </div>
                                            <input class="wce-input" type="hidden" name="<?php echo esc_attr($instalment_name); ?>[logoUrl]" value="<?php echo esc_attr($logo_url); ?>" >
                                        </div>
                                        <p class="description"><?php _e( 'You can upload here a new logo.', 'wc-etransactions' ); ?></p>
                                        <p class="description"><?php _e( 'We recommend that you use images with 30px height & 120px length maximum.', 'wc-etransactions' ); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th><?php _e( "Days between each instalment", 'wc-etransactions' ); ?></th>
                                    <td>
                                        <select name="<?php echo esc_attr($instalment_name); ?>[daysBetweenPayments]">
                                            <?php for ( $day=WC_Etransactions_Instalment::DAYS_BETWEEN_PAYMENTS_MIN; $day<=WC_Etransactions_Instalment::DAYS_BETWEEN_PAYMENTS_MAX; $day++ ): ?>
                                                <option value="<?php echo esc_attr($day); ?>" <?php selected( $days_between_payments, $day ); ?>><?php echo esc_html($day); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <p class="description"><?php _e( 'Number of days between each instalment. Delay between the first payment on the last instalment can\'t exceed 90 days.', 'wc-etransactions' ); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th><?php _e( "Payments division", 'wc-etransactions' ); ?></th>
                                    <td>
                                        <?php for ( $part=1; $part<=$partial_payments; $part++ ): ?>
                                            <div>
                                                <input type="number" class="<?php echo $partial_payments != $part ? 'subpart' : 'subpartAuto'; ?>" name="<?php echo esc_attr($instalment_name); ?>[percents][]" value="<?php echo esc_attr($percents[($part-1)]); ?>" step="1" min="1" max="<?php echo esc_attr( 100 - ((int)$partial_payments) + 1); ?>" <?php echo $partial_payments != $part ?: 'readonly'; ?> required="required">
                                                <span>%</span>
                                            </div>
                                        <?php endfor; ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th><?php _e( "Minimum amount of order to display payment option", 'wc-etransactions' ); ?></th>
                                    <td>
                                        <input type="number" name="<?php echo esc_attr($instalment_name); ?>[minAmount]" value="<?php echo esc_attr( $min_amount ); ?>" style="max-width:75px;" >
                                        <span><?php echo esc_html( $woocommerce_currency ); ?></span>
                                    </td>
                                </tr>

                                <tr>
                                    <th><?php _e( "Maximum amount of order to display payment option", 'wc-etransactions' ); ?></th>
                                    <td>
                                        <input type="number" name="<?php echo esc_attr($instalment_name); ?>[maxAmount]" value="<?php echo esc_attr( $max_amount ); ?>" style="max-width:75px;" >
                                        <span><?php echo esc_html( $woocommerce_currency ); ?></span>
                                    </td>
                                </tr>

                            </table>
                            
                        </div>
                            
                        <?php endforeach; ?>    

                    </div>

                </div>

            </div>

        </div>

        <div class="wc-etransactions__section__footer">
            <button type="submit" name="wc_etransactions_settings_instalment"><?php _e( "Save", 'wc-etransactions' ); ?></button>
            <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
        </div>

    </form>

</section>