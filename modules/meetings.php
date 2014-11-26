<?php

namespace localgov;

class Meetings_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Meetings_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}
	
	public function register_types() {
				
		add_rewrite_tag( '%' . LG_PREFIX . 'meeting_year%', '([0-9]{4})' );
	
		// Set up URL rewrites -- needs to happen before post type is registered
		// Note: Paging and feeds will not work on meeting group listing because meeting group regex is too generic
		/*$meeting_group_regex = '(.+?)';
		$meeting_year_regex = '([0-9]{4})';
		add_rewrite_tag( '%' . LG_PREFIX . 'meeting_slug%', '(meeting)s', 'post_type=' . LG_PREFIX );
		add_rewrite_tag( '%' . LG_PREFIX . 'meeting_group%', $meeting_group_regex );
		add_rewrite_tag( '%' . LG_PREFIX . 'meeting_year%', $meeting_year_regex );
		add_permastruct( 'meeting_archive', '%' . LG_PREFIX . 'meeting_slug%/%' . LG_PREFIX . 'meeting_group%/%' . LG_PREFIX . 'meeting_year%/%postname%' );*/
		
		register_taxonomy( LG_PREFIX . 'meeting_type', LG_PREFIX . 'meeting', array(
			'label' => __( 'Meeting Types' ),
			'rewrite' => array( 'slug' => 'meetings/types' )
		) );
	
		register_post_type( LG_PREFIX . 'meeting', array(
			'labels' => array(
				'name' => __( 'Meetings' ),
				'singular_name' => __( 'Meeting' ),
				'all_items' => __( 'All Meetings' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'meetings'
			),
			'supports' => array( 'title' )
		) );
	}
	
	public function init() {
		
		self::register_types();
		
		$meeting_fields = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'meeting',
			'children' => array(
				'type' => new \Fieldmanager_Select( 'Type', array(
					'datasource' => new \Fieldmanager_Datasource_Term( array(
						'taxonomy' => LG_PREFIX . 'meeting_type'
					) ),
					'index' => LG_PREFIX . 'meeting_type'
				) ),
				'date' => new \Fieldmanager_Datepicker( 'Date', array(
					'index' => LG_PREFIX . 'meeting_date',
					'use_time' => true
				) ),
				'description' => new \Fieldmanager_Textfield( 'Description' ),
				'agenda_file' => new \Fieldmanager_Media( 'Agenda File' ),
				'minutes_file' => new \Fieldmanager_Media( 'Minutes File' ),
				'files' => new \Fieldmanager_Group( array(
					'limit' => 0,
					'label' => 'Meeting File',
					'label_macro' => array( 'Meeting File: %s', 'title' ),
					'add_more_label' => 'Add Another Meeting File',
					'children' => array(
						'title' => new \Fieldmanager_Textfield( 'Title' ),
						'file' => new \Fieldmanager_Media( 'File' )
					)
				) )
			)
		) );
		
		$meeting_fields->add_meta_box( 'Meeting', array( LG_PREFIX . 'meeting' ) );
	
		add_filter( 'wp_unique_post_slug', function( $slug ) {
		
			if ( LG_PREFIX . 'meeting' != get_post_type() ) {
				return $slug;
			}

			$post = get_post();
			
			// Just create friendly slug on first save
			if ( !empty($post->post_name) ) {
				return $slug;
			}
			
			// Only change slug if type or date provided
			if( 
				empty( $_POST[LG_PREFIX . 'meeting']['type'] )
				&& empty( $_POST[LG_PREFIX . 'meeting']['date'] )
			) {
				return $slug;
			}
			
			$slug = '';
			
			$meeting = $_POST[LG_PREFIX . 'meeting'];
			
			if( !empty( $meeting['type'] ) ) {
				$term = get_term_by( 'id', $meeting['type'], LG_PREFIX . 'meeting_type' );
				
				if( !empty($term->slug) ) {
					$slug .= $term->slug;
				}
			}
			
			if( !empty( $meeting['date']['date']) ) {
				
				if( !empty($slug) ) {
					$slug .= '-';
				}
				
				$slug .= date( 'Y-m-d', strtotime($meeting['date']['date']) );
			}
	
			return $slug;
		} );
		
		// Add meeting date to title
		add_filter( 'the_title', function( $title ) {
			
			if( 
				!in_the_loop()
				|| get_post_type() != LG_PREFIX . 'meeting'
			) {
				return $title;
			}
			
			$meeting_date = get_post_meta( get_the_ID(), LG_PREFIX . 'meeting_date' );
			
			if( !empty( $meeting_date[0] ) ) {
				$title .= ' - ' . date( get_option( 'date_format'), $meeting_date[0] ); 
			}
			
			return $title;
			
		} );
		
		//flush_rewrite_rules();
		//global $wp_rewrite;
		//die(var_dump($wp_rewrite));
		
	}

	public function pre_get_posts($query) {
	
		if(
			is_admin()
			|| !$query->is_main_query()
			|| !(
				$query->is_post_type_archive( LG_PREFIX . 'meeting' )
				|| $query->is_tax( LG_PREFIX . 'meeting_type' )
			)
		) {
			return;
		}
		
		// Remove limit
		$query->set( 'posts_per_page', '-1' );
		
		// Meeting listing
		if( 
			$query->get( LG_PREFIX . 'meeting_type' ) 
			&& $query->get( LG_PREFIX . 'meeting_year' )
		) {
				
			add_filter( 'posts_where', function( $where ) {
				global $wpdb;
				
				$year = get_query_var( LG_PREFIX . 'meeting_year' );
				
				$where .= ' AND YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . '.meta_value)) = ' . $year;
				return $where;
			} );
			
			$query->set( 'meta_key', LG_PREFIX . 'meeting_date' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'DESC' );	
		}
		// Year listing
		else if(
			$query->get( LG_PREFIX . 'meeting_type' )
		) {
			$query->set( 'meta_key', LG_PREFIX . 'meeting_date' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			
			add_filter( 'posts_groupby', function( $groupby ) {
				global $wpdb;
			
				$groupby = 'YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . ".meta_value))";
				return $groupby;
			} );

		}
		// Meeting type listing
		else {
			
			$query->set( 'meta_key', LG_PREFIX . 'meeting_type' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			
			add_filter( 'posts_groupby', function( $groupby ) {
				global $wpdb;
			
				$groupby = $wpdb->postmeta . '.meta_value';
				return $groupby;
			} );
			
		}
	}
	
}

Meetings_Module::instance();