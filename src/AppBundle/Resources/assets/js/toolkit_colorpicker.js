(function ($) {

    // Color picker
    $('#tui_toolkit_brandbundle_brand_primaryColor').addClass('color_picker color_picker_full');
    $('#tui_toolkit_brandbundle_brand_buttonColor').addClass('color_picker color_picker_lite');
    // $('#tui_toolkit_brandbundle_brand_hoverColor').addClass('color_picker color_picker_full');

    $('input.color_picker_full').spectrum({
        preferredFormat: "rgb",
        showPaletteOnly: true,
        showPalette: true,
        showInitial: true,
        hideAfterPaletteSelect: true,
        palette: [
            ['rgb(121, 85, 72)', 'rgb(96, 125, 139)', 'rgb(158, 158, 158)'],
            ['rgb(255, 87, 34)', 'rgb(244, 67, 54)', 'rgb(233, 30, 99)'],
            ['rgb(156, 39, 176)', 'rgb(103, 58, 183)', 'rgb(63, 81, 181)'],
            ['rgb(33, 150, 243)', 'rgb(3, 169, 244)', 'rgb(0, 188, 212)'],
            ['rgb(0, 150, 136)', 'rgb(76, 175, 80)', 'rgb(139, 195, 74)'],
            ['rgb(205, 220, 57)', 'rgb(255, 235, 59)', 'rgb(255, 193, 7)'],
            ['rgb(255, 152, 0)']
        ],
    });

    $('input.color_picker_lite').spectrum({
        preferredFormat: "rgb",
        showPaletteOnly: true,
        showPalette: true,
        showInitial: true,
        hideAfterPaletteSelect: true,
        palette: [
            ['rgb(255, 87, 34)', 'rgb(244, 67, 54)', 'rgb(233, 30, 99)'],
            ['rgb(156, 39, 176)', 'rgb(103, 58, 183)', 'rgb(63, 81, 181)'],
            ['rgb(33, 150, 243)', 'rgb(3, 169, 244)', 'rgb(0, 188, 212)'],
            ['rgb(0, 150, 136)', 'rgb(76, 175, 80)', 'rgb(139, 195, 74)'],
            ['rgb(205, 220, 57)', 'rgb(255, 235, 59)', 'rgb(255, 193, 7)'],
            ['rgb(255, 152, 0)']
        ],
    });

    $('input.color_picker')
        .parent().removeClass().addClass('mdl-colorfield')
        .find('label').removeClass().addClass('mdl-label-mimic');

})(jQuery);
