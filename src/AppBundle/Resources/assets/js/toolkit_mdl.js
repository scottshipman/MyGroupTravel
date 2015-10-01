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
            'overflow': 'scroll',
            'overflow': 'auto',
            'margin-bottom': '1em'
        });

})(jQuery);
