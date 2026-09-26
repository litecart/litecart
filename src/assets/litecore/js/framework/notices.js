waitFor('jQuery', ($) => {

	// Auto-dismiss after 20s
	window._noticesTimeoutId = window.setTimeout(function() {
		$('.notices').fadeOut('slow');
	}, 20000);

	// Close: alert
	$('body').on('click', '.alert .close', function(e) {
		e.preventDefault();
		$(this).closest('.alert').fadeOut('fast', function() {
			$(this).remove();
		});
	});

	// Close: notice (immediate remove + cancel auto-dismiss)
	$('body').on('click', '.notice .close', function(e) {
		e.preventDefault();
		clearTimeout(window._noticesTimeoutId);
		$(this).closest('.notice').remove();
	});

});