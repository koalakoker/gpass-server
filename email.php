<?php
include_once "passDB_cript.php";
include_once "criptoFunc.php";
include_once "debugLog.php";
include_once "printable.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$dbg = new DebugLog("../log/email.txt", "a");
$emailPrint = new Printable("../log/email.html", "w");

if (!isset($_GET["chipher_password"]))
{
  $dbg->print("Missing chipher_password!");
  $dbg->close();
  die('{
    "error": "Missing chipher_password!",
    "logged"      : false
}');
}

if (!isset($_GET["user_name"]))
{
  $dbg->print("Missing user_name!");
  $dbg->close();
  die('{
    "error": "Missing user_name!",
    "logged"      : false
}');
}

if (!isset($_GET["user_password"]))
{
  $dbg->print("Missing user_password!");
  $dbg->close();
  die('{
    "error": "Missing user_password!",
    "logged"      : false
}');
}

$chipher_password = $_GET["chipher_password"];
$user_name = $_GET['user_name'];
$user_password = $_GET['user_password'];
$email = $_GET['email'];

$dbg->print("*** Received ***");
$dbg->print("UserName = "     . $user_name);
$dbg->print("UserPassword = " . $user_password);
$dbg->print("Chipher Password = " . $chipher_password);
$dbg->print("Email = " . $email);

$inputList = array(
  'chipher_password' => $chipher_password,
  'user_name' => $user_name,
  'user_password' => $user_password,
  'email' => $email );
$outputList = passDecrypt($inputList, true);

$dbg->print("*** Decoded ***");
$dbg->print("UserName = "     . $outputList['user_name']);
$dbg->print("UserPassword = " . $outputList['user_password']);
$dbg->print("Chipher Password = " . $outputList['chipher_password']);
$dbg->print("Email = " . $outputList['email']);

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

$emailPrint->print($message);
$emailPrint->close();

$dbg->print("Answer = " . $answer);
$dbg->close();

?>