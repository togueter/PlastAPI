<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";
require_once "clinicas.class.php";

class clinicas extends conexion{


    public function listaclinicas($pagina = 1, $limit = 10){
        $inicio =0;
        $cantidad = $limit;

        if($pagina >1){
            $inicio =($cantidad * ($pagina - 1) + 1);
            $cantidad = $cantidad * $pagina;
        }

        $query = "SELECT * FROM clinicas LIMIT $inicio,$cantidad";
        $datos = parent::obtenerDatos($query);
      
        return ($datos);
    }

    public function obtenerClinica($code){
        $query = "SELECT * FROM clinicas WHERE Identificador = '$code'";
        $datos = parent::obtenerDatos($query);
        return ($datos);
    }

    public function obtenerLocalidades(){
        $query = "SELECT DISTINCT Localidad,Identificador  FROM clinicas ";
        $datos = parent::obtenerDatos($query);
      
        return ($datos);
    }


    public function buscarToken($token){
        $query = "SELECT TokenId, UserID, Estado FROM user_token WHERE Token = '$token' AND Estado = 'Activo'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }
}

?>