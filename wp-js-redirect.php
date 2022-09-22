<?php

/*
 * Plugin Name:     JS Conditional URL Redirection
 * Version:         1.1.4
 * Plugin URI:       
 * Description:     Conditional redirect url plugin.
 * Author:          JSDigital
 * Author URI:      https://jsdigital.tech/
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 */
// Create a helper function for easy SDK access.
function jsdigital_wcr_fs()
{
    global  $jsdigital_wcr_fs ;
     
    
    return $jsdigital_wcr_fs;
}

// Init Freemius.
jsdigital_wcr_fs();
// Signal that SDK was initiated.
do_action( 'jsdigital_wcr_fs_loaded' );
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Load plugin class files
require_once 'includes/class-wp-js-redirect.php';
require_once 'includes/class-wp-js-redirect-settings.php';
// Load plugin libraries
require_once 'includes/lib/class-wp-js-redirect-admin-api.php';
require_once 'includes/lib/class-wp-js-redirect-logs-api.php';
/**
 * Returns the main instance of WP_JS_Redirect to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_JS_Redirect
 */
function WP_JS_Redirect()
{
    $instance = WP_JS_Redirect::instance( __FILE__, '1.0.0' );
    if ( is_null( $instance->settings ) ) {
        $instance->settings = WP_JS_Redirect_Settings::instance( $instance );
    }
    return $instance;
}

WP_JS_Redirect();