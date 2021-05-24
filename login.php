<?php
    require_once("auth.php");

    if(checkAuth()) //Se l'utente è autenticato
    {
        header("Location: index.php");
        exit;
    } //Altrimenti
    else if(!empty($_POST["email"]) && !empty($_POST["password"])) //Verifico se l'utente ha compilato i campi per effettuare il login
    {
        //Connessione con il database
        $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) 
                or die(mysqli_connect_error());
        
        $email= mysqli_real_escape_string($conn, strtolower($_POST["email"]));

        //Preparo la query
        $query=  "SELECT person.Email AS email, job, password
                  FROM person left join employee on person.Email=employee.Email 
                  WHERE person.Email='$email'";

        //Eseguo la query
        $res= mysqli_query($conn,$query) or die(mysqli_error($conn));
        $error= array();
        //Controllo se ho ottenuto dei risultati
        if(mysqli_num_rows($res) > 0)
        {
            $row= mysqli_fetch_assoc($res);
            
            //Confronto la password
            if(password_verify($_POST["password"], $row["password"])) //Se corrispondono
            {
                //Memorizzo nella sessione email e job
                $_SESSION["email"] = $row["email"];
                $_SESSION["job"] = isset($row["job"]) ? $row["job"] : "USER";
                
                if(!empty($_POST["remember"])) //Se "Ricorda l'accesso" è selezionato 
                {   //Creo i cookie necessari per mantenere la sessione anche dopo la chiusura del browser
                    $token = random_bytes(16); //Genera una stringa di 16 caratteri crittografici casuali
                    $hash = password_hash($token, PASSWORD_BCRYPT); //Ne calcolo l'hash che memorizzerò nel database
                    $expires = strtotime("+30 day"); //Do una validità al cookie di 30 giorni (strtotime ritorna un timestamp)
                   
                    $query= "INSERT INTO cookie (token, expires, email) VALUES ('$hash', '"
                            . date("Y-m-d", $expires) . //Date converte un timestamp in una stringa formattata
                            "', '". $_SESSION["email"] ."')";
                    
                    $res= mysqli_query($conn,$query) or die(mysqli_error($conn)); //Tengo traccia del cookie nel database
                    //Setto i cookie per l'utente
                    setcookie("hw_cookie_id", mysqli_insert_id($conn), $expires);
                    setcookie("hw_cookie_token", $token, $expires);
                }

                //Libero lo spazio e chiudo la connessione al database
                mysqli_free_result($res);
                mysqli_close($conn);

                //Reindirizzo alla home
                header("Location: index.php");
                exit();
            }
            else  //Errore password errata
                $error["pw"] = true;
        }
        else  //Errore email non registrata  
            $error["email"]= true;

        //Libero lo spazio e chiudo la connessione al database
        mysqli_free_result($res);
        mysqli_close($conn); 
    }
    else
    {   
        //Se ho compilato dei campi ma alcuni sono vuoti
        if(count($_POST) > 0)
            $error["general"] = true;   //Compilare tutti i campi
    }    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Unicase&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre&display=swap" rel="stylesheet">
        <title>MHW1 - MICHAEL LONGO - O46002125</title> 
        <link rel="stylesheet" href="style/generic.css">
        <link rel="stylesheet" href="style/login.css">
        <script src="scripts/generic.js" defer></script>
        <script src="scripts/login.js" defer></script>
    </head>

    <body>
        <nav>
            <a id="nav-logo" href="index.php">
                <img src="resources/icons/logo.png"/>
                <span>Home</span>
            </a>
            <div id="nav-links">
                <a href="ristorazione.php">Ristorazione</a>
                <a href="galleria.php">Galleria</a>
                <a href="prenotazione.php">Prenotazione</a>
                <a href="login.php">Login</a>
            </div>
            <div id="nav-menu">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </nav>
            
        <article>
            <section data-sec="login-sec"> 
                <form name="login-form" method="post">
                    <h3>EFFETTUA IL LOGIN</h3>

                    <span data-error="general"
                        <?php if(!isset($error["general"])) echo 'class="general hidden"'; else echo 'class="general"'; ?>>
                        Compilare tutti i campi!
                    </span>
            
                    <label>
                        <strong>EMAIL:</strong>
                        <input type="text" name="email" <?php if(isset($_POST["email"])) echo 'value="'.$_POST["email"].'"';?>>
                    </label>
                    <span data-error="email" 
                        <?php if(!isset($error["email"])) echo 'class="hidden"'; ?>>
                        Email non riconosciuta!
                    </span>
                    
                    <label>
                        <strong>PASSWORD:</strong>
                        <input type="password" name="password">
                    </label>
                    <span data-error="pw" 
                        <?php if(!isset($error["pw"])) echo 'class="hidden"'; ?>>
                        Password errata!
                    </span>
                    
                    <div class="remember">
                        <input type="checkbox" name="remember">Ricorda l'accesso
                    </div>

                    <div>
                        <input type="submit" value="Login">
                        <button type="button"><a href="signup.php">Registrati</a></button>
                    </div>
                </form>
            </section>
        </article>

        <footer>
            <span><strong>CREATORE SITO: </strong>Michael Longo O46002125</span>
            <span>
                <strong>INDIRIZZO: </strong><address>VIA NON ESISTENTE, 99 - 95100 CATANIA, ITALY</address><br>
                <strong>TEL: </strong><address>+39 095 XX XX XXX</address><br>
                <strong>MAIL: </strong><address>homeworkhotel@mhw.it</address>
            </span>
            <div id="footer-icon-cont">
                <a class="footer-icon">
                    <img src="resources/icons/facebook-icon.png">
                </a>
                <a class="footer-icon">
                    <img src="resources/icons/instagram-icon.png">
                </a>
            </div>
            <span id="copyright"> 
                © Copyright 2021 - HomeWork Hotel
            </span>
        </footer>
    </body>
</html>