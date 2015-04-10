<?php

namespace localgov;

class TemplateOptions_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TemplateOptions_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		add_action( 'init', array( __CLASS__, 'init' ), 30 );
	}

	public static function init() {
				
		// Add meta box with options to override title and excerpt
		$page_template_options_fields = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'template_options',
			'children' => array(
				'page_header' => new \Fieldmanager_Select( __('Page header'), array(
					'options' => array(
						'show' => 'Show',
						'hide' => 'Hide'
					),
					'default_value' => 'show'
				) ),
				'page_width' => new \Fieldmanager_Select( __('Page Width'), array(
					'options' => array(
						'full' => 'Full',
						'fixed' => 'Fixed'
					),
					'default_value' => 'fixed'
				) ),
				'sidebar' => new \Fieldmanager_Select( __('Sidebar'), array(
					'display_if' => array(
						'src' => 'page_width',
						'value' => 'fixed'
					),
					'options' => array(
						'none' => 'None',
						'left' => 'Left',
						'right' => 'Right'
					),
					'default_value' => 'right'
				) ),
				'featured_image' => new \Fieldmanager_Select( __('Featured Image'), array(
					'display_if' => array(
						'src' => 'page_width',
						'value' => 'fixed'
					),
					'options' => array(
						'show' => 'Show',
						'hide' => 'Hide',
					),
					'default_value' => 'show'
				) )
			)
		) );
		
		$page_template_options_fields->add_meta_box( 'Template Options', 'page', 'side' );
		
		// Posts don't have same template options as pages
		$post_template_options_fields = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'template_options',
			'children' => array(
				'featured_image' => new \Fieldmanager_Select( __('Featured Image'), array(
					'options' => array(
						'show' => 'Show',
						'hide' => 'Hide',
					),
					'default_value' => 'show'
				) )
			)
		) );
		
		$post_template_options_fields->add_meta_box( 'Template Options', 'post', 'side' );
	}
}

TemplateOptions_Module::instance();
