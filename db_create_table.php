<?php
	include_once "passDB_cript.php";
    include_once "criptoFunc.php";

    $userPassword = hashPass($_POST["chipher_password"]);

    if ($userPassword!=$Password)
    {
        die("Access denied");
    }

    $Server   = deChipher($Server, $userPassword);
    $Username = deChipher($Username, $userPassword);
    $PW       = deChipher($PW, $userPassword);
    $DB       = deChipher($DB, $userPassword);
	
    $conn = new mysqli($Server, $Username, $PW, $DB);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
	
    $result = $conn->query("DROP TABLE gpass");

    if ($result === TRUE) {
        echo "Table removed successfully<br>";
    } else {
        echo "Error removing table: " . $conn->error;
        echo "<br>";
    }
    
    $result = $conn->query("CREATE TABLE gpass (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        name             VARCHAR(128) NOT NULL,
        url              VARCHAR(128) NOT NULL,
        username         VARCHAR(128) NOT NULL,
        pass             VARCHAR(128) NOT NULL,
        registrationDate VARCHAR(128) NOT NULL,
        expirationDate   VARCHAR(128) NOT NULL
    )");

    if ($result === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error;
        die("");
    }
	
	$conn->close();
?>