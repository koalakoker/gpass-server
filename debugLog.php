<?php
include_once "printable.php";

class DebugLog extends Printable
{
  public function __construct($fileName, $accessMode)
  {
    parent::__construct($fileName, $accessMode);
    $this->print("-----------------------------------------------------------------------------");
    $this->print(date("Y-m-d H:i:s"));
  }

  public function close() {
    $this->print("#############################################################################");
    parent::close();
  }
}

?>