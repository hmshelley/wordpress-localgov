<?php

namespace localgov;

class Shortcodes {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Shortcodes;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		
		add_shortcode( 'currentdate', array( __CLASS__, 'current_date' ) );
		add_shortcode( 'archives', array( __CLASS__, 'archives' ) );
		add_shortcode( 'lgarchives', array( __CLASS__, 'lgarchives' ) );
		add_shortcode( 'lgdirectory', array( __CLASS__, 'lgdirectory' ) );
		add_shortcode( 'lgfeatured', array( __CLASS__, 'lgfeatured' ) );
		add_shortcode( 'lgsubmenu', array( __CLASS__, 'lgsubmenu' ) );
	}
	
	public static function current_date( $atts ) {
		
		$defaults = array(
			'format' => get_option( 'date_format' )
		);
		
		$atts = shortcode_atts( $defaults, $atts, 'localgov' );
		
		return date( $atts['format'] );
	}
	
	public static function archives( $atts ) {
		
		$args_key_map = array(
			'type' => 'type',
			'limit' => 'limit',
			'format' => 'format',
			'before' => 'before',
			'after' => 'after',
			'postcount' => 'show_post_count',
			'order' => 'order'
		);
		
		$args = array();
		
		if ( !empty( $atts) ) {
			foreach($atts as $key => $att) {
				if( isset( $args_key_map[$key] ) ) {
					$args[$args_key_map[$key]] = $att;
				}
			}
		}
		
		return wp_get_archives( $args );
	}
	
	public static function lgarchives( $atts ) {
		
		$args_key_map = array(
			'type' => 'type',
			'posttype' => 'post_type',
			'format' => 'format',
			'contentformat' => 'content_format',
			'limit' => 'limit',
			'orderby' => 'order_by',
			'order' => 'order',
			'postgroupby' => 'post_group_by',
			'postgrouporder' => 'post_group_order',
			'postgroupoffset' => 'post_group_offset',
			'template' => 'template',
			'paging' => 'paging'
		);
		
		$args = array();
		
		if ( !empty( $atts) ) {
			foreach( $atts as $key => $att ) {
				if( isset( $args_key_map[$key] ) ) {
					$args[$args_key_map[$key]] = $att;
				}
			}
		}
		
		return lg_get_archives( $args );
	}
	
	public static function lgdirectory( $atts ) {
		
		$defaults = array(
			'fields' => 'name, phone, email',
			'headers' => true
		);
		$atts = shortcode_atts( $defaults, $atts, 'lgdirectory' );
		
		$args = array(
			'template_options' => array(
				'fields' => $atts['fields'],
				'show_headers' => $atts['headers']
			)
		);
		
		if( !empty( $atts['fields'] ) ) {
			$args['template_options']['fields'] = preg_split("/[\s,]+/", $atts['fields'] );
		}
		
		if( $atts['headers'] === 'no' ) {
			$args['template_options']['show_headers'] = false;	
		}
		
		return lg_get_directory( $args );
	}
	
	public static function lgfeatured ( $atts ) {
		
		$args_key_map = array(
			'template' => 'template',
			'category' => 'category_name'
		);
		
		$args = array();
		
		if ( !empty( $atts) ) {
			foreach( $atts as $key => $att ) {
				if( isset( $args_key_map[$key] ) ) {
					$args[$args_key_map[$key]] = $att;
				}
			}
		}
		
		return lg_get_featured( $args );
	}
	
	public static function lgsubmenu( $atts ) {
	
		$args_key_map = array(
			'startdepth' => 'start_depth',
			'maxdepth' => 'max_depth'
		);
		
		$args = array();
		
		if ( !empty( $atts) ) {
			foreach( $atts as $key => $att ) {
				if( isset( $args_key_map[$key] ) ) {
					$args[$args_key_map[$key]] = $att;
				}
			}
		}
		
		return lg_submenu( $args );
	
	}
}

Shortcodes::instance();
