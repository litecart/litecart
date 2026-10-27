// Dropdown
waitFor('jQuery', ($) => {

	// Close an open dropdown, including any open dropdowns nested inside it
	function closeDropdown($dropdown) {
		$dropdown.removeClass('open');
		$dropdown.find('.dropdown.open').removeClass('open');
	}

	$(document).on('click', '.dropdown [data-toggle="dropdown"]', function(e) {
		e.preventDefault();

		let $toggle = $(this);
		let $dropdown = $toggle.closest('.dropdown');
		let $ancestors = $dropdown.parents('.dropdown');

		// Only one dropdown may be open at a time. Ancestors of this dropdown
		// are kept open so that multi-level menus keep working.
		$('.dropdown.open').not($dropdown).not($ancestors).each(function() {
			closeDropdown($(this));
		});

		$dropdown.toggleClass('open');
		$toggle.attr('aria-expanded', $dropdown.hasClass('open') ? 'true' : 'false');
	});

	$(document).on('click', '.dropdown-item a, .dropdown-item button, .dropdown-item input[type="radio"]', function(e) {
		// Dropdown toggles are handled by the handler above
		if ($(e.target).closest('[data-toggle="dropdown"]').length) return;

		let $dropdown = $(this).closest('.dropdown');
		$dropdown.removeClass('open');
		$dropdown.parents('.dropdown').removeClass('open');
	});

	$(document).on('change', '.dropdown :input', function(e) {

		let $dropdown = $(this).closest('.dropdown');
		let $toggle = $dropdown.find('.dropdown-toggle').filter('.form-select').first();
		if (!$toggle.length) return;

		let values = [];
		$dropdown.find(':input:checked').each(function() {
			values.push( $(this).parent().text().trim() );
		});

		if (!values.length) {
			values = [ $toggle.data('placeholder') ];
		}
		$toggle.text(values.join(', '));
	});

	// Close open dropdowns when clicking outside of them
	$(document).on('click', function(e) {
		let $target = $(e.target);

		$('.dropdown.open').each(function() {
			let $dropdown = $(this);

			// Keep the dropdown open when the click landed inside it, or inside
			// a dropdown nested within it.
			if ($dropdown.is($target) || $dropdown.has($target).length) return;

			closeDropdown($dropdown);
		});
	});

});