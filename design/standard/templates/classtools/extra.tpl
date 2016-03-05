<div class="global-view-full">



{if count( $extra_handlers )|gt(1)}
<div class="well">
    <form method="post" action="">
        <label for="handler">
            Seleziona impostazione
        </label>
        <select name="handler" id="handler">
            {foreach $extra_handlers as $identifier => $item}
                <option value="{$item.identifier}" {if $item.identifier|eq($handler.identifier)}selected="selected"{/if}>{$item.name|wash()}</option>
            {/foreach}
        </select>

        {def $classList = fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}
        <label for="class">
            Classe
        </label>
        <select name="class" id="class">
        {foreach $classList as $classItem}
            <option value="{$classItem.identifier}" {if $class.identifier|eq($classItem.identifier)}selected="selected"{/if}>{$classItem.name|wash()}</option>
        {/foreach}
        </select>
        <button type="submit" class="defaultbutton btn btn-info btn-sm">Seleziona</button>
    </form>
</div>
{/if}

{if is_set( $class )}
    <h1>{$class.name}</h1>

    <form action="{concat('/classtools/extra/',$class.identifier,'/',$handler.identifier)|ezurl()}" method="post">

        <input type="submit" class="extra_parameters_handlers defaultbutton btn btn-success pull-right object-right" name="StoreExtraParameters" value="Salva impostazioni" />

        <div class="extra_parameters_handlers">
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="handler-toggle" data-handler="{$handler.identifier}" name="extra_handler_{$handler.identifier}[class][{$class.identifier}][enabled]" value="1" {if $handler.enabled}checked="checked"{/if} /> Abilita {$handler.name|wash()}
                </label>
            </div>
            {include uri=$handler.class_edit_template_url handler=$handler class=$class}
        </div>



        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="table list">
            <tbody>
            {foreach $class.data_map as $attribute}
                <tr id="{$attribute.identifier}">
                    <th style="vertical-align: middle;width: 20%">
                        {$attribute.name} ({$attribute.identifier})
                    </th>
                    {include uri=$handler.attribute_edit_template_url handler=$handler class=$class attribute=$attribute}
                </tr>
            {/foreach}
            </tbody>
        </table>
        <input type="submit" class="extra_parameters_handlers defaultbutton btn btn-success pull-right object-right" name="StoreExtraParameters" value="Salva impostazioni" />
    </form>


{/if}

</div>