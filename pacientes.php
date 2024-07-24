<?php
require_once "classes/responses.class.php";
require_once "classes/pacientes.class.php";
require_once "classes/auth.class.php";

$_respuestas = new responses;
$_pacientes = new pacientes;
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

        if(isset($_GET["limit"])){

            $limit = $_GET["limit"]; 
                     
                if(isset($_GET["clinica"])){

                    $clinica = $_GET["clinica"];

                    $listaPacientes = $_pacientes->listaPacientes($pagina,$limit,$clinica);

                } else{

                    $listaPacientes = $_pacientes->listaPacientes($pagina,$limit);
                    
                }

        }else{
            $listaPacientes = $_pacientes->listaPacientes($pagina);
        }
 
        header("Content-Type-: application/json");
        echo json_encode($listaPacientes);
        http_response_code(200);
    } else if (isset($_GET['id'])){

        $pacienteid = $_GET['id'];
        $datosPaciente = $_pacientes->getPatient($pacienteid);
        header("Content-Type-: application/json");
        echo json_encode($datosPaciente);
        http_response_code(200);
    }else if (isset($_GET['rank'])){
        $pacienteid = $_GET['rank'];
        if(isset($_GET['limit']))
        {
            $limit = $_GET['limit'];
            $datosPaciente = $_pacientes->getRank($pacienteid,$limit);
        }else{
            $datosPaciente = $_pacientes->getRank($pacienteid);
        }
     
        header("Content-Type-: application/json");
        echo json_encode($datosPaciente);
        http_response_code(200);
    }else if (isset($_GET['top3'])){

        $pacienteid = $_GET['top3'];
        $datosPaciente = $_pacientes->getTop3();
        header("Content-Type-: application/json");
        echo json_encode($datosPaciente);
        http_response_code(200);
    }else if (isset($_GET['clinica'])){

        $clinica = $_GET['clinica'];
        $lista = $_pacientes->lista($clinica);
        header("Content-Type-: application/json");
        echo json_encode($lista);
        http_response_code(200);
    }else if (isset($_GET['3users'])){

        $lista = $_pacientes->GetTreeUser($_GET['3users']);
        header("Content-Type-: application/json");
        echo json_encode($lista);
        http_response_code(200);
    }else{
        $lista = $_pacientes->lista();      
        header("Content-Type-: application/json");
        echo json_encode($lista);
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


    $postBody = file_get_contents("php://input");
    //enviapos datos al controller
    $datosArray = $_pacientes->post($postBody);
 
    header("Content-Type-: application/json");
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);


} else if($_SERVER['REQUEST_METHOD'] == "PUT"){

        $postBody = file_get_contents("php://input");
        //enviapos datos al controller
        $datosArray = $_pacientes->put($postBody);
        header("Content-Type-: application/json");
        if(isset($datosArray["result"]["error_id"])){
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        }else{
            http_response_code(200);
        }
        echo json_encode($datosArray);
    
} else if($_SERVER['REQUEST_METHOD'] == "DELETE"){
    $headers = getallheaders();
    if(isset($headers['token']) && isset($headers['UserID'])){
        //recibimos datos por el header
        $send = [
            "token" => $headers['token'],
            "UserID" => $headers['UserID']
        ];
        $postBody = json_encode($send);
    }else{
        
        //recibimos datos enviados      
        $postBody= file_get_contents("php://input");
    }
       //enviamos datos al controlador
       $datosArray = $_pacientes->delete($postBody);
    
       header('Content-Type: application/json');
       if(isset($datosArray["result"]["error_id"])){
           $responseCode = $datosArray["result"]["error_id"];
          http_response_code($responseCode);
         
       } else {
           http_response_code(200);  
       }
       echo json_encode($datosArray);

}else{
    header('content-type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}
?>