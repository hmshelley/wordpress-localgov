<?php

namespace localgov;

class PublicNotices_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new PublicNotices_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
	
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
		//add_action( 'wp', array( $this, 'wp' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'posts_selection', array( $this, 'posts_selection' ) );
		add_action( 'admin_menu', array( $this, 'add_import_page' ) );
		
		add_filter( 'lg_get_archives_default_args', array( $this, 'filter_get_archives_default_args' ), 10, 2 );
	}
	
	public static function filter_get_archives_default_args( $defaults, $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'public_notice' && $args['post_type'] != 'lg_public_notice' )
		) {
			return $defaults;
		}
		
		$defaults['date_key'] = LG_PREFIX . 'public_notice_date';
		$defaults['date_type'] = 'timestamp'; 
		$defaults['order_by'] = array(
			LG_PREFIX . 'public_notice_date' => 'DESC',
			'post_title' => 'DESC'
		);
		$defaults['posts_per_page'] = -1;
		
		return $defaults;
	}

	public function register_types() {
	
		add_rewrite_tag( '%' . LG_PREFIX . 'public_notice_year%', '([0-9]{4})' );
	
		register_post_type( LG_PREFIX . 'public_notice', array(
			'labels' => array(
				'name' => __('Public Notices'),
				'singular_name' => __('Public Notice'),
				'all_items' => __('All Public Notices')
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'public-notices'
			),
			'supports' => array( 'title', 'editor' )
		) );
		
	}

	function init() {
		self::register_types();
	}
	
	function cmb2_init() {
		
		$public_notice_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'public_notice',
			'title' => __( 'Public Notice', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'public_notice' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$public_notice_metabox->add_field( array(
			'name' => __( 'Public Notice Date', 'localgov' ),
			'id' => LG_PREFIX . 'public_notice_date',
			'type' => 'text_date_timestamp'
		) );
		
		$public_notice_metabox->add_field( array(
			'name' => __( 'Public Notice File', 'localgov' ),
			'id' => LG_PREFIX . 'public_notice_file',
			'type' => 'file'
		) );
		
	}
	
	function wp( $wp ) {
	
		if( 
			!is_singular() 
			|| LG_PREFIX . 'public_notice' != get_post_type()
		) {
			return;
		}
		
		$public_notice_file = get_post_meta( get_the_ID(), LG_PREFIX . 'public_notice_file', true);
		
		if( empty( $public_notice_file ) ) {
			status_header(404);
			include( get_404_template() );
			exit;
		}
		
		header( "Location: $public_notice_file" );
		exit;
	}
	
	public function pre_get_posts( $query ) {
	
		if(
			is_admin()
			|| !$query->is_main_query()
			|| !$query->is_post_type_archive( LG_PREFIX . 'public_notice' )
		) {
			return;
		}
		
		// Public notice listing
		if( $query->get( LG_PREFIX . 'public_notice_year' ) ) {
				
			add_filter( 'posts_where', array( $this, 'posts_where_year' ) );
			
			$query->set( 'meta_key', LG_PREFIX . 'public_notice_date' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );	
		}
		// Year listing
		else {
			// Remove limit
			$query->set( 'posts_per_page', '-1' );
			
			$query->set( 'meta_key', LG_PREFIX . 'public_notice_date' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			
			add_filter( 'posts_groupby', array( $this, 'posts_groupby_year' ) );
		}
	}
	
	function posts_where_year( $where ) {
		
		global $wpdb;
		
		$year = get_query_var( LG_PREFIX . 'public_notice_year' );
		
		$where .= ' AND YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . '.meta_value)) = ' . $year;
		return $where;
			
	}
	
	function posts_groupby_year( $groupby ) {

		global $wpdb;
		
		$groupby = 'YEAR(FROM_UNIXTIME(' . $wpdb->postmeta . ".meta_value))";
		return $groupby;
	}
	
	public function posts_selection( $query ) {
			
		// Remove filters set in pre_get_posts
		remove_filter( 'posts_where', array( $this, 'posts_where_year' ) );
		remove_filter( 'posts_groupby', array( $this, 'posts_groupby_year' ) );
	}
	
	function add_import_page() {
		
		add_submenu_page( 'edit.php?post_type=lg_public_notice', 'Import Public Notices', 'Import Public Notices', 'manage_options', 'lg_public_notice-import', array($this, 'import_page') );
	}
	
	function import_page() {
	
		if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->import();
		}
	
?>
<div class="wrap">
	<h2>Import File</h2>
	<p>
		Import public notice information contained in a CSV file. Only the following member information will be imported:
		<br>Title*, Content, Date, File
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
        	
        	$public_notice = array();
        	
        	foreach( $header_indexes as $header_name => $header_index ) {
        		
        		if( !isset( $row[$header_index] ) ) {
        			continue;
        		}
        		
        		$public_notice[$header_name] = trim( $row[$header_index] );
        	}

        	// Title and date is required
        	if( 
        		!isset( $public_notice['title'] ) || empty( $public_notice['title'] )
        		|| !isset( $public_notice['date'] ) || empty( $public_notice['date'] )
        	) {
        		continue;
        	}
        	
        	$post = array(
        		'post_title' => $public_notice['title'],
        		'post_type' => LG_PREFIX . 'public_notice',
        		'post_status' => 'publish',
        		'post_content' => $public_notice['content']
        	);
        	
			$post['ID'] = wp_insert_post( $post );
		
			if( !$post['ID'] ) {
				$this->log['error'][] = "Error importing public notices: unable to save public notice $public_notice[title].";
				$this->print_messages();
				return;
			}
					
			if( isset( $public_notice['date'] ) ) {
				
				$timestamp = strtotime( $public_notice['date'] );
				
				if ( $timestamp !== false ) {
					update_post_meta( $post['ID'], LG_PREFIX . 'public_notice_date', $timestamp );
				}				
			}
			 
			if( isset( $public_notice['file'] ) ) {
			
				$file_id = attachment_url_to_postid( $public_notice['file'] );
			
				if( !empty( $file_id ) ) {				
					update_post_meta( $post['ID'], LG_PREFIX . 'public_notice_file', $public_notice['file'] );
					update_post_meta( $post['ID'], LG_PREFIX . 'public_notice_file_id', $file_id );
				}
				elseif ( !empty( $public_notice['file'] ) ) {
					$this->log['error'][] = $public_notice['file'] . ' not found.';
				}
			}
        	
        	$count++;
        }
        
        $this->log['notice'][] = $count . ' public notices imported successfully.';
    	$this->print_messages();
	}
}

PublicNotices_Module::instance();