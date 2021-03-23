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

function noAnswer($dbg) {
  $answer ='{}';
  echo ($answer);
  $dbg->log("answer: " . $answer);
  $dbg->close();
  die();
}

session_start();

$dbg = new DebugLog("../log/api.txt", "a");

if (($decryptPass = getDecryptPass()) == "") {
  $dbg->log("Missing decrypt key!");
  noAnswer($dbg);
};

$level = getLevel();
$allUsers = -1;

if (isset($_GET["fromuser"])) {
  if (($userid = getUserId()) == NULL) {
    $dbg->log("User id required!");
    noAnswer($dbg);
  }
} else {
  $userid = $allUsers;
}

$Server   = deChipher($Server,  $decryptPass);
$Username = deChipher($Username,$decryptPass);
$PW       = deChipher($PW,      $decryptPass);
$DB       = deChipher($DB,      $decryptPass);

if ($Server == "")
{
  session_unset();
  session_destroy();
  $dbg->log("Wrong decrypt key. Access denied!");
  noAnswer($dbg);
}

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);
 
// connect to the mysql database
$link = mysqli_connect($Server, $Username, $PW, $DB);
mysqli_set_charset($link,'utf8');
 
// retrieve the table and key from the path
$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$key = array_shift($request)+0;

// build the SET part of the SQL command
$set = '';
if ($input)
{
  // escape the columns and values from the input object
  $columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
  $values = array_map(function ($value) use ($link) {
  if ($value===null) return null;
  return mysqli_real_escape_string($link,(string)$value);
  },array_values($input));

  for ($i=0;$i<count($columns);$i++) {
    $set.=($i>0?',':'').'`'.$columns[$i].'`=';
    $set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
  }
}

$operation = new Operation($dbg);
$operation->operation = $method;
$operation->table = $table;
$operation->input = $input;
$operation->userid = $userid;
$operation->allusers = $allusers;
$operation->level = $level;

if (!$operation->checkAdminRightForOperation()) { 
  noAnswer($dbg);
};
 
// create SQL based on HTTP method
switch ($method) {
  case 'GET':
    if ($userid==$allUsers) {
      $sql = "select * from `$table`".( $key ? " WHERE id=$key" : '');
    } else {
      $sql = "select * from `$table` WHERE userid=" . $userid . ( $key ? " AND id=$key" : '');
    }
    break;
  case 'PUT':
    $sql = "update `$table` set $set where id=$key";
    break;
  case 'POST':
    $sql = "insert into `$table` set $set"; 
    break;
  case 'DELETE':
    $sql = "delete from `$table` where id=$key"; 
    break;
  default:
    break;
}
 
if ($sql) {

  $dbg->log("sql: " . $sql);
  
  // excecute SQL statement
  $result = mysqli_query($link,$sql);

  // Close session if SQL statement failed
  if (!$result) {
    session_unset();
    session_destroy();
    $dbg->log("MySQL error");
    noAnswer($dbg);
  }
  
  // log results, insert id or affected row count
  $answer = "";

  if ($method == 'GET') {
    if (!$key) {
      $answer .= '[';
    }
    for ($i = 0; $i < mysqli_num_rows($result); $i++) {
      $answer .= ($i > 0 ? ',' : '');
      $answer .= json_encode(mysqli_fetch_object($result));
    }
    if (!$key) {
      $answer .= ']';
    }
  } elseif ($method == 'POST') {
    $answer .= mysqli_insert_id($link);
  } else {
    $answer .= mysqli_affected_rows($link);
  }
  echo($answer);

  $dbg->log("answer: ". $answer); 
  
  // close mysql connection
  mysqli_close($link);
} else {
  noAnswer($dbg);
}

$dbg->close();

?>