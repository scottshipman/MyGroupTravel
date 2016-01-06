$(document).ready(function () {
    // Do the resize content block stuff - on the fly!
    $(document).on('click', 'a.move-to-accepted', function (e) {
        var t = $(this);
        e.preventDefault();
        var passengerId = t.attr('passenger');
        var tourId = t.attr('tour');
        $("#loader").css("display", "block");
        $.ajax({
            type: 'POST',
            url: '/tour/dashboard/move/accepted/' + tourId + '/' + passengerId,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function (response) {
            var remainingSpots = response[4] - response[1];
            $("#loader").css("display", "none");
            var passDiv = $('div[passenger="'+passengerId+'"]');
            passDiv.addClass('accepted');
            if (passDiv.hasClass('waitlist') || passDiv.hasClass('free')) {
                passDiv.removeClass('waitlist', 'free');
            }
            passDiv.slideUp(400);
            var realAccepted = response[1] - response[3];
            $("#acceptedPassengers").html('(' + realAccepted + ')');
            $("#waitlistPassengers").html('(' + response[2] + ')');
            $("#freePassengers").html('(' + response[3] + ')');
            $(".spots-remaining").html(remainingSpots);
            var acceptedLink = $('a.move-to-accepted').attr('passenger', passengerId);
            acceptedLink.css("display", "none");
            var waitlistLink = $('a.move-to-waitlist').attr('passenger', passengerId);
            waitlistLink.css("display", "block");
            var freeLink = $('a.move-to-free').attr('passenger', passengerId);
            freeLink.css("display", "block");
            });
    });

    $(document).on('click', 'a.move-to-waitlist', function (e) {
        var t = $(this);
        e.preventDefault();
        var passengerId = t.attr('passenger');
        var tourId = t.attr('tour');
        $("#loader").css("display", "block");;
        $.ajax({
            type: 'POST',
            url: '/tour/dashboard/move/waitlist/' + tourId + '/' + passengerId,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function (response) {
            var remainingSpots = response[4] - response[1];
            $("#loader").css("display", "none");
            var passDiv = $('div[passenger="'+passengerId+'"]');
            passDiv.addClass('waitlist');
            if (passDiv.hasClass('accepted') || passDiv.hasClass('free')) {
                passDiv.removeClass('accepted', 'free');
            }
            passDiv.slideUp(400);
            var realAccepted = response[1] - response[3];
            $("#acceptedPassengers").html('(' + realAccepted + ')');
            $("#waitlistPassengers").html('(' + response[2] + ')');
            $("#freePassengers").html('(' + response[3] + ')');
            $(".spots-remaining").html(remainingSpots);
            var acceptedLink = $('a.move-to-accepted').attr('passenger', passengerId);
            acceptedLink.css("display", "block");
            var waitlistLink = $('a.move-to-waitlist').attr('passenger', passengerId);
            waitlistLink.css("display", "none");
            var freeLink = $('a.move-to-free').attr('passenger', passengerId);
            freeLink.css("display", "none");
        });
    });

    $(document).on('click', 'a.move-to-free', function (e) {
        var t = $(this);
        e.preventDefault();
        var passengerId = t.attr('passenger');
        var tourId = t.attr('tour');
        $("#loader").css("display", "block");;
        $.ajax({
            type: 'POST',
            url: '/tour/dashboard/move/free/' + tourId + '/' + passengerId,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function (response) {
            var remainingSpots = response[4] - response[1];
            $("#loader").css("display", "none");
            var passDiv = $('div[passenger="'+passengerId+'"]');
            passDiv.addClass('free');
            if (passDiv.hasClass('accepted') || passDiv.hasClass('waitlist')) {
                passDiv.removeClass('accepted', 'waitlist');
            }
            passDiv.slideUp(400);
            var realAccepted = response[1] - response[3];
            $("#acceptedPassengers").html('(' + realAccepted + ')');
            $("#waitlistPassengers").html('(' + response[2] + ')');
            $("#freePassengers").html('(' + response[3] + ')');
            $(".spots-remaining").html(remainingSpots);
            var acceptedLink = $('a.move-to-accepted').attr('passenger', passengerId);
            acceptedLink.css("display", "block");
            var waitlistLink = $('a.move-to-waitlist').attr('passenger', passengerId);
            waitlistLink.css("display", "block");
            var freeLink = $('a.move-to-free').attr('passenger', passengerId);
            freeLink.css("display", "none");
        });
    });

    $('#medical').click(function(e) {
        e.preventDefault();
        $('.medical-form').addClass('expanded');
        $('#medical-close').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#medical').css("display", "none");
    });

    $('#medical-close').click(function(e) {
        e.preventDefault();
        $('.medical-form').removeClass('expanded');
        $('#medical').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#medical-close').css("display", "none");
    });

    $('#ajax_medical_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $("#loader").css("display", "block");
        e.preventDefault();
        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            },
            data: $('#ajax_medical_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            $("#loader").css("display", "none");
            //window.location.reload(true);
            console.log(response);
            $(".medical-form").removeClass('expanded');
            $('#medical').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#medical-close').css("display", "none");

            $('.doctor-name').html(response[0]);
            $('.doctors-number').html(response[1]);
            $('.medical-conditions').html(response[2]);
            $('.medications').html(response[3]);
        }).error(function (response) {
            console.log(response.errorReport[0]);
        })
    });

    $('#ajax_dietary_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $("#loader").css("display", "block");
        e.preventDefault();
        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            },
            data: $('#ajax_dietary_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            $("#loader").css("display", "none");
            //window.location.reload(true);
            $(".dietary-form").removeClass('expanded');
            $('#dietary').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#dietary-close').css("display", "none");
            $('.dietary-description').html(response);

        }).error(function (response) {
            console.log(response.errorReport[0]);
        })
    });

    $('#dietary').click(function(e) {
        e.preventDefault();
        $('.dietary-form').addClass('expanded');
        $('#dietary-close').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#dietary').css("display", "none");
    });

    $('#dietary-close').click(function(e) {
        e.preventDefault();
        $('.dietary-form').removeClass('expanded');
        $('#dietary').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#dietary-close').css("display", "none");
    });

    $('#ajax_passport_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $("#loader").css("display", "block");
        e.preventDefault();
        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            },
            data: $('#ajax_passport_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            $("#loader").css("display", "none");
            //window.location.reload(true);
            $(".passport-form").removeClass('expanded');
            $('#passport').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#passport-close').css("display", "none");
            $('.passport-number').html(response[0]);
            $('.passport-firstName').html(response[1]);
            $('.passport-lastName').html(response[2]);
            $('.passport-nationality').html(response[3]);
            $('.passport-dateOfIssue').html(response[4]);
            $('.passport-dateOfExpiry').html(response[5]);

        }).error(function (response) {
            console.log(response.errorReport[0]);
        })
    });

    $('#passport').click(function(e) {
        e.preventDefault();
        $('.passport-form').addClass('expanded');
        $('#passport-close').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#passport').css("display", "none");
    });

    $('#passport-close').click(function(e) {
        e.preventDefault();
        $('.passport-form').removeClass('expanded');
        $('#passport').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#passport-close').css("display", "none");
    });

    $('#ajax_emergency_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $("#loader").css("display", "block");
        e.preventDefault();
        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            },
            data: $('#ajax_emergency_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            $("#loader").css("display", "none");
            //window.location.reload(true);
            $(".emergency-form").removeClass('expanded');
            $('#emergency').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#emergency-close').css("display", "none");
            $('.emergency-name').html(response[0]);
            $('.emergency-relationship').html(response[1]);
            $('.emergency-number').html(response[2]);
            $('.emergency-email').html(response[3]);

        }).error(function (response) {
            console.log(response.errorReport[0]);
        })
    });

    $('#emergency').click(function(e) {
        e.preventDefault();
        $('.emergency-form').addClass('expanded');
        $('#emergency-close').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#emergency').css("display", "none");
    });

    $('#emergency-close').click(function(e) {
        e.preventDefault();
        $('.emergency-form').removeClass('expanded');
        $('#emergency').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#emergency-close').css("display", "none");
    });

});