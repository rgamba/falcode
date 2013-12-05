<?php
/**
* Load the javascript files using a dynamic include
*/
$data['js'] = Sys::$JS_Files;

if(@$tpl)
    $tpl->setContext($data);