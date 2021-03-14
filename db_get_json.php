<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Headers: *");

	include_once "passDB_cript.php";
	include_once "criptoFunc.php";
	
	$password = $_POST["chipher_password"];

	$Server = deChipher($Server, $password);
	$Username = deChipher($Username, $password);
	$PW = deChipher($PW, $password);
	$DB = deChipher($DB, $password);
	
	$conn = new mysqli($Server, $Username, $PW, $DB);
	
	$result = $conn->query("SELECT url, pass, registrationDate, expirationDate FROM gpass");
	
	$outp = "";
	while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
		if ($outp != "") {$outp .= ",";}
        $outp .= '{"url":"'            . $rs["url"]              . '",';
        $outp .= '"pass":"'            . $rs["pass"]             . '",';
        $outp .= '"registrationDate":"'. $rs["registrationDate"] . '",';
        $outp .= '"expirationDate":"'  . $rs["expirationDate"]   . '"}';
	}
	$conn->close();
			
	echo("[".$outp."]");
?>