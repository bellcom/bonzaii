/**
 * authors
 */
var bzAuthor = function ($) {
  var init = function() {
    bzCallbackHandler.setCallback('editAuthor', bzAuthor.edit);
    bzCallbackHandler.setCallback('deleteAuthor', bzAuthor.del);
  }

  var edit = function (result) {
    if (!result.status) {
      $('#author-form').prepend('<div class="message error">'+result.message+'</div>')
      return;
    }
    if ($('#author-form').length) {
      $('#author-form input[type="submit"]').val('Update author');
      $.each(result.data, function(key, value) {
        if ($('#author-form *[name="'+key+'"]').length == 0) {
          $('#author-form').append('<input type="hidden" name="'+key+'" value="'+value+'" />');
        }
        else {
          $('#author-form *[name="'+key+'"]').val(value);
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
bzAuthor.init();
