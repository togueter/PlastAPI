<?php
require_once "classes/responses.class.php";
require_once "classes/usuarios.class.php";
require_once "classes/auth.class.php";

$_respuestas = new responses;
$_usuarios = new usuarios;
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
    if(isset($_GET["page"])){
        $pagina = $_GET["page"];
        $listaUsuarios = $_usuarios->listaUsuarios($pagina);
        header("Content-Type-: application/json");
        echo json_encode($listaUsuarios);
        http_response_code(200);
    } else if (isset($_GET['id'])){
        $pacienteid = $_GET['id'];
        $datosPaciente = $_usuarios->obtenerUsuario($pacienteid);
        header("Content-Type-: application/json");
        echo json_encode($datosPaciente);
        http_response_code(200);
    } else if (isset($_GET['token'])){
        $token = $_GET['token'];
        $data = $_usuarios->getUserAuthByToken($token);
        header("Content-Type-: application/json");
        echo json_encode($data);
        http_response_code(200);
    }


}else if($_SERVER['REQUEST_METHOD'] == "POST"){
    
    //recibimos los datos enviados
    $postBody = file_get_contents("php://input");
    //Eviamos los datos al controlador, que es un metodo en la clase usuarios
    $datosArray = $_usuarios->post($postBody);
    //devolvemos respuesta
    header("Content-Type-: application/json");
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);

} else if($_SERVER['REQUEST_METHOD'] == "PUT"){
    header('Access-Control-Allow-Origin: *');
    $postBody = file_get_contents("php://input");
    //enviapos datos al controller
    $datosArray = $_usuarios->put($postBody);
    header("Content-Type-: application/json");
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else if($_SERVER['REQUEST_METHOD'] == "DELETE"){
    echo "hola delete";
}else{
    header('content-type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}

?>