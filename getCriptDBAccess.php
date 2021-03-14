<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
    
include_once "passDB_cript.php";
include_once "criptoFunc.php";

$userPassword = $_GET["chipher_password"];

if ($userPassword!=$Password)
{
  die("Access denied");
}

$Server   = deChipher($Server, $userPassword);
$Username = deChipher($Username, $userPassword);
$PW       = deChipher($PW, $userPassword);
$DB       = deChipher($DB, $userPassword);

$arr = array('masterPass' => $userPassword, 'server' => $Server, 'username' => $Username, 'password' => $PW, 'database' => $DB);

echo json_encode($arr);

?>