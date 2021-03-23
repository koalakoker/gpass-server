<?php
include_once "printable.php";

function sendEmail($userEmail, $user_name, $userPassword, $masterPassword, $returnUrl) {
  $emailPrint = new Printable("../log/email.html", "w");
  
  $subject = 'Invitation to GPass service';

  $message =
  '<html>
  <head>
    <title>Invitation to GPass service</title>
  </head>
  <body>
    <div>
      <h1><span style="color: #003366;">Invitation to GPass service</span></h1>
    </div>
    <p>&nbsp;</p>
    <p>Dear <span style="color: #800000;">' . $user_name . '</span> &lt;' . $userEmail . '&gt;</p>
    <p>GPass admins invite you to join to the service. Click the following link you will admit to use GPass.</p>
    <p style="text-align: center;"><a href="' . $returnUrl . '/' . $user_name . '/' . $userPassword . '/' . $masterPassword . '">Click on this link</a></p>
    <p>Note that this link will expire in 30 days.</p>
  </body>
</html> 
';

  // To send HTML mail, the Content-type header must be set
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/html; charset=iso-8859-1';

  // Additional headers
  $headers[] = 'To: ' . $user_name . ' <' . $userEmail . '>';
  $headers[] = 'From: GPass Admin <koala@koalakoker.com>';

  // Mail it
  mail($userEmail, $subject, $message, implode("\r\n", $headers));

  $emailPrint->log($message);
  $emailPrint->close();
}

?>