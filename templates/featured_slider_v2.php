<div id="lg-content-slider-<?php echo $lg_featured_id?>" class="lg-content-slider carousel slide" data-ride="carousel">
	
	<?php global $post; ?>
	
	<!-- Indicators -->
	<ol class="carousel-indicators">
		
		<?php foreach( $featured_posts as $i => $post ) : ?>
			<li data-target="#lg-content-slider-<?php echo $lg_featured_id ?>" data-slide-to="<?php echo $i ?>"<?php echo ($i == 0) ? ' class="active"' : '' ?>></li>
		<?php endforeach; ?>
	</ol>
	
	<div class="carousel-inner" role="listbox">
		
		<?php foreach( $featured_posts as $i => $post ) : setup_postdata( $post ); ?>
		
		<div class="lg-slide lg-slide-<?php echo $i ?> item<?php echo ($i == 0) ? ' active' : '' ?>">
	
			<a href="#" class="img-link">
				<?php if( has_post_thumbnail() ): ?>
					<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>
				<?php endif; ?>
			</a>					
			<div class="container">
				<div class="carousel-caption">
					<?php
						
						$title = get_post_meta( $post->ID, LG_PREFIX . 'featured_title', true );
						$title = ( $title ) ? $title : get_the_title();
						
						$excerpt = get_post_meta( $post->ID, LG_PREFIX . 'featured_excerpt', true );
						$excerpt = ( $excerpt ) ? $excerpt : get_the_excerpt();
						
						$more_link = get_post_meta( $post->ID, LG_PREFIX . 'featured_more_link', true );
						$more_link = ( $more_link ) ? $more_link : 'show';
					?>
					
					<h2><?php echo $title; ?></h2>
					<p><?php echo wpautop( $excerpt ); ?></p>
					
					<?php if( 'show' == $more_link ): ?>
							<p><a href="<?php echo get_the_permalink(); ?>" class="btn btn-default btn-sm" role="button">Read More</a></p>
					<?php endif; ?>
				</div>
			</div>
							
		</div><!-- /.lg-slide -->
		
		<?php endforeach; wp_reset_postdata(); ?>
		
	</div><!-- /.carousel-inner -->
	
	<!-- Controls -->
	<?php if( count($featured_posts) > 1): ?>
	<a class="left carousel-control" href="#lg-content-slider-<?php echo $lg_featured_id ?>" role="button" data-slide="prev">	
		<span class="icon-prev icon-angle-left" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="right carousel-control" href="#lg-content-slider-<?php echo $lg_featured_id ?>" role="button" data-slide="next">
		<span class="icon-next icon-angle-right" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>
	<?php endif; ?>
</div>