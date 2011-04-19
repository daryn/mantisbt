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

<td metal:define-macro="value" tal:define="updated_class php: bug.hasRecentComments() ? 'recently-updated' : ''" class="column-value column-bugnotes-count" tal:comment="@todo still needs bolded when recently changed">
	<span tal:attributes="class updated_class | nothing" tal:condition="bug/getVisibleBugnoteCount" tal:content="bug/getVisibleBugnoteCount"></span>
</td>
