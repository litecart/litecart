waitFor('jQuery', ($) => {

	// Inline handlers are blocked when script-src carries a nonce so we use this
	$(document).on('click', '[data-toggle="confirm"]', function(e) {
		if (!window.confirm($(this).data('confirm'))) {
			e.preventDefault();
			e.stopPropagation();
		}
	});

	$(document).on('click', '[data-toggle="history-back"]', function(e) {
		e.preventDefault();
		history.go(-1);
	});

	$(document).on('click', '[data-toggle="history-back"]', function(e) {
		e.preventDefault();
		let isPassword = $(this).prev().attr('type') == 'password';
		$(this).prev().attr('type', (isPassword) ? 'text' : 'password');
	});

	$(document).on('click', '[data-toggle="close-modal"]', function(e) {
		e.preventDefault();
		$.litebox.close();
	});
});