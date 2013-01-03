<?php
/**
 * PayPal Express Checkout
 * -----------------------------------------
 * @author    Ricardo Gamba
 * @desc    Clase para conectar y realizar
 *            transacciones via PayPal con el
 *            servicio de 'Express Checkout'
 **/
class PayPalEC{
    // Variable declaration
    private $config;
    private $params;
    private $response;     // Response http query string
    private $resVars;     // Parsed response var array -> val
    private $payer;     // Array con datos del comprador 
    private $result;

    /**
     * Constructor
     * @params array con parametros de conextion
     */
    public function __construct( $config = array(  ) ) {
        $this->config = array(
            'USER'        => '',
            'PWD'        => '',
            'SIGNATURE'    => '',
            'MODE'        => 'production' // sandbox | production
        );
        if(!empty($config)){
            foreach($config as $name => $value){
                if(array_key_exists(strtoupper($name),$this->config))
                    $this->config[strtoupper($name)]=$value;
            }
        }

        // Servidor de acceso
        if(empty($this->config['MODE']) || $this->config['MODE']=="sandbox"){
            $this->config['ENDPOINT']="https://api-3t.sandbox.paypal.com/nvp";
            $this->config['REDIRECT']="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout";
        }elseif($this->config['MODE']=="production"){
            $this->config['ENDPOINT']="https://api-3t.paypal.com/nvp";
            $this->config['REDIRECT']="https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout";
        }
    }

    /**
     * Magic function SET
     * @params    $name=nombre de la variable
     *            $val=valor de la variable
     */
    public function __set($name="", $val=""){
        if(empty($name))
            return false;
        $this->params[strtoupper($name)]=$val;
        return true;
    }

    /**
     * Magic function GET
     * @params    $name=nombre de la variable
     */
    public function __get($name=""){
        if(empty($name))
            return false;
        return $this->params[strtoupper($name)];
    }

