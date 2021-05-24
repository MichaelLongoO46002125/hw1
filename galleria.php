<?php
    require_once("auth.php");

    $logged= checkAuth();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Unicase&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre&display=swap" rel="stylesheet">
        <title>CAMERE - MICHAEL LONGO - O46002125</title> 
        <link rel="stylesheet" href="style/generic.css">
        <link rel="stylesheet" href="style/galleria.css">
        <script src="scripts/generic.js" defer></script>
        <script src="scripts/galleria.js" defer></script>
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
            <section data-sec="modal-sec"class="modal hidden">
                
            </section>
            <section data-sec="gallery-sec" class="gallery">
                <h1 class="error hidden">Si è verificato un errore!</h1>
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
