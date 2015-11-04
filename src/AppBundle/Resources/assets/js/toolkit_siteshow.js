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
        toolkitStandardPopup( "I'd prefer to make some changes", "/quote/view/change-request/form/" + entityId );
    });

    // Accept quote popup
    $(document).on('click', '#accept-quote', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        toolkitStandardPopup( "Like this quote", "/quote/view/accepted/form/" + entityId );
    });

    // Tour Sign Up popup
    $(document).on('click', '#accept-tour', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        $("#dialog").html("");
        $("#dialog").dialog("option", "title", "Sign Up");
        $("#dialog").dialog("open");
        $("#dialog").load('/passenger/new/' + entityId, function () {
            $(this).dialog("option", "title", "Sign Up");
            doMDLpopup($('#dialog'));// run the function to add appropriate MDL classes to form elements
            $('#ajax_passenger_form').on('submit', function(e) {

                var formAction = $(this).attr('action');
                console.log("message");
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
                    window.location.reload(true);
                }).error(function (response) {
                    console.log("you suck lulz");
                });

            })
        });
    });


});