    /**
     * Post variables via cURL POST method
     * @params     $url=url to post
     *            $vars=array to post
     * @return    False or response string
     */
    public function httpPost( $url = "", $vars = array(  ) ) {

        if( empty( $url ) || empty( $vars ) )
            return false;
        //Test if cURL is present
        if( !function_exists( 'curl_init' ) )
            return false;

        // Modificar el formato del array
        // postfields a string
        $pf = http_build_query( $vars );

        $postfields = $pf;

        //Attempt HTTPS connection
        $ch = curl_init(  );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1); // Debug

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; WINDOWS; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset'=>'utf-8,*'));
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        // -- Cookies
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "httpdocs/tmp/");
        curl_setopt($ch, CURLOPT_COOKIEJAR, "httpdocs/tmp/");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

        if( !empty( $port ) ) curl_setopt( $ch, CURLOPT_PORT, $port );

        if( defined( 'CURLOPT_ENCODING' ) ) curl_setopt( $ch, CURLOPT_ENCODING, "" );

        $res = curl_exec ( $ch );

        if( $res == null )
            return false;

        if ( !defined( 'CURLOPT_ENCODING' ) )
            return false;

        curl_close ( $ch );

        return $res;
    }

    /**
     * Examinar y ordenar respuesta en array
     * @ret none
     */
    private function parseResponse($res=""){
        if(empty($res))
            return false;
        $this->response=$res;
        $parts=explode("&",$res);
        foreach($parts as $i => $var){
            $pair=explode("=",$var);
            $this->resVars[$pair[0]]=urldecode($pair[1]);
        }
    }

    /**
     * Obtener el valor de la variable de respuesta
     * @params $name=nombre de la variable
     * @return valor de la variable o false

    >> Variables
    ------------------------------------------
    TIMESTAMP        =2007%2d04%2d05T23%3a23%3a07Z
    CORRELATIONID    =63cdac0b67b50
    ACK            =Success
    VERSION        =52%2e000000
    BUILD            =1%2e0006
    TOKEN            =EC%2d1NK66318YB717835M
    ------------------------------------------
     */
    public function getSC($name=""){
        if(empty($name))
            return false;
        if(empty($this->resVars))
            return false;
        if(array_key_exists($name,$this->resVars))
            return $this->resVars[$name];
        return false;
    }

    public function getAllSC(){
        return $this->resVars;
    }

    /**
     * Conectar o iniciar sesion
     */
    public function setCheckout($redirect=true){
        $params = array(
            'METHOD'        => "SetExpressCheckout",
            'VERSION'        => urlencode('52.0'),
            'USER'            => $this->config['USER'],
            'PWD'            => $this->config['PWD'],
            'SIGNATURE'        => $this->config['SIGNATURE'],
            'AMT'            => $this->params['AMT'],
            'CURRENCYCODE'    => $this->params['CURRENCYCODE'],
            'RETURNURL'        => $this->params['RETURNURL'],
            'CANCELURL'        => $this->params['CANCELURL'],
            'PAYMENTACTION'    => empty($this->params['PAYMENTACTION']) ? "Sale" : $this->params['PAYMENTACTION']
        );
        foreach($this->params as $k => $v){
            if(empty($params[$k]))
                $params[$k]=$v;
        }
        //die(print_r($params));
        $result = $this->httpPost( $this->config['ENDPOINT'], $params );
        if( $result ) {
            $this->parseResponse( $result );
            if($this->getSC("ACK")=="Failure"){
                throw new Exception("PayPal error: ".$this->getSC("L_LONGMESSAGE0"));
                return false;
            }
            if( !empty( $this->response['TOKEN'] ) ) {
                if( $redirect )
                    header("Location: " . $this->config['REDIRECT'] . "&token=" . $this->getSC( "TOKEN" ) ."&useraction=commit" );
                else
                    return "Location: " . $this->config['REDIRECT'] . "&token=" . $this->getSC( "TOKEN" ) ."&useraction=commit";
            }
        }else{
            throw new Exception("Couldn't send the HTTP request");
        }
        return false;
    }

    /**
     * Recibe y procesa los datos de tipo POST
     * devueltos por paypal
     * @params $req=post vars; default to all POST and GET
     */
    public function getCheckoutDetails( $req = "" ){
        if( empty( $req['token'] ) )
            $req = $_REQUEST;
        if( empty( $req ) )
            return false;
        $params = array(
            'METHOD'        => "GetExpressCheckoutDetails",
            'VERSION'        => urlencode('52.0'),
            'USER'            => $this->config['USER'],
            'PWD'            => $this->config['PWD'],
            'SIGNATURE'        => $this->config['SIGNATURE'],
            //'AMT'            => $_SESSION['_ord_det']['envio_monto'],
            'TOKEN'            => $req['token']
        );
        $result = $this->httpPost( $this->config['ENDPOINT'], $params );
        if(!result || empty($result))
            return false;
        $parts=explode("&",$result);
        foreach($parts as $i => $pair){
            $kv=explode("=",$pair);
            $this->payer[strtoupper($kv[0])]=urldecode($kv[1]);
        }
        return $this->payer;
    }

    /**
     * Devuelve el valor de la variable de detalles
     * del usuario
     * @params $name = nombre de la variable
     * @return string | false

    >> Variables
    ------------------------------------------
    TIMESTAMP        =2007%2d04%2d05T23%3a44%3a11Z
    CORRELATIONID    =6b174e9bac3b3
    ACK            =Success
    VERSION        =52%2e000000
    BUILD            =1%2e0006
    TOKEN            =EC%2d1NK66318YB717835M
    EMAIL            =jsmith01@example.com
    PAYERID        =7AKUSARZ7SAT8
    PAYERSTATUS    =verified
    FIRSTNAME        =...
    LASTNAME        =...
    COUNTRYCODE    =US
    BUSINESS        =...
    SHIPTONAME        =...
    SHIPTOSTREET    =...
    SHIPTOCITY        =...
    SHIPTOSTATE    =CA
    SHIPTOCOUNTRYCODE=US
    SHIPTOCOUNTRYNAME=United%20States
    SHIPTOZIP        =94666
    ADDRESSID        =...
    ADDRESSSTATUS    =Confirmed
    ------------------------------------------
     */
    public function getGD($name=""){
        if(empty($name))
            return false;
        if(array_key_exists(strtoupper($name),$this->payer))
            return $this->payer[strtoupper($name)];
        return false;
    }

    /**
     * Ejecuta y confirma el pago directamente en
     * paypal
     */
    public function doCheckoutPayment(){
        $params = array(
            'METHOD'        => "DoExpressCheckoutPayment",
            'VERSION'        => urlencode('52.0'),
            'USER'            => $this->config['USER'],
            'PWD'            => $this->config['PWD'],
            'SIGNATURE'        => $this->config['SIGNATURE'],
            'TOKEN'            => $this->getGD("TOKEN"),
            'PAYMENTACTION'    => "Sale",
            'PAYERID'        => $this->getGD("PAYERID"),
            'AMT'            => $this->params['AMT'],
            'CURRENCYCODE'     => $this->params['CURRENCYCODE']
        );
        $result = $this->httpPost( $this->config['ENDPOINT'], $params );
        if(!result || empty($result))
            return false;
        $parts=explode("&",$result);
        foreach($parts as $i => $pair){
            $kv=explode("=",$pair);
            $this->result[strtoupper($kv[0])]=urldecode($kv[1]);
        }
        return $this->result;
    }

    /**
     * Devuelve el valor de la variable de detalles
     * del resultado de la transaccion
     * @params $name = nombre de la variable
     * @return string | false

    >> Variables
    ------------------------------------------
    TIMESTAMP        =2007%2d04%2d05T23%3a30%3a16Z
    CORRELATIONID    =333fb808bb23 &ACK=Success
    VERSION        =52%2e000000
    BUILD            =1%2e0006
    TOKEN            =EC%2d1NK66318YB717835M
    TRANSACTIONID    =043144440L487742J
    TRANSACTIONTYPE=expresscheckout
    PAYMENTTYPE    =instant
    ORDERTIME        =2007%2d04%2d05T23%3a30%3a14Z
    AMT            =19%2e95
    CURRENCYCODE    =USD
    TAXAMT            =0%2e00
    PAYMENTSTATUS    =Completed
    PENDINGREASON    =None
    REASONCODE        =None
    FEEAMT            =0%2e43
    --------------------------------------------
     */
    public function getDP($name=""){
        if(empty($name))
            return false;
        if(array_key_exists(strtoupper($name),$this->result))
            return $this->result[strtoupper($name)];
        return false;
    }
}