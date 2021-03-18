<?php

class Operation
{
  public $method;
  public $table;
  public $userid;
  public $allUsers;
  public $level;
  public function __construct(){}
  
  public function checkAdminRightForOperation() {
    if (($this->table == "users") && 
        (($this->method=="GET")  || 
         ($this->method=="POST") ||
         ($this->method=="DELETE"))) {
      $minLevel = 1;
    }
    
    $haveRight = false;
    if ($this->level >= $minLevel) 
    {
      $haveRight = true;
    }
    return $haveRight;
  }
}

?>