<?php
include_once("middleware.php");

class Layout extends Middleware{

  private $__layout_path;
  private $ERROR = array(
    "NOT_A_FILE" => "Aucun layout portant le nom demandé.",
    "ARRAY_VALIDITY" => "La table de donnée ne semble pas valide pour le layout."
  );

  function __construct($layout_path){
    $this -> __layout_path = $layout_path;
    $this -> __verifyIntegrity();
  }

  function Program($routeur){

    $routeur["layout"] = function($fileName,$blockName,$variables = null){
      if($this -> __is_layout($fileName) == true){
        echo $this -> __load_layout($fileName,$blockName,$variables);
      }
      else new Error($this -> ERROR["NOT_A_FILE"]);
    };

    return $routeur;
  }

  /**
    *Description : fileName est-il un layout ? true : false.
  */
  private function __is_layout($fileName){
    return (in_array($fileName,$this -> __list_layout()) ? true : false);
  }

  /**
    *Description : retourne un tableau des fichiers .layout dans /layout
  */
  private function __list_layout(){

    $result = array();
    foreach (scandir($this -> __layout_path) as $filePath) {
      $path_info = pathinfo($filePath);
      if($path_info['extension'] == "layout")array_push($result,$path_info['filename']);
    }
    return $result;
  }

  /**
    *Description : retourne un string représentant le contenu du fichier .layout
  */
  private function __load_layout($fileName,$blockName,$variables){
    return $this -> __make_layout_block((new \Tools\FileSystem()) -> read_file($this -> __layout_path."/".$fileName.".layout"),$blockName,$variables);
  }

  /**
    *Description : Conversion du fichier .layout en tableau ayant identifier chaque block et leurs titre.
    *$result {array} contient le résultat de la conversion du fichier en un array ayant identifier chaque block et leurs titre.
    *$blockName {string} représente le nom du block souaité.
    *$variables {array} contient les variables à injecter dans le layout.
  */
  private function __make_layout_block($layout,$blockName,$variables){

    $result = array();
    $block_title = "";
    $data_block = array();

    foreach(explode("\n",$layout) as $line){
      $line = join("",preg_split('/\h{2,}/',$line)); // suppression des espaces
      if(strlen($line) > 0 && $line[0] == "#"){
        if(count($data_block) > 0){
          $result[$block_title] = $data_block;
          $block_title = "";
          $data_block = array();
        }
        $block_title = trim(join("",explode("#",$line)));
      }
      else if($line != "")array_push($data_block,$line);

    }
    $result[$block_title] = $data_block;
    $block_title = "";
    $data_block = array();
    return $this -> __merge_layout_block($result,$blockName,$variables);

  }

  /**
    *Description : Permet de modifier une partie de chaine de char dans un string par une valeur.
    *$line {string} ligne contenant la chaine de char à modifier.
    *$str_start {int} position de début de char.
    *$str_end {int} position de fin de char.
    *$value {string} varibale devant modifier la chaine de char.
  */
  private function __modify_string_range($line,$str_start,$str_end,$value){
    $new_line = "";
    $value_is_insert = false;
    for($i = 0 ; $i < strlen($line) ; $i++){
      if(($i > $str_start && $i < $str_end) == false)$new_line .= $line[$i];
      else if ($value_is_insert == false){
        $new_line .= "{$value}";
        $value_is_insert = true;
      }
    }
    return $new_line;
  }

  /**
    *Description : Résous les injection de variable dans une ligne.
    *$line {string} Chaine de string à modifier si contient des variables.
    *$variables {array} contient les variables à injecter dans la ligne.
  */
  private function __add_variables_in_line($line,$variables){
    if(strpos($line, "{@") !== false){
      $start = strpos($line, "{"); // début de la première variable détectée
      $end = strpos($line, "}"); // fin de la première variable détectée
      $value_key = trim(join("",explode("@",join("",array_slice(str_split($line, 1), $start +1 , ($end - $start) - 1))))); // extraction du nom de variable
      if(key_exists($value_key,$variables) == true)$line = $this -> __modify_string_range($line,$start-1,$end+1,$variables[$value_key]); // modification de la ligne
      else return $line;
    }
    if(strpos($line, "{@") !== false)return $this -> __add_variables_in_line($line,$variables); // si une autre variable est détectée après modification
    else return $line; // sinon retour de la ligne
  }

  /**
    *Description : Permet de savoir si les variables injectée sont sous forme de tableau<liste<T>> ou de liste<T>
    *$variables {array<list<T>> || list<T>} contient les variables à injecter dans le layout.
    *$variables {array} contient les variables à injecter dans le layout.
    *$return {bool|null} True si tableau 2D | True si tableau 1D | Null si la donnée ne correspond pas.
  */
  private function __is_valid_array($variables){
    if(is_array($variables) == false)return null;
    $key1 = array_keys($variables)[0];
    if(is_array($variables[$key1]) == false)return false;
    else {
      $key2 = array_keys($variables[$key1])[0];
      if(is_array($variables[$key1][$key2]) == true)return null;
      else return true;
    }
  }

  private function __build_block($result,$variables){
    foreach ($result as $name => $lines) {
      for($i = 0 ; $i < count($lines) ; $i++){
        $line = $lines[$i];
        if($variables)$line = $this -> __add_variables_in_line($lines[$i],$variables);
        $result[$name][$i] = $line;
        if($line[0] == "{"){
          $title = trim(join("",explode("}",join("",explode("{#",$line)))));
          $result[$name][$i] = $result[$title];
        }
      }
    }
    return $result;
  }

  /**
    *Description : Résous les injection de variable et liens entre block.
    *$result {array<list<T>> || list<T>} contient le résultat de la conversion du fichier en un array ayant identifier chaque block et leurs titre.
    *$blockName {string} représente le nom du block souaité.
    *$variables {array} contient les variables à injecter dans le layout.
  */
  private function __merge_layout_block($result,$blockName,$variables){

    if($variables){
      $array_lalidity = $this -> __is_valid_array($variables);
      if($array_lalidity === null)new Error($this -> ERROR["ARRAY_VALIDITY"]);
      else if($array_lalidity == false)$result = $this -> __build_block($result,$variables);
      else if($array_lalidity == true){
          $new_result = array();
          foreach ($variables as $variable) {
            array_push($new_result,$this -> __build_block($result,$variable)[$blockName]);
          }
          $result[$blockName] = $new_result;
      }
    }
    else $result = $this -> __build_block($result,$variables);

    // var_dump($result[$blockName]);

    return $this -> __normalise($result[$blockName]);

  }

  /**
    *Description : Normalise les tableaux et sous tableaux en une chaine de string.
  */
  private function __normalise($layout_block){
    for($i = 0 ; $i < count($layout_block) ; $i++){
      if(is_array($layout_block[$i]) == true)$layout_block[$i] = $this -> __normalise($layout_block[$i]);
    }
    return join("",$layout_block);
  }

  /**
    *Description :
  */
  private function __is_folder_exist(){
    return (is_dir($this -> __layout_path) ? true : false);
  }

  /**
    *Description :
  */
  private function __createMissingDirectory(){
    mkdir($this -> __layout_path, 0777);
  }

  /**
    *Description :
  */
  private function __verifyIntegrity(){
    if($this -> __is_folder_exist() == false){
      $this -> __createMissingDirectory();
      $this -> __verifyIntegrity();
    }
  }

}
?>
