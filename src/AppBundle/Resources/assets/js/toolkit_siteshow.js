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

});
