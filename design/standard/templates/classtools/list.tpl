<div class="global-view-full">
{ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js' ) )}
<script type="text/javascript">
var classBaseUrl = {'/classtools/compare'|ezurl()};
var remoteRequest = '{$remote}';
{literal}
$(document).ready(function() {
    var compare = function (id, container) {
        var url = classBaseUrl + '/' + id;
        $.ajax({
            type:     "get",
            data:     {format: 'json', 'remote': remoteRequest},
            cache:    false,
            url:      url,
            dataType: "json",
            error: function (request, error) {
                if(request.status==401)
                    container.find( 'td.response' ).html( '<div class="message-error text-center"><small>Unauthorized</small></div>' );
                else
                    container.find( 'td.response' ).html( '<div class="message-error text-center"><strong>?</strong></div>' );
            },
            success: function (data) {
                if (data.error){
                    container.find( 'td.response' ).html( '<div class="message-error text-center"><small>'+data.error+'</small></div>' );
                }else {
                    $.each(data, function (index, value) {
                        if (container.find('td.' + index).length > 0) {
                            if (value) {
                                container.find('td.' + index).html('<div class="message-error text-center"><strong>KO</strong></div>');
                            } else {
                                container.find('td.' + index).html('<div class="message-feedback text-center">OK</div>');
                            }
                            $("table.list").trigger("update");
                        }
                    });
                }
            }
        });
    };

    $("table.list").tablesorter();
    $("table.list th").css( 'cursor', 'pointer' );
    $("table tr.class").each( function(){
        var tr = $(this);
        var id = $(this).attr( 'id' );
        compare(id, tr);
    });
    $('.refresh').on('click', function (e) {
        var tr = $(this).parents('tr');
        var id = tr.attr( 'id' );
        tr.find( 'td.response' ).html( '<i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only"><em><small>caricamento...</small></em></span>' );
        compare(id, tr);
        e.preventDefault();
    })
});
{/literal}
</script>

{def $classList = fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}

<table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
    <thead>                
        <tr>
            <th style="vertical-align: middle">Classe</th>
            <th style="vertical-align: middle">Attributi mancanti</th>            
            <th style="vertical-align: middle">Attributi aggiuntivi</th>            
            <th style="vertical-align: middle">Proprietà<br />non sincronizzate</th>            
            <th style="vertical-align: middle">Attributi<br />non sincronizzati</th>            
            <th style="vertical-align: middle">Richiede controllo</th>            
        </tr>
    </thead>
    <tbody>
        {foreach $classList as $class sequence array(bglight,bgdark) as $style}
        <tr id="{$class.identifier}" class="class {$style}">            
            <td style="vertical-align: middle">
                <a target="_blank" href={concat('/classtools/compare/',$class.identifier,'?',$remote_query_string)|ezurl()}>
                    {$class.name} ({$class.identifier})
                </a>
                <a href="#" class="refresh pull-right"><i class="fa fa-refresh"></i></a>
            </td>
            <td class="response hasMissingAttributes text-center"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only"><em><small>caricamento...</small></em></span></td>
            <td class="response hasExtraAttributes text-center"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only"><em><small>caricamento...</small></em></span></td>
            <td class="response hasDiffProperties text-center"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only"><em><small>caricamento...</small></em></span></td>
            <td class="response hasDiffAttributes text-center"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only"><em><small>caricamento...</small></em></span></td>
            <td class="response hasError text-center"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sr-only"><em><small>caricamento...</small></em></span></td>
        </tr>
        {/foreach}
    </tbody>
</table>
</div>
