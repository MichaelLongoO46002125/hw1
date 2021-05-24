const modalBooking= document.querySelector('[data-modal="booking"]');

function openModalBooking(event)
{
    modalBooking.classList.remove("hidden");
    modalBooking.style.top= window.pageYOffset + "px";
    document.body.classList.add("no-scroll");
}
