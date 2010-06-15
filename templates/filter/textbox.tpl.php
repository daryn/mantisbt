<input 
	name="<?php echo string_attribute( $t_field->field ) ?>" 
	type="text" 
	value="<?php echo string_attribute( $t_field->filter_value ); ?>" 
	<?php echo ( $t_field->size > 0 ? " size=\"$t_field->size\"" : '' ) ?> 
/>
