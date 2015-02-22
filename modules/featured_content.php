<?php

namespace localgov;

class FeaturedContent_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;
	
	public static $max_posts = 15;
	
	public static $post_types = array( 'post', 'page', 'lg_directory_member' );	

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
		
		add_filter( 'lg_get_featured_posts', array( __CLASS__, 'get_featured_posts' ), 10, 1 );

		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		
		add_post_type_support( 'page', 'excerpt' );
		
		register_taxonomy_for_object_type( 'category', 'page' );
		
		// Add meta box with options to override title and excerpt
		$featured_content_fields = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'featured_content',
			'children' => array(
				'featured' => new \Fieldmanager_Checkbox( __('Featured in content slider on front page'), array(
					'index' => LG_PREFIX . 'featured'
				) ),
				'featured_categories' => new \Fieldmanager_Checkbox( __('Featured in content sliders for  categories'), array(
					'index' => LG_PREFIX . 'featured_categories'
				) ),
				'exclude' => new \Fieldmanager_Checkbox( __('Exclude from front page and archives'), array(
					'index' => LG_PREFIX . 'featured_exclude'
				) ),
				'title' => new \Fieldmanager_Textfield( __('Featured Title (Post/Page title is used if not specified)'), array(
					'index' => LG_PREFIX . 'featured_title'
				) ),
				'show_more_link' => new \Fieldmanager_Checkbox( __('Show "Read More" link'), array(
					'default_value' => true
				) )
			)
		) );
		
		$featured_content_fields->add_meta_box( 'Featured Content', self::$post_types );
	}

	public static function get_featured_posts( $options = array() ) {
			
		$args = array(
			'numberposts' => self::$max_posts,
			'post_type' => self::$post_types,
			'meta_key' => LG_PREFIX . 'featured',
			'meta_value' => true
		);
		
		if( !empty( $options['category_name'] ) ) {
			$args['category_name'] = $options['category_name'];
			$args['meta_key'] = LG_PREFIX . 'featured_categories';
		}
		
		$sticky_post_ids = get_option('sticky_posts');
		$sticky_posts = array();
		
		if( !empty( $sticky_post_ids ) ) {
		
			$sticky_posts = get_posts( array_merge( $args, array(
				'post__in' => $sticky_post_ids
			) ) );
			
			$args['post__not_in'] = $sticky_post_ids;
			$args['numberposts'] = self::$max_posts - count($sticky_posts);
		}
		
		// Query for featured posts
		$featured_posts = get_posts( $args );

		$featured_posts = array_merge( $sticky_posts, $featured_posts );

		return apply_filters( 'lg_featured_posts', $featured_posts );
		
	}


	/**
	 * Exclude featured posts from the blog query when the blog is the front-page.
	 */
	public static function pre_get_posts( $query ) {

		// Bail if admin or not main query.
		if ( 
			is_admin()
			|| ! $query->is_main_query()
			|| is_page()
		
		) {
			return;
		}
		
		$excluded = get_posts( array(
			'numberposts' => self::$max_posts,
			'post_type' => self::$post_types,
			'meta_key' => LG_PREFIX . 'featured_exclude',
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
