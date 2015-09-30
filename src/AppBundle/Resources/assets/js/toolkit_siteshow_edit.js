$(document).ready(function () {

    // Do the sortable stuff (after its re-enabled)
    $(".sortable-items").sortable({
        tolerance: "pointer",
        placeholder: "items-placeholder",
        forcePlaceholderSize: true,
        helper: "clone",
        start: function (e, ui) {
            ui.placeholder.height( ui.helper.outerHeight() );
            if ( ui.helper.css('width') != $(this).css('width') ) { // if the item is floated, through an indirect check
                ui.placeholder.css({'float': 'left', 'width': '50%'});
            } else {
                ui.placeholder.css({'float': 'none', 'width': 'auto', 'clear': 'both'});
            }
        },
        update: function(e, ui) {
            var entityId = $('.site-show').attr('entityId');
            var entityClass = $('.site-show').attr('entityClass');
            $("#loader").css("display", "block");
            // scrape DOM to get hierarchy of tabs and blocks
            var result = {};
            var data = $(".view-tab"); // a div for each tab
            data.each(function (i, obj) {
                tabText = $(this).find('.tab-name').text();
                tabId = $(this).attr('tabId');
                if ( $('#tabs-drawer-' + tabId).find('.site-content-blocks-item').size() == 0 ) {
                    result[tabId] = [tabText, new Array()];
                } else {
                    var children = [];
                    $('#tabs-drawer-' + tabId).find('.site-content-blocks-item').each(function (k, v) {
                        var blockId = 'content-blocks-' + $(this).attr('blockId');
                        children.push( blockId );
                    });
                    result[tabId] = [tabText, children];
                }
            });
            // POST to server using $.ajax
            $.ajax({
                data: result,
                type: 'POST',
                url: '/manage/contentblocks/update/' + entityId + '/' + entityClass
            }).done(function() {
                window.location.reload(true);
            });
        }
    });
    $(".sortable-items").disableSelection();

    // Disable drag-and-drop on page load b/c the default is preview mode
    $(".sortable-items").sortable("disable");

    // Toggle between preview and edit mode for content blocks and tabs
    $('#link-preview-edit').on('click', function (e) {
        e.preventDefault();
        var t = $('.mode-toggle');
        if (t.hasClass('mode-preview')) {
            // Switch to edit mode
            t.addClass('mode-edit').removeClass('mode-preview');
            $(this).html('Switch to Preview Mode');
            $(".sortable-items").sortable("enable");
            $('.add-content-block').show() // show ALL Add Block's for all tabs
        } else {
            // Switch to preview mode
            t.addClass('mode-preview').removeClass('mode-edit');
            $(this).html('Switch to Edit Mode');
            $(".sortable-items").sortable("disable");
            $('.add-content-block').hide() // hide ALL Add Block's for all tabs

        }
    });

    // Edit tabs popup
    $("#edit-tabs").on('click', function (e) {
        var entityId = $('.site-show').attr('entityId');
        var entityPath = $('.site-show').attr('entityPath');
        $("#dialog").html("");
        $("#dialog").dialog({width: '60%'}).dialog("option", "title", "Loading...").dialog("open");
        $("#dialog").load('/manage/' + entityPath + '/show/tabs/' + entityId, function () {
            $(this).dialog("option", "title", "Rearrange Tabs");
            $(".modal-sortable-tabs").sortable("enable");
        });
    });

    // Add a new tab to a site
    $("#add-new-tab").on('click', function (e) {
        var entityId = $('.site-show').attr('entityId');
        var entityPath = $('.site-show').attr('entityPath');
        contentBlocksNewTab( entityId, entityPath );
    });

    // Edit header slideshow in layout mode
    $(document).on('click', '#edit-header-block', function (e) {
        e.preventDefault();
        $("#loader").css("display", "block");
        var blockId = $('#header-block-content-item').attr('blockId')
        $("#site-header-editForm").load('/manage/headerblock/header/edit/layout-editor/' + blockId, function () {
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $('.add-content-block').hide();
            $('#site-header-editForm').show();
            $('#site-header-slideshow').hide();
            doMDLpopup($('#site-header-editForm')); // run the function to add appropriate MDL classes to form elements
            $("#loader").css("display", "none");
            // bind header edit form and provide a simple callback function
            $('#ajax_headerblock_layout_form').ajaxForm({
                success: function (response) {
                    // redraw the area by loading a twig file
                    $('#site-header-slideshow-content').load('/manage/headerblock/header/show/' + blockId, function () {
                        $('#site-header-editForm').empty();
                        $('#site-header-editForm').hide();
                        $('#site-header-slideshow').show();
                        $('.item-edit').show();
                        $('.flexslider').flexslider({
                            directionNav: false,
                            controlNav: false,
                            smoothHeight: true
                        });
                    });
                }
            });
        });
    });

    // Add header block in layout mode
    $("#add-header-content").on('click', function (e) {
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        $("#loader").css("display", "block");
        $("#site-header-editForm").load('/manage/headerblock/header/new/' + entityId + '/' + entityClass, function () {
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $("#loader").css("display", "none");
            $('#site-header-editForm').show();
            $('#site-header-slideshow').hide();
            doMDLpopup($('#site-header-editForm')); // run the function to add appropriate MDL classes to form elements
            $('#ajax_contentblocks_form').ajaxForm({
                success: function (response) {
                    var blockId = response["id"];
                    // redraw the area by loading a twig file
                    $('#site-header-slideshow-content').load('/manage/headerblock/header/show/' + blockId, function () {
                        $('#site-header-editForm').empty();
                        $('#site-header-editForm').hide();
                        $('#site-header-slideshow').show();
                        $('.item-edit').show();
                        $('.flexslider').flexslider({
                            directionNav: false,
                            controlNav: false,
                            smoothHeight: true
                        });
                        $('#site-header-slideshow').prepend(
                            '<div class="site-content-blocks-item">' +
                            '<div class="status-preview-edit site-content-block-actions">' +
                            '<div class="edit-icons">' +
                            '<i id="edit-header-block" class="content-block-item-action fa fa-pencil-square-o" title="Edit"></i>' +
                            '<i id="lock-header-block" class="content-block-item-action fa fa-lock" title="Lock"></i>' +
                            '</div>' +
                            '</div>' +
                            '</div>'
                        );
                    });
                }
            });
        });
    });

    // Cancel function for inline content block creation
    $(document).on('click', '.content-block-cancel', function (e) {
        var tabId = $(this).attr("tabId");
        $('#content-block-editForm-' + tabId).empty().hide();
        $('.item-edit').show();
        $(".site-content-blocks-edit").show();
        $(this).hide();
        //$('.add-content-block').hide() // hide ALL Add Block's for all tabs
        $(".sortable-items").sortable("enable");
    });

    // Inline content block creation
    $(".site-content-blocks-edit").on('click', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        $("#loader").css("display", "block");
        var tabId = $(this).attr("tabId");
        $("#content-block-editForm-" + tabId).load('/manage/contentblocks/new/' + entityClass + '/' + entityId +'/' + tabId, function () {
            $("#content-block-editForm-" + tabId + " .button-row").append( '<a id ="content-block-editForm-' + tabId + '-cancel" tabId="' + tabId + '" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored content-block-cancel" style="display: none; background-color: red; color: white;" href="#">Cancel</a>' );
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $("#loader").css("display", "none");
            $('#content-block-editForm-' + tabId).show();
            $('#content-block-editForm-' + tabId + '-cancel').show();
            $(".site-content-blocks-edit").hide();
            $(".sortable-items").sortable("disable");
            doMDLpopup($('#content-block-editForm-' + tabId)); // run the function to add appropriate MDL classes to form elements
            $('#newBlockIdForm').ajaxForm({
                beforeSerialize: function () {
                    CKEDITOR.instances.tui_toolkit_contentblocksbundle_contentblock_body.updateElement();
                },
                success: function (response) {
                    $('#content-block-editForm-' + tabId).empty().hide();
                    $("#loader").css("display", "block");
                    window.location.reload();
                }
            });
        });
    });

    // Do the resize content block stuff - on the fly!
    $('.item-resize').click(function () {
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        var t = $(this);
        $("#loader").css("display", "block");
        var blockID = t.attr('blockId');
        $.ajax({
            url: '/manage/contentblocks/' + blockID + '/resize/' + entityId + '/' + entityClass,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function () {
            if (t.hasClass('half')) {
                // Will now be full
                t.addClass('full').removeClass('half');
                $('#content-blocks-' + blockID).addClass('site-content-blocks-size-full').removeClass('site-content-blocks-size-half');
                $('#content-block-id-' + blockID).addClass('content-block-size-full').removeClass('content-block-size-half');
            } else {
                // Will now be half
                t.addClass('half').removeClass('full');
                $('#content-blocks-' + blockID).addClass('site-content-blocks-size-half').removeClass('site-content-blocks-size-full');
                $('#content-block-id-' + blockID).addClass('content-block-size-half').removeClass('content-block-size-full');
            }
            $("#loader").css("display", "none");
        });
    });

    // Edit a content block
    $(".item-edit").on('click', function () {
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        var t = $(this);
        $("#loader").css("display", "block");
        var blockId = t.attr('blockId');
        var editBlock = $("#editable-content-blocks-" + blockId);
        var previewBlock = $("#previewable-content-blocks-" + blockId);
        editBlock.html('');
        editBlock.load('/ajax/contentblocks/' + blockId + '/edit/' + entityId + '/' + entityClass, function () {
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $('.add-content-block').hide() // hide ALL Add Block's for all tabs
            doMDLpopup(editBlock); // run the function to add appropriate MDL classes to form elements
            previewBlock.html('');
            $("#loader").css("display", "none");
        });
    });

    // Toggle the locked boolean on a content block - on the fly!
    $(".item-lock").on('click', function (e) {
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        var t = $(this);
        $("#loader").css("display", "block");
        var blockID = t.attr('blockId');
        $.ajax({
            url: '/manage/contentblocks/' + blockID + '/lock/' + entityId + '/' + entityClass,
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function () {
            t.toggleClass('fa-unlock-alt').toggleClass('fa-lock');
            if (t.attr('title') == 'Unlock') {
                // Will now be unlocked
                t.parent().removeClass('content-block-locked');
                t.attr('title', 'Lock');
            } else {
                // Will now be locked
                t.parent().addClass('content-block-locked');
                t.attr('title', 'Unlock');
            }
            $("#loader").css("display", "none");
        });
    });

    // Toggle the header summary lock
    $(document).on('click', '#lock-header-summary', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        var entityPath = $('.site-show').attr('entityPath');
        var editElement = $('#edit-header-summary');
        $("#loader").css("display", "block");
        var action = $(this).attr('action');
        $.ajax({
            url: '/manage/' + entityPath + '/' + entityId + '/lock',
        }).done(function () {
            if (action == 'lock') {
                if (editElement) {
                    editElement.css('display','none');
                }
                $('#lock-header-summary').addClass('fa-unlock-alt');
                $('#lock-header-summary').removeClass('fa-lock');
                $('#lock-header-summary').attr('action', 'unlock');
            } else {
                if (editElement) {
                    editElement.css('display','inline-block');
                } else {
                    $(this).parent('.edit-icons').prepend('<i id="edit-header-summary" class="content-block-item-action item-edit fa fa-pencil-square-o" title="Edit"></i>');
                };
                $('#lock-header-summary').addClass('fa-lock');
                $('#lock-header-summary').removeClass('fa-unlock-alt');
                $('#lock-header-summary').attr('action', 'lock');
            }
            $("#loader").css("display", "none");
        });
    });

    // Edit header summary in layout mode
    $(document).on('click', '#edit-header-summary', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        var entityType = $('.site-show').attr('entityType');
        $("#loader").css("display", "block");
        $("#site-header-summary-form").load('/' + entityType + '/view/summary/edit/' + entityId, function () {
            $("#loader").css("display", "none");
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $('#site-header-summary-form').show();
            $('#site-header-summary').hide();
            $('#site-header-slideshow').hide();
            $('.form-summary-edit-form').find('.button-row').append('<a href="#" id="cancel-reload" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored">Cancel</a>');
            doMDLpopup($('#site-header-summary-form')); // run the function to add appropriate MDL classes to form elements
            // bind header edit form and provide a simple callback function
            $('#form-summary-edit-form').ajaxForm({
                beforeSerialize: function () {
                    if ( entityType === 'quote' ) {
                        CKEDITOR.instances.tui_toolkit_quotebundle_quotesummary_welcomeMsg.updateElement();
                    } else {
                        CKEDITOR.instances.tui_toolkit_tourbundle_toursummary_welcomeMsg.updateElement();
                    };
                },
                success: function (response) {
                    // redraw the area by loading a twig file
                    $('#site-header-summary-content').load('/' + entityType + '/view/summary/show/' + entityId, function () {
                        $('#site-header-summary-form').empty();
                        $('#site-header-summary-form').hide();
                        $('#site-header-summary').show();
                        $('#site-header-slideshow').show();
                        $('.item-edit').show();
                        // Copy to Clipboard
                        ZeroClipboard.config({swfPath: "//cdnjs.cloudflare.com/ajax/libs/zeroclipboard/2.2.0/ZeroClipboard.swf"});
                        var client = new ZeroClipboard(document.getElementById("copy-button"));
                        client.on('aftercopy', function (event) {
                            $('#copy-button').css('background-color', 'green');
                        });
                    });
                }
            });
        });
    });

    // Delete a content block
    $(document).on('click', '.item-delete', function (e) {
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        if ( confirm('Are you sure you want to remove this item? Warning: this can not be undone.') ) {
            var blockId = $(this).attr('blockId');
            $.ajax({
                method: 'DELETE',
                url: '/manage/contentblocks/' + blockId + '/delete/' + entityId + '/' + entityClass,
                headers: {
                    "Pragma": "no-cache",
                    "Expires": -1,
                    "Cache-Control": "no-cache"
                }
            }).done(function () {
                $('#content-blocks-' + blockId).remove();
            });
        }
    });

});
