<div metal:define-macro="logs" tal:condition="log/canLogToPage|nothing">
	<!--Mantis Debug Log Output-->
	<hr />
	<table id="log-event-list">
		<thead>
			<tr>
				<th tal:content="lang/log_page_number">Number</th>
				<th tal:content="lang/log_page_time">Execution time</th>
				<th tal:content="lang/log_page_caller">Caller</th>
				<th tal:content="lang/log_page_event">Event</th>
			</tr>
		</thead>
		<tbody tal:define="events log/getLogsForDisplay" tal:condition="events">
			<tr tal:repeat="logRecord events" tal:attributes="class php: ( logRecord.duplicate ? 'duplicate-query' : '' )">
				<td tal:content="string:${logRecord/level_label}-${logRecord/level_count}">Log level number</td>
				<td tal:content="logRecord/execution_time">Execution time</td>
				<td tal:content="logRecord/caller">Caller </td>
				<td tal:content="logRecord/event">log_event</td>
			</tr>
			<tr tal:condition="log/uniqueQueriesCount">
				<td tal:content="log/database_label"></td>
				<td colspan="3" tal:content="log/uniqueQueriesCountString">Unique Queries Executed</td>
			</tr>
			<tr tal:condition="log/totalQueriesCount">
				<td tal:content="log/database_label"></td>
				<td colspan="3" tal:content="log/totalQueriesCountString">Total Queries Executed</td>
			</tr>
			<tr tal:condition="log/totalQueryExecutionTime">
				<td tal:content="log/database_label"></td>
				<td colspan="3" tal:content="log/totalQueryExecutionTimeString">Total Query Time</td>
			</tr>
		</tbody>
	</table>
	<!--END Mantis Debug Log Output-->
</div>
