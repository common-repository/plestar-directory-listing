<?php
/*
 * Plugin Name: Plestar Directory Listing
 * Plugin URI: https://www.plestar.net
 * Description: Provides the ability to maintain a free business list on your WordPress site.
 * Version: 1.0
 * Author: Plestar Inc	
 * Author URI: https://www.plestar.net
 * License: GPLv2 or any later version
 *
 */

// Do not allow direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'PDL_PLUGIN_FILE' ) ) {
    define( 'PDL_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists( 'PDL' ) ) {
    require_once( dirname( PDL_PLUGIN_FILE ) . '/includes/class-pdl.php' );
}

/**
 * Returns the main instance of Plestar Directory Listing.
 * @return PDL
 */
function pdl() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new PDL();
    }

    return $instance;
}


// For backwards compatibility.
$GLOBALS['pdl'] = pdl();
