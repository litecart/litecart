waitFor('jQuery', $ => {

	// Keep-alive
	if (typeof _env !== 'undefined' && _env?.platform?.path) {
		setInterval(function() {
			$.get({
				url: _env.platform.path + 'ajax/keep_alive',
				cache: false
			});
		}, 60e3);
	}

});
