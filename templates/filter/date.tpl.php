<label for="filter_start_date"><?php echo lang_get( 'start_date_label' ); ?></label>
<input id="filter_start_date" 
	name="<?php echo $t_field->htmlStartDateFieldName(); ?>" 
	type="text" 
	value="<?php echo string_attribute( $t_field->start_date->__toString() ); ?>" 
	<?php echo ( $t_field->size > 0 ? " size=\"$t_field->size\"" : '' ) ?> 
	class="datepicker" /><br />

<label for="filter_end_date"><?php echo lang_get( 'end_date_label' ); ?></label>
<input id="filter_end_date" 
	name="<?php echo $t_field->htmlEndDateFieldName(); ?>" 
	type="text" 
	value="<?php echo string_attribute( $t_field->end_date->__toString() ); ?>" 
	<?php echo ( $t_field->size > 0 ? " size=\"$t_field->size\"" : '' ) ?> 
	class="datepicker" />
