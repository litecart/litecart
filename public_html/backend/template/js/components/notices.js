// Alerts
$('body').on('click', '.alert .close', function(e) {
	e.preventDefault()
	$(this).closest('.alert').fadeOut('fast', function() {
		$(this).remove()
	})
})
