<?php
    require_once("db_config.php");

    //Riprendo la sessione e la distruggo
    session_start();
    session_destroy();

    //Cancello eventuali cookie di sessione
    if(isset($_COOKIE["hw_cookie_id"]))
    {
        $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) 
        or die(mysqli_connect_error());

        //Li cancello dal database
        mysqli_query($conn, "DELETE FROM cookie WHERE ID='" . $_COOKIE["hw_cookie_id"]. "'") or die(mysqli_error($conn));

        //Cancello i cookie dell'utente
        setcookie("hw_cookie_id", "");
        setcookie("hw_cookie_token", "");

        //Libero lo spazio e chiudo la connessione al database
        mysqli_free_result($res);
        mysqli_close($conn);
    }

    header("Location: index.php");
?>