<?php
	echo pdl_admin_header(_x('Delete Form Field', 'form-fields admin', 'PDM'));
?>

<p>
	<?php echo sprintf(_x('Are you sure you want to delete the "%s" field?', 'form-fields admin', 'PDM'), $field->get_label()); ?>
</p>

<form action="" method="POST">
	<input type="hidden" name="id" value="<?php echo $field->get_id(); ?>" />
	<input type="hidden" name="doit" value="1" />
	<?php submit_button(_x('Delete Field', 'form-fields admin', 'PDM'), 'delete'); ?>
</form>

<?php
	echo pdl_admin_footer();
?>