<div class="global-view-full">

{if is_set( $class )}
    <h1>{$class.name}</h1>

    <form action="{concat('/classtools/extra/',$class.identifier,'/',$handler.identifier)|ezurl()}" method="post">

        <div class="extra_parameters_handlers">
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="handler-toggle" data-handler="{$handler.identifier}" name="extra_handler_{$handler.identifier}[class][{$class.identifier}][enabled]" value="1" {if $handler.enabled}checked="checked"{/if} /> Abilita {$handler.name|wash()}
                </label>
            </div>
            {include uri=$handler.class_edit_template_url handler=$handler class=$class}
        </div>


        {foreach $class.data_map as $attribute}
        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
            <thead>
                <tr id="{$attribute.identifier}">
                    <th style="vertical-align: middle">
                        {$attribute.name} ({$attribute.identifier})
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr id="handler_{$attribute.identifier}">
                    <td>
                        <div class="extra_parameters_handlers handler_{$handler.identifier}">
                            {include uri=$handler.attribute_edit_template_url handler=$handler class=$class attribute=$attribute}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        {/foreach}
        <input type="submit" class="extra_parameters_handlers defaultbutton btn btn-success pull-right object-right" name="StoreExtraParameters" value="Salva impostazioni" />
    </form>


{/if}

</div>