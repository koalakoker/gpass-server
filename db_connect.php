<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	
	$conn = new mysqli($Server, $Username, $PW, $DB);
	
	$result = $conn->query("SELECT table_name FROM information_schema.tables");
	
	$outp = "";
	while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
		$outp .= $rs["table_name"] ."\n";
	}
	$conn->close();
			
	echo($outp);
?>