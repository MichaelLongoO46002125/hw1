<?php
    require_once("auth.php");
    $logged = checkAuth();
    if($logged && $_SESSION["job"] !== "ADMIN") //Non sono ADMIN non ho diritto di stare qui
    {
        //Reindirizzo alla home
        header("Location: index.php");
        exit;
    }

    $error= array();

    //Se ho già compilato i campi
    if( !empty($_POST["name"]) && !empty($_POST["last_name"]) && !empty($_POST["email"]) && !empty($_POST["tel"]) && isset($_POST["job"]))
    {
        $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) or die(mysqli_connect_error());
        
        //Per il nome e il cognome mi limito solo a fare l'escape per non fare assunzioni particolari su di essi
        $name= mysqli_real_escape_string($conn, $_POST["name"]);
        $last_name= mysqli_real_escape_string($conn, $_POST["last_name"]);

        //VALIDAZIONE EMAIL
        if(filter_var(strtolower($_POST["email"]), FILTER_VALIDATE_EMAIL)) //Valida
        {   
            $email= mysqli_real_escape_string($conn, strtolower($_POST["email"])); 
            //Verifico se esiste già
            $query= "SELECT Email FROM Person WHERE Email='$email'";
            $res= mysqli_query($conn,$query) or die(mysqli_error($conn));

            if(mysqli_num_rows($res)>0)
            {
                $error["email1"] = true;
            }
        }    
        else //Non valida => Errore
            $error["email2"] = true;
        

        //VALIDAZIONE TELEFONO /^[+]?\d{1,15}$/
        if(!preg_match("/^[+]\d{1,15}$/", $_POST["tel"]))
            $error["tel"] = true;
            
        //VALIDAZIONE JOB
        switch(strtoupper($_POST["job"]))
        {
            case "USER":
            case "ADMIN":
            case "BARTENDER":
            case "CHEF":
            case "WAITER-WAITRESS":
                $job= mysqli_real_escape_string($conn, strtoupper($_POST["job"]));
                //Validazione dei campi salary, duty_start e duty_end
                if(!empty($_POST["salary"]))
                {
                    if(!preg_match("/^\d+(\.\d{1,2})?$/", $_POST["salary"]))
                        $error["salary"] = true;
                    else
                        $salary= mysqli_real_escape_string($conn, $_POST["salary"]);
                }

                if(!empty($_POST["duty_start"]))
                {
                    if(!preg_match("/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]$/", $_POST["duty_start"]))
                        $error["duty_start"] = true;
                    else
                        $duty_start= mysqli_real_escape_string($conn, $_POST["duty_start"]);
                }

                if(!empty($_POST["duty_end"]))
                {
                    if(!preg_match("/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]$/", $_POST["duty_end"]))
                        $error["duty_end"] = true;
                    else
                        $duty_end= mysqli_real_escape_string($conn, $_POST["duty_end"]);
                }
            break;

            default:
                $error["job"] = true; 
            break;
        }

        if(count($error) === 0)
        {
            $tel= mysqli_real_escape_string($conn, $_POST["tel"]);
            /*
                Dato che per inviare una email contente la password generata casualmente bisogna configurare un server SMTP
                ho deciso di evitare la generazione di password casuali e usare una password fissa: Prova123
            */ 
            $pw= password_hash("Prova123", PASSWORD_BCRYPT); 
            $query="INSERT INTO Person (Email, Name, LastName, PhoneNumber, Password) 
                    VALUES ('$email', '$name', '$last_name', '$tel', '$pw')";
            
            mysqli_query($conn, $query) or die(mysqli_query($conn));

            if(strtoupper($_POST["job"]) !== "USER")
            {
                $query= "INSERT INTO Employee (Email, Salary, Job, DutyStart, DutyEnd)
                         VALUES ('$email', '$salary', '$job', '$duty_start', '$duty_end')";

                mysqli_query($conn, $query) or die(mysqli_query($conn));
            }

            mysqli_free_result($res);
            mysqli_close($conn);

            header("Location: index.php");
        }
    }
    else
    {
        /*
            count($_POST) !== 0: 
            Se diverso da 0 allora ho già compilato almeno 1 campo  quindi notifico di compilare tutti i campi.
            Se uguale a 0 allora non ho compilato nessun campo (prima volta che entro oppure reload, 0 submit del form) 
            in questo caso non voglio visualizzare la notifica!
        */
        if(count($_POST) !== 0) 
            $error["general"] = true;   
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
        <link rel="stylesheet" href="style/admin_signup.css">
        <script src="scripts/generic.js" defer></script>
        <script src="scripts/admin_signup.js" defer></script>
    </head>

    <body>
        <nav>
            <a id="nav-logo" href="index.php">
                <img src="resources/icons/logo.png"/>
                <span>Home</span>
            </a>
            <div id="nav-links">
                <?php
                    if($logged && $_SESSION["job"] === "ADMIN")
                        echo '<a href="admin_signup.php">Registra Utente</a>';
                ?>
                <a href="ristorazione.php">Ristorazione</a>
                <a href="galleria.php">Galleria</a>
                <a href="prenotazione.php">Prenotazione</a>
                <?php
                    if($logged)
                        echo '<a href="logout.php">Logout</a>';
                    else
                        echo '<a href="login.php">Login</a>';
                ?>
            </div>
            <div id="nav-menu">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </nav>
        
        <article>
        <section data-sec="signup-sec"> 
                <form name="signup-form" method="post">
                    <h3>REGISTRATI</h3>
                    <?php
                        if(isset($error["job"]))
                            echo '<span>Si è verificato un errore!</span>';
                    ?>
                    <span data-error="general" 
                        <?php if(!isset($error["general"])) echo 'class="general hidden"'; else echo 'class="hidden"'; ?>>
                        Riempire tutti i campi!
                    </span>

                    <label>
                        <strong>NOME:</strong> 
                        <input type="text" name="name" <?php if(isset($_POST["name"])) echo 'value="'. $_POST["name"] .'"'; ?>>
                    </label>
                    <span data-error="name" class="hidden">Il nome non può essere vuoto!</span>

                    <label>
                        <strong>COGNOME:</strong> 
                        <input type="text" name="last_name" <?php if(isset($_POST["last_name"])) echo 'value="'. $_POST["last_name"] .'"'; ?>>
                    </label>
                    <span data-error="last_name" class="hidden">Il cognome non può essere vuoto!</span>

                    <label>
                        <strong>EMAIL:</strong> 
                        <input type="text" name="email" <?php if(isset($_POST["email"])) echo 'value="'. $_POST["email"] .'"'; ?>>
                    </label>
                    <?php 
                        if(!isset($error["email1"]) && !isset($error["email2"]))
                            echo '<span data-error="email" class="hidden">Default</span>'; //In questo modo lo span avrà un'altezza
                        else
                            echo '<span data-error="email">'. (isset($error["email1"]) ?  "Email già in uso!" : "Email non valida!") .'</span>';
                    ?>
                    
                    <label>
                        <strong>TELEFONO:</strong> 
                        <input type="text" name="tel" <?php if(isset($_POST["tel"])) echo 'value="'. $_POST["tel"] .'"'; ?>>
                    </label>
                    <span data-error="tel"
                        <?php if(!isset($error["tel"])) echo 'class="hidden"'; ?>>Numero inserito non valido!
                    </span>

                    <label class="select-job">
                        <strong>RUOLO:</strong>
                        <select name="job" >
                            <option value="USER">USER</option>
                            <option value="ADMIN">ADMIN</option>
                            <option value="BARTENDER">BARISTA</option>
                            <option value="CHEF">CHEF</option>
                            <option value="WAITER-WAITRESS">CAMERIERE/A</option>
                        </select>
                    </label>

                    <div data-subform="subform" class="subform none">
                        <label>
                            <strong>Inizio turno:</strong> 
                            <input type="text" name="duty_start">
                            <span data-error="duty_start" 
                                <?php if(!isset($error["duty_start"])) echo 'class="hidden"'; ?>>Orario non valido! [Formato 00:00]!
                            </span>
                        </label>
                        
                        <label>
                            <strong>Fine turno:</strong>
                            <input type="text" name="duty_end">
                            <span data-error="duty_end" 
                                <?php if(!isset($error["duty_end"])) echo 'class="hidden"'; ?>>Orario non valido! [Formato 00:00]!
                            </span>
                        </label>

                        <label>
                            <strong>Salario:</strong>
                            <input type="text" name="salary">
                            <span data-error="salary" 
                                <?php if(!isset($error["salary"])) echo 'class="hidden"'; ?>>Salario non valido!
                            </span> 
                        </label>
                    </div>

                    <input type="submit" value="Registrati">
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