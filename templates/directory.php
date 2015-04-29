<?php if( !empty( $grouped_results ) ): ?>
<table class="lg-directory-table">

<?php foreach( $grouped_results as $key => $grouped_result ): ?>
	
	<?php if( !empty( $group_posts ) ): ?>
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
		
		<?php
			
			$member = array();
			$member_metabox = cmb2_get_metabox( LG_PREFIX . 'directory_member' , LG_PREFIX . 'directory_member' );
			
			foreach( $member_metabox->prop( 'fields' ) as $field_id => $field ) {
				
				$field_value = get_post_meta( $post->ID, $field_id, true );
				
				if( $field_value ) {
					$array_field_id = str_replace( LG_PREFIX . 'directory_member_', '', $field_id );
					$member[ $array_field_id ] = $field_value;
				}
			}
		
		?>
	
		<tr>
			<?php foreach( $args['template_options']['fields'] as $field_name ): ?>
			
				<td>
					<span class="<?php echo 'lg-directory-' . $field_name; ?>">
					<?php							
						$field_value = '';
						if( isset( $member[$field_name] ) ) {
							$field_value = $member[$field_name];
						}
						echo apply_filters('lg_directory_member_field_value', $field_value, $field_name, $member, $args );
					?>
					</span>
				</td>
				
			<?php endforeach; ?>	
		</tr>
		
	<?php endforeach; wp_reset_postdata(); ?>
	
<?php endforeach; ?>

</table>
<?php endif; ?>