<?php
include_once("middleware.php");

/**
  *Name : Routes
  *Type : Class extends Middleware
  *Description : Gestionaire des fichier routes et chanel's callback
  *Use-case : Middleware de rapido
  *Sample : rapido -> use ( Routes::class , [options] );
*/
class Routes extends Middleware{
  private $__path;
  private $__fileroutes;

  function __construct($options){
    $this -> __path = array_keys($options)[0];
    $this -> __fileroutes = $options[array_keys($options)[0]];
    $this -> __verifyIntegrity();
    $this -> __include();
  }

  /**
    *Description :
  */
  private function __isFolderRouteExist(){
    return (is_dir($this -> __path) ? true : false);
  }

  /**
    *Description :
  */
  private function __isAllFileExist(){
    $result = array();
    foreach ($this -> __fileroutes as $key => $path){
      if(!file_exists($this -> __path."/route.".$path.".php"))array_push($result,false);
    }
    return (in_array(false, $result) ? false : true);
  }

  /**
    *Description :
  */
  private function __listeRoutesFiles(){
    return scandir($this -> __path);
  }

  /**
    *Description :
  */
  private function __verifyIntegrity(){
    $result = array();
    if($this -> __isFolderRouteExist() == false)array_push($result,$this -> __createMissingDirectory());
    if($this -> __isAllFileExist() == false)array_push($result,$this -> __createMissingFiles());
    return (in_array(false, $result) == true ? $this -> __verifyIntegrity() : true);

  }

  /**
    *Description :
  */
  private function __createMissingDirectory(){
    mkdir($this -> __path, 0777);
    return false;
  }

  /**
    *Description :
  */
  private function __createMissingFiles(){

    $template = "<?php\n\$Router = \$GLOBALS['App'];\n\n?>";

    foreach ($this -> __fileroutes as $key => $path){
      if(!file_exists($this -> __path."/route.".$path.".php")){
        $file = fopen($this -> __path."/route.".$path.".php", "w") or die("Unable to open file!");
        fwrite($file, $template);
        fclose($file);
      }
    }
    return false;
  }

  /**
    *Description :
  */
  private function __include(){
    foreach ($this -> __fileroutes as $key => $path){
      include $this -> __path."/route.".$path.".php";
    }
  }

}
?>
