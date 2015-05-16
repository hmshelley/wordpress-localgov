<?php

namespace {

function lg_get_directory( $args ) {
	
	$defaults = array(
		'type' => 'postbypost',
		'post_type' => LG_PREFIX . 'directory_member',
		'meta_query' => array(
			LG_PREFIX . 'directory_member_last_name' => array(
				'key' => LG_PREFIX . 'directory_member_last_name'
			),
			LG_PREFIX . 'directory_member_first_name' => array(
				'key' => LG_PREFIX . 'directory_member_first_name'
			)
		),
		'order_by' => array(
			LG_PREFIX . 'directory_member_last_name' => 'ASC',
			LG_PREFIX . 'directory_member_first_name' => 'ASC'
		),
		'posts_per_page' => -1,
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

}

namespace localgov {

class Directory_Module {
	
	/**
	 * Class variables
	 */
	private static $instance;

	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Directory_Module;
			self::$instance->setup();
		}
		return self::$instance;
	}

	public function setup() {
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
		add_action( 'admin_menu', array( $this ,'add_import_page' ) );
		
		add_filter( 'lg_directory_member_field_header', array( __CLASS__, 'filter_member_field_header' ) );
		add_filter( 'lg_directory_member_field_value', array( __CLASS__, 'filter_member_field_value' ), null, 4 );
	}
	
	public function register_types() {
	
		register_taxonomy( LG_PREFIX . 'directory_group', LG_PREFIX . 'directory_member', array(
			'label' => __( 'Directory Groups' ),
			'hierarchical' => true,
			'sort' => true,
			'show_admin_column' => true
		) );
	
		register_post_type( LG_PREFIX . 'directory_member', array(
			'labels' => array(
				'name' => __( 'Directory' ),
				'singular_name' => __( 'Member' ),
				'all_items' => __( 'All  Members' )
			),
			'public' => true,
			'has_archive' => false,
			'rewrite' => array(
				'slug' => 'directory'
			),
			'supports' => array( 'title', 'page-attributes', 'excerpt' ),
			'taxonomies' => array('category'),
			'hierarchical' => true
		) );
	}
	
	public function init() {
	
		self::register_types();	
	}
	
	function cmb2_init() {
		
		$member_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'directory_member',
			'title' => __( 'Directory Member', 'localgov' ),
			'object_types' => array( LG_PREFIX . 'directory_member' ),
			'context' => 'normal',
			'priority' => 'high',
			'show_names' => true
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'First Name', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_first_name',
			'type' => 'text'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Last Name', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_last_name',
			'type' => 'text'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Title', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_title',
			'type' => 'text'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Address', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_address',
			'type' => 'textarea_small'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'City', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_city',
			'type' => 'text'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Zip Code', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_zip_code',
			'type' => 'text'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Phone', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_phone',
			'type' => 'text'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Email', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_email',
			'type' => 'text_email'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Photo', 'localgov' ),
			'id' => '_thumbnail',
			'type' => 'file'
		) );
		
		$member_metabox->add_field( array(
			'name' => __( 'Bio', 'localgov' ),
			'id' => LG_PREFIX . 'directory_member_bio',
			'type' => 'wysiwyg'
		) );
	}
	
	function add_import_page() {
		
		add_submenu_page( 'edit.php?post_type=lg_directory_member', 'Import Members', 'Import Members', 'manage_options', 'lg_directory_member-import', array($this, 'import_page') );
	}
	
	function import_page() {
	
		if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$this->import();
		}
	
