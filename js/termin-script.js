jQuery(document).ready($ => {

$('.relilab-termin-focused').ready($ =>{
        $(document).click(function (){
            $('.relilab-termin-focused').removeClass('relilab-termin-focused');
        });
    })

    $('.relilab-termin-filled').each((i, btn) => {
        $(btn).click(function (event) {
            $('.relilab-termin-details').removeClass('relilab-termin-focused');
            $(btn).find('.relilab-termin-details').addClass('relilab-termin-focused');
            event.stopPropagation();
        });
    })


})
