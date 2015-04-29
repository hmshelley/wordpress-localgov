<?php

namespace localgov;

class SiteSettings {
	
	private static $instance;
	
	/**
 	 * Option page slug
 	 * @var string
 	 */
	public $menu_slug = LG_ADMIN_MENU_SLUG;
	
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'Site Settings';
	
	/**
 	 * Options page metaboxes
 	 * @var string
 	 */
	protected $metaboxes = array();
	
	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';
	
	
	public $social_types = array(
		'email' => 'Email',
		'facebook' => 'Facebook',
		'flickr' => 'Flickr',
		'google_plus' => 'Google+',
		'instagram' => 'Instagram',
		'linkedin' => 'LinkedIn',
		'pinterest' => 'Pinterest',
		'rss' => 'RSS',
		'twitter' => 'Twitter',
		'youtube' => 'YouTube'
	);
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new SiteSettings;
			self::$instance->setup();
		}
		return self::$instance;
	}
	
	public function setup() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_init', array( $this, 'cmb2_init' ) );
	}
	
	/**
	 * Register our setting to WP
	 */
	public function init() {
		foreach( $this->metaboxes as $metabox ) {
			register_setting( $metabox->cmb_id, $metabox->cmb_id );
		}
	}

	/**
	 * Add menu options page
	 */	
	public function add_options_page() {
	
		add_menu_page( $this->title, __('LocalGov'), 'manage_options', LG_ADMIN_MENU_SLUG, array( $this, 'admin_page_display' ), null, 62.5 );
	
		// Add again as submenu page to display different title in submenu
		$this->options_page = add_submenu_page( LG_ADMIN_MENU_SLUG, $this->title, $this->title, 'manage_options', $this->menu_slug, array( $this, 'admin_page_display' ) );
	}
	
	/**
	 * Admin page markup. Mostly handled by CMB2
	 */
	public function admin_page_display() {
	
		if( !empty( $_GET['tab'] ) ) {
			$current_tab_slug = $_GET['tab'];
		}
		else {
			$current_tab_slug = $this->metaboxes[0]->cmb_id;
		}
		
		?>
		<div class="wrap cmb2-options-page <?php echo $this->menu_slug; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			
			<h2 class="nav-tab-wrapper">
				<?php foreach( $this->metaboxes as $metabox ):
					 
					$tab_slug = $metabox->cmb_id;
					$class = ( $tab_slug == $current_tab_slug ) ? ' nav-tab-active': '';
				?>
					<a class="nav-tab<?php echo $class ?>" href="?page=<?php echo $this->menu_slug ?>&tab=<?php echo $tab_slug ?>"><?php esc_attr_e( $metabox->prop('title') ); ?></a>
				<?php endforeach; ?>
			</h2>
			
			<?php $metabox = cmb2_get_metabox( $current_tab_slug ); ?>
			<div id="<?php esc_attr_e( $metabox->cmb_id ); ?>" class="group">
				<?php cmb2_metabox_form( $metabox->cmb_id, $metabox->cmb_id ); ?>
			</div>
		<?php 
	}
	
	public function cmb2_init() {
		
		// General Options
		
		$general_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'site_options',
			'title' => 'General',
			'hookup' => false,
			'show_on' => array(
				'key' => 'options-page',
				'value' => array( LG_PREFIX . 'site_options' )
			)
		) );
		
		$general_metabox->add_field( array(
			'name' => __( 'Logo', 'localgov' ),
			'id' => 'logo',
			'type' => 'file',
		) );
		
		$general_metabox->add_field( array(
			'name' => __( 'Favicon', 'localgov' ),
			'id' => 'favicon',
			'type' => 'file',
		) );
		
		$general_metabox->add_field( array(
			'name' => __( 'Copyright Text', 'localgov' ),
			'id' => 'copyright_text',
			'type' => 'textarea_small'
		) );
		
		$this->metaboxes[] = $general_metabox;
		
		
		// Navigation Options
		
		$navigation_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'site_options_navigation',
			'title' => 'Navigation',
			'hookup' => false,
			'show_on' => array(
				'key' => 'options-page',
				'value' => array( LG_PREFIX . 'site_options_navigation' )
			)
		) );
		
		$navigation_metabox->add_field( array(
			'name' => __( 'Primary Menu Depth', 'localgov' ),
			'id' => 'primary_menu_depth',
			'type' => 'select',
			'default' => '2',
			'options' => array(
				'1' => '1',
				'2' => '2',
				'3' => '3'
			)
		) );
		
		$this->metaboxes[] = $navigation_metabox;
		
		
		// Social Options
		
		$social_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'site_options_social',
			'title' => 'Social',
			'hookup' => false,
			'show_on' => array(
				'key' => 'options-page',
				'value' => array( LG_PREFIX . 'site_options_social' )
			)
		) );
		
		$group_field_id = $social_metabox->add_field( array(
			'id' => 'social_links',
			'type' => 'group',
			'options' => array(
				'group_title' => __( 'Social Link {#}', 'localgov' ),
				'add_button' => __( 'Add Another Link', 'localgov' ),
				'remove_button' => __( 'Remove Link', 'localgov' ),
				'sortable' => true
			)
		) );
		
		$social_metabox->add_group_field( $group_field_id, array(
			'name' => __( 'Type', 'localgov' ),
			'id' => 'type',
			'type' => 'select',
			'options' => $this->social_types
		) );
		
		$social_metabox->add_group_field( $group_field_id, array(
			'name' => __( 'URL', 'localgov' ),
			'id' => 'url',
			'type' => 'text_url'
		) );
		
		$this->metaboxes[] = $social_metabox;
		
		
		// Contact Options
		
		$contact_metabox = new_cmb2_box( array(
			'id' => LG_PREFIX . 'site_options_contact',
			'title' => 'Contact Info',
			'hookup' => false,
			'show_on' => array(
				'key' => 'options-page',
				'value' => array( LG_PREFIX . 'site_options_contact' )
			)
		) );
		
		$contact_metabox->add_field( array(
			'name' => __( 'Phone', 'localgov' ),
			'id' => 'phone',
			'type' => 'text'
		) );
		
		$contact_metabox->add_field( array(
			'name' => __( 'Fax', 'localgov' ),
			'id' => 'fax',
			'type' => 'text'
		) );
		
		$contact_metabox->add_field( array(
			'name' => __( 'Address', 'localgov' ),
			'id' => 'address_physical',
			'type' => 'textarea_small'
		) );
		
		$contact_metabox->add_field( array(
			'name' => __( 'Mailing Address', 'localgov' ),
			'id' => 'address_mail',
			'type' => 'textarea_small'
		) );
		
		$this->metaboxes[] = $contact_metabox;
		
	}
	
}

SiteSettings::instance();
