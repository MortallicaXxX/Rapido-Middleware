<?php

/**
  *Name : Router
  *Type : Class
  *Description :
  *Use-case :
  *Sample :
*/
class Middleware{
  /**
    *Description :
  */
  protected $type = "middleware";
  /**
    *Description :
  */
  public function get_type(){return $this -> type;}
  /**
    *Description :
  */
  public function Program($routeur){return $routeur;}
}

/**
  *Name : Router
  *Type : Class
  *Description :
  *Use-case :
  *Sample :
*/
class Error{

  function __construct($msg){
    var_dump($msg);
  }

}

?>
