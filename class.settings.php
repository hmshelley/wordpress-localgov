<?php

namespace localgov;

class Settings {
	
	private static $instance;
	
	/**
 	 * Option page slug
 	 * @var string
 	 */
	public $menu_slug = 'localgov-settings';
	
	/**
 	 * Option key
 	 * @var string
 	 */
	private $key = 'lg_options';
	
	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'lg_options';
	
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'LocalGov Settings';
	
	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';
	
	private function __construct() {
		/* Don't do anything, needs to be initialized via instance() method */
	}
	
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Settings;
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
		register_setting( $this->key, $this->key );
	}
	
	/**
	 * Add menu options page
	 */
	public function add_options_page() {
		
		$this->options_page = add_submenu_page( LG_ADMIN_MENU_SLUG, $this->title, $this->title, 'manage_options', $this->menu_slug, array( $this, 'admin_page_display' ) );
	}
	
	/**
	 * Admin page markup. Mostly handled by CMB2
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}
	
	function cmb2_init() {
		
		$settings_metabox = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		
		$settings_metabox->add_field( array(
			'name' => __( 'Active Modules', 'localgov' ),
			'id' => LG_PREFIX . 'active_modules',
			'type' => 'multicheck',
			'options' => array(
				'directory' => 'Directory',
				'featured_content' => 'Featured Sliders',
				'meetings' => 'Meetings',
				'newsletters' => 'Newsletters',
				'submenus' => 'Submenus',
				'template_options' => 'Template Options'
			)
		) );
	}
}

Settings::instance();
