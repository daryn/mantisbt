<html xmlns="http://www.w3.org/1999/xhtml" metal:use-macro="macros/layout.tpl/layout">
<!--
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
-->

	<div metal:fill-slot="content">
		<br />
		<table class="width100" cellspacing="1">
			<thead><tr class="bug-nav">
				<td class="form-title" colspan="', $t_bugslist ? '3' : '4', '">
					<span tal:replace="lang/bug_view_title"></span>
					&#160;<span class="small">
					print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ), false, 'jump-to-bugnotes' );

# Send Bug Reminder
if ( $tpl_show_reminder_link ) {
	print_bracket_link( $tpl_bug_reminder_link, lang_get( 'bug_reminder' ), false, 'bug-reminder' );
}

if ( !is_blank( $tpl_wiki_link ) ) {
	print_bracket_link( $tpl_wiki_link, lang_get( 'wiki' ), false, 'wiki' );
}

foreach ( $tpl_links as $t_plugin => $t_hooks ) {
	foreach( $t_hooks as $t_hook ) {
		if ( is_array( $t_hook ) ) {
			foreach( $t_hook as $t_label => $t_href ) {
				if ( is_numeric( $t_label ) ) {
					print_bracket_link_prepared( $t_href );
				} else {
					print_bracket_link( $t_href, $t_label );
				}
			}
		} else {
			print_bracket_link_prepared( $t_hook );
		}
	}
}
					</span></td>

					# prev/next links
					<td tal:condition="bugslist" class="center prev-next-links"><span class="small">';

						$t_bugslist = explode( ',', $t_bugslist );
						$t_index = array_search( $f_bug_id, $t_bugslist );
						if ( false !== $t_index ) {
							if ( isset( $t_bugslist[$t_index-1] ) ) {
								print_bracket_link( 'bug_view_page.php?bug_id='.$t_bugslist[$t_index-1], '&lt;&lt;', false, 'previous-bug' );
							}

							if ( isset( $t_bugslist[$t_index+1] ) ) {
								print_bracket_link( 'bug_view_page.php?bug_id='.$t_bugslist[$t_index+1], '&gt;&gt;', false, 'next-bug' );
							}
						}
					</span></td>

					<td class="right alternate-views-links" colspan="2">
						<span tal:condition="history_link" class="small">
						print_bracket_link( $tpl_history_link, lang_get( 'bug_history' ), false , 'bug-history' );
						</span>
						<span class="small">
							print_bracket_link( $tpl_print_link, lang_get( 'print' ), false, 'print' );
						</span>
					</td>
				</tr>
				<tr tal:condition="top_buttons_enabled" class="top-buttons">
					<td colspan="6">
					html_buttons_view_bug_page( $tpl_bug_id );
					</td>
				</tr>
			</thead>

			<tfoot tal:condition="bottom_buttons_enabled">
				<tr class="bottom-buttons">
					<td colspan="6">html_buttons_view_bug_page( $tpl_bug_id );</td>
				</tr>
			</tfoot>
			<tbody>
				<tal:block tal:condition="php: show_id OR show_project OR show_category OR show_view_state OR show_date_submitted OR show_last_updated">
				<tr class="bug-header">
					<th class="bug-id category" width="15%">', $tpl_show_id ? lang_get( 'id' ) : '', '</th>
					<th class="bug-project category" width="20%">', $tpl_show_project ? lang_get( 'email_project' ) : '', '</th>
					<th class="bug-category category" width="15%">', $tpl_show_category ? lang_get( 'category' ) : '', '</th>
					<th class="bug-view-status category" width="15%">', $tpl_show_view_state ? lang_get( 'view_status' ) : '', '</th>
					<th class="bug-date-submitted category" width="15%">', $tpl_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</th>
					<th class="bug-last-modified category" width="20%">', $tpl_show_last_updated ? lang_get( 'last_update' ) : '','</th>
				</tr>
				<tr ', helper_alternate_class(null, 'row-1 bug-header-data', 'row-2 bug-header-data'), '>
					<td class="bug-id" tal:content="formatted_bug_id">formatted_bug_id</td>
					<td class="bug-project" tal:content="project_name">project_name</td>
					<th class="bug-category" tal:content="category">category</th>
					<td class="bug-view-status" tal:content="bug_view_state_enum">bug_view_state_enum</td>
					<td class="bug-date-submitted" tal:content="date_submitted">date_submitted</td>
					<td class="bug-last-modified" tal:content="last_updated">last_updated</td>
				</tr>
				<tr class="spacer"><td colspan="6"></td></tr>
				</tal:block>

				<tr tal:condition="show_reporter" ', helper_alternate_class(), '>
					<th class="bug-reporter category" tal:content="lang/reporter">Rreporter</th>
					<td class="bug-reporter">
						print_user_with_subject( $tpl_bug->reporter_id, $tpl_bug_id );
					</td>
					<td colspan="4">&#160;</td>
				</tr>
				<tr tal:condition="php: show_handler OR show_due_date" tal:attributes="class helper/alternateClass">
					<th class="bug-assigned-to category" tal:content="lang/assigned_to">Assigned To</th>
					<td class="bug-assigned-to">print_user_with_subject( $tpl_bug->handler_id, $tpl_bug_id );</td>
					<th tal:condition="show_due_date" class="bug-due-date category" tal:content="lang/due_date">Due Date</th>
					<td tal:condition="show_due_date" tal:attributes="class php: bug_overdue ? 'bug-due-date overdue': bug_due_date" tal:content="bug_due_date">Due Date</td>
					<td tal:condition="show_handler_row_spacer" tal:attributes="colspan show_handler_row_spacer">&#160;</td>
				</tr>
				<tr tal:condition="php: show_priority OR show_severity OR show_reproducibility" tal:attributes="class helper/alternateClass">
					<th tal:condition="show_priority" class="bug-priority category" tal:content="lang/priority">Priority</th>
					<td tal:condition="show_priority" class="bug-priority" tal:content="priority"></td>
					<th tal:condition="show_severity" class="bug-severity category" tal:content="lang/severity">Severity</th>
					<td tal:condition="show_severity" class="bug-severity" tal:content="severity"></td>
					<th tal:condition="show_reproducibility" class="bug-reproducibility category" tal:content="lang/reproducibility">Reproducibility</th>
					<td tal:condition="show_reproducibility" class="bug-reproducibility" tal:content="reproducibility"></td>
					<td tal:condition="priority_row_spacer" tal:attributes="colspan priority_row_spacer">&#160;</td>
				</tr>
				<tr tal:condition="php: show_status OR show_resolution" tal:attributes="class helper/alternateClass">
					<th tal:condition="show_status" class="bug-status category" tal:content="lang/status">Status</th>
					<td tal:condition="show_status" class="bug-status" bgcolor="', get_status_color( $tpl_bug->status ), '">', $tpl_status, '</td>
					<th tal:condition="show_resolution" class="bug-resolution category" tal:content="lang/resolution">Resolution</th>
					<td tal:condition="show_resolution" class="bug-resolution" tal:content="resolution">resolution</td>
					<td tal:condition="status_row_spacer" tal:attributes="colspan status_row_spacer">&#160;</td>
				</tr>
				<tr tal:condition="php: show_projection OR show_eta" tal:attributes="class helper/alternateClass">
					<th tal:condition="show_projection" class="bug-projection category" tal:content="lang/projection">Projection</th>
					<td tal:condition="show_projection" class="bug-projection" tal:content="projection"></td>
					<th tal:condition="show_eta" class="bug-eta category" tal:content="lang/eta">Eta</th>
					<td tal:condition="show_eta" class="bug-eta" tal:content="eta"></td>
					<td tal:condition="projection_row_spacer" tal:attributes="colspan projection_row_spacer">&#160;</td>
				</tr>
				<tr tal:condition="php: show_platform OR show_os OR show_os_version" tal:attributes="class helper/alternateClass">
					<th tal:condition="show_platform" class="bug-platform category" tal:content="lang/platform">Platform</th>
					<td tal:condition="show_platform" class="bug-platform" tal:content="platform">Platform</td>
					<th tal:condition="show_os" class="bug-os category" tal:content="lang/os">OS</th>
					<td tal:condition="show_os" class="bug-os" tal:content="os">os</td>
					<th tal:condition="show_os_version" class="bug-os-version category" tal:content="lang/os_version">OS Version</th>
					<td tal:condition="show_os_version" class="bug-os-version" tal:content="os_version">os version</td>
					<td tal:condition="platform_row_spacer" tal:attributes="colspan platform_row_spacer">&#160;</td>
				</tr>
				<tr tal:condition="php: show_product_version OR show_product_build" tal:attributes="class helper/alternateClass">
					<th tal:condition="show_product_version" class="bug-product-version category" tal:content="lang/product_version">Product Version</th>
					<td tal:condition="show_product_version" class="bug-product-version" tal:content="product_version_string">product_version_string</td>
					<th tal:condition="show_product_build" class="bug-product-build category" tal:content="lang/product_build">Product Build</th>
					<td tal:condition="show_product_build" class="bug-product-build" tal:content="product_build">product_build</td>
					<td tal:condition="version_row_spacer" tal:attributes="colspan version_row_spacer">&#160;</td>
				</tr>
				<tr tal:condition="php: show_target_version OR show_fixed_in_version" tal:attributes="class helper/alternateClass">
					<th tal:condition="show_target_version" class="bug-target-version category" tal:content="lang/target_version">Target Version</th>
					<td tal:condition="show_target_version" class="bug-target-version" tal:content="target_version_string">target_version_string</td>
					<th tal:condition="show_fixed_in_version" class="bug-fixed-in-version category" tal:content="lang/fixed_in_version">Fixed In Version</th>
					<td tal:condition="show_fixed_in_version" class="bug-fixed-in-version" tal:content="fixed_in_version_string">fixed_in_version_string</td>
					<td tal:condition="target_version_row_spacer" tal:attributes="colspan target_version_row_spacer">&#160;</td>
				</tr>
				<tal:block tal:condition="exists: bug_details_event" tal:repeat="event_macro bug_details" metal:use-macro="${event_macro}"></tal:block>
				<tr class="spacer"><td colspan="6"></td></tr>
				<tr tal:condition="php: show_summary" tal:attributes="class helper/alternateClass">
					<th class="bug-summary category" tal:content="lang/summary">Summary</th>
					<td class="bug-summary" colspan="5" tal:content="summary">summary</td>
				</tr>
				<tr tal:condition="php: show_description" tal:attributes="class helper/alternateClass">
					<th class="bug-description category" tal:content="lang/description">Description</th>
					<td class="bug-description" colspan="5" tal:content="description">description</td>
				</tr>
				<tr tal:condition="php: show_steps_to_reproduce" tal:attributes="class helper/alternateClass">
					<th class="bug-steps-to-reproduce category" tal:content="lang/steps_to_reproduce">Steps To Reproduce</th>
					<td class="bug-steps-to-reproduce" colspan="5" tal:content="steps_to_reproduce">steps_to_reproduce</td>
				</tr>
				<tr tal:condition="php: show_additional_information" tal:attributes="class helper/alternateClass">
					<th class="bug-additional-information category" tal:content="lang/additional_information">Additional Information</th>
					<td class="bug-additional-information" colspan="5" tal:content="additional_information">additional_information</td>
				</tr>
				<tr tal:condition="php: show_tags" tal:attributes="class helper/alternateClass">
					<th class="bug-tags category" tal:content="lang/tags">Tags</th>
					<td class="bug-tags" colspan="5">tag_display_attached( $tpl_bug_id );</td>
				</tr>
				<tr tal:condition="php: can_attach_tag" tal:attributes="class helper/alternateClass">
					<th class="bug-attach-tags category" tal:content="lang/tag_attach_long">Tag Attach Long</th>
					<td class="bug-attach-tags" colspan="5">print_tag_attach_form( $tpl_bug_id );</td>
				</tr>
				<tr class="spacer"><td colspan="6"></td></tr>

				<tr tal:repeat="cf custom_fields" tal:attributes="class helper/alternateClass">
					<th class="bug-custom-field category">string_display( lang_get_defaulted( $t_def['name'] ) )</th>
					<td class="bug-custom-field" colspan="5">print_custom_field_value( $t_def, $t_id, $f_bug_id );</td>
				</tr>

				<tr tal:condition="custom_fields_found" class="spacer"><td colspan="6"></td></tr>
				<tr id="attachments" tal:condition="php: show_attachments" tal:attributes="class helper/alternateClass">
					<th class="bug-attachments category" tal:content="lang/attached_files">Attached Files</th>
					<td class="bug-attachments" colspan="5">print_bug_attachments_list( $tpl_bug_id );</td>
				</tr>
			</tbody>
		</table>
		<div tal:condition="show_sponsorships_box" metal:use-macro="macros/bug_sponsorship_list_view.tpl"></div>
		<div tal:condition="show_relationships_box" metal:use-macro="macros/relationships_box.tpl"></div>
		<div tal:condition="show_upload_form" metal:use-macro="macros/bug_file_upload.tpl"></div>
		<div tal:condition="show_monitor_box" metal:use-macro="macros/bugnote_monitor_list_view.tpl"></div>

		<div tal:condition="bugnote_order_asc" metal:use-macro="macros/bugnote_add.tpl"></div>
		<div tal:condition="show_monitor_box" metal:use-macro="macros/bugnote_view.tpl"></div>
		<div tal:condition="bugnote_order_desc" metal:use-macro="macros/bugnote_add.tpl"></div>

		<tal:block tal:condition="exists: view_bug_extra" tal:repeat="event_macro view_bug_extra" metal:use-macro="${event_macro}"></tal:block>
		<div tal:condition="show_time_tracking" metal:use-macro="macros/bugnote_stats.tpl"></div>
		<div tal:condition="show_history" metal:use-macro="macros/history.tpl"></div>
	</div>
</html>
