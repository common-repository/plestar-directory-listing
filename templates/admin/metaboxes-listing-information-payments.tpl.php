<!-- {{ Recent payments. -->
<div id="pdl-listing-metabox-payments" class="pdl-listing-metabox-tab pdl-admin-tab-content" tabindex="2">
    <?php if ( $payments ): ?>
        <?php _ex( 'Click a transaction to see its details (and approve/reject).', 'listing metabox', 'PDM' ); ?>

            <?php foreach ( $payments as $payment ): ?>
            <?php $payment_link = esc_url( admin_url( 'admin.php?page=pdl_admin_payments&pdl-view=details&payment-id=' . $payment->id ) ); ?>
            <div class="pdl-payment-item pdl-payment-status-<?php echo $payment->status; ?> cf">
                <div class="pdl-payment-item-row">
                    <div class="pdl-payment-date">
                        <a href="<?php echo $payment_link; ?>">#<?php echo $payment->id; ?> - <?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->created_at ) ); ?></a>
                    </div>
                    <div class="pdl-payment-status"><span class="tag paymentstatus <?php echo $payment->status; ?>"><?php echo $payment->status; ?></span></div>
                </div>
                <div class="pdl-payment-item-row">
                    <div class="pdl-payment-summary"><a href="<?php echo $payment_link; ?>" title="<?php echo esc_attr( $payment->summary ); ?>"><?php echo $payment->summary; ?></a></div>
                    <div class="pdl-payment-total"><?php echo pdl_currency_format( $payment->amount ); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
    <?php else: ?>
        <?php _ex( 'No payments available.', 'listing metabox', 'PDM' ); ?>
    <?php endif; ?>
</div>
<!-- }} -->
