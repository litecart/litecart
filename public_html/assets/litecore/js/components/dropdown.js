// Dropdown
+waitFor('jQuery', ($) => {

	$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
		$(this).closest('.dropdown').toggleClass('open');
	});

	$('.dropdown-item').on('click', 'a', function(e) {
		$(this).closest('.dropdown').removeClass('open');
	});

	// Listen for clicks outside the dropdown to uncheck the input
	$(document).on('click', function(e) {

		if ($('.dropdown.open').length === 0) {
			return;
		}

		// If click is on dropdown::before psuedo element, remove open class
		if ($(e.target).closest('.dropdown').length === 0) {
			$('.dropdown.open').removeClass('open');
		}
	});

});