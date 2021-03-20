<?php

class Operation
{
  public $operation; // "GET", "POST", "DELETE", "PUT"
  public $table;
  public $input;
  public $userid;
  public $allUsers;
  public $level;
  private $dbg;
  
  public function __construct($dbg){
    $this->dbg = $dbg;
  }
  
  public function checkAdminRightForOperation() {
    $this->dbg->print("  Check operation");
    if ($this->table == "users") {
      // Get full list ?, addnew and delete requires admin
      if (($this->operation == "GET")  || 
          ($this->operation == "POST") ||
          ($this->operation == "DELETE")) {
        $minLevel = 1;
      }
      
      if ($this->operation == "PUT") {
        // Change the level to admin requires admin right
        $minLevel = $this->input["level"];
      }
    }

    $this->dbg->print("  User level:" . $this->level);
    $this->dbg->print("  Rights level:" . $minLevel);

    $haveRight = false;
    if ($this->level >= $minLevel) 
    {
      $haveRight = true;
      $this->dbg->print("  User has rights for the operation");
    } else {
      $this->dbg->print("  User doesn't has rights for the operation");
    }
    return $haveRight;
  }

}

?>