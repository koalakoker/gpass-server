<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json; charset=UTF-8");

	include_once "passDB_cript.php";
    include_once "criptoFunc.php";

    $userPassword = hashPass($_POST["chipher_password"]);

    //echo file_get_contents('php://input');

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
	
    $result = $conn->query("DROP TABLE backup");

    if ($result === TRUE) {
        echo "Table removed successfully<br>";
    } else {
        echo "Error removing table: " . $conn->error;
        echo "<br>";
    }
    
    $result = $conn->query("CREATE TABLE backup (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        name             VARCHAR(128) NOT NULL,
        date             VARCHAR(128) NOT NULL
    )");

    if ($result === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error;
        die("");
    }
	
	$conn->close();
?>