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

    //trim double spaces from concatenated table cells
    if ($('.mdl-data-table').length > 0) {
        $("td").each(function() {
            var $this = $(this);
            $this.html($this.html().replace(/&nbsp;/g, ''));
        });
    }

    //force numeric on amount fields
    $('input[type="number"]').keypress(function(key) {
        if(key.charCode == 101 || key.charCode == 69) return false;
    });



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
            var modal_title = "Create New " + type;
            var modal_url = "/ajax/" + type.toLowerCase() + "/new";
            $(element).parent('div').parent('div').append('<div class="add-new modal" data-modal-title="' + modal_title + '" data-modal-url="' + modal_url + '"><span class="fa-stack"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x fa-inverse"></i></span></div>');
        }
    });

    $("#dialog").dialog({
        modal: true,
        width: '620px',
        height: 'auto',
        draggable: false,
        resizable: false,
        title: 'Loading...',
        autoOpen: false,
        open: function (e, ui) {
            $(this).parent().removeClass('has-mdl-submit-row');
            $(this).parent().addClass('mdl-shadow--8dp');
            if ( !toolkitBreakpointAllowDrag() ) {
                // Modal to full screen on phone
                $("#dialog")
                    .dialog("option", "width", "100%")
                    .dialog("option", "height", 'auto' );
            } else {
                $("#dialog")
                    .dialog("option", "width", "620px")
                    .dialog("option", "height", "auto" )
            };
        }
    });

    $(".modal").on("click", function (e) {
        var modal = e.currentTarget;
        var modal_title = $(modal).attr('data-modal-title');
        var modal_url = $(modal).attr('data-modal-url');
        toolkitStandardPopup(modal_title, modal_url);
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
                    alert('No suggested results found for ' + text);
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

    // close flash messages
    $( document ).on('click', '.snackclose', function() {
        //$('.snack-wrap').css('display', 'none');
        $('.snack-wrap').fadeOut('slow', 'linear');
    });

    $(document).on('click', '.caller-btn', function(e) {
        var pageURL = $(location).attr("href");
        var pageTitle = $("h1").text().trim();
        var href = $(this).attr('href');
        $(this).attr('href', href + '?ref=' + pageURL + '&title=' + pageTitle);
    });

})(jQuery);

// Event handler for copy to clipboard button
function copyToClipboard(e) {
    e.preventDefault();
    // Find target element.
    var
        trigger = e.currentTarget,
        target_id = trigger.dataset.clipboardTarget,
        target_element = (target_id ? document.querySelector('#' + target_id) : null);

    // Is element selectable?
    if (target_element && target_element.select) {
        // Select text
        target_element.select();

        try {
            // Copy text
            document.execCommand('copy');
            target_element.blur();

            // Copied animation
            if (e.currentTarget.hasAttribute("brandPrimaryColor")) {
                trigger.style.backgroundColor = e.currentTarget.getAttribute("brandPrimaryColor");
            }
        }
        catch (err) {
            alert('Please press Ctrl/Cmd+C to copy');
        }
    }
}
