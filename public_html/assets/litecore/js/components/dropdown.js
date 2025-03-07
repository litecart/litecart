// Dropdown
+waitFor('jQuery', ($) => {

	$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
		$(this).closest('.dropdown').toggleClass('open')
	})

	$('.dropdown').on('click', 'a', function(e) {
		$(this).closest('.dropdown').removeClass('open')
	})

	// Listen for clicks outside the dropdown to uncheck the input
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.dropdown').length) {
			$('[data-toggle="dropdown"]').prop('checked', false);
		}
	});

})