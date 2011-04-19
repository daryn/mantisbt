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
	<div id="page-content" metal:fill-slot="content">
		<div id="account-profile-div" class="form-container">
			<form id="account-profile-form" method="post" action="account_prof_update.php">
				<fieldset class="has-required">
					<legend><span tal:content="lang/add_profile_title">Profile Title</span></legend>
					<tal:block metal:use-macro="macros/fields.tpl/hidden" tal:define="field_name string:profile_update_token;field_value security_token_add"></tal:block>
					<input type="hidden" name="action" value="add" />
					<input type="hidden" name="user_id" tal:attributes="value user_id" />
					<div tal:condition="exists: account_menu" tal:define="list_links account_menu;id string:account-menu" metal:use-macro="macros/unordered_list.tpl/list"></div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="platform" class="required"><span tal:content="lang/platform">Platform</span></label>
						<span class="input"><input id="platform" type="text" name="platform" size="32" maxlength="32" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="os" class="required"><span tal:content="lang/operating_system">Operating System</span></label>
						<span class="input"><input id="os" type="text" name="os" size="32" maxlength="32" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="os-version" class="required"><span tal:content="lang/os_version">OS Version</span></label>
						<span class="input"><input id="os-version" type="text" name="os_build" size="16" maxlength="16" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="description"><span tal:content="lang/additional_description">Additional Description</span></label>
						<span class="textarea"><textarea id="description" name="description" cols="80" rows="8"></textarea></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<span class="submit-button"><input type="submit" class="button" tal:attributes="value lang/add_profile_button" /></span>
				</fieldset>
			</form>
		</div>
		<div tal:condition="exists: profiles" id="account-profile-update-div" class="form-container" tal:comment="# Edit or Delete Profile Form BEGIN">
			<form id="account-profile-update-form" method="post" action="account_prof_update.php">
				<fieldset>
					<legend><span tal:content="lang/edit_or_delete_profiles_title">Edit Or Delete Profiles Title</span></legend>
					<tal:block metal:use-macro="macros/fields.tpl/hidden" tal:define="field_name string:profile_update_token;field_value security_token_edit"></tal:block>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="action-edit"><span tal:content="lang/edit_profile">Edit Profile</span></label>
						<span class="input"><input id="action-edit" type="radio" name="action" value="edit" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<tal:block tal:condition="not: global_profiles">
						<label for="action-default"><span tal:content="lang/make_default">Make Default</span></label>
						<span class="input"><input id="action-default" type="radio" name="action" value="make_default" /></span>
						</tal:block>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="action-delete"><span tal:content="lang/delete_profile">Delete Profile</span></label>
						<span class="input"><input id="action-delete" type="radio" name="action" value="delete" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="select-profile"><span tal:content="lang/select_profile">Select Profile</span></label>
						<span class="input">
							<select metal:use-macro="macros/fields.tpl/select" tal:define="field_id string:select-profile;field_name string:profile_id;options profiles"></select>
						</span>
						<span class="label-style">&nbsp;</span>
					</div>
					<span class="submit-button"><input type="submit" class="button" tal:attributes="value lang/submit_button" /></span>
				</fieldset>
			</form>
		</div>
	</div>
</html>
