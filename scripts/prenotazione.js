const showRoomSec= document.querySelector('[data-sub-sec="show-room"]');
const searchForm= document.forms["search-form"];
const modalError= document.querySelector('[data-modal="error"]');
const modalMsg= document.querySelector('[data-modal="message"]');
const sidebar= document.querySelector('[data-sub-sec="search-side-bar"]');
const sidebarCloseBtn= document.querySelector('[data-sb-btn="sidebar-btn-close"]');
const sidebarOpenBtn= document.querySelector('[data-sb-btn="sidebar-btn-open"]');

let results = null;

function closeSidebar()
{
    sidebar.classList.add("hidden");
    sidebarOpenBtn.classList.remove("hidden");
    sidebarOpenBtn.classList.remove("start-hidden-visible");
}

function openSidebar()
{
    sidebar.classList.remove("hidden");
    sidebar.classList.remove("start-visible-hidden");
    sidebarOpenBtn.classList.add("hidden");
}

function getPhotos(roomID)
{
    for(res of results)
    {
        if(res.roomNumber == roomID)
            return res.photos;
    }

    return null;
}

function nextPhoto(event)
{
    const roomID= event.currentTarget.dataset.nextPhotoRoomId;
    const photos = getPhotos(roomID);

    if(photos === null)
        return; //ERRORE

    //Cerco la foto attualmente visibile!
    let index = -1;

    for(let i=0; i<photos.length; i++)
    {
        const img= event.currentTarget.parentNode.querySelector('[data-photo-room-id="'+ roomID + i +'"]');

        if(!img.classList.contains("hidden"))
        {
            index = i;
            break;
        }
    }

    if(index != -1)
    {
        if( index < photos.length-1 )
        {
            event.currentTarget.parentNode.querySelector('[data-photo-room-id="'+ roomID + index +'"]').classList.add("hidden");
            index++;
            event.currentTarget.parentNode.querySelector('[data-photo-room-id="'+ roomID + index +'"]').classList.remove("hidden");
            event.currentTarget.parentNode.querySelector(".num-photo").textContent = (index+1) + "/" + photos.length;
        }
    }
}

function prevPhoto(event)
{
    const roomID= event.currentTarget.dataset.prevPhotoRoomId;
    const photos = getPhotos(roomID);

    if(photos === null)
        return; //ERRORE

    //Cerco la foto attualmente visibile!
    let index = -1;

    for(let i=0; i<photos.length; i++)
    {
        const img= event.currentTarget.parentNode.querySelector('[data-photo-room-id="'+ roomID + i +'"]');
 
        if(!img.classList.contains("hidden"))
        {
            index = i;
            break;
        }
    }
    
    if(index != -1)
    {
        if( index > 0 )
        {
            event.currentTarget.parentNode.querySelector('[data-photo-room-id="'+ roomID + index +'"]').classList.add("hidden");
            index--;
            event.currentTarget.parentNode.querySelector('[data-photo-room-id="'+ roomID + index +'"]').classList.remove("hidden");
            event.currentTarget.parentNode.querySelector(".num-photo").textContent = (index+1) + "/" + photos.length;
        }
    } //else ERRORE
}

