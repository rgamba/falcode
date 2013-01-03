<div class="breadcrumb">
{if:$ruta}
    {loop:$ruta,item=item}
        {if:$item.last}
            <span style="margin: 0 5px"><b>{$item.item}</b></span>
        {else}
            <span style="margin: 0 5px"><a href="{$item.url}">{$item.item}</a></span>»
        {/if}
    {/loop}
{/if}
</div>