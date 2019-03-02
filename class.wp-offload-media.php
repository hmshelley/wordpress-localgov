<?php

namespace localgov;
	
class WpOffloadMedia {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WpOffloadMedia;
			self::$instance->setup();
		}
		return self::$instance;
	}
	/**
	 *
	 */
	public function setup( ) {
		
		add_filter( 'get_post_metadata',  array( $this, 'filter_post_metadata'), 10, 4 );
	}
	
	/**
	 * Filter URLS so Amazon S3 URLS will be used instead
	 */
	public function filter_post_metadata( $value, $post_id, $meta_key, $single ) {
		
		if ( 
			isset( $meta_key ) 
			&& preg_match('/^lg_(.*)?file$/', $meta_key)
			&& $single
		) {
			global $as3cf;
			
			$file_id = get_post_meta( $post_id, $meta_key . '_id', true );
			
			$provider = $as3cf->is_attachment_served_by_provider( $file_id, false );
			
			if( $provider ) {
				$url = $as3cf->get_attachment_provider_url( $file_id, $provider );
	
				return array( $url );
			}
		} else if(
			isset( $meta_key ) 
			&& preg_match('/^lg_(.*)?files$/', $meta_key)
			&& $single
		) {
			global $as3cf;
			
			// Get value since no value passed in
			remove_filter( 'get_post_metadata', array( $this, 'filter_post_metadata'), 10 );
			$files = get_post_meta( $post_id, $meta_key, true );
			add_filter( 'get_post_metadata',  array( $this, 'filter_post_metadata'), 10, 4 );
			
			if( !empty( $files ) ) {
			
				if( is_string( $files ) ) {
					$files = unserialize( $files );
				}
				
				foreach( $files as &$file ) {
				
					$provider = $as3cf->is_attachment_served_by_provider( $file['file_id'], false );
				
					if( $provider ) {
				
						$url = $as3cf->get_attachment_provider_url( $file['file_id'], $provider );
				
						$file['file'] = $as3cf->get_attachment_provider_url( $file['file_id'], $provider );
					}
				}
			
				return array( $files );
				
			}			
		}
		
		return $value;
	}
	
}

// Check for global variable from Offload Media plugin
if( 
	array_key_exists( 'aws_meta', $GLOBALS ) 
	&& array_key_exists( 'amazon-s3-and-cloudfront', $GLOBALS['aws_meta'] )
){
	WpOffloadMedia::instance();
}