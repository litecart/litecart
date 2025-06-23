// Dropdown
waitFor('jQuery', ($) => {

	$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
		$(this).closest('.dropdown').toggleClass('open');
	});

	$('.dropdown-item').on('click', 'a,button,input[type="radio"]', function(e) {
		$(this).closest('.dropdown').removeClass('open');
	});

	$('.dropdown').on('change', ':input', function(e) {

		let $dropdown = $(this).closest('.dropdown');

		let values = [];
		$dropdown.find(':input:checked').each(function() {
			values.push( $(this).parent().text().trim() );
		});

		if (!values.length) {
			values = [ $dropdown.find('.dropdown-toggle').data('placeholder') ];
		}
		console.log('"'+values.join(', ')+'"');

		$dropdown.find('.dropdown-toggle').text(values.join(', '));
	});

	// Listen for clicks outside the dropdown to uncheck the input
	$(document).on('click', function(e) {
		// If click is on dropdown::before psuedo element, remove open class
		if ($('.dropdown.open').length && !$(e.target).closest('.dropdown').length) {
			$('.dropdown.open').removeClass('open');
		}
	});

});