const contentSec = document.querySelector('section[data-sec="content-sec"]'); //Sezione contenuti
const favSec= document.querySelector('section[data-sec="fav-sec"]');          //Sezione preferiti
const favList= favSec.querySelector('.fav-list');                             //Lista preferiti
const contentMsg= document.querySelector('[data-cont-msg="msg"]');            //Messaggio 0 contenuti
const contNext= document.querySelector('[data-btn="cont-next"]');             //Pulsante per visualizzare il prossimo contenuto
const favNext= document.querySelector('[data-btn="fav-next"]');               //Pulsante per visualizzare il prossimo preferito
const searchBar= document.forms["search-form"].search_bar;                    //Barra di ricerca
const contentPageSize=4;
const favPageSize=3;

let contents = null;      //Lista dei contenuti
let viewableContent = []; //ID dei contenuti visualizzabili 
let contentsOffset = 0;   //Indice per la lista viewableContent che indica il primo contenuto visualizzato
let otherCont= true;      //Indica se è possibile richiedere ulteriori contenuti

let favorites = null;     //Lista dei preferiti
let viewableFav = [];     //ID dei contenuti aggiunti tra i preferiti
let favOffset = 0;        //Indice per la lista viewableFav che indica il primo preferito visualizzato
let otherFav= true;       //Indica se è possibile richiedere ulteriori preferiti

let searchTitle= "";      //Indica l'ultimo termine di ricerca utilizzato

//Funzioni e Listener

function searchByID(ID) {
    for(let cont of contents) //Cerco il contenuto avente il dato ID
    {
        if(cont.id === ID)
            return cont; //Restituisco l'oggetto che rappresenta il contenuto
    }

    return null; //Se arrivo qui significa che non l'ho trovato, restituisco null per indicare errore
                 //basta modificare un id con ispeziona elemento per arrivare a questo punto
}

function removeContent(cont)
{
    const tmp= document.querySelector('[data-content-id="' + cont.id + '"]');
    if(tmp !== null) 
        tmp.remove();
}

function removeFavorite(id)
{
    //Cerco la posizione dell'id del preferito nella lista dei preferiti visualizzabili
    const i=viewableFav.indexOf(id);

    if(i === -1) //Se non lo trova allora o si è verificato un errore oppure è stato rimosso dai preferiti tramite sezione contenuti
        return;  //in quest'ultimo caso potrebbe accadere che il contenuto non è ancora stato caricato nella sezione preferiti

    //Rimuovo il preferito se è stato caricato e aggiorno la lista dei preferiti (client side)
    const fav= favSec.querySelector('[data-fav-id="' + id +'"]');

    if(fav !== null)
    {
        //Verifico se il preferito rimosso è tra i 3 visibili attualmente!
        //Trovo il limite massimo per la ricerca
        let max= favOffset+3;
        let inPage= false;
        if(favOffset+2 > viewableFav.length-1)
        {
            max= viewableFav.length;
        }

        for(let off= favOffset; off<max; off++)
        {
            if(viewableFav[off] == id)
                inPage= true;
        }

        fav.remove();

        //Verifico se oltre ai preferiti visualizzati ne ho almeno un altro che posso visualizzare e se c'è lo visualizzo
        if(viewableFav.length > favPageSize && inPage)
        {   //Ho almeno un preferito da visualizare, verifico se è adiacente al primo visualizzato
            if(favOffset>0) //Si, quindi lo rendo visibile 
            {
                favList.querySelector('[data-fav-id="' + viewableFav[--favOffset] + '"]')
                    .classList.remove('hidden');
            }
            else //No, allora devo rendere visibile il preferito nascosto adiacente all'ultimo preferito di destra
            {
                favList.querySelector('[data-fav-id="' + viewableFav[favOffset+favPageSize] + '"]')
                    .classList.remove('hidden');
            }
        }

        //Rimuovo dalla lista il preferito 
        viewableFav.splice(i, 1);
        favorites.splice(i, 1);

        if( viewableFav.length > 0 && viewableFav.length < 3 && otherCont)
        {
            fetch("fetch_favorite.php?num=1&offset="+viewableFav.length).then(onResponse).then(onLoadFavoriteJSON);
        }
        else if(viewableFav.length === 0) //Verifico se ho ancora dei preferiti altrimenti nascondo la sezione
        {
            if(otherFav)
            {
                fetch("fetch_favorite.php").then(onResponse).then(onLoadFavoriteJSON);
            }
            else
                favSec.classList.add('hidden');
        }
    }
}

