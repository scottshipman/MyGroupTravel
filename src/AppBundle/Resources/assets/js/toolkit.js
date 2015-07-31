(function ($) {

    $('.mdl-layout__drawer .menu_level_1').each(function () {
        var t = $(this);
        t.prev().addClass('menu-parent'); // add to link-like span
        t.css('overflow', 'hidden');
        if (t.parent().hasClass('current_ancestor')) {
            t.parent().addClass('expanded');
            t.children('span').css('margin-top', 0);
            t.show();
        } else {
            t.parent().addClass('collapsed');
            t.children('span').css('margin-top', '-' + $(this).css('height'));
            t.hide();
        }
    });

    $('.menu-parent').click(function () { // $(this) is the link-like span
        var t = $(this);
        if (t.parent().hasClass('collapsed')) {
            t.parent().removeClass('collapsed').addClass('expanded');
            t.next().show();
            t.next().children('span').animate({
                marginTop: 0
            }, 200);
        } else {
            t.parent().removeClass('expanded').addClass('collapsed');
            t.next().children('span').animate({
                marginTop: '-' + $(this).next().css('height')
            }, 200, 'swing', function () {
                t.next().hide();
            });
        }
    });

    // Color picker
    $('#tui_toolkit_brandbundle_brand_primaryColor').addClass('color_picker');
    $('#tui_toolkit_brandbundle_brand_buttonColor').addClass('color_picker');
    $('#tui_toolkit_brandbundle_brand_hoverColor').addClass('color_picker');

    $('input.color_picker').spectrum({
        preferredFormat: "rgb",
        showPaletteOnly: true,
        showPalette: true,
        showInitial: true,
        hideAfterPaletteSelect: true,
        palette: [
            ['rgb(121, 85, 72);', 'rgb(96, 125, 139);', 'rgb(158, 158, 158);'],
            ['rgb(255, 87, 34);', 'rgb(244, 67, 54);', 'rgb(233, 30, 99);'],
            ['rgb(156, 39, 176);', 'rgb(103, 58, 183);', 'rgb(63, 81, 181);'],
            ['rgb(33, 150, 243);', 'rgb(3, 169, 244);', 'rgb(0, 188, 212);'],
            ['rgb(0, 150, 136);', 'rgb(76, 175, 80);', 'rgb(139, 195, 74);'],
            ['rgb(205, 220, 57);', 'rgb(255, 235, 59);', 'rgb(255, 193, 7);'],
            ['rgb(255, 152, 0);'],
        ]
    });
    $('input.color_picker').parent().removeClass().addClass('mdl-colorfield').find('label').removeClass().addClass('mdl-label-mimic');

    // Work on date fields
    $('input[type="date"]').addClass('mdl-date').attr('type', 'text');
    $('.mdl-date').change(function () {
        $(this).parent().addClass('is-dirty');
    });

    //Add Placeholder Fields to Login Form
    $("#username").attr("placeholder", "Username");
    $("#password").attr("placeholder", "Password");

    if ($("form").hasClass("fos_user_resetting_request")) {
        $("body").addClass("main_login");
        $('h2.signin-form').text('Reset Password').show();
        $("#username").attr("placeholder", "Username Or Email Address");

    }

    //if ($("div").hasClass("login-message-block")){
    //    $("body").addClass("main_login");
    //}


  // Add New Link for Quote form - Add form element ID's to the array
    var elements = {'#tui_toolkit_quotebundle_quoteversion_quoteReference_organizer':'organizer',
                    '#tui_toolkit_quotebundle_quoteversion_quoteReference_institution': 'institution'};
    $.each(elements, function(element, type){
            if ( element.length ) {
                //  source a button or glyph here
                $(element).parent('div').parent('div').append('<div id= "' + type + 'add-new-link" class="add-new modal" style="display:inline;">Add New</div>');
                // add and hide a modal dialog
                //$(element).parent('div').parent('div').append('<div id="' + type + 'add-new-modal" class="add-new-modal" style="display:none;"><a href="http://www.google.com">Close</a>INSERT FORM HERE</div>');

            }
        });

    $("#dialog").dialog({
        autoOpen: false,
        modal: true,
        width: 600,
        height: 400,
        buttons: {
            "Close": function() {
                $(this).dialog("close");
            }
        }
    });

    $(".modal").on("click", function(e) {
        console.log('clicked');
        //e.preventDefault();
        $("#dialog").html("");
        console.log('html');
        $("#dialog").dialog("option", "title", "Loading...").dialog("open");
        console.log('dialog');
        $("#dialog").load('toolkit.dev', function() {
            console.log('load');
        //    //$(this).dialog("option", "title", $(this).find("h2").text());
        //    //$(this).find("h1").remove();
        });
    });

})(jQuery);
