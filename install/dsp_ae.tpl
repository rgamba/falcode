{JS}

<h1>{$AE} {table}</h1>
<div class="buttons">
    <a class="button" href="#" onclick="$('#frm_save').submit();return false"><span>{$system.lang.save}</span></a>
    <a class="button" href="#" onclick="document.forms[0].reset(); return false"><span>{$system.lang.reset}</span></a>
    <a class="button" href="{url:{table}/list}"><span>{$system.lang.cancel}</span></a>
</div>


<div class="content ae">

<form action="" method="post" enctype="multipart/form-data" id="frm_save">
<input type="hidden" name="i" value="{table}/save" />
<table class="form">
	{fields}
<tr>
    <td class="field-name">URI:<span class="help">Shortcut to view this</span> </td>
    <td>{$system.path.http}<input type="text" name="uri_alias" value="{$row.uri_alias}" validate="custom-or-empty" regex="^([a-zA-Z0-9_-])+$" error="{$system.lang.error_regex_uri}" /><br /></td>
</tr>
<tr>
    <td colspan="2" style="border-bottom: none; background: #f1f1f1;">
        <a class="button" href="#" onclick="$('#frm_save').submit(); return false;">{$system.lang.save}</a>
        <a class="button" href="{url:{table}/list}">{$system.lang.reset}</a>
        <a class="button" href="#" onclick="document.forms[0].reset(); return false;">{$system.lang.reset}</a>
    </td>
</tr>
</table>
<input type="hidden" name="uploaded_files" value="" id="uploaded_files" /> 
<input type="submit" style="display: none" />
</form>
</div>
