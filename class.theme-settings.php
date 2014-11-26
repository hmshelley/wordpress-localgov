<?php

namespace localgov;

class ThemeSettings {
	
	private static $instance;
	
	public $parent_slug;
	
	public $social_types = array(
		'email' => 'Email',
		'facebook' => 'Facebook',
		'flickr' => 'Flickr',
		'google_plus' => 'Google+',
		'instagram' => 'Instagram',
		'linkedin' => 'LinkedIn',
		'pinterest' => 'Pinterest',
		'rss' => 'RSS',
		'twitter' => 'Twitter',
		'youtube' => 'YouTube'
	);
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new ThemeSettings;
		}
		return self::$instance;
	}
	
	public function add_admin_page() {
		
		// Branding
		//	- Logo image
		//	- Favicon
		//	- Copyright text?
		// 
		// Social
		// 	- Social links
		//
		// Contact
		//  - Phone
		//  - Fax
		//  - Address
		// 
		// Archive
		//
		//  Header/Footer Scripts
		// 	- Google Analytics
		// 
		// Menu
		//  - Primary menu depth
		//
		// Featured Content
		//  - Layout: image, slider
		// 
		// Layout
		//  - Sidebar position: left, right
		
		$branding_fields = new \Fieldmanager_Group( array(
			'label' => 'Branding',
			'children' => array(
				'logo' => new \Fieldmanager_Media('Logo Image'),
				'copyright_text' => new \Fieldmanager_Textarea( 'Copyright Text', array(
					'attributes' => array(
						'rows' => '5',
						'cols' => '50'
					)
				) )
			)
		) );
		
		$social_fields = new \Fieldmanager_Group( array(
			'label' => 'Social Media',
			'children' => array(
				'social_links' => new \Fieldmanager_Group( array(
					'limit' => 0,
					'starting_count' => 0,
					'label' => 'New Social Link',
					'sortable' => true,
					'collapsible' => true,
					'label_macro' => array( 'Social Link: %s', 'type'),
					'add_more_label' => 'Add another link',
					'children' => array(
						'type' => new \Fieldmanager_Select( 'Type', array(
							'first_empty' => true,
							'options' => $this->social_types
						) ),
						'url' => new \Fieldmanager_Textfield( 'URL' )
					)
				) )
			)
		) );
		
		$contact_fields = new \Fieldmanager_Group( array(
			'label' => 'Contact Info',
			'children' => array(
				'phone' => new \Fieldmanager_Textfield( 'Phone' ),
				'fax' => new \Fieldmanager_Textfield( 'Fax' ),
				'address_physical' => new \Fieldmanager_Textarea( 'Address', array(
					'attributes' => array(
						'rows' => '5',
						'cols' => '50'
					)
				) ),
				'address_mail' => new \Fieldmanager_Textarea( 'Mailing Address', array(
					'attributes' => array(
						'rows' => '5',
						'cols' => '50'
					)
				) )
					
			)
		) );
		
		$menus_fields = new \Fieldmanager_Group( array(
			'label' => 'Menus',
			'children' => array(
				'primary_menu_depth' => new \Fieldmanager_Select( 'Primary Menu Depth', array(
					'options' => range(1, 3),
					'default_value' => 2
				) )
			)
		) );
		
		$fm = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'theme_options',
			'tabbed' => true,
			'children' => array(
				'branding' => $branding_fields,
				'social' => $social_fields,
				'contact_info' => $contact_fields,
				'menus' => $menus_fields
			)
		) );
		
		$fm->add_submenu_page( $this->parent_slug, 'Theme Settings', 'Theme Settings', 'manage_options', 'localgov-theme' );
	}
}

ThemeSettings::instance();
