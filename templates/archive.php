<?php foreach( $grouped_results as $key => $grouped_result ): ?>
	
	<?php if( !empty( $group_posts ) ): ?>
	<h3><?php echo $key ?></h3>
	<?php endif; ?>
	
	<ul>
	<?php foreach( $grouped_result as $post ): ?>
		
		<li><?php get_template_part( 'content', get_post_type() ); ?></li>
		
	<?php endforeach; ?>
	</ul>
	
<?php endforeach; ?>