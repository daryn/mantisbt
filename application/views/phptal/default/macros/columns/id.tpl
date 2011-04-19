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

<td metal:define-macro="value" class="column-id">
    <a tal:omit-tag="not: exists: bug/id" tal:attributes="title php: show_bug_link_details ? '[' . bug.statusLabel . '] ' . bug.summary : '';class php:bug.isResolved ? 'resolved':'';href bug/viewUrl" tal:content="bug/formattedId"></a>
</td>