function createRoom(room)
{
    const roomCont= document.createElement("div");
    roomCont.classList.add("room");

    const imgCont= document.createElement("div");
    imgCont.classList.add("img-container");
    roomCont.appendChild(imgCont);

    if(room.photos.length > 0)
    {
        let img = document.createElement("img"); 
        img.src= room.photos[0];
        img.dataset.photoRoomId = room.roomNumber + "0";
        imgCont.appendChild(img);

        if(room.photos.length > 1 )
        {
            for(let i=1; i<room.photos.length; i++)
            {
                img = document.createElement("img"); 
                img.src= room.photos[i];
                img.classList.add("hidden");
                img.dataset.photoRoomId = room.roomNumber + i;
                imgCont.appendChild(img);
            }

            let btn= document.createElement("div");
            btn.classList.add("next-photo");
            btn.addEventListener('click', nextPhoto);
            btn.dataset.nextPhotoRoomId = room.roomNumber;
            
            imgCont.appendChild(btn);

            btn= document.createElement("div");
            btn.classList.add("prev-photo");
            btn.addEventListener('click',prevPhoto);
            btn.dataset.prevPhotoRoomId = room.roomNumber;
            imgCont.appendChild(btn);

            const numPhoto= document.createElement("div");
            numPhoto.classList.add("num-photo");
            numPhoto.textContent = "1/" + room.photos.length;
            imgCont.appendChild(numPhoto);
        }
    }
    else
    {
        const span= document.createElement("span");
        span.textContent = "Foto non disponibile!";
        imgCont.appendChild(span);
    }
    
    let elem= document.createElement("h3");
    elem.textContent = room.roomType + " " + room.accomodation;
    roomCont.appendChild(elem);

    const twoColumnCont = document.createElement("div");
    twoColumnCont.classList.add("two-col-cont");
    roomCont.appendChild(twoColumnCont);

    const firstColumn = document.createElement("div");
    firstColumn.classList.add("first-col");
    twoColumnCont.appendChild(firstColumn);

    const secondColumn = document.createElement("div");
    secondColumn.classList.add("second-col");
    twoColumnCont.appendChild(secondColumn);

    //Descrizione
    elem= document.createElement("p");
    elem.textContent = room.description;
    secondColumn.appendChild(elem);

    //Tariffa per notte
    elem= document.createElement("span");
    elem.textContent = "Tariffa per notte: " + room.nightlyFee + "€";
    firstColumn.appendChild(elem);
    
    //Numero di persone
    let iconCont= document.createElement('div');
    iconCont.classList.add("icon-cont");

    elem= document.createElement("div");
    elem.classList.add("icon");
    elem.classList.add("persons-icon");
    iconCont.appendChild(elem);
        
    elem= document.createElement("span");
    elem.textContent = "Per " + room.personNumber + " persone";
    iconCont.appendChild(elem);

    firstColumn.appendChild(iconCont);
    
    //Letti matrimoniali
    iconCont= document.createElement('div');
    iconCont.classList.add("icon-cont");

    elem= document.createElement("div");
    elem.classList.add("icon");
    elem.classList.add("matrimonial-bed-icon");
    iconCont.appendChild(elem);
        
    elem= document.createElement("span");
    elem.textContent = room.matrimonialBed + " letti matrimoniali";
    iconCont.appendChild(elem);

    firstColumn.appendChild(iconCont);

    //Letti singoli
    iconCont= document.createElement('div');
    iconCont.classList.add("icon-cont");

    elem= document.createElement("div");
    elem.classList.add("icon");
    elem.classList.add("single-bed-icon");
    iconCont.appendChild(elem);
        
    elem= document.createElement("span");
    elem.textContent = room.singleBed + " letti singoli";
    iconCont.appendChild(elem);

    firstColumn.appendChild(iconCont);

    //Metri quadri
    iconCont= document.createElement('div');
    iconCont.classList.add("icon-cont");

    elem= document.createElement("div");
    elem.classList.add("icon");
    elem.classList.add("sqm-icon");
    iconCont.appendChild(elem);
        
    elem= document.createElement("span");
    elem.textContent = room.sqm + " m²";
    iconCont.appendChild(elem);

    firstColumn.appendChild(iconCont);

    if(room.wifi)
    {
        iconCont= document.createElement('div');
        iconCont.classList.add("icon-cont");

        elem= document.createElement("div");
        elem.classList.add("icon");
        elem.classList.add("wifi-icon");
        iconCont.appendChild(elem);
        
        elem= document.createElement("span");
        elem.textContent = "WiFi" + (room.wifiFree ? " Free": "");
        iconCont.appendChild(elem);

        firstColumn.appendChild(iconCont);
    }

    if(room.minibar)
    {
        iconCont= document.createElement('div');
        iconCont.classList.add("icon-cont");

        elem= document.createElement("div");
        elem.classList.add("icon");
        elem.classList.add("minibar-icon");
        iconCont.appendChild(elem);
        
        elem= document.createElement("span");
        elem.textContent = "Minibar";
        iconCont.appendChild(elem);

        firstColumn.appendChild(iconCont);
    }

    if(room.soundproofing)
    {
        iconCont= document.createElement('div');
        iconCont.classList.add("icon-cont");

        elem= document.createElement("div");
        elem.classList.add("icon");
        elem.classList.add("soundproofing-icon");
        iconCont.appendChild(elem);
        
        elem= document.createElement("span");
        elem.textContent = "Insonorizzazione";
        iconCont.appendChild(elem);

        firstColumn.appendChild(iconCont);
    }

    if(room.swimmingpool)
    {
        iconCont= document.createElement('div');
        iconCont.classList.add("icon-cont");

        elem= document.createElement("div");
        elem.classList.add("icon");
        elem.classList.add("swimming-pool-icon");
        iconCont.appendChild(elem);
        
        elem= document.createElement("span");
        elem.textContent = "Piscina";
        iconCont.appendChild(elem);

        firstColumn.appendChild(iconCont);
    }

    if(room.privateBathroom)
    {
        iconCont= document.createElement('div');
        iconCont.classList.add("icon-cont");

        elem= document.createElement("div");
        elem.classList.add("icon");
        elem.classList.add("private-bathroom-icon");
        iconCont.appendChild(elem);
        
        elem= document.createElement("span");
        elem.textContent = "Bagno privato";
        iconCont.appendChild(elem);

        firstColumn.appendChild(iconCont);
    }

    if(room.airConditioning)
    {
        iconCont= document.createElement('div');
        iconCont.classList.add("icon-cont");

        elem= document.createElement("div");
        elem.classList.add("icon");
        elem.classList.add("air-conditioning-icon");
        iconCont.appendChild(elem);
        
        elem= document.createElement("span");
        elem.textContent = "Aria condizionata";
        iconCont.appendChild(elem);

        firstColumn.appendChild(iconCont);
    }

    const button= document.createElement("button");
    button.classList.add("input");
    button.classList.add("confirm");
    button.textContent= "PRENOTA";
    button.dataset.roomNumber = room.roomNumber;
    button.addEventListener("click", openModalBooking);
    roomCont.appendChild(button);

    showRoomSec.appendChild(roomCont);
}

