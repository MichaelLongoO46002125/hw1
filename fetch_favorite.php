<?php  
    require_once("auth.php");
    header('Content-Type: application/json');

    //RESTITUISCE:
    //Una pagina di preferiti formata da $num preferiti 
    //Campo ok che può indicare:
    //  - Errore => null
    //  - Nessun risultato trovato => false
    //  - Risultati trovati => true

    if(!checkAuth()) //Se non ho fatto il login non posso avere preferiti
    {
        echo json_encode(array("ok" => null)); //Restituisco ok=null per indicare errore
        exit;
    }

    //Se sono autenticato
    
    $num= 3; //Default
    $off= 0; //Default

    if(!empty($_GET["num"])) //Verifico la presenza del parametro num che indica il numero massimo di preferiti richiesti (default = 3)
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
    $num= mysqli_real_escape_string($conn, $num);
    $off= mysqli_real_escape_string($conn, $off);

    //Eseguo la query 

    $query = "SELECT fc.id as id, title, imageURL, data, name as tag
              FROM (
                    (SELECT c.id AS id, title, imageURL, data 
                     FROM content c INNER JOIN favorite f ON c.ID=f.IDContent
                     WHERE f.Email='" . $_SESSION["email"] . "' LIMIT $num OFFSET $off
                    ) AS fc
                    INNER JOIN contenttag ct ON ct.IDContent=fc.id
                   ) INNER JOIN tag t ON t.ID=ct.IDTag;";

    $res= mysqli_query($conn, $query) or die(json_encode(array("ok" => null)));

    if(mysqli_num_rows($res) > 0) //Ho ottenuto dei risultati
    {   

        $json= array( //Conterrà il risultato finale
            "ok" => true,
            "favorites" => array()
        );

        $index= array(); //Utilizzo questo array per gestire i contenuti che hanno più tag (equivalenti a più righe della query)
                         //ES:  riga1: ID1, Tag1
                         //     riga2: ID1, Tag2
        while($row= mysqli_fetch_assoc($res))
        {
            if(!isset($index[ $row["id"] ])) //Se non ho già inserito questo contenuto in $json
            {   //Lo inserisco
                $json["favorites"][]= array(
                    "id" => $row["id"],
                    "title" => $row["title"],
                    "image" => $row["imageURL"],
                    "date"  => date("d/m/Y", strtotime($row["data"])),
                    "tags" => array($row["tag"])
                );

                $index[ $row["id"] ]= count($json["favorites"])-1; //Memorizzo la posizione in cui si trova l'id del contenuto nell'array json
            }
            else //Lo ho già inserito quindi devo solo aggiungere il tag ottenuto
            {
                $json["favorites"][ $index[ $row["id"] ] ]["tags"][]= $row["tag"]; 
            }
        }
        echo json_encode($json);
    }
    else //Nessun risultato trovato
        echo json_encode(array("ok" => false));

    mysqli_free_result($res);
    mysqli_close($conn);
?>