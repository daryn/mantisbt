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

<th metal:define-macro="title" class="column-title column-attachment-count">
	<a tal:omit-tag="column/isTargetCSV" tal:attributes="href column/titleUrl;class string:attachment-icon ${column/sortClass}" tal:content="lang/attachment_count"></a>
</th>

<td metal:define-macro="value" class="center column-attachments" tal:define="count bug/getAttachmentCount;class php: config.show_attachment_indicator ? 'attachment-icon':''">
    <tal:block tal:condition="count">
		<a title="t_href_title" tal:attributes="title attachments_title: bug;href string:${bug/viewUrl}#attachments;class class|nothing" tal:content="count">Attachment Count</a>
    </tal:block>
	<tal:block tal:condition="not: count">&#160;</tal:block>
</td>
