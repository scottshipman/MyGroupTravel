
/**
 * get the config for the passenger list.
 */
function getPassengerFilterConfig() {
    var $free = $('.free'),
        $waitlist = $('.waitlist'),
        $accepted = $('.accepted'),
        $organizerCtaCard = $('.organizer-cta-card'),
        $passengerCtaCard = $('.passenger-cta-card'),
        $passengers = $('.passengers'),
        $acceptedPassengers = $passengers.filter('.accepted'),
        $waitlistPassengers = $passengers.filter('.waitlist'),
        $freePassengers = $passengers.filter('.free'),
        $organizers = $('.organizers'),
        $acceptedOrganizers = $organizers.filter('.accepted'),
        $freeOrganizers = $organizers.filter('.free'),
        $waitlistOrganizers = $organizers.filter('.waitlist');

    var config = {
        showAllPassengers: {
            cards: $passengerCtaCard,
            title: passengerStatus.passenger + ' List',
            items: $acceptedPassengers.add($freePassengers).add($acceptedOrganizers).add($freeOrganizers)
        },
        showAcceptedPassengers: {
            cards: $passengerCtaCard,
            title: passengerStatus.accepted + ' ' + passengerStatus.passenger + ' List',
            items: $acceptedPassengers
        },
        showWaitlistPassengers: {
            cards: $passengerCtaCard,
            title: passengerStatus.waitlist + ' ' + passengerStatus.passenger + ' List',
            items: $waitlistPassengers
        },
        showFreePassengers: {
            cards: $passengerCtaCard,
            title: passengerStatus.free + ' ' + passengerStatus.passenger + ' List',
            items: $freePassengers
        },
        showAllOrganizers: {
            cards: $organizerCtaCard,
            title: passengerStatus.organizer + ' List',
            items: $organizers
        },
        showAcceptedOrganizers: {
            cards: $organizerCtaCard,
            title: passengerStatus.accepted + ' ' + passengerStatus.organizer + ' List',
            items: $acceptedOrganizers
        },
        showWaitlistOrganizers: {
            cards: $organizerCtaCard,
            title: passengerStatus.waitlist + ' ' + passengerStatus.organizer + ' List',
            items: $waitlistOrganizers
        },
        showFreeOrganizers: {
            cards: $organizerCtaCard,
            title: passengerStatus.free + ' ' + passengerStatus.organizer + ' List',
            items: $freeOrganizers
        },
        showEveryone: {
            cards: $passengerCtaCard,
            title: passengerStatus.all,
            items: $accepted.add($waitlist).add($free)
        }
    };

    return config;
}

function filterPassengersByString($items, string) {
    // Filter the items to show.
    // If the search is empty then everything should be shown.
    string = string.toUpperCase();
    return $items.filter(function() {
        return $(this).find('.pcardname').text().toUpperCase().indexOf(string) >= 0;
    });
}

/**
 * filterPassenger
 * @param elemID = the elemID to filter by, reflects status
 * @param resetSearch = whether to reset the text search.
 */
