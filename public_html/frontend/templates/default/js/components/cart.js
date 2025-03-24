// Add to cart
+waitFor('jQuery', ($) => {

	$('.listing.products .product button[name="add_cart_product"]').on('click', function(e) {
		e.preventDefault();

		let $button = $(this),
			$target = $('#site-navigation .shopping-cart'),
			$product = $button.closest('.product');

		$object = $('<div id="animated-cart-item"></div>').css({
			position: 'absolute',
			top: $button.offset().top,
			left: $button.offset().left,
			height: $button.height(),
			width: $button.width(),
			border: '1px rgba(0, 136, 204, 1) solid',
			backgroundColor: 'rgba(0, 136, 204, .5)',
			borderRadius: 'var(--border-radius)',
			padding: '.5em',
			zIndex: '999999',
		});

		updateCart('product_id='+ $product.data('id') +'&add_cart_product=true');

		$object
		.appendTo('body')
		.animate({
			top: $target.offset().top,
			left: $target.offset().left,
			width: $target.innerWidth(),
			height: $target.innerHeight(),
			borderRadius: 0
		}, {
			duration: 1000,
			easing: 'easeInCubic'
		})
		.animate({
			opacity: 0
		}, {
			duration: 250,
			complete: function(){
				$object.remove();
				$target.addClass('open');
			}
		});
	});

	// Add to cart animation
	$('body').on('submit', 'form[name="buy_now_form"]', function(e) {
		e.preventDefault();

		let $form = $(this),
			$button = $form.find('button[type="submit"]'),
			$target = $('#site-navigation .shopping-cart'),
			$object = $('<div id="animated-cart-item"></div>').css({
				position: 'absolute',
				top: $button.offset().top,
				left: $button.offset().left,
				height: $button.height(),
				width: $button.width(),
				border: '1px rgba(0, 136, 204, 1) solid',
				backgroundColor: 'rgba(0, 136, 204, .5)',
				borderRadius: 'var(--border-radius)',
				padding: '.5em',
				zIndex: '999999',
			});

		updateCart($form.serialize() + '&add_cart_product=true');

		$object
			.appendTo('body')
			.animate({
				top: $target.offset().top,
				left: $target.offset().left,
				width: $target.innerWidth(),
				height: $target.innerHeight(),
				borderRadius: 'var(--border-radius)'
			}, {
				duration: 1000,
				easing: 'easeOutCubic'
			})
			.animate({
				opacity: 0
			}, {
				duration: 250,
				complete: function(){
					$object.remove();
					$target.addClass('open');
				}
			});
	});

	$('body').on('click', 'button[name="remove_cart_item"]', function(e) {
		updateCart('remove_cart_item='+ $(this).val());
	});

	// Update cart / Keep alive
	if (typeof(_env) !== 'undefined') {
		window.updateCart = function(data) {

			$.ajax({
				url: _env.platform.url + 'ajax/cart.json',
				type: data ? 'post' : 'get',
				data: data,
				cache: false,
				async: true,
				dataType: 'json',

				beforeSend: function(jqXHR) {
					jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
				},

				error: function(jqXHR, textStatus, errorThrown) {
					$('#animated-cart-item').remove();
					if (data) alert('Error while updating cart');
				},

				success: function(result) {

					if (result.alert) {
						$('#animated-cart-item').remove();
						alert(result.alert);
					}

					$('#site-navigation .shopping-cart .badge').text(result.items.length);
					$('#site-navigation .shopping-cart').toggleClass('filled', result.items.length ? true : false);
					$('#site-navigation .shopping-cart ul .item').remove();

					$.each(result.items, function(key, item) {
						$('#site-navigation .shopping-cart .items').append([
							'<li class="item">',
							'  <div class="grid">',
							'    <div class="col-2">',
							'      ' + $('<img class="image img-responsive">').attr({'src': item.image.thumbnail, 'srcset': item.image.thumbnail +' 1x, '+ item.image.thumbnail_2x + ' 2x', 'alt': item.name}).prop('outerHTML'),
							'    </div>',
							'    <div class="col-7">',
							'      <div>' + $('<a class="name"></a>').attr('href', item.link).text(item.name).prop('outerHTML') + '</div>',
							'      ' + $('<div class="price"></div>').text(item.formatted_price).prop('outerHTML'),
							'    </div>',
							'    <div class="col-3 text-end">',
							'      ' + $('<button class="btn btn-danger btn-sm" name="remove_cart_item" type="submit"><i class="icon icon-trash"></i></button>').val(item.key).prop('outerHTML'),
							'    </div>',
							'  </div>',
							'</li>'
						].join('\n'));
					});
				}
			});
		};

		let timerCart = setInterval('updateCart()', 60e3); // Keeps session alive
	}
});