/**
 * Helper functions for working with the current uri.
 */
var Location;
Location = function (uri) {

    /**
     * Uri can be set by constructor but will default to the current uri.
     *
     * @type {string}
     */
    uri = uri !== undefined ? uri : window.location.href;

    /**
     * Get a query parameter by key.
     *
     * @param key
     * @returns {*}
     */
    this.getQueryParam = function (key) {

        if (key == 'search') {
            var hash = this.getHash();

            if (hash.includes('=')) {
                var searchTerm = hash.split('=');

                if (searchTerm[1].includes('&')) {
                    var result = searchTerm[1].split('&');
                    return result[0];
                }

                var verifySearch = searchTerm[0].split('&');

                if (verifySearch[1] == 'title') {
                    return '';
                }

                return searchTerm[1];
            }
        }
    };

    /**
     * Set a query parameter by key.
     *
     * @param key
     * @param value
     */
    this.setQueryParam = function (key, value) {

        var currentHash = this.getHash();

        if (currentHash.includes('=')) {
            currentHash = currentHash.split('=', 1);
            window.location.hash = currentHash[0] + '=' + value;
        } else {
            window.location.hash = currentHash + '=' + value;
        }
    };

    /**
     * Get the location hash.
     *
     * @returns {string}
     */
    this.getHash = function () {

        return window.location.hash;
    };

    /**
     * Set the location hash.
     *
     * @param value
     */
    this.setHash = function (value) {

        var currentHash = this.getHash();

        if (currentHash.includes('=')) {
            currentHash = currentHash.split('=');
            window.location.hash = (value + '=' + currentHash[1]);
        } else {
            window.location.hash = value;
        }
    };
};

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
        return $(this).find('.mini-card-title').text().toUpperCase().indexOf(string) >= 0;
    });
}

/**
 * filterPassengers
 *
 * @param elemID = the elemID to filter by, reflects status
 * @param resetSearch = whether to reset the text search.
 */
