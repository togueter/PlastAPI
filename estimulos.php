<?php
require_once "classes/responses.class.php";
require_once "classes/auth.class.php";
require_once "classes/estimulos.class.php";

$_respuestas = new responses;
$_estimulos = new estimulos;
$_auth = new auth;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type, accept');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    die();
}

if($_SERVER['REQUEST_METHOD'] == "GET")
{
    $headers = getallheaders();
    if(!isset($headers['token'])){ return $_respuestas->error_401();}
    if(isset($headers['token'])){ 
      
        $resp = $_auth->validateToken($headers['token']);
        if($resp!="cuchara"){
            echo json_encode($_respuestas->error_401());
            return ;
        }      
    }

    if(isset($_GET['controlled'])){
        $listaEstimulos = $_estimulos->getControlados(); 
    } else {
        $listaEstimulos = $_estimulos->getListaStimulos();
    }
  
    header("Content-Type-: application/json");
    echo json_encode($listaEstimulos);
    http_response_code(200);
}
?>