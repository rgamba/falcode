<div class="left"></div>
<div class="right"></div>
<div class="heading">
<h1 style="background-image: url('{PATH_IMG}report.png');">{$system.lang.view} {table}</h1>
<div class="buttons">
    <a class="button" href="#" onclick="history.back()"><span>{$system.lang.ok}</span></a>
    <a class="button" href="{url:{table}/edit?id=$row.{pk}}"><span>{$system.lang.edit}</span></a>
    </div>
</div>

<div class="content ae">

<table class="form">
	{fields}
	{fields_img}
<tr>
    <td colspan="2" style="border-bottom: none; background: #f1f1f1;">
        <div style="float: right; margin-right: 8px">
        <a class="buttonst" href="{url:{table}/list}">
            <span class="button_left button_save"></span>
            <span class="button_middle">{$system.lang.ok}</span>
            <span class="button_right"></span>
        </a>
        </div>
        <div style="float: right; margin-right: 8px">
            <a class="buttonst" href="{url:{table}/del?id=$row.{pk}}">
            <span class="button_left button_delete"></span>
            <span class="button_middle">{$system.lang.delete}</span>
            <span class="button_right"></span>
        </a>
        </div>
        <div style="float: right; margin-right: 8px">
        <a class="buttonst" href="{url:{table}/edit?id=$row.{pk}}">
            <span class="button_left button_invoice"></span>
            <span class="button_middle">{$system.lang.edit}</span>
            <span class="button_right"></span>
        </a>
        </div>
    </td>
</tr>
</table>
</div>
