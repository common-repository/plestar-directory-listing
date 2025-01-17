<div id="pdl-renewal-page" class="pdl-renewal-page plestardirectory-renewal plestardirectory pdl-page">
    <h2><?php _ex( 'Recurring Fee Management', 'templates', 'PDM' ); ?></h2>

    <p><?php _ex( 'Because you are on a recurring fee plan you don\'t have to renew your listing right now as this will be handled automatically when renewal comes.', 'renew', 'PDM' ); ?></p>

    <h4><?php _ex( 'Current Fee Details', 'renewal', 'PDM' ); ?></h4>

    <dl class="recurring-fee-details">
        <dt><?php _ex( 'Name:', 'renewal', 'PDM' ); ?></dt>
        <dd><?php echo $plan->fee_label; ?></dd>
        <dt><?php _ex( 'Number of images:', 'renewal', 'PDM' ); ?></dt>
        <dd><?php echo $plan->fee_images; ?></dd>
        <dt><?php _ex( 'Expiration date:', 'renewal', 'PDM' ); ?></dt>
        <dd><?php echo date_i18n( get_option( 'date_format' ), strtotime( $plan->expiration_date ) ); ?></dd>
    </dl>

    <?php if ( $show_cancel_subscription_button ): ?>
    <?php
        $url = add_query_arg( array(
            'pdl_view' => 'manage_recurring',
            'action' => 'cancel-subscription',
            'listing' => $listing->get_id(),
            'nonce' => wp_create_nonce( 'cancel-subscription-' . $listing->get_id() ),
        ), pdl_url( 'main' ) );

        $message = _x( 'However, if you want to cancel your subscription you can do that on <manage-recurring-link>the manage recurring payments page</manage-recurring-link>. When the renewal time comes you\'ll be able to change your settings again.', 'renew', 'PDM' );

        $message = str_replace( '<manage-recurring-link>', '<a href="' . esc_url( $url ) . '">', $message );
        $message = str_replace( '</manage-recurring-link>', '</a>', $message );
    ?>
    <p><?php echo $message; ?></p>

    <p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php _ex( 'Go to Manage Recurring Payments page', 'renew', 'PDM' ); ?></a>
    <?php endif; ?>
</div>
