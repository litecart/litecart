waitFor('jQuery', $ => {

	$('.listing .product button[name="add_to_favorites"]').on('click', function (e) {
		e.preventDefault();

		let $product = $(this).closest('.product');

		$.ajax({
			url: _env.platform.url + 'favorites',
			type: 'post',
			data: {
				add: true,
				product_id: $product.data('id'),
			},
			cache: false,
			async: true,
			dataType: 'json',
			success: function (result) {
				if (result.status == 'ok') {
					$product.data('in-favorites', result.added ? '1' : '0');
				}
			},
		});
	});

	$('.listing .product button[name="remove_from_favorites"]').on('click', function (e) {
		e.preventDefault();

		let $product = $(this).closest('.product');

		$.ajax({
			url: _env.platform.url + 'favorites',
			type: 'post',
			data: {
				remove: true,
				product_id: $product.data('id'),
			},
			cache: false,
			async: true,
			dataType: 'json',
			success: function (result) {
				if (result.status == 'ok') {
					$product.data('in-favorites', result.added ? '1' : '0');
				}
			},
		});
	});
});
