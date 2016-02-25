/* filterPassenger
 * @param elemID = the elemID to filter by, reflects status
 */
function filterPassengers(elemID) {
    switch(elemID) {
        case 'showAcceptedPassengers':
            $('.free, .organizers, .waitlist, .organizer-cta-card').slideUp(400, function () {
                $('.accepted.passengers, .passenger-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#card-title').html(passengerStatus.accepted + ' ' + passengerStatus.passenger + ' List');
            break;
        case 'showWaitlistPassengers':
            $('.accepted, .organizers, .free, .organizer-cta-card').slideUp(400, function () {
                $('.waitlist.passengers, .passenger-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#card-title').html(passengerStatus.waitlist + ' ' + passengerStatus.passenger + ' List');
            break;
        case 'showFreePassengers':
            $('.accepted, .waitlist, .organizers,  .organizer-cta-card').slideUp(400, function () {
                $('.free.passengers, .passenger-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#card-title').html(passengerStatus.free + ' ' + passengerStatus.passenger + ' List');
            break;
        case 'showAllPassengers':
            $('.organizers, .passengers, .organizer-cta-card').slideUp(400, function () {
                $(' .accepted.passengers, .free.passengers, .accepted.organizers, .passenger-cta-card, .free.organizers, .passenger-dashboard-buttons').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#card-title').html(passengerStatus.passenger + ' List');
            break;
        case 'showAllOrganizers':
            $('.passengers, .passenger-cta-card').slideUp(400, function () {
                $('.organizers, .organizer-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#organizer-card-title').html(passengerStatus.organizer + ' List');
            $('.organizer-cta-card').css("display", "block");
            break;
        case 'showAcceptedOrganizers':
            $('.waitlist, .free,  .passenger-cta-card, .passengers').slideUp(400, function () {
                $('.accepted.organizers, .organizer-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#organizer-card-title').html(passengerStatus.accepted + ' ' + passengerStatus.organizer + ' List');
            $('.organizer-cta-card').css("display", "block");
            break;
        case 'showWaitlistOrganizers':
            $('.accepted, .free,  .passenger-cta-card, .passengers').slideUp(400, function () {
                $('.waitlist.organizers, .organizer-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#organizer-card-title').html(passengerStatus.waitlist + ' ' + passengerStatus.organizer + ' List');
            $('.organizer-cta-card').css("display", "block");
            break;
        case 'showFreeOrganizers':
            $('.accepted, .waitlist, .passenger-cta-card, .passengers').slideUp(400, function () {
                $('.free.organizers, .organizer-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor);
            $('#organizer-card-title').html(passengerStatus.free + ' ' + passengerStatus.organizer + ' List');
            $('.organizer-cta-card').css("display", "block");
            break;
        case 'showEveryone':
            $('.organizer-cta-card').slideUp(400, function () {
                $('.accepted, .waitlist, .free, .passenger-cta-card').slideDown(400);
            });
            $('.passenger-filter').css("color", passengerStatus.primaryColor);
            $('#card-title').html(passengerStatus.all);
            break;
        default:
            // do nothing
            break;
        }
    }

    function updateCounts(response, paxId) {
        // When Passengers moved across lists, update Counts on front-end real time
        // response = 0 : passenger object
        //            1 : statusCounts (organizer=>accepted,waitlist,free; passenger=>accepted,waitlist,free)
        //            2 : payingPLaces (int)
        //            3 : freePlaces (int)
        //            4 : passengers array


        //$("#loader").css("display", "none");
        var passDiv = $('div[passenger="'+paxId+'"]');
        passDiv.slideUp(400);
        var payingSpots = response[2] - (response[1]['organizer']['accepted'] + response[1]['passenger']['accepted']);
        var freeSpots = response[3] - (response[1]['organizer']['free'] + response[1]['passenger']['free']);
        var organizerAll = (response[1]['organizer']['accepted'] + response[1]['organizer']['waitlist'] + response[1]['organizer']['free']);
        var passengerAll = (response[1]['passenger']['accepted'] + response[1]['passenger']['waitlist'] + response[1]['passenger']['free']);
        $("#allPassengers").html('(' + passengerAll + ')');
        $("#acceptedPassengers").html('(' + response[1]['passenger']['accepted'] + ')');
        $("#waitlistPassengers").html('(' + response[1]['passenger']['waitlist'] + ')');
        $("#freePassengers").html('(' + response[1]['passenger']['free'] + ')');
        $("#allOrganizers").html('(' + organizerAll + ')');
        $("#acceptedOrganizers").html('(' + response[1]['organizer']['accepted'] + ')');
        $("#waitlistOrganizers").html('(' + response[1]['organizer']['waitlist'] + ')');
        $("#freeOrganizers").html('(' + response[1]['organizer']['free'] + ')');
        $(".payingspots-remaining").html(payingSpots);
        $(".freespots-remaining").html(freeSpots);
        location.reload(true);
    }


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
            updateCounts(response, passengerId);
        });
    });

    $(document).on('click', 'a.move-to-waitlist', function (e) {
        var t = $(this);
        e.preventDefault();
        var passengerId = t.attr('passenger');
        var tourId = t.attr('tour');
        $("#loader").css("display", "block");
        $.ajax({
            type: 'POST',
            url: '/tour/dashboard/move/waitlist/' + tourId + '/' + passengerId,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function (response) {
            updateCounts(response, passengerId);
        });
    });

    $(document).on('click', 'a.move-to-free', function (e) {
        var t = $(this);
        e.preventDefault();
        var passengerId = t.attr('passenger');
        var tourId = t.attr('tour');
        $("#loader").css("display", "block");
        $.ajax({
            type: 'POST',
            url: '/tour/dashboard/move/free/' + tourId + '/' + passengerId,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function (response) {
            updateCounts(response, passengerId);
        });
    });

    // Payment popup
    $(document).on('click', 'a.make-a-payment', function (e) {
        var t = $(this);
        e.preventDefault();
        var passengerId = t.attr('passenger');
        var tourId = t.attr('tour');
        //$("#loader").css("display", "block");
        toolkitStandardPopup("Log A Payment", '/payment/tour/' + tourId + '/passenger/' + passengerId + '/new' );
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
            $('#passenger-dob').html("Age: " + dob);
            $('#passenger-gender').html("Gender: " + gender);
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