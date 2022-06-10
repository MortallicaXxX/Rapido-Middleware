<?php
include_once("middleware.php");

/**
  *Name : Router
  *Type : Class
  *Description :
  *Use-case :
  *Sample :
*/
class BodyParser extends Middleware{

  public function Program($routeur){
    if($routeur["REQUEST_METHOD"] == "POST" || $routeur["REQUEST_METHOD"] == "PUT" || $routeur["REQUEST_METHOD"] == "PATCH" || $routeur["REQUEST_METHOD"] == "DELETE"){
      $body = $this -> __body();
      $routeur["body"] = $body;
    }
    return $routeur;
  }

  /**
    *Description :
  */
  private function __body(){
    $data = json_decode(file_get_contents('php://input'), true);
    if(is_null($data))$data = array();
    return (array_key_exists("body",$data) ? $data["body"] : $data);
  }

}
?>
