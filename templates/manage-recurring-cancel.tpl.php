<h3><?php _ex( 'Manage Recurring Payments - Cancel Subscription', 'manage recurring', 'PDM' ); ?></h3>

<div id="pdl-manage-recurring-cancel">
    <h4><?php _ex( 'Plan Details', 'manage recurring', 'PDM' ); ?></h4>

    <dl>
        <dt>
            <?php _ex( 'Name:', 'manage recurring', 'PDM' ); ?>
        </dt>
        <dd>
            <?php echo $plan->fee_label; ?>
        </dd>
        <dt>
            <?php _ex( 'Cost:', 'manage recurring', 'PDM' ); ?>
        </dt>
        <dd>
            <?php printf( _x( '%s every %s days.', 'manage recurring', 'PDM' ),
                              pdl_currency_format( $plan->fee_price ),
                              $plan->fee_days ); ?>
        </dd>
        <!--<dt>
            <?php _ex( 'Number of images:', 'manage recurring', 'PDM' ); ?>
        </dt>
        <dd>
            <?php echo $plan->fee_images; ?>
        </dd>-->
        <dt>
            <?php _ex( 'Expires on:', 'manage recurring', 'PDM' ); ?>
        </dt>
        <dd>
            <?php echo date_i18n( get_option( 'date_format' ), strtotime( $plan->expiration_date ) ); ?>
        </dd>
    </dl>

    <form class="pdl-cancel-subscription-form" action="" method="post">
        <p><?php echo _x( 'Are you sure you want to cancel this subscription?', 'manage recurring', 'PDM' ); ?></p>
        <p>
            <input class="button button-primary" type="submit" name="cancel-subscription" value="<?php echo esc_attr( _x( 'Yes, cancel subscription', 'manage recurring', 'PDM' ) ); ?>" />
            <input class="button" type="submit" name="return-to-subscriptions" value="<?php echo esc_attr( _x( 'No, go back to my subscriptions', 'manage recurring', 'PDM' ) ); ?>" />
        </p>
    </form>
</div>