function resetContents()
{
    if(contents !== null)
    {
        while(contents.length !== 0)
        {
            removeContent(contents[0]);
            contents.shift();
        }
        contents = null;
        viewableContent= []; //Resetto la lista che indica quali contenuti posso visualizzare
        contentsOffset= 0;   //Resetto l'offset
    }
}

function createContent(cont)
{
    const divContent= document.createElement('div');
    divContent.classList.add('content-col'); //<div class="content-col">
    if(viewableContent.length >= contentPageSize) //Dato che voglio visualizzare massimo "contentPageSize" elementi per volta
        divContent.classList.add('hidden'); //se ne ho già caricati almeno 4(contentPageSize) gli altri li rendo invisibili
    divContent.dataset.contentId= cont.id; //data-content-id="XXXX"
    contentSec.appendChild(divContent);

    let elem= document.createElement('img');
    elem.src= cont.image; //<img src="XXXX">
    divContent.appendChild(elem);

    const divContentText= document.createElement('div');
    divContentText.classList.add('content-text'); //<div class="content-text">
    divContent.appendChild(divContentText);

    const divTagDate= document.createElement('div'); 
    divContentText.appendChild(divTagDate);

    elem= document.createElement('span');
    elem.classList.add('content-tag');
    let tags= "";

    for(let i=0; i<cont.tags.length; i++)
    {
        if(i==0)
            tags= cont.tags[i];
        else
            tags+=", " + cont.tags[i];
    }

    elem.textContent= tags;
    divTagDate.appendChild(elem); //<span class="content-tag">TAG</span>

    elem= document.createElement('span');
    elem.textContent= cont.date;
    divTagDate.appendChild(elem); //<span>DD/MM/YYYY</span>

    elem= document.createElement('h4');
    elem.textContent= cont.title;
    divContentText.appendChild(elem); //<h4>Titolo</h4>

    elem= document.createElement('p');
    elem.textContent= 'Clicca per mostrare la descrizione...';
    elem.addEventListener('click', showDescription);
    divContentText.appendChild(elem); //<p>Descrizione</p>

    elem= document.createElement('div');

    if(cont.isFav !== null)//Se diverso da null allora l'utente è autenticato e può aggiungere/rimuovere 
    {   //il contenuto dai preferiti, altrimenti se non è autenticato non può ne aggiungerlo ne rimuoverlo
        if(cont.isFav == true) //Se è un preferito creo un bottone per torglierlo dai preferiti
        {   //<div class="btn-rem-fav"></div>
            elem.classList.add('btn-rem-fav');
            elem.addEventListener('click', remFav);
        }
        else //Se non è un preferito (false) allora creo un bottone per aggiungerlo ai preferiti
        {   //<div class="btn-add-fav"></div>
            elem.classList.add('btn-add-fav');
            elem.addEventListener('click', addFav);
        }
        divContent.appendChild(elem); 
    }
    viewableContent.push(cont.id);
}

function createFavorite(fav)
{
    if(favSec.classList.contains('hidden'))
        favSec.classList.remove('hidden');

    const favItem = document.createElement('div');
    favItem.classList.add('fav-item');
    if(viewableFav.length >= favPageSize) //Se sto già mostrando "favPageSize" preferiti gli altri che aggiungo li rendo non visibili.
        favItem.classList.add('hidden');
    favItem.dataset.favId= fav.id; 

    let elem = document.createElement('img');
    elem.src= fav.image; //<img src="XXXX">
    favItem.appendChild(elem);

    const divTagDate= document.createElement('div'); 
    favItem.appendChild(divTagDate);

    elem= document.createElement('span');
    elem.classList.add('content-tag');
    let tags= "";

    for(let i=0; i<fav.tags.length; i++)
    {
        if(i==0)
            tags= fav.tags[i];
        else
            tags+=", " + fav.tags[i];
    }

    elem.textContent= tags;
    divTagDate.appendChild(elem); //<span class="content-tag">TAG</span>

    elem= document.createElement('span');
    elem.textContent= fav.date;
    divTagDate.appendChild(elem); //<span>DD/MM/YYYY</span>

    elem= document.createElement('h4');
    elem.textContent= fav.title;
    favItem.appendChild(elem); //<h4>Titolo</h4>

    elem= document.createElement('div');
    elem.classList.add('btn-rem-fav');
    elem.addEventListener('click', remFav);
    favItem.appendChild(elem); //<div class="btn-rem-fav"></div>

    favList.appendChild(favItem); //<div class="fav-item" data-fav-id="XXXX"></div>
    viewableFav.push(fav.id);
}

function createFavoriteFromContent(cont)
{
    const fav = {
        "date": cont.date,
        "id": cont.id,
        "image": cont.image,
        "tags": cont.tags,
        "title": cont.title
    };
    if(favorites === null)
        favorites = [];
    favorites.push(fav);
}