function openModalMsg(msg)
{
    modalMsg.querySelector("p").textContent= msg;
    modalMsg.classList.remove("hidden");
    modalMsg.style.top= window.pageYOffset + "px";
    document.body.classList.add("no-scroll");
}

function closeModalMsg()
{
    modalMsg.classList.add("hidden");
    document.body.classList.remove("no-scroll");
}

function openModalError(error)
{
    modalError.querySelector("p").textContent= error;
    modalError.classList.remove("hidden");
    modalError.style.top= window.pageYOffset + "px";
    document.body.classList.add("no-scroll");
}

function closeModalError()
{
    modalError.classList.add("hidden");
    document.body.classList.remove("no-scroll");
}

function validateSearch(event)
{
    event.preventDefault();
    closeModalError();

    let error= "";
    const today= new Date();
    const strToday = today.getFullYear() + "-" + //trasformo il valore di today in una stringa del formato YYYY-MM-DD
                    ((today.getMonth()+1) < 10 ? ("0" + (today.getMonth()+1)) : (today.getMonth()+1) )
                    + "-" + today.getDate();

    if(searchForm.check_in.value < strToday)
        error= "Data di check-in non valida!";

    if(searchForm.check_in.value >= searchForm.check_out.value)
        error+= (error !== "" ? "\n" : "") + "La data di check-out deve essere maggiore di quella di check-in!";
    //Se il testo è nonvuoto e non è nel formato 1234 o 1234.56 
    if(!/^\d*(\.\d{1,2})?$/.test(searchForm.min_fee.value) || !/^\d*(\.\d{1,2})?$/.test(searchForm.max_fee.value))
        error+= (error !== "" ? "\n" : "") + "Tariffa non valida!";
    else if(searchForm.min_fee.value.length > 0 && 
            searchForm.max_fee.value.length > 0 && 
            parseFloat(searchForm.min_fee.value) >= parseFloat(searchForm.max_fee.value)
        )
        error+= (error != "" ? "\n" : "") + "La tariffa minima deve essere minore di quella massima!";

    if(error !== "")
    {
        openModalError(error);
    }
    else
        fetch("fetch_showroom.php?check_in="+encodeURIComponent(searchForm.check_in.value)
                +"&check_out="+encodeURIComponent(searchForm.check_out.value)
                +"&persons_num="+searchForm.persons_num.value
                +"&matrimonial="+searchForm.matrimonial.checked
                +"&single="+searchForm.single.checked
                +"&min_fee="+encodeURIComponent(searchForm.min_fee.value)
                +"&max_fee="+encodeURIComponent(searchForm.max_fee.value)
        ).then(onResponse).then(onJSON);
}

function onJSON(json)
{
    if( json === null || json.ok !== true )  //Errore
    {
        showRoomSec.innerHTML= "";
        const msgCont= document.createElement("div");
        msgCont.classList.add("error");
        const msg= document.createElement("span");
        msg.textContent= "Nessuna camera trovata!";
        msgCont.appendChild(msg);
        showRoomSec.appendChild(msgCont);
        return;
    }

    if(results !== null)
    {
        showRoomSec.innerHTML= "";
    }

    results= json.results;
    
    for(let room of results)
        createRoom(room);
}

function onResponse(response)
{
    if(response.ok) //Controllo che non si siano verificati errori
        return response.json();
    else
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onJSON
}

fetch("fetch_showroom.php").then(onResponse).then(onJSON);

searchForm.addEventListener("submit", validateSearch);

modalError.addEventListener("click", closeModalError);
modalMsg.addEventListener("click", closeModalMsg);
sidebarCloseBtn.addEventListener("click", closeSidebar);
sidebarOpenBtn.addEventListener("click", openSidebar);