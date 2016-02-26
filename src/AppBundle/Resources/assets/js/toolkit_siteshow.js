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
        toolkitStandardPopup("Sign Up", '/passenger/new/' + entityId);

    });
    
});


