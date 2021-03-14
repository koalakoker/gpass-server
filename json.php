<?php
include_once("dbaccessData.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$conn = mysqli_connect($Server, $Username, $PW, $DB, 3307);
if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

$result = $conn->query("SELECT * FROM `gpass`");

$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}
    $outp .= '{"url":"'             . $rs["url"]              . '",';
    $outp .= '"pass":"'             . $rs["pass"]             . '",';
    $outp .= '"registrationDate":"' . $rs["registrationDate"] . '",';
    $outp .= '"expirationDate":"'   . $rs["expirationDate"]   . '"}';
}
$outp ='['.$outp.']';
$conn->close();

echo($outp);
?>