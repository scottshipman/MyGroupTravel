(function ($) {

    // Color picker
    $('#tui_toolkit_brandbundle_brand_primaryColor').addClass('color_picker');
    $('#tui_toolkit_brandbundle_brand_secondaryColor').addClass('color_picker');

    $('input.color_picker').spectrum({
        preferredFormat: "rgb",
        showInitial: true,
        showInput: true,
        hideAfterPaletteSelect: true,
    });

    $('input.color_picker')
        .parent().removeClass().addClass('mdl-colorfield')
        .find('label').removeClass().addClass('mdl-label-mimic');

})(jQuery);
