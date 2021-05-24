<?php  
    require_once("auth.php");
    header('Content-Type: application/json');

    //Se non ho fatto il login o non ho fornito l'id del contenuto da aggiungere tra i preferiti
    if(!checkAuth() || empty($_GET["id"]) || !is_numeric($_GET["id"])) //o l'id non è in un formato valido
    {
        echo json_encode(null); //Restituisco ok=null per indicare errore
        exit;
    }

    $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) 
           or die(json_encode(null));

    $id= mysqli_real_escape_string($conn,$_GET["id"]);

    $query="DELETE FROM favorite WHERE Email='" . $_SESSION["email"] . "' AND IDContent='$id'";

    $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => false)));

    mysqli_close($conn);

    echo json_encode(array("ok" => true, "id" => $_GET["id"]));
?>