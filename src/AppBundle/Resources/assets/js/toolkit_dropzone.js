(function ($) {

    // Dropzone manipulation
    $(document).on('click', '.media-placeholder-image', function () {
        $('.media-placeholder-image').css({"display": "none"});
        $("#dropzone_form").css({"display": "block"});
        $("#dropzone-form-close").css({"display": "inline-block"});
    });

    $(document).on('click', '#avatar-label', function () {
        $('.media-placeholder-image').css({"display": "block"});
        $("#dropzone_form").css({"display": "none"});
        $("#dropzone-form-close").css({"display": "none"});
    });

    $(document).on('submit', '#dropzone_form', function () {
        $("img#user_media").css({
            display: "none"
        });
    });

})(jQuery);
