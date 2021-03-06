<?php

/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
function lg_paging_nav( $query = null ) {

	$query = isset( $query ) ? $query : $GLOBALS['wp_query'];

	// Don't print empty markup if there's only one page.
	if ( $query->max_num_pages < 2 ) {
		return;
	}

	$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$query_args   = array();
	$url_parts    = explode( '?', $pagenum_link );

	if ( isset( $url_parts[1] ) ) {
		wp_parse_str( $url_parts[1], $query_args );
	}

	$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
	$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

	$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

	// Set up paginated links.
	$links = paginate_links( array(
		'type'     => 'list',
		'base'     => $pagenum_link,
		'format'   => $format,
		'total'    => $query->max_num_pages,
		'current'  => $paged,
		'mid_size' => 1,
		'add_args' => array_map( 'urlencode', $query_args ),
		'prev_text' => __( '&larr;', 'localgov' ),
		'next_text' => __( '&rarr;', 'localgov' ),
	) );

	if ( $links ) :
	?>
	<nav class="paging-nav" role="navigation">
		<h3 class="sr-only"><?php _e( 'Posts navigation', 'localgov' ); ?></h3>
		<div class="pagination loop-pagination">
			<?php echo $links; ?>
		</div><!-- .pagination -->
	</nav><!-- .paging-nav -->
	<?php
	endif;
}

function lg_get_breadcrumbs() {
	
	$html = '<ol class="breadcrumb">';
	$html .= '<li><a href="' . get_home_url() . '"><span class="icon-home"></span></a></li>';
	
	if( is_single() && get_post_type() == 'post' ) {
		$html .= '<li>' . get_the_category_list( ', ' ) . '</li>';
	}
	elseif( is_page() ) {
	
		$post = get_post();
		$ancestors = get_post_ancestors( $post );
		
		foreach( array_reverse( $ancestors ) as $ancestor ) {
			$html .= '<li><a href="' . get_permalink( $ancestor ) . '" title="' . get_the_title( $ancestor ) . '">' . get_the_title( $ancestor ) . '</a></li>';
		}
	}
	elseif( is_search() ) {
		$html .= '<li>Search Results</li>';
	}
	elseif( get_post_type() ) {
		$post_type = get_post_type_object( get_post_type() );
		$html .= '<li><a href="' . get_post_type_archive_link( get_post_type() ) . '">' . $post_type->label . '</a></li>';
		
		if( get_post_type() == LG_PREFIX . 'newsletter' ) {
		
			if( get_query_var( LG_PREFIX . 'newsletter_year' ) ) {
				$newsletter_year = get_query_var( LG_PREFIX . 'newsletter_year' );
				$url = get_post_type_archive_link( LG_PREFIX . 'newsletter' );
				$url = add_query_arg( array( LG_PREFIX . 'newsletter_year' => $newsletter_year ), $url );
				$html .= '<li><a href="' . $url . '">' . $newsletter_year . '</a></li>';
			}
		}
		else if( get_post_type() == LG_PREFIX . 'meeting' ) {
			
			$type_term = '';
			$meeting_year = '';
			
			if( is_single() ) {
				$meeting = get_post_meta( get_the_ID(), LG_PREFIX . 'meeting', true );
				
				if( !empty( $meeting['type'] ) ) {
					$type_term = get_term_by( 'id', $meeting['type'] , LG_PREFIX . 'meeting_type' );
				}
				
				if( !empty( $meeting['date'] ) ) {
					$meeting_year = date( 'Y', $meeting['date'] );
				}
				
			}
			elseif( get_query_var( LG_PREFIX . 'meeting_type' ) ) {
				$type_term = get_term_by( 'slug', get_query_var( LG_PREFIX . 'meeting_type' ), LG_PREFIX . 'meeting_type' );
				
				if( get_query_var( LG_PREFIX . 'meeting_year' ) ) {
					$meeting_year = get_query_var( LG_PREFIX . 'meeting_year' );
				}
			}
			
			if( !empty($type_term) ) {
				$url = get_term_link( $type_term->slug, LG_PREFIX . 'meeting_type' );
				$html .= '<li><a href="' . $url . '">' . $type_term->name . '</a></li>';
			
				if( !empty($meeting_year) ) {
				
					$url = add_query_arg( array( LG_PREFIX . 'meeting_year' => $meeting_year ), $url );
					$html .= '<li><a href="' . $url . '">' . $meeting_year . '</a></li>';
				}
			}
		}
		else if( get_post_type() == LG_PREFIX . 'public_notice' ) {
			
			$public_notice_year =  '';
			
			if( get_query_var( LG_PREFIX . 'public_notice_year' ) ) {
				$public_notice_year = get_query_var( LG_PREFIX . 'public_notice_year' );	
			}
			elseif( is_single() ) {
				$public_notice_date = get_post_meta( get_the_ID(), LG_PREFIX . 'public_notice_date', true );
				
				if( !empty( $public_notice_date ) ) {
					$public_notice_year = date( 'Y', $public_notice_date );
				}	
			}
			
			if( !empty( $public_notice_year ) ) {
				$url = get_post_type_archive_link( LG_PREFIX . 'public_notice' );
				$url = add_query_arg( array( LG_PREFIX . 'public_notice_year' => $public_notice_year ), $url );
				$html .= '<li><a href="' . $url . '">' . $public_notice_year . '</a></li>';
			}
		}
		else if( get_post_type() == LG_PREFIX . 'press_release' ) {
			
			$year = get_the_date( 'Y' );
			$url = get_year_link( $year );
			$url .= '?post_type=' . LG_PREFIX . 'press_release';
			
			$html .= "<li><a href=\"$url\">$year</a></li>";
		}
	}
	elseif( is_author() ) {
		$html .= '<li>Author Archive</li>';
	}
	elseif( is_tag() ) {
		$html .= '<li>' . get_single_tag_title() . '</li>';
	}
	elseif( is_day() ) {
		$html .= '<li>' . get_the_time( 'F jS, Y' ) . '</li>';
	}
	elseif( is_month() ) {
		$html .= '<li>' . get_the_time( 'F, Y' ) . '</li>';
	}
	elseif( is_year() ) {
		$year = get_the_date( 'Y' );
		$url = get_year_link( $year );
	
		$html .= "<li><a href=\"$url\">$year</a></li>";
	}
	
	
	$html .= '</ol>';
	
	$html = apply_filters( 'lg_breadcrumbs', $html ); 
	
	return $html;
}

