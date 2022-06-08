<?php
include_once("middleware.php");
include_once("Datastorage/index.js")

/**
  *Name : Router
  *Type : Class
  *Description :
  *Use-case :
  *Sample :
*/
class Sessions extends Middleware{

  private $sessions_db;
  private $session_db_user;
  private $sessions_path;

  function __construct($sessions_path){
    $this -> __start_session();
    $this -> sessions_path = $sessions_path;
    $this -> sessions_db = new \Datastorage\DB($sessions_path);
  }

  public function Program($routeur){

    $_SESSION["_EventTime"] = time();

    $routeur["addKeySession"] = function($key,$value){
      $_SESSION[$key] = $value;
      $this -> __copy_session_var();
    };

    $routeur["deleteKeySession"] = function($dataToDelete){
      return ($this -> sessions_db -> collection(session_id())) -> delete(array("session_id" => session_id()),$dataToDelete,function($error,$result,$client){
        $client -> save_file_integrity();
        $this -> __retrieve_session();
      });
    };

    $routeur["getSession"] = ((new \Datastorage\DB("./sessions")) -> collection(session_id())) -> find(array("session_id" => session_id()));

    $routeur["mergeSession"] = function($filter){
      $session = (((new \Datastorage\DB("./sessions")) -> collection(session_id())) -> find(array("session_id" => session_id())))[0];
      $collections = (new \Datastorage\DB("./sessions")) -> collection_list();

      function compare($src1,$src2,$filter){
        $c = array();
        foreach ($filter as $key => $value) {
          if(is_array($value) && in_array($key , array_keys(get_object_vars($src1))) && in_array($key , array_keys(get_object_vars($src2))))array_push($c,compare($src1 -> {$key},$src2 -> {$key},$filter[$key]));
          else if($src1 -> {$value} == $src2 -> {$value})array_push($c,true);
          else array_push($c,false);
        }
        return !in_array(false,$c);
      }

      function merge($old,$_sess_var){
        foreach ($old as $key => $value) {
          if ($key != "session_id" && $key != "_id" && $key != "_EventTime" ){
            if(is_object($value) && in_array($key,array_keys($_sess_var)) == false)$_sess_var[$key] = array();
            if(is_object($value))$_sess_var[$key] = merge($value,$_sess_var[$key]);
            else {
              $_sess_var[$key] = $value;
            }
          }
        }
        return $_sess_var;
      }

      foreach ($collections as $key => $collection) {
        if($collection != $session -> session_id){
          $s = (((new \Datastorage\DB("./sessions")) -> collection($collection)) -> find(array()))[0];
          if(compare($session,$s,$filter) == true){
            unlink("./sessions/".$s -> session_id.".store");
            $_SESSION = merge($s,$_SESSION);
          }
        }
      }

      $this -> __copy_session_var();

    };

    $this -> session_db_user = $this -> sessions_db -> collection(session_id());

    $this -> session_db_user -> find(array("session_id" => session_id()) , function($error,$result,$collection){
      if($result !== null && count($result) == 0)$collection -> insert(array(
        "session_id" => session_id()
      ),function($error,$result,$collection){
        $collection -> save_file_integrity();
      });
    });

    $this -> __copy_session_var();

    return $routeur;
  }

  private function __copy_session_var(){
    $this -> session_db_user -> update(array("session_id" => session_id()),$_SESSION,function($error,$result,$collection){
      $collection -> save_file_integrity();
    });
  }

  private function __clear_session(){
    session_destroy();
  }

  /**
  * Description : Recupere la session depuis le .store
  */
  private function __retrieve_session(){
    $this -> __clear_session();
    $this -> session_db_user -> find(array("session_id" => session_id()),function($error,$result,$collection){
      if($result)foreach ($result[0] as $key => $value) {
        $_SESSION[$key] = $value;
      }
    });
  }

  /**
    *Description :
  */
  private function __start_session(){
    session_start();
  }

  /**
    *Description :
  */
  private function __destroy_session(){

  }

}
?>
