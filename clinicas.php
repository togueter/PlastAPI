<?php
require_once "classes/responses.class.php";
require_once "classes/usuarios.class.php";
require_once "classes/clinicas.class.php";
require_once "classes/auth.class.php";

$_respuestas = new responses;
$_clinicas = new clinicas;
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
header('Access-Control-Allow-Origin: *');
//header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == "GET"){         

    $headers = getallheaders();
    if(!isset($headers['token'])){ return $_respuestas->error_401();}
    if(isset($headers['token'])){ 
      
        $resp = $_auth->validateToken($headers['token']);
        if($resp!="cuchara"){
            echo "No autorizado";
            return ;
        }      
    }


    if(isset($_GET["page"])){
     
        $pagina = $_GET["page"];
        if(isset($_GET["limit"])){
            $limit = $_GET["limit"];
            $listaClinicas = $_clinicas->listaclinicas($pagina,$limit);
        }else{
            $listaClinicas = $_clinicas->listaclinicas($pagina);
        }
 
        header("Content-Type-: application/json");
        echo json_encode($listaClinicas);
        http_response_code(200);
    } else if (isset($_GET['id'])){

        $clinicaid = $_GET['id'];
        $datosclinica = $_clinicas->obtenerClinica($clinicaid);
        header("Content-Type-: application/json");
        echo json_encode($datosclinica);
        http_response_code(200);
    } else {
        $listalocalidades = $_clinicas->obtenerLocalidades();
        header("Content-Type-: application/json");
        echo json_encode($listalocalidades);
        http_response_code(200);
    } 


}else if($_SERVER['REQUEST_METHOD'] == "POST"){

    echo "Hola POST";

} else if($_SERVER['REQUEST_METHOD'] == "PUT"){

    echo "Hola PUT";
    
} else if($_SERVER['REQUEST_METHOD'] == "DELETE"){
    echo "hola delete";
}else{
    header('content-type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}



?>