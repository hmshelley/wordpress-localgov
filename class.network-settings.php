<?php

namespace localgov;

class NetworkSettings {
	
	private static $instance;
	
	public $parent_slug;
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new NetworkSettings;
		}
		return self::$instance;
	}
	
	public function add_admin_page() {
		
		$theme_fields = new \Fieldmanager_Group( array(
			'label' => 'Network Theme',
			'children' => array(
				'logo' => new \Fieldmanager_Media('Logo')
			)
		) );
		
		$fm = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'network_options',
			'tabbed' => true,
			'children' => array(
				'theme' => $theme_fields
			)
		) );
		
		lg_load_class( 'FM_Context_Submenu_Network' );
		
		$submenu = new \FM_Context_Submenu_Network('settings.php', 'LocalGov Settings', 'LocalGov Settings', 'manage_options', 'localgov-network', $fm, true );
		add_action( 'network_admin_menu', array( $submenu, 'register_submenu_page' ) );
	}
}

NetworkSettings::instance();
