<?php
require_once "classes/pauth.class.php";
require_once "classes/responses.class.php";

$_auth = new pauth;
$_respuestas = new responses;
 #asi evitamos  los errores de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    die();
}
//header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == "GET"){ 

    echo "Hola";
}    
else if($_SERVER['REQUEST_METHOD']== "POST"){

    $postBody = file_get_contents("php://input");
    //enviamos los datos al controlador
    $datosArray = $_auth->login($postBody);
    //devolvemos una respuesta, indicando que tipo de respuesta es
    header('Content-Type: application/json');
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
       http_response_code($responseCode);
      
    } else {
        http_response_code(200);
  
    }
    echo json_encode($datosArray);
}else{
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}
?>