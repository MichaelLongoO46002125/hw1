const modalBooking= document.querySelector('[data-modal="booking"]');
const msgError= modalBooking.querySelector('[data-modal-msg="error"]');
const emailError= modalBooking.querySelector('[data-modal-msg="email_error"]');
const email= modalBooking.querySelector('[data-modal-in="email"]');
const emailForm= document.forms["reg-email-form"];
let sendReq=false;  //Indica se voglio effettuare la richiesta di prenotazione (se si allora conterrà il numero della camera)

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
    else //Abilito l'invio della richiesta di prenotazione e verifico l'email
    {   
        sendReq= event.currentTarget.dataset.roomNumber;
        checkEmail();
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
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onJSON
}

function onEmailJSON(json)
{
    if(json === null)
    {
        sendReq=false;
        return; //Si è verificato un errore
    }

    if(!json.ok) //Se l'email non è registrata
    {
        emailError.textContent = "Email non registrata!";
        emailError.classList.remove("hidden"); 
        sendReq=false;
        emailForm.email.value= email.value.toLowerCase();
        emailForm.classList.remove("hidden");
        return;
    }
    else //Email già esistente
    {
        emailError.classList.add("hidden"); 
        if(sendReq) //Se != false/null significa che voglio effettuare la richiesta di prenotazione
        {
            const checkIn = modalBooking.querySelector('[data-modal-in="check_in"]').value;
            const checkOut = modalBooking.querySelector('[data-modal-in="check_out"]').value;

            fetch("fetch_prenota.php?room=" + sendReq +
                  "&check_in="+encodeURIComponent(checkIn)+
                  "&check_out="+encodeURIComponent(checkOut)+
                  "&email="+encodeURIComponent(email.value.toLowerCase())
            ).then(onBookingResponse).then(onBookingJSON);
            sendReq=false;
        }
    }
}

function checkEmail() 
{
    emailForm.classList.add("hidden");
    if(email.value.length == 0)
    {
        emailError.textContent = "L'email non può essere vuota!";
        emailError.classList.remove("hidden"); 
        sendReq=false;
        return;
    }
    else if(!/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
             .test(email.value.toLowerCase())
    ) //Se l'email non ha un formato valido
    {
        emailError.textContent = "Email non valida!";
        emailError.classList.remove("hidden"); 
        sendReq=false;
        return;
    }

    fetch("check_email.php?email=" + encodeURIComponent(email.value.toLowerCase())).then(onBookingResponse).then(onEmailJSON);
}

email.addEventListener("blur", checkEmail);