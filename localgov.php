<?php
/*
Plugin Name: LocalGov
Plugin URI: 
Description: Wordpress plugin designed for local government including towns, cities, and school districts, as well as small groups and organizations. Features include meeting agendas and minutes, newsletters, featured content for homepage, and other functionality commonly used in local government websites.
Version: 1.0-alpha
Author: Heather Shelley
Author URI: 
Text Domain: localgov
Network: true
License:
*/

define( 'LG_VERSION', '1.0-alpha' );
define( 'LG_BASE_DIR', dirname( __FILE__ ) );
define( 'LG_CUSTOM_DIR', WP_CONTENT_DIR . '/localgov' );

if( file_exists( LG_CUSTOM_DIR . '/config.php' ) ) {
	require LG_CUSTOM_DIR . '/config.php';
}

defined( 'LG_PREFIX' )   or define( 'LG_PREFIX', 'lg_' );
defined( 'LG_ADMIN_MENU_SLUG' )	or define( 'LG_ADMIN_MENU_SLUG', 'localgov-site-settings' );

/**
 * Exception Class for classes that could not be loaded
 */
class LG_Class_Not_Found_Exception extends Exception { }

/** 
 * Load classes
 */
function localgov_load_class( $class ) {

	if ( class_exists( $class ) ) {
		return;
	}
	
	$filename = str_replace( 'localgov\\' , '', $class );
	$filename = str_replace( '_' , '-', $filename );
	$filename = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $filename));
		
	$file = LG_BASE_DIR . '/class.' . $filename . '.php';
		
	if ( !file_exists( $file ) ) {
		throw new LG_Class_Not_Found_Exception( $file );
	}
	
	require_once( $file );
}

localgov_load_class( 'localgov\Localgov' );

register_activation_hook( __FILE__, array( 'localgov\Localgov', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'localgov\Localgov', 'plugin_deactivation' ) );

if ( is_admin() ) {

	require_once  __DIR__ . '/cmb2/init.php';
	
	localgov_load_class( 'localgov\SiteSettings' );
	localgov_load_class( 'localgov\Settings' );
	localgov_load_class( 'localgov\NetworkSettings' );
}
	
require_once __DIR__ . '/template_tags.php';

localgov\Localgov::load_modules();

localgov_load_class( 'localgov\Shortcodes' );

add_action( 'widgets_init', array( 'localgov\Localgov', 'load_widgets' ) );