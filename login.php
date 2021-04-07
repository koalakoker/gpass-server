<?php
include_once "passDB_cript.php";
include_once "criptoFunc.php";
include_once "debugLog.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();

$errorCodeMissingParams     = "1";
$errorCodeWrongMasterKey    = "2";
$errorCodeWrongUsername     = "3";
$errorCodeWrongUserPassword = "4";
$errorCodeSessionDestroyed  = "5";

$dbg = new DebugLog("../log/login.txt", "a");

if (isset($_GET["logout"]))
{
  session_unset();
  session_destroy();
  $dbg->log("Session destroyed");
  $dbg->close();
  die ('{ "error": "Session destroyed",
          "errorCode": ' . $errorCodeSessionDestroyed  . ' }');
}

$chipher_hash = "";
if (isset($_GET["chipher_hash"])) {
  $chipher_hash = $_GET["chipher_hash"];
}
$chipher_password = "";
 if (isset($_GET["chipher_password"])) {
  $chipher_password = $_GET["chipher_password"];
}

if (($chipher_password == "") && ($chipher_hash == "")) {
  $dbg->log("Missing chipher_password!");
  $dbg->close();
  die('{
    "error": "Missing chipher_password!",
    "errorCode" : ' . $errorCodeMissingParams . ',
    "logged"      : false
  }');
}

if (isset($_GET["user_name"])) {
  $user_name = $_GET['user_name'];
} else {
  $dbg->log("Missing user_name!");
  $dbg->close();
  die('{
    "error": "Missing user_name!",
    "errorCode" : ' . $errorCodeMissingParams . ',
    "logged"      : false
}');
}

if (isset($_GET["user_hash"])) {
  $user_hash = $_GET['user_hash'];
} else {
  $dbg->log("Missing user_hash!");
  $dbg->close();
  die('{
    "error": "Missing user_hash!",
    "errorCode" : ' . $errorCodeMissingParams . ',
    "logged"      : false
}');
}

if (isset($_SESSION['decryptPass'])) {
  $prevSession = '"prevSession" : "' . $_SESSION["decryptPass"] . '",';
}
else {
  $prevSession ='';
}

if (isset($_SESSION['userName'])) {
  $prevSessionUserName = '"prevSessionUserName" : "' . $_SESSION["userName"] . '",';
}
else {
  $prevSessionUserName ='';
}

if (isset($_SESSION['userHash'])) {
  $prevSessionUserHash = '"prevSessionUserHash" : "' . $_SESSION["userHash"] . '",';
}
else {
  $prevSessionUserHash ='';
}

$dbg->log("*** Received ***");
$dbg->log("UserName = "         . $user_name);
$dbg->log("UserHash = "         . $user_hash);
$dbg->log("chipher_password = " . $chipher_password);
$dbg->log("chipher_hash = "     . $chipher_hash);

$inputList = array(
  'chipher_password' => $chipher_password,
  'chipher_hash'     => $chipher_hash,
  'user_name'        => $user_name,
  'user_hash'        => $user_hash);
$outputList = passDecrypt($inputList, false);

$chipher_password = $outputList['chipher_password'];
$chipher_hash = $outputList['chipher_hash'];
$user_name   = $outputList['user_name'];
$user_hash   = $outputList['user_hash'];

$dbg->log("*** Decoded ***");
$dbg->log("chipher_password = " . $chipher_password);
$dbg->log("chipher_hash = "     . $chipher_hash);
$dbg->log("user_name = "        . $user_name);
$dbg->log("user_hash = "        . $user_hash);

if ($chipher_password != '') {
  $decryptPass = hashPass($chipher_password);
} else {
  $decryptPass = $chipher_hash;
}

$_SESSION['decryptPass'] = $decryptPass;
$_SESSION['userName'] = $user_name;
$_SESSION['userHash'] = $user_hash;

$dbg->log("*** Master Pass ***");
$dbg->log($decryptPass);

$Server   = deChipher($Server,  $decryptPass);
$Username = deChipher($Username,$decryptPass);
$PW       = deChipher($PW,      $decryptPass);
$DB       = deChipher($DB,      $decryptPass);

if ($Server == "")
{
  session_unset();
  session_destroy();
  $dbg->log("Wrong decrypt key. Access denied!");
  $dbg->close();
  die('{
      "error"  : "Wrong decrypt key. Access denied!",
      "errorCode" : ' . $errorCodeWrongMasterKey . ' ,
      "logged" : false
    }');
}

// connect to the mysql database
$link = mysqli_connect($Server, $Username, $PW, $DB);
mysqli_set_charset($link,'utf8');

// Create SQL statement
$sql = "SELECT * FROM `users` WHERE `username`='" . $user_name ."'";

// excecute SQL statement
$result = mysqli_query($link,$sql);

$obj = mysqli_fetch_object($result);

if ($obj == null) {
  session_unset();
  session_destroy();
  $dbg->log("Username not present in database");
  $dbg->close();
  die('{
      "error"  : "Username not present in database",
      "errorCode" : ' . $errorCodeWrongUsername . ',
      "logged" : false
    }');
}

// Check user hash
if ($user_hash != $obj->userhash) {
  session_unset();
  session_destroy();
  $dbg->log("Wrong password. Access denied!");
  $dbg->close();
  die('{
      "error"  : "Wrong password. Access denied!",
      "errorCode" : ' . $errorCodeWrongUserPassword . ',
      "logged" : false
    }');
}

$_SESSION['userid'] = $obj->id;
$_SESSION['level'] = $obj->level;

$answer = '{' .
  $prevSession . '
  ' . $prevSessionUserName . '
  ' . $prevSessionUserPassword . '  
  "txt"         : "Login done.",
  "logged"      : true,
  "encrypted"   : "' . $_SESSION["decryptPass"] . '",
  "userName"    : "' . $_SESSION["userName"] . '",
  "userPassword": "' . $_SESSION["userPass"] . '",
  "userid": '        . $_SESSION['userid'] . ',
  "level": '         . $_SESSION['level'] .' 
}'; 
echo($answer);

$dbg->log("Answer = " . $answer);
$dbg->close();

?>