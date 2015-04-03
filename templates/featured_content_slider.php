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
		
		<div class="row lg-slide lg-slide-<?php echo $i ?> item<?php echo ($i == 0) ? ' active' : '' ?>">
				<div class="col-md-4 lg-slide-col-content">
					<div class="lg-slide-content">
						<?php
						
							$title = get_the_title();
															
							$featured_content = get_post_meta( $post->ID, LG_PREFIX . 'featured_content' );
							
							if( !empty ( $featured_content[0]['title'] ) ) {
								$title = $featured_content[0]['title'];
							}
						?>
						
						<h2><?php echo $title; ?></h2>
						<p><?php echo wpautop( get_the_excerpt() ); ?></p>
						
						<?php if( !isset( $featured_content[0]['show_more_link'] ) || $featured_content[0]['show_more_link'] ): ?>
							<p><a href="<?php echo get_the_permalink(); ?>" class="btn btn-primary"><span class="glyphicon-chevron-right"></span> Read More</a></p>
						<?php endif; ?>
						
					</div>
				</div>
				<div class="col-md-8 lg-slide-col-image">
					<?php 
						if( has_post_thumbnail() ) {
							echo '<div class="lg-slide-mobile-image">';
							echo get_the_post_thumbnail( $post->ID, 'large' );
							echo '</div>';
							
							$thumbnail_id = get_post_thumbnail_id( $post->ID );
							$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'large' );
							$thumbnail_url = $thumbnail['0'];
							
							echo '<div class="lg-slide-bg-image" style="background-image: url('.$thumbnail_url.')"></div>';
							
							$attachment = get_post( $thumbnail_id ); 
							if( !empty( $attachment->post_content ) ) {
								echo '<cite>' . $attachment->post_content . '</cite>';
							}
						}
					?>
				</div>
		</div><!-- /.lg-slide -->
		
		<?php endforeach; wp_reset_postdata(); ?>
		
	</div><!-- /.carousel-inner -->
	
	<!-- Controls -->
	<?php if( count($featured_posts) > 1): ?>
	<a class="left carousel-control" href="#lg-content-slider-<?php echo $lg_featured_id ?>" role="button" data-slide="prev">
		<span class="glyphicon-chevron-left" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="right carousel-control" href="#lg-content-slider-<?php echo $lg_featured_id ?>" role="button" data-slide="next">
		<span class="glyphicon-chevron-right" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>
	<?php endif; ?>
</div>