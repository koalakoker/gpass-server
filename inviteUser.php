<?php
include_once "passDB_cript.php";
include_once "criptoFunc.php";
include_once "getVars.php";
include_once "debugLog.php";
include_once "sendEmail.php";
include_once "operation.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

class InviteUser
{
  private $dbg;

  private $decryptPass;
  private $userid;
  private $level;

  private $invitedUserId;

  private $user_name;
  private $email;

  private $user_password;
  private $return_url;

  public function __construct() {}

  private function getSessionParameters() {
    $this->dbg->log("Get session parameters");

    if (($this->decryptPass = getDecryptPass()) == "") {
      $this->dbg->log("Missing decrypt key!");
      $this->answer(false);
    }

    if (($this->userid = getUserId()) == NULL) {
      $this->dbg->log("User id required!");
      $this->answer(false);
    }

    $this->level = getLevel();

    $this->dbg->log("Master password = " . $this->decryptPass);
    $this->dbg->log("User Id = " . $this->userid);
    $this->dbg->log("Level = " . $this->level);
  }

  private function checkAdminRights() {
    $this->dbg->log("Check admin right");
    $opetation = new Operation($this->dbg);
    $opetation->operation = "EMAIL";
    $opetation->level = $this->level;
    if (!$opetation->checkAdminRightForOperation()) {
      $this->answer(false);
    };
  }

  private function getParametersFromURLWithoutEncription() {
    $this->dbg->log("Get parameters from URL without encription");
    if (!isset($_GET['invitedUserId'])) {
      $this->dbg->log("Missing invited user id!");
      $this->answer(false);
    }

    $this->invitedUserId = $_GET['invitedUserId'];
    $this->dbg->log("Invited user Id:" . $this->invitedUserId);
  }

  private function getInvitedUserDataFromDB() {
    $this->dbg->log("Access db to get info about invited user");

    $dataBaseAccess = DataBaseAccess::getInstance();

    // Globals defined for legacy
    $Server = $dataBaseAccess->getCryptDBServer();
    $Username = $dataBaseAccess->getCryptDBUserName();
    $PW = $dataBaseAccess->getCryptDBPassword();
    $DB = $dataBaseAccess->getCryptDBName();

    $Server   = deChipher($Server,   $this->decryptPass);
    $Username = deChipher($Username, $this->decryptPass);
    $PW       = deChipher($PW,       $this->decryptPass);
    $DB       = deChipher($DB,       $this->decryptPass);

    if ($Server == "") {
      session_unset();
      session_destroy();
      $this->dbg->log("Wrong decrypt key. Access denied!");
      $this->answer(false);
    }

    // connect to the mysql database
    $link = mysqli_connect($Server, $Username, $PW, $DB);
    mysqli_set_charset($link, 'utf8');

    // SQL statement
    $sql = "SELECT * FROM `users` WHERE `id` = " . $this->invitedUserId;

    // excecute SQL statement
    $result = mysqli_query($link, $sql);

    // Close session if SQL statement failed
    if (!$result) {
      session_unset();
      session_destroy();
      $this->dbg->log("MySQL error");
      $this->answer(false);
    }

    $userInfo = mysqli_fetch_object($result);

    if ($userInfo == null) {
      session_unset();
      session_destroy();
      $this->dbg->log("User info is null");
      $this->answer(false);
    }

    $this->user_name = $userInfo->username;
    $this->email =  $userInfo->email;

    $this->dbg->log("*** From DB ***");
    $this->dbg->log("UserName = "     . $this->user_name);
    $this->dbg->log("Email = " . $this->email);

  }

  private function getEncryptedParameters() {
    $this->dbg->log("Get info from url params and decode them");

    $user_password = $_GET['user_password'];
    $return_url = $_GET["return_url"];

    $this->dbg->log("*** Received ***");
    $this->dbg->log("UserPassword = " . $user_password);
    $this->dbg->log("ReturnUrl = " . $return_url);

    $inputList = array(
      'user_password' => $user_password,
      'return_url' => $return_url
    );
    $outputList = passDecrypt($inputList, true);

    $this->dbg->log("*** Decoded ***");
    $this->dbg->log("UserPassword = " . $outputList['user_password']);
    $this->dbg->log("ReturnUrl = " . $outputList['return_url']);

    $this->user_password = $outputList['user_password'];
    $this->return_url = $outputList['return_url'];
  }

  private function sendEmail() {
    $this->dbg->log("Send email");

    $userEmail = $this->email;
    $userName = $this->user_name;
    $userPassword = $this->user_password;
    $masterPassword = $this->decryptPass;
    $returnUrl = $this->return_url;

    sendEmail($userEmail, $userName, $userPassword, $masterPassword, $returnUrl);
  }

  private function answer($done)
  {
    if ($done) {
      $answer = '{ "done" : true }';
    } else {
      $answer = '{ "done" : false }';
    }
    echo ($answer);

    $this->dbg->log("Answer = " . $answer);
    $this->dbg->close();
    die();
  }

  public function execute() {
    $this->dbg = new DebugLog("../log/email.txt", "a");

    $this->getSessionParameters();
    $this->checkAdminRights();
    $this->getParametersFromURLWithoutEncription();
    $this->getInvitedUserDataFromDB();
    $this->getEncryptedParameters();
    $this->sendEmail();
    $this->answer(true);
    
  }
  
}

$inviteUser = new InviteUser();
$inviteUser->execute();

?>