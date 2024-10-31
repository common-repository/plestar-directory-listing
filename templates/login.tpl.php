<?php
$show_message = isset( $show_message ) ? $show_message : true;

$registration_url = trim( pdl_get_option( 'registration-url', '' ) );

if ( ! $registration_url && get_option( 'users_can_register' ) ) {
    if ( function_exists( 'wp_registration_url' ) ) {
        $registration_url = wp_registration_url();
    } else {
        $registration_url = site_url( 'wp-login.php?action=register', 'login' );
    }
}

$registration_url = $registration_url ? add_query_arg( array( 'redirect_to' => urlencode( $redirect_to ) ), $registration_url ) : '';
$lost_password_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), wp_lostpassword_url() );
?>

<div id="pdl-login-view">

    <?php if ( $show_message ): ?>
    <?php echo pdl_render_msg(_x("You are not currently logged in. Please login or register first. When registering, you will receive an activation email. Be sure to check your spam if you don't see it in your email within 60 minutes.", 'templates', 'PDM')); ?>
    <?php endif; ?>

    <div class="pdl-login-options <?php echo $access_key_enabled ? 'options-2' : 'options-1'; ?>">

    <div id="pdl-login-form" class="pdl-login-option">
        <h4><?php _ex( 'Login', 'views:login', 'PDM' ); ?></h4>
        <?php wp_login_form( array( 'redirect' => $redirect_to ) ); ?>

        <p class="pdl-login-form-extra-links">
            <?php if ( $registration_url ): ?>
            <a href="<?php echo esc_url( $registration_url ); ?>"><?php _ex( 'Not yet registered?', 'templates', 'PDM' ); ?></a> | 
            <?php endif; ?>
            <a href="<?php echo esc_url( $lost_password_url ); ?>"><?php _ex( 'Lost your password?', 'templates', 'PDM' ); ?></a>
        </p>
    </div>

    <?php if ( $access_key_enabled ): ?>
    <div id="pdl-login-access-key-form" class="pdl-login-option">
        <h4><?php _ex( '... or use an Access Key', 'views:login', 'PDM' ); ?></h4>
        <p><?php _ex( 'Please enter your access key and e-mail address.', 'views:login', 'PDM' ); ?></p>

        <form action="" method="post">
            <input type="hidden" name="method" value="access_key" />
            <p><input type="text" name="email" value="" placeholder="<?php _ex( 'E-Mail Address', 'views:login', 'PDM'); ?>" /></p>
            <p><input type="text" name="access_key" value="" placeholder="<?php _ex( 'Access Key', 'views:login', 'PDM' ); ?>" /></p>
            <p><input type="submit" value="<?php _ex( 'Use Access Key', 'views:login', 'PDM' ); ?>" /></p>
            <p><a href="<?php echo esc_url( $request_access_key_url ); ?>"><?php _ex( 'Request access key?', 'views:login', 'PDM' ); ?></a></p>
        </form>
    </div>
    <?php endif; ?>
</div>
