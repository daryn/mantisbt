<span metal:define-macro="pager">
    [<span tal:repeat="link pager" class="pager_link"><a tal:omit-tag="not:link/url" tal:attributes="href link/url" tal:content="link/label">First</a></span>]
</span>
