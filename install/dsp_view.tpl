<h1>{$system.lang.view} {table}</h1>
<div class="buttons">
    <a class="button" href="#" onclick="history.back()"><span>{$system.lang.ok}</span></a>
    <a class="button" href="{url:{table}/edit?id=$row.{pk}}"><span>{$system.lang.edit}</span></a>
</div>

<div class="content ae">

<table class="form">
	{fields}
	{fields_img}
<tr>
    <td colspan="2" style="border-bottom: none; background: #f1f1f1;">
        <a class="button" href="{url:{table}/list}">{$system.lang.ok}</a>
        <a class="button" href="{url:{table}/del?id=$row.{pk}}">{$system.lang.delete}</a>
        <a class="button" href="{url:{table}/edit?id=$row.{pk}}">{$system.lang.edit}</a>
    </td>
</tr>
</table>
</div>
