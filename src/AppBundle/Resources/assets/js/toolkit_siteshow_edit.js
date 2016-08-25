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
                $('#loader').css('display', 'none');
            });
        }
    });
    // $(".sortable-items").disableSelection();

    // Toggle between preview and edit mode for content blocks and tabs
    $('#link-preview-edit').on('click', function (e) {
        e.preventDefault();
        $(".editable-tour-name").editable("enable");
        var t = $('.mode-toggle');
        if (t.hasClass('mode-preview')) {
            // Switch to edit mode
            t.addClass('mode-edit').removeClass('mode-preview');
            $(this).html('Switch to Preview Mode');
            if ( toolkitBreakpointAllowDrag() ) {
                // Sorting not allowed on phone for content blocks
                $(".sortable-items").sortable("enable");
            };
            $('.add-content-block').show() // show ALL Add Block's for all tabs
            window.location.hash = 'mode-edit';
        } else {
            // Switch to preview mode
            t.addClass('mode-preview').removeClass('mode-edit');
            $(".editable-tour-name").editable("disable");
            $(this).html('Switch to Edit Mode');
            $(".sortable-items").sortable("disable");
            $('.add-content-block').hide() // hide ALL Add Block's for all tabs
            window.location.hash = 'mode-preview';
        }
    });

    // Edit tabs popup
    $("#edit-tabs").on('click', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        var entityPath = $('.site-show').attr('entityPath');
        toolkitStandardPopup("Rearrange Tabs", "/manage/" + entityPath + "/show/tabs/" + entityId, function () {
            $(".modal-sortable-tabs").sortable("enable");
        });
    });

    // Add a new tab to a site
    $("#add-new-tab").on('click', function (e) {
        var entityId = $('.site-show').attr('entityId');
        var entityPath = $('.site-show').attr('entityPath');
        contentBlocksNewTab( entityId, entityPath, window.location.hash.substr(1) );
    });

    // Edit header slideshow in layout mode
    $(document).on('click', '#edit-header-block', function (e) {
        e.preventDefault();
        $("#loader").css("display", "block");
        var blockId = $('#header-block-content-item').attr('blockId');
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        $("#site-header-editForm").load('/manage/headerblock/' + blockId + '/edit/layout-editor/' + entityId + '/' + entityClass, function () {
            $('#site-header-editForm .button-row').append( '<a id ="site-header-editForm-header-cancel" tabId="header" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored content-block-cancel" style="background-color: red; color: white;" href="#">Cancel</a>' );
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $('.add-content-block').hide();
            $('#site-header-editForm').show();
            $('#site-header-slideshow').hide();
            doMDLpopup($('#site-header-editForm')); // run the function to add appropriate MDL classes to form elements
            $('#site-header-editForm-header-cancel').show();
            $("#loader").css("display", "none");
            // bind header edit form and provide a simple callback function
            $('#ajax_headerblock_layout_form').ajaxForm({
                success: function (response) {
                    // redraw the area by loading a twig file
                    if ($(".site-show").attr('page-type') == "show") {
                        $("#loader").css("display", "block");
                        $('#site-header-editForm').empty();
                        $('#site-header-editForm').hide();
                        $('#loader').css('display', 'none');
                    }else {
                        $('#site-header-slideshow-content').load('/manage/headerblock/' + blockId + '/show/' + entityId + '/' + entityClass, function () {
                            $('#site-header-editForm').empty();
                            $('#site-header-editForm').hide();
                            $('#site-header-slideshow').show();
                            $('.item-edit').show();
                            $('.flexslider').flexslider({
                                directionNav: true,
                                controlNav: true,
                                smoothHeight: true
                            });
                        });
                    }
                }
            });
        });
    });

    // Add header block in layout mode
    $("#add-header-content").on('click', function (e) {
        e.preventDefault();
        var entityId = $('.site-show').attr('entityId');
        var entityClass = $('.site-show').attr('entityClass');
        $("#loader").css("display", "block");
        $("#site-header-editForm").load('/manage/headerblock/new/' + entityId + '/' + entityClass, function () {
            $('#site-header-editForm .button-row').append( '<a id ="site-header-editForm-header-cancel" tabId="header" class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored content-block-cancel" style="background-color: red; color: white;" href="#">Cancel</a>' );
            $('.item-edit').hide(); // hide ALL edit buttons for all content blocks
            $("#loader").css("display", "none");
            $('#site-header-editForm').show();
            $('#site-header-slideshow').hide();
            doMDLpopup($('#site-header-editForm')); // run the function to add appropriate MDL classes to form elements

            $('#site-header-editForm-header-cancel').show();
            $('#ajax_contentblocks_form').ajaxForm({
                success: function (response) {
                    var blockId = response["id"];
                    //just hide the content block when on the quote show page
                    if ($(".site-show").attr('page-type') == "show") {
                        $("#loader").css("display", "block");
                        $('#site-header-editForm').empty();
                        $('#site-header-editForm').hide();
                        // TOOL 625 - cannot easily be changed
                        window.location.reload(true);
                    }else {
                        // redraw the area by loading a twig file
                        $('#site-header-slideshow-content').load('/manage/headerblock/' + blockId + '/show/' + entityId + '/' + entityClass, function () {
                            $('#site-header-editForm').empty();
                            $('#site-header-editForm').hide();
                            $('#site-header-slideshow').show();
                            $('.item-edit').show();
                            $('.flexslider').flexslider({
                                directionNav: true,
                                controlNav: true,
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
                }
            });
        });
    });

    // Cancel function for inline content block creation
    $(document).on('click', '.content-block-cancel', function (e) {
        var tabId = $(this).attr("tabId");
        if(tabId == 'header') {
            $('#site-header-editForm').empty().hide();
            $('#site-header-slideshow').show();
        } else {
            $('#content-block-editForm-' + tabId).empty().hide();
            $(".site-content-blocks-edit").show();
        }
        $('.item-edit').show();
        $(this).hide();
        //$('.add-content-block').hide() // hide ALL Add Block's for all tabs
        if ( toolkitBreakpointAllowDrag() ) {
            // Sorting not allowed on phone for content blocks
            $(".sortable-items").sortable("enable");
            $('.noembed-meta-info').remove();
        };
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
                    $('#loader').css('display', 'block');
                    // TOOL-625 Cannot easily replace this as markup is complex
                    window.location.reload(true);
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
            $('.noembed-meta-info').remove();
            $(".sortable-items").sortable("disable");
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

    // Toggle the hide/show on alternate quote tab
    $('.alt-hide').click(function () {
        $("#loader").css("display", "block");
        var entityId = $('.site-show').attr('entityId');
        var t = $(this);
        $.ajax({
            url: '/manage/quoteversion/' + entityId + '/hideAlt',
            headers: {
                "Pragma": "no-cache",
                "Expires": -1,
                "Cache-Control": "no-cache"
            }
        }).done(function () {
            t.toggleClass('fa-eye').toggleClass('fa-eye-slash');
            if (t.attr('hide') == true) {
                t.attr('hide', false)
                    .attr('title', 'Visible to Everyone');
            } else {
                t.attr('hide', true)
                    .attr('title', 'Only visible to Brand Administrators');
            }
            $("#loader").css("display", "none");
        });
    });

    // Do all things for the loaded page to check various settings

    // Disable drag-and-drop on page load b/c the default is preview mode
    $(".sortable-items").sortable("disable");
    $(".editable-tour-name").editable("disable");

    // Check whether the page should be in edit mode
    if ( window.location.hash.substr(1) === 'mode-edit' ) {
        $('#link-preview-edit').trigger('click');
    };

});
