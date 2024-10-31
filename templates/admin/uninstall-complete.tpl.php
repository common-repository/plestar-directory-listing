<?php
	echo pdl_admin_header(_x('Uninstall Plestar Directory Listing', 'admin uninstall', 'PDM'));
?>

<?php pdl_admin_notices(); ?>

<p><?php _ex("Uninstall completed.", 'admin uninstall', "PDM"); ?></p>
<p><a href="<?php echo admin_url(); ?>"><?php _ex('Return to Dashboard.', 'admin uninstall', 'PDM'); ?></p>

<?php
	echo pdl_admin_footer();
?>