//Listener per la rimozione/aggiunta dei preferiti

function onRemFavJSON(json)
{
    if(json === null || !json.ok) //Errore/Operazione fallita!
        return; 

    //Prendo il contenuto corrispondente nella sezione dei contenuti
    let cont = contentSec.querySelector('[data-content-id="' + json.id +'"]');

    if(cont !== null) //Se il contenuto tolto dai preferiti è anche un contenuto attualmente caricato aggiorno il pulsante 
    {   //trasformandolo in pulsante per aggiungere il contenuto tra i preferiti
        //Rimuovo il listener per la rimozione dai preferiti

        cont= cont.querySelector('.btn-rem-fav');
        cont.removeEventListener('click', remFav); 
        //Cambio il tipo di pulsante in quello di aggiunta tra i preferiti
        cont.classList.add('btn-add-fav');
        cont.classList.remove('btn-rem-fav');   
        //Aggiungo il listener appropriato
        cont.addEventListener('click', addFav); 
    }

    removeFavorite(json.id);
}

function remFav(event)
{
    //Se l'evento nasce dalla pressione del pulsante di un elemento nella sezione preferiti
    if(event.currentTarget.parentNode.dataset.favId !== undefined)
    {   
        const id= event.currentTarget.parentNode.dataset.favId;
        //Cerco la posizione dell'id del preferito nella lista dei preferiti visualizzabili
        const i=viewableFav.indexOf(id);

        if(i === -1) //Se non lo trova allora si è veririficato un errore e non fa nulla
            return;
        
        //Rimuovo il preferito (server side)
        fetch("rem_favorite.php?id=" + id).then(onResponse).then(onRemFavJSON);
    }
    else //Altrimenti l'evento nasce dalla pressione del pulsante di un elemento nella sezione contenuti
    {
        const id= event.currentTarget.parentNode.dataset.contentId;
        //Cerco il contenuto avente tale ID memorizzato in contents
        const cont= searchByID(id); 

        if( cont === null)  //Se non ho caricato in pagina contenuti con questo id non faccio nulla 
            return; 
        
        //Rimuovo il preferito (server side)
        fetch("rem_favorite.php?id=" + id).then(onResponse).then(onRemFavJSON);
    }
}

function onAddFavJSON(json)
{
    if(json === null || !json.ok) //Errore/Operazione fallita!
        return; 

    //Aggiungo nella sezione preferiti il nuovo contenuto
    const cont= searchByID(json.id);

    if(cont === null)  //Se non ho caricato in pagina contenuti con questo id non faccio nulla (ERRORE)
        return;      
    
    createFavorite(cont);
    createFavoriteFromContent(cont);

    //Prendo il contenuto corrispondente nella sezione dei contenuti
    let contBtn = contentSec.querySelector('[data-content-id="' + json.id +'"]');

    if(contBtn !== null)
    {
        contBtn= contBtn.querySelector('.btn-add-fav');
        //Rimuovo il listener per aggiungerlo tra i preferiti
        contBtn.removeEventListener('click', addFav); 
        //Cambio il tipo di pulsante in quello per rimuoverlo dai preferiti
        contBtn.classList.remove('btn-add-fav');
        contBtn.classList.add('btn-rem-fav');   
        //Aggiungo il listener per rimuoverlo tra i preferiti
        contBtn.addEventListener('click', remFav); 
    }
}

function addFav(event)
{
    const id= event.currentTarget.parentNode.dataset.contentId;

    //Cerco il contenuto avente tale ID memorizzato in contents
    const cont= searchByID(id); 

    if( cont === null)  //Se non ho caricato in pagina contenuti con questo id non faccio nulla (ERRORE)
        return;      
    
    fetch("add_favorite.php?id=" + id).then(onResponse).then(onAddFavJSON);
}

//Listener per i pulsanti prossimo/precedente preferito
function showNextFav(res)
{
    if(res === null) //Se si è verificato un errore non fa nulla
       return;

    //Della lista mostro solo 3(favPageSize) elementi per volta quindi logicamente devo lavorare con una "finestra" 
    //di dimensione 3 e quindi il mio offset non può essere maggiore della lunghezza della lista - 3                                   
    if(favOffset < viewableFav.length-favPageSize) //Se la condizione è falsa => non ho preferiti nascosti a destra
    {   //Nascondo il primo preferito visualizzato
        favList.querySelector('[data-fav-id="' + viewableFav[favOffset] + '"]')
            .classList.add('hidden');
        //Visualizzo il prossimo preferito
        favList.querySelector('[data-fav-id="' + viewableFav[favOffset+favPageSize] + '"]')
            .classList.remove('hidden');

        favOffset++; //Sposto l'offset di 1 posizione a destra (sposto la "finestra" di una posizione a destra)
    }  
}

