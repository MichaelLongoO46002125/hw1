<?php
    define("UNSPLASH_APIKEY","ONE7KXWEyNw3T0_YN7DXcIuOU-VYjlAzdURk5mnAK5s");
    define("UNSPLASH_ENDPOINT", "https://api.unsplash.com/search/photos?");

    header('Content-Type: application/json');
/* 
    Richiesta fetch per cercare delle immagini sul sito unsplash.com
    Richiede i parametri:
    client_id=API_KEY 
    query=TERMINE_DI_RICERCA
    Parametri opzionali:
    per_page=NUMERO => Indica il numero di risultati da dare per ogni pagina (default: 10)
    orientation=landscape => Indica che voglio le foto che hanno l'orientazione landscape
    Quindi: UNSPLASH_ENDPOINT ."client_id=". UNSPLASH_APIKEY ."&query=".urlencode(Hotel room)."&per_page=30&orientation=landscape"
*/

    $queryHTTP = array(
        "client_id" => UNSPLASH_APIKEY,
        "query" => "Hotel room",
        "per_page" => 30,
        "orientation" => "landscape"
    );

    $queryHTTP = http_build_query($queryHTTP);

    $curl = curl_init(); //Inizializzo una sessione cURL
    curl_setopt($curl, CURLOPT_URL, UNSPLASH_ENDPOINT . $queryHTTP); //Imposto l'URL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //Richiedo il risultato come stringa
    $results = curl_exec($curl); //Eseguo la richiesta e ottengo il risultato
    $statusCode= curl_getinfo($curl, CURLINFO_HTTP_CODE); //Ottengo informazioni sullo status code fornito nella risposta
    curl_close($curl);  //Chiudo la sessione cURL e libero le risorse.

    if($statusCode == 200) //La richiesta è stata effettuata con successo
    {
        $results= json_decode($results, true); //Trasformo il testo JSON in un array associativo
        $response = array();
    
        foreach($results["results"] as $result) //Estraggo solo quello che mi serve
        {
            $response[]= array( "image" => $result["urls"]["regular"], "description" => $result["description"] );
        }

        echo json_encode($response); //Converto in formato JSON l'array ottenuto e lo stampo
    }
    else
    {
        echo json_encode(null); //In caso di errore restituisco null
    }
?>  