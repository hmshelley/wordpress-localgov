<?php

namespace { // global code
	function lg_show_on_page_only( $field ) {
		return 'page' == get_post_type();
	}
}

namespace localgov {

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
		add_action( 'cmb2_init', array( __CLASS__, 'cmb2_init' ) );
	}
	
	public function cmb2_init() {
		
		$template_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'template_options',
			'title' => __( 'Template Options', 'localgov' ),
			'object_types' => array( 'page', 'post' ),
			'context' => 'side', 
			'priority' => 'low',
			'show_names' => true
		) );
		
		$template_metabox->add_field( array(
			'name' => __( 'Page Header', 'localgov' ),
			'id' => LG_PREFIX . 'template_page_header',
			'type' => 'select',
			'default' => 'show', 
			'options' => array(
				'show' => __( 'Show', 'localgov' ),
				'hide' => __('Hide', 'localgov' )
			),
			'show_on_cb' => 'lg_show_on_page_only'
		) );
		
		$template_metabox->add_field( array(
			'name' => __( 'Page Width', 'localgov' ),
			'id' => LG_PREFIX . 'template_page_width',
			'type' => 'select',
			'default' => 'fixed', 
			'options' => array(
				'full' => __( 'Full', 'localgov' ),
				'fixed' => __( 'Fixed', 'localgov' )
			),
			'show_on_cb' => 'lg_show_on_page_only'
		) );
		
		$template_metabox->add_field( array(
			'name' => __( 'Sidebar (Fixed Width Only)', 'localgov' ),
			'id' => LG_PREFIX . 'template_sidebar',
			'type' => 'select',
			'default' => 'show', 
			'options' => array(
				'show' => __( 'Show', 'localgov' ),
				'hide' => __('Hide', 'localgov' )
			),
			'show_on_cb' => 'lg_show_on_page_only'
		) );
		
		$template_metabox->add_field( array(
			'name' => __( 'Featured Image', 'localgov' ),
			'id' => LG_PREFIX . 'template_featured_image',
			'type' => 'select',
			'default' => 'show', 
			'options' => array(
				'show' => __( 'Show', 'localgov' ),
				'hide' => __('Hide', 'localgov' )
			)
		) );
		
	}
	
}

TemplateOptions_Module::instance();

}