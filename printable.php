<?php

include_once "config.php";

class Printable
{
  private $logFile;

  public function __construct($fileName, $accessMode){
    if (isLogEnabled()) {
      $this->logFile = fopen($fileName, $accessMode);
    } else {
      $this->logFile = null;
    }
  }

  public function log($message) {
    if ($this->logFile) {
      fwrite($this->logFile, $message . "\n");
    }
  }

  public function logArray($array) {
    $this->log("Array:");
    foreach ($array as $key => $value) {
      $this->log($key . "->" . $value);
    }
  }

  public function close() {
    if ($this->logFile) {
      fclose($this->logFile);
    }
  }
  
}

?>