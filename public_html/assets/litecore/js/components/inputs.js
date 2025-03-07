// Form required asterix
+waitFor('jQuery', ($) => {

	$(':input[required]').closest('.form-group').addClass('required')

	// Dropdown Select
	$('.dropdown .form-select + .dropdown-menu :input').on('input', function(e) {

		let $dropdown = $(this).closest('.dropdown')
		let $input = $dropdown.find(':input:checked')

		if (!$dropdown.find(':input:checked').length) return

		$dropdown.find('li.active').removeClass('active')

		if ($input.data('title')) {
			$dropdown.find('.form-select').text( $input.data('title') )
		} else if ($input.closest('.option').find('.title').length) {
			$dropdown.find('.form-select').text( $input.closest('.option').find('.title').text() )
		} else {
			$dropdown.find('.form-select').text( $input.parent().text() )
		}

		$input.closest('li').addClass('active')
		$dropdown.trigger('click.bs.dropdown')

	}).trigger('input')

	// Input Number Decimals
	$('body').on('change', 'input[type="number"][data-decimals]', function() {
		var value = parseFloat($(this).val()),
			decimals = $(this).data('decimals')
		if (decimals != '') {
			$(this).val(value.toFixed(decimals))
		}
	})

})