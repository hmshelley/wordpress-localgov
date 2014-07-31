<?php

namespace localgovernment;

class PublicNotices_Module {
	
	private static $instance;
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PublicNotices_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
	
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}
	
	public function register_types() {
		
		register_post_type(LG_PREFIX . 'public_notice', array(
			'labels' => array(
				'name' => __('Public Notices'),
				'singular_name' => __('Public Notice'),
				'all_items' => __('All Public Notices')
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'public-notices'),
			'supports' => array('title')
		));
	}
	
	function init() {
		
		self::register_types();
		
		$public_notice_fields = new \Fieldmanager_Group(array(
			'name' => LG_PREFIX . 'public_notice',
			'children' => array(
				'date' => new \Fieldmanager_Datepicker('Date', array(
					'index' => LG_PREFIX . 'public_notice_date'
				)),
				'public_notice_file' => new \Fieldmanager_Media('Public Notice File')
			)
		));
		
		$public_notice_fields->add_meta_box('Public Notice', array(LG_PREFIX . 'public_notice'));
	}


	function pre_get_posts($query) {
	
		if(
			!is_admin() &&
			$query->is_main_query() &&
			$query->is_post_type_archive(LG_PREFIX . 'public_notice')
		) {
			$query->set('meta_key', LG_PREFIX . 'public_notice_date');
			$query->set('orderby', 'meta_value');
			$query->set('order', 'DESC');
		}
	}
	
}

PublicNotices_Module::instance();