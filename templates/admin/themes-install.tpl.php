<?php
echo pdl_admin_header( _x( 'Upload Directory Theme', 'themes', 'PDM' ), 'themes-install', array() );
?>
<?php echo pdl_admin_notices(); ?>

<div class="pdl-note">
<p><?php
printf( _x( 'This is a theme or skin from %s and is NOT a regular WordPress theme.', 'themes', 'PDM' ),
        '<a href="http://plestar.net/premium-themes/">http://plestar.net/premium-themes/</a>' );
?></p>
</div>

<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="pdl-action" value="upload-theme" />
    <?php wp_nonce_field( 'upload theme zip' ); ?>

    <table class="form-table">
        <tbody>
            <tr>
                <th>
                    <?php _ex( 'PDL Theme archive (ZIP file)', 'themes', 'PDM' ); ?>
                </th>
                <td>
                    <input type="file" name="themezip" />
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( _x( 'Begin Upload', 'themes', 'PDM' ), 'primary', 'begin-theme-upload' ); ?>
</form>

<?php
echo pdl_admin_footer();
?>

