<?php
include_once "passDB_cript.php";
include_once "criptoFunc.php";
include_once "debugLog.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();

$dbg = new DebugLog("../log/login.txt", "a");

if (isset($_GET["logout"]))
{
  session_unset();
  session_destroy();
  $dbg->log("Session destroyed");
  $dbg->close();
  die ('{ "error": "Session destroyed"}');
}

if (!isset($_GET["chipher_password"]))
{
  $dbg->log("Missing chipher_password!");
  $dbg->close();
  die('{
    "error": "Missing chipher_password!",
    "logged"      : false
}');
}

if (!isset($_GET["user_name"]))
{
  $dbg->log("Missing user_name!");
  $dbg->close();
  die('{
    "error": "Missing user_name!",
    "logged"      : false
}');
}

if (!isset($_GET["user_password"]))
{
  $dbg->log("Missing user_password!");
  $dbg->close();
  die('{
    "error": "Missing user_password!",
    "logged"      : false
}');
}

$chipher_password = $_GET["chipher_password"];
$user_name = $_GET['user_name'];
$user_password = $_GET['user_password'];

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

if (isset($_SESSION['userPass'])) {
  $prevSessionUserPass = '"prevSessionUserPass" : "' . $_SESSION["userPass"] . '",';
}
else {
  $prevSessionUserPass ='';
}

$dbg->log("*** Received ***");
$dbg->log("UserName = "     . $user_name);
$dbg->log("UserPassword = " . $user_password);

$inputList = array('chipher_password' => $chipher_password,'user_name' => $user_name,'user_password' => $user_password );
$outputList = passDecrypt($inputList, false);

$decryptPass = $outputList['chipher_password'];
$user_name = $outputList['user_name'];
$user_password = $outputList['user_password'];

$dbg->log("*** Decoded ***");
$dbg->log("UserName = "     . $user_name);
$dbg->log("UserPassword = " . $user_password);

$decryptPass = hashPass($decryptPass);
$user_password = hashPass($user_password);

$dbg->log("*** Hash ***");
$dbg->log("UserPassword = " . $user_password);

$_SESSION['decryptPass'] = $decryptPass;
$_SESSION['userName'] = $user_name;
$_SESSION['userPass'] = $user_password;

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