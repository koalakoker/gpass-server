<?php
include_once "printable.php";

class DebugLog extends Printable
{
  public function __construct($fileName, $accessMode)
  {
    parent::__construct($fileName, $accessMode);
    $this->log("-----------------------------------------------------------------------------");
    $this->log(date("Y-m-d H:i:s"));
  }

  public function close() {
    $this->log("#############################################################################");
    parent::close();
  }

  public function here() {
    $this->log("here");
  }
}

?>