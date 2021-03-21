<?php
include_once "passDB_cript.php";
include_once "criptoFunc.php";
include_once "getVars.php";
include_once "debugLog.php";
include_once "sendEmail.php";
include_once "operation.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$dbg = new DebugLog("../log/email.txt", "a");

$dbg->print("Get session parameters"); 

if (($decryptPass = getDecryptPass()) == "") {
  $dbg->print("Missing decrypt key!");
  $dbg->close();
  die();
}

if (($userid = getUserId()) == NULL) {
  $dbg->print("User id required!");
  $dbg->close();
  die();
}

$level = getLevel();

$dbg->print("Master password = " . $decryptPass);
$dbg->print("User Id = " . $userid);
$dbg->print("Level = " . $level);

// Check admin right

$dbg->print("Check admin right");
$opetation = new Operation($dbg);
$opetation->operation = "EMAIL";
$opetation->level = $level;
$opetation->checkAdminRightForOperation();

$dbg->print("Get info from URL without encription");
if (!isset($_GET['invitedUserId'])) {
  $dbg->print("Missing invited user id!");
  $dbg->close();
  die();
}

$invitedUserId = $_GET['invitedUserId'];
$dbg->print("Invited user Id:" . $invitedUserId);

$dbg->print("Access db to get info about invited user");

$Server   = deChipher($Server,  $decryptPass);
$Username = deChipher($Username, $decryptPass);
$PW       = deChipher($PW,      $decryptPass);
$DB       = deChipher($DB,      $decryptPass);

if ($Server == "") {
  session_unset();
  session_destroy();
  $dbg->print("Wrong decrypt key. Access denied!");
  $dbg->close();
  die();
}

// connect to the mysql database
$link = mysqli_connect($Server, $Username, $PW, $DB);
mysqli_set_charset($link, 'utf8');

// SQL statement
$sql = "SELECT * FROM `users` WHERE `id` = " . $invitedUserId;

// excecute SQL statement
$result = mysqli_query($link, $sql);

// Close session if SQL statement failed
if (!$result) {
  session_unset();
  session_destroy();
  $dbg->print("MySQL error");
  $dbg->close();
  die();
}

$userInfo = mysqli_fetch_object($result);

if ($userInfo == null) {
  session_unset();
  session_destroy();
  $dbg->print("User info is null");
  $dbg->close();
  die();
}

$user_name = $userInfo->username;
$email =  $userInfo->email;

$dbg->print("*** From DB ***");
$dbg->print("UserName = "     . $user_name);
$dbg->print("Email = " . $email);

$dbg->print("Get info from url params and decode them");

$user_password = $_GET['user_password'];
$return_url = $_GET["return_url"];

$dbg->print("*** Received ***");
$dbg->print("UserPassword = " . $user_password);
$dbg->print("ReturnUrl = " . $return_url);

$inputList = array(
  'user_password' => $user_password,
  'return_url' => $return_url);
$outputList = passDecrypt($inputList, true);

$dbg->print("*** Decoded ***");
$dbg->print("UserPassword = " . $outputList['user_password']);
$dbg->print("ReturnUrl = " . $outputList['return_url']);

$dbg->print("Send email");

$userEmail = $email;
$userName = $user_name;
$userPassword = $outputList['user_password'];
$masterPassword = $decryptPass;
$returnUrl = $outputList['return_url'];

sendEmail($userEmail, $userName, $userPassword, $masterPassword, $returnUrl);

$answer = '{
  "txt"         : "Login done.",
  "logged"      : true
}';
echo ($answer);

$dbg->print("Answer = " . $answer);
$dbg->close();

?>