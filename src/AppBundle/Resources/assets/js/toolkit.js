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
    $("#username").attr("placeholder", "Email");
    $("#password").attr("placeholder", "Password");

    if ($("form").hasClass("fos_user_resetting_request")) {
        $("body").addClass("main_login");
        $('h2.signin-form').text('Reset Password').show();
        $("#username").attr("placeholder", "Username Or Email Address");

    }

    //if ($("div").hasClass("login-message-block")){
    //    $("body").addClass("main_login");
    //}


  // *
  // "Add New" Link and Dialog modal for New Quote form
  // *
    $('body').append('<div id="dialog"></div>');
    var elements = {'#tui_toolkit_quotebundle_quoteversion_quoteReference_organizer':'Organizer',
                    '#tui_toolkit_quotebundle_quoteversion_quoteReference_institution': 'Institution'};
    $.each(elements, function(element, type){
            if ( element.length ) {
                //  source a button or glyph here
                $(element).parent('div').parent('div').append('<div id= "' + type + '-add-new-link" class="add-new modal" style="display:inline;cursor:Pointer"><i class="material-icons">&#xE147;</i></div>');
            }
        });

    $("#dialog").dialog({
        autoOpen: false,
        modal: true,
        width: 600,
        height: 400
/*        buttons: {
            "Close": function() {
                $(this).dialog("close");
            }
        }*/
    });

    $(".modal").on("click", function(e) {
        var modal_form = e.currentTarget.id;
        var parts = modal_form.split("-add");
        var form_type = parts[0].toLowerCase();
        //e.preventDefault();
        console.log(form_type);
        $("#dialog").html("");
        $("#dialog").dialog("option", "title", "Loading...").dialog("open");
        $("#dialog").load('/ajax/' + form_type + '/new', function() {
        $(this).dialog("option", "title", 'Create New ' + parts[0]);
        });
    });

   /*
    * Autocomplete Handle empty responses
    */

    var suggest = ['tui_toolkit_quotebundle_quoteversion_quoteReference_organizer',
                   'tui_toolkit_quotebundle_quoteversion_quoteReference_institution',
                   'tui_toolkit_quotebundle_quoteversion_quoteReference_salesAgent',
                   'tui_toolkit_quotebundle_quoteversion_quoteReference_secondaryContact'];

    $.each(suggest, function(index, formfield){
        $('#' + formfield).autocomplete({
            response: function( event, ui ) {
                var label = $("label[for='"+event.target.id+"']");
                var text = label.text().trim();
                if (ui.content.length == 0) {
                    alert('No suggested results  found for ' + text);
                    $(this).val('');
                }

            }
        });
    })

    /**
     * Dropzone manipulation
     */

    $('.media-placeholder-image').each(function () {
        var mWidth = 300
        var mHeight = 160;
        $(this).css('width', mWidth + 'px');
        $(this).css('height', mHeight + 'px');
        var imgWidth = $(this).width();
        var imgHeight = $(this).height();
        var dropzone_form = $("#dropzone_form");

        dropzone_form.css({
            width: mWidth,
            height: mHeight
        });
    });

    $('.media-placeholder-image').on("click", function(){
        $('.media-placeholder-image').css({"display":"none"});
        $("#dropzone_form").css({"display":"block"});
    });

    $('.media-placeholder-image').on("click", function(){
        $('.media-placeholder-image').css({"display":"none"});
        $("#dropzone_form").css({"display":"block"});
        $("#dropzone-form-close").css({"display":"inline-block"});
    });

    $('#avatar-label').on("click", function(){
        $('.media-placeholder-image').css({"display":"block"});
        $("#dropzone_form").css({"display":"none"});
        $("#dropzone-form-close").css({"display":"none"});
    });

    //if ($("#dropzone_form").hasClass("dz-max-files-reached")) {
    $("#dropzone_form").submit(function(){
        console.log('dropzone submitted remove image');
        $("img#user_media").css({
            display: "none"
        });
    });

    /**
     * Drag and Drop Sorting
     */

    $( ".sortable-tabs" ).sortable({
        containment: "parent",
        items: "> div",
        // handle: ".move",
        tolerance: "pointer",
        cursor: "move",
        opacity: 0.7,
        revert: 300,
        delay: 150,
        dropOnEmpty: true,
        placeholder: "tabs-placeholder",
        start: function(e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        axis: 'y'
        //  update: function (event, ui) {
        //      var data = $(this).sortable('serialize');
        //// POST to server using $.post or $.ajax
        //           $.ajax({
        //                data: data,
        //                type: 'POST',
        //                url: '/your/url/here'
        //            });
        //// }
    });
    //$( ".sortable-tabs" ).disableSelection();
    $( ".sortable-items" ).sortable({
        containment: "document",
        items: "> div",
        tolerance: "pointer",
        connectWith: '.sortable-items',
        placeholder: "items-placeholder",
        start: function(e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        axis: 'y'
        //  update: function (event, ui) {
        //      var data = $(this).sortable('serialize');
        //// POST to server using $.post or $.ajax
        //            $.ajax({
        //                data: data,
        //                type: 'POST',
        //                url: '/your/url/here'
        //            });
        ////  }
    });
//     $( ".sortable-items" ).disableSelection();


    /**
     * Add font-awesome to Delete buttons
     */

    $('.button-row').children(':button').each(function(){
        if ($.trim($(this).html()) == 'Delete'){
            $(this).html('<i class="fa fa-trash-o"></i> Delete');
        }
        if ($.trim($(this).html()) == 'Update'){
            $(this).html('<i class="fa fa-check-circle"></i> Update');
        }
    })


})(jQuery);
