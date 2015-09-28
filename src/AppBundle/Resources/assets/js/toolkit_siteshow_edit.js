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
        } else {
            // Switch to preview mode
            t.addClass('mode-preview').removeClass('mode-edit');
            $(this).html('Switch to Edit Mode');
            $(".sortable-items").sortable("disable");
        }
    });

});
