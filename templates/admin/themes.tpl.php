<?php
echo pdl_admin_header( null, 'themes', array(
    array( _x( 'Upload Directory Theme', 'themes', 'PDM' ), esc_url( add_query_arg( 'action', 'theme-install' ) ) ),
    array( _x( 'Manage Theme Tags', 'form-fields admin', 'PDM' ), esc_url( 'admin.php?page=pdl_admin_formfields&action=updatetags') ),
    array( _x( 'Settings', 'themes', 'PDM' ), esc_url( admin_url( 'admin.php?page=pdl_settings&tab=appearance&subtab=themes' ) ) ),
), true );

echo pdl_admin_notices();
?>

<div class="pdl-note">
<?php
echo str_replace( '<a>',
                  '<a href="http://plestar.net/premium-themes/" target="_blank" rel="noopener">',
                  _x( '<a><b>Directory Themes</b></a> are pre-made templates for the <i>Plestar Directory Listing</i> to change the look of the directory quickly and easily. We have a number of them available for purchase <a>here</a>.', 'themes', 'PDM' ) ); ?><br />
<?php echo _x( 'They are <strong>different</strong> than your regular WordPress theme and they are <strong>not</strong> a replacement for WP themes either. They will change the look and feel of your directory only.', 'themes', 'PDM' ); ?>
</div>

<div id="pdl-theme-selection" class="pdl-theme-selection cf">
<?php foreach ( $themes as &$t ): ?>
    <?php echo pdl_render_page( PDL_PATH . 'templates/admin/themes-item.tpl.php', array( 'theme' => $t ) ); ?>
<?php endforeach; ?>
</div>

<?php
echo pdl_admin_footer();
?>
