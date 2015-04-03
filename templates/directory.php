<?php if( !empty( $grouped_results) ): ?>
<table class="lg-directory-table">

<?php foreach( $grouped_results as $key => $grouped_result ): ?>
	
	<?php if( !empty($key) ): ?>
	<tr>
		<th colspan="<?php echo count( $args['fields'] ); ?>" scope="rowgroup">
			<?php $term = get_term($key, LG_PREFIX . 'directory_group'); ?>
			<?php echo $term->name; ?>
		</th>
	</tr>
	<?php endif; ?>
	
	<?php if( 
		!isset( $args['template_options']['show_headers'] )
		|| $args['template_options']['show_headers'] 
	): ?>
	<tr>
		<?php foreach( $args['template_options']['fields'] as $field_name ): ?>
			<th><small><?php echo apply_filters('lg_directory_member_field_header', $field_name ); ?></small></th>
		<?php endforeach; ?>
	</tr>
	<?php endif; ?>
	
	<?php global $post; ?>
	<?php foreach( $grouped_result as $post ): setup_postdata($post); ?>
		
		<?php $member = get_post_meta( $post->ID, LG_PREFIX . 'directory_member' ); ?>
	
		<tr>
			<?php foreach( $args['template_options']['fields'] as $field_name ): ?>
			
				<td>
					<span class="<?php echo $field_name; ?>">
					<?php
						if( isset ( $member[0] ) ) {
							
							$field_value = '';
							if( isset( $member[0][$field_name] ) ) {
								$field_value = $member[0][$field_name];
							}
							echo apply_filters('lg_directory_member_field_value', $field_value, $field_name, $member[0], $args );
						}
					?>
					</span>
				</td>
				
			<?php endforeach; ?>	
		</tr>
		
	<?php endforeach; wp_reset_postdata(); ?>
	
<?php endforeach; ?>

</table>
<?php endif; ?>