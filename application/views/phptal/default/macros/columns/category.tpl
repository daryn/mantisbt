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

<td metal:define-macro="value" class="column-value column-category">
	<span class="bug-project" tal:condition="bug/showProjectLink">
		<a tal:omit-tag="column/isTargetCSV" tal:attributes="href column/titleUrl;class column/sortClass|nothing" tal:content="project_name: bug/project_id"></a>
	</span>
	<span class="category-name" tal:content="category_fullname: bug"></span>
</td>
