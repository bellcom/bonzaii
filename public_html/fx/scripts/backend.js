jQuery(function($) {
  $('a[rel$=".json"]').live('click', function(event) {
    event.preventDefault();

    var elm = this;
    var _type = 'GET';
    if (this.rel == 'post.json') {
      _type = 'POST';
    }

    $.ajax(this.href, {
      type : _type,
      dataType : 'json',
      success : function(result) {
        if (!result) {
          jQuery.error('bugger... call failed, see server log');
          return;
        }
        if (result.callback != 'undefined') {
          jQuery.extend(result, {element : elm});
          bzCallbackHandler.call(result.callback, result);
        }
      }
    });
  });
});


var bzCallbackHandler = (function() {
  var pub = {};
  var config = {
    callbacks : {}
  };

  // register new callbacks
  pub.setCallback = function(name, callback) {
    config.callbacks[name] = callback;
  };

  // execure callback
  pub.call = function(name, params) {
    config.callbacks[name](params);
  };

  return pub;
}());
