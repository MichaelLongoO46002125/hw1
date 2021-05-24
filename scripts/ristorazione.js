const menuList = document.querySelector('[data-subSec="menu-list"]'); //Menu
const recipeDetails = document.querySelector('[data-subSec="recipe-details"]'); //Dettagli ricetta
let res = null; //Lista risultati

/*
    Per fare il sito responsive ho fatto in modo che la ricetta per schermi piccoli sia una finestra "modale"
    quindi inizialmente l'utente vede solo il menu perché con schermi piccoli la ricetta "modale" ha la proprietà 
    display: none;
    Cliccando su una ricetta verrà mostrata la finestra "modale" con tutte le informazioni relative ad essa
    per fare questo aggiungo alla finestra la classe show-flex che contiene la proprietà display: flex che sovrascrive 
    quella con none.
    NB: La finestra "modale" non copre tutta la pagina infatti lascia visibile la navbar e eventualmente una parte di 
    sotto alla finestra.
    Il mio interesse non era una vera e propria finestra modale che si sovrappone a tutto ma semplicemente una scheda che si apre
    appena l'utente clicca su una ricetta senza costringere l'utente a dover scorrere la pagina per vederla
*/
function closeRM(event) //Questa funzione è invocata dal listener del bottone di chiusura della ricetta "modale"
{
    recipeDetails.classList.remove("show-flex"); 
}

function showRecipeDetails(ind) //Si occupa di mostrare i dettagli della ricetta
{   //Come parametro accetta l'indice della ricetta nella lista contenuta in res
    if(ind < 0 || ind > res.length) //Se l'indice non è valido segnalo l'errore nella console e non faccio altro
    {
        console.log("Error: Index out of bounds, index: " + ind);
        return;
    }   
    //Se l'indice è corretto
    recipeDetails.querySelector("img").src= res[ind].image; //Cambio l'immagine
    recipeDetails.querySelector("h2").textContent= res[ind].title; //Cambio il titolo

    let str= "";
    //Carico la lista del tipo di cucina da cui proviene la ricetta con la seguente formattazione: Cucina1, Cucina2, ...
    for(let i=0; i < res[ind].cuisines.length; i++)
    {
        if( i == 0 )
            str += res[ind].cuisines[i];
        else
            str += ", " + res[ind].cuisines[i];
    }

    recipeDetails.querySelector('[data-detail="cuisines"]').textContent = str; //Infine la assegno come testo nell'elemento apposito

    str= "";
    //Applico lo stesso ragionamento per il tipo di pasto 
    for(let i=0; i < res[ind].dishTypes.length; i++)
    {
        if( i == 0 )
            str += res[ind].dishTypes[i];
        else
            str += ", " + res[ind].dishTypes[i];
    }

    recipeDetails.querySelector('[data-detail="dish-types"]').textContent = str;
    //Inserisco Si o No in due elementi di testo per rispondere rispettivamente alle domande Vegano? e Vegetariano?
    recipeDetails.querySelector('[data-detail="vegan"]').textContent = res[ind].vegan? "Si" : "No"; //Vegano
    recipeDetails.querySelector('[data-detail="vegetarian"]').textContent = res[ind].vegetarian? "Si" : "No"; //Vegetariano

    if( res[ind].glutenFree ) //Indico se la ricetta contiene glutine
        recipeDetails.querySelector('[data-detail="gluten"]').textContent= "Senza glutine";
    else
        recipeDetails.querySelector('[data-detail="gluten"]').textContent= "Contiene glutine";

    if( res[ind].dairyFree ) //Indico se la ricetta contiene latticini
        recipeDetails.querySelector('[data-detail="dairy"]').textContent= "Senza latticini";
    else
        recipeDetails.querySelector('[data-detail="dairy"]').textContent= "Contiene latticini";

    //Applicando lo stesso ragionamento fatto in precedenza indico la lista degli ingredienti
    str= "";
    const ingr = res[ind].extendedIngredients;

    for(let i=0; i<ingr.length; i++)
    {
        if(i==0)
            str+= ingr[i].nameClean;
        else 
            str+= ", " + ingr[i].nameClean;
    }

    recipeDetails.querySelector('[data-detail="ingredients"]').textContent= str;
}

function showRecipe(event) //Funzione che mostra la ricetta selezionata
{   //Invoco la funzione che si occupa di mostrare tutte le informazioni della ricetta
    showRecipeDetails( event.currentTarget.dataset.recipeInd ); 
    recipeDetails.classList.add("show-flex"); 
    /*
        Se ho uno schermo di dimensioni normali/grandi non comporta modifiche.
        Se ho uno schermo piccolo allora la ricetta "modale" è nascosta, in questo modo dato che ho selezionato una ricetta
        rendo visibile tale finestra
    */
}

function onJSON(json)
{
    if( json !== null ) //Se non è null allora non si sono verificati errori
    {
        res = json; //Memorizzo i risultati ottenuti

        for( let i=0; i<res.length; i++ ) 
        {   //Per ogni ricetta ottenuta creo una voce nel menu contenente il titolo della ricetta e il suo prezzo associato.
            const listItem = document.createElement("div");
            listItem.classList.add("menu-list-item");
            listItem.dataset.recipeInd= i; //Aggiungo un meta-* che indica l'indice della ricetta nella lista dei risultati
            listItem.addEventListener('click', showRecipe); //Aggiungo il listener per mostrare i dettagli della ricetta
    
            let elem = document.createElement("span");
            elem.textContent= res[i].title; //Titolo
            listItem.appendChild(elem);
    
            elem = document.createElement("span");
            elem.textContent= res[i].pricePerServing; //Prezzo
            listItem.appendChild(elem);
    
            menuList.appendChild(listItem);
        }
    }
    else //Se è null allora si è verificato un problema quindi imposto i risultati a null
        res= null;

    if(res !== null && res.length > 0) //Se non si sono verificati errori ed effettivamente ho ottenuto dei risultati
        showRecipeDetails(0); //Rendo visibili i dettagli della prima ricetta del menu
    else
    {   
        menuList.querySelector("h4").classList.remove("hidden"); //Altrimenti mostro un messaggio di errore
        recipeDetails.classList.add("hidden"); //E nascondo i dettagli della ricetta
    }
}

function onResponse(response)
{
    if(response.ok) //Controllo che non si siano verificati errori
        return response.json();
    else
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onJson
}

fetch("fetch_ristorazione.php").then(onResponse).then(onJSON);

const btnCloseRM= document.querySelector('[data-btn="close-rm"]');  //Pulsante di chiusa della ricetta "modale"
btnCloseRM.addEventListener('click', closeRM); //Aggiungo il listener per la chiusura