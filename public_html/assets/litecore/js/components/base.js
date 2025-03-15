window.waitFor = waitFor || ((objectName, callback) => {
	if (typeof(window[objectName]) !== "undefined") {
		callback(window[objectName]);
	} else {
		setTimeout(() => {
			waitFor(objectName, callback);
		}, 50);
	}
});

+waitFor('jQuery', ($) => {

  // Stylesheet Loader
  $.loadStylesheet = function(url, options, callback, fallback) {

    options = $.extend(options || {}, {
      rel: 'stylesheet',
      href: url,
      onload: callback,
      onerror: fallback
    })

    $('<link>', options).appendTo('head')
  }

  // JavaScript Loader
  $.loadScript = function(url, options, callback, fallback) {

    options = $.extend(options || {}, {
      method: 'GET',
      dataType: 'script',
      cache: true,
      onload: callback,
      onerror: fallback
    });

    return jQuery.ajax(url, options);
  };

  // Keep-alive
	if (_env && _env.platform && _env.platform.path) {
		let keepAlive = setInterval(function() {
			$.get({
				url: _env.platform.path + 'ajax/cart.json',
				cache: false
			})
		}, 60e3)
	}
});