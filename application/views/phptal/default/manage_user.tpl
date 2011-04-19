<br />
<center>
	<table class="width75">
		<tr>
			<td tal:repeat="link filter_links" tal:attributes="class php: link.selected ? bold : ''">
				<a tal:omit-tag="link/selected" tal:attributes="href link/url" tal:content="link/label">Filter link</a>
				<span tal:condition="php: repeat.link.key === 'UNUSED'">
					[<span tal:replace="unused_user_count" />]<br />
					(<span tal:replace="never_logged_in_label" />)<br />
					<form method="get" action='manage_user_prune.php' tal:comment="# CSRF protection not required here - form does not result in modifications">
						<span tal:replace="structure prune_form_security_field" />
						<input type="submit" class="button-small" tal:attributes="value prune_accounts_label" />
					</form>
				</span>
				<span tal:condition="php: repeat.link.key === 'NEW'">
					[<span tal:replace="new_user_count" />]<br />(<span tal:replace="one_week_label" />)
				</span>
			</td>
		</tr>
	</table>
</center>

<br />
<table class="width100" cellspacing="1">
	<tr>
		<td class="form-title" colspan="5">
			<span tal:replace="manage_accounts_label" /><span tal:replace="string:[${total_user_count}]" />
			<form method="get" action='manage_user_create_page.php' tal:comment="# CSRF protection not required here - form does not result in modifications">
				<input type="submit" class="button-small" tal:attributes="value create_new_account_label" />
			</form>
		</td>
		<td class="center" colspan="3">
			<form method="post" action="manage_user_page.php" tal:comment="# CSRF protection not required here - form does not result in modifications"> 
				<input type="hidden" name="sort_column" tal:attributes="value sort_column" />
				<input type="hidden" name="sort_direction" tal:attributes="value sort_direction" />
				<input type="hidden" name="save" value="1" />
				<input type="hidden" name="filter" tal:attributes="value filter" />
				<input type="checkbox" name="hide_inactive" value="1" tal:attributes="checked hide_inactive" /> <label tal:content="hide_inactive_label">Hide Inactive</label> 
				<input type="submit" class="button" tal:attributes="value filter_button_label" />
			</form>
		</td>
	</tr>
	<tr class="row-category">
		<td tal:repeat="link sort_links">
			<a tal:condition="php: repeat.link.key ne 'protected'" tal:attributes="href link/url" tal:content="link/label">Sort Column Label</a>
			<a tal:condition="php:repeat.link.key eq 'protected'" tal:attributes="href link/url"><img tal:attributes="src protected_icon_path;alt string:$protected_label Column" /></a>
			<img tal:condition="link/icon_url | nothing" tal:attributes="src link/icon_url;alt link/sort_alt" />
		</td>
	</tr>
	<tr tal:repeat="user users" tal:attributes="class php: repeat.user.odd ? 'row-1' : 'row-2'">
		<td>
			<a tal:omit-tag="not: user/canCurrentUserManage" tal:attributes="href string:manage_user_edit_page.php?user_id=${user/id}" tal:content="user/username">Username</a>
		</td>
		<td tal:content="user/realname">Realname</td>
		<td tal:content="user/email_link">Email</td>
		<td tal:content="user/access_level_label">Access Level label</td>
		<td tal:content="php: user.enabled ? 'X':''">Enabled Yes/No</td>
		<td class="center"><img tal:condition="user/protected" tal:attributes="src protected_icon_path;alt protected_label" width="8" height="15" border="0" /></td>
		<td tal:content="user/date_created/__toString">Date Created</td>
		<td tal:content="user/last_visit/__toString">Last Visit</td>
	</tr>

	<tr tal:condition="pager" tal:comment="# -- Page number links --">
		<td class="right" colspan="8">
			<span class="small">
				<span metal:use-macro="macros/pager.tpl/pager"></span>
			</span>
		</td>
	</tr>
</table>

<br />

<form method="get" action="manage_user_edit_page.php" tal:comment="# CSRF protection not required here - form does not result in modifications">
	<label tal:content="username_label" />
	<input type="text" name="username" value="" />
	<input type="submit" class="button" tal:attributes="value manage_user_label" />
</form>
