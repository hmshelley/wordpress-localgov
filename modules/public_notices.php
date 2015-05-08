<?php

namespace localgov;

class PublicNotices_Module {
	
	/**
	 * Class variables
	 */
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
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
		add_action( 'wp', array( $this, 'wp' ) );
		
		add_filter( 'lg_get_archives_default_args', array( $this, 'filter_get_archives_default_args' ), 10, 2 );
		add_filter( 'lg_get_archives_args', array( $this, 'filter_get_archives_args' ) );
	}
	
	public static function filter_get_archives_default_args( $defaults, $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'public_notice' && $args['post_type'] != 'lg_public_notice' )
		) {
			return $defaults;
		}
		
		$defaults['order_by'] = 'lg_public_notice_date DESC, post_title DESC';
		$defaults['date_key'] = 'lg_public_notice_date';
		$defaults['date_type'] = 'timestamp'; 
		
		return $defaults;
	}
	
	public static function filter_get_archives_args( $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'public_notice' && $args['post_type'] != 'lg_public_notice' )
		) {
			return $args;
		}
		
		$args['post_type'] = 'lg_public_notice';
		return $args;
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
			'rewrite' => array(
				'slug' => 'public-notices'
			),
			'supports' => array( 'title' )
		));
		
	}

	function init() {
		self::register_types();
	}
	
	function cmb2_init() {
		
		$public_notice_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'public_notice',
			'title' => __( 'Public Notice', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'public_notice' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$public_notice_metabox->add_field( array(
			'name' => __( 'Public Notice Date', 'localgov' ),
			'id' => LG_PREFIX . 'public_notice_date',
			'type' => 'text_date_timestamp'
		) );
		
		$public_notice_metabox->add_field( array(
			'name' => __( 'Public Notice File', 'localgov' ),
			'id' => LG_PREFIX . 'public_notice_file',
			'type' => 'file'
		) );
		
	}
	
	function wp( $wp ) {
	
		if( 
			!is_singular() 
			|| LG_PREFIX . 'public_notice' != get_post_type()
		) {
			return;
		}
		
		$public_notice_file = get_post_meta( get_the_ID(), LG_PREFIX . 'public_notice_file', true);
		
		if( empty( $public_notice_file ) ) {
			status_header(404);
			include( get_404_template() );
			exit;
		}
		
		header( "Location: $public_notice_file" );
		exit;
	}	
}

PublicNotices_Module::instance();