<div id="carousel" class="carousel slide" data-ride="carousel">

	<div class="carousel-inner">
		
		<?php global $post; ?>
		<?php foreach( $featured_posts as $i => $post ) : setup_postdata( $post ); ?>
		
		<div class="item<?php echo ($i == 0) ? ' active' : '' ?>">
			<div class="border">
				<div class="container">
					<div class="row">
						<div class="col-md-4">
							<div class="carousel-caption">
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
						<div class="col-md-8">
							<div class="featured-image"><!--
								--><?php if( has_post_thumbnail() ): ?><!--
									--><?php echo get_the_post_thumbnail( $post->ID, 'localgov-featured' ); ?><!--
									--><?php $attachment = get_post( get_post_thumbnail_id() ); 
										if( !empty( $attachment->post_content ) ):
									?><cite><?php echo $attachment->post_content; ?></cite><!--
								--><?php endif; endif; ?><!--
							--></div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- /.item -->
		
		<?php endforeach; ?>
		<?php wp_reset_postdata(); ?>
		
	</div><!-- /.carousel-inner -->
	
	<!-- Controls -->
	<?php if( count($featured_posts) > 1): ?>
	<a class="left carousel-control" href="#carousel" data-slide="prev">
	<span class="glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#carousel" data-slide="next">
	<span class="glyphicon-chevron-right"></span>
	</a>
	<?php endif; ?>
</div>