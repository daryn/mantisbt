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

<th metal:define-macro="title" class="column-title column-overdue">
	<a tal:omit-tag="column/isTargetCSV" tal:attributes="href column/titleUrl;class string:overdue-icon" tal:content="lang/overdue"></a>
</th>
<td metal:define-macro="value" class="column-value column-overdue" tal:define="due_date short_date:bug/due_date">
	<span tal:condition="php: bug.canViewDueDate() AND bug.isOverdue()" tal:attributes="title string:${lang/overdue}. ${lang/due_date_was_label} ${due_date}" tal:content="string:${lang/overdue}. ${lang/due_date_was_label} ${due_date}" class="overdue-icon"></span>
</td>
