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
        <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre&display=swap" rel="stylesheet">
        <title>MHW1 - MICHAEL LONGO - O46002125</title> 
        <link rel="stylesheet" href="style/generic.css">
        <link rel="stylesheet" href="style/mhw1.css">
        <script src="scripts/generic.js" defer></script>
        <script src="scripts/script.js" defer></script>
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

        <header>
            <div id="overlay"></div>
            <h1>HomeWork Hotel</h1>
            <span>Camere di lusso, servizio in camera, piscina, bar, ristorante e tanto altro...</span>
        </header>
            
        <article>
            <section data-sec="fav-sec" class="fav-sec hidden"> <!--fav section-->
                <h2>PREFERITI</h2>
                <div class="fav-list">
                    <div data-btn="fav-next" class="next-cont"></div>
                    <div data-btn="fav-prev" class="prev-cont"></div>
                </div>
            </section>

            <section data-sec="search-sec"> <!--search bar section-->
                <form name="search-form" class="search-form">
                    <span>Cerca per titolo</span>
                    <div>
                        <input type="text" name="search_bar" placeholder="Cerca...">
                        <button type="submit">
                            <img src="resources/icons/search-icon.png">
                        </button>
                    </div>
                </form>
            </section>

            <section data-sec="content-sec" class="content-sec"> <!--content section-->
                <div data-cont-msg="msg"  class="content-msg hidden">Nessun risultato trovato</div>
                <div data-btn="cont-next" class="next-cont"></div>
                <div data-btn="cont-prev" class="prev-cont"></div>
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
