<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";
require_once "usuarios.class.php";
require_once "auth.class.php";
require_once "clinicas.class.php";
class pacientes extends conexion {

    public function listaPacientes($pagina = 1, $limit = 25, $clinCode = "Global"){
        $_clinicas = new clinicas;
        $inicio =0;
        $cantidad = $limit;
        if($pagina >1){
            $inicio =($cantidad * ($pagina - 1) + 1);
            $cantidad = $cantidad * $pagina;
        }
      
        if($clinCode=="Global"){           
            $query = "SELECT UserID, ClinicID, Score, Operado,Grupo, ResponseTime, UTest, UCompleted, Difficulty, LastRoundID, TestControlledCount FROM userdata LIMIT $inicio,$limit";
        }else{
            $query = "SELECT UserID, ClinicID, Score, Operado,Grupo, ResponseTime, UTest, UCompleted, Difficulty, LastRoundID, TestControlledCount FROM userdata WHERE ClinicID = '$clinCode' LIMIT $inicio,$limit";
        }

       
        $datos = parent::obtenerDatos($query);
      
        return ($datos);
    }
    
    public function lista($clinica = "Global"){
        if($clinica != "Global"){
            $query = "SELECT COUNT(*) FROM userdata WHERE CLinicID = '$clinica' ";
        }else{
            $query = "SELECT COUNT(*) FROM userdata ";
        }
   
        $datos = parent::obtenerDatos($query);
        return $datos[0]['COUNT(*)'];
    }
       
