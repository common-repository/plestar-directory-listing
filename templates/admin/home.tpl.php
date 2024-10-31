<?php echo pdl_admin_header(); ?>

<div class="pdl-note welcome-message">
    <h4><?php printf( _x( 'Welcome to Plestar Directory Listing. You are using %s.', 'admin home', 'PDM' ), '<span class="version">' . pdl_get_version() . '</span>' ); ?></h4>
    <p><?php _ex( 'Thanks for choosing us.  There\'s a lot you probably want to get done, so let\'s jump right in!',
                  'admin home',
                  'PDM' ); ?></p>
    <ul>
        <li>
            <?php echo str_replace( '<a>', '<a href="https://plestar.net/knowledge-base/" target="_blank" rel="noopener">',
                                    _x( 'Our complete documentation is <a>here</a> which we encourage you to use while setting things up.', 'admin home', 'PDM' ) ); ?>
        <li>
            <?php echo str_replace( '<a>', '<a href="http://plestar.net/quick-start-guide/" target="_blank" rel="noopener">',
                                    _x( 'We have some quick-start scenarios that you will find useful regarding setup and configuration <a>here</a>.', 'admin home', 'PDM' ) ); ?>
        </li>
        <li>
            <?php echo str_replace( '<a>', '<a href="http://plestar.net/support-forum/" target="_blank" rel="noopener">',
                                    _x( 'If you have questions, please post a comment on <a>support forum</a> and we\'ll answer it within 24 hours most days.', 'admin home', 'PDM' ) ); ?>

    </ul>
</div>

<ul class="shortcuts">
    <li>
        <a href="<?php echo admin_url( 'admin.php?page=pdl_settings' ); ?>" class="button"><?php _e( 'Manage Options', 'PDM' ); ?></a>
    </li>
    <li>
        <a href="<?php echo admin_url( 'admin.php?page=pdl_admin_formfields' ); ?>" class="button"><?php _e( 'Manage Form Fields', 'PDM' ); ?></a>
    </li>
    <li>
        <a href="<?php echo admin_url( 'admin.php?page=pdl-admin-fees' ); ?>" class="button"><?php _e( 'Manage Fees', 'PDM' ); ?></a>
    </li>
    <li class="clear"></li>


    <?php if ( pdl_get_option( 'payments-on' ) ): ?>
    <li>
        <a href="<?php echo admin_url( sprintf( 'edit.php?post_type=%s&pdmfilter=unpaid', PDL_POST_TYPE ) ) ?>" class="button"><?php _e( 'Manage Paid Listings', 'PDM' ); ?></a>
    </li>
    <?php endif; ?>
</ul>

<?php echo pdl_admin_footer(); ?>
