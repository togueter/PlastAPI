<?php

require_once "conexion/conexion.php";
require_once "responses.class.php";
require_once "usuarios.class.php";

class rounds extends conexion{


    public function listaRondas($pagina = 1, $limit = 10){
        $inicio =0;
        $cantidad = $limit;

        if($pagina >1){
            $inicio =($cantidad * ($pagina - 1) + 1);
            $cantidad = $cantidad * $pagina;
        }

        $query = "SELECT RoundID,Date,UserID,Grupo,Clinica,StimulusIDs,StimulusResponses,StimulusTimes FROM eventos LIMIT $inicio,$cantidad";
        $datos = parent::obtenerDatos($query);
      
        return ($datos);
    }
    public function lastRound($id){
        $_respuestas = new responses;
        $query = "SELECT RoundID,Date,UserID,Grupo,Clinica,StimulusIDs,StimulusResponses,StimulusTimes FROM eventos WHERE UserID = '$id' ORDER BY RoundID DESC LIMIT 1";
    
        $datos = parent::obtenerDatos($query);

        if($datos){
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "RoundData" => $datos
            );
           return $respuesta;
        }else{
            return $_respuestas->error_200("Usuario desconocido");
        }
    }
    public function listaRondaPaciente($id, $pagina = 1, $limit = 10){
        $inicio =0;
        $cantidad = $limit;
        $_respuestas = new responses;
        if($pagina >1){
            $inicio =($cantidad * ($pagina - 1) + 1);
            $cantidad = $cantidad * $pagina;
        }

        $query = "SELECT RoundID,Date,UserID,Grupo,Clinica,StimulusIDs,StimulusResponses,StimulusTimes FROM eventos WHERE UserID = '$id' LIMIT $inicio,$cantidad ";
        $datos = parent::obtenerDatos($query);
        if($datos){
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "RoundData" => $datos
            );
           return $respuesta;
        }else{
            return $_respuestas->error_200("Usuario desconocido");
        }
       // return ($datos);
    }

    public function obtenerRonda($id,$userId){
        $query = "SELECT RoundID,Date,UserID,Grupo,Clinica,StimulusIDs,StimulusResponses,StimulusTimes FROM eventos WHERE RoundID = '$id' AND UserID = '$userId'";
        return parent::obtenerDatos($query);
    }

    public function post($json){
        $_respuestas = new responses;
        $_usuarios = new usuarios;
        $datos = json_decode($json,true);
       if(!isset($datos['RoundID']) || !isset($datos['UserID'])|| !isset($datos['Date'])|| !isset($datos['StimulusIDs'])|| !isset($datos['StimulusResponses'])|| !isset($datos['StimulusTimes'])){
        return $_respuestas->error_400();
       } else if(($datos['RoundID']=="") ||($datos['UserID']=="") ){
        return $_respuestas->error_200("¡El RoundID o el UserID no pueden estar vacios!");
       }else{
        $user = $_usuarios->obtenerUsuario($datos['UserID']);   
        if(!$user){
            return $_respuestas->error_200("El usuario no existe");
        }
        $ronda = $this->obtenerRonda($datos['RoundID'],$datos['UserID']);

        if($ronda){
            return $_respuestas->error_200("Algo va mal, la ronda ya existe");
        }
      
         $resp = $this->nuevaRonda($datos); 
   
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "RoundID" => $datos['RoundID']
                );

                return $respuesta;
            }else{
                return $_respuestas->error_500();

            }
         
       }

    }


    public function nuevaRonda($json){ 
        $query = "INSERT INTO eventos (RoundID,Date,UserID,Grupo,Clinica,StimulusIDs,StimulusResponses,StimulusTimes) values
        ('" . $json['RoundID'] . "','" . $json['Date'] . "','" . $json['UserID'] . "','" . $json['Grupo'] . "','" . $json['Clinica'] . "','" . $json['StimulusIDs'] . "','" . $json['StimulusResponses'] . "','" . $json['StimulusTimes'] . "')";
        $respuesta = parent::nonQueryId($query);    
        
        if($respuesta){

            return $respuesta;
        }else{
            return 0;
        }
       
    }

}

?>