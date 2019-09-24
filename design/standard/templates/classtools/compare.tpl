<div class="global-view-full">

    {ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js' ) )}

    <script type="text/javascript">
        {literal}
        $(document).ready(function () {
            $("table.list").tablesorter();
            $("table.list th").css('cursor', 'pointer');
        });
        {/literal}
    </script>
    {if is_set($data.error)}
        <div class="message-error">
            <p>{$data.error}</p>
            {if $locale_not_found}
                <form action={concat('classtools/compare/', $request_id)|ezurl()} method="post">
                    <input type="hidden" name="remote" value="{$remote_request|wash()}" />
                    <div class="text-center">
                        <input type="submit" name="InstallButton" value="Installa classe {$request_id|wash()}"
                               class="defaultbutton btn btn-lg mt-4 mb-4"/>
                    </div>
                </form>
            {/if}
        </div>
    {else}
        <h2>{$locale.name|wash()}</h2>

        <div style="font-weight:bold;margin-bottom: 20px">Confronto con {$remote_url|wash}</div>

        {if and( count($diff_properties)|eq(0), count($missing_in_remote)|eq(0), count($missing_in_locale)|eq(0), count($diff)|eq(0) )}
            <div class="message-feedback">
                <h2>La classe è sincronizzata con {$remote_url|wash}</h2>
            </div>
        {elseif and( count($errors)|eq(0), count($warnings)|eq(0), count($missing_in_locale)|eq(0))}
            <div class="message-feedback">
                <h2>La classe è compatibile con {$remote_url|wash}</h2>
            </div>
        {else}
            <div class="message-error">
                <h2>La classe non è compatibile con {$remote_url|wash}</h2>
            </div>
        {/if}

        {if or( count($diff_properties)|ne(0), count($missing_in_remote)|ne(0), count($missing_in_locale)|ne(0), count($diff)|ne(0) )}
            <form action={concat('classtools/compare/', $locale.identifier)ezurl()} method="post">
                <input type="hidden" name="remote" value="{$remote_request|wash()}" />
                <div class="text-center">
                    <input type="submit" name="SyncButton" value="Sincronizza adesso" class="defaultbutton btn btn-lg mt-4 mb-4" />
                </div>
                {if count($errors)|gt(0)}
                    <div class="message-error" style="margin-top: 10px">
                        <h2>Attenzione</h2>
                        <p>
                            La classe contiene uno o più elementi che impediscono la sincronizzazione automatica, per forzare la sincronizzazione spunta la casella seguente.
                        </p>
                        <label>
                            <input type="checkbox" name="ForceSync" value="1" />
                            Forza la sincronizzazione
                        </label>
                        <p><strong>Forzando la sincronizzazione tutti i contenuti attualmente presenti nei campi {foreach $errors as $identifier => $value}"{$identifier}"{delimiter}, {/delimiter}{/foreach} andranno persi</strong></p>
                    </div>
                {/if}
                {if count($missing_in_remote)|gt(0)}
                    <div class="message-error" style="margin-top: 10px">
                        <label>
                            <input type="checkbox" name="RemoveExtra" value="1" />
                            Rimuovi attributi locali personalizzati
                        </label>
                        <p><strong>Rimuovendo gli attributi personalizzati tutti i contenuti attualmente presenti nei campi {foreach $missing_in_remote as $item}"{$item.Identifier}"{delimiter}, {/delimiter}{/foreach} andranno persi</strong></p>
                    </div>
                {/if}
            </form>
        {/if}

        {if count($diff_properties)|gt(0)}
            <h3>Proprietà che differiscono rispetto al modello</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list table table-striped">
                <thead>
                <tr>
                    <th>Proprietà</th>
                    <th>Sito</th>
                    <th>{$remote_url|wash}</th>
                    <th width="1"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $diff_properties as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}"
                        {if and( is_set($errors['properties']), is_set( $errors['properties'][$item.field_name] ) )}style="background:#ff0"
                        {elseif and( is_set($warnings['properties']), is_set( $warnings['properties'][$item.field_name] ) )}style="background:#f2dede"
                            {/if}>
                        <td>{$item.field_name}</td>
                        <td>{$item.locale_value}</td>
                        <td>{$item.remote_value}</td>
                        <td style="vertical-align: middle">
                            {if array('class_group')|contains($item.field_name)|not()}
                                <form action={concat('classtools/compare/', $locale.identifier)ezurl()} method="post">
                                    <input type="hidden" name="remote" value="{$remote_request|wash()}" />
                                    <input type="hidden" name="SyncPropertyIdentifier" value="{$item.field_name|wash()}" />
                                    <button class="defaultbutton btn btn-primary" type="submit" name="SyncPropertyButton"><i class="fa fa-exchange"></i> <span class="sr-only">Sincronizza</span></button>
                                </form>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($missing_in_locale)|gt(0)}
            <h3>Attributi mancanti rispetto a {$remote_url|wash}</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list table table-striped">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>DataType</th>
                    <th width="1"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $missing_in_locale as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}" style="background:#f2dede">
                        <td>{$item.Identifier}</td>
                        <td>{$item.DataTypeString}</td>
                        <td style="vertical-align: middle">
                            <form action={concat('classtools/compare/', $locale.identifier)ezurl()} method="post">
                                <input type="hidden" name="remote" value="{$remote_request|wash()}" />
                                <input type="hidden" name="AddAttributeIdentifier" value="{$item.Identifier|wash()}" />
                                <button class="defaultbutton btn btn-primary" type="submit" name="AddAttributeButton"><i class="fa fa-plus"></i> <span class="sr-only">Aggiungi</span></button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($diff)|gt(0)}
            <h3>Attributi che differiscono da {$remote_url|wash}</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list table table-striped">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>Proprietà</th>
                    <th style="text-align: center">Sito</th>
                    <th style="text-align: center">{$remote_url|wash}</th>
                    {*<th style="text-align: center">Numero di oggetti <br/>con attributo valorizzato</th>*}
                    <th width="1"></th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $diff as $identifier => $items sequence array(bglight,bgdark) as $style}

                    {def $firstRow = false()}
                    {foreach $items as $item}
                        <tr class="{$style}"
                            {if and( is_set($errors[$identifier]), is_set( $errors[$identifier][$item.field_name] ) )}style="background:#ff0"
                            {elseif and( is_set($warnings[$identifier]), is_set( $warnings[$identifier][$item.field_name] ) )}style="background:#f2dede"
                                {/if}>
                            {if $firstRow|not()}
                                <td rowspan="{$items|count()}" style="vertical-align: middle">{$identifier}</td>
                                {set $firstRow = true()}
                            {/if}
                            <td style="vertical-align: middle">{$item.field_name}</td>
                            <td style="text-align: center"><strong>{$item.locale_value}</strong></td>
                            <td style="text-align: center">{$item.remote_value}</td>
                            {*<td class="text-center">
                                {if $item.detail}{$item.detail.count}{/if}
                            </td>*}
                            <td style="vertical-align: middle">
                                {if array('placement', 'data_type_string')|contains($item.field_name)|not()}
                                    <form action={concat('classtools/compare/', $locale.identifier)ezurl()} method="post">
                                        <input type="hidden" name="remote" value="{$remote_request|wash()}" />
                                        <input type="hidden" name="SyncAttributeIdentifier" value="{$identifier|wash()}/{$item.field_name|wash()}" />
                                        <button class="defaultbutton btn btn-primary" type="submit" name="SyncAttributeButton"><i class="fa fa-exchange"></i> <span class="sr-only">Sincronizza</span></button>
                                    </form>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    {undef $firstRow}

                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($missing_in_remote)|gt(0)}
            <h3>Attributi aggiuntivi rispetto a {$remote_url|wash}</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list table table-striped">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>DataType</th>
                    {*<th>Numero di oggetti <br/>con attributo valorizzato</th>*}
                    <th width="1"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $missing_in_remote as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.Identifier}</td>
                        <td>{$item.DataTypeString}</td>
                        {*<td class="text-center">{$missing_in_remote_details[$item.Identifier].count}</td>*}
                        <td>
                            <form action={concat('classtools/compare/', $locale.identifier)ezurl()} method="post">
                                <input type="hidden" name="remote" value="{$remote_request|wash()}" />
                                <input type="hidden" name="RemoveAttributeIdentifier" value="{$item.Identifier|wash()}" />
                                <button class="defaultbutton btn btn-primary" type="submit" name="RemoveAttributeButton"><i class="fa fa-minus"></i> <span class="sr-only">Rimuovi</span></button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

    {/if}

</div>