<?php

namespace {

function lg_get_featured_posts( $options ) {

	$featured_posts = localgov\Featured_Module::get_featured_posts( $options );

	return apply_filters( 'lg_featured_posts', $featured_posts );
}


$lg_featured_id = 0;

function lg_get_featured( $args = array() ) {
		
	global $lg_featured_id;
	$lg_featured_id++;
	
	$defaults = array (
		'template' => LG_BASE_DIR . '/templates/featured_slider.php',
		'category_name' => ''
	);
	
	/**
	 * Filter the default args
	 * 
	 * @param array  $defaults
	 * @param array  $args
	 */
	$defaults = apply_filters( 'lg_get_featured_default_args', $defaults , $args );
	
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * Filter the args
	 * 
	 * @param array  $args
	 */
	$args = apply_filters( 'lg_get_featured_args', $args );
	
	$options = array( 
		'category_name' => $args['category_name']
	);
	
	$featured_posts = lg_get_featured_posts( $options );
	
	ob_start();
	include $args['template'];
	return ob_get_clean();
}

}

namespace localgov {

class Featured_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;
	
	public static $max_posts = 15;
	
	public static $post_types = array( 'post', 'page', 'lg_directory_member', 'tribe_events' );	

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Featured_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		add_action( 'after_setup_theme', array( __CLASS__, 'after_setup_theme' ) );
		add_action( 'init', array( __CLASS__, 'init' ), 30 );
		add_action( 'cmb2_init', array( __CLASS__, 'cmb2_init' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
	}
	
	public static function after_setup_theme() {
		add_post_type_support( 'post', 'excerpt' );
	
		add_theme_support( 'post-thumbnails' );
	}
	
	public static function init() {
		
		register_taxonomy( LG_PREFIX . 'featured_category', self::$post_types, array(
			'label' => __( 'Featured Categories' ),
			'hierarchical' => true,
			'show_admin_column' => true
		) );
		
	}
	
	public function cmb2_init() {
	
		$featured_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'featured',
			'title' => __( 'Featured Content', 'localgov' ),
			'object_types' => self::$post_types,
			'context' => 'normal', 
			'priority' => 'low',
			'show_names' => true
		) );
		
		$featured_metabox->add_field( array(
			'name' => __( 'Featured on' ),
			'id' => LG_PREFIX . 'featured_categories',
			'type' => 'taxonomy_multicheck',
			'taxonomy' => LG_PREFIX . 'featured_category'
		) );
		
		$featured_metabox->add_field( array(
			'name' => __( 'Featured Title' ),
			'id' => LG_PREFIX . 'featured_title',
			'type' => 'text'
		) );
		
		$featured_metabox->add_field( array(
			'name' => __( 'Featured Excerpt' ),
			'id' => LG_PREFIX . 'featured_excerpt',
			'type' => 'wysiwyg',
			'options' => array(
				'textarea_rows' => 5
			)
		) );
		
		$featured_metabox->add_field( array(
			'name' =>  __('"Read More" link'),
			'id' => LG_PREFIX . 'featured_more_link',
			'type' => 'select',
			'default' => 'show', 
			'options' => array(
				'show' => __( 'Show', 'localgov' ),
				'hide' => __('Hide', 'localgov' )
			)
		) );
		
		$featured_metabox->add_field( array(
			'name' => __( 'Exclude from home page news and archives' ),
			'id' => LG_PREFIX . 'featured_exclude',
			'type' => 'checkbox'
		) );
		
	}

	public static function get_featured_posts( $options = array() ) {
		
		$args = array(
			'numberposts' => self::$max_posts,
			'post_type' => self::$post_types,
			'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'tax_query' => array(
				array(
					'taxonomy' => LG_PREFIX . 'featured_category',
					'field' => 'slug',
					'terms' => 'front-page',
					'operator' => 'IN'
				)
			)
		);
		
		if ( !empty( $options['category_name'] ) ) {
			$args['tax_query'][0]['terms'] = $options['category_name'];
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
		
		return $featured_posts;
	}

	/**
	 * Exclude featured posts from the blog query when the blog is the front-page.
	 */
	public static function pre_get_posts( $query ) {
		
		// Bail if not archives or home
		if ( 
			!isset( $query->lg_is_archives )
			&& (
				is_admin()
				|| !$query->is_main_query()
				|| !$query->is_home()
			)
		) {
			return;
		}
		
		$excluded = get_posts( array(
			'numberposts' => self::$max_posts,
			'post_type' => self::$post_types,
			'meta_key' => LG_PREFIX . 'featured_exclude',
			'meta_value' => 'on'
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

Featured_Module::instance();

}
