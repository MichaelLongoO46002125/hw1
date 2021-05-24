<?php
    require_once("db_config.php");

    session_start(); //Inizio/Riprendo la sessione

    function checkAuth()
    {
        GLOBAL $db_config;

        if(!isset($_SESSION["email"]) || !isset($_SESSION["job"])) //Se non ho una sessione attiva
        {
            //Controllo se sono presenti dei cookie di sessione
            if(isset($_COOKIE["hw_cookie_id"]) && isset($_COOKIE["hw_cookie_token"]))
            {
                //Effettuo una richiesta al database per verificare che siano validi
                $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) 
                       or die(mysqli_connect_error());

                $cookie_id= mysqli_real_escape_string($conn,$_COOKIE["hw_cookie_id"]);
                $cookie_token= $_COOKIE["hw_cookie_token"];

                $query= "SELECT * FROM cookie WHERE ID='$cookie_id'";
                $res= mysqli_query($conn,$query) or die(mysqli_error($conn));
                
                if(mysqli_num_rows($res) > 0) //Ho trovato un risultato
                {
                    $row= mysqli_fetch_assoc($res);
                   
                    //Verifico che i token corrispondano
                    if( password_verify($cookie_token, $row["Token"]) )
                    {   //Verifico la validità temporale 
                        if(date("Y-m-d") > $row["Expires"]) //Se la sessione è scaduta
                        {   //Cancello i cookie
                            mysqli_query($conn, "DELETE FROM cookie WHERE ID='$cookie_id'") or die(mysqli_error());
                            setcookie("hw_cookie_id", "");
                            setcookie("hw_cookie_token", "");
    
                            //Libero lo spazio e chiudo la connessione al database
                            mysqli_free_result($res);
                            mysqli_close($conn);
                            return false; 
                        }
                        //allora si ha una sessione valida
                        $_SESSION["email"] = $row["Email"];

                        //Ricavo il tipo di utente "job"
                        $res= mysqli_query($conn, "SELECT job FROM employee WHERE Email='" . $_SESSION["email"] . "'") 
                              or die(mysqli_error($conn));

                        if(mysqli_num_rows($res) > 0) //Se ho ottenuto un risultato allora l'utente è un impiegato e ha un ruolo (job)
                        {
                            $row= mysqli_fetch_assoc($res);
                            $_SESSION["job"] = $row["job"];
                        } 
                        else //Altrimenti è un normale utente
                            $_SESSION["job"] = "USER";
                            
                        //Libero lo spazio e chiudo la connessione al database
                        mysqli_free_result($res);
                        mysqli_close($conn);

                        return true;
                    }
                }

                //Libero lo spazio e chiudo la connessione al database
                mysqli_free_result($res);
                mysqli_close($conn);
            }
            
            //Non ho cookie di sessione o li ho ma non sono validi o si è verificato un errore
            return false;
        }
        else //Ho una sessione attiva
            return true;
    }
?>