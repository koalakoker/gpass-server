<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");

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

    $newTable = $_POST["newtable"];
    $oldTable = "gpass";
	
    $result = $conn->query("CREATE TABLE " . $newTable . " LIKE " . $oldTable); 
    
    if ($result === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error;
        die("");
    }
    
    $result = $conn->query("INSERT " . $newTable . " SELECT * FROM " . $oldTable);
    
    if ($result === TRUE) {
        echo "Table populated successfully<br>";
    } else {
        echo "Error populating table: " . $conn->error;
        die("");
    }

    $result = $conn->query("INSERT INTO `backup` (`id`, `name`, `date`) VALUES (NULL,'" . $newTable ."', CURDATE() ) ");
    if ($result === TRUE) {
        echo "Backup successfully<br>";
    } else {
        echo "Error writing backup table: " . $conn->error;
        die("");
    }
	
    $conn->close();
    
    echo("Backup completed!");
?>