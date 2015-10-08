(function ($) {

    // MDL additions
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

    // Work on date fields
    $('input[type="date"]').addClass('mdl-date').attr('type', 'text');
    $('.mdl-date').change(function () {
        $(this).parent().addClass('is-dirty');
    });

    // Work on table scrolling
    $('.mdl-data-table').wrap('<div class="mld-data-table__wrapper" />')
        .css('margin-bottom', 0)
        .parent().css({
            'overflow': 'auto',
            'margin-bottom': '1em'
        });

    // Turn Row Actions into a dropdown menu
    if ($('.grid-row-actions').length) {
        $('.grid-row-actions').each(function (index) {
            var size = $(this).children().length;
            var btn = "rowactionbtn-" + index;
            $(this).parent().prepend('<a id="'+ btn +'" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"><i class="fa fa-ellipsis-v"></i></a>');
            $(this).addClass('mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect');
            $(this).attr('for', btn);
            var pixels = (50 * size) + 34;
            $(this).parent().find('.mdl-menu').css({'min-width': pixels + 'px'});

            $(this).find('a').each(function () {
                $(this).addClass('mdl-menu__item');
            });
        });
        $('table td .fa').css({
            'position': 'relative',
            'top': '50%',
            'transform': 'translateY(-50%)',
            '-ms-transform': 'translateY(-50%)',
            '-webkit-transform': 'translateY(-50%)'
        });
    }
    else if ($('.table-actions').length) {
        $('.table-actions').each(function (index) {
            var size = $(this).children().length;
            var btn = "rowactionbtn-" + index;
            $(this).parent().prepend('<a id="'+ btn +'" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"><i class="fa fa-ellipsis-v"></i></a>');
            $(this).addClass('mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect');
            $(this).attr('for', btn);
            var pixels = (50 * size) + 30;
            $(this).parent().find('.mdl-menu').css({'min-width': pixels + 'px'});

            $(this).find('a').each(function () {
                $(this).addClass('mdl-menu__item');
            });
        });
        $('table td .fa').css({
            'position': 'relative',
            'top': '50%',
            'transform': 'translateY(-50%)',
            '-ms-transform': 'translateY(-50%)',
            '-webkit-transform': 'translateY(-50%)'
        });
    }
    ;
    //
    if ($(".mdl-data-table").length) {
        var tableRow = $("a[title='Unlock']").parent().parent().parent().parent();
        tableRow.css({"background-color": "#ccc", "opacity": "0.4"});
        tableRow.append('<i style = "position:absolute; right:55%;"class="fa fa-unlock-alt fa-4x"></i>');
    }

})(jQuery);
