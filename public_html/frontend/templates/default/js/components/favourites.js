waitFor('jQuery', $ => {

	$('.listing .product button[name="add_to_favourites"]').on('click', function (e) {
		e.preventDefault();

		let $product = $(this).closest('.product');

		$.ajax({
			url: _env.platform.url + 'favourites',
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
					$product.data('in-favourites', result.added ? '1' : '0');
				}
			},
		});
	});

	$('.listing .product button[name="remove_from_favourites"]').on('click', function (e) {
		e.preventDefault();

		let $product = $(this).closest('.product');

		$.ajax({
			url: _env.platform.url + 'favourites',
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
					$product.data('in-favourites', result.added ? '1' : '0');
				}
			},
		});
	});
});
