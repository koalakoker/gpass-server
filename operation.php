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
  private $minLevel;
  
  public function __construct($dbg){
    $this->dbg = $dbg;
    $this->operation = "";
    $this->table = "";
    $this->level = 0;
    $this->minLevel = 1;
  }

  private function checkTableUsersAccess() {
    if ($this->table == "users") {
      // Get full list ?, addnew and delete requires admin
      if (($this->operation == "GET")  ||
        ($this->operation == "POST") ||
        ($this->operation == "DELETE")
      ) {
        $this->minLevel = 1;
      }

      if ($this->operation == "PUT") {
        // Change the level to admin requires admin right
        $this->minLevel = $this->input["level"];
      }
    }
  }

  private function checkInviteUserForOperation() {
    if ($this->operation == "EMAIL") {
      $this->minLevel = 1;
    }
  }
  
  public function checkAdminRightForOperation() {
    $this->dbg->print("  Check operation");
    
    $this->minLevel = 0;
    $this->checkTableUsersAccess();
    $this->checkInviteUserForOperation();
    
    $this->dbg->print("  User level:"   . $this->level);
    $this->dbg->print("  Rights level:" . $this->minLevel);

    if ($this->level >= $this->minLevel) 
    {
      $this->dbg->print("  User has rights for the operation");
      return true;
    } else {
      $this->dbg->print("  User doesn't has rights for the operation");
      return false;
    }
  }

}

?>