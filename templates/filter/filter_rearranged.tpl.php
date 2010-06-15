<br />
<span id="filter">
	<span id="loading_string"><?php echo $t_loading_string_label; ?></span>

	<form method="post" name="bug_filter" id="bug_filter" action="<?php echo $t_action;?>">
		<?php # CSRF protection not required here - form does not result in modifications ?>
		<input type="hidden" name="type" value="1" />
		<?php if( $t_for_screen == false ) { ?>
		<input type="hidden" name="print" value="1" />
		<input type="hidden" name="offset" value="0" />
		<?php } ?>
		<input type="hidden" name="page_number" value="<?php echo $t_page_number ?>" />
		<input type="hidden" name="view_type" value="<?php echo $t_view_type->filter_value ?>" />
		<?php collapse_open( 'filter' ); ?>
			<table cellspacing="1" border="0" width="100%">
				<tr class="row-category2"><?php 
					dhtmlLink( $t_reporter_field, $t_for_screen ); 
					dhtmlLink( $t_status_field, $t_for_screen );
					dhtmlLink( $t_version_field, $t_for_screen );
					dhtmlLink( $t_profile_field, $t_for_screen );
					dhtmlLink( $t_date_submitted_field, $t_for_screen );
					filterField( $t_date_submitted_field, 3 );
				?></tr>
				<tr class="row-1"><?php
					filterField( $t_reporter_field);
					filterField( $t_status_field );
					filterField( $t_version_field );
					filterField( $t_profile_field );
					dhtmlLink( $t_last_updated_field, $t_for_screen );
					filterField( $t_last_updated_field, 3 );
				?></tr>
				<tr class="row-category2"><?php
					dhtmlLink( $t_handler_field, $t_for_screen );
					dhtmlLink( $t_hide_status_field, $t_for_screen );
					dhtmlLink( $t_product_build_field, $t_for_screen );
					dhtmlLink( $t_platform_field, $t_for_screen );
					dhtmlLink( $t_due_date_field, $t_for_screen );
					filterField( $t_due_date_field, 3 );
				?></tr>
				<tr class="row-1"><?php
					filterField( $t_handler_field );
					filterField( $t_hide_status_field );
					filterField( $t_product_build_field );
					filterField( $t_platform_field );
					dhtmlLink( $t_project_field, $t_for_screen );
					filterField( $t_project_field, 3 );
				?></tr>
				<tr class="row-category2"><?php
					dhtmlLink( $t_monitored_by_field, $t_for_screen );
					dhtmlLink( $t_priority_field, $t_for_screen );
					dhtmlLink( $t_target_version_field, $t_for_screen );
					dhtmlLink( $t_os_field, $t_for_screen );
					dhtmlLink( $t_category_field, $t_for_screen );
					filterField( $t_category_field, 3 );
				?></tr>
				<tr class="row-1"><?php
					filterField( $t_monitored_by_field );
					filterField( $t_priority_field );
					filterField( $t_target_version_field );
					filterField( $t_os_field );
					dhtmlLink( $t_tags_field, $t_for_screen );
					filterField( $t_tags_field, 3 );
				?></tr>
				<tr class="row-category2"><?php
					dhtmlLink( $t_note_by_field, $t_for_screen );
					dhtmlLink( $t_severity_field, $t_for_screen );
					dhtmlLink( $t_fixed_in_version_field, $t_for_screen );
					dhtmlLink( $t_os_build_field, $t_for_screen );
					dhtmlLink( $t_relationships_field, $t_for_screen );
					filterField( $t_relationships_field, 3 );
				?></tr>
				<tr class="row-1"><?php
					filterField( $t_note_by_field );
					filterField( $t_severity_field );
					filterField( $t_fixed_in_version_field );
					filterField( $t_os_build_field );
					dhtmlLink( $t_sort_field, $t_for_screen );
					filterField( $t_sort_field, 3 );
				?></tr>
				<tr class="row-category2"><?php
					dhtmlLink( $t_view_state_field, $t_for_screen );
					dhtmlLink( $t_resolution_field, $t_for_screen );
					?><td colspan="6">&nbsp;</td><?php
				?></tr>
				<tr class="row-1"><?php
					filterField( $t_view_state_field );
					filterField( $t_resolution_field );
					dhtmlLink( $t_issues_per_page_field, $t_for_screen );
					filterField( $t_issues_per_page_field );
					dhtmlLink( $t_sticky_field, $t_for_screen );
					filterField( $t_sticky_field );
					dhtmlLink( $t_changed_field, $t_for_screen );
					filterField( $t_changed_field );
				?></tr><?php
	
				$t_count = count( $t_custom_fields );
				if ( $t_count > 0 ) {
					$t_cf_chunks = array_chunk( $t_custom_fields, $t_filter_cols );
					foreach( $t_cf_chunks AS $t_row ) {
						echo '<tr class="row-category2">';
						foreach( $t_row AS $t_field ) {
							dhtmlLink( $t_field, $t_for_screen );
						}
						if( $t_count < $t_filter_cols ) {
							echo '<td colspan="', $t_filter_cols-$t_count,'">&nbsp;</td>';
						}
						echo '</tr>';
						echo '<tr class="row-1">';
						foreach( $t_row AS $t_field ) {
							filterField( $t_field );
						}
						if( $t_count < $t_filter_cols ) {
							echo '<td colspan="', $t_filter_cols-$t_count,'">&nbsp;</td>';
						}
						echo '</tr>';
					}
				}
				$t_count = count( $t_plugin_fields );
				if ( $t_count > 0 ) {
					$t_plugin_chunks = array_chunk( $t_plugin_fields, $t_filter_cols );
					foreach( $t_plugin_chunks AS $t_row ) {
						echo '<tr class="row-category2">';
						foreach( $t_row AS $t_field ) {
							dhtmlLink( $t_field, $t_for_screen );
						}
						if( $t_count < $t_filter_cols ) {
							echo '<td colspan="', $t_filter_cols-$t_count,'">&nbsp;</td>';
						}
						echo '</tr>';
						echo '<tr class="row-1">';
						foreach( $t_row AS $t_field ) {
							filterField( $t_field );
						}
						if( $t_count < $t_filter_cols ) {
							echo '<td colspan="', $t_filter_cols-$t_count,'">&nbsp;</td>';
						}
						echo '</tr>';
					}
				} ?>
			</table>
		<?php collapse_end( 'filter' ); ?>
		<span id="search_filters">
			<span id="search_filter_fields" class="filter-fields">
				<?php collapse_icon( 'filter' ); ?>
				<span id="search_filter_title" class="field-group-title"><?php echo $t_search_field->title . '&nbsp;'; ?></span>
				<span class="search-field"><input type="text" size="16" name="<?php echo $t_search_field->field; ?>" value="<?php echo string_html_specialchars( $t_search_field->filter_value ); ?>" class="search-box" /></span>
				<input type="submit" name="apply_filter_button" class="button-small" value="<?php echo lang_get( 'filter_button' )?>" />
				<?php if( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) { ?>
				<input type="submit" name="save_query_button" class="button-small" value="<?php echo lang_get( 'save_query' )?>" />
				<?php } ?>
			</span>
		</span>
	</form>
	<span id="switch_links">
		[<a id="view_type_link" href="<?php echo $f_switch_view_link; ?>"><?php echo $t_switch_view_label; ?></a>]
		[<a id="permalink" href="<?php echo $t_permalink_url; ?>"><?php echo $t_permalink_label; ?></a>]
	</span>
	<span id="other_forms">
		<?php if( count( $t_stored_queries_arr ) > 0 ) { ?>
		<form method="get" id="stored_queries" name="stored_queries" action="view_all_set.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="type" value="3" />
			<select id="source_query_id" name="source_query_id">
				<option value="-1">[<?php echo $t_reset_query_label ?>]</option>
				<option value="-1"></option>
				<?php foreach( $t_stored_queries_arr as $t_query_id => $t_stored_query ) { ?>
				<option value="<?php echo $t_query_id ?>" <?php check_selected( $t_query_id, $t_filter->getField('_source_query_id' )->filter_value ); ?>><?php echo $t_stored_query->name ?></option>
				<?php } ?>
			</select>
			<input type="submit" name="switch_to_query_button" class="button-small" value="<?php echo $t_use_query_label ?>" />
		</form>
		<form method="post" name="open_queries" action="query_view_page.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" name="switch_to_query_button" class="button-small" value="<?php echo $t_open_queries_label ?>" />
		</form><?php
		} else { ?>
		<form method="get" name="reset_query" action="view_all_set.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="type" value="3" />
			<input type="hidden" name="source_query_id" value="-1" />
			<input type="submit" name="reset_query_button" class="button-small" value="<?php echo $t_reset_query_label ?>" />
		</form><?php
		} ?>
	</span>
</span>
