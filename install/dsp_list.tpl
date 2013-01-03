{if:!$system.const.DSP_BLANK}
<div class="left"></div>
<div class="right"></div>
<div class="heading">
<h1 style="background-image: url('{$system.path(img)}log.png');">{table}</h1>
<div class="buttons">
<div style="float: left">
    <form action="{url:{table}/list}" method="post" id="search_form">
    <div class="search" style="margin-right: 4px">
    <input type="text" name="search" value="{$system.request.search}" alt="Ingresa tu busqueda" />&nbsp;&nbsp;
    <a class="button" href="#" onclick="$('#search_form').submit();return false"><span>{$system.lang.search}</span></a>
    </div>
    </form>
    </div>
    <a class="button" href="{url:{table}/add}"><span>{$system.lang.create}</span></a>
    <a class="button" href="#" onclick="if(confirm('{$system.lang.confirm_delete}')){document.getElementById('list_form').submit();}"><span>Eliminar</span></a>
    </div>
</div>

<div class="content">
{/if}
{if:$recordset}
<form action="{url:{table}/del}" method="post" id="list_form">
<table style="clear: both" class="list">
<thead>
	<tr>
        <td style="width: 20px"><input type="checkbox" class="checkall" /></td>
		{headers}
	</tr>
</thead>
<tbody>
	{loop:$recordset,key=i,item=row}
	<tr style="background: {$bg_color}">
		{campos}
	</tr>
	{/loop}
</tbody>
</table>
</form>
{if:!$system.const.DSP_BLANK}
<div>{$Paginator.preventTagEncode()}</div>
</div>
{/if}
{else}
<div style="border: 1px solid #DDDDDD; background: #F7F7F7; text-align: center; overflow: auto; width: 100%; padding: 0">
<div class="information">{$system.lang.no_records_found}</div>
</div></div>
{/if}
