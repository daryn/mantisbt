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
	<div id="view-all-bug-div" metal:fill-slot="content" class="table-container form-container">
		<!-- div tal:condition="filter_show_top" metal:use-macro="macros/filter.tpl/filter"></div -->
		<div tal:condition="status_legend_show_top" metal:use-macro="macros/status_legend.tpl/status_legend"></div>

		<form name="bug_action" method="get" action="bug_actiongroup_page.php" tal:comment="# CSRF protection not required here - form does not result in modifications">
			<fieldset>
				<legend><span tal:replace="lang/viewing_bugs_title"></span></legend>
				<div id="page-range" tal:content="string:(${range_start} - ${range_end} / ${bug_count} )"></div>
				<div tal:define="list_links print_export_menu; class string:print-export-menu" metal:use-macro="macros/unordered_list.tpl/list"></div>
				<div class="buglist-nav">
					<div metal:use-macro="macros/unordered_list.tpl/list" tal:define="id string:header-bug-pager;list_links bug_list_pager;class string:bug-pager"></div>
				</div>
				<table id="buglist" cellspacing="1">
					<thead>
						<tr class="buglist-headers row-category">
							<tal:block tal:repeat="column columns">
							<th tal:condition="column/titleMacro" metal:use-macro="${column/titleMacro}"></th>
							</tal:block>
						</tr>
						<tr class="spacer">
							<td tal:attributes="colspan column_count"></td>
						</tr>
					</thead>
					<tfoot>
						<tr class="buglist-footer">
							<td tal:attributes="colspan column_count">
								<span class="floatleft">
									<tal:block tal:condition="php: show_checkboxes && config.use_javascript">
									<input type="checkbox" id="bug_arr_all" name="bug_arr_all" value="all" class="check_all" />
									<label for="bug_arr_all" tal:content="lang/select_all">Select All</label>
									</tal:block>	
									<tal:block tal:condition="show_checkboxes">
									<select name="action">
										<?php print_all_bug_action_option_list( $t_unique_project_ids ) ?>
									</select>
									<input type="submit" class="button" tal:attributes="value lang/ok" />
									</tal:block>
									&#160;
								</span>
								<div metal:use-macro="macros/unordered_list.tpl/list" tal:define="id string:footer-bug-pager;list_links bug_list_pager;class string:bug-pager"></div>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr tal:condition="sticky_bugs" tal:repeat="bug sticky_bugs" tal:attributes="class string:sticky-bug ${bug/statusLabel}-color">
							<tal:block tal:repeat="column columns"> 
							<td tal:condition="column/valueMacro" metal:use-macro="${column/valueMacro}"></td>
							</tal:block>
						</tr>
						<tr tal:condition="sticky_bugs">
							<td class="left" tal:attributes="colspan column_count" bgcolor="#999999">&#160;</td>
						</tr>
						<tr tal:condition="exists: bugs" tal:repeat="bug bugs" tal:attributes="class string:${bug/statusLabel}-color">
							<tal:block tal:condition="exists: bug" tal:repeat="column columns">
							<td tal:condition="column/valueMacro" metal:use-macro="${column/valueMacro}"></td>
							</tal:block>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</form>
		<div tal:condition="status_legend_show_bottom" metal:use-macro="macros/status_legend.tpl/status_legend"></div>
		<!-- div tal:condition="filter_show_bottom" metal:use-macro="macros/filter.tpl/filter"></div -->
	</div>
</html>
