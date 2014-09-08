<?php

namespace localgovernment;

class Shortcodes_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Shortcodes_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		
		add_shortcode( 'currentdate', array( __CLASS__, 'current_date' ) );
		add_shortcode( 'archives', array( __CLASS__, 'archives' ) );
		add_shortcode( 'lgarchives', array( __CLASS__, 'lgarchives' ) );
		
	}
	
	public static function current_date( $atts ) {
		
		$defaults = array(
			'format' => get_option( 'date_format' )
		);
		
		$atts = shortcode_atts( $defaults, $atts, 'lg' );
		
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
		
		foreach($atts as $key => $att) {
			if( isset( $args_key_map[$key] ) ) {
				$args[$args_key_map[$key]] = $att;
			}
		}
		
		return wp_get_archives( $args );
	}
	
	public static function lgarchives( $atts ) {
		
		$args_key_map = array(
			'type' => 'type',
			'posttype' => 'post_type',
			'limit' => 'limit',
			'orderby' => 'order_by',
			'datekey' => 'date_key',
			'datevalue' => 'date_value',
			'groupposts' => 'group_posts',
			'grouporder' => 'group_order',
			'template' => 'template'
		);
		
		$args = array();
		
		foreach($atts as $key => $att) {
			if( isset( $args_key_map[$key] ) ) {
				$args[$args_key_map[$key]] = $att;
			}
		}
		
		return lg_get_archives( $args );
	}
}

Shortcodes_Module::instance();