?>
<div class="wrap">
	<h2>Import File</h2>
	<p>
		Import member information contained in a CSV file. Only the following member information will be imported:
		<br>Last Name, First Name, Address, City, Zip Code, Phone, Email
		<br>The text for the headers in the first row of the CSV file must match those given above -- otherwise the column(s) will be skipped.
	</p>
	<p>
		An existing member's information will be updated if the emails match.
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
        	'last_name',
        	'first_name',
        	'title',
        	'address',
        	'city',
        	'zip_code',
        	'phone',
        	'email'
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
        	
        	$member = array();
        	
        	foreach( $header_indexes as $header_name => $header_index ) {
        		
        		if( !isset( $row[$header_index] ) ) {
        			continue;
        		}
        		
        		$member[$header_name] = trim( $row[$header_index] );
        	}
        	
        	$name = !empty( $member['first_name'] ) ? $member['first_name'] : '';
			$name .= !empty( $member['last_name'] ) ? ' ' . $member['last_name'] : '';
			$name = trim( $name );
        	
        	// Name is required
        	if( empty( $name ) ) {
        		continue;
        	}
        	
        	$post = array(
        		'post_title' => $name,
        		'post_type' => LG_PREFIX . 'directory_member',
        		'post_status' => 'publish'
        	);
        	
        	// Look up existing members using email
        	if( !empty( $member['email'] ) ) {
        	
        		$posts = get_posts(	array(
        			'post_type' => LG_PREFIX . 'directory_member',
        			'meta_key' => LG_PREFIX . 'directory_member_email',
        			'meta_value' => $member['email']
        		) );
        		
        		if( !empty($posts) ) {
        			$post['ID'] = $posts[0]->ID;
        		}
        	}
        	
        	if( !empty( $post['ID'] ) ) {
        	
        		$success = wp_update_post( $post );
        		
        		if( !$success ) {
        			$this->log['error'][] = "Error importing members: unable to save member $name.";
					$this->print_messages();
					return;
        		}
        	}
        	else {
        	
        		$post['ID'] = wp_insert_post( $post );
        	
				if( !$post['ID'] ) {
					$this->log['error'][] = "Error importing members: unable to save member $name.";
					$this->print_messages();
					return;
				}
			}
				
			if( isset( $member['first_name'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_first_name', $member['first_name'] );
			}
			
			if( isset( $member['last_name'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_last_name', $member['last_name'] );
			}
			
			if( isset( $member['title'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_title', $member['title'] );
			}
			
			if( isset( $member['address'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_address', $member['address'] );
			}
			
			if( isset( $member['city'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_city', $member['city'] );
			}
			
			if( isset( $member['zip_code'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_zip_code', $member['zip_code'] );
			}
			
			if( isset( $member['phone'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_phone', $member['phone'] );
			}
			
			if( isset( $member['email'] ) ) {
				update_post_meta( $post['ID'], LG_PREFIX . 'directory_member_email', $member['email'] );
			}
        	
        	$count++;
        }
        
        $this->log['notice'][] = $count . ' members imported successfully.';
    	$this->print_messages();
	}
	
	public function filter_member_field_value( $field_value, $field_name, $member, $args ) {
		
		$member_link = '<a href="' . get_permalink() . '"';
		if( !empty( $args['template_options']['member_link_attributes'] ) ) {
			foreach( $args['template_options']['member_link_attributes'] as $key => $value ) {
				$member_link .= ' ' . $key . '="' . $value . '"';
			}
		}
		$member_link .= '>';
		
		switch( $field_name ) {
		
			case 'thumbnail':
				
				if( has_post_thumbnail() ) {
					$field_value = $member_link . get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) . '</a>';
				}
				break;
				
			case 'name':
			
				$name = ( !empty ($member['first_name'] ) ) ? $member['first_name'] : '';
				$name .= ( !empty ($member['last_name'] ) ) ? ' ' . $member['last_name'] : '';
				$name = trim($name);
			
				if( !empty( $member['bio'] ) ) {
					$field_value = $member_link . $name . '</a>';	
				}
				else {
					$field_value = $name;
				}
				if ( !empty ( $member['title'] ) ) {
					$field_value .= '<br>' . $member['title'];
				}
				break;
				
			case 'address':
				$address = ( !empty ($member['address'] ) ) ? $member['address'] . '<br>' : '';
				$address .= ( !empty ($member['city'] ) ) ? $member['city'] : '';
				$address .= ( !empty ($member['zip_code'] ) ) ? ', ' . $member['zip_code'] : '';
				
				$field_value = $address;
				break;
				
			case 'email':
				if ( !empty ( $field_value ) ) {
					$field_value = '<a href="mailto:' . $field_value . '">' . $field_value . '</a>';
				}
				break;
		}
		
		return $field_value;
	}
	
	public function filter_member_field_header( $field_name ) {
		$field_header = str_replace( '_', ' ', $field_name );
		$field_header = ucwords( $field_header );
		
		switch( $field_name ) {
			case 'thumbnail':
				$field_header = '';
		}
		
		return $field_header;
	}
}

Directory_Module::instance();

}