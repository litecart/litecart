waitFor('jQuery', ($) => {

	// Form required asterix
	$(':input[required]').closest('.form-group').addClass('required');

	// Dropdown Select
	$('.dropdown .form-select + .dropdown-menu :input').on('input', function(e) {

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

	}).trigger('input');

	// Input Number Decimals
	$('body').on('change', 'input[type="number"][data-decimals]', function() {
		var value = parseFloat($(this).val()),
			decimals = $(this).data('decimals');
		if (decimals != '') {
			$(this).val(value.toFixed(decimals));
		}
	});

});