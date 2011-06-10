/**
 * redirects
 */
var bzRedirect = function ($) {
  var init = function() {
    bzCallbackHandler.setCallback('editRedirect', bzRedirect.edit);
    bzCallbackHandler.setCallback('deleteRedirect', bzRedirect.del);
  }

  var edit = function (result) {
    if (!result.status) {
      $('#redirect-form').prepend('<div class="message error">'+result.message+'</div>')
      return;
    }
    if ($('#redirect-form').length) {
      $('#redirect-form input[type="submit"]').val('Update redirect');
      $.each(result.data, function(key, value) {
        if ($('#redirect-form *[name="'+key+'"]').length == 0) {
          $('#redirect-form').append('<input type="hidden" name="'+key+'" value="'+value+'" />');
        }
        else {
          $('#redirect-form *[name="'+key+'"]').val(value);
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
bzRedirect.init();
