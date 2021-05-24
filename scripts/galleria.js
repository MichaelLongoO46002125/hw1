const gallerySec= document.querySelector('[data-sec="gallery-sec"]'); //Sezione della galleria 
const modalSec= document.querySelector('[data-sec="modal-sec"]'); //Finestra modale
let results= null; //Lista dei risultati che ottengo tramite la fetch

function closeModal(event) 
{
    modalSec.classList.add("hidden"); //nascondo la modale
    modalSec.innerHTML=''; //La svuoto
    document.body.classList.remove("no-scroll"); //rendo body nuovamente scorrevole
}

function openModal(event)
{
    const ind= event.currentTarget.dataset.resIND;
    if( ind < 0 || ind >= results.length ) //Verifico che l'indice ottenuto non sia fuori dal range di results
        return; //in questo caso non fa nulla

    modalSec.style.top= window.pageYOffset + "px"; //Modifico l'offset della finestra modale all'offset della pagina
    modalSec.classList.remove("hidden"); //Rendo visibile la finestra
    document.body.classList.add("no-scroll"); //Rendo non scorrevole il body per non avere la barra di scorrimento

    let elem= document.createElement("img"); //Creo l'immagine 
    elem.src= results[ind].image;
    modalSec.appendChild(elem);

    elem= document.createElement("p"); //Creo la descrizione
    elem.textContent= results[ind].description;
    modalSec.appendChild(elem);
}

function onJson(json)
{   //Controllo se si è verificato un errore (null) e anche l'assenza di risultati nonostante sia andato tutto bene
    if(json === null || json.length < 1) 
    {
        gallerySec.querySelector("h1").classList.remove("hidden"); //In tal caso rendo visibile il messaggio di errore.
        return;
    }

    results= json; //Se è andato tutto bene e ho risultati li memorizzo in results
    
    for(let i=0; i<results.length; i++) //Per ogni risultato
    {
        const cont= document.createElement("div"); //Creo un contenitore che contiene i prossimi elementi
        const contImg= document.createElement("div"); //Contenitore che conterrà l'immagine
        cont.appendChild(contImg);

        let elem= document.createElement("img");
        elem.src= results[i].image; 
        contImg.appendChild(elem); 

        elem= document.createElement("p"); //Creo il paragrafo che conterrà al descrizione
        if( results[i].description !== null ) //Se è presente una descrizione
        { 
            if(results[i].description.length > 100) //Se la descrizione ha più di 100 caratteri
            {   //Allora non li mostro tutti ma solo dal primo carattere al 97° (range 0-96) in particolare mostro
                elem.textContent = results[i].description.substring(0,97) + "..."; //testo...
                const textBtn= document.createElement("span"); //Creo uno span che farà da pulsante "Leggi tutto"
                textBtn.textContent= "Leggi tutto";
                textBtn.addEventListener("click", openModal); //Aggiungo il listener per aprire la finestra modale
                textBtn.dataset.resIND= i; //Associo un data-* che indica l'indice di tale elemento in results
                elem.appendChild(textBtn);
            }
            else //Se ha al massimo 100 caratteri allora mostro semplicemente la descrizione
                elem.textContent = results[i].description;
        }
        else //Se non è presente una descrizione lo notifico con un messaggio
            elem.textContent= "Descrizione non disponibile";
        cont.appendChild(elem);

        gallerySec.appendChild(cont);
    }
}

function onResponse(response)
{
    console.log(response);
    if(response.ok) //Controllo che non si siano verificati errori
        return response.json();
    else
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onJson
}

fetch("fetch_galleria.php").then(onResponse).then(onJson);

//Aggiungo il listenere per la chiusura della finestra modale
modalSec.addEventListener("click", closeModal);