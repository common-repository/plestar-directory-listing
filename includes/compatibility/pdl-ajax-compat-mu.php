<?php
/*
 * Plugin Name: Plestar Directory Listing - AJAX Compatibility Module
 * Plugin URI: http://www.plestar.net
 * Version: 1.0
 * Author: D. Rodenbaugh
 * Author URI: http://plestar.net
 * License: GPLv2 or any later version
 */

global $pdl_ajax_compat;
$pdl_ajax_compat = true;

// Only activate PDL plugins during PDL-related AJAX requests.
function pdl_ajax_compat_exclude_plugins( $plugins ) {
    if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! isset( $_REQUEST['action'] ) || false === strpos( $_REQUEST['action'], 'pdl' ) )
        return $plugins;

    foreach ( $plugins as $key => $plugin ) {
        if ( false !== strpos( $plugin, 'directory-listing-' ) )
            continue;

        unset( $plugins[ $key ] );
    }

    return $plugins;
}
add_filter( 'option_active_plugins', 'pdl_ajax_compat_exclude_plugins' );
