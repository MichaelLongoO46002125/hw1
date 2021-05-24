const modalBooking= document.querySelector('[data-modal="booking"]');
const msgError= modalBooking.querySelector('[data-modal-msg="error"]');

function closeModalBooking()
{
    modalBooking.classList.add("hidden");
    document.body.classList.remove("no-scroll"); 
}

function openModalBooking(event)
{
    msgError.classList.add("hidden");
    modalBooking.classList.remove("hidden");
    modalBooking.style.top= window.pageYOffset + "px";
    document.body.classList.add("no-scroll"); 
    let room = null;

    for(res of results)
    {
        if(res.roomNumber === event.currentTarget.dataset.roomNumber)
        {
            room= res;
            break;
        }
    }

    if( room !== null )
    {
        const mymodal = modalBooking.querySelector("div");
        mymodal.querySelector("h3").textContent = room.roomType + " " + room.accomodation;
        mymodal.querySelector("span").textContent = "Tariffa per notte: " + room.nightlyFee + "€";
        mymodal.querySelector('[data-modal-in="check_in"]').value= searchForm.check_in.value;
        mymodal.querySelector('[data-modal-in="check_out"]').value= searchForm.check_out.value;
        mymodal.querySelector('[data-modal-in="close"]').addEventListener("click", closeModalBooking);
        mymodal.querySelector('[data-modal-in="submit"]').dataset.roomNumber = room.roomNumber;
        mymodal.querySelector('[data-modal-in="submit"]').addEventListener("click", checkModalBooking);
    }
    else //Errore
    {
        closeModalBooking();
        openModalError("Si è verificato un errore!");
    }
}

function checkModalBooking(event)
{
    msgError.classList.add("hidden");

    let error= "";
    const today= new Date();
    const strToday = today.getFullYear() + "-" + //trasformo il valore di today in una stringa del formato YYYY-MM-DD
                    ((today.getMonth()+1) < 10 ? ("0" + (today.getMonth()+1)) : (today.getMonth()+1) )
                    + "-" + today.getDate();
    
    const checkIn = modalBooking.querySelector('[data-modal-in="check_in"]').value;
    const checkOut = modalBooking.querySelector('[data-modal-in="check_out"]').value;

    if(checkIn < strToday)
        error= "Data di check-in non valida!";

    if(checkIn >= checkOut)
        error+= (error !== "" ? "\n" : "") + "La data di check-out deve essere maggiore di quella di check-in!";

    if(error !== "")
    {
        msgError.textContent = error;
        msgError.classList.remove("hidden");
    }
    else
    {
        fetch("fetch_prenota.php?room=" + event.currentTarget.dataset.roomNumber +
              "&check_in="+encodeURIComponent(checkIn)+
              "&check_out="+encodeURIComponent(checkOut)
        ).then(onBookingResponse).then(onBookingJSON);
    }
}


function onBookingJSON(json)
{
    if( json === null )  //Errore
    {
        msgError.textContent = "Si è verificato un errore!";
        msgError.classList.remove("hidden");
    }
    else if( json.ok !== true )
    {
        msgError.textContent = "La camera non è disponibile per il periodo selezionato!";
        msgError.classList.remove("hidden");
    }
    else
    {
        closeModalBooking();
        openModalMsg("Camera prenotata con successo!");
    }

}

function onBookingResponse(response)
{
    if(response.ok) //Controllo che non si siano verificati errori
        return response.json();
    else
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onBookingJSON
}