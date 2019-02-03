<?php

namespace localgov;

class Submenu_Widget extends \WP_Widget {

	public static $start_depth = 1;
	public static $max_depth = 3;


	/**
	 * Register widget with Wordpress
	 */
	function __construct() {
		parent::__construct(
			'SubmenuWidget',
			__('LG: Submenu', 'localgov'),
			array( 
				'description' => __( 'Displays a submenu according to current post/page.', 'localgov')
			)
		);
	}
	
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		$content = $this->content( $args, $instance);
		
		echo $args['before_widget'];
		
		if( !empty( $instance['title']) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		echo $content;
		
		echo $args['after_widget'];
	}
	
	public function content( $instance=array() ) {
		
		$depth = 2;
		$start_depth = self::$start_depth;
		$max_depth = self::$max_depth;
		
		if( 
			isset( $instance['start_depth'] ) 
			&& is_numeric( $instance['start_depth'] )
		) {
			$start_depth = $instance['start_depth'];
		}
		
		if( 
			isset( $instance['max_depth'] ) 
			&& is_numeric( $instance['max_depth'] )
		) {
			$max_depth = $instance['max_depth'];
		}
		
		$nav_menu = wp_nav_menu(array(
			'menu'              => 'primary',
			'theme_location'    => 'primary',
			'depth'             => $depth,
			'menu_class'        => 'menu-list',
			'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
			'lg_submenu'	=> true,
			'lg_start_depth' => $start_depth,
			'lg_max_depth'	=> $max_depth,
			'echo' => false
			
		));
		
		if(empty($nav_menu) ) {
			return;
		}
		
		return $nav_menu;
		
	}
	
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$instance = wp_parse_args((array) $instance, array(
			'title' => 'Menu',
			'start_depth' => self::$start_depth,
			'max_depth' => self::$max_depth
		));
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'start_depth' ); ?>"><?php _e( 'Start Depth:' ); ?>
	<input id="<?php echo $this->get_field_id( 'start_depth' ); ?>" name="<?php echo $this->get_field_name('start_depth'); ?>" type="text" value="<?php echo $instance['start_depth']; ?>" size="2">
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'max_depth' ); ?>"><?php _e( 'Max Depth:' ); ?>
	<input id="<?php echo $this->get_field_id( 'max_depth' ); ?>" name="<?php echo $this->get_field_name('max_depth'); ?>" type="text" value="<?php echo $instance['max_depth']; ?>" size="2">
</p>
<?php
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
	
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['start_depth'] = ( isset( $new_instance['start_depth'] ) ) ? strip_tags( $new_instance['start_depth'] ) : '';
		$instance['max_depth'] = ( isset( $new_instance['max_depth'] ) ) ? strip_tags( $new_instance['max_depth'] ) : '';
		
		return $instance;
	}
}