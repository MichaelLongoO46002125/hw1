<?php
    define("SPOONACULAR_APIKEY","a1391ab44f7c424aa84492ef3d49ec71");
    define("SPOONACULAR_ENDPOINT", "https://api.spoonacular.com/recipes/complexSearch?");

    header('Content-Type: application/json');
/*
    Parametri obbligatori: 
    apiKey=API_KEY
    Parametri opzionali: 
    cuisine=Cucina oppure Cucina1,Cucina2… la , funziona da OR =>Indica il tipo di cucina delle ricette che vogliamo.
    addRecipeInformation=true/false => Permette di aggiungere informazioni sulla ricetta (vegana, vegetariana ecc…)
    fillIngredients=true/false => Indica se vogliamo elencati o non gli ingredienti
    number=NUMERO => Indica il numero massimo di ricette che vogliamo ottenere (1-100)
    Quindi: SPOONACULAR_ENDPOINT ."apiKey=". SPOONACULAR_APIKEY ."&cuisine=Italian,America,European&addRecipeInformation=true
            &fillIngredients=true&number=15"
*/
    
    $queryHTTP = array(
        "apiKey" => SPOONACULAR_APIKEY,
        "cuisine" => "Italian,America,European",
        "addRecipeInformation" => "true",
        "fillIngredients" => "true",
        "number" => 15
    );
    
    $queryHTTP = http_build_query($queryHTTP);

    $curl = curl_init(); //Inizializzo una sessione cURL
    curl_setopt($curl, CURLOPT_URL, SPOONACULAR_ENDPOINT . $queryHTTP); //Imposto l'URL
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
            $response[] = array(
                "title" => $result["title"],
                "pricePerServing" => $result["pricePerServing"],
                "image" => $result["image"],
                "cuisines" => $result["cuisines"],
                "dishTypes" => $result["dishTypes"],
                "vegan" => $result["vegan"],
                "vegetarian" => $result["vegetarian"],
                "glutenFree" => $result["glutenFree"],
                "dairyFree" => $result["dairyFree"],
                "extendedIngredients" => $result["extendedIngredients"]
            );
        }

        echo json_encode($response); //Converto in formato JSON l'array ottenuto e lo stampo
    }
    else
    {
        echo json_encode(null); //In caso di errore restituisco null
    }
?>