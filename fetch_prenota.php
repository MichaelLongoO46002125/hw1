<?php
    require_once("auth.php");
    header('Content-Type: application/json');

    if(!checkAuth()) //Se non ho fatto il login non posso prenotare
    {
        echo json_encode(array("ok" => null)); //Restituisco ok=null per indicare errore
        exit;
    }

    /*  Parametri:
        -   room: indica la camera
        -   check_in: data di check_in
        -   check_out: data di check_out
        -   email: indica il cliente per cui si sta prenotando la camera (obbligatorio se a fare la prenotazione è l'ADMIN)!

        Restituisce:
        -   null        => Errore!
        -   ok = false  => La camera non è disponibile per il periodo selezionato!
        -   ok = true   => Prenotazione effettuata con successo!
    */

    if(!empty($_GET["room"]) && !empty($_GET["check_in"]) && !empty($_GET["check_out"]))
    {
        //VALIDO I CAMPI

        //Verifico se le date sono scritte in un formato errato o se sono minori della data attuale
        //YYYY-MM-DD
        //0123456789
        if( $_GET["check_in"][4] !=="-" || $_GET["check_in"][7] !=="-" || $_GET["check_out"][4] !=="-" || $_GET["check_out"][7] !=="-" ) 
        {
            echo json_encode(array("ok" => null));
            exit;
        }

        $year= (int)substr($_GET["check_in"], 0, 4);
        $month = (int)substr($_GET["check_in"], 5, 2);
        $day= (int)substr($_GET["check_in"], 8, 2);

        //checkdate ( int $month , int $day , int $year )
        if(!checkdate($month, $day, $year) || $_GET["check_in"] < date("Y-m-d") ) 
        {
            echo json_encode(array("ok" => null));
            exit;
        }

        $year= (int)substr($_GET["check_out"], 0, 4);
        $month = (int)substr($_GET["check_out"], 5, 2);
        $day= (int)substr($_GET["check_out"], 8, 2); 

        if(!checkdate($month, $day, $year) || $_GET["check_out"] < date("Y-m-d") )
        {
            echo json_encode(array("ok" => null));
            exit;
        }

        if( $_GET["check_out"] <= $_GET["check_in"] )
        {
            echo json_encode(array("ok" => null));
            exit;
        }

        //Se il numero della camera non rispetta il pattern A000 
        if( !preg_match("/^\d{3}[a-zA-Z]$/", $_GET["room"] ) ) 
        {
            echo json_encode(array("ok" => null));
            exit;
        }
        
        //Verifico l'esistenza della camera ed estraggo la tarrifa per notte!
        $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) or
               die(json_encode(array("ok" => null)));

        $roomN= mysqli_real_escape_string($conn, $_GET["room"]);

        $query= "SELECT RoomNumber, NightlyFee FROM rooms WHERE RoomNumber='$roomN'";

        $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => null)));

        if(mysqli_num_rows($res) > 0)
        {
            $row= mysqli_fetch_assoc($res);
            $nightlyFee = $row["NightlyFee"];
        }
        else //Camera non esistente => Errore!
        {
            echo json_encode(array("ok" => null));
            mysqli_close($conn);
            exit;
        }

        $check_in= mysqli_real_escape_string($conn, $_GET["check_in"]);
        $check_out= mysqli_real_escape_string($conn, $_GET["check_out"]);

        //Se chi sta effettuando la prenotazione è l'ADMIN allora prenoto per conto di un utente
        if($_SESSION["job"] === "ADMIN")
        {
            //Quindi devo validare la email
            if(!empty($_GET["email"]) && filter_var(strtolower($_GET["email"]), FILTER_VALIDATE_EMAIL))
            {
                $email= mysqli_real_escape_string($conn, strtolower($_GET["email"]));

                //Verifico se esiste 
                $query= "SELECT Email FROM Person WHERE Email='$email'";
                $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => null)));
            
                if(mysqli_num_rows($res) == 0) //Email non registrata
                {
                    echo json_encode(array("ok" => null)); //Errore 
                    mysqli_close($conn);
                    exit;
                }
            }
            else //Email non valida
            {
                echo json_encode(array("ok" => null));
                mysqli_close($conn);
                exit;
            }
        }
        //Verifico se la camera risulta prenotata durante quell'intervallo di tempo
        $query= "SELECT RoomNumber 
                    FROM rent
                    WHERE RoomNumber='$roomN' AND (
                    (CheckIn <= '$check_in' AND CheckOut>= '$check_in') OR (CheckIn <= '$check_out' AND CheckOut>= '$check_out') 
                    OR (CheckIn >= '$check_in' AND CheckOut<= '$check_out')
                    )";

        $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => null)));

        if(mysqli_num_rows($res) > 0) //La camera risulta prenotata durante quell'intervallo di tempo
        {
            echo json_encode(array("ok" => false));
            mysqli_free_result($res);
            mysqli_close($conn);
            exit;
        }
        
        //Calcolo il numero di notti!
        $origin = new DateTime($_GET["check_in"]);
        $target = new DateTime($_GET["check_out"]);
        $nightStay = ($origin->diff($target))->days;
    
        //La camera non risulta prenotata => La prenoto!

        if($_SESSION["job"] !== "ADMIN") //Se non sono l'admin allora metto l'email dell'utente attuale altrimenti conterrà
            $email= $_SESSION["email"];  //quella validata in precedenza nel caso ADMIN

        $query= "INSERT INTO rent (PersonID,RoomNumber, NightStay, NightlyFee, CheckIn, CheckOut) VALUES
                ('$email', '$roomN', '$nightStay', '$nightlyFee', '$check_in', '$check_out')";

        $res= mysqli_query($conn,$query) or die(json_encode(array("ok" => null)));

        if($res)
            echo json_encode(array("ok" => true));
        else
            echo json_encode(array("ok" => null));

        mysqli_close($conn);
    }
    else
    {
        echo json_encode(array("ok" => null));
        exit;
    }
?>