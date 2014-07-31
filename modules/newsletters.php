<?php

namespace localgovernment;

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
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	public function register_types() {
		
		add_rewrite_tag( '%' . LG_PREFIX . 'newsletter_year%', '([0-9]{4})' );
	
		// Set up URL rewrites -- needs to happen before post type is registered
		/*$year_regex = '([0-9]{4})';
		add_rewrite_tag( '%' . LG_PREFIX . 'newsletter_year%', $year_regex );
		add_rewrite_rule( 'newsletters/' . $year_regex . '/?$', 'index.php?post_type=' . LG_PREFIX . 'newsletter&' . LG_PREFIX . 'newsletter_year=$matches[1]', 'top' );*/
		
		// Change page title
		add_filter( 'post_type_archive_title', function( $title ) {
			
			if( get_query_var( LG_PREFIX . 'newsletter_year' ) ) {
				$title = $title . ' - ' . get_query_var( LG_PREFIX . 'newsletter_year' );
			}
			
			return $title;
			
		} );
	
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
	
		$months = array(
			'1' => 'January',
			'2' => 'February',
			'3' => 'March',
			'4' => 'April',
			'5' => 'May',
			'6' => 'June',
			'7' => 'July',
			'8' => 'August',
			'9' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
		
		$years = range(date('Y', strtotime('+1 year')), date('Y', strtotime('-50 years')));
		
		$fm = new \Fieldmanager_Group(array(
			'name' => LG_PREFIX . 'newsletter',
			'children' => array(
				'month' => new \Fieldmanager_Select('Month', array(
					'options' => $months, 
					'default_value' => date('n'),
					'index' => LG_PREFIX . 'newsletter_month'
				)),
				'year' => new \Fieldmanager_Select('Year', array(
					'options' => $years, 
					'default_value' => date('Y'),
					'index' => LG_PREFIX . 'newsletter_year'
				)),
				'newsletter_file' => new \Fieldmanager_Media('Newsletter File')
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
	
	function pre_get_posts( $query ) {
		
		if( 
			is_admin()
			|| !$query->is_main_query()
			|| !$query->is_post_type_archive( LG_PREFIX . 'newsletter' )
		) {
			return;
		}
		
		// Get custom fields for sorting
		add_filter( 'posts_join', function( $join ) {
		
			global $wpdb;
			
			$join = "INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '" . LG_PREFIX . "newsletter_year') INNER JOIN $wpdb->postmeta AS mt1 ON ($wpdb->posts.ID = mt1.post_id AND mt1.meta_key = '" . LG_PREFIX . "newsletter_month')";
			
			return $join;
		} );
	
		// Sort by custom fields
		add_filter( 'posts_orderby', function( $orderby ) {
		
			global $wpdb;
		
			$orderby = "$wpdb->postmeta.meta_value+0 DESC, mt1.meta_value+0 DESC";
			
			return $orderby;
		} );
		
		// Remove limit
		$query->set( 'posts_per_page', '-1' );
		
		// Filter by year if provided
		$newsletter_year = get_query_var( LG_PREFIX . 'newsletter_year' );
		
		if( !empty( $newsletter_year )) {
			
			$meta_query = array(
				array(
					'value' => $newsletter_year,
					'compare' => '='
				)
			);
			$query->set( 'meta_query', $meta_query );
		}
		// Listing by year
		else {

			add_filter( 'posts_groupby', function( $groupby ) {
				global $wpdb;
			
				$groupby = "$wpdb->postmeta.meta_value";
				return $groupby;
			} );
		}
	}
}

Newsletters_Module::instance();