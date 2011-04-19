<div metal:define-macro="list" tal:attributes="id id|nothing;class class|nothing">
	<div>
		<ul class="list">
			<tal:block tal:repeat="link list_links">
			<li tal:condition="not: exists: link/structure" tal:attributes="class link/active|nothing">
				<a tal:omit-tag="not:link/url" tal:attributes="href link/url"><span tal:replace="link/label">Main</span></a>
				<ul class="sub-list" tal:condition="exists: link/sublist">
					<li tal:repeat="sublink link/sublist/default" tal:attributes="class sublink/active|nothing"><a tal:attributes="href sublink/url" tal:content="sublink/label">Custom</a></li>
					<li tal:condition="exists: link/sublist/plugin" tal:repeat="sublink link/sublist/plugin" tal:attributes="class sublink/active|nothing" tal:content="structure sublink"></li>
				</ul>
			</li>
			<li tal:condition="exists:link/structure" tal:content="structure link"></li>
			</tal:block>
	    </ul>
	</div>
</div>
