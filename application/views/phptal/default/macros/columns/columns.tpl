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

<!-- data columns -->
<td metal:define-macro="valueCustomField" class="column-custom-field">
Custom field here
</td>

<td metal:define-macro="valuePlugin" tal:omit-tag="column/isTargetCSV" class="column-plugin">
function print_column_plugin( $p_column_object, $p_bug, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	if ( $p_columns_target != COLUMNS_TARGET_CSV_PAGE ) {
		$p_column_object->display( $p_bug, $p_columns_target );
	} else {
		$p_column_object->display( $p_bug, $p_columns_target );
	}
</td>
