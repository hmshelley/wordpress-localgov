<?php

function lg_get_archives( $args ) {
	
	global $wpdb, $wp_locale, $post;
	
	$defaults = array (
		'type' => 'yearly',
		'post_type' => 'post',
		'limit' => '',
		'order_by' => 'post_date DESC',
		'date_key' => 'post_date',
		'date_value' => 'post_date',
		'group_posts' => '',
		'group_order' => '',
		'template' => LG_BASE_DIR . '/templates/archive.php'
	);
	
	/**
	 * Filter the default args
	 * 
	 * @param array  $defaults	An array of default args
	 * @param array  $args	An array of user-provided args
	 */
	$defaults = apply_filters( 'lgarchives_default_args', $defaults , $args );
	
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * Filter the shortcode attributes
	 * 
	 * @param array  $args	An array of user-provided attributes
	 */
	$args = apply_filters( 'lgarchives_args', $args );

	$join = "";
	
	$where = "WHERE post_type = '$args[post_type]' AND post_status = 'publish'";

	$order_by = $args['order_by'];
	
	$date_col = 'post_date';
	if( !empty( $args['date_key'] ) ) {
	
		$join = "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$args[date_key]')";
		
		$date_col = 'meta_value';
		if( !empty($args['date_value']) ) {
			$date_col = $args['date_value'];
		}
	}

	$limit = '';
	if ( !empty( $args['limit'] ) ) {
		$limit = ' LIMIT ' . absint( $args['limit'] );
	}

	if ( 'yearly' == $args['type'] ) {
		$query = "SELECT *, $date_col AS `date_col`, YEAR($date_col) AS `year` FROM $wpdb->posts $join $where GROUP BY YEAR($date_col) ORDER BY $order_by $limit";
		
		$results = $wpdb->get_results( $query );
		
		$output = '';
		if ( $results ) {
			foreach ( (array) $results as $result) {
				$url = get_year_link( $result->year );
				$text = sprintf( '%d', $result->year );
				$output .= get_archives_link( $url, $text );
			}
		}
	} elseif ( 'postbypost' == $args['type'] ) {
		
		$group_posts = $args['group_posts'];
		$group_order = strtoupper( $args['group_order'] );
		
		$group_interval_col = '';
		if( 'academicyear' == $group_posts ) {
			$group_interval_col = ", CONCAT( YEAR($date_col-INTERVAL 7 MONTH), '-', 1+YEAR($date_col-INTERVAL 7 MONTH)) AS `group_interval`";
		}
		
		$query = "SELECT *, $date_col AS `date_col` $group_interval_col FROM $wpdb->posts $join $where ORDER BY $order_by $limit";
	
		$posts = $wpdb->get_results( $query );
		
		$grouped_results = array( $posts );
		
		if( !empty( $args['group_posts'] ) ) {
			
			$grouped_results = array();
			
			foreach( $posts as $post ) {
			
				$key = '';
				if( 'year' == $args['group_posts'] ) {
					$key = date('Y', strtotime( $post->date_col ) );
				}
				else if( 'academicyear' == $args['group_posts'] ) {
					$key = $post->group_interval;
				}
				else if( isset($result->$group_posts) ) {
					$key = $post->$group_posts;
				}
				
				$grouped_results[$key][] = $post;
			}
			
			// Change order of grouped results
			if( $group_order == 'ASC' ) {
				ksort( $grouped_results );
			}
			else if( $group_order == 'DESC' ) {
				krsort( $grouped_results );	
			}
		}
		
		ob_start();
		include $args['template'];
		return ob_get_clean();
	}
}