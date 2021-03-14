<?php

include_once "criptoFunc.php";

$password = $_POST["chipher_password"];
$password = hashPass($password);

echo '  $Password = "' . $password                              . '";<br>';
echo '  $Server =   "' . chipher($_POST["server"]  , $password) . '";<br>';
echo '  $Username = "' . chipher($_POST["username"], $password) . '";<br>';
echo '  $PW = "'       . chipher($_POST["password"], $password) . '";<br>';
echo '  $DB = "'       . chipher($_POST["database"], $password) . '";<br>';
?>