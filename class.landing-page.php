<?php

namespace localgov;

class LandingPage {
	
	private static $instance;
	
	public $menu_slug;
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new LandingPage;
		}
		return self::$instance;
	}
	
	public function add_admin_page( ) {
		
		add_menu_page( __('LocalGov'), __('LocalGov'), 'manage_options', $this->menu_slug, array($this, 'render_page'), null, 62.5 );
	}
	
	public static function render_page() {
		echo '<h2>LocalGov</h2>';
	}
}

LandingPage::instance();
