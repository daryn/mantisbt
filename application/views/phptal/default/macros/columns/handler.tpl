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

<td metal:define-macro="value" class="column-value column-assigned-to">
    <a tal:define="handler bug/getHandler" tal:condition="handler"
        tal:omit-tag="not: handler/enabled" class="user"
        tal:comment="@todo see prepare_user_name and string_sanitize_url. Do we need to sanitize this beyond phptal built in and does it need an absolute url?"
        tal:attributes="href string:view_user_page.php?id=${bug/handler_id}"><span tal:omit-tag="handler/enabled" class="deleted" tal:content="handler/username">Username</span></a>
</td>
