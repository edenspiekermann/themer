var themer = top || parent;

if( ! window.jQuery) {
  var js = document.createElement('script');
  js.type = "text/javascript";
  js.src = 'themer_asset/js/jquery.min.js';
}

(function($){
  $('a').click(function(e) { 
    var prevent = e.isDefaultPrevented(),
        is_local = themer.isLocal(this.href),
        is_hash = $(this).attr('href').match(/^#/);

    if(prevent || ! is_local || is_hash) return;
    e.prevenDefault();
    themer.updateTheme(this.href);
    return false;
  });
})(jQuery);