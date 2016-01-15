<div class="global-view-full">

{if is_set( $class )}
    <h1><a href={'classtools/classes'|ezurl()}>Classi di contenuto</a> &raquo; <a href={concat('classtools/classes/',$class.identifier)|ezurl()}>{$class.name}</a></h1>

    <form action="{concat('/classtools/extra/',$class.identifier)|ezurl()}" method="post">

        {if $extra_handlers|count()}
        <div class="extra_parameters_handlers">
            {foreach $extra_handlers as $identifier => $handler}

                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="handler-toggle" data-handler="{$identifier}" name="extra_handler_{$handler.identifier}[class][{$class.identifier}][enabled]" value="1" {if $handler.enabled}checked="checked"{/if} /> Abilita {$handler.name|wash()}
                    </label>
                </div>
                {include uri=$handler.class_edit_template_url handler=$handler class=$class}
            {/foreach}
        </div>
        {/if}

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
                    {if $extra_handlers|count()}
                        {foreach $extra_handlers as $identifier => $handler}
                            <div class="extra_parameters_handlers handler_{$identifier}" style="display: {if $handler.enabled}block{else}none{/if}">
                                {include uri=$handler.attribute_edit_template_url handler=$handler class=$class attribute=$attribute}
                            </div>
                        {/foreach}
                    {/if}
                    </td>
                </tr>
            </tbody>
        </table>
        {/foreach}
        {if $extra_handlers|count()}
            <input type="submit" class="extra_parameters_handlers defaultbutton btn btn-success pull-right object-right" name="StoreExtraParameters" value="Salva impostazioni" />
        {/if}
    </form>

    {ezscript_require( array( 'ezjsc::jquery' ) )}
    {literal}
        <script type="application/javascript">
            $(document).ready(function(){
                $('input.handler-toggle').bind('change',function(e){
                    var identifier = $(e.currentTarget).data('handler');
                    $('div.handler_'+identifier).toggle();
                });
            });
        </script>
    {/literal}

{/if}

</div>