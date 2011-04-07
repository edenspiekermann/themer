$(function(){
  
  var COLOR_PICKER        = $('#color-picker'),
      COLOR_PICKER_INPUT  = $('#picker-input'),
      COLOR_PICKER_OK     = $('#picker-ok'),
      COLOR_PICKER_CANCEL = $('#picker-cancel');
      
  /*-------------------------------------------------------
  * Initialize the iFrame
  -------------------------------------------------------*/
  
  $('iframe').attr('src', '/?theme');
  
  $('#menus input').change(function(){
    var frame = $('#theme-frame'),
        uri = frame.get(0).contentWindow.location.pathname,
        query = $("#menus").serialize();
    
    // If we are loading the theme home page, we need to specify that we are
    // loading only the theme with '?theme'. If we dont, then Themer will
    // reload the entire application, along with the app header. If we are
    // loading a specfic theme page, Themer will simply render the theme,
    // assume the output will be rendered within an <iframe>, and disregard
    // loading the Themer header.
    
    query = (uri == '/') ? "?theme&" + query : "?" + query;
    
    frame.attr('src', uri + query);
  });
  
  /*-------------------------------------------------------
  * Option Box Display
  -------------------------------------------------------*/
  $('#nav a.option').click(function(e){
    e.preventDefault();
    
    // close all open option menus
    $('div.menu').hide();
    
    // if it's already open, just remove
    // the class and stop
    
    if($(this).hasClass('open'))
    {
      $(this).removeClass('open');
      return;
    }
    
    // unset all open buttons  
    $('#nav a.open').removeClass('open');
    
    // set the button as open
    $(this).addClass('open');
    
    // re-position the option menu and show it
    menu = $("#" + $(this).attr('name'));
    menu.css({'left': $(this).offset().left - 5}).show();
  });
  
  /*-------------------------------------------------------
  * Color Picker: Farbtastic
  -------------------------------------------------------*/  
  COLOR_PICKER.data({
    'top': '5px',
    'left': '320px'
  });
  
  $('#appearance div.color').each(function(){ 
    
    $('label', $(this)).click(function(){ 
      var box = $(this),
          input = $(this).parent().find('input'),
          picker = $('<div>', {id: 'actual-color-picker'}),
          cache = input.val();
      
      $('#actual-color-picker').remove();
      $('#color-picker').prepend(picker);
      
      // cache the original color
      COLOR_PICKER_CANCEL.data({
        'color': cache,
        'box': box,
        'input': input
      });
      
      COLOR_PICKER.draggable();
      
      p = COLOR_PICKER.data();
      
      COLOR_PICKER.css({
        'top': p.top,
        'left': p.left
      }).show();
      
      COLOR_PICKER_INPUT.val(cache);
      
      fb = $.farbtastic(picker, function(color){
        input.val(color);
        COLOR_PICKER_INPUT.val(color);
        box.css({'background-color': color});
      });
      
      fb.setColor(input.val());
    
      COLOR_PICKER_INPUT.keypress(function(e){
        fb.setColor($(this).val());
      });
    });
  });
  
  COLOR_PICKER_OK.click(function(e){
    e.preventDefault();
    $('input').first().change();
    resetPicker();
  });
  
  COLOR_PICKER_CANCEL.click(function(e){
    e.preventDefault();
    data = $(this).data();
    data.box.css({'background-color': data.color});
    data.input.val(data.color);
    resetPicker();
  });
  
  function resetPicker()
  {
    $("#actual-color-picker").remove();
    
    COLOR_PICKER.draggable('destroy');
    COLOR_PICKER.hide();
    COLOR_PICKER_INPUT.val('')
      .unbind('blur')
      .unbind('keypress');
  }
});