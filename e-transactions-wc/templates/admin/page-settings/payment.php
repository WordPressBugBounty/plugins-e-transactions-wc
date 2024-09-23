<?php
/**
 * The admin settings payment section
 */
?>

<section class="wc-etransactions__section wc-etransactions__section--payment">

    <form method="post">

        <div class="wc-etransactions__section__header">
            <?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/credit-card.svg' ); ?>
            <?php _e( "Payment configuration", 'wc-etransactions' ); ?>
        </div>

        <div class="wc-etransactions__section__body">

            <table class="wc-etransactions__section__body__table form-table">

                <tr>
                    <th><?php _e( "Display of payment methods", 'wc-etransactions' ); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="wc_etransactions_payment_display" value="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_DISPLAY_SIMPLE); ?>" <?php checked( $payment_display, WC_Etransactions_Payment::PAYMENT_DISPLAY_SIMPLE ); ?> id="WCE-JS-payment-display-simple">
                            <?php _e( 'Grouped', 'wc-etransactions' ); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="wc_etransactions_payment_display" value="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_DISPLAY_DETAILED); ?>" <?php checked( $payment_display, WC_Etransactions_Payment::PAYMENT_DISPLAY_DETAILED ); ?> id="WCE-JS-payment-display-detailed">
                            <?php _e( 'Advanced', 'wc-etransactions' ); ?>
                        </label>
                        <p class="description"><?php echo sprintf( __( "%sGrouped:%s display only one payment button for all means of payment.", 'wc-etransactions'), '<strong>', '</strong>' ); ?></p>
                        <p class="description"><?php echo sprintf( __( "%sAdvanced:%s display one button for each means of payment activated.", 'wc-etransactions'), '<strong>', '</strong>' ); ?></p>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table <?php echo $payment_display !== 'simple' ? 'hide' : ''; ?>" id="WCE-JS-payment-table-simple">

                <tr>
                    <th><?php _e( "Title displayed on your payment page", 'wc-etransactions' ); ?></th>
                    <td>
                        <input type="text" name="wc_etransactions_payment_display_title" value="<?php echo esc_attr($payment_display_title); ?>" placeholder="<?php _e( WC_Etransactions_Payment::PAYMENT_DISPLAY_TITLE_DEFAULT, 'wc-etransactions' ); ?>" style="width: 260px;" >
                        <p class="description"><?php _e( 'Title of generic payment option displayed on your page with means of payment choices (for translation edit the .pot file).', 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( "Logo displayed on your payment page", 'wc-etransactions' ); ?></th>
                    <td>
                        <div class="wce-upload-image">
                            <img class="wce-preview" src="<?php echo empty($payment_display_logo) ? esc_url(WC_Etransactions_Payment::PAYMENT_DISPLAY_LOGO_DEFAULT) : esc_url($payment_display_logo); ?>" alt="logo" data-default="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_DISPLAY_LOGO_DEFAULT); ?>">
                            <div class="wce-actions">
                                <a class="wce-upload" href="javascript:void(0);"><?php _e( "Upload", 'wc-etransactions' ); ?></a>
                                <a class="wce-reset <?php echo empty($payment_display_logo) ? '' : 'show'; ?>" href="javascript:void(0);">X</a>
                            </div>
                            <input class="wce-input" type="hidden" name="wc_etransactions_payment_display_logo" value="<?php echo esc_attr($payment_display_logo); ?>" >
                        </div>
                        <p class="description"><?php _e( 'You can upload here a new logo.', 'wc-etransactions' ); ?></p>
                        <p class="description"><?php _e( 'We recommend that you use images with 30px height & 120px length maximum.', 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table">

                <tr>
                    <th><?php _e( "Debit type", 'wc-etransactions' ); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="wc_etransactions_payment_debit_type" value="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_IMMEDIATE); ?>" <?php checked( $payment_debit_type, WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_IMMEDIATE ); ?> id="WCE-JS-payment-debit-type-immediate">
                            <?php _e( 'Immediate', 'wc-etransactions' ); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="wc_etransactions_payment_debit_type" value="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_DEFERRED); ?>" <?php checked( $payment_debit_type, WC_Etransactions_Payment::PAYMENT_DEBIT_TYPE_DEFERRED ); ?> id="WCE-JS-payment-debit-type-deferred">
                            <?php _e( 'Deferred', 'wc-etransactions' ); ?>
                        </label>
                        <p class="description"><?php echo sprintf( __( "%sImmediate:%s debit is done the day of the order.", 'wc-etransactions'), '<strong>', '</strong>' ); ?></p>
                        <p class="description"><?php echo sprintf( __( "%sDeferred:%s you can set number of days to wait before remittance to bank.", 'wc-etransactions'), '<strong>', '</strong>' ); ?></p>
                    </td>
                </tr>

            </table>

            <table class="wc-etransactions__section__body__table form-table <?php echo $payment_debit_type !== 'deferred' ? 'hide' : ''; ?>" id="WCE-JS-payment-table-deferred">

				<?php if ( WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_PREMIUM === $account_contract_access ): ?>
                <tr>
                    <th><?php _e( "Event that will trigger remittance to bank", 'wc-etransactions' ); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="wc_etransactions_payment_capture_event" value="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_DAYS); ?>" <?php checked( $payment_capture_event, WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_DAYS ); ?> id="WCE-JS-payment-capture-event-days">
                            <?php _e( 'Delay', 'wc-etransactions' ); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="wc_etransactions_payment_capture_event" value="<?php echo esc_attr(WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_STATUS); ?>" <?php checked( $payment_capture_event, WC_Etransactions_Payment::PAYMENT_CAPTURE_EVENT_STATUS ); ?> id="WCE-JS-payment-capture-event-status">
                            <?php _e( 'Order status', 'wc-etransactions' ); ?>
                        </label>
                        <p class="description"><?php echo sprintf( __( "%sDelay:%s automatically triggered after a delay.", 'wc-etransactions'), '<strong>', '</strong>' ); ?></p>
                        <p class="description"><?php echo sprintf( __( "%sOrder Status:%s automatically triggered on order status changed.", 'wc-etransactions'), '<strong>', '</strong>' ); ?></p>
                        <p class="description"><?php _e( "Please note that order status option, allow to trigger remittance also manually by using action button in order detail.", 'wc-etransactions'); ?></p>
                    </td>
                </tr>
				<?php endif; ?>

                <tr class="<?php echo $payment_capture_event !== 'days' ? 'hide' : ''; ?>" id="WCE-JS-payment-table-days">
                    <th><?php _e( "Delay (days) before remittance to bank", 'wc-etransactions' ); ?></th>
                    <td>
                        <select name="wc_etransactions_payment_deferred_days">
                            <?php for ( $day=WC_Etransactions_Payment::PAYMENT_DEFERRED_DAYS_MIN; $day<=WC_Etransactions_Payment::PAYMENT_DEFERRED_DAYS_MAX; $day++ ): ?>
                                <option value="<?php echo esc_attr($day); ?>" <?php selected( $payment_deferred_days, $day ); ?>><?php echo esc_html($day); ?></option>
                            <?php endfor; ?>
                        </select>
                        <p class="description"><?php _e( "Number of days before integration of your transaction in remittance to bank treatment.", 'wc-etransactions'); ?></p>
                    </td>
                </tr>

                <tr class="<?php echo $payment_capture_event !== 'status' ? 'hide' : ''; ?>" id="WCE-JS-payment-table-status">
                    <th><?php _e( "Order statuses that trigger capture", 'wc-etransactions' ); ?></th>
                    <td>
                        <div class="multi-select">
                            <select class="wce-select2" name="wc_etransactions_payment_capture_status[]" multiple="multiple" style="width: 100%;" >
                                <?php foreach ( $wc_order_statuses as $order_id => $order_name ): ?>
                                    <option value="<?php echo esc_attr($order_id); ?>" <?php echo in_array( $order_id, $payment_capture_status ) ? 'selected' : ''; ?> ><?php echo esc_html($order_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="description"><?php _e( 'Define order statuses that trigger automatically the capture  for the remittance to bank of the transaction.', 'wc-etransactions' ); ?></p>
                    </td>
                </tr>

            </table>

            <div class="wc-etransactions__section__body__table payment-methods <?php echo $payment_display !== 'detailed' ? 'hide' : ''; ?>" id="WCE-JS-payment-table-detailed">

                <div class="wc-etransactions__info">
                    <div class="wc-etransactions__info__icon"><?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/question-circle.svg'); ?></div>
                    <div class="wc-etransactions__info__text">
                        <p><?php _e( "File types accepted for logos are: .png .gif .jpg only", 'wc-etransactions' ); ?></p>
                        <p><?php _e( "We recommend that you use images with 40px height & 120px length maximum", 'wc-etransactions' ); ?></p>
                    </div>
                </div>
                
                <table class="form-table">
                    <thead>
                        <tr>
                            <th></th>
                            <td><?php _e( "Active", 'wc-etransactions' ) ?></td>
                            <td><?php _e( "Payment display", 'wc-etransactions' ) ?></td>
                            <?php if( $account_contract_access === 'premium' ): ?>
                            <td><?php _e( "1-Click", 'wc-etransactions' ) ?></td>
                            <?php endif; ?>
                            <td><?php _e( "Display text", 'wc-etransactions' ) ?></td>
                            <td><?php _e( "Logo", 'wc-etransactions' ) ?></td>
                            <td><?php _e( "From", 'wc-etransactions' ) ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $payment_methods as $method_id => $default_data ): 
        
                            $method_data_in_db      = $payment_methods_settings[$method_id] ?? array();
                            $identifier             = $default_data['identifier'];
                            $method_name            = 'wc_etransactions_payment_methods_settings[' . $method_id . ']';
                            $is_selectable          = isset($method_data_in_db['isSelectable']) ? $method_data_in_db['isSelectable'] : $default_data['isSelectable'];
                            $enabled                = isset($method_data_in_db['enabled']) ? $method_data_in_db['enabled'] : $default_data['enabled'];
                            $force_redirect         = $default_data['forceRedirect'] === '1';
                            $display_type           = isset($method_data_in_db['displayType']) ? $method_data_in_db['displayType'] : $default_data['displayType'];
                            $one_click_available    = $default_data['oneClickAvailable'] === '1';
                            $one_click_enabled      = isset($method_data_in_db['oneClickEnabled']) ? $method_data_in_db['oneClickEnabled'] : $default_data['oneClickEnabled'];
                            $title                  = isset($method_data_in_db['title']) ? $method_data_in_db['title'] : '';
                            $default_title          = $default_data['title'];
                            $logo_url               = isset($method_data_in_db['logoUrl']) ? $method_data_in_db['logoUrl'] : '';
                            $default_logo_url       = $default_data['logoUrl'];
                            $min_amount             = isset($method_data_in_db['minAmount']) ? $method_data_in_db['minAmount'] : $default_data['minAmount'];
                            $display_none           = $account_contract_access === WC_Etransactions_Account::ACCOUNT_CONTRACT_ACCESS_ACCESS && !in_array( $identifier, WC_Etransactions_Payment::PAYMENT_CONTRACTS_ACCESS );
        
                            ?>
                            <tr data-id="<?php echo esc_attr($method_id); ?>" class="contract <?php echo $is_selectable === '1' ? 'selectable' : ''; ?>" <?php echo $display_none ? 'style="display:none"' : ''; ?>>
                                <th>
                                    <?php
                                        if ( $method_id === 'OTHER' ) {
                                            _e( "Display a generic payment option for all payment methods subscribed", 'wc-etransactions' );
                                        } else {
                                            echo esc_html($identifier);
                                            echo '<br/>';
                                            if ( $method_id !== 'CB' ) {
                                                echo '<a class="WCE-JS-remove-contract" data-id="'.esc_attr($method_id).'" href="javascript:void(0);" >(' . __( "remove", 'wc-etransactions' ) . ')</a>';
                                            }
                                        }
                                    ?>
                                    <input type="hidden" class="input-isSelectable" name="<?php echo esc_attr($method_name); ?>[isSelectable]" value="<?php echo esc_attr($is_selectable); ?>">
                                </th>
                                <td>
                                    <label class="wc-etransactions__toggle">
                                        <input type="hidden" name="<?php echo esc_attr($method_name); ?>[enabled]" value="0">
                                        <input type="checkbox" name="<?php echo esc_attr($method_name); ?>[enabled]" value="1" <?php checked( $enabled, '1' ); ?>>
                                        <span class="slider"></span>
                                        <div class="text">
                                            <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                            <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                                        </div>
                                    </label>
                                </td>
                                <td>
                                    <select name="<?php echo esc_attr($method_name) ?>[displayType]" <?php disabled($force_redirect); ?> >
                                        <option value="iframe" <?php selected( $display_type, 'iframe'); ?>><?php _e( "Integrated", 'wc-etransactions' ); ?></option>
                                        <option value="redirect" <?php selected( $display_type, 'redirect'); ?>><?php _e( "Redirected", 'wc-etransactions' ); ?></option>
                                    </select>
                                </td>
                                <?php if( $account_contract_access === 'premium' ): ?>
                                <td>
                                    <?php if( $one_click_available ): ?>
                                        <label class="wc-etransactions__toggle">
                                            <input type="hidden" name="<?php echo esc_attr($method_name); ?>[oneClickEnabled]" value="0">
                                            <input type="checkbox" name="<?php echo esc_attr($method_name); ?>[oneClickEnabled]" value="1" <?php checked( $one_click_enabled, '1' ); ?>>
                                            <span class="slider"></span>
                                            <div class="text">
                                                <span class="yes"><?php _e( 'Yes', 'wc-etransactions' ); ?></span>
                                                <span class="no"><?php _e( 'No', 'wc-etransactions' ); ?></span>
                                            </div>
                                        </label>
                                    <?php else: ?>
                                        ---
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <input type="text" name="<?php echo esc_attr($method_name); ?>[title]" value="<?php echo esc_attr($title); ?>" placeholder="<?php echo esc_attr($default_title); ?>" >
                                </td>
                                <td>
                                    <div class="wce-upload-image">
                                        <img class="wce-preview" src="<?php echo empty($logo_url) ? esc_url($default_logo_url) : esc_url($logo_url); ?>" alt="logo" data-default="<?php echo esc_attr($default_logo_url); ?>">
                                        <div class="wce-actions">
                                            <a class="wce-upload" href="javascript:void(0);"><?php _e( "Upload", 'wc-etransactions' ); ?></a>
                                            <a class="wce-reset <?php echo empty($logo_url) ? '' : 'show'; ?>" href="javascript:void(0);">X</a>
                                        </div>
                                        <input class="wce-input" type="hidden" name="<?php echo esc_attr($method_name); ?>[logoUrl]" value="<?php echo esc_attr($logo_url); ?>" >
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="<?php echo esc_attr($method_name); ?>[minAmount]" value="<?php echo esc_attr( $min_amount ); ?>" style="max-width:55px;" >
                                    <span><?php echo esc_html( $woocommerce_currency ); ?></span>
                                </td>
                            </tr>
        
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10" >
                                <p><?php _e( "Add a means of payment", 'wc-etransactions' ); ?></p>
                                <select id="WCE-JS-payment-select-add-means">
                                    <option value="-1">-- <?php _e( "Choose a means of payment", 'wc-etransactions' ); ?> --</option>
                                    <?php foreach ( $payment_methods as $method_id => $default_data ) :
                                        
                                        $method_data_in_db  = $payment_methods_settings[$method_id] ?? array();
                                        $identifier         = $default_data['identifier'];
                                        $is_selectable      = isset($method_data_in_db['isSelectable']) ? $method_data_in_db['isSelectable'] : $default_data['isSelectable'];
                                        $display_none       = $is_selectable === '0' || ( $account_contract_access === 'access' && !in_array( $identifier, WC_Etransactions_Payment::PAYMENT_CONTRACTS_ACCESS ) );
                                        ?>
                                        <option value="<?php echo esc_attr($method_id); ?>" <?php echo $display_none ? 'style="display:none"' : ''; ?>><?php echo esc_html($identifier); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e( 'You can activate means of payment only included in your contract.', 'wc-etransactions' ); ?></p>
                            </td>
                        </tr>
                    </tfoot>
                </table>

            </div>

        </div>
    
        <div class="wc-etransactions__section__footer">
            <button type="submit" name="wc_etransactions_settings_payment"><?php _e( "Save", 'wc-etransactions' ); ?></button>
            <?php wp_nonce_field( 'wc_etransactions_admin_action', 'wc_etransactions_admin_nonce' ); ?>
        </div>
        
    </form>

</section>