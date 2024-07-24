<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";

class gameconfig extends conexion{


    public function GetConfig(){
        $_respuestas = new responses;
        $respuesta = $_respuestas->response;
        $archivo = './gameconfig.txt';
        if (file_exists($archivo)) {
            // Lee el contenido del archivo en una cadena

            $contenido = file_get_contents($archivo);
           $respuesta["result"] = array(
              "config" => json_decode($contenido)
           );     
           return $respuesta;

            
        } else {
            return $_respuestas->error_200("No se pudo cargarla configuracion");
        }       
    }



}


?>