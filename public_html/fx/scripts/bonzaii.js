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

  if($(":input[type!='hidden']")[0] && $('input:visible')[0]) {
    $('input:visible')[0].focus();
  }

  if ($('#message')) {
    $('#message').delay(1200).fadeOut(1000);
  }

  // send external links to new window
  $('a[rel="external"]').click(function(event) {
    window.open(this.href);
    event.preventDefault();
  });
});
