$(document).ready(function () {

    // Do the resize content block stuff - on the fly!
    $('.convert').click(function () {
        var t = $(this);
        var passengerId = t.parent().attr('passenger');
        var tourId = t.parent().attr('tour');
        console.log(passengerId);
        console.log(tourId);
        //$("#loader").css("display", "block");
        $.ajax({
            type: 'POST',
            url: '/tour/dashboard/waitlist/accepted/' + tourId + '/' + passengerId,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function () {
            $("#loader").css("display", "none");
            //t.parent().transition({ opacity: 0 });
            t.parent().addClass("passenger-removed");
            t.parent().css("opacity", "0");
            t.parent().css("padding", "0");
            //t.parent().css("display", "none");
        });
    });

});