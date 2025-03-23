// AJAX Search
+waitFor('jQuery', ($) => {

	$('.navbar-search :input').on('focus', function() {
		$(this).closest('.dropdown').addClass('open');
	});

	$('.navbar-search :input').on('blur', function() {
		$(this).closest('.dropdown').removeClass('open');
	});

	let xhrAjaxSearch;
	$('.navbar-search :input').on('input', function() {

		let $navbar_search = $(this).closest('.navbar-search'),
			$dropdown = $navbar_search.find('.dropdown-menu');

		$navbar_search.find('.dropdown-menu').html('');

		if (xhrAjaxSearch) {
			xhrAjaxSearch.abort();
		}

		if (!$(this).val()) {
			$navbar_search.find('.dropdown-menu').append(
				$('<li></li>').text($navbar_search.data('hint'))
			);
			return;
		}

		xhrAjaxSearch = $.ajax({
			url: _env.platform.url + 'ajax/search_results.json',
			type: 'get',
			data: { query: $(this).val() },
			cache: false,
			async: true,
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
			},
			success: function(result) {

				if (!result) {
					dropdown.html('<li>:(</li>');
					return;
				}

				if (result.categories && result.categories.length) {
					$.each(result.products, function(i, product) {

						let $item = $([
							'<li class="dropdown-item"><a class="dropdown-menu-link" href="'+ category.link.escapeAttr() +'">',
							'  <img src="'+ product.thumbnail.escapeAttr() +'" style="height: 1em;"> ' + category.name.escapeAttr(),
							'</a></li>',
						].join('\n'));

						$dropdown.append($item);
					});
				}

				if (result.products && result.products.length) {
					$.each(result.products, function(i, product) {

						let $item = $([
							'<li class="dropdown-item"><a class="dropdown-menu-link" href="'+ product.link.escapeAttr() +'">',
							'  <img src="'+ product.image.thumbnail.escapeAttr() +'" style="height: 1em;"> ' + product.name.escapeAttr(),
							'</a></li>',
						].join('\n'));

						$dropdown.append($item);
					});
				}
			}
		});
	});
});