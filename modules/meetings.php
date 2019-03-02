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
		add_action( 'posts_selection', array( $this, 'posts_selection' ) );
		add_action( 'admin_menu', array( $this, 'add_import_page' ) );
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
			'name' => __( 'Notes', 'localgov' ),
			'id' => LG_PREFIX . 'meeting_notes',
			'type' => 'textarea_small'
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
				
			add_filter( 'posts_where', array( $this, 'posts_where_year' ) );
			
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
			
			add_filter( 'posts_groupby', array( $this, 'posts_groupby_year' ) );

		}
		// Meeting type listing
		else {
			
			$query->set( 'meta_key', LG_PREFIX . 'meeting_type' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
			
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );
			
		}
	}
	
	function posts_where_year( $where ) {
		global $wpdb;
		
		$year = get_query_var( LG_PREFIX . 'meeting_year' );
		
		$where .= ' AND YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . '.meta_value)) = ' . $year;
		return $where;
	}
	
	function posts_groupby( $groupby ) {
	
		global $wpdb;
	
		$groupby = $wpdb->postmeta . '.meta_value';
		return $groupby;
	}
	
	function posts_groupby_year( $groupby ) {

		global $wpdb;
	
		$groupby = 'YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . ".meta_value))";
		return $groupby;			
	}
	
	public function posts_selection( $query ) {
			
		// Remove filters set in pre_get_posts
		remove_filter( 'posts_where', array( $this, 'posts_where_year' ) );
		remove_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );
		remove_filter( 'posts_groupby', array( $this, 'posts_groupby_year' ) );
	}
	
	function add_import_page() {
		
		add_submenu_page( 'edit.php?post_type=lg_meeting', 'Import Meetings', 'Import Meetings', 'manage_options', 'lg_meeting-import', array($this, 'import_page') );
	}
	
	function import_page() {
	
		if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->import();
		}
	
?>
<div class="wrap">
	<h2>Import File</h2>
	<p>
		Import meeting information contained in a CSV file. Only the following member information will be imported:
		<br>Date*, Title*, Description, Agenda, Minutes
		<br>The text for the headers in the first row of the CSV file must match those given above -- otherwise the column(s) will be skipped.
		<br><small>* = Required fields</small>
	</p>
	
	<form method="post" enctype="multipart/form-data">
		
		<p>
			<label for="csv_import">Import File (CSV)</label>
			<br /><input name="csv_import" id="csv_import" type="file" value="" aria-required="true" />
		</p>
		
		<p class="submit"><input type="submit" class="button" name="submit" value="Import" /></p>
	</form>
</div>

