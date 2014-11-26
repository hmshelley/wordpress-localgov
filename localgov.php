<?php
/*
Plugin Name: LocalGov
Plugin URI: 
Description: Wordpress plugin designed for local government including towns, cities, and school districts, as well as small groups and organizations. Features include meeting agendas and minutes, newsletters, featured content for homepage, and other functionality commonly used in local government websites. Dependent on Fieldmanager plugin.
Version: 1.0-alpha
Author: Heather Shelley
Author URI: 
License:
*/

define( 'LG_VERSION', '1.0-alpha' );
define( 'LG_BASE_DIR', dirname( __FILE__ ) );
define( 'LG_CUSTOM_DIR', WP_CONTENT_DIR . '/localgov' );

if( file_exists( LG_CUSTOM_DIR . '/config.php' ) ) {
	require LG_CUSTOM_DIR . '/config.php';
}

defined( 'LG_PREFIX' )   or define( 'LG_PREFIX', 'lg_' );

/** 
 * Autoload classes
 */
function lg_load_class( $class ) {

	if ( class_exists( $class ) ) { //|| strpos( $class, 'localgov' ) !== 0 ) {
		return;
	}
	
	$filename = str_replace( 'localgov\\' , '', $class );
	$filename = str_replace( 'FM_' , 'fm-', $filename );
	$filename = str_replace( '_' , '-', $filename );
	$filename = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $filename));
		
	$file = LG_BASE_DIR . '/class.' . $filename . '.php';
		
	if ( !file_exists( $file ) ) {
		throw new LG_Class_Not_Found_Exception( $file );
	}
	
	require_once( $file );
}

//if ( function_exists( 'spl_autoload_register' ) ) {
//	spl_autoload_register( 'lg_load_class' );
//}

lg_load_class( 'localgov\Localgov' );

register_activation_hook( __FILE__, array( 'localgov\Localgov', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'localgov\Localgov', 'plugin_deactivation' ) );

add_action( 'plugins_loaded', array( 'localgov\Localgov', 'load_modules' ) );		
add_action( 'widgets_init', array( 'localgov\Localgov', 'load_widgets' ) );

if ( is_admin() ) {
	lg_load_class( 'localgov\Admin' );
}


require 'template_tags.php';

/**
 * Exception Class for classes that could not be loaded
 */
class LG_Class_Not_Found_Exception extends Exception { }