<?php
include_once "passDB_cript.php";
include_once "criptoFunc.php";
include_once "getVars.php";
include_once "operation.php";
include_once "debugLog.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: x-requested-with, Content-Type, origin, authorization, accept, client-security-token");
header("Content-Type: application/json; charset=UTF-8");

class Api
{
  // Members
  private $dbg;
  private $method;
  private $key;
  private $result;
  private $link;
  
  private function failAnswer() {
    $answer ='{
      "result": "fail"
    }';
    echo ($answer);
    $this->dbg->log("answer: " . $answer);
    $this->dbg->close();
    die();
  }
  
  private function noAnswer() {
    $answer = '{}';
    echo ($answer);
    $this->dbg->log("answer: " . $answer);
    $this->dbg->close();
    die();
  }
  
  private function correctAnswer() {
    // log results, insert id or affected row count
    $answer = "";
    
    if ($this->method == 'GET') {
      if (!$this->key) {
        $answer .= '[';
      }
      for ($i = 0; $i < mysqli_num_rows($this->result); $i++) {
        $answer .= ($i > 0 ? ',' : '');
        $answer .= json_encode(mysqli_fetch_object($this->result));
      }
      if (!$this->key) {
        $answer .= ']';
      }
    } elseif ($this->method == 'POST') {
      $answer .= mysqli_insert_id($this->link);
    } else {
      $answer .= mysqli_affected_rows($this->link);
    }
    echo ($answer);
    
    $this->dbg->log("answer: " . $answer);
    $this->dbg->close();
  }

  public function execute() {
    
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
      $silent = true;
    } else {
      $silent = false;
    }
    
    $this->dbg = new DebugLog("../log/api.txt", "a", $silent);

    if (($decryptPass = getDecryptPass()) == "") {
      $this->dbg->log("Missing decrypt key!");
      $this->failAnswer();
    };
    
    $sessionUserId = getUserId();
    $level = getLevel();
    $allUsers = -1;
    
    if (isset($_GET["fromuser"])) {
      if (($userid = getUserId()) == NULL) {
        $this->dbg->log("User id required!");
        $this->failAnswer();
      }
    } else {
      $userid = $allUsers;
    }

    $dataBaseAccess = DataBaseAccess::getInstance();

    // Globals defined for legacy
    $Server = $dataBaseAccess->getCryptDBServer();
    $Username = $dataBaseAccess->getCryptDBUserName();
    $PW = $dataBaseAccess->getCryptDBPassword();
    $DB = $dataBaseAccess->getCryptDBName();
    
    $Server   = deChipher($Server,  $decryptPass);
    $Username = deChipher($Username,$decryptPass);
    $PW       = deChipher($PW,      $decryptPass);
    $DB       = deChipher($DB,      $decryptPass);
    
    if ($Server == "")
    {
      session_unset();
      session_destroy();
      $this->dbg->log("Wrong decrypt key. Access denied!");
      $this->failAnswer();
    }
    
    $this->dbg->log($_SERVER['REQUEST_METHOD']);
    $this->dbg->log($_SERVER['PATH_INFO']);
    $this->dbg->log(file_get_contents('php://input'));
    
    // get the HTTP method, path and body of the request
    $this->method = $_SERVER['REQUEST_METHOD'];
    $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
    $input = json_decode(file_get_contents('php://input'),true);
    
    // connect to the mysql database
    $this->link = mysqli_connect($Server, $Username, $PW, $DB);
    mysqli_set_charset($this->link,'utf8');
    
    // retrieve the table and key from the path
    $table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
    $this->key = array_shift($request)+0;
    
    // build the SET part of the SQL command
    $set = '';
    if ($input)
    {
      // escape the columns and values from the input object
      $columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
      $link = $this->link;
      $values = array_map(function ($value) use ($link) {
        if ($value===null) return null;
        return mysqli_real_escape_string($link, (string)$value);
      }, array_values($input));
      
      for ($i=0;$i<count($columns);$i++) {
        $set.=($i>0?',':'').'`'.$columns[$i].'`=';
        $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
      }
    }
    
    $operation = new Operation($this->dbg);
    $operation->operation = $this->method;
    $operation->operationOnId = $this->key;
    $operation->table = $table;
    $operation->input = $input;
    $operation->sessionUserid = $sessionUserId;
    $operation->sessionLevel = $level;
    
    if (!$operation->checkAdminRightForOperation()) { 
      $this->failAnswer();
    };
    
    // create SQL based on HTTP method
    switch ($this->method) {
      case 'GET':
        if ($userid==$allUsers) {
          $sql = "select * from `$table`".( $this->key ? " WHERE id=$this->key" : '');
        } else {
          $sql = "select * from `$table` WHERE userid=" . $userid . ( $this->key ? " AND id=$this->key" : '');
        }
      break;
      case 'PUT':
        $sql = "update `$table` set $set where id=$this->key";
      break;
      case 'POST':
        $sql = "insert into `$table` set $set"; 
      break;
      case 'DELETE':
        $sql = "delete from `$table` where id=$this->key"; 
      break;
      case 'OPTIONS':
        $this->noAnswer();
      default:
      break;
    }
              
    if ($sql) {
      
      $this->dbg->log("sql: " . $sql);
      
      // excecute SQL statement
      $this->result = mysqli_query($this->link,$sql);
      
      // Close session if SQL statement failed
      if (!$this->result) {
        session_unset();
        session_destroy();
        $this->dbg->log("MySQL error");
        $this->failAnswer();
      }
      
      $this->correctAnswer();
      
      // close mysql connection
      mysqli_close($this->link);
    } else {
      $this->failAnswer();
    }
              
  }
}

$api = new Api();
$api->execute();

?>