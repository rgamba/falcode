<?php
/**
* Set metadata information
*/
$data['META_DESCRIPTION'] = Tpl::get(META_DESCRIPTION);
$data['META_KEYWORDS'] = Tpl::get(META_KEYWORDS);

if($tpl)
    $tpl->setContext($data);
