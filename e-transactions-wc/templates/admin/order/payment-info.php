<?php
/**
 * The order payment metabox
 */

?>

<?php if ( isset( $_POST['wce_order_error'] ) ) : ?>
    <div class="wc-etransactions__danger">
        <div class="wc-etransactions__danger__icon"><?php echo file_get_contents(WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/circle-danger.svg'); ?></div>
        <div class="wc-etransactions__danger__text">
            <p><?php echo esc_html( $_POST['wce_order_error'] ); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ( isset( $_POST['wce_order_capture_confirmation'] ) ) : ?>
    <div class="wc-etransactions__success">
        <div class="wc-etransactions__success__icon"><?php echo file_get_contents(WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/check-circle.svg'); ?></div>
        <div class="wc-etransactions__success__text">
            <p><?php _e("Funds have been captured successfully.", 'wc-etransactions'); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ( isset( $_POST['wce_order_refund_confirmation'] ) ) : ?>
    <div class="wc-etransactions__success">
        <div class="wc-etransactions__success__icon"><?php echo file_get_contents(WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/check-circle.svg'); ?></div>
        <div class="wc-etransactions__success__text">
            <p><?php _e("Refund done successfully.", 'wc-etransactions'); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php foreach ( $transactions as $transaction ):

    $transaction_number = $transaction['captured'] ? $transaction['numtrans'] : $transaction['auth_numtrans'];
    $capturable_amount  = $transaction['captured'] ? 0 : $transaction['amount'];
    $refundable_amount  = $transaction['captured'] ? ( $transaction['amount_captured'] - $order_refunded_amount ) : 0;

    ?>

    <div class="wc-etransactions__info">
        <div class="wc-etransactions__info__item">
            <div class="wc-etransactions__info__item__title"><?php _e( 'Transaction number', 'wc-etransactions' ) ?></div>
            <div class="wc-etransactions__info__item__content"><?php echo esc_html( $transaction_number ); ?></div>
        </div>
        <div class="wc-etransactions__info__item">
            <div class="wc-etransactions__info__item__title"><?php _e( 'Transaction amount', 'wc-etransactions' ) ?></div>
            <div class="wc-etransactions__info__item__content"><?php echo wc_price($transaction['amount']); ?></div>
        </div>
        <div class="wc-etransactions__info__item">
            <div class="wc-etransactions__info__item__title"><?php _e( 'Means of payment', 'wc-etransactions' ) ?></div>
            <div class="wc-etransactions__info__item__content"><?php echo esc_html($transaction['card_type']); ?></div>
        </div>
        <div class="wc-etransactions__info__item">
            <div class="wc-etransactions__info__item__title"><?php _e( '3D-Secure garantee', 'wc-etransactions' ) ?></div>
            <div class="wc-etransactions__info__item__content"><?php echo $transaction['guarantee_3ds'] ? __( 'Yes', 'wc-etransactions' ) : __( 'No', 'wc-etransactions' ); ?></div>
        </div>
    </div>

    <?php if ( !$is_contract_access ): ?>

        <div class="wc-etransactions__row">
            <div class="wc-etransactions__left">
                <div class="wc-etransactions__block">
                    <h4><?php _e('Capture', 'wc-etransactions'); ?></h4>
                    <div class="wc-etransactions__block__container">
                        <?php if ( !$is_instalement ): ?>
                            <div class="wc-etransactions__block__item">
                                <div class="wc-etransactions__block__item__title"><?php _e('Amount captured', 'wc-etransactions'); ?></div>
                                <div class="wc-etransactions__block__item__content"><?php echo wc_price($transaction['amount_captured']); ?></div>
                            </div>
                            <div class="wc-etransactions__block__item">
                                <div class="wc-etransactions__block__item__title"><?php _e('Amount that can be captured', 'wc-etransactions'); ?></div>
                                <div class="wc-etransactions__block__item__content"><?php echo wc_price($capturable_amount); ?></div>
                            </div>
                            <?php if ($capturable_amount): ?>
                                <hr>
                                <div>
                                    <div class="wc-etransactions__block__group-money">
                                        <input type="text" name="wc-etransactions-capture[amount_to_capture]" onchange="this.value = parseFloat(this.value.replace(/,/g, '.')) || 0" value="<?php echo esc_attr($capturable_amount); ?>">
                                        <span class="symbol"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                        <button type="submit" name="wc-etransactions-order-submit" value="capture" class="button button-primary" ><?php _e('Submit', 'wc-etransactions') ?></button>
                                    </div>
                                    <input type="hidden" name="wc-etransactions-capture[id_order]" value="<?php echo esc_attr($order->get_id()); ?>"/>
                                    <input type="hidden" name="wc-etransactions-capture[numappel]" value="<?php echo esc_attr($transaction['numappel']); ?>"/>
                                    <?php wp_nonce_field( 'wc-etransactions-order-action', 'wc-etransactions-order-action-nonce'); ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="wc-etransactions__block__item">
                                <div class="wc-etransactions__block__item__title"><?php _e('Amount captured', 'wc-etransactions'); ?></div>
                                <div class="wc-etransactions__block__item__content"><?php echo wc_price($x3captured_amount); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="wc-etransactions__right">
                <div class="wc-etransactions__block">
                    <h4><?php _e('Refund', 'wc-etransactions'); ?></h4>
                    <div class="wc-etransactions__block__container">
                        <div class="wc-etransactions__block__item">
                            <div class="wc-etransactions__block__item__title"><?php _e('Amount refunded', 'wc-etransactions'); ?></div>
                            <div class="wc-etransactions__block__item__content"><?php echo wc_price($order_refunded_amount); ?></div>
                        </div>
                        <div class="wc-etransactions__block__item">
                            <div class="wc-etransactions__block__item__title"><?php _e('Amount that can be refunded', 'wc-etransactions'); ?></div>
                            <div class="wc-etransactions__block__item__content"><?php echo wc_price($refundable_amount); ?></div>
                        </div>
                        <?php if ( $refundable_amount && !in_array($transaction['card_type'], WC_Etransactions_Config::NOT_REFUNDABLE) ): ?>
                            <hr>
                            <div>
                                <div class="wc-etransactions__block__group-money">
                                    <input type="text" name="wc-etransactions-refund[amount_to_refund]" onchange="this.value = parseFloat(this.value.replace(/,/g, '.')) || 0" value="<?php echo esc_attr($is_instalement ? $deadlines[0]['amount'] : $refundable_amount ); ?>">
                                    <span class="symbol"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                    <button type="submit" name="wc-etransactions-order-submit" value="refund" class="button button-primary"><?php _e('Make refund', 'wc-etransactions'); ?></button>
                                </div>
                                <input type="hidden" name="wc-etransactions-refund[id_order]" value="<?php echo esc_attr($order->get_id()); ?>"/>
                                <?php wp_nonce_field( 'wc-etransactions-order-action', 'wc-etransactions-order-action-nonce'); ?>

                                <?php if( $is_instalement ) : ?>
                                <div class="wc-etransactions__warning">
                                    <div class="wc-etransactions__warning__icon"><?php echo file_get_contents(WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/warning-triangle.svg'); ?></div>
                                    <div class="wc-etransactions__warning__text">
                                        <p><?php _e("It is only possible to refund the first installment, for the other cash functions, it is necessary to perform the actions on the Vision back-office.", 'wc-etransactions'); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

<?php endforeach; ?>

<?php if ( !empty($operations) ): ?>

    <div class="wc-etransactions__block">
        <h4><?php _e('Operations history', 'wc-etransactions'); ?></h4>
        <div class="wc-etransactions__block__container">
            <table class="wc-etransactions__table">
                <thead>
                    <tr>
                        <th><?php _e('Operation type', 'wc-etransactions'); ?></th>
                        <th><?php _e('Operation amount', 'wc-etransactions'); ?></th>
                        <th><?php _e('Operation date', 'wc-etransactions'); ?></th>
                        <th><?php _e('Code returned', 'wc-etransactions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $operations as $operation ): ?>
                        <tr>
                            <td><?php echo $operation['type'] == 'refund' ? __('Refund', 'wc-etransactions') : __('Capture', 'wc-etransactions') ; ?></td>
                            <td><?php echo $operation['type'] == 'refund' ? '-' : ''; ?><?php echo wc_price($operation['amount']); ?></td>
                            <td><?php echo esc_html($operation['date']); ?></td>
                            <td class="<?php echo esc_attr($operation['success']); ?>"><?php echo esc_html($operation['result']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php if ( $is_instalement ): ?>

    <div class="wc-etransactions__block">
        <h4><?php _e('Instalment details', 'wc-etransactions'); ?></h4>
        <div class="wc-etransactions__block__container">
            <table class="wc-etransactions__table">
                <thead>
                    <tr>
                        <th><?php _e('Transaction number', 'wc-etransactions'); ?></th>
                        <th><?php _e('Amount', 'wc-etransactions'); ?></th>
                        <th><?php _e('State', 'wc-etransactions'); ?></th>
                        <th><?php _e('Performed date', 'wc-etransactions'); ?></th>
                        <th><?php _e('Predicted date', 'wc-etransactions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $deadlines as $deadline ): ?>
                        <tr>
                            <td><?php echo esc_html($deadline['transaction']); ?></td>
                            <td><?php echo wc_price($deadline['amount']); ?></td>
                            <td><?php echo $deadline['canceled'] ? __('Cancelled', 'wc-etransactions') : ($deadline['captured'] ? __('Captured', 'wc-etransactions') : __('Pending payment', 'wc-etransactions')); ?></td>
                            <td><?php echo $deadline['captured'] ? esc_html($deadline['date_execution']) : '-'; ?></td>
                            <td><?php echo esc_html($deadline['date_intended']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>
