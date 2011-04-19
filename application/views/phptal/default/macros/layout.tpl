<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html metal:define-macro="layout" xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" >
	<head>
		<link tal:condition="exists: css_links" tal:repeat="link_url css_links" rel="stylesheet" type="text/css" tal:attributes="href link_url" />
		<meta http-equiv="Content-type" content="application/xhtml+xml; charset=UTF-8" />
		<meta tal:condition="exists: meta_include_file" tal:replace="meta_include_file" />
		<meta tal:condition="exists: robots_meta" name="robots" tal:attributes="content robots_meta" />
		<link tal:condition="exists: rss_feed_url" rel="alternate" type="application/rss+xml" title="RSS" tal:attributes="href rss_feed_url" />
		<link tal:condition="exists: include_favicon" rel="shortcut icon" tal:attributes="href favicon_image" type="image/x-icon" />
		<link tal:condition="exists: browser_text_search_url" rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" tal:attributes="href browser_text_search_url" />
		<link tal:condition="exists: browser_id_search_url" rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" tal:attributes="href browser_id_search_url" />
		<title tal:content="page_title|nothing">MantisBT</title>
		<script tal:condition="php: config.use_javascript and javascript_links"  tal:repeat="link_url javascript_links" type="text/javascript" tal:attributes="src link_url"></script>
		<meta tal:condition="exists: redirect_content_url" http-equiv="Refresh" tal:attributes="content redirect_content_url" />
		<tal:block tal:condition="exists: layout_resources" tal:repeat="event_macro layout_resources" metal:use-macro="${event_macro}"></tal:block>
	</head>
	<body>
		<div id="mantis">
			<tal:block tal:condition="exists: layout_body_begin" tal:repeat="event_macro layout_body_begin" metal:use-macro="${event_macro}"></tal:block>
			<div class="center" tal:condition="config/page_title"><span class="pagetitle" tal:content="config/page_title"></span></div>
			<tal:block tal:condition="exists: top_include_page" tal:replace="structure top_include_page"></tal:block>
			<div id="banner" tal:condition="exists: show_logo">
			<a tal:omit-tag="not: logo_url" id="logo-link" tal:attributes="href logo_url"><img id="logo-image" alt="Mantis Bug Tracker" tal:attributes="src logo_image" /></a>
			</div>
			<tal:block tal:condition="exists: layout_page_header" tal:repeat="event_macro layout_page_header" metal:use-macro="${event_macro}"></tal:block>
			<div tal:condition="user_is_authenticated|nothing" id="login-info">
				<span tal:condition="current_user_is_anonymous">
					<span id="logged-anon-label" tal:content="lang/anonymous"></span>
					<span id="login-link"><a tal:attributes="href login_link_url" tal:content="lang/login_link">Login</a></span>
					<span tal:condition="config/allow_signup" id="signup-link"><a tal:attributes="href signup__url" tal:content="lang/signup_link">Signup</a></span>
				</span>
				<span tal:condition="not: current_user_is_anonymous">
					<span id="logged-in-label" tal:content="lang/logged_in_as">Logged In As:</span>
					<span id="logged-in-user" tal:content="current_user/username">Current Username</span>
					<span id="logged-in">
						<span tal:condition="current_user/realname" id="logged-in-realname" tal:content="current_user/realname">User Realname</span>
						<span id="logged-in-accesslevel" tal:attributes="class current_user/access_level_label" tal:content="current_user/access_level_label">Administrator</span>
					</span>
				</span>
			</div>
			<div tal:condition="exists: rss_get_issues_feed_url" id="rss-feed">
				<a tal:attributes="href rss_get_issues_feed_url"><img tal:attributes="src rss_image_url;alt lang/rss;title lang/rss" /></a>
			</div>
			<form tal:condition="php: project_selector_action AND project_option_list" method="post" id="form-set-project" tal:attributes="action project_selector_action">
				<fieldset id="project-selector" tal:comment="CSRF protection not required here - form does not result in modifications">
					<label for="form-set-project-id" tal:content="lang/email_project">Project:</label>
					<select metal:use-macro="macros/fields.tpl/select" tal:define="field_id string:form-set-project-id;field_name string:project_id;options project_option_list"></select>
					<input type="submit" class="button" tal:attributes="value lang/switch" />
				</fieldset>
			</form>
			<div id="current-time" tal:content="current_time">Now</div>
			<tal:block tal:condition="config/show_project_menu_bar">
			<div id="project-menu-bar" tal:define="list_links project_menu" metal:use-macro="macros/unordered_list.tpl/list"></div>
			</tal:block>
			<form method="post" tal:attributes="action jumpaction" class="bug-jump-form">
				<fieldset class="bug-jump" tal:comment="# CSRF protection not required here - form does not result in modifications">
					<input type="hidden" name="bug_label" tal:attributes="value lang/issue_id" />
					<input type="text" name="bug_id" size="10" class="small" />&#160;
					<input type="submit" class="button-small" tal:attributes="value lang/jump" />&#160;
				</fieldset>
			</form>
			<div tal:define="list_links main_menu; class string:main-menu" metal:use-macro="macros/unordered_list.tpl/list"></div>
			<div id="content">
				<tal:block tal:condition="exists: layout_content_begin" tal:repeat="event_macro layout_content_begin" metal:use-macro="${event_macro}"></tal:block>
				<span metal:define-slot="content"></span>
				<tal:block tal:condition="exists: layout_content_end" tal:repeat="event_macro layout_content_end" metal:use-macro="${event_macro}"></tal:block>
			</div>
			<div tal:condition="config/show_footer_menu" class="main-menu" tal:define="list_links main_menu" metal:use-macro="macros/unordered_list.tpl/list"></div>
			<tal:block tal:condition="exists: bottom_include_page" tal:replace="structure bottom_include_page"></tal:block>
			<div id="footer">
				<hr />
				<div id="powered-by-mantisbt-logo">
					<a href="http://www.mantisbt.org" tal:attributes="title string:${lang/powered_by} ${lang/mantis_title_label} ${lang/mantis_description}"><img tal:attributes="src mantisbt_logo_url;alt string:${lang/powered_by} ${lang/mantis_title_label} ${lang/mantis_description}" width="88" height="35" /></a>
				</div>
				<address tal:condition="config/copyright_statement" id="user-copyright" tal:content="copyright_statement">Copyright Statement</address>
				<address id="mantisbt-copyright"><span tal:replace="lang/powered_by">Powered by</span> <a href="http://www.mantisbt.org" tal:attributes="title string:${lang/mantis_title_label} ${lang/mantis_description}" tal:content="lang/mantis_title">Mantis Bug Tracker</a> (MantisBT)<span tal:condition="config/show_version" tal:replace="version_suffix"></span>. Copyright &copy;<span tal:condition="config/show_version" tal:replace="copyright_years"></span> MantisBT contributors. Licensed under the terms of the <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" title="GNU General Public License (GPL) version 2">GNU General Public License (GPL) version 2</a> or a later version.</address>
				<address id="webmaster-contact-information" tal:content="structure webmaster_contact_information">Webmaster Contact Information</address>

				<tal:block tal:condition="exists: layout_page_footer" tal:repeat="event_macro layout_page_footer" metal:use-macro="${event_macro}"></tal:block>

				<hr tal:condition="php: config.show_timer OR config.show_memory_usage OR config.show_queries_count" />
				<p tal:condition="config/show_timer" id="page-execution-time" tal:content="string:${lang/time_label} ${request_time} ${lang/seconds}">Page Execution Time</p>
				<p tal:condition="config/show_memory_usage" id="page-memory-usage" tal:content="memory_string">Page Memory Usage</p>
				<tal:block tal:condition="config/show_queries_count">
				<p id="total-queries-count" tal:content="log/totalQueriesCountString">Total Queries Executed</p>
				<p tal:condition="config/db_log_queries" id="unique-queries-count" tal:content="log/uniqueQueriesCountString">Unique Queries Executed</p>
				<p id="total-query-execution-time" tal:content="log/totalQueryExecutionTimeString">Total Query Time</p>
				</tal:block>
				<div metal:use-macro="macros/log_output.tpl/logs"></div>
			</div>
			<tal:block tal:condition="exists: layout_body_end" tal:repeat="event_macro layout_body_end" metal:use-macro="${event_macro}"></tal:block>
		</div>
	</body>
</html>
