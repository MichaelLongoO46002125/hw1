<?php
    require_once("db_config.php");
    header('Content-Type: application/json');
    
    /*
        Richieste consentite:
        -   Senza parametri => Restituirà tutte le camere presenti
        -   Con i seguenti parametri: check_in, check_out, persons_num, matrimonial, single, min_fee, max_fee
            - min_fee e max_fee possono essere vuoti
            => Restituirà tutte le camere che corrispondono a tali parametri
    */

    //Se ho inserito tutti i parametri
    if(!empty($_GET["check_in"]) && !empty($_GET["check_out"]) && !empty($_GET["persons_num"]) && 
       !empty($_GET["matrimonial"]) && !empty($_GET["single"]) && isset($_GET["min_fee"]) && isset($_GET["max_fee"])
    )
    {
        //Valido i campi
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

        if(!is_numeric($_GET["persons_num"]) || $_GET["persons_num"] < 1 || $_GET["persons_num"] > 10)
        {
            echo json_encode(array("ok" => null));
            exit;
        }

        if($_GET["matrimonial"] === "true")
            $matrimonial= true;
        else if($_GET["matrimonial"] === "false")
            $matrimonial = false;
        else
        {
            echo json_encode(array("ok" => null));
            exit;
        }

        if($_GET["single"] === "true")
            $single= true;
        else if($_GET["single"] === "false")
            $single = false;
        else
        {
            echo json_encode(array("ok" => null));
            exit;
        }
        
        $check_in = $_GET["check_in"];
        $check_out = $_GET["check_out"];
        $persons_num = $_GET["persons_num"];

        if(empty($_GET["min_fee"])) 
            $min_fee= false;        //Indico che è vuoto    
        else if(!preg_match("/^\d*(\.\d{1,2})?$/", $_GET["min_fee"])) //Se non rispetta questo pattern allora non è valido
        {
            echo json_encode(array("ok" => null));
            exit;
        }
        else
            $min_fee= $_GET["min_fee"];

        if(empty($_GET["max_fee"]))
            $max_fee= false;
        else if(!preg_match("/^\d*(\.\d{1,2})?$/", $_GET["max_fee"])) 
        {
            echo json_encode(array("ok" => null));
            exit;
        }
        else
            $max_fee= $_GET["max_fee"];

        $conn = mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"])
                or die(json_encode(array("ok" => null)));

        $check_in= mysqli_real_escape_string($conn, $check_in);
        $check_out= mysqli_real_escape_string($conn, $check_out);
        $persons_num= mysqli_real_escape_string($conn, $persons_num);
        $min_fee= mysqli_real_escape_string($conn, $min_fee);
        $max_fee= mysqli_real_escape_string($conn, $max_fee);

        //Costruisco la query 
        $query="SELECT r.RoomNumber as RoomN, PersonNumber, MatrimonialBed, SingleBed, WiFi, WiFiFree, Minibar, Soundproofing, 
                       SwimmingPool, PrivateBathroom, AirConditioning, sqm, r.NightlyFee AS NightlyFee, Description, Type, 
                       Accomodation, PhotoPath
                FROM ((rooms r INNER JOIN room_types rt ON r.RoomType=rt.ID) LEFT JOIN roomphotos rp ON rp.RoomNumber=r.RoomNumber)
                WHERE r.RoomNumber NOT IN (
                    SELECT RoomNumber FROM rent 
                    WHERE   (CheckIn <= '$check_in' AND CheckOut>= '$check_in') OR (CheckIn <= '$check_out' AND CheckOut>= '$check_out') 
                            OR (CheckIn >= '$check_in' AND CheckOut<= '$check_out')
                )
                AND r.PersonNumber=$persons_num ";
        if($matrimonial || $single) //Se almeno uno dei due è checked
        {   //Approfondisco la ricerca
            if($matrimonial)
                $query.= "AND r.MatrimonialBed > 0 ";
            else
                $query.= "AND r.MatrimonialBed = 0 ";

            if($single)
                $query.= "AND r.SingleBed > 0 ";
            else
                $query.= "AND r.SingleBed = 0 ";
        }
        
        if($min_fee)
            $query.= "AND $min_fee <= r.NightlyFee ";
        
        if($max_fee)
            $query.= "AND $max_fee >= r.NightlyFee ";
    } 
    else if(count($_GET) > 0) //Ho inserito dei parametri ma non tutti => Errore!
    {
        echo json_encode(array("ok" => null));
        exit;
    }
    else //Non ho parametri
    {
        $conn = mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"])
                or die(json_encode(array("ok" => null)));

        //Costruisco la query
        $query = "SELECT r.RoomNumber as RoomN, PersonNumber, MatrimonialBed, SingleBed, WiFi, WiFiFree, Minibar, Soundproofing, 
                  SwimmingPool, PrivateBathroom, AirConditioning, sqm, NightlyFee, Description, Type, Accomodation, PhotoPath
                  FROM (rooms r INNER JOIN room_types rt ON r.RoomType=rt.ID) LEFT JOIN roomphotos rp ON rp.RoomNumber=r.RoomNumber";
    }

    $res = mysqli_query($conn, $query) or die(json_encode(array("ok" => null))); 

    if(mysqli_num_rows($res)>0)
    {
        $json = array(
            "ok" => true,
            "results" => array()
        );

        $index= array(); //Utilizzo questo array per gestire i risultati che hanno più immagini

        while($row = mysqli_fetch_assoc($res))
        {
            if(!isset($index[ $row["RoomN"] ])) //Se non ho già inserito questo risultato in $json
            {   //Lo inserisco
                $json["results"][]= array(
                    "roomNumber" => $row["RoomN"],
                    "personNumber" => $row["PersonNumber"],
                    "matrimonialBed" => $row["MatrimonialBed"],
                    "singleBed" => $row["SingleBed"],
                    "wifi" => (bool)$row["WiFi"],
                    "wifiFree" => (bool)$row["WiFiFree"],
                    "minibar" => (bool)$row["Minibar"],
                    "soundproofing" => (bool)$row["Soundproofing"],
                    "swimmingPool" => (bool)$row["SwimmingPool"],
                    "privateBathroom" => (bool)$row["PrivateBathroom"],
                    "airConditioning" => (bool)$row["AirConditioning"],
                    "sqm" => $row["sqm"],
                    "nightlyFee" => $row["NightlyFee"],
                    "description" => $row["Description"],
                    "roomType" => $row["Type"],
                    "accomodation" => $row["Accomodation"],
                    "photos" => (isset($row["PhotoPath"]) ? array($row["PhotoPath"]) : [])
                );

                $index[ $row["RoomN"] ]= count($json["results"])-1; //Memorizzo la posizione in cui si trova l'id del risultato nell'array json
            }
            else //Lo ho già inserito quindi devo solo aggiungere il path della foto 
            {
                $json["results"][ $index[ $row["RoomN"] ] ]["photos"][]= $row["PhotoPath"]; 
            }
        }

        echo json_encode($json);
    }
    else
        echo json_encode(array("ok" => false));

    mysqli_free_result($res);
    mysqli_close($conn);
?>