<select id="<?php echo FILTER_PROPERTY_RELATIONSHIP_TYPE; ?>" name="<?php echo FILTER_PROPERTY_RELATIONSHIP_TYPE ?>">
<?php if( $t_field->has_any ) { ?>
<option value="<?php echo META_FILTER_ANY ?>" <?php echo $t_field->isAny( $t_field->relationship_type ) ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'any' )?>]</option>
<?php } ?>

<?php if( $t_field->has_none ) { ?>
<option value="<?php echo META_FILTER_NONE ?>" <?php echo $t_field->isNone( $t_field->relationship_type ) ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'none' )?>]</option>
<?php } ?>

<?php
foreach( $t_field->options() AS $t_option ) { ?>
<option value="<?php echo $t_option['value']; ?>" <?php echo $t_option['selected'] ? 'selected="selected"' : '' ?>><?php echo $t_option['label']; ?></option>
<?php } ?>
</select>

<input 
	name="<?php echo string_attribute( FILTER_PROPERTY_RELATIONSHIP_BUG ) ?>" 
	type="text" 
	value="<?php echo string_attribute( $t_field->relationship_bug ); ?>" 
	<?php echo ( $t_field->size > 0 ? " size=\"$t_field->size\"" : '' ) ?> 
/>
