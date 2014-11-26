<?php

namespace localgov;

class Admin {
	
	private static $instance;
	
	public $landing_page;
	public $theme_settings;
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Admin;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		
		lg_load_class( 'localgov\LandingPage' );
		lg_load_class( 'localgov\ThemeSettings' );
		lg_load_class( 'localgov\NetworkSettings' );
		
		$this->landing_page = LandingPage::instance();
		$this->theme_settings = ThemeSettings::instance();
		$this->network_settings = NetworkSettings::instance();
		
		$menu_slug = 'admin.php?page=localgov-admin';
		
		$this->landing_page->menu_slug = $menu_slug;
		$this->theme_settings->parent_slug = $menu_slug;
		
		add_action( 'admin_menu', array( $this->landing_page, 'add_admin_page' ) );
		add_action( 'after_setup_theme', array( $this->theme_settings ,'add_admin_page' ) );
		
		add_action( 'after_setup_theme', array( $this->network_settings, 'add_admin_page' ) );
	}
}

Admin::instance();
