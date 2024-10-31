<?php
echo pdl_admin_header( __( 'Plestar Directory Listing - Reset Defaults', 'PDM' ),
                         'admin-settings',
                         array( array( _x( 'â†� Return to "Manage Options"', 'settings', 'PDM' ),
                                       admin_url( 'admin.php?page=pdl_settings' ) )
                              ) );
?>

<div class="pdl-note warning">
    <?php _e( 'Use this option if you want to go back to the original factory settings for PDL.', 'PDM' ); ?>
    <b><?php _e( 'Please note that all of your existing settings will be lost.', 'PDM' ); ?></b>
    <br/>
    <?php _e( 'Your existing listings will NOT be deleted doing this.', 'PDM' ); ?>
</div>

<form action="" method="POST">
    <input type="hidden" name="pdl-action" value="reset-default-settings" />
    <?php wp_nonce_field( 'reset defaults' ); ?>
	<?php echo submit_button( __( 'Reset Defaults', 'PDM' ), 'delete button-primary' ); ?>
</form>

<?php
	echo pdl_admin_footer();
?>
