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

    //Turn Row Actions into a dropdown menu
    if ($('.grid-row-actions').length) {
        $('.grid-row-actions').each(function (index) {
            console.log(index);
            var btn = "rowactionbtn-" + index;
            console.log(btn);
            $(this).parent().prepend('<a id="'+ btn +'" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"><i class="material-icons">arrow_drop_down</i></a>');
            $(this).addClass('mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect');
            $(this).attr('for', btn);
            $('.grid-row-actions.mdl-menu').css({'min-width': '330px'});

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
            console.log(index);
            var btn = "rowactionbtn-" + index;
            console.log(btn);
            $(this).parent().prepend('<a id="'+ btn +'" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon"><i class="material-icons">arrow_drop_down</i></a>');
            $(this).addClass('mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect');
            $(this).attr('for', btn);

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

    })(jQuery);
