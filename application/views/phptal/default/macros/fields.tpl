<tal:block metal:define-macro="email" tal:define="email_value email_domain:email">
	<input id="email-field" type="text" tal:attributes="name field_name;value email_value;size php: config.limit_email_domain ? '20' : '32'" maxlength="64" />
	<span tal:condition="config/limit_email_domain" tal:content="string: @${config/limit_email_domain}"></span>
</tal:block>
<input metal:define-macro="hidden" type="hidden" tal:attributes="name field_name;value field_value"/>

<select metal:define-macro="select" tal:attributes="id field_id|nothing;name field_name;class field_class|nothing">
	<option metal:use-macro="options" tal:condition="options"></option>
</select>
<option metal:define-macro="options" tal:repeat="option options" tal:attributes="value option/value;class option/class|nothing;selected option/selected" tal:content="option/label"></option>
