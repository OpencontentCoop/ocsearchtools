{def $remote_request_suffix = concat('?remote=', $remote_url)}
<div class="global-view-full">
	<h1>{$class.name|wash()}</h1>
	<div style="font-weight:bold;margin-bottom: 20px">Sincronizzazione degli extra parameters con {$remote_url|wash()}</div>
	
	{if $error}

		<div class="message-error">
		  <p>{$error|wash()}</p>    
		</div>

	{else}

		{if count($diff)|eq(0)}
	        <div class="message-feedback">
	            <h3>I parametri della classe sono sincronizzati con il modello</h3>
	        </div>
		{else}

			<div class="message-warning">           
            	<h3>I parametri della classe non sono sincronizzati con il modello</h3>
            </div>

            <form action={concat('classtools/extra_compare/', $class.identifier, $remote_request_suffix)ezurl()} method="post" style="margin-bottom: 20px">
                <input type="submit" name="SyncButton" value="Sincronizza adesso" class="defaultbutton" />
            </form>
		
			{foreach $diff as $handler => $handler_values}
			<h3>{$handler|wash()}</h3>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" class="list" style="margin-bottom: 20px">
				{foreach $handler_values as $identifier => $values}
					<tr>
						<th colspan="2">{$identifier|wash}</th>
					</tr>
					{if is_array($values)}				
					{foreach $values as $key => $value}
					<tr>
						<td>{$key|wash()}</td>
						<td>{$value|wash()}</td>
					</tr>
					{/foreach}
					{else}
					<tr>
						<td colspan="2">{$values|wash}</td>
					</tr>
					{/if}
				{/foreach}
			</table>
			{/foreach}
		{/if}
	

	{/if}
</div>