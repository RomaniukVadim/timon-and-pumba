<?php
/*
Plugin Name: WP-Cache.com
Plugin URI: http://wp-cache.com/
Description: The easiest and fastest WordPress Cache plugin. WP-Cache.com, it just works!
Version: 1.1.1
Author: Kenth Hagström
Author URI: http://kenthhagstrom.se
License: GNU GPL 3.0
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: wpcache
Domain Path: /lang
*/


/**
 * THERE IS NO WARRANTY FOR THIS PLUGIN, TO THE EXTENT PERMITTED BY APPLICABLE LAW. EXCEPT 
 * WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES PROVIDE THE 
 * PLUGIN "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, 
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
 * PARTICULAR PURPOSE. THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PLUGIN IS 
 * WITH YOU. SHOULD THE PLUGIN PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY 
 * SERVICING, REPAIR OR CORRECTION.
 * 
 */


// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// enqueue css
function wpcache_wp_admin_style(){
    wp_register_style( 'wpcache_wp_admin_css', plugins_url( 'css/style.css' , __FILE__ ), false, '1.0.0' );
    wp_enqueue_style( 'wpcache_wp_admin_css' );
}
add_action('admin_enqueue_scripts', 'wpcache_wp_admin_style');

// Add required includes
require_once(dirname(__FILE__)."/inc/functions.php");
$wpcache = new WPCache();

if(is_admin()){

    // Add options panel
    $wpcache->add_options_panel();

    }else{

    // Start caching
    $wpcache->startCache();

}

// Runs on plugin deactivation
register_deactivation_hook( __FILE__, array('WPCache', 'deactivate') );
 
