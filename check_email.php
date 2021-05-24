<?php
    require_once("db_config.php");

    if(!filter_var(strtolower($_GET["email"]), FILTER_VALIDATE_EMAIL)) //Se ricevo una email non valida
    {  
        echo json_encode(array("ok" => null));
        exit;
    }

    $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) 
           or die(json_encode(array("ok" => null)));

    $email= mysqli_real_escape_string($conn, strtolower($_GET["email"]));

    //Verifico se esiste già
    $query= "SELECT Email FROM Person WHERE Email='$email'";
    $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => null)));

    if(mysqli_num_rows($res)>0)
        echo json_encode(array("ok" => true));
    else
        echo json_encode(array("ok" => false));

    
    mysqli_free_result($res);
    mysqli_close($conn);
?>