// Accordion (.accordion)

waitFor('jQuery', ($) => {
	'use strict';

	$.fn.accordion = function(options) {

		const settings = $.extend({
			active: null, // Index of the item to open on init, null keeps the markup state
			toggle: true, // Allow closing the open item by clicking it again
			multiple: false // Allow more than one item open at a time
		}, options);

		return this.each(function() {

			const $accordion = $(this);

			const openItem = ($item) => {
				$item.addClass('active');
				$item.children('.accordion-header').attr('aria-expanded', 'true');
				$accordion.trigger('accordion:open', [$item]);
			};

			const closeItem = ($item) => {
				$item.removeClass('active');
				$item.children('.accordion-header').attr('aria-expanded', 'false');
				$accordion.trigger('accordion:close', [$item]);
			};

			$accordion.find('.accordion-header').each(function() {

				const $header = $(this);
				const $item = $header.closest('.accordion-item');

				if (!$item.length) return;

				$header.attr({
					role: 'button',
					tabindex: 0,
					'aria-expanded': $item.hasClass('active') ? 'true' : 'false'
				});

				$item.attr('role', 'region');

				const select = () => {

					const is_active = $item.hasClass('active');

					if (is_active && !settings.toggle) return;

					if (!settings.multiple) {
						$accordion.find('.accordion-item.active').each(function() {
							if (this !== $item[0]) closeItem($(this));
						});
					}

					is_active ? closeItem($item) : openItem($item);
				};

				$header.on('click', function(e) {
					e.preventDefault();
					select();
				});

				$header.on('keydown', function(e) {
					if (e.which !== 13 && e.which !== 32) return;
					e.preventDefault();
					select();
				});
			});

			// Restore the open item on re-initialisation, otherwise honour the requested index
			const $initial = settings.active === null
				? $accordion.find('.accordion-item.active').first()
				: $accordion.find('.accordion-item').eq(settings.active);

			$accordion.find('.accordion-item').not($initial).each(function() {
				closeItem($(this));
			});

			if ($initial.length) openItem($initial);
		});
	};

	const init = ($accordion) => {

		const active = $accordion.find('.accordion-item.active').index();

		$accordion.accordion({
			active: active === -1 ? null : active
		});
	};

	$('.accordion').each(function() {
		init($(this));
	});

	$(document).on('ajaxcomplete', function() {
		$('.accordion').each(function() {
			init($(this));
		});
	});

});