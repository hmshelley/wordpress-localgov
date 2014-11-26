<?php

class FM_Context_Submenu_Network extends Fieldmanager_Context_Submenu {

	
	/**
	 * Create a submenu page out of a field
	 * @param string $parent_slug
	 * @param string $page_title
	 * @param string $menu_title
	 * @param string $capability
	 * @param string $menu_slug
	 * @param Fieldmanager_Field $fm
	 */
	public function __construct( $parent_slug, $page_title, $menu_title = Null, $capability = 'manage_options', $menu_slug = Null, $fm = Null, $already_registered = False ) {
		parent::__construct( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $fm, true);
	
		add_action( 'network_admin_menu', array( $this, 'register_submenu_page' ) );
	}

	/**
	 * Helper to attach element_markup() to add_meta_box(). Prints markup for options page.
	 * @return void.
	 */
	public function render_submenu_page() {
		$values = get_site_option( $this->fm->name, null );
		?>
		<div class="wrap">
			<?php if ( ! empty( $_GET['msg'] ) && 'success' == $_GET['msg'] ) : ?>
				<div class="updated success"><p><?php esc_html_e( 'Options updated', 'fieldmanager' ); ?></p></div>
			<?php endif ?>

			<h2><?php echo esc_html( $this->page_title ) ?></h2>

			<form method="POST" id="<?php echo esc_attr( $this->uniqid ) ?>">
				<div class="fm-submenu-form-wrapper">
					<input type="hidden" name="fm-options-action" value="<?php echo sanitize_title( $this->fm->name ) ?>" />
					<?php wp_nonce_field( 'fieldmanager-save-' . $this->fm->name, 'fieldmanager-' . $this->fm->name . '-nonce' ); ?>
					<?php echo $this->fm->element_markup( $values ); ?>
				</div>
				<?php submit_button( $this->submit_button_label, 'submit', 'fm-submit' ) ?>
			</form>
		</div>
		<?php

		// Check if any validation is required
		$fm_validation = Fieldmanager_Util_Validation( $this->uniqid, 'submenu' );
		$fm_validation->add_field( $this->fm );
	}
	
	/**
	 * Save a submenu page
	 * @return void
	 */
	public function handle_submenu_save() {
		if ( ! empty( $_POST ) && ! empty( $_GET['page'] ) && $_GET['page'] == $this->menu_slug && current_user_can( $this->capability ) ) {
			if ( $this->save_submenu_data() ) {
				wp_redirect( add_query_arg( array( 'page' => $this->menu_slug, 'msg' => 'success' ), network_admin_url( $this->parent_slug ) ) );
				exit;
			}
		}
	}

	public function save_submenu_data() {
		// Make sure that our nonce field arrived intact
		if( ! wp_verify_nonce( $_POST['fieldmanager-' . $this->fm->name . '-nonce'], 'fieldmanager-save-' . $this->fm->name ) ) {
			$this->fm->_unauthorized_access( __( 'Nonce validation failed', 'fieldmanager' ) );
		}

		$this->fm->data_id = $this->fm->name;
		$this->fm->data_type = 'options';
		$current = get_site_option( $this->fm->name, null );
		$value = isset( $_POST[ $this->fm->name ] ) ? $_POST[ $this->fm->name ] : "";
		$data = $this->fm->presave_all( $value, $current );
		$data = apply_filters( 'fm_submenu_presave_data', $data, $this );

		if ( isset( $current ) ) {
			update_site_option( $this->fm->name, $data );
		} else {
			add_site_option( $this->fm->name, $data, '', $this->wp_option_autoload ? 'yes' : 'no' );
		}

		return true;
	}
}
