<?php
/**~engine/core/map.php
* ---
* map
* ---
* URL Mapping para modulos y acciones
* 
* @package      FALCODE
* @version      2.0
* @author       FALCODE
*/
class Map extends Router{
    public function __construct(){
        /**
        * El siguiente ejemplo se ejcutara cuando se trate de accesar al modulo
        * 'alias' cuando las acciones o controles sean 'alias_action1' o 'alias_action2',
        * y luego se ejecutará el modulo 'modulo'
        */
        //$this->map('alias','modulo',array('alias_action1','alias_action2'));
        /**
        * El siguiente ejemplo se ejecutará cuando se trate de accesar al modulo 'alias'
        * y se mostrará el modulo 'modulo' y se ejecutará la accion o control 'accion'
        */
        //$this->map('alias','modulo.accion');
        /**
        * El siguiente ejemplo evaluará los alias 'alias1' y alias2 y se mostrará el modulo
        * 'destino'
        */
        //$this->map(array('alias1','alias2'),'destino');
        return;
    }
}