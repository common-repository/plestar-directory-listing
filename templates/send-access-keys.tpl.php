
<form class="pdl-form" action="" method="post">
    <?php wp_nonce_field( 'request_access_keys' ); ?>
    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( esc_url( $redirect_to ) ); ?>" />

    <div class="pdl-form-row pdl-form-textfield">
        <label for="pdl-listing-email"><?php _ex( 'Enter your e-mail address', 'send-access-keys', 'PDM' ); ?></label>
        <input type="text" name="email" id="pdl-listing-email">
    </div>

    <p><input class="submit" type="submit" value="<?php _ex( 'Continue', 'send-access-keys', 'PDM' ); ?>" /></p>
</form>
