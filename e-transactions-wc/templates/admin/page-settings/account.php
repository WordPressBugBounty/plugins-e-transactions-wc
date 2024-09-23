<?php
/**
 * The admin settings account section
 */
?>

<section class="wc-etransactions__section wc-etransactions__section--account">

    <form method="post">

        <div class="wc-etransactions__section__header">
            <?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/user.svg' ); ?>
            <?php _e( "My Account", 'wc-etransactions' ); ?>
        </div>
        
        <div class="wc-etransactions__section__body">

            <table class="wc-etransactions__section__body__table form-table">

                <tr>
                    <th><?php _e( "Use demo mode", 'wc-etransactions' ); ?></th>
                    <td>
                        <label class="wc-etransactions__toggle">
                            <input type="hidden" name="wc_etransactions_account_demo_mode" value="0">
                            <input type="checkbox" name="wc_etransactions_account_demo_mode" id="WCE-JS-account-demo-mode" value="1" <?php checked( $account_demo_mode, '1' ); ?>>
                            <span class="slider"></span>
                            <div class="text">
                                <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                            </div>
                        </label>
                        <p class="description"><?php _e( "With demo mode you can check this module and fonctionalities of Up2pay e-Transactions solution.", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table <?php echo $account_demo_mode === '1' ? 'hide' : ''; ?> " id="WCE-JS-account-table-no-demo">

                <tr>
                    <th><?php _e( "Environment", 'wc-etransactions' ); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="wc_etransactions_account_environment" value="<?php echo esc_attr(WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST); ?>" <?php checked( $account_environment, WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_TEST ); ?> id="WCE-JS-account-environment-test">
                            <?php _e( 'Test', 'wc-etransactions' ); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="wc_etransactions_account_environment" value="<?php echo esc_attr(WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_PRODUCTION); ?>" <?php checked( $account_environment, WC_Etransactions_Account::ACCOUNT_ENVIRONMENT_PRODUCTION ); ?> id="WCE-JS-account-environment-production">
                            <?php _e( 'Production', 'wc-etransactions' ); ?>
                        </label>
                    </td>
                </tr>

                <tr class="required">
                    <th><?php _e( "Site Number", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" name="wc_etransactions_account_site_number" value="<?php echo esc_attr( $account_site_number ); ?>" <?php echo $account_demo_mode === '0' ? 'required="required"' : ''; ?>>
                        <p class="description"><?php _e( "7-digit number - Informations founded in you welcome email.", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr class="required">
                    <th><?php _e( "Rank", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" name="wc_etransactions_account_rank" value="<?php echo esc_attr( $account_rank ); ?>" <?php echo $account_demo_mode === '0' ? 'required="required"' : ''; ?>>
                        <p class="description"><?php _e( "2 or 3-digit number", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr class="required">
                    <th><?php _e( "ID", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" name="wc_etransactions_account_id" value="<?php echo esc_attr( $account_id ); ?>" <?php echo $account_demo_mode === '0' ? 'required="required"' : ''; ?>>
                        <p class="description"><?php _e( "1-digit to 9-digit number", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr id="WCE-JS-account-hmac-key-test" class="<?php echo $account_environment === 'test' ? 'required' : 'opacity'; ?>">
                    <th><?php _e( "TEST HMAC key", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" name="wc_etransactions_account_hmac_test" value="<?php echo esc_attr( $account_hmac_test ); ?>" <?php echo $account_demo_mode === '0' && $account_environment === 'test' ? 'required="required"' : ''; ?>>
                        <p class="description"><?php _e( "Generate your HMAC secret key in you Vision Back-Office in Test environment", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr id="WCE-JS-account-hmac-key-production" class="<?php echo $account_environment === 'production' ? 'required' : 'opacity'; ?>">
                    <th><?php _e( "PRODUCTION HMAC key", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" name="wc_etransactions_account_hmac_prod" value="<?php echo esc_attr( $account_hmac_prod ); ?>" <?php echo $account_demo_mode === '0' && $account_environment === 'production' ? 'required="required"' : ''; ?>>
                        <p class="description"><?php _e( "Generate your HMAC secret key in you Vision Back-Office in Production environment", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table <?php echo $account_demo_mode === '0' ? 'hide' : ''; ?>" id="WCE-JS-account-table-demo">

                <tr class="required">
                    <th><?php _e( "Site Number", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr( $account_site_number_demo ); ?>" readonly="readonly">
                        <p class="description"><?php _e( "7-digit number - Informations founded in you welcome email.", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr class="required">
                    <th><?php _e( "Rank", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr( $account_rank_demo ); ?>" readonly="readonly">
                        <p class="description"><?php _e( "2 or 3-digit number", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr class="required">
                    <th><?php _e( "ID", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr( $account_id_demo ); ?>" readonly="readonly">
                        <p class="description"><?php _e( "1-digit to 9-digit number", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr class="required">
                    <th><?php _e( "HMAC key", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" value="<?php echo esc_attr( $account_hmac_demo ); ?>" readonly="readonly">
                        <p class="description"><?php _e( "With demo mode you can check this module and fonctionalities of Up2pay e-Transactions solution.", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table">

                <tr>
                    <th><?php _e( "Up2pay e-Transactions offer subscribed", 'wc-etransactions' ); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="wc_etransactions_account_contract_access" value="<?php echo esc_attr(WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS); ?>" <?php checked( $account_contract_access, WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS ); ?>>
                            <?php _e( 'Access', 'wc-etransactions' ); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="wc_etransactions_account_contract_access" value="<?php echo esc_attr(WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM); ?>" <?php checked( $account_contract_access, WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM ); ?>>
                            <?php _e( 'Premium', 'wc-etransactions' ); ?>
                        </label>
                        <p class="description"><?php _e( "Up2pay e-Transactions offer subscribed with your Credit Agricole Regional Bank.", 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th></th>
                    <td>
                        <label>
                            <input type="hidden" name="wc_etransactions_account_exemption3DS" value="0">
                            <input type="checkbox" name="wc_etransactions_account_exemption3DS" value="1" <?php checked( $account_exemption3DS, '1' ); ?> id="WCE-JS-account-exemption3DS">
                            <?php _e( 'I want to request frictionless transactions 3DS', 'wc-etransactions' ); ?>
                        </label>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table <?php echo $account_exemption3DS === '0' ? 'hide' : '';  ?>" id="WCE-JS-account-table-exemption3DS">
                <tr>
                    <th><?php _e( "Whose amount is less than or equal", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="number" name="wc_etransactions_account_max_amount3DS" value="<?php echo esc_attr( $account_max_amount3DS ); ?>" min="1" max="<?php echo esc_attr(WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX); ?>" style="max-width:80px;" >
                        <span><?php echo esc_html( $woocommerce_currency ); ?></span>
                        <p class="description"><?php _e( "Be aware that for order without 3DS challenge, bank remittance is not garantied if chargeback asked by payer.", 'wc-etransactions' ); ?></p>
                        <p class="description"><?php echo sprintf( __( "Maximum amount requested can't exceed %d %s", 'wc-etransactions' ), esc_html(WC_Etransactions_Account::ACCOUNT_MAX_AMOUNT3DS_MAX), esc_html($woocommerce_currency) ); ?></p>
                    </td>
                </tr>
            </table>

        </div>
    
        <div class="wc-etransactions__section__footer">
            <button type="submit" name="wc_etransactions_settings_account"><?php _e( "Save", 'wc-etransactions' ); ?></button>
            <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
        </div>
        
    </form>

</section>
