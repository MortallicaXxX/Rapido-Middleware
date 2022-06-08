<?php
include_once("middleware.php");


  class DotEnv extends Middleware{

    private $filePath;

    function __construct($filePath){
      $this -> filePath = $filePath;
    }

    function Program($router){
      if($this -> __is_file_exist() == true){
        $router["dotenv"] = $this -> __load_file();
      }
      return $router;
    }

    private function __is_file_exist(){
      return is_file($this -> filePath);
    }

    private function __load_file(){
      return $this -> __normalize((new \Tools\FileSystem()) -> read_file($this -> filePath));
    }

    private function __normalize($strFile){
      $lines = array_filter(explode("\n",$strFile), function($item){return $item;});
      $result = array();
      for($i = 0 ; $i < count($lines) ; $i++){
        $keyLine = array_keys($lines)[$i];
        $lines[$keyLine] = explode("=",$lines[$keyLine]);
        for($y = 0; $y < count($lines[$keyLine]); $y++) {
          $keyCell = array_keys($lines[$keyLine])[$y];
          $lines[$keyLine][$keyCell] = trim($lines[$keyLine][$keyCell]);
        }
        $result[$lines[$keyLine][0]] = $lines[$keyLine][1];
      }
      return $result;
    }

  }
?>
