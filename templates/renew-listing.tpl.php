<div id="pdl-renewal-page" class="pdl-renewal-page plestardirectory-renewal plestardirectory pdl-page">

    <h2><?php _ex('Renew Listing', 'templates', 'PDM'); ?></h2>

    <?php if ( isset( $payment ) && $payment ): ?>
        <form action="<?php echo esc_url( $payment->get_checkout_url() ); ?>" method="POST">
        <input type="submit" value="<?php _ex( 'Proceed to Checkout', 'renewal', 'PDM' ); ?>" />
        </form>
    <?php else: ?>
        <p><?php printf( _x( 'You are about to renew your listing "%s" publication.',
                             'templates',
                             'PDM' ),
                         esc_html( $listing->get_title() ) ); ?></p>
        <p><?php _ex( 'Please select a fee option or click "Do not renew my listing" to remove your listing from the directory.', 'PDM' ); ?></p>

        <form id="pdl-renewlisting-form" method="post" action="">
            <?php echo pdl_render( 'plan-selection', array( 'plans' => $plans, 'selected' => $current_plan->fee_id ) ); ?>

            <p><input type="submit" class="submit" name="submit" value="<?php _ex('Continue', 'templates', 'PDM'); ?>" /></p>

            <div class="do-not-renew-listing">
                <p><?php _ex( 'Clicking the button below will cause your listing to be permanently removed from the directory.', 'renewal', 'PDM' ); ?></p>

                <input type="submit" class="submit" name="cancel-renewal" value="<?php _ex('Do not renew my listing', 'templates', 'PDM'); ?>" />
            </div>
        </form>
    <?php endif; ?>

</div>
