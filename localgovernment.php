<?php
/*
Plugin Name: Local Government
Plugin URI: 
Description: Wordpress plugin designed for local government including towns, cities, and school districts, as well as small groups and organizations. Features include meeting agendas and minutes, newsletters, featured content for homepage, and other functionality commonly used in local government websites. Dependent on Fieldmanager plugin.
Version: 1.0-alpha
Author: Heather Shelley
Author URI: 
License:
*/

define( 'LG_VERSION', '1.0-alpha' );
define( 'LG_BASE_DIR', dirname( __FILE__ ) );
define( 'LG_CUSTOM_DIR', WP_CONTENT_DIR . '/localgovernment' );

if( file_exists( LG_CUSTOM_DIR . '/config.php' ) ) {
	require LG_CUSTOM_DIR . '/config.php';
}

defined( 'LG_PREFIX' )   or define( 'LG_PREFIX', 'lg_' );

/** 
 * Autoload classes
 */
function lg_load_class( $class ) {

	if ( class_exists( $class ) || strpos( $class, 'localgovernment' ) !== 0 ) {
		return;
	}
	
	$filename = str_replace( 'localgovernment\\' , '', $class );
	$filename = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $filename));
		
	$file = LG_BASE_DIR . '/class.' . $filename . '.php';
		
	if ( !file_exists( $file ) ) {
		throw new LG_Class_Not_Found_Exception( $file );
	}
	
	require_once( $file );
}

//if ( function_exists( 'spl_autoload_register' ) ) {
//	spl_autoload_register( 'lg_load_class' );
//}

lg_load_class( 'localgovernment\LocalGovernment' );
register_activation_hook( __FILE__, array( 'localgovernment\LocalGovernment', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'localgovernment\LocalGovernment', 'plugin_deactivation' ) );

add_action( 'plugins_loaded', array( 'localgovernment\LocalGovernment', 'load_modules' ) );
add_action( 'widgets_init', array( 'localgovernment\LocalGovernment', 'load_widgets' ) );

lg_load_class( 'localgovernment\ThemeOptions' );
add_action( 'after_setup_theme', array( 'localgovernment\ThemeOptions', 'setup_page' ) );

/**
 * Exception Class for classes that could not be loaded
 */
class LG_Class_Not_Found_Exception extends Exception { }