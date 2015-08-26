(function ($) {

    // Add Placeholder Fields to Login Form
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
            doMDLpopup( $(this) );
        });
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
    });

})(jQuery);
