<?php
    require_once("auth.php");

    if(checkAuth())//Se ho già fatto il login significa che non ho bisogno di un account quindi nego l'accesso portandolo nella home
    {
        header("Location: index.php");
        exit;
    }

    $error= array();

    //Se ho già compilato i campi
    if( !empty($_POST["name"]) && !empty($_POST["last_name"]) && !empty($_POST["email"]) &&
        !empty($_POST["tel"]) && !empty($_POST["pw"]) && !empty($_POST["cpw"])
       )
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
        
        //VALIDAZIONE PASSWORD: Almeno 8 caratteri, almeno 1 lettera maiuscola, almeno 1 lettera minuscola e almeno 1 numero
        if(strlen($_POST["pw"]) < 8)
            $error["pw1"] = true;

        if(!preg_match("/[A-Z]/", $_POST["pw"]) || !preg_match("/[a-z]/", $_POST["pw"]) || !preg_match("/[0-9]/", $_POST["pw"]))
            $error["pw2"] = true;
        //VALIDAZIONE CONFERMA PASSWORD:
        if(strcmp($_POST["pw"], $_POST["cpw"]) !== 0)
            $error["cpw"] = true;

        //VALIDAZIONE TELEFONO /^[+]?\d{1,15}$/
        if(!preg_match("/^[+]\d{1,15}$/", $_POST["tel"]))
            $error["tel"] = true;
            
        if(count($error) === 0)
        {
            $tel= mysqli_real_escape_string($conn, $_POST["tel"]);
            $pw= mysqli_real_escape_string($conn, $_POST["pw"]);
            $pw= password_hash($pw, PASSWORD_BCRYPT);
            $query="INSERT INTO Person (Email, Name, LastName, PhoneNumber, Password) 
                    VALUES ('$email','$name', '$last_name', '$tel', '$pw')";
            
            mysqli_query($conn, $query) or die(mysqli_query($conn));

            mysqli_free_result($res);
            mysqli_close($conn);

            header("Location: login.php");
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
        <link rel="stylesheet" href="style/signup.css">
        <script src="scripts/generic.js" defer></script>
        <script src="scripts/signup.js" defer></script>
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
        <section data-sec="signup-sec"> 
                <form name="signup-form" method="post">
                    <h3>REGISTRATI</h3>

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

                    <label>
                        <strong>PASSWORD:</strong>
                        <input type="password" name="pw">
                    </label>
                    <?php //Span per indicare gli errori del campo password
                        $pwerror = "";
                        if(isset($error["pw1"]))
                            $pwerror .= "La password deve contenere almeno 8 caratteri!";
                        if(isset($error["pw2"]))
                        {
                            if(isset($error["pw1"]))
                                $pwerror .= "\nLa password deve contenere almeno 1 lettera maiuscola, almeno 1 lettera minuscola e almeno 1 numero!";
                            else
                                $pwerror .= "La password deve contenere almeno 1 lettera maiuscola, almeno 1 lettera minuscola e almeno 1 numero!";
                        }    
                        
                        echo '<span data-error="pw"';
                        if(!isset($error["pw1"]) && !isset($error["pw2"])) 
                            echo ' class="hidden" >Default</span>';
                        else
                            echo '>' . $pwerror . '</span>';
                    ?>

                    <label>
                        <strong>CONFERMA PASSWORD:</strong>
                        <input type="password" name="cpw">
                    </label>
                    <span data-error="cpw"
                        <?php if(!isset($error["cpw"])) echo 'class="hidden"'; ?>>Le due password devono coincidere!
                    </span>
                    
                    <div>
                        <input type="submit" value="Registrati">
                        <button type="button"><a href="login.php">Accedi</a></button>
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