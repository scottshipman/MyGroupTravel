/********* Global Methods Go below here ******************************/

/**
 * Persist Content block data to the database/entity
 * @param id - Quote Version # passed from window.path
 */

var contentBlocksUpdate = function (id, classtype) {
    // update server with new data
    $("#loader").css("display", "block");
    var result = {};
    var data = $(".content-blocks-tab");
    data.each(function (i, obj) {
        tabText = $(this).find('.editable-tab').text();
        tabId = $(this).attr('id');
        if ($(this).find('.content-blocks-item').size() == 0) {
            result[tabId] = [tabText, new Array()];
        } else {
            var children = []
            $(this).find('.content-blocks-item').each(function (k, v) {
                children.push(this.id);
            });
            result[tabId] = [tabText, children];
        }
    });
    //POST to server using $.post or $.ajax
    $.ajax({
        data: result,
        type: 'POST',
        url: '/manage/contentblocks/update/'+ id + '/' + classtype
    });
    //reload the window so changes are redrawn - its lazy non-ajaxy, but...
    contentBlocksRefresh(id);
};

/**
 * Add a New Tab for Content Blocks
 * @param elem The parent container of the content blocks
 * @param id The id of the QuoteVersion Object that owns the content blocks
 */

var contentBlocksAddTab= function (elem, id, classtype){
    var newId = new Date().getTime();
    $("#content-blocks-wrapper").prepend(
        '<div id="'  + (newId)+ '" class="content-blocks-tab">' +
            '<span class="content-blocks tab-label">' +
                '<i class="content-block-tab-handle fa fa-arrows"></i> ' +
                '<h4 id="tab-label-'  + (newId)+ '" class="editable-tab"> New Tab </h4>' +
                '<span class="tab-delete icon-label"><i class="content-block-tab-actions fa fa-trash-o"></i> Delete Tab</span>' +
                '<span class="tab-new icon-label"><i class="content-block-tab-actions fa fa-plus-circle"></i> Add Content</span>' +
            '</span>' +
            '<div id="tabs-drawer-tab' + (newId) + '" class="sortable-items content-blocks-drawer"></div>' +
        '</div>'
    );

    $(".sortable-tabs").sortable('refresh');
    $(".sortable-items").sortable();
    contentBlocksUpdate(id, classtype);
}

/**
 * Create new tab and Persist Content block data to the database/entity
 * @param id - Quote Version # passed from window.path
 */

var contentBlocksNewTab = function (id, classtype) {
    // update server with new data
    var newId = new Date().getTime();
    $("#loader").css("display", "block");
    var result = {};
    tabText = "New Tab";
    TabId = newId;
    result[TabId] = [tabText, new Array()];
    //POST to server using $.post or $.ajax
    $.ajax({
        data: result,
        type: 'POST',
        url: '/manage/contentblocks/tab/new/'+ id + '/' + classtype
    });
    //reload the window so changes are redrawn - its lazy non-ajaxy, but...
    contentBlocksRefresh(id);
};

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

// Do lots of MDL stuff when a jQuery modal is opened
var doMDLpopup = function(t) {
    t.addClass('mdl-form-mimic');
    t.find('.mdl-textfield__input').each(function () {
        if ( $(this).val() ) {
            $(this).parent()
                .addClass('is-dirty')
                .addClass('is-upgraded');
        } else {
            if ( $(this).attr('required') ) {
                $(this).parent().addClass('is-invalid');
            };
        };
    });
    t.find('.mdl-checkbox__input').each(function () {
        $(this).parent()
            .addClass('is-upgraded')
            .append('<span class="mdl-checkbox__focus-helper"></span><span class="mdl-checkbox__box-outline"><span class="mdl-checkbox__tick-outline"></span></span>');
        if ( $(this).is(':checked') ) {
            $(this).parent().addClass('is-checked');
        }
    });
    t.find('.mdl-radio__button').each(function () {
        $(this).parent()
            .addClass('is-upgraded')
            .append('<span class="mdl-radio__outer-circle"></span><span class="mdl-radio__inner-circle"></span>');
        if ( $(this).is(':checked') ) {
            $(this).parent().addClass('is-checked');
        }
    });
};

// Do lots of MDL stuff within a jQuery modal window
$(document).on('focus', '.mdl-form-mimic .mdl-textfield__input', function () {
    $(this).parent().addClass('is-focused');
}).on('blur', '.mdl-form-mimic .mdl-textfield__input', function () {
    $(this).parent().removeClass('is-focused');
}).on('change paste keyup', '.mdl-form-mimic .mdl-textfield__input', function () {
    if ($(this).val()) {
        $(this).parent().addClass('is-dirty').addClass('is-upgraded').removeClass('is-invalid');
    } else {
        $(this).parent().removeClass('is-dirty').removeClass('is-upgraded');
        if ($(this).attr('required')) {
            $(this).parent().addClass('is-invalid');
        };
    }
});

$(document).on('click', '.mdl-form-mimic .mdl-checkbox__input', function () {
    if ( $(this).is(':checked') ) {
        $(this).parent().addClass('is-checked');
    } else {
        $(this).parent().removeClass('is-checked');
    }
});

$(document).on('click', '.mdl-form-mimic .mdl-radio__button', function () {
    name = $(this).attr('name');
    $(document).find('input[name="' + name + '"]').parent().removeClass('is-checked');
    if ( $(this).is(':checked') ) {
        $(this).parent().addClass('is-checked');
    } else {
        $(this).parent().removeClass('is-checked');
    }
});
