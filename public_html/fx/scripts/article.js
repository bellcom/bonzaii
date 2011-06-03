var bzArticle = function ($) {
  var init = function() {
    bzCallbackHandler.setCallback('editArticle', bzArticle.edit);
    bzCallbackHandler.setCallback('deleteArticle', bzArticle.del);
  }

  var edit = function (result) {
    if (!result.status) {
      $('#article-form').prepend('<div class="message error">'+result.message+'</div>')
      return;
    }
    if ($('#article-form').length) {
      $('#article-form input[type="submit"]').val('Update article');
      $.each(result.data, function(key, value) {
        if ($('#article-form *[name="'+key+'"]').length == 0) {
          $('#article-form').append('<input type="hidden" name="'+key+'" value="'+value+'" />');
        }
        else {
          $('#article-form *[name="'+key+'"]').val(value);
        }
      });
    }
  }

  var del = function(result) {
    $('#context').prepend('<div class="message">'+result.message+'</div>')
    if (!result.status) {
      $('#context div.message').addClass('error');
      return;
    }
    $(result.element).parent().parent().fadeOut();
  }

  return {
    init : init,
    edit : edit,
    del : del
  }
}(jQuery);
bzArticle.init();
