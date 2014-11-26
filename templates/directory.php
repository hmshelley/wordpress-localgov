<?php if( !empty( $grouped_results) ): ?>
<table>

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
			
			$title = ( !empty ($member[0]['title'] ) ) ? '<br>' . $member[0]['title'] : '';
			
			$address = ( !empty ($member[0]['address'] ) ) ? $member[0]['address'] . '<br>' : '';
			$address .= ( !empty ($member[0]['city'] ) ) ? $member[0]['city'] : '';
			$address .= ( !empty ($member[0]['zip_code'] ) ) ? ', ' . $member[0]['zip_code'] : '';
			
			$phone = ( !empty ($member[0]['phone'] ) ) ? $member[0]['phone'] : '';
			
			$email = ( !empty ($member[0]['email'] ) ) ? '<a href="mailto:' . $member[0]['email'] . '">' . $member[0]['email'] . '</a>': '';
		?>
	
		<tr>
			<?php foreach($args['fields'] as $field): ?>
			
				<td>
					<span class="<?php echo $field; ?>">
					<?php 
						switch($field) {
							case 'name': 
								echo $name;
								echo $title;
								break;
							case 'address':
								echo $address;
								break;
							case 'phone':
								echo $phone;
								break;
							case 'email':
								echo $email;
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