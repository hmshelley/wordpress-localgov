<?php

namespace localgovernment;

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
		add_action( 'wp', array( $this, 'wp' ) );
		
		add_filter( 'lgarchives_default_args', array( $this, 'filter_lgarchives_default_args' ), 10, 2 );
		add_filter( 'lgarchives_args', array( $this, 'filter_lgarchives_args' ) );
	}
	
	public static function filter_lgarchives_default_args( $defaults, $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'newsletter' && $args['post_type'] != 'lg_newsletter' )
		) {
			return $defaults;
		}
		
		$defaults['order_by'] = 'date_col DESC, post_title DESC';
		$defaults['date_key'] = 'lg_newsletter_date';
		$defaults['date_value'] = 'meta_value';
		
		return $defaults;
	}
	
	public static function filter_lgarchives_args( $args ) {
		
		if(	
			!isset( $args['post_type'] )
			|| ( $args['post_type'] != 'newsletter' && $args['post_type'] != 'lg_newsletter' )
		) {
			return $args;
		}
		
		$args['post_type'] = 'lg_newsletter';
		return $args;
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
			'hierarchical' => false,
			'rewrite' => array('slug' => 'newsletters'),
			'supports' => array('title')
		));
	}

	function init() {
	
		self::register_types();
		
		$fm = new \Fieldmanager_Group( array(
			'name' => LG_PREFIX . 'newsletter',
			'children' => array(
				'date' => new MonthYear('Newsletter Date', array(
					'index' => LG_PREFIX . 'newsletter_date'
				)),
				'newsletter_file' => new \Fieldmanager_Media('Newsletter File'),
				'files' => new \Fieldmanager_Group( array(
					'limit' => 0,
					'label' => 'Related File',
					'label_macro' => array( 'File: %s', 'title' ),
					'add_more_label' => 'Add Another File',
					'children' => array(
						'title' => new \Fieldmanager_Textfield( 'Title' ),
						'file' => new \Fieldmanager_Media( 'File' )
					)
				) )
			)
		));
		
		$fm->add_meta_box('Newsletter', array( LG_PREFIX . 'newsletter') );
	}
	
	function wp( $wp ) {
	
		if( 
			!is_singular() 
			|| LG_PREFIX . 'newsletter' != get_post_type()
		) {
			return;
		}
		
		$newsletter = get_post_meta( get_the_ID(), LG_PREFIX . 'newsletter');
		
		if( empty( $newsletter[0]['newsletter_file'] ) ) {
			status_header(404);
			include( get_404_template() );
			exit;
		}
		
		$url = wp_get_attachment_url( $newsletter[0]['newsletter_file'] );
		
		header( "Location: $url" );
		exit;
	}	
}

Newsletters_Module::instance();


class MonthYear extends \Fieldmanager_Select {
	
	public function __construct( $label, $options = array() ) {
		
		parent::__construct( $label, $options );
	}
	
	public function form_element( $value ) {
	
		$months = array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);
		
		$years = range(date('Y', strtotime('+1 year')), date('Y', strtotime('-50 years')));
	
		$output = '';
		
		if( empty( $value ) ) {
			$value = date('Y-m-d H:i:s');
		}
		
		$month_opts = '';
		foreach( $months as $month_value => $month_name ) {
			
			$data = array(
				'name' => $month_name,
				'value' => $month_value
			);
			
			$month_opts .= $this->form_data_element( $data, array( date('m', strtotime( $value ) ) ) );
		}
	
		$year_opts = '';
		foreach( $years as $year ) {
		
			$data = array(
				'name' => $year,
				'value' => $year
			);
		
			$year_opts .= $this->form_data_element( $data, array( date('Y', strtotime($value) ) ) );
		}
	
		$output .= sprintf(
			'<select name="%s">%s</select>',
			$this->get_form_name( '[month]' ),
			$month_opts
		);
	
		$output .= sprintf(
			'<select name="%s">%s</select>',
			$this->get_form_name( '[year]' ),
			$year_opts
		);
		
		return $output;
	}

	/**
	 * Convert date to timestamp
	 * @param $value
	 * @param $current_value
	 * @return int unix timestamp
	 */
	public function presave( $value, $current_value = array() ) {
		
		if( empty( $value['year'] ) || empty( $value['month'] ) )  {
			return 0;
		}
		
		$date = $value['year'] . '-' . $value['month'] . '-01';
		
		return $date . ' 00:00:00';
	}
}