$(window).load(function () {

    // Flexslider
    $('.flexslider').flexslider({
        directionNav: false,
        controlNav: false,
        smoothHeight: true
    });

});

$(document).ready(function () {

    // Change request popup
    $(document).on('click', '#change-request', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        toolkitStandardPopup( "I'd like to make some changes", "/quote/view/change-request/form/" + entityId );
    });

    // Accept quote popup
    $(document).on('click', '#accept-quote', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        toolkitStandardPopup( "I like this quote, what's next?", "/quote/view/accepted/form/" + entityId );
    });

    // Tour Sign Up popup
    $(document).on('click', '#accept-tour', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        toolkitStandardPopup("Sign Up", '/manage/passenger/new/' + entityId, function () {
            $('#ajax_passenger_form').on('submit', function(e) {

                var formAction = $(this).attr('action');
                var form =$(this);
                e.preventDefault();
                $.ajax({
                    url: formAction,
                    type: 'POST',
                    headers: {
                        "Pragma": "no-cache",
                        "Expires": -1,
                        "Cache-Control": "no-cache"
                    },
                    data: $('#ajax_passenger_form').serialize(),
                    contentType: "application/x-www-form-urlencoded",
                }).success(function (response) {
                    $("#loader").show();
                    window.location.reload(true);
                }).error(function (response) {
                    var parsed = $.parseJSON(response.responseText);
                        $.each(parsed, function(i, item) {
                            //console.log(i);
                            $.each(item, function(c, child) {
                                console.log(c);
                                var task = c;
                                $.each(child, function(f, field) {
                                    var specificFrom = $('.new_passenger[task="'+task+'"]');
                                    specificFrom.prepend('<p style="color:red;">'+ field + '</p>');
                                });
                            });
                        });
                    });
                });

            })
        });
    });