<?php
	}
	
	function print_messages() {
       
		if (!empty($this->log)) {
?>

<div class="wrap">
    <?php if (!empty($this->log['error'])): ?>

    <div class="error">

        <?php foreach ($this->log['error'] as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <?php if (!empty($this->log['notice'])): ?>

    <div class="updated fade">

        <?php foreach ($this->log['notice'] as $notice): ?>
            <p><?php echo $notice; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>
</div><!-- end wrap -->

<?php
        // end messages HTML }}}

            $this->log = array();
        }
    }
	
	function import() {
		
		if (empty($_FILES['csv_import']['tmp_name'])) {
            $this->log['error'][] = 'Error importing members: no file chosen.';
            $this->print_messages();
            return;
        }
              
        $file_path = $_FILES['csv_import']['tmp_name'];
        
        $valid_headers = array(
        	'date',
        	'title',
        	'description',
        	'meeting_type',
        	'agenda',
        	'minutes',
        	'audio',
        	'files'
        );
        
        ini_set("auto_detect_line_endings", true);
        $file = fopen( $file_path, 'r' );
        
        $headers = fgetcsv( $file );
       
       	// Get indexes of valid headers
        $header_indexes = array();
        foreach( $headers as $index => $header ) {
        	
        	// Convert "friendly" headers to machine names
        	$converted_header = strtolower($header);
        	$converted_header = preg_replace("/\s+/", "_", $converted_header);
        	
        	if( !in_array( $converted_header, $valid_headers ) ) {
        		continue;
        	}
        	
        	$header_indexes[$converted_header] = $index;
        }
        
        // Ensure at least one valid header was provided
        if( empty( $header_indexes ) ) {
        	$this->log['error'][] = 'Error importing members: invalid CSV file uploaded.';
            $this->print_messages();
            return;
        }
        
        $count = 0;
        while ( $row = fgetcsv( $file ) ) {
        	
        	$meeting = array();
        	
        	foreach( $header_indexes as $header_name => $header_index ) {
        		
        		if( !isset( $row[$header_index] ) ) {
        			continue;
        		}
        		
        		$meeting[$header_name] = trim( $row[$header_index] );
        	}

        	// Title and date is required
        	if( 
        		!isset( $meeting['title'] ) || empty( $meeting['title'] )
        		|| !isset( $meeting['date'] ) || empty( $meeting['date'] )
        	) {
        		continue;
        	}
        	
        	$post = array(
        		'post_title' => $meeting['title'],
        		'post_type' => LG_PREFIX . 'meeting',
        		'post_status' => 'publish'
        	);
        	
        	
			$post['ID'] = wp_insert_post( $post );
		
			if( !$post['ID'] ) {
				$this->log['error'][] = "Error importing meetings: unable to save meeting $meeting[title].";
				$this->print_messages();
				return;
			}
					
			if( isset( $meeting['date'] ) ) {
				
				$timestamp = strtotime( $meeting['date'] );
				
				if ( $timestamp !== false ) {
					update_post_meta( $post['ID'], LG_PREFIX . 'meeting_date', $timestamp );
				}				
			}
			
			if( 
				isset( $meeting['description'] ) 
				&& !empty( $meeting['description'] ) 
			) {
				
				update_post_meta( $post['ID'], LG_PREFIX . 'meeting_description', $meeting['description'] );
								
			}
			
			if( 
				isset( $meeting['meeting_type'] )
				&& !empty( $meeting['meeting_type'] )
			){
				wp_set_object_terms( $post['ID'], $meeting['meeting_type'], LG_PREFIX . 'meeting_type', false );
			}
			
			if( isset( $meeting['agenda'] ) ) {
			
				$file_id = attachment_url_to_postid( $meeting['agenda'] );
			
				if( !empty( $file_id ) ) {				
					update_post_meta( $post['ID'], LG_PREFIX . 'meeting_agenda_file', $meeting['agenda'] );
					update_post_meta( $post['ID'], LG_PREFIX . 'meeting_agenda_file_id', $file_id );
				}
				elseif ( !empty( $meeting['agenda'] ) ) {
					$this->log['error'][] = $meeting['agenda'] . ' not found.';
				}
			}
        	
        	if( isset( $meeting['minutes'] ) ) {
			
				$file_id = attachment_url_to_postid( $meeting['minutes'] );
			
				if( !empty( $file_id ) ) {				
					update_post_meta( $post['ID'], LG_PREFIX . 'meeting_minutes_file', $meeting['minutes'] );
					update_post_meta( $post['ID'], LG_PREFIX . 'meeting_minutes_file_id', $file_id );
				}
				elseif ( ! empty( $meeting['minutes'] ) ) {
					$this->log['error'][] = $meeting['minutes'] . ' not found.';
				}
			}
			
			$files = array();
			
			if( isset ( $meeting['audio'] ) ) {
				
				$file_id = attachment_url_to_postid( $meeting['audio'] );
				
				if( !empty( $file_id ) ) {
					
					array_push($files, array(
						'title' => 'Audio',
						'file_id' => $file_id,
						'file' => $meeting['audio']
					));	
				}
				elseif ( !empty( $meeting['audio'] ) ) {
					$this->log['error'][] = $meeting['audio'] . ' not found.';
				}
			}
			
			if( isset( $meeting['files'] ) ) {
				
				$file_paths = preg_split( "/[\s,]+/", $meeting['files'] );
				
				foreach( $file_paths as $file_path ) {
				
					$file_id = attachment_url_to_postid( $file_path );
				
					if( !empty( $file_id ) ) {
				
						$pathinfo = pathinfo( $file_path );
						$file_title = $pathinfo['basename'];
						$file_title = preg_replace( '/([^\d])-/', '${1} ', $file_title );
						$file_title = ucwords( $file_title );
				
						array_push($files, array(
							'title' => $file_title,
							'file_id' => $file_id,
							'file' => $file_path
						));
						
					}
					elseif ( !empty( $file_path ) ) {
						$this->log['error'][] = $file_path . ' not found.';
					}
				}
				
			}
			
			if( !empty( $files ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'meeting_files', serialize( $files ) );
			}
        	
        	$count++;
        }
        
        $this->log['notice'][] = $count . ' meetings imported successfully.';
    	$this->print_messages();
	}
	
	
}

Meetings_Module::instance();