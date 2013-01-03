<script type="text/javascript">
    var Sys={session_id:'{$system.session_id}',path:{http:'{$system.path.http}',img:'{$system.path(img)}',swf:'{$system.path(swf)}',js:'{$system.path(js)}'},user:{logged:{$system.is_logged.ifEmpty("false","true")},pic:'{$system.user_pic.default("null")}'},module:'{$system.module}',control:'{$system.control}',flash:{$system.flash.jsonEncode().preventTagEncode()}};
{if:$system.lang}
    var Lang={$system.lang.preventTagEncode().jsonEncode()};
{/if}
</script>
{if:$js}
{loop:$js,item=js_file}
<script type="text/javascript" src="{$js_file}"></script>
{/loop}
{/if}
{if:$system.msg(error)}
<script type="text/javascript">
    $(function(){ Misc.showErrorMsg("{$system.msg(error)}"); });
</script>
{/if}
{if:$system.msg(info)}
<script type="text/javascript">
    $(function(){ Misc.showInfoMsg("{$system.msg(info)}",10); });
</script>
{/if}