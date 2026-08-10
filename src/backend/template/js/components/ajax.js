waitFor('jQuery', ($) => {

	// CSRF token for AJAX requests
	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		if (!/^(GET|HEAD|OPTIONS)$/i.test(options.type) && window._env && window._env.csrf_token) {
			jqXHR.setRequestHeader('X-CSRF-Token', window._env.csrf_token);
		}
	});

});