<?php echo pdl_admin_header( _x( 'Edit Listing Fee', 'fees admin', 'PDM' ) ); ?>
<?php pdl_admin_notices(); ?>
<?php echo pdl_render_page( PDL_PATH . 'templates/admin/fees-form.tpl.php', array( 'fee' => $fee ) ); ?>
<?php echo pdl_admin_footer(); ?>