function lg_get_archives( $args ) {
	
	global $wpdb;
	
	$defaults = array (
		'type' => 'postbypost', // values: postbypost, future: yearly, monthly, daily, weekly
		'post_type' => 'post',
		'format' => 'feed', // values: list, feed, future: table, grid, gallery
		'content_format' => 'teaser', // values: link, teaser, full
		'date_key' => 'post_date',	// for grouping by date field other than post_date
		'date_type' => 'datetime', // values: date, datetime, timestamp, future: custom
		'meta_query' => array(),
		'order_by' => 'post_date',
		'order' => 'DESC',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'post_group_by' => '', // only applies to archives of type 'postbypost', values: year, custom field name, future: taxonomy
		'post_group_order' => '',
		'post_group_offset' => '', // e.g. "8 MONTH" to display posts grouped by academic year
		'template' => LG_BASE_DIR . '/templates/archives.php',
		'template_options' => array(),
		'paging' => true
	);
	
	/**
	 * Filter the default args
	 * 
	 * @param array  $defaults
	 * @param array  $args
	 */
	$defaults = apply_filters( 'lg_get_archives_default_args', $defaults , $args );
	
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * Filter the args
	 * 
	 * @param array  $args
	 */
	$args = apply_filters( 'lg_get_archives_args', $args );
	
	$query = new WP_Query;
	$query->lg_is_archives = true;
	
	$paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	
	$query_args = array(
		'post_type' => $args['post_type'],
		'post_status' => 'publish',
		'posts_per_page' => $args['posts_per_page'],
		'meta_query' => $args['meta_query'],
		'orderby' => $args['order_by'],
		'paged' => $paged
	);
	
	if( !is_array( $args['order_by'] ) ) {
		$query_args['order'] = $args['order'];
	}
	
	// Use a date other than post_date to group posts
	$date_field = 'post_date';
	
	if( !empty( $args['date_key'] ) && 'post_date' != $args['date_key'] ) {
		
		$query_args['meta_key'] = $args['date_key'];
		$date_field = "$wpdb->postmeta.meta_value";
		
		if( 'timestamp' == $args['date_type'] ) {
		
			// Prevent timestamps from being converted to server timezone
			$wpdb->query("SET time_zone = '+00:00'");
			
			$date_field = 'FROM_UNIXTIME(' . $date_field . ')';
		}
	}
	
	$query->lg_date_field = $date_field;
	
	
	if ( 'yearly' == $args['type'] ) {
		
		if( $args['date_key'] == 'post_date' ) {
			return wp_get_archives( array( 
				'type' => 'yearly',
				'post_type' => $args['post_type']
			) );
		}
		
	} elseif ( 'postbypost' == $args['type'] ) {
		
		// Set params on WP_Query object so they are available in filters
		$query->lg_post_group_by = $args['post_group_by'];
		$query->lg_post_group_offset = $args['post_group_offset'];
		$query->lg_post_group_order = $args['post_group_order'];
		
		add_filter( 'posts_fields', function( $fields, $query ) {
			
			if( empty( $query->lg_post_group_by ) ) {
				return $fields;
			}
			
			switch( $query->lg_post_group_by ) {
			
				case 'year':
					$lg_archives_group = ", YEAR($query->lg_date_field) AS `lg_archives_group`";
					
					if( !empty( $query->lg_post_group_offset ) ) {
						$lg_archives_group = ", CONCAT( YEAR($query->lg_date_field-INTERVAL $query->lg_post_group_offset), '-', 1+YEAR($query->lg_date_field-INTERVAL $query->lg_post_group_offset)) AS `lg_archives_group`";
					}
					
					break;
				
				default:
					$lg_archives_group = ", $query->lg_post_group_by AS `lg_archives_group`";
			}
			
			$fields .= $lg_archives_group;
			
			return $fields;
			
		}, 10, 2 );
		
		
		add_filter( 'posts_orderby', function( $orderby, $query ) {
			
			if( empty( $query->lg_post_group_by ) ) {
				return $orderby;
			}
			
			$post_group_order = 'ASC';
			if( 'DESC' == strtoupper( $query->lg_post_group_order ) ) {
				$post_group_order = 'DESC';
			}
			
			$new_orderby = "lg_archives_group $post_group_order";
			
			if( !empty( $orderby ) ) {
				$new_orderby .= ', ' . $orderby;
			}
			
			return $new_orderby;
			
		}, 10, 2 );
		
		
		$posts = $query->query( $query_args );
		//echo $query->request;
		
		$grouped_results = array( $posts );
		
		if( !empty( $args['post_group_by'] ) ) {
			
			$grouped_results = array();
			
			foreach( $posts as $post ) {
			
				$key = 'all';
				if( !empty( $post->lg_archives_group ) ) {
					$key = $post->lg_archives_group;
				}
				
				$grouped_results[$key][] = $post;
			}
		}
		
		// Set vars for template
		$post_group_by = $args['post_group_by'];
		
		ob_start();
		include $args['template'];
		return ob_get_clean();
	}
}

function lg_submenu( $args ) {
	
	$defaults = array (
		'start_depth' => 1, 
		'max_depth' => 3
	);
	
	/**
	 * Filter the default args
	 * 
	 * @param array  $defaults
	 * @param array  $args
	 */
	$defaults = apply_filters( 'lg_submenu_default_args', $defaults , $args );
	
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * Filter the args
	 * 
	 * @param array  $args
	 */
	$args = apply_filters( 'lg_submenu_args', $args );
	
	if( ! class_exists('localgov\Submenu_Widget') ) {
		return;
	}
	$Submenu_Widget = new localgov\Submenu_Widget;
	
	return $Submenu_Widget->content($args);

}