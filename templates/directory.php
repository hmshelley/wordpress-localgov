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
	
	<?php foreach( $grouped_result as $post ): ?>
		<?php 
			$member = get_post_meta( $post->ID, LG_PREFIX . 'directory_member' );
			
			$name = ( !empty ($member[0]['first_name'] ) ) ? $member[0]['first_name'] : '';
			$name .= ( !empty ($member[0]['last_name'] ) ) ? ' ' . $member[0]['last_name'] : '';
			$name = trim($name);
			
			$address = ( !empty ($member[0]['address'] ) ) ? $member[0]['address'] . '<br>' : '';
			$address .= ( !empty ($member[0]['city'] ) ) ? $member[0]['city'] : '';
			$address .= ( !empty ($member[0]['zip_code'] ) ) ? ', ' . $member[0]['zip_code'] : '';
			
			$member_link = '<a href="' . get_permalink() . '"';
			if( !empty( $args['template_options']['member_link_attributes'] ) ) {
				foreach( $args['template_options']['member_link_attributes'] as $key => $value ) {
					$member_link .= ' ' . $key . '="' . $value . '"';
				}
			}
			$member_link .= '>';
		?>
	
		<tr>
			<?php foreach($args['template_options']['fields'] as $field): ?>
			
				<td>
					<span class="<?php echo $field; ?>">
					<?php 
						switch($field) {
							case 'photo': 
								if( !empty( $member[0]['photo'] ) ) {
									echo $member_link . wp_get_attachment_image( $member[0]['photo'], 'thumbnail' ) . '</a>';
								}
								break;
							case 'name':
								if( !empty( $member[0]['bio'] ) ) {
									echo $member_link . $name . '</a>';	
								}
								else {
									echo $name;
								}
								if ( !empty ($member[0]['title'] ) ) {
									echo '<br>' . $member[0]['title'];
								}
								break;
							case 'address':
								echo $address;
								break;
							case 'phone':
								if ( !empty ($member[0]['phone'] ) ) {
									echo $member[0]['phone'];
								}
								break;
							case 'email':
								if ( !empty ($member[0]['email'] ) ) {
									echo '<a href="mailto:' . $member[0]['email'] . '">' . $member[0]['email'] . '</a>';
								}
								break;
						}
					?>
					</span>
				</td>
				
			<?php endforeach; ?>	
		</tr>
		
	<?php endforeach; ?>
	
<?php endforeach; ?>

</table>
<?php endif; ?>