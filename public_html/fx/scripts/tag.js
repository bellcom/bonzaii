/**
 * tags
 */
var bzTag = function ($) {
  var init = function() {
    bzCallbackHandler.setCallback('editTag', bzTag.edit);
    bzCallbackHandler.setCallback('deleteTag', bzTag.del);
  }

  var edit = function (result) {
    if (!result.status) {
      $('#tag-form').prepend('<div class="message error">'+result.message+'</div>')
      return;
    }
    if ($('#tag-form').length) {
      $('#tag-form input[type="submit"]').val('Update tag');
      $.each(result.data, function(key, value) {
        if ($('#tag-form *[name="'+key+'"]').length == 0) {
          $('#tag-form').append('<input type="hidden" name="'+key+'" value="'+value+'" />');
        }
        else {
          $('#tag-form *[name="'+key+'"]').val(value);
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
bzTag.init();
