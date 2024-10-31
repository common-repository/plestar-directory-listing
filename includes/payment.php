<?php
require_once( PDL_PATH . 'includes/class-payment.php' );
require_once( PDL_INC . 'class-payment-gateway.php' );
require_once( PDL_PATH . 'includes/class-fees-api.php' );

/*
 * Fees/Payment API
 */

if ( ! class_exists( 'PDL_PaymentsAPI' ) ) {

class PDL_PaymentsAPI {

    public function __construct() {
        // Listing abandonment.
        add_filter( 'PDL_Listing::get_payment_status', array( &$this, 'abandonment_status' ), 10, 2 );
        add_filter( 'pdl_admin_directory_views', array( &$this, 'abandonment_admin_views' ), 10, 2 );
        add_filter( 'pdl_admin_directory_filter', array( &$this, 'abandonment_admin_filter' ), 10, 2 );
    }

    public function cancel_subscription( $listing, $subscription ) {
        $payment = $subscription->get_parent_payment();

        if ( ! $payment ) {
            $message = __( "We couldn't find a payment associated with the given subscription.", 'PDM' );
            throw new Exception( $message );
        }

        $gateway = $GLOBALS['pdl']->payment_gateways->get( $payment->gateway );

        if ( ! $gateway ) {
            $message = __( 'The payment gateway "<payment-gateway>" is not available.', 'PDM' );
            $message = str_replace( '<payment-gateway>', $gateway, $message );
            throw new Exception( $message );
        }

        $gateway->cancel_subscription( $listing, $subscription );
    }

    /**
     * @since 5.0
     */
    public function render_receipt( $payment ) {
        ob_start();
        do_action( 'pdl_before_render_receipt', $payment );
?>

<div class="pdl-payment-receipt">

    <div class="pdl-payment-receipt-header">
        <h4><?php printf( _x( 'Payment #%s', 'payments', 'PDM' ), $payment->id ); ?></h4>
        <span class="pdl-payment-receipt-date"><?php echo date( 'Y-m-d H:i', strtotime( $payment->created_at ) ); ?></span>

        <span class="pdl-tag pdl-payment-status pdl-payment-status-<?php echo $payment->status; ?>"><?php echo PDL_Payment::get_status_label( $payment->status ); ?></span>
    </div>
    <div class="pdl-payment-receipt-details">
        <dl>
            <?php if ( $payment->gateway ): ?>
            <dt><?php _ex( 'Gateway:', 'payments', 'PDM' ); ?></dt>
            <dd><?php echo $payment->gateway; ?></dd>
            <dt><?php _ex( 'Gateway Transaction ID:', 'payments', 'PDM' ); ?></dt>
            <dd><?php echo $payment->gateway_tx_id; ?></dd>
            <?php endif; ?>
            <dt><?php _ex( 'Bill To:', 'payments', 'PDM' ); ?></dt>
            <dd>
                <?php if ( $payment->payer_first_name || $payment->payer_last_name ) : ?>
                    <?php echo $payment->payer_first_name; ?> <?php echo $payment->payer_last_name; ?><br />
                <?php endif; ?>
                <?php echo implode( '<br />', array_filter( $payment->payer_address ) ); ?>

                <?php if ( $payment->payer_email ): ?>
                    <br /><br /><?php echo $payment->payer_email; ?>
                <?php endif; ?>
            </dd>
        </dl>
    </div>

    <?php echo $this->render_invoice( $payment ); ?>

    <input type="button" class="pdl-payment-receipt-print" value="<?php _ex( 'Print Receipt', 'checkout', 'PDM' ); ?>" />
</div>

<?php
        do_action( 'pdl_after_render_receipt', $payment );
        return ob_get_clean();
    }

    /**
     * Renders an invoice table for a given payment.
     * @param $payment PDL_Payment
     * @return string HTML output.
     * @since 3.4
     */
    public function render_invoice( &$payment ) {
        $html  = '';
        $html .= '<div class="pdl-checkout-invoice">';
        $html .= pdl_render( 'payment/payment_items', array( 'payment' => $payment ), false );
        $html .= '</div>';

        return $html;
    }

    /**
     * @since 3.5.8
     */
    public function abandonment_status( $status, $listing_id ) {
        // For now, we only consider abandonment if it involves listings with pending INITIAL payments.
        if ( 'pending' != $status || ! $listing_id || ! pdl_get_option( 'payment-abandonment' ) )
            return $status;

        $last_pending = PDL_Payment::objects()->filter( array( 'listing_id' => $listing_id, 'status' => 'pending' ) )->order_by( '-created_at' )->get();

        if ( ! $last_pending || 'initial' != $last_pending->payment_type ) {
            return $status;
        }

        $threshold = max( 1, absint( pdl_get_option( 'payment-abandonment-threshold' ) ) );
        $hours_elapsed = ( current_time( 'timestamp' ) - strtotime( $last_pending['created_at'] ) ) / ( 60 * 60 );

        if ( $hours_elapsed <= 0 )
            return $status;

        if ( $hours_elapsed >= ( 2 * $threshold ) ) {
            return 'payment-abandoned';
        } elseif ( $hours_elapsed >= $threshold ) {
            return 'pending-abandonment';
        }

        return $status;
    }

    /**
     * @since 3.5.8
     */
    public function abandonment_admin_views( $views, $post_statuses ) {
        global $wpdb;

        if ( ! pdl_get_option( 'payment-abandonment' ) )
            return $views;

        $threshold = max( 1, absint( pdl_get_option( 'payment-abandonment-threshold' ) ) );
        $now = current_time( 'timestamp' );

        $within_pending = pdl_format_time( strtotime( sprintf( '-%d hours', $threshold ), $now ), 'mysql' );
        $within_abandonment = pdl_format_time( strtotime( sprintf( '-%d hours', $threshold * 2 ), $now ), 'mysql' );

        $count_pending = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pdl_payments ps LEFT JOIN {$wpdb->posts} p ON p.ID = ps.listing_id WHERE ps.created_at > %s AND ps.created_at <= %s AND ps.status = %s AND ps.payment_type = %s AND p.post_status IN ({$post_statuses})",
            $within_abandonment,
            $within_pending,
            'pending',
            'initial'
        ) );
        $count_abandoned = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pdl_payments ps LEFT JOIN {$wpdb->posts} p ON p.ID = ps.listing_id WHERE ps.created_at <= %s AND ps.status = %s AND ps.payment_type = %s AND p.post_status IN ({$post_statuses})",
            $within_abandonment,
            'pending',
            'initial'
        ) );

        $views['pending-abandonment'] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                                 esc_url( add_query_arg( 'pdmfilter', 'pending-abandonment', remove_query_arg( 'listing_status' ) ) ),
                                                 'pending-abandonment' == pdl_getv( $_REQUEST, 'pdmfilter' ) ? 'current' : '',
                                                 _x( 'Pending Abandonment', 'admin', 'PDM' ),
                                                 number_format_i18n( $count_pending ) );
        $views['abandoned'] = sprintf( '<a href="%s" class="%s">%s</a> <span class="count">(%s)</span></a>',
                                        esc_url( add_query_arg( 'pdmfilter', 'abandoned', remove_query_arg( 'listing_status' ) ) ),
                                        'abandoned' == pdl_getv( $_REQUEST, 'pdmfilter' ) ? 'current' : '',
                                        _x( 'Abandoned', 'admin', 'PDM' ),
                                        number_format_i18n( $count_abandoned ) );

        return $views;
    }

    /**
     * @since 3.5.8
     */
    public function abandonment_admin_filter( $pieces, $filter = '' ) {
        if ( ! pdl_get_option( 'payment-abandonment' ) ||
             ! in_array( $filter, array( 'abandoned', 'pending-abandonment' ), true ) )
            return $pieces;

        global $wpdb;

        // TODO: move this code elsewhere since it is used in several places.
        $threshold = max( 1, absint( pdl_get_option( 'payment-abandonment-threshold' ) ) );
        $now = current_time( 'timestamp' );

        $within_pending = pdl_format_time( strtotime( sprintf( '-%d hours', $threshold ), $now ), 'mysql' );
        $within_abandonment = pdl_format_time( strtotime( sprintf( '-%d hours', $threshold * 2 ), $now ), 'mysql' );

        $pieces['join'] .= " LEFT JOIN {$wpdb->prefix}pdl_payments ps ON {$wpdb->posts}.ID = ps.listing_id";
        $pieces['where'] .= $wpdb->prepare( ' AND ps.payment_type = %s AND ps.status = %s ', 'initial', 'pending' );

        switch ( $filter ) {
            case 'abandoned':
                $pieces['where'] .= $wpdb->prepare( ' AND ps.created_at <= %s ', $within_abandonment );
                break;

            case 'pending-abandonment':
                $pieces['where'] .= $wpdb->prepare( ' AND ps.created_at > %s AND ps.created_at <= %s ', $within_abandonment, $within_pending );
                break;
        }

        return $pieces;
    }

    /**
     * @since 3.5.8
     */
    public function notify_abandoned_payments() {
        global $wpdb;

        $threshold = max( 1, absint( pdl_get_option( 'payment-abandonment-threshold' ) ) );
        $time_for_pending = pdl_format_time( strtotime( "-{$threshold} hours", current_time( 'timestamp' ) ), 'mysql' );
        $notified = get_option( 'pdl-payment-abandonment-notified', array() );

        if ( ! is_array( $notified ) )
               $notified = array();

        // For now, we only notify listings with pending INITIAL payments.
        $to_notify = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pdl_payments WHERE status = %s AND tag = %s AND created_at < %s ORDER BY created_at",
                            'pending',
                            'initial',
                            $time_for_pending )
        );

        foreach ( $to_notify as &$data ) {
            if ( in_array( $data->id, $notified ) )
                continue;

            $payment = PDL_Payment::get( $data->id );

            // Send e-mail.
            $replacements = array(
                'listing' => get_the_title( $payment->get_listing_id() ),
                'link' => sprintf( '<a href="%1$s">%1$s</a>', esc_url( $payment->get_checkout_url() ) )
            );

            $email = pdl_email_from_template( 'email-templates-payment-abandoned', $replacements );
            $email->to[] = wpdirlist_get_the_business_email( $payment->get_listing_id() );
            $email->template = 'plestardirectory-email';
            $email->send();

            $notified[] = $data->id;
        }

        update_option( 'pdl-payment-abandonment-notified', $notified );
    }

}

}
