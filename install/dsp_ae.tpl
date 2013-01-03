{JS}
<div class="left"></div>
<div class="right"></div>
<div class="heading">
<h1 style="background-image: url('{$system.path(img)}review.png');">{$AE} {table}</h1>
<div class="buttons">
    <a class="button" href="#" onclick="$('#frm_save').submit();return false"><span>{$system.lang.save}</span></a>
    <a class="button" href="#" onclick="document.forms[0].reset(); return false"><span>{$system.lang.reset}</span></a>
    <a class="button" href="{url:{table}/list}"><span>{$system.lang.cancel}</span></a>
</div>
</div>

<div class="content ae">

<form action="" method="post" enctype="multipart/form-data" id="frm_save">
<input type="hidden" name="i" value="{table}/save" />
<table class="form">
	{fields}
<tr>
    <td class="field-name">URI:<span class="help">Sólamente letras, números, guión medio y guión bajo (sin acentos ni ñ)</span> </td>
    <td>{$system.path.http}<input type="text" name="uri_alias" value="{$row.uri_alias}" validate="custom-or-empty" regex="^([a-zA-Z0-9_-])+$" error="{$system.lang.error_regex_uri}" /><br /></td>
</tr>
<tr>
    <td colspan="2" style="border-bottom: none; background: #f1f1f1;">
        <div style="float: right; margin-right: 8px">
        <a class="buttonst" href="#" onclick="$('#frm_save').submit(); return false;">
            <span class="button_left button_save"></span>
            <span class="button_middle">{$system.lang.save}</span>
            <span class="button_right"></span>
        </a>
        </div>
        <div style="float: right; margin-right: 8px">
        <a class="buttonst" href="{url:{table}/list}">
            <span class="button_left button_cancel"></span>
            <span class="button_middle">{$system.lang.reset}</span>
            <span class="button_right"></span>
        </a>
        </div>
        <div style="float: left; margin-right: 8px">
        <a class="buttonst" href="#" onclick="document.forms[0].reset(); return false;">
            <span class="button_left button_back"></span>
            <span class="button_middle">{$system.lang.reset}</span>
            <span class="button_right"></span>
        </a>
        </div>
    </td>
</tr>
</table>
<input type="hidden" name="uploaded_files" value="" id="uploaded_files" /> 
<input type="submit" style="display: none" />
</form>
</div>
