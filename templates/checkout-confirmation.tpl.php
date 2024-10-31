<?php
switch ( $payment->status ):
case 'completed':
    echo pdl_get_option( 'payment-message' );
    break;
case 'on-hold':
    echo pdl_render_msg( _x( 'Your payment is on hold. Please contact the admin if you need further details.', 'checkout', 'PDM' ) );
    break;
case 'failed':
    echo pdl_render_msg( _x( 'Your payment was rejected. Please contact the admin for further details.', 'checkout', 'PDM' ), 'error' );
    break;
case 'canceled':
    echo pdl_render_msg( sprintf( _x( 'The payment (#%s) was canceled at your request.', 'checkout', 'PDM' ), $payment->id ) );
    break;
case 'pending':
    echo '<p>';
    _ex( 'Your payment is awaiting verification by the gateway.', 'checkout', 'PDM' );
    echo '</p>';
    echo pdl_render_msg( _x( 'Verification usually takes some minutes. This page will automatically refresh if there\'s an update.', 'checkout', 'PDM' ) );
    break;
default:
    wp_die();
endswitch
?>

<?php if ( 'canceled' != $payment->status ): ?>
<div id="pdl-checkout-confirmation-receipt">
    <?php echo pdl()->payments->render_receipt( $payment ); ?>
</div>
<?php endif; ?>

<?php if ( 'pending' == $payment->status ): ?>
<script type="text/javascript">
setTimeout(function() {
    location.reload();
}, 5000 );
</script>
<?php endif; ?>
