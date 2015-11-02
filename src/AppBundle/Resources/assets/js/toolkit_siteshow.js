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
        $("#dialog").html("");
        $("#dialog").dialog("open");
        $("#dialog").load('/quote/view/change-request/form/' + entityId, function () {
            $(this).dialog("option", "title", "I'd prefer to make some changes");
            doMDLpopup($('#dialog')); // run the function to add appropriate MDL classes to form elements
        });
    });

    // Accept quote popup
    $(document).on('click', '#accept-quote', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        $("#dialog").html("");
        $("#dialog").dialog("open");
        $("#dialog").load('/quote/view/accepted/form/' + entityId, function () {
            $(this).dialog("option", "title", "Like this quote");
            doMDLpopup($('#dialog')); // run the function to add appropriate MDL classes to form elements
        });
    });

    // Tour Sign Up popup
    $(document).on('click', '#accept-tour', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        $("#dialog").html("");
        $("#dialog").dialog("open");
        $("#dialog").load('/tour/view/new/passenger/form/' + entityId, function () {
            $(this).dialog("option", "title", "Sign Up");
            doMDLpopup($('#dialog')); // run the function to add appropriate MDL classes to form elements
        });
    });


});
