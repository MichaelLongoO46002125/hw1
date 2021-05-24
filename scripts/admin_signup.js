const regform= document.forms["signup-form"];
const subForm= document.querySelector('[data-subform="subform"]'); 

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
        regform.tel.value.length == 0 || ( //Se ho scelto un job diverso da USER controllo anche se i campi restanti sono vuoti
            regform.job.value !== "USER" && (
                    regform.salary.value.length == 0 || 
                    regform.duty_start.value.length == 0 || 
                    regform.duty_end.value.length == 0
            )
        )
    )
    {
        regform.querySelector('[data-error="general"]').classList.remove("hidden");
    }
    else
    {   //Se tutti i campi tranne email sono validati correttamente 
        if( validateName() && validateLastName() && validateTel() )
        {   console.log("Si");
            if(  regform.job.value === "USER" || //Se sto registrando un USER o un utente con job diverso ma i campi sono validi
                ( regform.job.value !== "USER" && validateDutyStart() && validateDutyEnd() && validateSalary() )
            ) //Allora verifico la email e se valida eseguo il submit.
            {console.log("Si2");    validateEmail();}
        }
    }
}

function selectChanged()
{
    if(regform.job.value !== "USER")
        subForm.classList.remove("none");
    else
        subForm.classList.add("none");
}

function validateSalary()
{
    if(!/^\d+(\.\d{1,2})?$/.test(regform.salary.value))
    {
        regform.querySelector('[data-error="salary"]').classList.remove("hidden");
        return false;
    }

    regform.querySelector('[data-error="salary"]').classList.add("hidden");
    return true;
}

function validateDuty(duty)
{
    if(!/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]$/.test(duty))
        return false;

    return true;
}

function validateDutyStart()
{
    if(!validateDuty(regform.duty_start.value))
    {
        regform.querySelector('[data-error="duty_start"]').classList.remove("hidden");
        return false;
    }

    regform.querySelector('[data-error="duty_start"]').classList.add("hidden");
    return true;
}

function validateDutyEnd()
{
    if(!validateDuty(regform.duty_end.value))
    {
        regform.querySelector('[data-error="duty_end"]').classList.remove("hidden");
        return false;
    }

    regform.querySelector('[data-error="duty_end"]').classList.add("hidden");
    return true;
}

//Aggiungo i listener
regform.addEventListener('submit', validateForm);
regform.name.addEventListener('blur', validateName);
regform.last_name.addEventListener('blur', validateLastName);
regform.email.addEventListener('blur', eventValidateEmail);
regform.tel.addEventListener('blur', validateTel);
regform.job.addEventListener('change', selectChanged);
regform.salary.addEventListener('blur', validateSalary);
regform.duty_start.addEventListener('blur', validateDutyStart);
regform.duty_end.addEventListener('blur', validateDutyEnd);
