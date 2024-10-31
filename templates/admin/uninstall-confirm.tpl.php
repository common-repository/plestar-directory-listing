<?php echo pdl_admin_header( _x( 'Uninstall Plestar Directory Listing', 'uninstall', 'PDM' ), 'admin-uninstall' ); ?>

<?php pdl_admin_notices(); ?>

<div id="pdl-uninstall-messages">
    <div id="pdl-uninstall-warning">
        <div class="pdl-warning-margin">
            <p><span class="dashicons dashicons-warning"></span></p>
        </div>
        <div class="pdl-warning-content">
            <p><?php _ex( 'Uninstalling Plestar Directory Listing will do the following:', 'uninstall', 'PDM' ); ?></p>

            <ul>
                <li><?php _ex( 'Remove ALL directory listings', 'uninstall', 'PDM' ); ?></li>
                <li><?php _ex( 'Remove ALL directory categories', 'uninstall', 'PDM' ); ?></li>
                <li><?php _ex( 'Remove ALL directory settings', 'uninstall', 'PDM' ); ?></li>
                <li><?php _ex( 'Remove ALL premium module configuration data (regions, maps, ratings, featured levels)', 'uninstall', 'PDM' ); ?></li>
                <li><?php _ex( 'Deactivate the plugin from the file system', 'uninstall', 'PDM' ); ?></li>
            </ul>

            <p><?php _ex( 'ONLY do this if you are sure you\'re OK with LOSING ALL OF YOUR DATA.', 'uninstall', 'PDM' ); ?></p>
        </div>

        <a id="pdl-uninstall-proceed-btn" class="button"><?php _ex( 'Yes, I want to uninstall', 'uninstall', 'PDM' ); ?></a>
    </div>

    <div id="pdl-uninstall-reinstall-suggestion">
        <p><?php _ex( 'If you just need to reinstall the plugin, please do the following:', 'uninstall', 'PDM' ); ?></p>

        <ul>
            <li><?php echo str_replace( '<a>', '<a href="' . admin_url( 'plugins.php?plugin_status=active' ) . '">', _x( 'Go to <a>Plugins->Installed Plugins', 'uninstall', 'PDM' ) ); ?></a></li>
            <li><?php _ex( 'Click on "Deactivate" for Plestar Directory Listing. Wait for this to finish', 'uninstall', 'PDM' ); ?></li>
            <li><?php _ex( 'Click on "Delete" for Plestar Directory Listing. <i>THIS OPERATION IS SAFE--your data will NOT BE LOST doing this</i>', 'uninstall', 'PDM' ); ?></li>
            <li><?php _ex( 'Wait for the delete to finish', 'uninstall', 'PDM' ); ?></li>
            <li><?php _ex( 'The plugin is now removed, but your data is still present inside of your database.', 'uninstall', 'PDM' ); ?></li>
            <li><?php echo str_replace( '<a>', '<a href="' . admin_url( 'plugin-install.php' ) . '">', _x( 'You can reinstall the plugin again under <a>Plugins->Add New</a>', 'uninstall', 'PDM' ) ); ?></li>
        </ul>

        <a href="<?php echo admin_url( 'plugins.php' ); ?>" class="button"><?php _ex( 'Take me to the <b>Plugins</b> screen', 'uninstall', 'PDM' ); ?></a>
    </div>
</div>

<?php echo pdl_render_page( PDL_PATH . 'templates/admin/uninstall-capture-form.tpl.php' ); ?>

<?php echo pdl_admin_footer(); ?>