    public function obtenerPaciente($id){
        $query = "SELECT * FROM userdata WHERE UserID = '$id'";
        return parent::obtenerDatos($query);
    }
    public function nuevoPaciente($userid,$password,$clinica,$operado,$grupo){
        $encrypted = parent::encriptar($password);
      // $encrypted = $password;
        $query = "INSERT INTO userdata (UserID, ClinicID, Operado, Grupo, Score, ResponseTime, UTest, UCompleted, Difficulty, LastRoundID, TestControlledCount,clave) values
        ('" . $userid . "','" . $clinica . "','" . $operado . "','" . $grupo . "','0','0','0','0','0','0','3','" .  $encrypted . "')";
        $respuesta = parent::nonQuery($query);     

        if($respuesta){

            return $respuesta;
        }else{
            return 0;
        }
       
    }
    public function getPatient($userId){
        $_respuestas = new responses;
        $resp = $this->obtenerPaciente($userId);  
        if($resp){
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "NetUserData" => $resp
            );     
            return $respuesta;
        }else{
           
            return $_respuestas->error_200("El Paciente no existe");

        }
    }

    public function post($json){
        $_respuestas = new responses;
        $_usuarios = new usuarios;
        $_auth = new auth;
        $_clinicas = new clinicas;
        $datos = json_decode($json,true);     
      
        if(!isset($datos['usuario']) || !isset($datos['password']) || !isset($datos['clinica']) || !isset($datos['operado']) || !isset($datos['grupo'])){
            return $_respuestas->error_400();
        }
     
        if(($datos['usuario']=="") || ($datos['password']=="") || ($datos['clinica']=="") || ($datos['operado']=="") || isset($datos['grupo'])==""){
           
            return $_respuestas->error_200("Los campos no pueden estar vacios");
        }       
        //comprobamos si ya existe el paciente
        $resp = $_usuarios->obtenerUsuario($datos['usuario']);  
        
        if($resp["status"] == "ok"){
            return $_respuestas->error_200("Error, el usuario ya existe");
        }
  
       //Intentamos crear el usuario $userid,$password,$estado,$autor,$clinica
       // $resp = $_usuarios->nuevoUsuario($datos['usuario'],$datos['password'],"Activo","0",$datos['clinica']);
       // if(!$resp){ return $_respuestas->error_500("Error, no se ha podido crear el paciente");}
       //Intentamos crear el paciente $userid,$clinica,$operado,$grupo
        $resp = $this->nuevoPaciente($datos['usuario'],$datos['usuario'],$datos['clinica'],$datos['operado'],$datos['grupo']);
        $token = $_auth->insertarToken($datos['usuario']);      
        if($resp){
            $respuesta = $_respuestas->response;
            $respuesta["result"] = array(
                "UserID" => $datos['usuario']
                
            );     
            return $respuesta;
        }else{
            $_usuarios->borrarUsuario($datos['usuario']);
            return $_respuestas->error_500("Usuario borrado");

        }
       
       
    }

    public function put($json){
        $_respuestas = new responses;
        $datos = json_decode($json,true);

       if(!isset($datos['UserID'])){
        return $_respuestas->error_400();
       }else{ 
   
         $resp = $this->modificarPaciente($json);       
 
            if($resp>0){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "result" => 'actualizado'
                );

                return $respuesta;
            }else{
                $respuesta["result"] = array(
                    "result" => 'noactualizado'
                );
                return $respuesta;

            }
         
       }
    }

    public function delete($json){
        $_respuestas = new responses;
        $_usuarios = new usuarios;
        $datos = json_decode($json,true);   
     
        if(!isset($datos['token'])){return $_respuestas->error_401();}        
   
        $token = $this->buscarToken($datos['token']); //buscamos el token
        if(!$token){ return $_respuestas->error_401("El token es invalidado o ha caducado");}

        if(!isset($datos['UserID'])){return $_respuestas->error_400();}                              
       
        $resp = $_usuarios->obtenerUsuario($datos['UserID']);
        if(!$resp){return $_respuestas->error_200("El paciente no existe");}
        $resp = $this->eliminarUsuario($datos['UserID']);
            return $resp;
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "pacienteId" => $datos['UserID']
                );
                return $respuesta;
            }else{
                return $_respuestas->error_500();
            }
      
       
       

    }

    private function eliminarUsuario($userid){     
        $_respuestas = new responses;

        $query = "DELETE FROM users WHERE UserID= '$userid'";
        $resp = parent::nonQuery($query);      
        if($resp < 1){ return $_respuestas->error_500("No se pudo eliminar el usuario");}   

        $query = "DELETE FROM userdata WHERE UserID= '$userid'";
        $resp = parent::nonQuery($query);
 
        if($resp < 1){ return $_respuestas->error_500("No se pudo eliminar el paciente");}
        $token = $this->buscarToken($userid);   
        if($token==0){
        $query = "DELETE FROM user_token WHERE UserID= '$userid'";
        $resp = parent::nonQuery($query); 
        if($resp < 1){ return $_respuestas->error_200("No se pudo eliminar el usuario completamente");}
        }else {
            return $_respuestas->error_200("El usuario $userId ha sido eliminado");
        }
        return $resp;
        
    }
    private function checkToken($token){
        if(!isset($token)){         
            return $_respuestas->error_401();
        }else{
            $this->token = $token;
            $arrayToken = $this->buscarToken($token);
            if(!$arrayToken){
                return $_respuestas->error_401("El token es invalido o ha caducado");          
            }           
        }
    }

    public function modificarPaciente($json){
        $paciente = json_decode($json,true);

        $query = "UPDATE userdata SET Operado = '" . $paciente['Operado'] . "', Grupo = '" . $paciente['Grupo'] . "', Score='" . $paciente['Score'] . "', ResponseTime='" . $paciente['ResponseTime'] . "', UTest='" . $paciente['UTest'] . "', UCompleted='"
         . $paciente['UCompleted'] . "', Percentage='" . $paciente['Percentage'] . "', Difficulty='" . $paciente['Difficulty'] .
          "', LastRoundID='" . $paciente['LastRoundID'] . "', TestControlledCount='" . $paciente['TestControlledCount'] . "'WHERE UserID='" . $paciente['UserID'] . "'";
        $respuesta = parent::nonQuery($query);   

        if($respuesta >= 1){

            return $respuesta;
        }else{
            return 0;
        }
    
    }
    private function buscarToken($token){
        $query = "SELECT TokenId, UserID, Estado FROM user_token WHERE Token = '$token' AND Estado = 'Activo'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }
    public function userPosition($userID){
        $query =  $query = "SELECT Score FROM userdata WHERE UserID = '$userID'";
        $score = parent::obtenerDatos($query)[0]['Score'];
        $query = "SELECT COUNT(*) + 1 AS rank FROM userdata WHERE score = '$score'";
        $result = parent::obtenerDatos($query)[0]['rank'];     
        return $result;
    }
    public function getRank($userID,$limit = 10)
    {
        $_respuestas = new responses;
        $respuesta = $_respuestas->response;
        $resp = $this->obtenerPaciente($userID);  
        if(count($resp)==0) {

           return $_respuestas->error_200("El Paciente no existe");
        }

        $query =  $query = "SELECT Score FROM userdata WHERE UserID = '$userID'";      
        $score = parent::obtenerDatos($query)[0]['Score'];
        $query = "SELECT COUNT(*) + 1 AS rank FROM userdata WHERE score > '$score'";
        $rank = parent::obtenerDatos($query)[0]['rank']; 
        $query = "SELECT COUNT(*) AS totalPlayers FROM userdata";
        $totalPlayers = parent::obtenerDatos($query)[0]; 
        #print_r("UserID: " . $userID . " Score:" .$score . " Rank:" .  $rank . " Total Player: " . $totalPlayers['totalPlayers']. "\n" );
        // Paso 5: Ajustar los límites para los jugadores a recuperar
        $startRank = max((int)$rank - 5, 1); // No puede ser menor que 1
        #print_r("start rank " . $startRank . "\n");
        $endRank = min((int)$rank + 5, $totalPlayers['totalPlayers']);
        #print_r("end rank " . $endRank ."\n");
        if ($startRank == 1 && $endRank < 21) {
            $endRank = min(21, $totalPlayers);
        }
          // Si está al final de la lista
        if ($endRank == $totalPlayers && $startRank > $totalPlayers - 20) {
            $startRank = max($totalPlayers - 20, 1);
        }
        $query = "SELECT *, @rownum := @rownum + 1 AS rank FROM (SELECT p.UserID, p.Score, p.Difficulty FROM userdata p, (SELECT @rownum := 0) r ORDER BY p.Score DESC) 
        ranked HAVING rank BETWEEN $startRank AND $endRank ORDER BY rank ASC LIMIT $limit";
         $playersInRange  = parent::obtenerDatos($query);  
          // return json_encode($playersInRange, JSON_PRETTY_PRINT); 
        if($playersInRange){
          
            $respuesta["result"] = array(
                "Lista" => $playersInRange
            );     
            return $respuesta;
        } else{
            return $_respuestas->error_400("hubo un error");
        }
       
    }
    public function getTop3()
    {
        $_respuestas = new responses;
        $respuesta = $_respuestas->response;
        $query = "SELECT *, (SELECT COUNT(*) FROM UserData b WHERE a.Score <= b.Score) AS rank FROM UserData a ORDER BY rank ASC LIMIT 3";
        $resp =  parent::obtenerDatos($query);
        if($resp){          
            $respuesta["result"] = array(
                "Lista" => $resp
            );     
            return $respuesta;
        } else{
            return $_respuestas->error_400("hubo un error");
        }      
    }
       
    public function getTreeUser($name)
    {  
        $_respuestas = new responses;
        $respuesta = $_respuestas->response;
        $query ="CREATE TEMPORARY TABLE ranking SELECT  *, (SELECT  1+COUNT(*)FROM UserData b WHERE a.Score < b.Score) AS rank FROM UserData a ;";
        $query.="(SELECT * FROM ranking WHERE ranking.rank = (SELECT rank FROM ranking WHERE UserID ='" . $name . "' ) -2 ORDER BY rank DESC LIMIT 1)
                UNION ALL (SELECT * FROM ranking WHERE ranking.rank = (SELECT rank FROM ranking WHERE UserID ='" . $name . "' ) -1 ORDER BY rank DESC LIMIT 1)
                 UNION ALL (SELECT * FROM ranking WHERE UserID =  '" . $name . "' )
                  UNION ALL (SELECT * FROM ranking WHERE ranking.rank > (SELECT rank FROM ranking WHERE UserID =  '" . $name . "' ) ORDER BY rank ASC LIMIT 2);";
        $resp = parent::multiQuery($query);
        if($resp){          
            $respuesta["result"] = array(
                "Lista" => $resp
            );     
            return $respuesta;
        } else{
            return $_respuestas->error_400("hubo un error");
        }      
    }
}



?>