<?php

class conexion{
    private $server;
    private $user;
    private $password;
    private $database;
    private $port;
    private $conexion;
    const MYSQLI_CODE_DUPLICATE_KEY = 1062;

    function __construct(){
        $listadatos = $this->datosConexion();
        foreach ($listadatos as $key => $value){
            $this->server = $value['server'] ;
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }

        $this->conexion = new mysqli($this->server,$this->user,$this->password,$this->database,$this->port);
        if($this->conexion->connect_errno)
        {
            echo "algo va mal con la conexion";
            die();
        }
    }

    private function datosConexion(){
        
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);

    }

    private function convertirUTF8($array){

        array_walk_recursive($array,function(&$item,$key){
            if(!mb_detect_encoding($item,'utf-8',true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    public function obtenerDatos($sqlstr){

        $results = $this->conexion->query($sqlstr);
        $resultArray = array();
        foreach ($results as $key) {
            $resultArray[] = $key;
        }
        return $this->convertirUTF8($resultArray);
    }
    
    public function multiQuery($sqlstr){

        $this->conexion->multi_query($sqlstr);
        do{
            if($result = $this->conexion->store_result())
            {
                 return $result->fetch_all(MYSQLI_ASSOC);               
            }
        }while($this->conexion->more_results() && $this->conexion->next_result());
    
      return $result;
    }


    public function nonQuery($sqlstr){
        $results = $this->conexion->query($sqlstr);
        return $this->conexion->affected_rows;
    }
    //Insert devuelve el ultimo id de la fila que insertamos
    public function nonQueryId($sqlstr){
        
        
        $results = $this->conexion->query($sqlstr);
          $filas =  $this->conexion->affected_rows;
        if($filas>=1){      
            return $this->conexion->insert_id;
        }else{

            return 0;
        }
    }
    protected function encriptar($str){

        //return md5($str);
        return password_hash($str, PASSWORD_BCRYPT);
    }
}
?>