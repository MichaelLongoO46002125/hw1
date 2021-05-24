<?php
    require_once("auth.php");

    header('Content-Type: application/json');
    $logged= checkAuth();
    
    //Restituisce una pagina di contenuti formata da $num contenuti
    //Campo ok che può indicare:
    //  - Errore => null
    //  - Nessun risultato trovato => false
    //  - Risultati trovati => true

    $num = 4; //Default
    $off = 0; //Default
    $title = false; //Default (Nessun titolo richiesto)

    if(!empty($_GET["num"])) //Verifico la presenza del parametro num che indica il numero massimo di contenuti richiesti (default = 4)
    {
        $num= $_GET["num"];

        //is_numeric restituisce true se il parametro dato è un numero o una stringa numerica, false se è una stringa alfanumerica
        if(!is_numeric($num) || $num < 1) //Se non è un numero o è non positivo restituisco errore
        {
            //Se non è un numero restituisco null che indica errore!
            echo json_encode(array("ok" => null)); 
            exit;
        }       
    }

    if(!empty($_GET["offset"]))
    {
        $off= $_GET["offset"];

        if(!is_numeric($off) || $off < 0) //Se non è un numero o è negativo restituisco errore
        {
            echo json_encode(array("ok" => null));
            exit;
        }
    }

    $conn= mysqli_connect($db_config["host"], $db_config["user"], $db_config["pw"], $db_config["db"]) 
           or die(json_encode(array("ok" => null)));
    
    //Faccio l'escape    
    $num = mysqli_real_escape_string($conn, $num);
    $off = mysqli_real_escape_string($conn, $off);

    if(!empty($_GET["title"]))
    {   
        $title = strtoupper($_GET["title"]);
        $title = ltrim($title, " ");
        $title = rtrim($title, " ");
        $title = mysqli_real_escape_string($conn, $title);
    }

    //Eseguo la query
    $query = "SELECT T1.id AS id, title, imageURL, description, data, Name as tag, ";
    if($logged) //Se sono autenticato potrei avere dei preferiti quindi per ogni contenuto indico anche se è un preferito tramite isFav che è un booleano 
        $query.= " EXISTS (SELECT * FROM favorite WHERE Email='" . $_SESSION["email"] . "' AND IDContent=T1.id) AS isFav";
    else //altrimenti estraggo solo i contenuti (mettendo isFav=NULL che indicherà lato client che è un utente non autenticato)
        $query.= " NULL AS isFav";
    
    $query.=" FROM ((SELECT id, title, description, imageURL, data FROM content";

    if($title)
        $query.= " WHERE UPPER(title) LIKE '%$title%'";

    $query.= " ORDER BY data DESC LIMIT $num OFFSET $off) AS T1 INNER JOIN contenttag ct ON T1.id=ct.IDContent) 
               INNER JOIN tag t ON t.ID=ct.IDTag
               ORDER BY data DESC";

    $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => null)));

    if(mysqli_num_rows($res) > 0) //Ho ottenuto dei risultati
    {   
        $json= array( //Conterrà il risultato finale
            "ok" => true,
            "contents" => array()
        );

        $index= array(); //Utilizzo questo array per gestire i contenuti che hanno più tag (equivalenti a più righe della query)
                         //ES:  riga1: ID1, Tag1
                         //     riga2: ID1, Tag2
        while($row= mysqli_fetch_assoc($res))
        {
            if(!isset($index[ $row["id"] ])) //Se non ho già inserito questo contenuto in $json
            {   //Lo inserisco
                $json["contents"][]= array(
                    "id" => $row["id"],
                    "title" => $row["title"],
                    "image" => $row["imageURL"],
                    "date"  => date("d/m/Y", strtotime($row["data"])),
                    "description" => $row["description"],
                    "tags" => array($row["tag"]),
                    "isFav" => $row["isFav"]
                );
                
                $index[ $row["id"] ]= count($json["contents"])-1; //Memorizzo la posizione in cui si trova l'id del conteuto nell'array json
            }
            else //Lo ho già inserito quindi devo solo aggiungere il tag ottenuto
            {
                $json["contents"][ $index[ $row["id"] ] ]["tags"][]= $row["tag"];
            }
        }
        echo json_encode($json);
    }
    else //Nessun risultato trovato
        echo json_encode(array("ok" => false));

    mysqli_free_result($res);
    mysqli_close($conn);
?>