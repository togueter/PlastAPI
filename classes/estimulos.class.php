<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";

class estimulos extends conexion{

    public function getListaStimulos(){
        $_respuestas = new responses;
        $respuesta = $_respuestas->response;
        $query = "SELECT * FROM estimulos";
        $resp = parent::obtenerDatos($query);
        if($resp){          
            $respuesta["result"] = array(
                "Lista" => $resp
            );     
            return $respuesta;
        } else{
            return $_respuestas->error_200("No se pudo cargar la base de datos");
        }  
    }
    public function getControlados(){
        $_respuestas = new responses;
        $respuesta = $_respuestas->response;
        $archivo = './controlados.txt';
        if (file_exists($archivo)) {
            // Lee el contenido del archivo en una cadena

            $contenido = file_get_contents($archivo);
           $respuesta["result"] = array(
              "Lista" =>$contenido
           );     
           return $respuesta;

            
        } else {
            return $_respuestas->error_200("No se pudo cargar la base de datos");
        }       
    }
}
?>