<?php

namespace localgov;

class Newsletters_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Newsletters_Module;
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
			|| ( $args['post_type'] != 'newsletter' && $args['post_type'] != 'lg_newsletter' )
		) {
			return $defaults;
		}
		
		$defaults['order_by'] = 'lg_newsletter_date DESC, post_title DESC';
		$defaults['date_key'] = 'lg_newsletter_date';
		$defaults['date_type'] = 'timestamp'; 
		
		return $defaults;
	}
	
	public static function filter_get_archives_args( $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'newsletter' && $args['post_type'] != 'lg_newsletter' )
		) {
			return $args;
		}
		
		$args['post_type'] = 'lg_newsletter';
		return $args;
	}

	public function register_types() {
	
		register_post_type(LG_PREFIX . 'newsletter', array(
			'labels' => array(
				'name' => __('Newsletters'),
				'singular_name' => __('Newsletter'),
				'all_items' => __('All Newsletters')
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'newsletters'
			),
			'supports' => array( 'title' )
		));
		
	}

	function init() {
		self::register_types();
	}
	
	function cmb2_init() {
		
		$newsletter_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'newsletter',
			'title' => __( 'Newsletter', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'newsletter' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$newsletter_metabox->add_field( array(
			'name' => __( 'Newsletter Date', 'localgov' ),
			'id' => LG_PREFIX . 'newsletter_date',
			'type' => 'text_date_timestamp'
		) );
		
		$newsletter_metabox->add_field( array(
			'name' => __( 'Newsletter File', 'localgov' ),
			'id' => LG_PREFIX . 'newsletter_file',
			'type' => 'file'
		) );
		
		$files_group_id = $newsletter_metabox->add_field( array(
			'name' => __( 'Files', 'localgov' ),
			'id' => LG_PREFIX . 'newsletter_files',
			'type' => 'group',
			'options' => array(
				'group_title' => __( 'Related File {#}', 'localgov' ),
				'add_button' => __( 'Add Another File', 'localgov' ),
				'remove_button' => __( 'Remove File', 'localgov' ),
				'sortable' => true
			)
		) );
		
		$newsletter_metabox->add_group_field( $files_group_id, array(
			'name' => 'Title',
			'id' => 'title',
			'type' => 'text'
		) );
		
		$newsletter_metabox->add_group_field( $files_group_id, array(
			'name' => 'File',
			'id' => 'file',
			'type' => 'file'
		) );
		
	}
	
	function wp( $wp ) {
	
		if( 
			!is_singular() 
			|| LG_PREFIX . 'newsletter' != get_post_type()
		) {
			return;
		}
		
		$newsletter = get_post_meta( get_the_ID(), LG_PREFIX . 'newsletter');
		
		if( empty( $newsletter[0]['newsletter_file'] ) ) {
			status_header(404);
			include( get_404_template() );
			exit;
		}
		
		$url = wp_get_attachment_url( $newsletter[0]['newsletter_file'] );
		
		header( "Location: $url" );
		exit;
	}	
}

Newsletters_Module::instance();