<?php
if ( pdl_starts_with( $note->actor, 'user' ) ) {
    $author = get_user_meta( (int) str_replace( 'user:', '', $note->actor ), 'nickname', true );
} else {
    $author = $note->actor;
}

$date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $note->created_at ) );
?>
<div class="pdl-payment-note" data-id="<?php echo $note->id; ?>">

    <?php if ( $note->log_type == 'payment.note' ): ?>
    <a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=pdl_admin_ajax&handler=payments__delete_note&note=' . $note->id . '&payment_id=' . $payment_id ) ); ?>" class="pdl-admin-delete-link pdl-admin-confirm" data-confirm="<?php _ex( 'Are you sure you want to delete this note?', 'payments admin', 'PDM' ); ?>"><?php _ex( 'Delete', 'payments admin', 'PDM' ); ?></a>
    <?php endif; ?>
    <div class="pdl-payment-note-meta">
        <span class="pdl-payment-note-meta-user"><?php echo $author; ?></span>
        <span class="sep"> - </span>
        <span class="pdl-payment-note-meta-date"><?php echo $date; ?></span>
    </div>

    <div class="pdl-payment-note-text">
        <?php echo esc_html( $note->message ); ?>
    </div>

</div>

