<?php

namespace localgov;

class PressReleases_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PressReleases_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
	
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
		//add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		//add_action( 'posts_selection', array( $this, 'posts_selection' ) );
		add_action( 'admin_menu', array( $this, 'add_import_page' ) );
		
		//add_filter( 'lg_get_archives_default_args', array( $this, 'filter_get_archives_default_args' ), 10, 2 );
	}
	
	/*public static function filter_get_archives_default_args( $defaults, $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'press_release' && $args['post_type'] != 'lg_press_release' )
		) {
			return $defaults;
		}
		
		$defaults['date_key'] = LG_PREFIX . 'press_release_date';
		$defaults['date_type'] = 'timestamp'; 
		$defaults['order_by'] = array(
			LG_PREFIX . 'press_release_date' => 'DESC',
			'post_title' => 'DESC'
		);
		$defaults['posts_per_page'] = -1;
		
		return $defaults;
	}*/

	public function register_types() {
		
		register_post_type( LG_PREFIX . 'press_release', array(
			'labels' => array(
				'name' => __('Press Releases'),
				'singular_name' => __('Press Release'),
				'all_items' => __('All Press Releases')
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'press-releases'
			),
			'supports' => array( 'title', 'editor' )
		) );
		
	}

	function init() {
		self::register_types();
	}
	
	function cmb2_init() {
		
		$press_release_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'press_release',
			'title' => __( 'Press Release', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'press_release' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$press_release_metabox->add_field( array(
			'name' => __( 'Press Release File', 'localgov' ),
			'id' => LG_PREFIX . 'press_release_file',
			'type' => 'file'
		) );
		
	}
	
	public function pre_get_posts( $query ) {
	
		if(
			is_admin()
			|| !$query->is_main_query()
			|| !$query->is_post_type_archive( LG_PREFIX . 'press_release' )
		) {
			return;
		}
		
		// Public notice listing
		/*if( $query->get( LG_PREFIX . 'press_release_year' ) ) {
				
			add_filter( 'posts_where', array( $this, 'posts_where' ) );
			
			$query->set( 'meta_key', LG_PREFIX . 'press_release_date' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );	
		}
		// Year listing
		else {
			// Remove limit
			$query->set( 'posts_per_page', '-1' );
			
			$query->set( 'meta_key', LG_PREFIX . 'press_release_date' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );
		}*/
	}
	
	/*function posts_where( $where ) {
		
		global $wpdb;
		
		$year = get_query_var( LG_PREFIX . 'press_release_year' );
		
		$where .= ' AND YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . '.meta_value)) = ' . $year;
		return $where;
			
	}
	
	function posts_groupby( $groupby ) {

		global $wpdb;
		
		$groupby = 'YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . ".meta_value))";
		return $groupby;
	}
	
	public function posts_selection( $query ) {
			
		// Remove filters set in pre_get_posts
		remove_filter( 'posts_where', array( $this, 'posts_where' ) );
		remove_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );
	}*/
	
	function add_import_page() {
		
		add_submenu_page( 'edit.php?post_type=lg_press_release', 'Import Press Releases', 'Import Press Releases', 'manage_options', 'lg_press_release-import', array($this, 'import_page') );
	}
	
	function import_page() {
	
		if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->import();
		}
	
?>
<div class="wrap">
	<h2>Import File</h2>
	<p>
		Import press release information contained in a CSV file. Only the following member information will be imported:
		<br>Title*, Content*, Date, File
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
        	'title',
        	'content',
        	'date',
        	'file'
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
        	
        	$press_release = array();
        	
        	foreach( $header_indexes as $header_name => $header_index ) {
        		
        		if( !isset( $row[$header_index] ) ) {
        			continue;
        		}
        		
        		$press_release[$header_name] = trim( $row[$header_index] );
        	}

        	// Title and content is required
        	if( 
        		!isset( $press_release['title'] ) || empty( $press_release['title'] )
        		|| !isset( $press_release['content'] ) || empty( $press_release['content'] )
        	) {
        		continue;
        	}
        	
        	$post = array(
        		'post_title' => $press_release['title'],
        		'post_type' => LG_PREFIX . 'press_release',
        		'post_status' => 'publish',
        		'post_content' => $press_release['content'],
        	);
        	
        	if( isset( $press_release['date'] ) ) {
        		$timestamp = strtotime( $press_release['date'] );
        		
        		if( $timestamp !== false ) {
	        		$post['post_date'] = date( 'Y-m-d H:i:s', $timestamp ); 
	        	}
        	}
        	
			$post['ID'] = wp_insert_post( $post );
		
			if( !$post['ID'] ) {
				$this->log['error'][] = "Error importing press releases: unable to save press release $press_release[title].";
				$this->print_messages();
				return;
			}
			 
			if( isset( $press_release['file'] ) ) {
			
				$file_id = attachment_url_to_postid( $press_release['file'] );
			
				if( !empty( $file_id ) ) {				
					update_post_meta( $post['ID'], LG_PREFIX . 'press_release_file', $press_release['file'] );
					update_post_meta( $post['ID'], LG_PREFIX . 'press_release_file_id', $file_id );
				}
				elseif ( !empty( $press_release['file'] ) ) {
					$this->log['error'][] = $press_release['file'] . ' not found.';
				}
			}
        	
        	$count++;
        }
        
        $this->log['notice'][] = $count . ' press releases imported successfully.';
    	$this->print_messages();
	}
}

PressReleases_Module::instance();