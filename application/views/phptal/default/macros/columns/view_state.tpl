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

<th metal:define-macro="title" class="column-title column-view-state">
	<a tal:omit-tag="column/isTargetCSV" tal:attributes="href column/titleUrl;class string:private-icon ${column/sortClass}" tal:content="lang/view_status"></a>
</th>
<td metal:define-macro="value" class="column-value column-view-state">
    <span tal:condition="bug/isPrivate" tal:attributes="title lang/private" tal:content="lang/private" class="private-icon"></span>
</td>
