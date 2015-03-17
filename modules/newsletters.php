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
			'hierarchical' => false,
			'rewrite' => array('slug' => 'newsletters'),
			'supports' => array('title')
		));
	}

	function init() {
	
		self::register_types();
		
		localgov_load_class( 'FM_Month_Year' );
		
		$fm = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'newsletter',
			'children' => array(
				'date' => new \FM_Month_Year('Newsletter Date', array(
					'index' => LG_PREFIX . 'newsletter_date'
				)),
				'newsletter_file' => new \Fieldmanager_Media('Newsletter File'),
				'files' => new \Fieldmanager_Group( array(
					'limit' => 0,
					'label' => 'Related File',
					'label_macro' => array( 'File: %s', 'title' ),
					'add_more_label' => 'Add Another File',
					'children' => array(
						'title' => new \Fieldmanager_Textfield( 'Title' ),
						'file' => new \Fieldmanager_Media( 'File' )
					)
				) )
			)
		));
		
		$fm->add_meta_box('Newsletter', array( LG_PREFIX . 'newsletter') );
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