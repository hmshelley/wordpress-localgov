<?php

namespace localgov;

class Submenus_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Submenus_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		add_filter( 'wp_nav_menu_objects', array( $this, 'filter_nav_menu_objects' ), 10, 2 );
	}
	
	public function filter_nav_menu_objects( $items, $args ) {
	
		if ( empty( $args->lg_submenu ) ) {
			return $items;
		}
		
		$max_depth = 0;
		if( !empty( $args->lg_max_depth ) ) {
			$max_depth = $args->lg_max_depth;
		}
		
		$start_depth = 0;
		if( !empty( $args->lg_start_depth ) ) {
			$start_depth = $args->lg_start_depth;
		}
		
		$current = array_pop( wp_filter_object_list( $items, array( 
			'current' => true
		) ) );
		
		$current_depth = 0;
		$parent = $current;
		while( $parent ) {
			$parent = array_pop( wp_filter_object_list( $items, array( 'ID' => $parent->menu_item_parent ) ) );
			$ancestors[] = $parent;
			$current_depth++;
		}
	
		if( $current_depth < $start_depth ) {
			return array();
		}

		$siblings = wp_filter_object_list( $items, array( 
			'menu_item_parent' => $current->menu_item_parent
		) );
		
		if( $current_depth == $start_depth) {
			$siblings = array();
		}

		if( $max_depth != 0 && $current_depth >= $max_depth ) {
			$ancestors = array_reverse( $ancestors );
			
			$parent_siblings = wp_filter_object_list( $items, array( 'menu_item_parent' => $ancestors[$max_depth - 1]->menu_item_parent ) );
			
			return $parent_siblings + $siblings;
		}
		
		$children = wp_filter_object_list( $items, array( 'menu_item_parent' => $current->ID ) );
		
		if( !empty( $children ) ) {
			
			if( ( $max_depth - 1) == $current_depth ) {
				
				return $siblings + $children;
			}
			
			return $children;
		}
		
		return $siblings;
	}
}

Submenus_Module::instance();
