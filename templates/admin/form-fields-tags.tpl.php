<?php
function _fields_dropdown( $name, $field_id, $fixed = false ) {
?>
    <select name="<?php echo $name; ?>" <?php echo ( $fixed ? 'disabled="disabled"' : '' ); ?> >
        <option value=""><?php _ex( '-- None --', 'form-fields admin', 'PDM' ); ?></option>
        <?php foreach ( pdl_get_form_fields() as $f ): ?>
            <option value="<?php echo $f->get_id(); ?>" <?php selected( $field_id, $f->get_id() ); ?> ><?php echo esc_attr( $f->get_label() ); ?></option>
        <?php endforeach; ?>
    </select>
<?php
}
?>

<?php echo pdl_admin_header( _x( 'Theme Tags', 'form-fields admin', 'PDM' ) ); ?>
<?php pdl_admin_notices(); ?>

<?php if ( $missing_fields ): ?>
<div class="pdl-note">
<?php _ex( 'Before you create fields, make sure you\'ve mapped all of your EXISTING ones first, otherwise you\'ll appear to be "missing data" on your listings.',
           'form-fields admin',
           'PDM' ); ?><br /><br />
<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=pdl-themes&pdl-action=create-theme-suggested-fields' ), 'create_suggested_fields' ); ?>" class="button"><?php _ex( 'Create Missing Fields', 'form-fields admin', 'PDM'); ?></a>
</div>
<?php endif; ?>

<form action="" method="post">
    <table class="form-table">
    <!--    <thead>
            <tr>
                <th><?php _ex( 'Field Tag', 'form-fields admin', 'PDM' ); ?></th>
                <th><?php _ex( 'Field', 'form-fields admin', 'PDM' ); ?></th>
            </tr>
        </thead>-->

        <tbody>
            <?php foreach ( $field_tags as $ft ): ?>
            <tr>
                <th scope="row">
                    <?php echo $ft['description']; ?>
                </th>
                <td>
                    <?php _fields_dropdown( 'field_tags[' . $ft['tag'] . ']', $ft['field_id'], $ft['fixed'] ); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php submit_button(); ?>
</form>

<?php echo pdl_admin_footer(); ?>