function onNextFav(){
    //Se sto già visualizzando l'ultimo preferito caricato allora ne richiedo un altro (se possibile)
    if(favOffset >= viewableFav.length-favPageSize && otherFav)
    {
        fetch("fetch_favorite.php?offset=" + favorites.length)  
            .then(onResponse).then(onLoadFavoriteJSON).then(showNextFav);  
    }
    else
        showNextFav(true);
}

function onPrevFav(){
    if(favOffset>0) //Controllo se prima del primo preferito visualizzato ci sono preferiti nascosti che lo precedono
    {
        favOffset--; //Sposto l'offset di una posizione a sinistra
        //Nascondo il preferito visualizzato più a destra
        favList.querySelector('[data-fav-id="' + viewableFav[favOffset+favPageSize] + '"]')
            .classList.add('hidden');
        //Visualizzo il preferito nascosto che precede l'attuale primo preferito
        favList.querySelector('[data-fav-id="' + viewableFav[favOffset] + '"]')
            .classList.remove('hidden');
    }
}

//Listener per i pulsanti prossimo/precedente contenuto
function showNextCont(res)
{
    if(res === null) //Se si è verificato un errore non fa nulla
        return;

    // Della lista mostro solo 4(contentPageSize) contenuti per volta quindi logicamente devo lavorare con una "finestra" 
    // di dimensione 4 e quindi il mio offset non può essere maggiore della lunghezza della lista - 4                             
    if(contentsOffset < viewableContent.length-contentPageSize) //Se la condizione è falsa => non ho contenuti nascosti a destra
    {   //Nascondo il primo contenuto visualizzato
        contentSec.querySelector('[data-content-id="' + viewableContent[contentsOffset] + '"]')
            .classList.add('hidden');
        //Visualizzo il prossimo contenuto
        contentSec.querySelector('[data-content-id="' + viewableContent[contentsOffset+contentPageSize] + '"]')
            .classList.remove('hidden');

        contentsOffset++; //Sposto l'offset di 1 posizione a destra (sposto la "finestra" di una posizione a destra)
    } 
}

function onNextCont(){
    //Se sto già visualizzando l'ultimo contenuto caricato allora ne richiedo un altro (se possibile)
    if(contentsOffset >= viewableContent.length-contentPageSize && otherCont)
    {
        //Se sto visualizzando contenuti caricati tramite la barra di ricerca (searchTitle != "")
        if(searchTitle != "")
            fetch("fetch_content.php?title=" + encodeURIComponent(searchTitle) + "&offset=" + contents.length)  
                .then(onResponse).then(onLoadContentJSON).then(showNextCont);  
        else
            fetch("fetch_content.php?offset=" + contents.length)  
                .then(onResponse).then(onLoadContentJSON).then(showNextCont);  
    }
    else
        showNextCont(true);
}

function onPrevCont(){
    if(contentsOffset>0)//Controllo se prima del primo contenuto visualizzato ci sono contenuti nascosti che lo precedono
    {
        contentsOffset--; //Sposto l'offset di una posizione a sinistra
        //Nascondo il contenuto visualizzato più a destra
        contentSec.querySelector('[data-content-id="' + viewableContent[contentsOffset+contentPageSize] + '"]')
            .classList.add('hidden');
        //Visualizzo il contenuto nascosto che precede l'attuale primo visualizzato
        contentSec.querySelector('[data-content-id="' + viewableContent[contentsOffset] + '"]')
            .classList.remove('hidden');
    }
}

//Listener per le descrizioni
function showDescription(event)
{
    //Pendo l'ID associato al contenuto e cerco tale ID nel file contents.js
    const cont= searchByID(event.currentTarget.parentNode.parentNode.dataset.contentId);

    if(cont === null) 
    {
        console.log("Errore");
        return;
    }

    event.currentTarget.textContent= cont.description;

    event.currentTarget.removeEventListener('click', showDescription);
    event.currentTarget.addEventListener('click', hideDescription);
}

function hideDescription(event)
{
    event.currentTarget.textContent= 'Clicca per mostrare la descrizione...';

    event.currentTarget.removeEventListener('click', hideDescription);
    event.currentTarget.addEventListener('click', showDescription);
}

