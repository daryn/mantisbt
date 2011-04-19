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

<!-- title columns -->
<th metal:define-macro="title" tal:omit-tag="column/isTargetCSV" tal:attributes="class string:column-title ${column/titleClass}">
	<a tal:omit-tag="column/isTargetCSV" tal:attributes="href column/titleUrl;class column/sortClass|nothing" tal:content="lang/priority_abbreviation"></a>
</th>

<td metal:define-macro="value" tal:attributes="class string:column-value ${column/valueClass}" tal:define="label enum_label: bug/priority">
	<span tal:content="bug/${column/column}Label" tal:define="class php: config.show_priority_text ? 'priority-text' : 'priority-icon priority-' . label . '-icon'" tal:attributes="title bug/priorityLabel;class php: bug.priorityIsSignificant() ? class . ' priority-significant' : class"></span>
 </td>
