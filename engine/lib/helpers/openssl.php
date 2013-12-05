<?php
function openssl_cer2pem($input,$output = NULL,$pass = ""){
    if(empty($output))
        $output = $input . '.pem';
    exec('openssl x509 -in '. $input .' -inform der -outform pem -out ' . $output);
    if(!file_exists($output))
        throw new Exception("invalid_file");
    return true;
}
function openssl_key2pem($input,$output = NULL,$pass = ""){
    $tmp_suffix = empty($pass) ? '' : ".tmp";
    if(empty($output))
        $output = $input . '.pem';
    $response = array();
    exec('openssl pkcs8 -inform der -in '. $input .' -outform pem -out '. $output.$tmp_suffix .(!empty($pass) ? ' -passin pass:"'.$pass.'"' : '') . ' 2>&1', $response);
    if(trim(@$response[0]) == "Error decrypting key")
        throw new Exception("invalid_pass");
    if(!file_exists($output.$tmp_suffix))
        throw new Exception("invalid_file");
    // Now encrypt back the pem encoded key with the original passphrase
    if($pass != ""){
        exec('openssl rsa -in ' . $output . $tmp_suffix . ' -des3 -out ' . $output . ' -passout pass:' . $pass, $response);
        unlink($output . $tmp_suffix);
    }
    if(!file_exists($output))
        throw new Exception("invalid_file");
    return true;
}
function openssl_validate_date($file, $is_file = true){
    if($is_file)
        $file = file_get_contents($file);
    $cert = openssl_x509_parse($file);
    $time = time();
    if($time < $cert['validFrom_time_t'] || $time > $cert['validTo_time_t'])
        return false;
    return true;
}
function openssl_check_name($file,$name){
    $cert = openssl_x509_parse(file_get_contents($file));
    $cert_name = str_replace(array('á','é','í','ó','ú','ñ',' '),array('a','e','i','o','u','n',''),$cert['subject']['CN']);
    $cert_name = trim(strtolower($cert_name));

    $name = str_replace(array('á','é','í','ó','ú','ñ',' '),array('a','e','i','o','u','n',''),$name);
    $name = trim(strtolower($name));

    echo $cert_name." : ".$name;
}
function openssl_check_id($file,$id_match){
    $cert = openssl_x509_parse(file_get_contents($file));
    $id = strtolower($cert['subject']['x500UniqueIdentifier']);
    $id = explode('/',$id);
    $id_match = trim(strtolower($id_match));
    foreach($id as $i => $_id){
        $id[$i] = trim($_id);
        if($id[$i] == $id_match)
            return true;
    }
}
function openssl_get_uniqueid($file, $string = false){
    if(!$string)
        $file = file_get_contents($file);
    $cert = openssl_x509_parse($file);
    return @$cert['subject']['x500UniqueIdentifier'];
}
function openssl_get_issuer($file, $string = false){
    if(!$string)
        $file = file_get_contents($file);
    $cert = openssl_x509_parse($file);
    return $cert['issuer'];
}
function openssl_get_issuer_id($file, $string = false){
    if(!$string)
        $file = file_get_contents($file);
    $cert = openssl_x509_parse($file);
    return @$cert['issuer']['x500UniqueIdentifier'];

}
function openssl_check_certkeymatch($cert,$key,$pass = ""){
    $res = array();
    exec('openssl x509 -noout -modulus -in '. $cert .' | openssl md5 2>&1',$res);
    exec('openssl rsa -noout -modulus -in '. $key .' -passin pass:'. $pass .' | openssl md5 2>&1',$res);

    return @$res[0] === @$res[1];
}
function openssl_get_serial($cert,$tmp_file = false){
    if($tmp_file){
        $tfl =sha1(uniqid());
        file_put_contents(PATH_CONTENT.'tmp/'.$tfl,$cert);
        $cert = PATH_CONTENT.'tmp/'.$tfl;
    }
    exec('openssl x509 -in '.$cert.' -serial', $output);
    if(!$output)
        return false;
    $output = explode("=",$output[0]);

    if($tmp_file)
        unlink(PATH_CONTENT.'tmp/'.$tfl);
    $serial = str_split($output[1],2);
    $o = "";
    foreach($serial as $s){
        $o .= substr($s,1);
    }
    return $o;
}