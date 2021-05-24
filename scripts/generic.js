const btnNavMenu= document.querySelector('#nav-menu'); //Pulsante menu della navbar (Responsive)

//Listener per il bottone della navbar (responsive)
function closeNavMenu()
{
    btnNavMenu.addEventListener('click', openNavMenu);
    btnNavMenu.removeEventListener('click', closeNavMenu);

    document.querySelector('#nav-links').classList.remove('open-nav-menu');
}

function openNavMenu()
{
    btnNavMenu.removeEventListener('click', openNavMenu);
    btnNavMenu.addEventListener('click', closeNavMenu);
    
    document.querySelector('#nav-links').classList.add('open-nav-menu');
}

//Aggiungo i listener ai pulsanti della navbar
btnNavMenu.addEventListener('click', openNavMenu);
