<?php

namespace localgovernment;

class PageMenu_Widget extends \WP_Widget {


	/**
	 * Register widget with Wordpress
	 */
	function __construct() {
		parent::__construct(
			'PageMenuWidget',
			__('LG: Page Menu', 'localgovernment'),
			array( 
				'description' => __( 'Displays a menu using page heirarchy.', 'localgovernment')
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
		
		if( !is_page() ) {
			return;
		}
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		$start_page_depth = 2;
		$menu_depth = 2;

		$page = get_post();
		
		$page_ids = array_reverse( get_ancestors( $page->ID, 'page' ) );
		array_push( $page_ids, $page->ID );
		
		if( count( $page_ids ) < $start_page_depth )
		{
			return;
		}
		
		$child_of = $page_ids[$start_page_depth - 1];
		
		$children = wp_list_pages("title_li=&child_of=".$child_of."&depth=" . $menu_depth . "&echo=0");
		$post_title = '<a href="' . get_permalink( $child_of ) . '">' . get_the_title( $child_of ) . '</a>';
		
		if( !$children ) {
			return;
		}

		echo $args['before_widget'];
		
		if( !empty( $instance['title']) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		else {
			echo $args['before_title'] . $post_title . $args['after_title'];
		}
		
		echo '<ul class="menu-list">';
		echo $children;
		echo '</ul>';
		
		echo $args['after_widget'];
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
			'title' => ''
		));
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
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
		
		return $instance;
	}
}

register_widget( 'localgovernment\PageMenu_Widget' );