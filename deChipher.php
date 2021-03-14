<?php

include_once "criptoFunc.php";
include_once "passDB_cript.php";

$password = $_POST["chipher_password"];

echo '  $Server =   "' . deChipher($Server  , $password) . '"<br>';
echo '  $Username = "' . deChipher($Username, $password) . '"<br>';
echo '  $PW = "'       . deChipher($PW      , $password) . '"<br>';
echo '  $DB = "'       . deChipher($DB      , $password) . '"<br>';
?>