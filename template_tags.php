<?php

/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
function lg_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
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
		'total'    => $GLOBALS['wp_query']->max_num_pages,
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
	$html .= '<li><a href="' . get_home_url() . '"><span class="glyphicon-home"></span></a></li>';
	
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
	elseif( is_tag() ) {
		$html .= '<li>' . get_single_tag_title() . '</li>';
	}
	elseif( is_day() ) {
		$html .= '<li>Archive for ' . get_the_time( 'F jS, Y' ) . ' Archive</li>';
	}
	elseif( is_month() ) {
		$html .= '<li>Archive for ' . get_the_time( 'F, Y' ) . ' Archive</li>';
	}
	elseif( is_year() ) {
		$html .= '<li>' . get_the_time( 'Y' ) . ' Archive</li>';
	}
	elseif( is_author() ) {
		$html .= '<li>Author Archive</li>';
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
				$meeting = get_post_meta( LG_PREFIX . 'meeting' );
				
				if( !empty( $meeting[0]['type'] ) ) {
					$type_term = get_term_by( 'id', $meeting[0]['type'] , LG_PREFIX . 'meeting_type' );
				}
				
				if( !empty( $meeting[0]['date'] ) ) {
					$meeting_year = date( 'Y', $meeting[0]['date'] );
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
	}
	
	$html .= '</ol>';
	
	$html = apply_filters( 'lg_breadcrumbs', $html ); 
	
	return $html;
}

function lg_get_archives( $args ) {
	
	global $wpdb, $wp_locale, $post;
	
	$defaults = array (
		'type' => 'yearly',
		'post_type' => 'post',
		'limit' => '',
		'order_by' => 'post_date DESC',
		'date_key' => '',
		'group_posts' => '',
		'group_order' => '',
		'postmeta_keys' => array(),
		'template' => LG_BASE_DIR . '/templates/archive.php',
		'template_options' => array()
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

	$join = "";
	
	$where = "WHERE post_type = '$args[post_type]' AND post_status = 'publish'";

	$order_by = $args['order_by'];
	
	$postmeta_keys = $args['postmeta_keys'];
	$postmeta_fields = '';

	$date_col = 'post_date';
	
	if( !empty( $args['date_key'] ) ) {

		if( !in_array( $args['date_key'], $postmeta_keys ) ) {
			$postmeta_keys[] = $args['date_key'];
		}
	}

	if( !empty( $postmeta_keys ) ) {
		
		foreach( $postmeta_keys as $i => $key ) {
			
			$join .= " LEFT JOIN $wpdb->postmeta AS `postmeta_$i` ON ($wpdb->posts.ID = postmeta_$i.post_id AND postmeta_$i.meta_key = '$key')";
			$postmeta_fields .= ", postmeta_$i.meta_value as $key";
			
			if( !empty( $args['date_key']) && $key == $args['date_key'] ) {
				$date_col = "postmeta_$i.meta_value";
			}
		}
	}
	
	$limit = '';
	if ( !empty( $args['limit'] ) ) {
		$limit = ' LIMIT ' . absint( $args['limit'] );
	}

	if ( 'yearly' == $args['type'] ) {
		$query = "SELECT *, $date_col AS `date_col`, YEAR($date_col) AS `year` $postmeta_fields FROM $wpdb->posts $join $where GROUP BY YEAR($date_col) ORDER BY $order_by $limit";
		
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
		
		$query = "SELECT *, $date_col AS `date_col` $group_interval_col $postmeta_fields FROM $wpdb->posts $join $where ORDER BY $order_by $limit";
	
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
				else if( isset($post->$group_posts) ) {
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


function lg_get_directory( $args ) {
	
	$defaults = array(
		'type' => 'postbypost',
		'post_type' => LG_PREFIX . 'directory_member',
		'order_by' => LG_PREFIX . 'directory_member_last_name ASC, '. LG_PREFIX . 'directory_member_first_name ASC',
		'postmeta_keys' => array(LG_PREFIX . 'directory_member_last_name', LG_PREFIX . 'directory_member_first_name', LG_PREFIX . 'directory_member_group'),
		'group_posts' => LG_PREFIX . 'directory_member_group',
		'group_order' => 'ASC',
		'template' => LG_BASE_DIR . '/templates/directory.php',
		'template_options' => array(
			'fields' => '',
			'show_headers' => true
		)
	);
	
	/**
	 * Filter the default args
	 * 
	 * @param array  $defaults
	 * @param array  $args
	 */
	$defaults = apply_filters( 'lg_get_directory_default_args', $defaults , $args );
	
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * Filter the args
	 * 
	 * @param array  $args
	 */
	$args = apply_filters( 'lg_get_directory_args', $args );
	
	return lg_get_archives( $args );
}

function lg_get_featured_posts( $options ) {

	$featured_posts = localgov\FeaturedContent_Module::get_featured_posts( $options );

	return apply_filters( 'lg_featured_posts', $featured_posts );
}


$lg_featured_id = 0;

function lg_get_featured( $args = array() ) {
		
	global $lg_featured_id;
	$lg_featured_id++;
	
	$defaults = array (
		'template' => LG_BASE_DIR . '/templates/featured_content_slider.php',
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