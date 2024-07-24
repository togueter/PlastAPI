<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";
require_once "pacientes.class.php";
require_once "auth.class.php";
class usuarios extends conexion {
    private $token = "";
//37733fae2175224932e006b693530871
    public function listaUsuarios($pagina = 1){
        $inicio =0;
        $cantidad = 25;
        if($pagina >1){
            $inicio =($cantidad * ($pagina - 1) + 1);
            $cantidad = $cantidad * $pagina;
        }

     
       $query = "SELECT * FROM users LIMIT $inicio,$cantidad";
        $datos = parent::obtenerDatos($query);
        return ($datos);
    }

    public function obtenerUsuario($id){
        $_respuestas = new responses;

        $query = "SELECT * FROM users WHERE UserID = '$id'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "UserData" => $resp
            );

            return $respuesta;
        }else{
            return $_respuestas->error_200("El usuario no existe");

        }
        return $respuesta;
    }

    public function post($json){
        $_respuestas = new responses;
        $_auth = new auth;
        $datos = json_decode($json,true);
/*
        if(!isset($datos['token'])){         
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->buscarToken();
            if(!$arrayToken){
                return $_respuestas->error_401("El token es invalido o ha caducado");          
            }           
        }*/
   

       if(!isset($datos['usuario']) || !isset($datos['password']) || !isset($datos['estado']) || !isset($datos['autor']) || !isset($datos['clinica'])|| !isset($datos['nombre'])|| !isset($datos['email'])){
        return $_respuestas->error_400();
       } else if(($datos['usuario']=="") || ($datos['password']=="") || ($datos['estado']=="") || ($datos['autor']=="") || isset($datos['clinica'])==""|| isset($datos['nombre'])==""|| isset($datos['email'])==""){
    
        return $_respuestas->error_200("Los campos no pueden estar vacios");
       }else{
            $password = parent::encriptar($datos['password']);
            $resp = $this->obtenerUsuario($datos['usuario']);     
    
            if($resp['status']=="ok"){ 
               //return  $datos['usuario'];         
              return $_respuestas->error_200("El usuario ya existe");
            }
     
            $resp = $this->nuevoUsuario($datos['usuario'], $datos['password'],$datos['estado'],$datos['autor'],$datos['clinica'],$datos['nombre'],$datos['email']);
         
            if($resp){
                $token = $_auth->insertarToken($datos['usuario']); 
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "ID" => $resp
                );     
                return $respuesta;
            }else{
                return $_respuestas->error_500();

            }
           
       }
    }

    public function nuevoUsuario($userid,$password,$estado,$autor,$clinica,$nombre,$email){
        $encrypted = parent::encriptar($password);
        $query = "INSERT INTO users (UserID, Password, Estado, Autor, Clinica,Nombre,Email) values ('" . $userid . "','" . $encrypted . "','" . $estado . "','" . $autor . "','" . $clinica . "','" . $nombre . "','" . $email . "')";
        $respuesta = parent::nonQueryId($query);     
       
        if($respuesta){

            return $respuesta;
        }else{
            return 0;
        }
       
    }
    
    public function borrarUsuario($userid){

        $query = "DELETE FROM users WHERE UserID= '$userid'";
        $respuesta = parent::nonQuery($query);    
        if($respuesta >= 1){
            return $respuesta;

        }else{
            return 0;
        }

    }

    public function put($json){
        $_respuestas = new responses;
        $datos = json_decode($json,true);


        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->buscarToken();
            if(!$arrayToken){
                return $_respuestas->error_401("El token es invalido o ha caducado");          
            }           
        }


       if(!isset($datos['usuario'])){
        return $_respuestas->error_400();
       } else if(($datos['usuario']=="")){
        return $_respuestas->error_200("El usuario  no puede estar en blanco");
       }else{
        $user = $this->obtenerUsuario($datos['usuario']);   
        if(!$user){
            return $_respuestas->error_200("El usuario no existe");
        }
        $usuario = $user[0];

        $resp = "";

        if(isset($datos['estado']) && isset($datos['autor'])){
            $estado = $datos['estado'];
            $autor = $datos['autor'];
            if($estado == $usuario["Estado"] && $autor == $usuario["Autor"]){
                return $_respuestas->error_200("Los datos no se han modificado porque son los mismos");
            }
            $resp = $this->modificarUsuario($usuario['UserID'],$estado,$autor);
        }
 

        if(isset($datos['password'])){
            $password = $datos['password'];
            if($password == $usuario['Password']){
                return $_respuestas->error_200("No se puede usar la misma contraseña que  tenías");
            }
            $resp = $this->modificarPassword($usuario['UserID'],$password);
        
        }  
   
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "pacienteId" => $datos['usuario']
                );

                return $respuesta;
            }else{
                return $_respuestas->error_500();

            }
         
       }
    }
    public function modificarUsuario($userid,$estado,$autor){
     
        $query = "UPDATE users SET Estado='" . $estado . "',Autor='" . $autor . "' WHERE UserID='" . $userid . "'";
        $respuesta = parent::nonQuery($query);   
       
        if($respuesta >= 1){

            return $respuesta;
        }else{
            return 0;
        }
    
    }

    public function modificarPassword($userid,$password)
    {       
        
        $query = "UPDATE users SET Password='" . $password . "' WHERE UserID='" . $userid . "'";
        $respuesta = parent::nonQuery($query);   
  
        if($respuesta >= 1){

            return $respuesta;
        }else{
            return 0;
        }
    }

    private function buscarToken(){
        $query = "SELECT TokenId, UserID, Estado FROM user_token WHERE Token = '" . $this->token . "' AND Estado = 'Activo'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

    private function actualizarToken($tokenid){
        $date = date("d-m-Y H:i");
        $query = "UPDATE usuarios_token SET Fecha = '$date' WHERE TokenId = '$tokenid'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }

    }

    public function getUserAuthByToken($tokenid){
        $_respuestas = new responses;
        $query = "SELECT UserID  FROM user_token WHERE Token = '$tokenid'";
        $resp = parent::obtenerDatos($query);
        $userID = $resp[0]['UserID'];  
        if($resp){
            $query = "SELECT * FROM users WHERE UserID = '$userID'";
            $resp = parent::obtenerDatos($query);      
            $respuesta = $_respuestas->response;
            $respuesta["result"] = $resp[0];

            return $respuesta;
        }else{
            return $_respuestas->error_200("No se ha encontrado el token");

        }
        return $respuesta;
    }
}
?>