//Listener per la barra di ricerca
function searchContents(event)
{   
    event.preventDefault();
    const search= (searchBar.value.trim()).toUpperCase();

    if( search == "" && (search != searchTitle) ) //Se ho resettato il contenuto della barra di ricerca
    {
        contentMsg.classList.add("hidden"); //Nascondo il messaggio di errore nel caso sia visualizzato!
        otherCont= true; //Resetto otherCont indicando che posso richiedere ulteriori contenuti
        resetContents(); //Rimuovo tutti i contenuti caricati attualmente
        searchTitle= ""; //Indico che il termine di ricerca attuale è ""
        fetch("fetch_content.php").then(onResponse).then(onLoadContentJSON); //Richiedo nuovi contenuti
    } 
    else if(search != searchTitle) //Se cerco un nuovo titolo
    {
        contentMsg.classList.add("hidden"); //Nascondo il messaggio di errore nel caso sia visualizzato!
        otherCont= true; //Resetto otherCont indicando che posso richiedere ulteriori contenuti
        resetContents(); //Rimuovo tutti i contenuti caricati attualmente
        searchTitle= search; //Indico che il termine di ricerca attuale è il valore nel campo di ricerca
        fetch("fetch_content.php?title=" + encodeURIComponent(search)) //Eseguo la richiesta chiedendo la prima pagina di risultati
            .then(onResponse).then(onLoadContentJSON);      
    }
    //Altrimenti sto cercando sempre lo stesso titolo, quindi non faccio nulla!
}

/*
    function onLoadContentJSON(json) 
    Restituisce:
    - null  => Errore!
    - false => Non ci sono altri contenuti caricabili!
    - true  => Contenuti caricati con successo!
*/
function onLoadContentJSON(json) 
{   console.log(json);
    if( json === null ) //Errore
    {   
        //Se non ho caricato contenuti visualizzo il messaggio di errore all'utente
        if(contents === null)
            contentMsg.classList.remove('hidden');

        return null;
    }

    if(json.ok === false)  //Se non ho contenuti da caricare 
    {   //Verifico se non ho già caricato dei contenuti
        if(contents === null) //In questo caso segnalo con un messaggio
            contentMsg.classList.remove('hidden');

        otherCont= false; //Indico che non è possibile richiedere ulteriori contenuti
        return false;
    }

    if( contents !== null ) //Se ho già caricato dei contenuti in precedenza unisco le due liste di contenuti
    {   
        for(let cont of json.contents)
        {
            if(searchByID(cont.id) === null) //Se non è un duplicato 
            {
                contents.push(cont); 
                createContent(cont);
            }    
        }
    }
    else //Altrimenti li carico per la prima volta
    {   
        contents= json.contents;

        for(let cont of contents)
        {
            createContent(cont);
        }
    }

    return true;
}

/*
    function onLoadFavoriteJSON(json)
    Restituisce:
    - null  => Errore!
    - false => Non ci sono altri contenuti caricabili!
    - true  => Contenuti caricati con successo!
*/

function onLoadFavoriteJSON(json) 
{
    if( json === null || json.ok === null )  //Errore
        return null; 

    if(!json.ok) //Non ho trovato altri preferiti
    {   
        if(favorites == null) //Se non ho già caricato dei preferiti
            favSec.classList.add('hidden');
        otherFav= false; //Indico che non è possibile richiedere ulteriori preferiti 
        return false;
    }

    if(json.favorites.length<3)
        otherFav = false;

    if( favorites !== null ) //Se ho già caricato dei preferiti in precedenza unisco le due liste
    {
        for(let fav of json.favorites)
        {
            if(viewableFav.indexOf(fav.id) === -1) //Se non è un duplicato 
            {   
                favorites.push(fav); 
                createFavorite(fav);
            }    
        }
    }
    else
    {
        favorites= json.favorites;
        for(let fav of favorites)
            createFavorite(fav);
    }

    return true;
}

function onResponse(response)
{   
    if(response.ok) //Controllo che non si siano verificati errori
        return response.json();
    else
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onJson
}

//MAIN

fetch("fetch_content.php").then(onResponse).then(onLoadContentJSON);
fetch("fetch_favorite.php").then(onResponse).then(onLoadFavoriteJSON);

//Aggiungo il listener al form della search bar
document.forms["search-form"].addEventListener('submit', searchContents);


//Aggiungo i listener per i pulsati prossimo/precedente contenuto
contNext.addEventListener('click', onNextCont);
document.querySelector('[data-btn="cont-prev"]').addEventListener('click', onPrevCont);


//Aggiungo i listener per i pulsati prossimo/precedente preferito
favNext.addEventListener('click', onNextFav);
document.querySelector('[data-btn="fav-prev"]').addEventListener('click', onPrevFav);