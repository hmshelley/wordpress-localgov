<?php

namespace localgov;

// TODO: Move to settings page
defined( 'LG_TWITTER_CONSUMER_KEY' )   or define( 'LG_TWITTER_CONSUMER_KEY', '' );
defined( 'LG_TWITTER_CONSUMER_SECRET' )   or define( 'LG_TWITTER_CONSUMER_SECRET', '' );
defined( 'LG_TWITTER_OAUTH_TOKEN' )   or define( 'LG_TWITTER_OAUTH_TOKEN', '' );
defined( 'LG_TWITTER_OAUTH_TOKEN_SECRET' )   or define( 'LG_TWITTER_OAUTH_TOKEN_SECRET', '' );

// https://dev.twitter.com/docs/auth/oauth/single-user-with-examples
require LG_BASE_DIR . '/lib/twitteroauth/OAuth.php';
require LG_BASE_DIR . '/lib/twitteroauth/twitteroauth.php';

class Twitter_Widget extends \WP_Widget {
	
	/**
	 * Register widget with Wordpress
	 */
	function __construct() {
		parent::__construct(
			'TwitterWidget',
			__('LG: Recent Tweets', 'localgov'),
			array( 
				'description' => __( 'Displays recent tweets.', 'localgov')
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
		
  		$connection = new \TwitterOAuth(LG_TWITTER_CONSUMER_KEY, LG_TWITTER_CONSUMER_SECRET, LG_TWITTER_OAUTH_TOKEN, LG_TWITTER_OAUTH_TOKEN_SECRET);
  		
  		$tweet_count = 3;
  		if( 
  			!empty($instance['tweet_count'])
  			&& is_numeric($instance['tweet_count'])
  		) {
  			$tweet_count = $instance['tweet_count'];
  		}
  		
  		$params = array(
  			'screen_name' => $instance['twitter_username'],
  			'count' => $tweet_count
  		);
  		
		$tweets = $connection->get("statuses/user_timeline", $params);
		
?>
<?php echo $args['before_widget']; ?>
<?php echo $args['before_title'] . $title . $args['after_title']; ?>
<ol class="tweets">
<?php foreach($tweets as $tweet): ?>
	<li>
		<div class="text"><?php echo $tweet->text; ?></div>
		<?php $time_diff = human_time_diff( strtotime($tweet->created_at), current_time('timestamp') ); ?>
		<div class="time-diff"><?php printf( __('%s ago', 'localgov'), $time_diff); ?></div>
	</li>
<?php endforeach; ?>
</ol>
<p><a href="http://twitter.com/<?php echo $instance['twitter_username'] ?>"><?php printf( __('Follow @%s', 'localgov' ),  $instance['twitter_username'] ); ?></a></p>
<?php echo $args['after_widget']; ?>
<?php	
	}
	
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$title = __( 'Tweets', 'localgov' );
		
		if ( isset( $instance[ 'title' ] )) {
			$title = $instance[ 'title' ];
		}

		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
			'twitter_username' => '',
			'tweet_count' => ''
		));
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'twitter_username' ); ?>"><?php _e( 'Twitter Username:' ); ?>
	<input class="widefat" id="<?php echo $this->get_field_id( 'twitter_username' ); ?>" name="<?php echo $this->get_field_name('twitter_username'); ?>" type="text" value="<?php echo esc_attr( $instance['twitter_username'] ); ?>">
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'tweet_count' ); ?>"><?php _e( 'Tweet Count:' ); ?>
	<input id="<?php echo $this->get_field_id( 'tweet_count' ); ?>" name="<?php echo $this->get_field_name('tweet_count'); ?>" type="text" value="<?php echo esc_attr( $instance['tweet_count'] ); ?>" size="2">
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
		$instance['twitter_username'] = ( !empty( $new_instance['twitter_username'] ) ) ? strip_tags( $new_instance['twitter_username'] ) : '';
		$instance['tweet_count'] = ( !empty( $new_instance['tweet_count'] ) ) ? strip_tags( $new_instance['tweet_count'] ) : '';
		
		return $instance;
	}
}

register_widget( 'localgov\Twitter_Widget' );
