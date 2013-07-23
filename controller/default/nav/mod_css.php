<?php
/**
* Loads CSS files using a dynamic include
*/
$data['css'] = Sys::$CSS_Files;

if($tpl)
    $tpl->setContext($data);