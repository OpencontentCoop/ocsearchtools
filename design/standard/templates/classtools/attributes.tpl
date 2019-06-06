{ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js', 'jquery.quicksearch.js' ) )}
<script type="text/javascript">
{literal}
$(document).ready(function() {
    $("table.sort").tablesorter();
    $("table.sort th").css( 'cursor', 'pointer' );    
});
{/literal}
</script>
<style>
th, td {ldelim}
	font-size: .7em;
{rdelim}
td p.description {ldelim}
	font-size: .7em;
	margin: 0;
	font-style: italic;
{rdelim}
tbody tr:nth-child(even) {ldelim}
    background-color: #f2f2f2
{rdelim}}
</style>

<h1>Attributi di classe</h1>

<div class="table-responsive">
<table id="class-list" class="list sort" cellspacing="0">
	<thead>
		<tr>
			<th>Gruppo di classe</th>
			<th>Classe</th>
		    <th>Identificatore di classe</th>
		    <th>Attributo</th>
		    <th>Identificatore</th>
		    <th>Datatype</th>
		    <th>Categoria</th>		    
		</tr>
	</thead>
	{foreach $attributes as $attribute}
		<tr>
			<td>
				{foreach $class_by_id[$attribute.contentclass_id].ingroup_list as $group}
					{$group.group_name|wash()}{delimiter}, {/delimiter}
				{/foreach}
			</td>
			<td>{$class_by_id[$attribute.contentclass_id].name|wash()}</td>
			<td>{$class_by_id[$attribute.contentclass_id].identifier}</td>
			<td>
				{$attribute.name|wash()}
				<p class="description">{$attribute.description|wash()}</p>
			</td>
			<td>{$attribute.identifier}</td>
			<td>{$attribute.data_type_string}</td>
			<td>{$attribute.category|wash()}</td>
		</tr>
	{/foreach}
	<tbody>
	</tbody>
</table>
</div>