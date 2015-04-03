<?php
	$wrapper_before = '';
	$wrapper_after = '';
	$before = '';
	$after = '';

	if( 'list' == $args['format'] ) {
		$wrapper_before = '<ul>';
		$wrapper_after = '</ul>';
		$before = '<li>';
		$after = '</li>';
	}
?>

<?php foreach( $grouped_results as $key => $grouped_result ): ?>
	
	<?php if( !empty( $group_posts ) ): ?>
	<h3><?php echo $key ?></h3>
	<?php endif; ?>
	
	<?php echo $wrapper_before; ?>
	
		<?php global $post; ?>
		<?php foreach( $grouped_result as $post ): setup_postdata($post); ?>
			
			<?php echo $before; ?>
			
				<?php if('link' == $args['content_format'] ): ?>
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					
				<?php else: ?>
					<?php set_query_var( 'content_format', $args['content_format'] ); ?>
					<?php get_template_part( 'content', get_post_type() ); ?>
				<?php endif; ?>	
				
			<?php echo $after; ?>
			
		<?php endforeach; wp_reset_postdata(); ?>
			
	<?php echo $wrapper_after; ?>
	
	<?php if( $args['paging'] ): ?>
	<?php echo lg_paging_nav(); ?>
	<?php endif; ?>
	
<?php endforeach; ?>