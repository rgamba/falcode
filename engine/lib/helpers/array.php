<?php
/**
* get_parse_json()
* 
* Identify the json object within the string and return it as array
* con json_decode()
* @param mixed $string
* @return
*/
function get_parse_json($string=NULL){
    if(empty($string))
        return false;
    if(strpos($string,"}")===false)
        return false;
    $last=strrpos($string,"}");
    $string=substr($string,0,$last+1);
    return json_decode($string,true);
}

/**
 * Empty the array without modifying it's index
 */
function empty_array(&$array=array()){
    foreach($array as $k => $v)
        $array[$k]=NULL;
    return;
}

/**
* Flatten the array
* 
* @param mixed $array
* @param mixed $return
*/
function array_flatten($array,$return=array()){
    for($x = 0; $x <= count($array); $x++)
    {
        if(is_array($array[$x]))
        {
            $return = array_flatten($array[$x],$return);
        }
        else
        {
            if($array[$x])
            {
                $return[] = $array[$x];
            }
        }
    }
    return $return;
}

/**
 * objectToArray()
 * 
 * Convierte un objeto en un arreglo
 * @return Array
 */
function object_to_array($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
 }