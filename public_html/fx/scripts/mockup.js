$(function(){
  $('.content form *[title]').each(function() {
    if (this.title == '') {
      return;
    }
    this.value = this.title;
    $(this).css('color', '#ccc');
  });
  $('.content form *[title]').bind('focus blur', function(e) {
    if (e.type == 'focus') {
      if (this.value == this.title) {
        this.value = '';
      }
      $(this).css('color', 'inherit');
    } else if (e.type == 'blur') {
      if (!this.value) {
        $(this).css('color', '#ccc');
        this.value = this.title;
      }
    }
  });

  $('h1').bind('click', function(){
    document.location.href = '/';
  });

  var _fonts = [];
  $("link[rel='stylesheet']").each(function(x) {
    if (this.href.indexOf('family=') > 0) {
      var font = this.href.split('family=')[1].replace(/(\+|%20)/g, ' ');
      $('#fonts-container').append('<a href="#'+font+'">'+font+'</a>');
      _fonts.unshift(font);
    }
  });

  $('#fonts-container a').live('click', function(e) {
    var font = $(this).text();
    $('h1,h2,h3, h3 a,#menu-n-stuff a,').css('font-family', font);
  });

  if (document.location.href.indexOf('#') > 0) {
    var font = document.location.href.split('#')[1].replace(/(\+|%20)/g, ' ');
    if (_fonts.indexOf(font) == -1) {
      $('head').append("<link href='http://fonts.googleapis.com/css?family=" + font.replace(/ /g, '+') + "' rel='stylesheet' type='text/css' />");
      _fonts.unshift(font);
      $('#fonts-container').append('<a href="#'+font+'">'+font+'</a>');
      $('#fonts-container a:last').click();
    }
  }

  $('hr:last').remove();
});