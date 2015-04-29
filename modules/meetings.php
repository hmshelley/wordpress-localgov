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
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
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
			'hierarchical' => true,
			'show_admin_column' => true,
			'rewrite' => array( 
				'slug' => 'meetings/types',
				'hierarchical' => true
			)
			
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
	
		add_filter( 'wp_unique_post_slug', function( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		
			if ( LG_PREFIX . 'meeting' != get_post_type() ) {
				return $slug;
			}

			$post = get_post();
			
			// Just create friendly slug on first save
			if ( !empty( $post->post_name ) ) {
				return $slug;
			}
			
			// Only change slug if type and date provided
			if( 
				empty( $_POST['tax_input'][LG_PREFIX . 'meeting_type'][1] )
				|| empty( $_POST[LG_PREFIX . 'meeting_date'] ) 
			) {
				return $slug;
			}
			
			$slug = '';
			
			$term_id = $_POST['tax_input'][LG_PREFIX . 'meeting_type'][1];
			$term = get_term( $term_id, LG_PREFIX . 'meeting_type' );
			
			if( !empty( $term->slug ) ) {
				$slug .= $term->slug . '-';
			}
			
			if( !empty( $_POST[LG_PREFIX .'meeting_date']['date']) ) {
								
				$slug .= date( 'Y-m-d', strtotime( $_POST[LG_PREFIX .'meeting_date']['date'] ) );
			}
	
			return $slug;
		}, 10, 6 );
		
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
		
	}
	
	function cmb2_init() {
	
		$meeting_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'meeting',
			'title' => __( 'Meeting', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'meeting' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$meeting_metabox->add_field( array(
			'name' => __( 'Date', 'localgov' ),
			'id' => LG_PREFIX . 'meeting_date',
			'type' => 'text_datetime_timestamp'
		) );
		
		$meeting_metabox->add_field( array(
			'name' => __( 'Description', 'localgov' ),
			'id' => LG_PREFIX . 'meeting_description',
			'type' => 'text'
		) );
		
		$meeting_metabox->add_field( array(
			'name' => __( 'Agenda File', 'localgov' ),
			'id' => LG_PREFIX . 'meeting_agenda_file',
			'type' => 'file'
		) );
		
		$meeting_metabox->add_field( array(
			'name' => __( 'Minutes File', 'localgov' ),
			'id' => LG_PREFIX . 'meeting_minutes_file',
			'type' => 'file'
		) );
		
		$files_group_id = $meeting_metabox->add_field( array(
			'name' => __( 'Files', 'localgov' ),
			'id' => LG_PREFIX . 'meeting_files',
			'type' => 'group',
			'options' => array(
				'group_title' => __( 'Meeting File {#}', 'localgov' ),
				'add_button' => __( 'Add Another Meeting File', 'localgov' ),
				'remove_button' => __( 'Remove Meeting File', 'localgov' ),
				'sortable' => true
			)
		) );
		
		$meeting_metabox->add_group_field( $files_group_id, array(
			'name' => 'Title',
			'id' => 'title',
			'type' => 'text'
		) );
		
		$meeting_metabox->add_group_field( $files_group_id, array(
			'name' => 'File',
			'id' => 'file',
			'type' => 'file'
		) );
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