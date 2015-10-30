(function ($) {

    if ($('#reset-password-form').length) {
       // $("#fos_user_resetting_form_plainPassword_first").attr("placeholder", "New Password");
       // $("#fos_user_resetting_form_plainPassword_second").attr("placeholder", "Confirm New Password");
        $('h2').text("Reset Password").show();
        $('.login-reset a').attr("href", "/").text("Sign In").css({"color": "#8DC74B"});
    }

    if($('#activation-form').length) {
        //$('#fos_user_resetting_form_plainPassword_first').attr("placeholder", "Password");
        //$('#fos_user_resetting_form_plainPassword_second').attr("placeholder", "Confirm Password");
        $('h2').text("Activate Account").show();
        $('.login-reset').css({'display': 'none'});
        $('input[type="submit"]').css({'margin-bottom': '20px'});
        $('.login-block .login-form > div').css({'color': 'black'});

    }

    if ($('.profile-content').length) {
        $('.login-block').removeClass('login-block');
    }


    // *
    // "Add New" Link and Dialog modal for New Quote form
    // *
    $('body').append('<div id="dialog"></div>');
    var elements = {
        '#tui_toolkit_quotebundle_quoteversion_quoteReference_organizer': 'Organizer',
        '#tui_toolkit_quotebundle_quoteversion_quoteReference_institution': 'Institution',
        '#tui_toolkit_tourbundle_tour_organizer': 'Organizer',
        '#tui_toolkit_tourbundle_tour_institution': 'Institution'
    };
    $.each(elements, function (element, type) {
        if (element.length) {
            //  source a button or glyph here
            $(element).parent('div').parent('div').append('<div id= "' + type.toLowerCase() + '-add-new-link" class="add-new modal"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x fa-inverse"></i></span></div>');
        }
    });

    $("#dialog").dialog({
        modal: true,
        width: '60%',
        height: 400,
        draggable: false,
        resizable: false,
        title: 'Loading...',
        autoOpen: false,
        open: function (e, ui) {
            $(this).parent().addClass('mdl-shadow--8dp');
            if ( !toolkitBreakpointAllowDrag() ) {
                // Modal to full screen on phone
                $("#dialog")
                    .dialog("option", "width", "100%")
                    .dialog("option", "height", $(window).height() );
            } else {
                $("#dialog")
                    .dialog("option", "width", "60%")
                    .dialog("option", "height", 400 );
            };
        }
    });

    $(".modal").on("click", function (e) {
        var modal_form = e.currentTarget.id;
        var parts = modal_form.split("-add");
        var form_type = parts[0].toLowerCase();
        //e.preventDefault();
        $("#dialog").html("");
        $("#dialog").dialog("open");
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
        'tui_toolkit_quotebundle_quoteversion_quoteReference_secondaryContact',
        'tui_toolkit_tourbundle_tour_organizer',
        'tui_toolkit_tourbundle_tour_institution',
        'tui_toolkit_tourbundle_tour_salesAgent',
        'tui_toolkit_tourbundle_tour_secondaryContact'];

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

    /**
     * Add a mimic label to the ckeditor
     */

    $('textarea').each( function() {
        if ( !$(this).next().is('label') ) {
            var labelText = $(this).attr('name');
            labelText = labelText.split('[');
            labelText = labelText[1].split(']');
            labelText = labelText[0].replace(/([a-z])([A-Z])/g, '$1 $2');
            labelText = labelText.toLowerCase();
            labelText = labelText.charAt(0).toUpperCase() + labelText.slice(1);
            $(this).before('<label class="mdl-label-mimic">' + labelText + '</label>');
            $(this).parent().addClass('cke-wrapper');
        };
    });

    $(document).on('change', '#tui_toolkit_tourbundle_toursetup_pricePersonPublic', function(e) {

        var price = $(this).val();
        var passengers = $("#passengers").text();
        var newTotal = Number(price) * Number(passengers);
        var adjusted =  $('#adjusted-price');

        adjusted.text(newTotal);
        var total = $("#total").text();
        if (Number(newTotal) < Number(total)){
            adjusted.css({"color":"red"});
        }
        else if (Number(newTotal) >= Number(total)){
            adjusted.css({"color":"green"});
        }
    });

})(jQuery);
