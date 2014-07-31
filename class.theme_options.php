<?php

namespace localgovernment;

class ThemeOptions {
	
	private static $instance;
	
	public static $social_types = array(
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
			self::$instance = new ThemeOptions;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
	}
	
	public function setup_page() {
	
		// http://www.paulund.co.uk/theme-options-page
		// General
		//	- Logo image
		//	- Logo text
		//	- Favicon
		// 	- Google Analytics?
		//	- Custom CSS
		// 
		// Social
		// 	- Social links
		//  - 
		//
		// Branding/Styling/Skin/Design:
		// 	- primary color/secondary color
		//	- custom header image
		//	- custom font?
		// 
		// Homepage
		// - Jumbotron/slideshow type
		// 
		// Layout?
		
		$general_fields = new \Fieldmanager_Group( array(
			'label' => 'General',
			'children' => array(
				'logo' => new \Fieldmanager_Media('Logo'),
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
							'options' => self::$social_types
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
		
		$fm = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'theme_options',
			'tabbed' => true,
			'children' => array(
				'general' => $general_fields,
				'social' => $social_fields,
				'contact_info' => $contact_fields
			)
		) );
		$fm->add_submenu_page( 'themes.php', 'Local Government Options', 'Local Government', 'manage_options', 'localgovernment' );
	}
}

ThemeOptions::instance();
