<select <?php echo $t_view_type->filter_value == 'advanced' ? 'multiple="multiple" size="10"' : '' ; ?> name="<?php echo $t_field->htmlFieldName(); ?>">
<?php if( $t_field->has_any ) { ?>
<option value="<?php echo META_FILTER_ANY ?>" <?php echo $t_field->isAny() ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'any' )?>]</option>
<?php } ?>

<?php if( $t_field->has_none ) { ?>
<option value="<?php echo META_FILTER_NONE ?>" <?php echo $t_field->isNone( $t_field->filter_value ) ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'none' )?>]</option>
<?php } ?>

<?php if( $t_field->has_myself) { ?>
<option value="<?php echo META_FILTER_MYSELF?>" <?php echo $t_field->isMyself( $t_field->filter_value ) ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'myself' )?>]</option>
<?php } ?>

<?php if( $t_field->has_current ) { ?>
<option value="<?php echo META_FILTER_CURRENT?>" <?php echo $t_field->isCurrent( $t_field->filter_value ) ? 'selected="selected"' : '' ?>>[<?php echo lang_get( 'current' )?>]</option>
<?php }

foreach( $t_field->options() AS $t_option ) { ?>
<option value="<?php echo $t_option['value']; ?>" <?php echo $t_option['selected'] ? 'selected="selected"' : '' ?>><?php echo $t_option['label']; ?></option>
<?php } ?>
</select>
