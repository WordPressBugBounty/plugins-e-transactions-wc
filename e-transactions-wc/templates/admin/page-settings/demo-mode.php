<?php
/**
 * The admin settings demo mode section
 */
?>

<section class="wc-etransactions__section--demo">

    <div class="wc-etransactions__section--demo__content <?php echo esc_attr(strtolower($account_environment)); ?>">
        <?php echo file_get_contents( WC_ETRANSACTIONS_PLUGIN_PATH . 'assets/svg/warning-triangle.svg' ); ?>

        <?php
            if ( $account_demo_mode === '1' ) {
                $mode = 'DEMO';
            } else {
                $mode = $account_environment;
            }

            echo sprintf( __( "Your are using the %s mode", 'wc-etransactions' ), esc_html($mode) );
        ?>
    </div>

</section>
