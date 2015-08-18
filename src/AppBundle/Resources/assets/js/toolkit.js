(function ($) {
    /**
     * Material Design Stuff
     */

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
    $('input.color_picker')
        .parent().removeClass().addClass('mdl-colorfield')
        .find('label').removeClass().addClass('mdl-label-mimic');

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
        $('h2').text('Reset Password').show();
        $("#username").attr("placeholder", "Email Address");
        $('.login-reset a').attr("href", "/").text("Sign In").css({"color": "#8DC74B"});

    }

    if ($('#fos_user_resetting_form').length) {
        $("#fos_user_resetting_form_plainPassword_first").attr("placeholder", "New Password");
        $("#fos_user_resetting_form_plainPassword_second").attr("placeholder", "Confirm New Password");
        $('h2').text("Reset Password").show();
        $('.login-reset a').attr("href", "/").text("Sign In").css({"color": "#8DC74B"});
    }


    // *
    // "Add New" Link and Dialog modal for New Quote form
    // *
    $('body').append('<div id="dialog"></div>');
    var elements = {
        '#tui_toolkit_quotebundle_quoteversion_quoteReference_organizer': 'Organizer',
        '#tui_toolkit_quotebundle_quoteversion_quoteReference_institution': 'Institution'
    };
    $.each(elements, function (element, type) {
        if (element.length) {
            //  source a button or glyph here
            $(element).parent('div').parent('div').append('<div id= "' + type.toLowerCase() + '-add-new-link" class="add-new modal"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x fa-inverse"></i></span></div>');
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

    $(".modal").on("click", function (e) {
        var modal_form = e.currentTarget.id;
        var parts = modal_form.split("-add");
        var form_type = parts[0].toLowerCase();
        //e.preventDefault();
        $("#dialog").html("");
        $("#dialog").dialog("option", "title", "Loading...").dialog("open");
        $("#dialog").load('/ajax/' + form_type + '/new', function () {
            $(this).dialog("option", "title", 'Create New ' + parts[0]);
            $(this).find('.mdl-textfield__input').each(function () {
                if ($(this).attr('required')) {
                    $(this).parent().addClass('is-invalid');
                }
                ;
            });
        });
    });

    $(document).on('focus', '.ui-dialog .mdl-textfield__input', function () {
        $(this).parent().addClass('is-focused');
    }).on('blur', '.ui-dialog .mdl-textfield__input', function () {
        $(this).parent().removeClass('is-focused');
    }).on('change paste keyup', '.ui-dialog .mdl-textfield__input', function () {
        if ($(this).val()) {
            $(this).parent().addClass('is-dirty').addClass('is-upgraded').removeClass('is-invalid');
        } else {
            $(this).parent().removeClass('is-dirty').removeClass('is-upgraded');
            if ($(this).attr('required')) {
                $(this).parent().addClass('is-invalid');
            }
            ;
        }
    });

    /*
     * Autocomplete Handle empty responses
     */

    var suggest = ['tui_toolkit_quotebundle_quoteversion_quoteReference_organizer',
        'tui_toolkit_quotebundle_quoteversion_quoteReference_institution',
        'tui_toolkit_quotebundle_quoteversion_quoteReference_salesAgent',
        'tui_toolkit_quotebundle_quoteversion_quoteReference_secondaryContact'];

    $.each(suggest, function (index, formfield) {
        $('#' + formfield).autocomplete({
            response: function (event, ui) {
                var label = $("label[for='" + event.target.id + "']");
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

    $('.media-placeholder-image').on("click", function () {
        $('.media-placeholder-image').css({"display": "none"});
        $("#dropzone_form").css({"display": "block"});
    });

    $('.media-placeholder-image').on("click", function () {
        $('.media-placeholder-image').css({"display": "none"});
        $("#dropzone_form").css({"display": "block"});
        $("#dropzone-form-close").css({"display": "inline-block"});
    });

    $('#avatar-label').on("click", function () {
        $('.media-placeholder-image').css({"display": "block"});
        $("#dropzone_form").css({"display": "none"});
        $("#dropzone-form-close").css({"display": "none"});
    });

    //if ($("#dropzone_form").hasClass("dz-max-files-reached")) {
    $("#dropzone_form").submit(function () {
        console.log('dropzone submitted remove image');
        $("img#user_media").css({
            display: "none"
        });
    });

    /**
     * Drag and Drop Sorting
     */

    $(".sortable-tabs").sortable({
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
        start: function (e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        axis: 'y',
        update: function(e, ui) {
            var pathArray = window.location.pathname.split( '/' );
            contentBlocksUpdate(pathArray[3]); // fourth [3] part in the path should be quote ID to pass in
        }
    });


    $( ".sortable-items" ).sortable({
        containment: "document",
        items: "> div",
        tolerance: "pointer",
        connectWith: '.sortable-items',
        placeholder: "items-placeholder",
        start: function (e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        axis: 'y',
        update: function(e, ui) {
            var pathArray = window.location.pathname.split( '/' );
            contentBlocksUpdate(pathArray[3]); // fourth [3] part in the path should be quote ID to pass in
        }
    });

    /**
     * Add font-awesome to Delete buttons
     */

    $('.button-row').children(':button').each(function () {
        if ($.trim($(this).html()) == 'Delete') {
            $(this).html('<i class="fa fa-trash-o"></i> Delete');
        }
        if ($.trim($(this).html()) == 'Update') {
            $(this).html('<i class="fa fa-check-circle"></i> Update');
        }
    })

    /**
     * Add, Edit and Delete Content Block Tabs
     *
     */



})(jQuery);

/********* Global Methods Go below here ******************************/

/**
 * Persist Content block data to the database/entity
 * @param id - Quote Version # passed from window.path
 */

var contentBlocksUpdate = function (id) {
    // update server with new data
    var result = {};
    var data = $(".content-blocks-tab");
    data.each(function (i, obj) {
        tabText = $(this).find('.editable-tab').text();
        if ($(this).find('.content-blocks-item').size() == 0) {
            result[tabText] = '';
        } else {
            result[tabText] = [];
            var children = []
            $(this).find('.content-blocks-item').each(function (k, v) {
                children.push(this.id);
            });
            result[tabText] = children;
        }
    });
    console.log(result);
    //POST to server using $.post or $.ajax
    $.ajax({
        data: result,
        type: 'POST',
        url: '/manage/contentblocks/update/'+ id
    });
    //reload the window so changes are redrawn - its lazy non-ajaxy, but...
    contentBlocksRefresh(id);
};

/**
 * Add a New Tab for Content Blocks
 * @param elem The parent container of the content blocks
 * @param id The id of the QuoteVersion Object that owns the content blocks
 */

var contentBlocksAddTab= function (elem, id){
    var newId = $(elem).children().length;
    $("#content-blocks-wrapper").prepend(
        '<div id="tab-tab'  + (newId + 1)+ '" class="content-blocks-tab">' +
        '<span class="content-blocks tab-label"><i class="content-block-tab-handle fa fa-arrows"></i>  <h4 id="tab-label-{{ tab }}" class="editable-tab"> New Tab '  + (newId + 1)+ '</h4>' +
        '<i class="tab-delete content-block-tab-actions fa fa-trash-o"> Delete Tab</i>' +
        '<i class="tab-new content-block-tab-actions fa fa-plus-circle"> Add Content</i>' +
        '</span>' +
        '<div id="tabs-drawer-tab' + (newId +1) + '" class="sortable-items content-blocks-drawer">' +
        '</div></div>'
    );

    $(".sortable-tabs").sortable('refresh');
    $(".sortable-items").sortable();
    contentBlocksUpdate(id);
}

/**
 * Reload the page that shows the content blocks and tabs
 * @param id
 */
var contentBlocksRefresh = function(id){
    $.ajax({
        url: window.location.href,
        headers: {
            "Pragma": "no-cache",
            "Expires": -1,
            "Cache-Control": "no-cache"
        }
    }).done(function () {
        window.location.hash = 'site-content';
        window.location.reload(true);
    });
}

