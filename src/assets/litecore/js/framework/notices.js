waitFor('jQuery', ($) => {

	// Auto-dismiss after 20s
	window._noticesTimeoutId = window.setTimeout(function() {
		$('.notices').fadeOut('slow', function() {
			$(this).remove();
		});
	}, 20000);

	// Close: alert
	$('body').on('click', '.alert .close', function(e) {
		e.preventDefault();
		$(this).closest('.alert').fadeOut('fast', function() {
			$(this).remove();
		});
	});

	// Close: notice (fade out, then remove)
	$('body').on('click', '.notice .close', function(e) {
		e.preventDefault();
		$(this).closest('.notice').fadeOut(200, function() {
			$(this).remove();
			// Nothing left to dismiss, so cancel the auto-dismiss timer
			if (!$('.notice').length) {
				clearTimeout(window._noticesTimeoutId);
			}
		});
	});

});