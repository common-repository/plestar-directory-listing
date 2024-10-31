<?php echo pdl_admin_header( _x( 'Delete Directory Theme', 'themes admin', 'PDM' ), 'theme-delete' ); ?>

<p><?php printf( _x( 'Are you sure you want to delete the directory theme "%s"?', 'themes admin', 'PDM' ),
                 $theme->name ); ?></p>

<form action="" method="post">
    <input type="hidden" name="theme_id" value="<?php echo $theme->id; ?>" />
    <input type="hidden" name="dodelete" value="1" />
    <input type="hidden" name="pdl-action" value="delete-theme" />
    <?php wp_nonce_field( 'delete theme ' . $theme->id ); ?>

    <?php submit_button( _x('Cancel', 'themes admin', 'PDM'), 'secondary', 'cancel', false ); ?>
    <?php submit_button( _x('Delete Directory Theme', 'themes admin', 'PDM'), 'delete', 'delete-theme', false ); ?>
</form>

<?php echo pdl_admin_footer(); ?>
