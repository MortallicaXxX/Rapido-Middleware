<?php
include_once("middleware.php");

class MyMiddleware extends Middleware{

  function __construct($args){
    $this -> test = $args;
  }

  public function Program($routeur){
    // TOTO :
    return $router;
  }

}
?>
