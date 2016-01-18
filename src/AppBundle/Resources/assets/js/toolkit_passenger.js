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
            var attribute = 'tui_toolkit_passengerbundle_medical_';
            ajaxFormErrors(response, attribute);
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
            var attribute = 'tui_toolkit_passengerbundle_dietary_';
            ajaxFormErrors(response, attribute);
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
            var attribute = 'tui_toolkit_passengerbundle_passport_';
            ajaxFormErrors(response, attribute);
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
            var attribute = 'tui_toolkit_passengerbundle_emergency_';
            ajaxFormErrors(response, attribute);
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

    $('#ajax_passenger_edit_form').on('submit', function(e) {

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
            data: $('#ajax_passenger_edit_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            var firstName = response[0];
            var lastName = response[1]
            var dob = response[2];
            var gender = response[3];
            var passengerId =  response[4];
            $("#loader").css("display", "none");
            //window.location.reload(true);
            $(".passenger-edit-form").removeClass('expanded');
            $('#passenger-edit-actions-menu-drop-' + passengerId).css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#passenger-close').css("display", "none");
            $('#passenger-fname').html(firstName);
            $('#passenger-lname').html(lastName);
            $('#passenger-dob').html(dob);
            $('#passenger-gender').html(gender);
            var length = response.length;
            if (length == 7) {
                var imagePath = response[5] + '/' + response[6];
                if ($('#passenger-avatar').is('img')) {
                    $('#passenger-avatar').attr('src', imagePath);
                }else if ($('#passenger-avatar').is('span')) {
                    $('#passenger-avatar').replaceWith('<img style="float: left; margin-right: 15px;" id="passenger-avatar" class="tui-image-avatar" src="'+ imagePath +'">');
                }
            }else {
                if ($('#passenger-avatar').is('img')) {
                    $('#passenger-avatar').replaceWith('<span style="float: left; margin-right: 15px;" id="passenger-avatar" class="tui-text-avatar mdl-typography--headline">' + firstName.charAt(0) + lastName.charAt(0) + '</span>');
                }else if ($('#passenger-avatar').is('span')){
                    $('#passenger-avatar').html(firstName.charAt(0) + lastName.charAt(0));
                }
            }
        }).error(function (response) {
            var attribute = 'tui_toolkit_passengerbundle_passenger_';
            ajaxFormErrors(response, attribute);
        })
    });

    $('#passenger').click(function(e) {
        e.preventDefault();
        var passenger = $(this).attr('passenger');
        $('.passenger-edit-form').addClass('expanded');
        $('#passenger-close').css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#passenger-edit-actions-menu-drop-' + passenger).css("display", "none");
    });

    $('#passenger-close').click(function(e) {
        e.preventDefault();
        var passenger = $(this).attr('passenger');
        $('.passenger-edit-form').removeClass('expanded');
        $('#passenger-edit-actions-menu-drop-' + passenger).css({
            "color": "grey",
            "position": "absolute",
            "right": "15px",
            "display": "inline-block"
        });
        $('#passenger-close').css("display", "none");
    });

});