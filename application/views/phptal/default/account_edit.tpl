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
		<div tal:condition="force_pw_reset" id="reset-passwd-msg" class="important-msg">
			<ul>
				<li tal:content="lang/verify_warning"></li>
				<li tal:condition="auth_can_change_password" tal:content="lang/verify_change_password"></li>
			</ul>
		</div>

		<div id="account-update-div" class="form-container">
			<form id="account-update-form" method="post" action="account_update.php">
				<fieldset tal:attributes="class php: force_pw_reset ? 'has-required' : NULL">
					<legend><span tal:content="lang/edit_account_title"></span></legend>
					<tal:block metal:use-macro="macros/fields.tpl/hidden" tal:define="field_name security_field;field_value security_token"></tal:block>
					 <div tal:condition="exists: account_menu" tal:define="list_links account_menu;id string:account-menu" metal:use-macro="macros/unordered_list.tpl/list"></div>
					<tal:block tal:condition="not: auth_can_change_password" tal:comment="# With LDAP">
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span class="display-label"><span tal:content="lang/username"></span></span>
						<span class="input"><span class="field-value" tal:content="user/username"></span></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span class="display-label"><span tal:content="lang/password"></span></span>
						<span class="input"><span class="field-value" tal:content="lang/no_password_change"></span></span>
						<span class="label-style">&nbsp;</span>
					</div>
					</tal:block>

					<tal:block tal:condition="auth_can_change_password" tal:comment="# Without LDAP">
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span class="display-label"><span tal:content="lang/username"></span></span>
						<span class="input"><span class="field-value" tal:content="user/username"></span></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="password" tal:attributes="class php: force_pw_reset ? 'required' : NULL"><span tal:content="lang/password"></span></label>
						<span class="input"><input id="password" type="password" name="password" size="32" tal:attributes="maxlength max_passlength" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<label for="password-confirm" tal:attributes="class php: force_pw_reset ? 'required' : NULL"><span tal:content="lang/confirm_password"></span></label>
						<span class="input"><input id="password-confirm" type="password" name="password_confirm" size="32" tal:attributes="maxlength max_passlength" /></span>
						<span class="label-style">&nbsp;</span>
					</div>
					</tal:block>

					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span tal:omit-tag="show_update_button" class="display-label">
							<label tal:omit-tag="not: show_update_button" for="email-field">
							<span tal:content="lang/email">Email</span>
							</label>
						</span>
						<span class="input">
							<span tal:condition="ldap" class="field-value" tal:content="user/email">useremail@somewhere.com</span>
							<tal:block tal:condition="not: ldap" metal:use-macro="macros/fields.tpl/email" tal:define="email user/email; field_name string:email"></tal:block>
						</span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span tal:omit-tag="show_update_button" class="display-label">
							<label tal:omit-tag="not: show_update_button" for="realname">
								<span tal:content="lang/realname">Real Name</span>
							</label>
						</span>
						<span class="input">
							<span tal:condition="not: show_update_button" class="field-value" tal:content="realname"></span>
							<input tal:condition="show_update_button" id="realname" name="realname" type="text" size="32" tal:attributes="value user/realname;maxlength max_realname_length" />
						</span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span class="display-label"><span tal:content="lang/access_level"></span></span>
						<span class="input"><span class="field-value" tal:content="user/access_level_label" tal:comment="This is the user's global access level"></span></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:attributes="class string:field-container ${helper/alternateClass}">
						<span class="display-label"><span tal:content="lang/access_level_project"></span></span>
						<span class="input"><span class="field-value" tal:content="current_project_access_level" tal:comment="This is the users access level for the current project"></span></span>
						<span class="label-style">&nbsp;</span>
					</div>
					<div tal:condition="projects" tal:attributes="class string:field-container ${helper/alternateClass}">
						<span class="display-label"><span tal:content="lang/assigned_projects"></span></span>
						<div class="input">
							<ul class="project-list">
								<li tal:repeat="project projects">
									<span class="project-name" tal:content="project/name">Project Name</span>
									<span class="access-level" tal:content="project/access_level">Access Level</span>
									<span class="view-state" tal:content="project/view_state">View State</span>
								</li>
							</ul>
						</div>
						<span class="label-style">&nbsp;</span>
					</div>
					<span tal:condition="show_update_button" class="submit-button"><input type="submit" class="button" tal:attributes="value lang/update_user_button" /></span>
				</fieldset>
			</form>
		</div>
		<!-- Delete Button -->
		<div tal:condition="config/allow_account_delete" class="form-container">
			<form method="post" action="account_delete.php">
				<fieldset>
					<?php echo form_security_field( 'account_delete' ) ?>
					<span class="submit-button"><input type="submit" class="button" tal:attributes="value lang/delete_account_button" /></span>
				</fieldset>
			</form>
		</div>
	</div>
</html>
