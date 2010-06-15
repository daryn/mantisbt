<input type="hidden" id="tag_separator" value="<?php echo $t_field->separator; ?>" />

<input 
	id="<?php echo FILTER_PROPERTY_TAG_STRING ?>" 
	name="<?php echo FILTER_PROPERTY_TAG_STRING ?>" 
	type="text" 
	value="<?php echo string_attribute( $t_field->string); ?>" 
	<?php echo ( $t_field->size > 0 ? " size=\"$t_field->size\"" : '' ) ?> 
/>

<select id="<?php echo FILTER_PROPERTY_TAG_SELECT; ?>" name="<?php echo FILTER_PROPERTY_TAG_SELECT ?>">

<option value="0"><?php echo lang_get( 'tag_existing' )?></option>

<?php if( $t_field->has_none ) { ?>
<option value="<?php echo META_FILTER_NONE ?>" <?php echo $t_field->isNone( $t_field->select ) ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'none' )?>]</option>
<?php } ?>
<?php
foreach( $t_field->options() AS $t_option ) { ?>
<option value="<?php echo $t_option['value']; ?>" <?php echo $t_option['selected'] ? 'selected="selected"' : '' ?>><?php echo $t_option['label']; ?></option>
<?php } ?>
</select>

