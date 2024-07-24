<?php
 header("Access-Control-Allow-Origin: *");
require_once "conexion/conexion.php";
require_once "responses.class.php";


class pauth extends conexion{

    //metodo de login
    public function login($json){
       
       
        $_respuestas = new responses;

        $datos = json_decode($json,true);  
        if(!isset($datos['usuario']) || !isset($datos['password'])){ //campos Json
            //error con los campos        

          return $_respuestas->error_400();
    
        }else{
            //todo esta bien

            $usuario = $datos['usuario'];
            $password= $datos['password'];  
      
            $datos = $this->obtenerDatosUsuario($usuario); 
                     //verificamos si no existe el usuario 
            if(!$datos){ return $_respuestas->error_200("El usuario $usuario no existe");}
            //return password_verify($password,$datos[0]['Clave']);

            if(!password_verify($password,$datos[0]['Clave'])){
                return $_respuestas->error_200("El password es invalido"); 
            }
           // if($password!=$datos[0]['Clave']){ return $_respuestas->error_200("El password es invalido"); }       
                       
            $verificar = $this->insertarToken($datos[0]['UserID']);

                       
            if($verificar){
                //si se ha guardado. Vamos a formatear el response para reultilizarlo
    
                $result = $_respuestas->response;
                $result["result"] = array(
                    "token" => $verificar                
                );
                return $result;
    
    
            }else{
                    //error a guardar
                    return $_respuestas->error_500("Error interno, no hemos podido guardar el token");
            }    
               

        }
    }

    


    private function obtenerDatosUsuario($userName){
        $query = "SELECT * FROM userdata WHERE UserID = '$userName'";
        $datos = parent::obtenerDatos($query);
        if(isset($datos[0]["UserID"])){ //comprobamos si existe el campo usuarioId, se pone [0] porque el array de una sola fila siempre comienza en 0 y buscamos el campo usuarioId que es el primero
            return $datos;
        } else{
            return 0;
        }
    }

    public function insertarToken($usuarioid){

        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $date = date("Y-m-d H:i");
        $estado = "Activo";
        $query0 = "SELECT Token FROM user_token WHERE UserID = '$usuarioid'";
        $resp = parent::nonQuery($query0);
        if($resp){
            $query = "UPDATE user_token SET Token = '$token' WHERE UserID = '$usuarioid'";
        }else{
            $query = "INSERT INTO user_token (UserID,Token,Estado,Fecha) VALUE ('$usuarioid','$token','$estado','$date')";
        }
    
        $verifica = parent::nonQuery($query);
        if($verifica){ //verificamos si se guardo el token

            return $token;

        }else{
            return 0;
        }
    }

    public function validateToken($token){   
        $_respuestas = new responses;      
        $arrayToken = $this->buscarToken($token);

        if($arrayToken<1){return $_respuestas->error_401("El token es invalido o ha caducado");} 
        else{
            $resp = $_respuestas->response['result'] = "cuchara";
            return $resp;
        } 
  
    }
    private function buscarToken($token){
        $query = "SELECT TokenId  FROM user_token WHERE Token = '$token' AND Estado = 'Activo'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

}