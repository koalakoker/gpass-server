<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");
	
    $conn = new mysqli($Server, $Username, $PW, $DB);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
	
    $result = $conn->query("INSERT INTO gpass (url, pass, registrationDate, expirationDate) VALUES ('www.pippo.com','pippo','01/01/2018','01/01/2019')");

    if ($result === TRUE) {
        echo "Ok";
    } else {
        echo "Error: " . $conn->error;
    }

    $result = $conn->query("INSERT INTO gpass (url, pass, registrationDate, expirationDate) VALUES ('www.mario.com','mario','01/02/2018','01/02/2019')");

    if ($result === TRUE) {
        echo "Ok";
    } else {
        echo "Error: " . $conn->error;
    }

    $result = $conn->query("INSERT INTO gpass (url, pass, registrationDate, expirationDate) VALUES ('www.rogerWaters.com','rog','01/06/2018','01/06/2019')");

    if ($result === TRUE) {
        echo "Ok";
    } else {
        echo "Error: " . $conn->error;
    }
	
	$conn->close();
?>