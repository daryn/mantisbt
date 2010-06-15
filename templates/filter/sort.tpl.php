<?php
$t_sort_count = count( $t_field->filter_value );
$t_sort_options = $t_field->options();
$t_dir_options = $t_field->directionOptions();
$t_option_count = count( $t_sort_options );
$t_sort_field = $t_field->htmlSortFieldName(); 
$t_direction_field = $t_field->htmlSortDirectionName(); 
$i=0;
if( is_array( $t_field->filter_value ) ) {
	foreach( $t_field->filter_value AS $t_sort ) {
		$t_sort_id = FILTER_PROPERTY_SORT_FIELD_NAME . '_' . $i;
		$t_dir_id = FILTER_PROPERTY_SORT_DIRECTION . '_' . $i;
		?>
		<select id="<?php echo $t_sort_id; ?>" name="<?php echo $t_sort_field; ?>">
			<?php
			foreach( $t_sort_options AS $t_option ) { 
				$t_selected = '';
				if( $t_sort[FILTER_PROPERTY_SORT_FIELD_NAME] == $t_option['value'] ) {
					$t_selected = 'selected="selected"';
				}
			?>
			<option value="<?php echo $t_option['value']; ?>" <?php echo $t_selected; ?>><?php echo $t_option['label']; ?></option>
			<?php } ?>
		</select>
	
		<select id="<?php echo $t_dir_id; ?>" name="<?php echo $t_direction_field; ?>"><?php
			foreach( $t_dir_options AS $t_option ) {
				$t_selected = '';
				if( $t_sort[FILTER_PROPERTY_SORT_DIRECTION] == $t_option['value'] ) {
					$t_selected = 'selected="selected"';
				} ?>
			<option value="<?php echo $t_option['value']; ?>" <?php echo $t_selected; ?>><?php echo $t_option['label']; ?></option>
			<?php } ?>
		</select>
	<?php 
		if( $i < $t_sort_count ) {
			echo ',';
		}
		$i++;
	}
}
# only display this if there are more fields available for sorting
if( $t_sort_count < $t_option_count ) {
	$t_sort_id = FILTER_PROPERTY_SORT_FIELD_NAME . '_' . $i;
	$t_dir_id = FILTER_PROPERTY_SORT_DIRECTION . '_' . $i;
?>
	<select id="<?php echo $t_sort_id; ?>" name="<?php echo $t_sort_field; ?>">
		<option value=''></option>
		<?php
		foreach( $t_sort_options AS $t_option ) {
		if( !in_array( $t_option['value'], $t_field->sort_field ) ) { ?>
		<option value="<?php echo $t_option['value']; ?>"><?php echo $t_option['label']; ?></option>
		<?php }
		} ?>
	</select>

	<select id="<?php echo $t_dir_id; ?>" name="<?php echo $t_direction_field; ?>">
		<option value=''></option><?php
		foreach( $t_dir_options AS $t_option ) { ?>
		<option value="<?php echo $t_option['value']; ?>"><?php echo $t_option['label']; ?></option>
		<?php } ?>
	</select><?php
} ?>
