<h2><?php _ex( 'Checkout', 'checkout', 'PDM') ;?></h2>

<div class="pdl-payment-invoice">
    <?php echo $invoice; ?>
</div>

<form id="pdl-checkout-form" action="" method="POST">
    <input type="hidden" name="payment" value="<?php echo $payment->payment_key; ?>" />
    <input type="hidden" name="action" value="do_checkout" />
    <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />

    <?php echo $checkout_form_top; ?>

    <div class="pdl-checkout-errors pdl-checkout-section">
        <?php if ( ! empty( $errors ) ): ?>
            <?php foreach ( $errors as $error ): ?>
            <div class="pdl-msg error pdl-checkout-error"><?php echo $error; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>


    <div class="pdl-checkout-gateway-selection pdl-checkout-section">
        <h3><?php _ex( 'Select a Payment Method', 'checkout', 'PDM' ); ?></h3>
        <?php foreach ( pdl()->payment_gateways->get_available_gateways( array( 'currency_code' => $payment->currency_code ) ) as $gateway ): ?>
        <label><input type="radio" name="gateway" value="<?php echo $gateway->get_id(); ?>" <?php checked( $chosen_gateway->get_id(), $gateway->get_id() ); ?>/> <?php echo $gateway->get_logo(); ?></label>
        <?php endforeach; ?>
        <div class="pdl-checkout-submit pdl-no-js"><input type="submit" value="<?php _ex( 'Next', 'checkout', 'PDM' ); ?>" /></div>
    </div>
    <!-- end .pdl-checkout-gateway-selection -->

    <div id="pdl-checkout-form-fields" class="pdl-payment-gateway-<?php echo $chosen_gateway->get_id(); ?>-form-fields">
        <?php echo $checkout_form; ?>
    </div>

    <?php echo $checkout_form_bottom; ?>
</form>