function filterPassengers(elemID, resetSearch = true) {
    if (resetSearch) {
        $('#passenger-name-filter').val('');
    }

    var config = getPassengerFilterConfig(),
        currentElement = config[elemID],
        $items = $('.passengers').add('.organizers'),
        $itemsToShow = currentElement.items,
        $cards = $('.passenger-cta-card').add('.organizer-cta-card'),
        $cardsToShow = currentElement.cards,
        textSearch = $('#passenger-name-filter').val();

    // See if there is an active name filter.
    if (textSearch !== 'undefined') {
        $itemsToShow = filterPassengersByString($itemsToShow, textSearch);
    }

    var $itemsToHide = $items.filter(function(index, el) {
            return $itemsToShow.index(el) < 0;
        }),
        $cardsToHide = $cards.filter(function(index, el) {
            return $cardsToShow.index(el) < 0;
        });

    // Execute the show / hide.
    $itemsToHide.slideUp(400);
    $itemsToShow.slideDown(400);

    $cardsToHide.hide();
    $cardsToShow.show();

    $('.passenger-filter').css("color", passengerStatus.primaryColor).removeClass('active');
    $("a[data-id='" + elemID + "']").css("color", passengerStatus.secondaryColor).addClass('active');
    $cards.find('.mdl-card__title-text').html(currentElement.title);
    $('#clear-filters').css("color", passengerStatus.secondaryColor);
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
    // move passenger to new lists links
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
        $('.errors').remove();
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


    $('#ajax_new_medical_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $('.errors').remove();
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
            data: $('#ajax_new_medical_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            $("#loader").css("display", "none");
            window.location.reload(true);
            $(".medical-form").removeClass('expanded');
            $('#medical').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#medical-close').css("display", "none");

        }).error(function (response) {
            $("#loader").hide();
            var parsed = $.parseJSON(response.responseText);
            $.each(parsed, function(i, item) {
                var field = '#tui_toolkit_passengerbundle_medical_' + i;
                if($(field).is('input')){
                    $(field).parent().after('<p class="errors" style="color:red;">'+ item + '</p>');
                }
                else if($(field).is('div')){
                    $(field).append('<p class="errors" style="color:red;">'+ item + '</p>');
                }
            });
        })
    });

    $('#ajax_dietary_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $('.errors').remove();
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

    $('#ajax_new_dietary_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $('.errors').remove();
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
            window.location.reload(true);
            $(".dietary-form").removeClass('expanded');
            $('#dietary').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
            $('#dietary-close').css("display", "none");

        }).error(function (response) {
            $("#loader").hide();
            var parsed = $.parseJSON(response.responseText);
            $.each(parsed, function(i, item) {
                var field = '#tui_toolkit_passengerbundle_dietary_' + i;
                if($(field).is('input')){
                    $(field).parent().after('<p class="errors" style="color:red;">'+ item + '</p>');
                }
                else if($(field).is('div')){
                    $(field).append('<p class="errors" style="color:red;">'+ item + '</p>');
                }
            });
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
        $('.errors').remove();
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
            $('.passport-lastName').html(response[0]);
            $('.passport-firstName').html(response[1]);
            $('.passport-middleName').html(response[2]);
            $('.passport-gender').html(response[3]);
            $('.passport-title').html(response[4]);
            $('.passport-issuingState').html(response[5]);
            $('.passport-number').html(response[6]);
            $('.passport-nationality').html(response[7]);
            $('.passport-dateOfBirth').html(response[8]);
            $('.passport-dateOfIssue').html(response[9]);
            $('.passport-dateOfExpiry').html(response[10]);

        }).error(function (response) {
            var attribute = 'tui_toolkit_passengerbundle_passport_';
            ajaxFormErrors(response, attribute);
        })
    });

    $('#ajax_new_passport_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        var form = $(this);
        $('.errors').remove();
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
            data: $('#ajax_new_passport_form').serialize(),
            contentType: "application/x-www-form-urlencoded",
        }).success(function (response) {
            $("#loader").css("display", "none");
            window.location.reload(true);
            $(".passport-form").removeClass('expanded');
            $('#passport').css({
                "color": "grey",
                "position": "absolute",
                "right": "15px",
                "display": "inline-block"
            });
        }).error(function (response) {
            $("#loader").hide();
            var parsed = $.parseJSON(response.responseText);
            $.each(parsed, function(i, item) {
                var field = '#tui_toolkit_passengerbundle_passport_' + i;
                if($(field).is('input')){
                    $(field).parent().after('<p class="errors" style="color:red;">'+ item + '</p>');
                }
                else if($(field).is('div')){
                    $(field).append('<p class="errors" style="color:red;">'+ item + '</p>');
                }
            });
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
        $('.errors').remove();
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

    $('#ajax_new_emergency_form').on('submit', function(e) {

        var formAction = $(this).attr('action');
        $('.errors').remove();
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
            data: $('#ajax_new_emergency_form').serialize(),
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
            $("#loader").hide();
            var parsed = $.parseJSON(response.responseText);
            $.each(parsed, function(i, item) {
                var field = '#tui_toolkit_passengerbundle_emergency_' + i;
                if($(field).is('input')){
                    $(field).parent().after('<p class="errors" style="color:red;">'+ item + '</p>');
                }
                else if($(field).is('div')){
                    $(field).append('<p class="errors" style="color:red;">'+ item + '</p>');
                }
            });
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
        $('.errors').remove();
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
            $("#loader").hide();
            var parsed = $.parseJSON(response.responseText);
            $.each(parsed, function(i, item) {
                field= '#tui_toolkit_passengerbundle_passenger_' + i;
                if($(field).is('input')){
                    $(field).parent().after('<p class="errors" style="color:red;">'+ item + '</p>');
                }
                else if($(field).is('div')){
                    $(field).append('<p class="errors" style="color:red;">'+ item + '</p>');
                }
            });
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

    // Filter passengers by search
    var delayTimer;
    $('#passenger-name-filter').keyup(function() {
        if ($('.passenger-filter').filter('.active').length > 0) {
            var elemID = $('.passenger-filter').filter('.active').attr('data-id');
        } else {
            elemID = 'showEveryone';
        }

        // Don't do this after every keypress, delay the search.
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function() {
            filterPassengers(elemID, false);
        }, 100);
    });
});