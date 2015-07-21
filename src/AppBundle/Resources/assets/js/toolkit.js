(function ($) {

  $('.mdl-layout__drawer .menu_level_1').each( function() {
    var t = $(this);
    t.parent().addClass('collapsed');
    t.prev().addClass('menu-parent');
    t.children('span').css( 'margin-top', '-' + $(this).css('height') );
    t.css('overflow', 'hidden').hide();
  });

  $('.menu-parent').click( function() { // $(this) is the link-like span
    var t = $(this);
    if ( t.parent().hasClass('collapsed') ) {
      t.parent().removeClass('collapsed').addClass('expanded');
      t.next().show();
      t.next().children('span').animate({
        marginTop: 0
      }, 200 );
    } else {
      t.parent().removeClass('expanded').addClass('collapsed');
      t.next().children('span').animate({
        marginTop: '-' + $(this).next().css('height')
      }, 200, 'swing', function() {
        t.next().hide();
      });
    }
  });

})(jQuery);
