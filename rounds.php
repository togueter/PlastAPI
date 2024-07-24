<?php
require_once "classes/responses.class.php";
require_once "classes/usuarios.class.php";
require_once "classes/rounds.class.php";
require_once "classes/auth.class.php";

$_respuestas = new responses;
$_rounds = new rounds;
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
            echo "No autorizado";
            return ;
        }      
    }
  
    if(isset($_GET["page"])){
     
        $pagina = $_GET["page"];
        if(isset($_GET["limit"])){
            $limit = $_GET["limit"];
            $listaRondas = $_rounds->listaRondas($pagina,$limit);
        }else{
            $listaRondas = $_rounds->listaRondas($pagina);
        }
 
        header("Content-Type-: application/json");
        echo json_encode($listaRondas);
        http_response_code(200);
    } else if (isset($_GET['id'])){

        $userId = $_GET['id'];
        if(isset($_GET['page'])){
            $datosRonda = $_rounds->listaRondaPaciente($userId,$_GET['page']);
        }elseif(isset($_GET['page']) && isset($_GET['limit'])){
            $datosRonda = $_rounds->listaRondaPaciente($userId,$_GET['page'],$_GET['limit']);
        }
        else if (isset($_GET['last'])){      
            $datosRonda = $_rounds->lastRound($userId);
        }else        
        {
            $datosRonda = $_rounds->listaRondaPaciente($userId);
        }
   
        header("Content-Type-: application/json");
        echo json_encode($datosRonda);
        http_response_code(200);
    } 


}else if($_SERVER['REQUEST_METHOD'] == "POST"){
   
    $headers = getallheaders();
    if(!isset($headers['token'])){ return $_respuestas->error_401();}
    if(isset($headers['token'])){ 
      
        $resp = $_auth->validateToken($headers['token']);
        if($resp!="cuchara"){
            echo "No autorizado";
            return ;
        }      
    }
      //recibimos los datos enviados
      $postBody = file_get_contents("php://input");
      //Eviamos los datos al controlador, que es un metodo en la clase usuarios
      $datosArray = $_rounds->post($postBody);
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

    echo "Hola PUT";
    
} else if($_SERVER['REQUEST_METHOD'] == "DELETE"){
    echo "hola delete";
}else{
    header('content-type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}

?>