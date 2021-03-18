<?php

include_once "config.php";

function getLevel() {
  $level = 0; // User
  if (isset($_SESSION["level"])) {
    $level = $_SESSION["level"];
  } elseif (isConfigForTesting()) {
    $level = $_GET["level"];
  }
  return $level;
}

function getDecryptPass() {
  $decryptPass = "";
  if (isset($_SESSION['decryptPass'])) {
    $decryptPass = $_SESSION['decryptPass'];
  } elseif (isConfigForTesting()) {
    $decryptPass = $_GET["chipher_password"];
  } 
  return $decryptPass;
}

function getUserId() {
  if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
  } elseif (isConfigForTesting()) {
    $userid = $_GET["userid"];
  }
  return $userid;
}

?>