function filterPassengers(elemID, resetSearch) {

    elemID = elemID.replace('#', '');


    if(elemID.indexOf('=') != -1) {
        elemID = elemID.split('=');
        elemID = elemID[0];
    }

    if(elemID.indexOf('&') != -1) {
        elemID = elemID.split('&');
        elemID = elemID[0];
    }

    if(elemID == '') {
        elemID = 'showEveryone';
    }


    if (resetSearch) {
        $('#passenger-name-filter').val('');

        var location = new Location();
        location.setQueryParam('search', '');
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

function passengerSort(type) {

    var $items = $('.passengers').add('.organizers');
    $items.remove();

    // Sort the elements
    if (type == 'name') {
        // Sort by surname then forename

        $items = $items.sort(function(a, b) {
            var vA = $('.surname', a).text() + $('.forename', a).text();
            var vB = $('.surname', b).text() + $('.forename', b).text();
            return (vA.toLowerCase() < vB.toLowerCase()) ? -1 : (vA.toLowerCase() > vB.toLowerCase()) ? 1 : 0;
        });
    }
    else if (type == 'date') {
        $items = $items.sort(function(a, b) {
            var vA = $('.date', a).data('signup-date') + $('.surname', a).text() + $('.forename', a).text();
            var vB = $('.date', b).data('signup-date') + $('.surname', b).text() + $('.forename', b).text();
            return (vA.toLowerCase() < vB.toLowerCase()) ? -1 : (vA.toLowerCase() > vB.toLowerCase()) ? 1 : 0;
        });
    }
    else {
        // Do nothing.
        return;
    }

    // Re-add the items in the new order.
    $('.tour-show-right-column').append($items);
}

function isAutoSort() {
    var module = window.location.pathname;
    if(module.substr(module.lastIndexOf('/') + 1) == 'passengers') {
        return true;
    }
    return false;
}

function updateMarkup(route, element) {
    $.get(route, function(data) {
        $(element).html(data);
    });
}

function updateTasks() {
    // Possible and completed tasks are assigned by the Twig template
    if (completed_tasks >= possible_tasks) {
        $('#no-tasks-container, #tasks-completed-tick').css('display', 'inherit');
    }
}


$(document).ready(function () {

    // Sort passengers by name
    if(isAutoSort()) {
        passengerSort('name');
    }
    
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
        toolkitStandardPopup("Log A Payment", '/payment/tour/' + tourId + '/passenger/' + passengerId + '/new' );
    });


    // Medical information cards
    $(document).on('submit', '#ajax_medical_form, #ajax_new_medical_form', function (e) {

        // Don't submit the form
        e.preventDefault();

        // Display the loading spinner
        $('#loader').css('display', 'block');

        var formAction = $(this).attr('action');
        var form = $(this);

        // Remove any existing errors
        $('.errors').remove();

        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                'Pragma': 'no-cache',
                'Expires': -1,
                'Cache-Control': 'no-cache'
            },
            data: $('#' + this.id).serialize(),
            contentType: 'application/x-www-form-urlencoded',
        }).success(function (response) {

            // Update the markup for the edit form
            updateMarkup('/passenger/medical/'+ response['id'] + '/edit', '#medical-edit-container');

            // Populate the "front" of the edit card
            $('.doctor-name').html(response['name']);
            $('.doctors-number').html(response['number']);
            $('.medical-conditions').html(response['conditions']);
            $('.medications').html(response['medications']);

            // Remove the "new" box
            $('#medical-new-card').remove();

            // Show the "edit" box
            $('#medical-edit-card').css('display', 'block');

            // Hide the edit form (no longer expanded)
            $(".medical-form").removeClass('expanded');

            // Set the clickable icon CSS
            $('.medical').css({
                'color': 'grey',
                'position': 'absolute',
                'right': '15px',
                'display': 'inline-block'
            });

            // Set the close CSS to not be displayed
            $('.medical-close').css('display', 'none');

            // Record completed task and check if we need to render completed message
            completed_tasks++;
            updateTasks();

            // Hide the loading spinner
            $('#loader').css('display', 'none');

        }).error(function (response) {
            var attribute = '#tui_toolkit_passengerbundle_medical_';
            ajaxFormErrors(response, attribute);

            // Hide the loading spinner
            $('#loader').css('display', 'none');
        })
    });


    $(document).on('click', '.medical', function (e) {
        e.preventDefault();
        $('.medical-form').addClass('expanded');
        $('.medical-close').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.medical').css('display', 'none');
    });

    $(document).on('click', '.medical-close', function (e) {
        e.preventDefault();
        $('.medical-form').removeClass('expanded');
        $('.medical').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.medical-close').css('display', 'none');
    });

    // End of medical card



    // Dietary information cards
    $(document).on('submit', '#ajax_dietary_form, #ajax_new_dietary_form', function (e) {

        // Don't submit the form
        e.preventDefault();

        // Display the loading spinner
        $('#loader').css('display', 'block');

        var formAction = $(this).attr('action');
        var form = $(this);

        // Remove any existing errors
        $('.errors').remove();

        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                'Pragma': 'no-cache',
                'Expires': -1,
                'Cache-Control': 'no-cache'
            },
            data: $('#' + this.id).serialize(),
            contentType: 'application/x-www-form-urlencoded',
        }).success(function (response) {

            // Update the markup for the edit form
            updateMarkup('/passenger/dietary/'+ response['id'] + '/edit', '#dietary-edit-container');

            // Populate the "front" of the edit card
            $('.dietary-description').html(response['description']);

            // Remove the "new" box
            $('#dietary-new-card').remove();

            // Show the "edit" box
            $('#dietary-edit-card').css('display', 'block');

            // Hide the edit form (no longer expanded)
            $(".dietary-form").removeClass('expanded');

            // Set the clickable icon CSS
            $('.dietary').css({
                'color': 'grey',
                'position': 'absolute',
                'right': '15px',
                'display': 'inline-block'
            });

            // Set the close CSS to not be displayed
            $('.dietary-close').css('display', 'none');

            // Record completed task and check if we need to render completed message
            completed_tasks++;
            updateTasks();

            // Hide the loading spinner
            $('#loader').css('display', 'none');

        }).error(function (response) {
            var attribute = '#tui_toolkit_passengerbundle_dietary_';
            ajaxFormErrors(response, attribute);

            // Hide the loading spinner
            $('#loader').css('display', 'none');
        })
    });


    $(document).on('click', '.dietary', function (e) {
        e.preventDefault();
        $('.dietary-form').addClass('expanded');
        $('.dietary-close').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.dietary').css('display', 'none');
    });

    $(document).on('click', '.dietary-close', function (e) {
        e.preventDefault();
        $('.dietary-form').removeClass('expanded');
        $('.dietary').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.dietary-close').css('display', 'none');
    });

    // End of dietary card


    // Passport information cards
    $(document).on('submit', '#ajax_passport_form, #ajax_new_passport_form', function (e) {

        // Don't submit the form
        e.preventDefault();

        // Display the loading spinner
        $('#loader').css('display', 'block');

        var formAction = $(this).attr('action');
        var form = $(this);

        // Remove any existing errors
        $('.errors').remove();

        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                'Pragma': 'no-cache',
                'Expires': -1,
                'Cache-Control': 'no-cache'
            },
            data: $('#' + this.id).serialize(),
            contentType: 'application/x-www-form-urlencoded',
        }).success(function (response) {

            // Update the markup for the edit form
            updateMarkup('/passenger/passport/'+ response['id'] + '/edit', '#passport-edit-container');

            // Populate the "front" of the edit card
            $('.passport-lastName').html(response['last_name']);
            $('.passport-firstName').html(response['first_name']);
            $('.passport-middleName').html(response['middle_name']);
            $('.passport-gender').html(response['gender']);
            $('.passport-title').html(response['title']);
            $('.passport-issuingState').html(response['issuing_state']);
            $('.passport-number').html(response['passport_number']);
            $('.passport-nationality').html(response['nationality']);
            $('.passport-dateOfBirth').html(response['date_of_birth']);
            $('.passport-dateOfIssue').html(response['date_of_issue']);
            $('.passport-dateOfExpiry').html(response['date_of_expiry']);

            // Remove the "new" box
            $('#passport-new-card').remove();

            // Show the "edit" box
            $('#passport-edit-card').css('display', 'block');

            // Hide the edit form (no longer expanded)
            $(".passport-form").removeClass('expanded');

            // Set the clickable icon CSS
            $('.passport').css({
                'color': 'grey',
                'position': 'absolute',
                'right': '15px',
                'display': 'inline-block'
            });

            // Set the close CSS to not be displayed
            $('.passport-close').css('display', 'none');

            // Record completed task and check if we need to render completed message
            completed_tasks++;
            updateTasks();

            // Hide the loading spinner
            $('#loader').css('display', 'none');

        }).error(function (response) {
            var attribute = '#tui_toolkit_passengerbundle_passport_';
            ajaxFormErrors(response, attribute);

            // Hide the loading spinner
            $('#loader').css('display', 'none');
        })
    });


    $(document).on('click', '.passport', function (e) {
        e.preventDefault();
        $('.passport-form').addClass('expanded');
        $('.passport-close').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.passport').css('display', 'none');
    });

    $(document).on('click', '.passport-close', function (e) {
        e.preventDefault();
        $('.passport-form').removeClass('expanded');
        $('.passport').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.passport-close').css('display', 'none');
    });

    // End of passport card


    // Emergency information cards
    $(document).on('submit', '#ajax_emergency_form, #ajax_new_emergency_form', function (e) {

        // Don't submit the form
        e.preventDefault();

        // Display the loading spinner
        $('#loader').css('display', 'block');

        var formAction = $(this).attr('action');
        var form = $(this);

        // Remove any existing errors
        $('.errors').remove();

        $.ajax({
            url: formAction,
            type: 'POST',
            headers: {
                'Pragma': 'no-cache',
                'Expires': -1,
                'Cache-Control': 'no-cache'
            },
            data: $('#' + this.id).serialize(),
            contentType: 'application/x-www-form-urlencoded',
        }).success(function (response) {

            // Update the markup for the edit form
            updateMarkup('/passenger/emergency/'+ response['id'] + '/edit', '#emergency-edit-container');

            // Populate the "front" of the edit card
            $('.emergency-name').html(response['name']);
            $('.emergency-relationship').html(response['relationship']);
            $('.emergency-number').html(response['telephone']);
            $('.emergency-email').html(response['email']);

            // Remove the "new" box
            $('#emergency-new-card').remove();

            // Show the "edit" box
            $('#emergency-edit-card').css('display', 'block');

            // Hide the edit form (no longer expanded)
            $(".emergency-form").removeClass('expanded');

            // Set the clickable icon CSS
            $('.emergency').css({
                'color': 'grey',
                'position': 'absolute',
                'right': '15px',
                'display': 'inline-block'
            });

            // Set the close CSS to not be displayed
            $('.emergency-close').css('display', 'none');

            // Record completed task and check if we need to render completed message
            completed_tasks++;
            updateTasks();

            // Hide the loading spinner
            $('#loader').css('display', 'none');

        }).error(function (response) {
            var attribute = '#tui_toolkit_passengerbundle_emergency_';
            ajaxFormErrors(response, attribute);

            // Hide the loading spinner
            $('#loader').css('display', 'none');
        })
    });


    $(document).on('click', '.emergency', function (e) {
        e.preventDefault();
        $('.emergency-form').addClass('expanded');
        $('.emergency-close').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.emergency').css('display', 'none');
    });

    $(document).on('click', '.emergency-close', function (e) {
        e.preventDefault();
        $('.emergency-form').removeClass('expanded');
        $('.emergency').css({
            'color': 'grey',
            'position': 'absolute',
            'right': '15px',
            'display': 'inline-block'
        });
        $('.emergency-close').css('display', 'none');
    });

    // End of emergency card


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
            var lastName = response[1];
            var dob = response[2];
            var gender = response[3];
            var passengerId =  response[4];
            $("#loader").css("display", "none");
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
            $('.form-row').css('display', 'block');
            var field = '#tui_toolkit_passengerbundle_passenger_';
            ajaxFormErrors(response, field);
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

    // See if there is a name search.
    var location = new Location(),
        hash = location.getHash(),
        search = location.getQueryParam('search'),
        elemID = hash !== '' ? hash : 'showEveryone';

    $('#passenger-name-filter').val(search);
    
    if (isAutoSort()) {
        filterPassengers(elemID, false);
    }
    
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
            var search = $('#passenger-name-filter').val(),
                location = new Location();

            location.setQueryParam('search', search);
            filterPassengers(elemID, false);
        }, 100);
    });

    // On change track the search in the url.
    $('#passenger-name-filter').change(function() {
        var search = $('#passenger-name-filter').val(),
            location = new Location();

        location.setQueryParam('search', search);
    });


    // Clicking a passenger filter.
    $('.passenger-filter').click(function (e) {
        e.preventDefault();

        //Scroll functionality if at tablet breakpoint
        if (parseInt($(window).width()) < 900) {
            var target = $('#passenger-card');
            $('html, body').animate({
                scrollTop: target.offset().top
            }, 1000);
        }

        var elemID = this.getAttribute('data-id'),
            location = new Location;

        // If the clear filter item has been clicked,
        // remove the value of the search box
        var reset = false;
        if(elemID == 'showEveryone') {
            reset = true;
        }


        location.setHash(elemID);

        filterPassengers(elemID, reset);

        if(elemID == 'showWaitlistPassengers') {
            passengerSort('date');
        } else {
            passengerSort('name');
        }

    });

});