// AJAX Search
waitFor('jQuery', ($) => {

	let $search = $('.navbar-search');

	if (!$search.length) {
		return;
	}

	let $input = $search.find(':input'),
		$dropdown = $search.find('.dropdown-menu');

	function renderEmpty(message) {

		$dropdown.html([
			'<li class="search-suggestions-empty">',
			'	<span class="search-suggestions-empty-icon font-icon icon-search" aria-hidden="true"></span>',
			'	<span class="search-suggestions-empty-text">' + (message || '').escapeHTML() + '</span>',
			'</li>',
		].join('\n'));
	}

	function renderSection(title) {
		return '<li class="search-suggestions-header">' + title.escapeHTML() + '</li>';
	}

	function renderSuggestion(item, kind) {

		let thumb = (item.image && item.image.thumbnail) ? item.image.thumbnail : '';

		let html = [
			'<li class="dropdown-item search-suggestion search-suggestion--' + kind + '">',
			'	<a class="search-suggestion-link" href="' + item.link.escapeAttr() + '">',
		];

		if (thumb) {
			html.push('		<img class="search-suggestion-thumb" src="' + thumb.escapeAttr() + '" alt="" loading="lazy">');
		} else {
			html.push('		<span class="search-suggestion-icon font-icon ' + (kind === 'category' ? 'icon-tag' : 'icon-basket') + '" aria-hidden="true"></span>');
		}

		html.push('		<span class="search-suggestion-name">' + item.name.escapeHTML() + '</span>');
		html.push('	</a>');
		html.push('</li>');

		return $(html.join('\n'));
	}

	function renderNoResults(query) {

		$dropdown.html([
			'<li class="search-suggestions-empty search-suggestions-empty--no-results">',
			'	<span class="search-suggestions-empty-icon font-icon icon-frown" aria-hidden="true"></span>',
			'	<span class="search-suggestions-empty-text">' + query.escapeHTML() + '</span>',
			'	<span class="search-suggestions-empty-hint">' + 'No matches found'.escapeHTML() + '</span>',
			+ '</li>',
		].join('\n'));
	}

	$input.on('focus', function() {

		$(this).closest('.dropdown').addClass('open');

		if (!$(this).val().trim()) {
			renderEmpty($search.data('hint'));
		}
	});

	$input.on('blur', function() {
		$(this).closest('.dropdown').removeClass('open');
	});

	let xhrAjaxSearch;

	$input.on('input', function() {

		$dropdown.html('');

		let query = $(this).val().trim();

		if (xhrAjaxSearch) {
			xhrAjaxSearch.abort();
		}

		if (!query) {
			renderEmpty($search.data('hint'));
			return;
		}

		xhrAjaxSearch = $.ajax({
			url: _env.platform.url + 'ajax/search_results.json',
			type: 'get',
			data: { query: query },
			cache: false,
			async: true,
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
			},
			success: function(result) {

				if (!result) {
					renderNoResults(query);
					return;
				}

				let hasCategories = result.categories && result.categories.length,
					hasProducts = result.products && result.products.length;

				if (!hasCategories && !hasProducts) {
					renderNoResults(query);
					return;
				}

				if (hasCategories) {

					$dropdown.append(renderSection('Categories'));

					$.each(result.categories, function(i, category) {
						$dropdown.append(renderSuggestion(category, 'category'));
					});
				}

				if (hasProducts) {

					$dropdown.append(renderSection('Products'));

					$.each(result.products, function(i, product) {
						$dropdown.append(renderSuggestion(product, 'product'));
					});
				}
			},
			error: function(jqXHR) {
				if (jqXHR.statusText === 'abort') return;
				renderNoResults(query);
			}
		});
	});
});