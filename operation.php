<?php

class Operation
{
  public $operation; // "GET", "POST", "DELETE", "PUT"
  public $operationOnId;
  public $table;
  public $input;
  public $sessionUserid;
  public $sessionLevel;
  private $dbg;
  private $minLevel;

  private $userLevel = 0;
  private $adminLevel = 1;
  private $forbiddenLevel = 2;
  
  public function __construct($dbg){
    $this->dbg = $dbg;
    $this->operation = "";
    $this->table = "";
    $this->sessionLevel = $this->userLevel;
    $this->minLevel = $this->userLevel;
  }

  private function increaseRightLevelTo($newMinLevel) {
    if ($newMinLevel > $this->minLevel) {
      $this->minLevel = $newMinLevel;
    }
  }

  private function checkTableUsersAccess() {
    if ($this->table == "users") {
      // Get full list ?, addnew and delete requires admin
      if (($this->operation == "POST") ||
        ($this->operation == "DELETE")
      ) {
        $this->increaseRightLevelTo($this->adminLevel);
      }

      if ($this->operation == "GET") {
        if ($this->operationOnId != $this->sessionUserid) {
          // Operation on different user id requires admin
          $this->increaseRightLevelTo($this->adminLevel);
          $this->dbg->log("  Operation on id that is not session id");
        }
      }

      if ($this->operation == "PUT") {
        if (isset($this->input["level"])) {
          // Change the level requires same right
          $this->increaseRightLevelTo($this->input["level"]);
          $this->dbg->log("  Change level");
        }
        if ($this->operationOnId != $this->sessionUserid) {
          // Operation on different user id requires admin
          $this->increaseRightLevelTo($this->adminLevel);
          $this->dbg->log("  Operation on id that is not session id");
        }
        if (isset($this->input["id"])) {
          if ($this->operationOnId != $this->input["id"]) {
            // Operation on different user id is forbidden
            $this->increaseRightLevelTo($this->forbiddenLevel);
            $this->dbg->log("  Operation on different id");
          }
        }
        if (isset($this->input["username"])) {
          // Protect parameters that can't be modified
          $this->increaseRightLevelTo($this->forbiddenLevel);
          $this->dbg->log("  Change user name is forbidden!");
        }
      }
    }
  }

  private function checkInviteUserForOperation() {
    if ($this->operation == "EMAIL") {
      $this->minLevel = $this->adminLevel;
    }
  }
  
  public function checkAdminRightForOperation() {
    $this->dbg->log("  Check operation");
    
    $this->minLevel = $this->userLevel;
    $this->checkTableUsersAccess();
    $this->checkInviteUserForOperation();
    
    $this->dbg->log("  User level:"   . $this->sessionLevel);
    $this->dbg->log("  Rights level:" . $this->minLevel);

    if ($this->sessionLevel >= $this->minLevel) 
    {
      $this->dbg->log("  User has rights for the operation");
      return true;
    } else {
      $this->dbg->log("  User doesn't has rights for the operation");
      return false;
    }
  }

}

?>