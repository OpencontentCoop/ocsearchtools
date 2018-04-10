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
tbody tr:nth-child(even) {ldelim}
    background-color: #f2f2f2
{rdelim}}
</style>
<table id="class-list" class="list sort" cellspacing="0">
	<thead>
		<tr>
		    <th>Classe</th>
		    <th>Identificatore di classe</th>
		    <th>Gruppo di classe</th>
		    <th>Attributo</th>
		    <th>Identificatore</th>
		    <th>Descrizione</th>
		    <th>Datatype</th>
		    <th>Categoria</th>		    
		</tr>
	</thead>
	{foreach $attributes as $attribute}
		<tr>
			<td>{$class_by_id[$attribute.contentclass_id].name|wash()}</td>
			<td>{$class_by_id[$attribute.contentclass_id].identifier}</td>
			<td>
				{foreach $class_by_id[$attribute.contentclass_id].ingroup_list as $group}
					{$group.group_name|wash()}{delimiter}, {/delimiter}
				{/foreach}
			</td>
			<td>{$attribute.name|wash()}</td>
			<td>{$attribute.identifier}</td>
			<td>{$attribute.description|wash()}</td>
			<td>{$attribute.data_type_string}</td>
			<td>{$attribute.category|wash()}</td>
		</tr>
	{/foreach}
	<tbody>
	</tbody>
</table>