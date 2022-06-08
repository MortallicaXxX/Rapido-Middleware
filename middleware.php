<?php

/**
  *Name : Router
  *Type : Class
  *Description :
  *Use-case :
  *Sample :
*/
if(!class_exists('Middleware')){
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
}

?>
