<?php

namespace localgov;

class Newsletters_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Newsletters_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
	
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
		add_action( 'wp', array( $this, 'wp' ) );
		add_action( 'admin_menu', array( $this, 'add_import_page' ) );
		
		add_filter( 'lg_get_archives_default_args', array( $this, 'filter_get_archives_default_args' ), 10, 2 );
	}
	
	public static function filter_get_archives_default_args( $defaults, $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'newsletter' && $args['post_type'] != LG_PREFIX . 'newsletter' )
		) {
			return $defaults;
		}
		
		$defaults['date_key'] = LG_PREFIX . 'newsletter_date';
		$defaults['date_type'] = 'timestamp';
		$defaults['order_by'] = array( 
			LG_PREFIX . 'newsletter_date' => 'DESC', 
			'post_title' => 'DESC'
		);
		$defaults['posts_per_page'] = -1;
		
		return $defaults;
	}

	public function register_types() {
	
		register_post_type(LG_PREFIX . 'newsletter', array(
			'labels' => array(
				'name' => __('Newsletters'),
				'singular_name' => __('Newsletter'),
				'all_items' => __('All Newsletters')
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'newsletters'
			),
			'supports' => array( 'title' )
		));
		
	}

	function init() {
		self::register_types();
	}
	
	function cmb2_init() {
		
		$newsletter_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'newsletter',
			'title' => __( 'Newsletter', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'newsletter' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$newsletter_metabox->add_field( array(
			'name' => __( 'Newsletter Date', 'localgov' ),
			'id' => LG_PREFIX . 'newsletter_date',
			'type' => 'text_date_timestamp'
		) );
		
		$newsletter_metabox->add_field( array(
			'name' => __( 'Newsletter File', 'localgov' ),
			'id' => LG_PREFIX . 'newsletter_file',
			'type' => 'file'
		) );
		
		$files_group_id = $newsletter_metabox->add_field( array(
			'name' => __( 'Files', 'localgov' ),
			'id' => LG_PREFIX . 'newsletter_files',
			'type' => 'group',
			'options' => array(
				'group_title' => __( 'Related File {#}', 'localgov' ),
				'add_button' => __( 'Add Another File', 'localgov' ),
				'remove_button' => __( 'Remove File', 'localgov' ),
				'sortable' => true
			)
		) );
		
		$newsletter_metabox->add_group_field( $files_group_id, array(
			'name' => 'Title',
			'id' => 'title',
			'type' => 'text'
		) );
		
		$newsletter_metabox->add_group_field( $files_group_id, array(
			'name' => 'File',
			'id' => 'file',
			'type' => 'file'
		) );
		
	}
	
	function wp( $wp ) {
	
		if( 
			!is_singular() 
			|| LG_PREFIX . 'newsletter' != get_post_type()
		) {
			return;
		}
		
		$newsletter_file = get_post_meta( get_the_ID(), LG_PREFIX . 'newsletter_file', true);
		
		if( empty( $newsletter_file ) ) {
			status_header(404);
			include( get_404_template() );
			exit;
		}
		
		header( "Location: $newsletter_file" );
		exit;
	}
	
	function add_import_page() {
		
		add_submenu_page( 'edit.php?post_type=lg_newsletter', 'Import Newsletters', 'Import Newsletters', 'manage_options', 'lg_newsletter-import', array($this, 'import_page') );
	}
	
	function import_page() {
	
		if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->import();
		}
	
?>
<div class="wrap">
	<h2>Import File</h2>
	<p>
		Import newsletter information contained in a CSV file. Only the following member information will be imported:
		<br>Date, Title*, File
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
        	
        	$newsletter = array();
        	
        	foreach( $header_indexes as $header_name => $header_index ) {
        		
        		if( !isset( $row[$header_index] ) ) {
        			continue;
        		}
        		
        		$newsletter[$header_name] = trim( $row[$header_index] );
        	}

        	// Title and date is required
        	if( 
        		!isset( $newsletter['title'] ) || empty( $newsletter['title'] )
        		|| !isset( $newsletter['date'] ) || empty( $newsletter['date'] )
        	) {
        		continue;
        	}
        	
        	$post = array(
        		'post_title' => $newsletter['title'],
        		'post_type' => LG_PREFIX . 'newsletter',
        		'post_status' => 'publish'
        	);
        	
        	
			$post['ID'] = wp_insert_post( $post );
		
			if( !$post['ID'] ) {
				$this->log['error'][] = "Error importing newsletters: unable to save newsletter $newsletter[title].";
				$this->print_messages();
				return;
			}
					
			if( isset( $newsletter['date'] ) ) {
				
				$timestamp = strtotime( $newsletter['date'] );
				
				if ( $timestamp !== false ) {
					update_post_meta( $post['ID'], LG_PREFIX . 'newsletter_date', $timestamp );
				}				
			}
			 
			if( isset( $newsletter['file'] ) ) {
			
				$file_id = attachment_url_to_postid( $newsletter['file'] );
			
				if( !empty( $file_id ) ) {				
					update_post_meta( $post['ID'], LG_PREFIX . 'newsletter_file', $newsletter['file'] );
					update_post_meta( $post['ID'], LG_PREFIX . 'newsletter_file_id', $file_id );
				}
			}
        	
        	$count++;
        }
        
        $this->log['notice'][] = $count . ' newsletters imported successfully.';
    	$this->print_messages();
	}
	
}

Newsletters_Module::instance();