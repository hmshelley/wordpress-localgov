<?php

namespace localgovernment;

class FeaturedContent_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;
	
	public static $max_posts = 15;
	
	public static $post_types = array( 'post', 'page' );	

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new FeaturedContent_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		add_action( 'after_setup_theme', array( __CLASS__, 'after_setup_theme' ) );
		add_action( 'init', array( __CLASS__, 'init' ), 30 );
	}
	
	public static function after_setup_theme() {
		add_post_type_support( 'post', 'excerpt' );
		add_post_type_support( 'page', 'excerpt' );
	
		add_theme_support( 'post-thumbnails' );
	}
	
	public static function init() {	
		
		add_filter( 'lg_get_featured_posts', array( __CLASS__, 'get_featured_posts' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		
		add_post_type_support( 'page', 'excerpt' );
		
		// Add meta box with options to override title and excerpt
		$featured_content_fields = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'featured_content',
			'children' => array(
				'featured' => new \Fieldmanager_Checkbox( 'Featured in front page slideshow', array(
					'index' => LG_PREFIX . 'featured'
				) ),
				'exclude' => new \Fieldmanager_Checkbox( 'Exclude from article list on front page', array(
					'index' => LG_PREFIX . 'exclude_front_page'
				) ),
				'title' => new \Fieldmanager_Textfield( 'Featured Title (to use instead of page/post title)' ),
				
			)
		) );
		
		$featured_content_fields->add_meta_box( 'Featured Content', self::$post_types );
	}

	public static function get_featured_posts() {
	
		$sticky_post_ids = get_option('sticky_posts');
		
		$options = array(
			'numberposts' => self::$max_posts,
			'post_type' => self::$post_types,
			'meta_key' => LG_PREFIX . 'featured',
			'meta_value' => true,
		);
		
		$sticky = get_posts( array_merge( $options, array(
			'post__in' => $sticky_post_ids
		) ) );
		
		// Query for featured posts.
		$featured = get_posts( array_merge( $options, array(
			'post__not_in' => $sticky_post_ids,
			'numberposts' => self::$max_posts - count($sticky)
		) ) );

		$featured = array_merge( $sticky, $featured );

		return apply_filters( 'lg_featured_posts', $featured );
		
	}


	/**
	 * Exclude featured posts from the blog query when the blog is the front-page.
	 */
	public static function pre_get_posts( $query ) {

		// Bail if not home or not main query.
		if ( ! $query->is_home() || ! $query->is_main_query() ) {
			return;
		}
		
		$excluded = get_posts( array(
			'numberposts' => self::$max_posts,
			'post_type' => self::$post_types,
			'meta_key' => LG_PREFIX . 'exclude_front_page',
			'meta_value' => true,
		) );
		
		// Bail if nothing to exclude
		if ( ! $excluded ) {
			return;
		}
		
		$exclude_ids = wp_list_pluck( (array) $excluded, 'ID' );
		$exclude_ids = array_map( 'absint', $exclude_ids );

		// We need to respect post ids already in the blacklist.
		$post__not_in = $query->get( 'post__not_in' );

		if ( ! empty( $post__not_in ) ) {
			$exclude_ids = array_merge( (array) $post__not_in, $exclude_ids );
			$exclude_ids = array_unique( $exclude_ids );
		}

		$query->set( 'post__not_in', $exclude_ids );
	}
}

FeaturedContent_Module::instance();
