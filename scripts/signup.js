const regform= document.forms["signup-form"];

function validateName()
{
    if(regform.name.value.length == 0)  
    {
        regform.querySelector('[data-error="name"]').classList.remove("hidden");
        return false;   
    }

    regform.querySelector('[data-error="name"]').classList.add("hidden");
    return true;
}

function validateLastName()
{
    if(regform.last_name.value.length == 0)
    {
        regform.querySelector('[data-error="last_name"]').classList.remove("hidden");
        return false;   
    }

    regform.querySelector('[data-error="last_name"]').classList.add("hidden");
    return true;
}

function validateTel()
{
    if(!/^[+]\d{1,15}$/.test(regform.tel.value)) //Se non rispetta la sintassi: +XXXXXXXXX con X=cifra per un massimo di 15 cifre
    {
        regform.querySelector('[data-error="tel"]').classList.remove("hidden");
        return false;   
    }

    regform.querySelector('[data-error="tel"]').classList.add("hidden");
    return true;
}

function validatePw()
{
    const spanMsg= regform.querySelector('[data-error="pw"]');
    let errorMsg = "";
    let ok= true;

    //Devo verificare che le password coincidano e aggiornare il messaggio di errore di conseguenza
    validateCPw(); 

    if(regform.pw.value.length < 8) //La password deve essere di almeno 8 caratteri
    {
        errorMsg = errorMsg + "La password deve contenere almeno 8 caratteri!";
        ok= false;
    }

    if(!/[A-Z]/.test(regform.pw.value) || !/[a-z]/.test(regform.pw.value) || !/[0-9]/.test(regform.pw.value))
    {
        if(ok) //Se non ho già rilevato altri errori
        {
            errorMsg = errorMsg + "La password deve contenere almeno 1 lettera maiuscola, almeno 1 lettera minuscola e almeno 1 numero!";
            ok= false;
        }
        else
        {
            errorMsg = errorMsg + "\nLa password deve contenere almeno 1 lettera maiuscola, almeno 1 lettera minuscola e almeno 1 numero!";
        }
    }

    if(ok)
    {
        spanMsg.textContent = "Default"; //In questo modo lo span avrà un'altezza
        spanMsg.classList.add("hidden");
    }
    else
    {   spanMsg.textContent = errorMsg;
        spanMsg.classList.remove("hidden");
    }

    return ok;
}

function validateCPw()
{
    if(regform.pw.value != regform.cpw.value)
    {
        regform.querySelector('[data-error="cpw"]').classList.remove("hidden");
        return false;
    }

    regform.querySelector('[data-error="cpw"]').classList.add("hidden");
    return true;
}

function onEventJSON(json)
{
    if(json === null) //Se si è verificato un errore non faccio nulla
        return;
    
    const errorMsg = regform.querySelector('[data-error="email"]');

    if(json.ok) //Se l'email esiste già
    {
        errorMsg.textContent = "Email già in uso!";
        errorMsg.classList.remove("hidden"); 
        return;
    }
    
    errorMsg.classList.add("hidden"); 
}

function onFormJSON(json)
{
    if(json === null) //Se si è verificato un errore non faccio nulla
        return;
    
    const errorMsg = regform.querySelector('[data-error="email"]');

    if(json.ok) //Se l'email esiste già
    {
        errorMsg.textContent = "Email già in uso!";
        errorMsg.classList.remove("hidden"); 
        return;
    }
    
    regform.submit();
}

function onResponse(response)
{   
    if(response.ok) //Controllo che non si siano verificati errori
        return response.json();
    else
        return null; //In caso di errore restituisco null che indica la presenza di errori alla funzione onJSON
}

function controlEmail() //Esegue tutti i controlli per validare l'email fattibili lato client
{
    const errorMsg = regform.querySelector('[data-error="email"]');

    if(regform.email.value.length == 0)
    {
        errorMsg.textContent = "L'email non può essere vuota!";
        errorMsg.classList.remove("hidden"); 
        return false;
    }
    else if(!/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
             .test(regform.email.value.toLowerCase())
    ) //Se l'email non ha un fomrato valido
    {
        errorMsg.textContent = "Email non valida!";
        errorMsg.classList.remove("hidden"); 
        return false;
    }

    return true;
}

function eventValidateEmail() //Questa viene chiamata dal listener dell'input per la email quando si ha l'evento blur
{   
    if(controlEmail()) //Se i controlli lato client sono corretti
    {   //Verifico se l'email esiste già nel sistema
        fetch("check_email.php?email=" + encodeURIComponent(regform.email.value.toLowerCase())).then(onResponse).then(onEventJSON);
    }
}

function validateEmail() //Questa viene chiamata dal form per verificare l'email ed eventualmente fare il submit del form
{
    if(controlEmail()) //Se i controlli lato client sono corretti
    {   //Verifico se l'email esiste già nel sistema
        fetch("check_email.php?email=" + encodeURIComponent(regform.email.value.toLowerCase())).then(onResponse).then(onFormJSON);
    }
}

function validateForm(event)
{
    event.preventDefault();
    //Controllo se i campi sono riempiti
    if( regform.name.value.length == 0 || regform.last_name.value.length == 0 || regform.email.value.length == 0 ||
        regform.tel.value.length == 0 || regform.pw.value.length == 0 || regform.cpw.value.length == 0
    )
    {
        regform.querySelector('[data-error="general"]').classList.remove("hidden");
    }
    else
    {   //Se tutti i campi tranne email sono validati correttamente 
        if(validateName() && validateLastName() && validateTel() && validatePw() && validateCPw())
        {   //Verifico la email e se valida eseguo il submit.
            validateEmail();
        }
    }
}


//Aggiungo i listener
regform.addEventListener('submit', validateForm);
regform.name.addEventListener('blur', validateName);
regform.last_name.addEventListener('blur', validateLastName);
regform.email.addEventListener('blur', eventValidateEmail);
regform.tel.addEventListener('blur', validateTel);
regform.pw.addEventListener('blur', validatePw);
regform.cpw.addEventListener('keyup', validateCPw);