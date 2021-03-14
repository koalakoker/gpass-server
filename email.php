<?php

include_once "config.php";
include_once "passDB_cript.php";
include_once "criptoFunc.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (isConfigForTesting()) {
  $logFile = fopen("../log/email.txt", "a");
  $emailFile = fopen("../log/email.html", "w");
}

if ($logFile) {
  fwrite($logFile, "-----------------------------------------------------------------------------\n");
  fwrite($logFile, date("Y-m-d H:i:s") . "\n");
}

if (!isset($_GET["chipher_password"]))
{
  if ($logFile) {
    fwrite($logFile, "Missing chipher_password!" . "\n");
    fclose($logFile);
  }
  die('{
    "error": "Missing chipher_password!",
    "logged"      : false
}');
}

if (!isset($_GET["user_name"]))
{
  if ($logFile) {
    fwrite($logFile, "Missing user_name!" . "\n");
    fclose($logFile);
  }
  die('{
    "error": "Missing user_name!",
    "logged"      : false
}');
}

if (!isset($_GET["user_password"]))
{
  if ($logFile) {
    fwrite($logFile, "Missing user_password!" . "\n");
    fclose($logFile);
  }
  die('{
    "error": "Missing user_password!",
    "logged"      : false
}');
}

$chipher_password = $_GET["chipher_password"];
$user_name = $_GET['user_name'];
$user_password = $_GET['user_password'];
$email = $_GET['email'];

if ($logFile) {
  fwrite($logFile, "*** Received ***\n");
  fwrite($logFile, "UserName = "     . $user_name . "\n");
  fwrite($logFile, "UserPassword = " . $user_password . "\n");
  fwrite($logFile, "Chipher Password = " . $chipher_password . "\n");
  fwrite($logFile, "Email = " . $email . "\n");
}

$inputList = array(
  'chipher_password' => $chipher_password,
  'user_name' => $user_name,
  'user_password' => $user_password,
  'email' => $email );
$outputList = passDecrypt($inputList, true);

if ($logFile) {
  fwrite($logFile, "*** Decoded ***\n");
  fwrite($logFile, "UserName = "     . $outputList['user_name'] . "\n");
  fwrite($logFile, "UserPassword = " . $outputList['user_password'] . "\n");
  fwrite($logFile, "Chipher Password = " . $outputList['chipher_password'] . "\n");
  fwrite($logFile, "Email = " . $outputList['email'] . "\n");
}

$answer = '{
  "txt"         : "Login done.",
  "logged"      : true
}'; 
echo($answer);

$to = $outputList['email'];
$subject = 'Invitation to GPass service';

$message = 
'<html>
  <head>
    <title>Invitation to GPass service</title>
  </head>
  <body>
    <div>
      <h1><span style="color: #003366;">Invitation to GPass service</span></h1>
    </div>
    <p>&nbsp;</p>
    <p>Dear <span style="color: #800000;">' . $outputList['user_name'] . '</span> &lt;' . $to . '&gt;</p>
    <p>GPass admins invite you to join to the service. Click the following link you will admit to use GPass.</p>
    <p style="text-align: center;"><a href="' . $_GET["returnurl"] . '/' . $user_name . '/' . $user_password . '/' . $chipher_password .'">Click on this link</a></p>
    <p>Note that this link will expire in 30 days.</p>
  </body>
</html> 
';

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
$headers[] = 'To: '. $outputList['user_name'] . ' <' . $to . '>';
$headers[] = 'From: GPass Admin <koala@koalakoker.com>';

// Mail it
mail($to, $subject, $message, implode("\r\n", $headers));

if ($emailFile) {
  fwrite($emailFile, $message);
  fclose($emailFile);
}

if ($logFile) {
  fwrite($logFile, "Answer = " . $answer . "\n");
  fwrite($logFile, "#############################################################################\n");
  fclose($logFile);
}

?>