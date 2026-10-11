waitFor('jQuery', ($) => {

	// Form Input Tags
	$('input[data-toggle="tags"]').each(function() {

		let $originalInput = $(this);

		let $tagField = $(
			'<div class="form-input">\
				<ul class="tokens">\
					<span class="input" contenteditable></span>\
				</ul>\
			</div>'
		);

		$tagField.tags = [];

		$tagField.add = function(input){

			input = input.trim();

			if (!input) return;

			if ($.inArray(input, $tagField.tags) !== -1) return;

			$tagField.tags.push(input);

			let $tag = $(
				'<li class="tag">\
					<span class="value"></span>\
					<span class="remove">x</span>\
				</li>');

			$('.value', $tag).text(input);
			$('.input', $tagField).before($tag);

			$tagField.trigger('change');
		};

		$tagField.remove = function(input){

			$tagField.tags = $.grep($tagField.tags, function(value) {
				return value != input;
			});

			$('.tag .value', $tagField).each(function() {
				if ($(this).text() == input) {
					$(this).parent('.tag').remove();
				}
			});

			$tagField.trigger('change');
		};

		// Optional remote lookup via data-source
		let source = $originalInput.data('source');

		if (source) {

			let $suggestions = $('<ul class="suggestions"></ul>'),
				xhr = null,
				active = -1,
				timer = null;

			$tagField.append($suggestions);

			$tagField.close = function() {
				active = -1;
				$suggestions.html('').removeClass('open');
			};

			$tagField.search = function(query) {

				if (xhr) xhr.abort();

				$tagField.close();

				if (query.length < 2) return;

				xhr = $.ajax({
					type: 'get',
					url: source,
					dataType: 'json',
					data: {
						query: query,
					},
					success: function(results) {

						if (!$.isArray(results)) return;

						$.each(results, function(i, result) {

							let $option = $('<li></li>');

							$option.text(result.name || result.id || result);
							$option.data('id', result.id || result);

							$suggestions.append($option);
						});

						$suggestions.toggleClass('open', !!$suggestions.children().length);
					},
				});
			};

			$tagField.on('input', '.input', function() {
				let query = $(this).text();
				clearTimeout(timer);
				timer = setTimeout(function() {
					$tagField.search(query.trim());
				}, 250);
			});

			$tagField.on('keydown', '.input', function(e) {

				if (!$suggestions.hasClass('open')) return;

				let $options = $suggestions.children();

				switch (e.which) {

					case 38: // Arrow up
						e.preventDefault();
						active = (active <= 0) ? $options.length - 1 : active - 1;
						break;

					case 40: // Arrow down
						e.preventDefault();
						active = (active + 1 >= $options.length) ? 0 : active + 1;
						break;

					case 13: // Enter
						e.preventDefault();
						e.stopImmediatePropagation();
						$options.eq(active).trigger('mousedown');
						return false;

					case 27: // Escape
						e.preventDefault();
						e.stopImmediatePropagation();
						$tagField.close();
						return false;
				}

				$options.removeClass('active');
				$options.eq(active).addClass('active');
			});

			$tagField.on('mousedown', '.suggestions li', function(e) {

				e.preventDefault();

				$tagField.add($(this).data('id'));
				$('.input', $tagField).text('').focus();
				$tagField.close();
			});

			$tagField.on('blur', '.input', function() {
				setTimeout(function() {
					$tagField.close();
				}, 150);
			});
		}

		let tags = $.grep($originalInput.val().split(/\s*,\s*/), function(value) {
			return value;
		});

		$.each(tags, function() {
			$tagField.add(this);
		});

		$tagField.on('keypress', '.input', function(e) {
			if (e.which == 44) { // Comma
				e.preventDefault();
				$tagField.add($(this).text());
				$(this).text('');
				if ($tagField.close) $tagField.close();
			}
		});

		$tagField.on('keydown', '.input', function(e) {
			if (e.which != 13) return;
			e.preventDefault();
			$tagField.add($(this).text());
			$(this).text('');
			if ($tagField.close) $tagField.close();
		});

		$tagField.on('blur', '.input', function() {
			$tagField.add($(this).text());
			$(this).text('');
		});

		$tagField.on('click', '.remove', function(e) {
			$tagField.remove($(this).siblings('.value').text());
		});

		$tagField.on('change', function() {

			$originalInput.val($tagField.tags.join(','));

			// Array-style names post one hidden field per tag
			if (/\[\]$/.test($originalInput.attr('name'))) {

				$originalInput.prop('disabled', true);

				$originalInput.siblings('[data-tags-array]').remove();

				$.each($tagField.tags, function(i, tag) {
					$('<input>', {
						type: 'hidden',
						name: $originalInput.attr('name'),
						'data-tags-array': '1',
						value: tag,
					}).insertAfter($originalInput);
				});
			}
		});

		$originalInput.data('tags', $tagField);

		$(this).hide().after($tagField);
	});

});
