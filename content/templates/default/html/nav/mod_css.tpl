{if:$css}
    {loop:$css,item=css_file}
    <link rel="stylesheet" href="{$css_file}" rev="stylesheet" type="text/css">
    {/loop}
{/if}
<!--[if lt IE 9]>
	<link rel="stylesheet" type="text/css" href="{$system.path(css)}ie8.css" />
<![endif]-->
