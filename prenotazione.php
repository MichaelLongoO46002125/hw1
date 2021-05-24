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
        <link rel="stylesheet" href="style/prenotazione.css">
        <script src="scripts/generic.js" defer></script>
        <?php
            if(isset($_SESSION["job"]) && ($_SESSION["job"] === "ADMIN")) //ADMIN
                echo '<script src="scripts/admin_prenotazione.js" defer></script>';
            else if($logged) //UTENTE AUTENTICATO
                echo '<script src="scripts/user_prenotazione.js" defer></script>';
            else //UTENTE NON AUTENTICATO
                echo '<script src="scripts/prenotazione_2.js" defer></script>';
        ?>
        <script src="scripts/prenotazione.js" defer></script>
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
            <section data-sec="booking-sec" class="booking-sec">
                <div data-sub-sec="search-side-bar" class="search-side-bar start-visible-hidden">
                    <div data-sb-btn="sidebar-btn-close" class="close-sidebar"></div>
                    <h3>CERCA</h3>
                    <form method="post" name="search-form">
                        <label> 
                            <strong>Check-In</strong>
                            <input type="date" class="input" name="check_in" value="<?php echo date('Y-m-d'); ?>">
                        </label>

                        <label> 
                            <strong>Check-Out</strong>
                            <input type="date" class="input" name="check_out" value="<?php echo date('Y-m-d', strtotime("+1 day")); ?>">
                        </label>

                        <label>  
                            <strong>Numero persone</strong>
                            <input type="number" class="input" name="persons_num" min="1" max="10" value="1">
                        </label>

                        <label class="label-checkbox">
                            <input type="checkbox" name="matrimonial">
                            <strong>Letto matrimoniale</strong>
                        </label>

                        <label class="label-checkbox">
                            <input type="checkbox" name="single">
                            <strong>Letto singolo</strong>
                        </label>

                        <label>
                            <strong>Tariffa minima</strong>
                            <input type="text" class="input" name="min_fee">
                        </label>

                        <label>
                            <strong>Tariffa massima</strong>
                            <input type="text" class="input" name="max_fee">
                        </label>

                        <input type="submit" value="CERCA">
                    </form>
                </div>

                <div data-sub-sec="show-room" class="show-room">

                </div>
            </section>

            <section data-modal="error" class="modal-msg hidden">
                <div>
                    <h3>ERRORE</h3>
                    <p></p>
                    <button type="button" data-modal-error="close" class="input confirm">CHIUDI</button> 
                </div>
            </section>

            <section data-modal="message" class="modal-msg hidden">
                <div>
                    <h3>ATTENZIONE</h3>
                    <p></p>
                    <button type="button" data-modal-msg="close" class="input confirm">CHIUDI</button> 
                </div>
            </section>

            <section data-modal="booking" class="modal-booking hidden">
                <div>
                    <?php
                        if(!$logged)
                        {
                            echo '<span>Per prenotare devi effettuare il login</span>';
                            echo '<button class="input confirm" type="button"><a href="login.php">Login</a></button>';
                            echo '<span>oppure</span>';
                            echo '<span>Chiama al +390950000000</span>';
                        }
                        else if(isset($_SESSION["job"]) && ($_SESSION["job"] === "ADMIN"))
                        {
                            echo '<h2>CONFERMA</h2>';
                            echo '<h3></h3>'; //Tipo+Sistemazione
                            echo '<span></span>'; //Prezzo
                            echo '<label>'. //Email
                                 '<strong>Email</strong>'.
                                 '<input type="text" class="input" data-modal-in="email">'.
                                 '</label>';
                            echo '<span class="error hidden" data-modal-msg="email_error"></span>';
                            echo '<form method="post" name="reg-email-form" class="hidden" action="admin_signup.php">'.
                                 '<input type="hidden" name="email" value="">'.
                                 '<input type="submit" class="input confirm-form" value="REGISTRA CLIENTE">'.
                                 '</form>';
                            echo '<div class="check-date">'; //Conferma date
                            echo '<label>'. 
                                 '<strong>Check-In</strong>'.
                                 '<input type="date" class="input" data-modal-in="check_in" value="' . date('Y-m-d') . '">'.
                                 '</label>';
                            echo '<label>'. 
                                 '<strong>Check-Out</strong>'.
                                 '<input type="date" class="input"  data-modal-in="check_out" value="' . date('Y-m-d', strtotime("+1 day")) . '">'.
                                 '</label>';
                            echo '</div>';
                            echo '<span class="error hidden" data-modal-msg="error"></span>';
                            echo '<div class="button-area">';
                            echo '<button class="input confirm" data-modal-in="close"  type="button">CHIUDI</button>';
                            echo '<button class="input confirm" data-modal-in="submit" type="button">PRENOTA</button>';
                            echo '</div>'; 
                        }
                        else
                        {
                            echo '<h2>CONFERMA</h2>';
                            echo '<h3></h3>'; //Tipo+Sistemazione
                            echo '<span></span>'; //Prezzo
                            echo '<div class="check-date">'; //Conferma date
                            echo '<label>'. 
                                 '<strong>Check-In</strong>'.
                                 '<input type="date" class="input" data-modal-in="check_in" value="' . date('Y-m-d') . '">'.
                                 '</label>';
                            echo '<label>'. 
                                 '<strong>Check-Out</strong>'.
                                 '<input type="date" class="input"  data-modal-in="check_out" value="' . date('Y-m-d', strtotime("+1 day")) . '">'.
                                 '</label>';
                            echo '</div>';
                            echo '<span class="error hidden" data-modal-msg="error"></span>';
                            echo '<div class="button-area">';
                            echo '<button class="input confirm" data-modal-in="close"  type="button">CHIUDI</button>';
                            echo '<button class="input confirm" data-modal-in="submit" type="button">PRENOTA</button>';
                            echo '</div>';
                        } 
                    ?>
                </div>
            </section>
            <div data-sb-btn="sidebar-btn-open" class="open-sidebar start-hidden-visible"></div>
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
