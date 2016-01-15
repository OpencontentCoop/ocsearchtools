<div class="global-view-full">

{if is_set( $class )}
    <h1>{$class.name}</h1>

    <h2>Attributi</h2>
    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="table table-striped list">
        <thead>
        <tr>
            <th style="vertical-align: middle">Attributo</th>
            <th style="vertical-align: middle">Identificatore</th>
            <th style="vertical-align: middle">Descrizione</th>
            <th style="vertical-align: middle">Tipo di dato</th>
            <th style="vertical-align: middle">Obbligatorio</th>
            <th style="vertical-align: middle">Ricercabile</th>
        </tr>
        </thead>
        <tbody>
        {foreach $class.data_map as $attribute sequence array(bglight,bgdark) as $style}
            <tr id="{$attribute.identifier}" class="class {$style}">
                <td style="vertical-align: middle">
                    {$attribute.name}
                </td>
                <td style="vertical-align: middle">
                    {$attribute.identifier}
                </td>
                <td>{$attribute.description}</td>
                <td>{$attribute.data_type.information.name} ({$attribute.data_type_string})</td>
                <td style="text-align: center">{if $attribute.is_required}X{/if}</td>
                <td style="text-align: center">{if $attribute.is_searchable}X{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>

    <h2>Utilità</h2>
    <ul>
        <li>
            Numero di oggetti presenti: {$class.object_count}
        </li>
        {if ezmodule( 'classlists' )}
            <li>
                <a href={concat( 'classlists/list/', $class.identifier )|ezurl()}>
                    Visualizza elenco degli oggetti
                </a>
            </li>
        {/if}
        <li>
            <a href="{concat('exportas/csv/', $class.identifier, '/1')|ezurl(no)}">
                Esporta oggetti in formato CSV
            </a>
        </li>
        <li>
            <a href="{concat('exportas/xml/', $class.identifier, '/1')|ezurl(no)}">
                Esporta oggetti in formato XML
            </a>
        </li>
    </ul>

    <h2>Informazioni</h2>
    <ul>
        <li>
            <a href={concat('/class/view/',$class.id)|ezurl()}>
                Vai all'interfaccia di modifica della classe
            </a>
        </li>
        <li>
            <a href={concat('/classtools/relations/',$class.identifier)|ezurl()}>
                Visualizza diagramma relazioni
            </a>
        </li>
        <li>
            <a href={concat('/classtools/definition/',$class.identifier)|ezurl()}>
                Esporta definizione in formato JSON
            </a>
        </li>
    </ul>

    <h2>Sincronizzazione</h2>
    <ul>
        <li>
            <form action="{concat('/classtools/compare/',$class.identifier)|ezurl(no)}" method="get" class="form-inline">
                <input id="RemoteHost" type="text"  class="halfbox form-control" name="remote" value="" placeholder="http://www.domain.ltd" />
                <input type="submit" value="Confronta" class="button btn btn-info" />
            </form>
        </li>
    </ul>

    {if count( $extra_handlers )}
        <h2>Impostazioni aggiuntive</h2>
        <ul>
        {foreach $extra_handlers as $identifier => $handler}
            <li><a href={concat('/classtools/extra/',$class.identifier, '/', $handler.identifier)|ezurl()}>{$handler.name|wash()}</a></li>
        {/foreach}
        </ul>
    {/if}

{else}
    <h1>Classi di contenuto</h1>
    {def $classList = fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}
    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
        <thead>
            <tr>
                <th style="vertical-align: middle">Classe</th>
                <th style="vertical-align: middle">Identificatore</th>
                <th style="vertical-align: middle">Descrizione</th>
                <th style="vertical-align: middle">Relazioni</th>
            </tr>
        </thead>
        <tbody>
            {foreach $classList as $class sequence array(bglight,bgdark) as $style}
            <tr id="{$class.identifier}" class="class {$style}">
                <td style="vertical-align: middle;white-space: nowrap">
                    <a href={concat('/classtools/classes/',$class.identifier)|ezurl()}>
                        {$class.name}
                    </a>
                </td>
                <td style="vertical-align: middle">
                    {$class.identifier}
                </td>
                <td>{$class.description}</td>
                <td style="text-align: center">
                    <a href={concat('/classtools/relations/',$class.identifier)|ezurl()}>
                        <img src={'websitetoolbar/ezwt-icon-locations.png'|ezimage()} />
                    </a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {/if}

</div>