jQuery(document).ready($ => {

$('.event-termin-focused').ready($ =>{
        $(document).click(function (){
            $('.event-termin-focused').removeClass('event-termin-focused');
        });
    })

    $('.event-termin-filled').each((i, btn) => {
        $(btn).click(function (event) {
            $('.event-termin-details').removeClass('event-termin-focused');
            $(btn).find('.event-termin-details').addClass('event-termin-focused');
            event.stopPropagation();
        });
    })


})
