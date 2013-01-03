<?php
/**
* Modulo Dinamico
* ---
* Breadcrumb / Pathway
*/
$BREADCRUMB = Tpl::get('BREACRUMB');
if(empty($BREADCRUMB) || !is_array($BREADCRUMB)){
    $index=SiteMap::getIndex();
    $BREADCRUMB=array(
        array($index['desc'],url($index['url']))
    );
    $uri=DSP_MODULE.(DSP_CONTROL!="" ? '/'.DSP_CONTROL : '');
    $parents=array();
    SiteMap::getAncestors(SiteMap::findByUrl($uri,true),&$parents);
    
    if(!empty($parents)){
        foreach($parents as $i => $p){
            $BREADCRUMB[]=array(
                $p['desc'],
                url($p['url'])
            );
        }
        $act=SiteMap::findByUrl($uri);
        $BREADCRUMB[]=array(
                $act['desc'],
                url($act['url'])
            );
    }else{
        if(DSP_MODULE!=''){
            $BREADCRUMB[]=array(ucwords(str_replace('_',' ',DSP_MODULE)),url(DSP_MODULE));
            if(DSP_CONTROL!=''){
                $BREADCRUMB[]=array(ucwords(DSP_CONTROL),url(DSP_MODULE.'/'.DSP_CONTROL));
            }
        }
    }
}
if(is_array($BREADCRUMB) && !empty($BREADCRUMB)){
    $ruta=array();
    foreach($BREADCRUMB as $i => $v){
        $ruta[]=array(
            'first' => ($i==0 ? 'first' : ''),
            'last' => (($i+1)==count($BREADCRUMB)),
            'item' => empty($v['name']) ? $v[0] : $v['name'],
            'url' => empty($v['url']) ? $v[1] : $v['url']
        );
    }
}
$tpl->ruta=$ruta;