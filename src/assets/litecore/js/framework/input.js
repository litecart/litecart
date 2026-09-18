waitFor('jQuery', ($) => {

	// Form required asterix
	$(':input[required]').closest('.form-group').addClass('required');

	// Dropdown Select
	function updateDropdownLabel() {

		const $dropdown = $(this).closest('.dropdown');

		values = [];

		$dropdown.find(':input:checked').each(function() {

			let name;

			if ($(this).data('name')) {
				name = $(this).data('name');
			} else {
				name = $(this).parent().text();
			}

			if ($(this).is(':checkbox')) {
				values.push(name);
			} else {
				values = [name];
			}
		});

		if (values.length === 0) {
			values = [$dropdown.data('placeholder')];
		}

		$dropdown.find('.form-select').text( values.join(', ') );
		$dropdown.removeClass('open');
	}

	$('.dropdown .form-select + .dropdown-menu :input').on('input', updateDropdownLabel);

	// Initialize dropdown labels on page load by calling the handler directly,
	// without dispatching input events (which would trigger other listeners).
	$('.dropdown .form-select + .dropdown-menu :input').each(updateDropdownLabel);

	// Input Number Decimals
	$('body').on('change', 'input[type="number"][data-decimals]', function() {
		var value = parseFloat($(this).val()),
			decimals = $(this).data('decimals');
		if (decimals != '') {
			$(this).val(value.toFixed(decimals));
		}
	});

});