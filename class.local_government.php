<?php

namespace localgovernment;

class LocalGovernment {
	
	private static $instance;

	static $modules = array(
		'meetings',
		'newsletters',
		'featured_content',
		'submenus'
	);
	
	static $widgets = array(
		'twitter',
		'submenu'
	);

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new LocalGovernment;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
	
	}
	
	function init() {
		
	}
	
	/**
	 * Hook for plugin activation
	 */
	public static function plugin_activation() {
		
		// Load modules to register the rewrite rules
		self::load_modules();
		
		self::register_types();
		
		flush_rewrite_rules();
	}

	/**
	 * Hook for plugin deactivation
	 */
	public static function plugin_deactivation() {
		
		flush_rewrite_rules();
	}
	
	/**
	 * Register custom post types in active modules
	 */
	public static function register_types() {
		
		$modules = self::get_active_modules();
		
		foreach( $modules as $module ) {
		
			$class = preg_replace('/(?:^|_)(.?)/e',"strtoupper('$1')", $module); 
			$class = "localgovernment\\" . $class . '_Module';
			
			if( method_exists ( $class, 'register_types' ) ) {
				$class::register_types();
			}
		}
	}

	/**
	 * Loads the currently active modules.
	 */
	public static function load_modules() {

		$modules = self::get_active_modules();
		
		foreach ( $modules as $module ) {
		
			$path = self::get_module_path( $module );
			
			if ( !file_exists( $path ) ) {
				throw new \LG_Class_Not_Found_Exception( $path );
			}
		
			require $path;
		}

		do_action( 'lg_modules_loaded' );
	}

	/**
	 * Generate a module's path from its slug.
	 */
	public static function get_module_path( $slug ) {
		
		return LG_BASE_DIR . "/modules/$slug.php";
	}
	
	/**
	 * Get a list of activated modules as an array of module slugs.
	 */
	public static function get_active_modules() {
		/*$active = Jetpack_Options::get_option( 'active_modules' );
		if ( ! is_array( $active ) )
			$active = array();
		if ( is_admin() ) {
			$active[] = 'vaultpress';
		} else {
			$active = array_diff( $active, array( 'vaultpress' ) );
		}
		return array_unique( $active );*/
		
		return self::$modules;
	}

	/**
	 * Loads the currently active widgets.
	 */
	public static function load_widgets() {

		$widgets = self::get_active_widgets();
		
		foreach ( $widgets as $widget ) {
		
			$path = self::get_widget_path( $widget );
			
			if ( !file_exists( $path ) ) {
				
				throw new \LG_Class_Not_Found_Exception( $path );
			}
		
			require $path;
		}
		
	}

	/**
	 * Generate a widget's path from its slug.
	 */
	public static function get_widget_path( $slug ) {
		
		return LG_BASE_DIR . "/widgets/{$slug}_widget.php";
	}
	
	/**
	 * Get a list of activated widgets as an array of widget slugs.
	 */
	public static function get_active_widgets() {		
		return self::$widgets;
	}

}

LocalGovernment::instance();