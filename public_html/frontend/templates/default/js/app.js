/*!
 * LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built without nonsense.
 * @link https://www.litecart.net/
 * @license CC-BY-ND-4.0
 * @author T. Almroth
 */

// Banner Click Tracking

+waitFor('jQuery', ($) => {

	if (_env && _env.platform && _env.platform.path) {

		var mouseOverAd = null;

		$('.banner[data-id]').hover(function() {
			mouseOverAd = $(this).data('id');
		}, function() {
			mouseOverAd = null;
		});

		$('.banner[data-id]').on('click', function() {
			$.post(_env.platform.path + 'ajax/bct', 'banner_id=' + $(this).data('id'));
		});

		$(window).on('blur', function() {
			if (mouseOverAd){
				$.post(_env.platform.path + 'ajax/bct', 'banner_id=' + mouseOverAd);
			}
		});
	}
})

// Add to cart
+waitFor('jQuery', ($) => {

	$('.listing.products .product button[name="add_cart_product"]').on('click', function(e) {
		e.preventDefault()

		let $button = $(this),
			$target = $('#site-navigation .shopping-cart'),
			$product = $button.closest('.product')

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
		})

		updateCart('product_id='+ $product.data('id') +'&add_cart_product=true')

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
				$object.remove()
				$target.addClass('open')
			}
		})
	})

	// Add to cart animation
	$('body').on('submit', 'form[name="buy_now_form"]', function(e) {
		e.preventDefault()

		let $form = $(this),
			$button = $(this).find('button[type="submit"]'),
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
			})

		updateCart($form.serialize() + '&add_cart_product=true')

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
					$object.remove()
					$target.addClass('open')
				}
			})
	})

	$('body').on('click', 'button[name="remove_cart_item"]', function(e) {
		updateCart('remove_cart_item='+ $(this).val())
	})

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
					jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'))
				},

				error: function(jqXHR, textStatus, errorThrown) {
					$('#animated-cart-item').remove()
					if (data) alert('Error while updating cart')
				},

				success: function(result) {

					if (result.alert) {
						$('#animated-cart-item').remove()
						alert(result.alert)
					}

					$('#site-navigation .shopping-cart .badge').text(result.items.length)
					$('#site-navigation .shopping-cart').toggleClass('filled', result.items.length ? true : false)
					$('#site-navigation .shopping-cart ul .item').remove()

					$.each(result.items, function(key, item) {
						$('#site-navigation .shopping-cart ul').append([
							'<li class="item">',
							'  <div class="grid">',
							'    <div class="col-2">',
							'      ' + $('<img class="image img-responsive">').attr({'src': item.image.thumbnail, 'srcset': item.image.thumbnail +' 1x, '+ item.image.thumbnail_2x + ' 2x', 'alt': item.name}).prop('outerHTML'),
							'    </div>',
							'    <div class="col-8">',
							'      <div>' + $('<a class="name"></a>').attr('href', item.link).text(item.name).prop('outerHTML') + '</div>',
							'      ' + $('<div class="price"></div>').text(item.formatted_price).prop('outerHTML'),
							'    </div>',
							'    <div class="col-2 text-end">',
							'      ' + $('<button class="btn btn-danger btn-sm" name="remove_cart_item" type="submit"><i class="icon icon-trash"></i></button>').val(item.key).prop('outerHTML'),
							'    </div>',
							'  </div>',
							'</li>'
						].join('\n'))
					})
				}
			})
		}

		let timerCart = setInterval('updateCart()', 60e3); // Keeps session alive
	}
})

// Sidebar parallax effect
+waitFor('jQuery', ($) => {

	if (_env && _env.template.settings.sidebar_parallax_effect == true) {

		let $sidebar = $('#sidebar')
			//let sidebar_max_offset = $sidebar.parent().height() - $sidebar.height() - 200; // Failsafe 30

		$(window).on('resize scroll', function(e) {
			if ($(window).width() >= 768 && ($sidebar.parent().height() - $sidebar.height()) > 500) {
				let offset = $(this).scrollTop() * .6
				if (offset > 0) $sidebar.css('margin-top', offset + 'px')
			} else {
				$sidebar.css('margin-top', 0)
			}
		}).trigger('resize')
	}

})

/* Privacy Consent Manager */
+waitFor('jQuery', ($) => {

	$.fn.privacyConsent = function(privacyClasses, consents) {
		var $element = $(this);

		$(this).privacyClasses = privacyClasses || [];
		$(this).consents = consents || [];

		$element.on('open', function(e) {
			e.preventDefault();
			$(this).hide();
			$(this).fadeIn();
		});

		$element.on('openExpanded', function(e) {
			e.preventDefault();
			$(this).hide().find('.privacy-classes').addClass('expanded');
			$(this).fadeIn();
		});

		$element.on('close', function(e) {
			e.preventDefault();
			$(this).fadeOut(function(){
				$(this).find('.privacy-classes').removeClass('expanded');
			});
		});

		$element.on('cookiesAccepted', function(e) {
			$('.require-consent').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).data('content'));
				}
			});

			$('script[type="application/privacy-script"]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$newElement = $('<script>').attr('src', $(this).attr('src')).html($(this).prop('innerHTML'));
					$(this).replaceWith($newElement);
				}
			});

			$('script[type="application/privacy-content"]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).prop('innerHTML'));
				}
			});

			$('.require-consent[data-privacy-class][third-party-id]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).data('content'));
				}
			});
		});

		$element.hasConsent = function(privacyClass, third_party_id) {

		};

		$element.find('button[name="customize"]').click(function() {
			$element.find('.privacy-classes').toggleClass('expanded');
		});

		$element.find('input[name^="consents"][value="all"]').change(function() {
			var state = $(this).prop('checked');
			$(this).closest('.privacy-class').find('input[name^="consents"][value!="all"]').each(function() {
				$(this).prop('disabled', state);
				if (state) {
					$(this).prop('checked', true);
				}
			});
		}).trigger('change');

		$(document).ready(function() {
			$('.require-consent[data-privacy-class][third-party-id]').each(function() {
				var $output = $([
					'<p>This element requires a privacy consent for class &quot;' + $(this).data('privacy-class') + '&quot;).</p>',
					'<button class="btn btn-default"></button>'
				].join('\n'));

				$('button', $output).text("<?php echo functions::escape_js(language::translate('text_click_here_to_manage_cookie_consents', 'Click here to manage your cookie consents')); ?>");

				$('button', $output).on('click', function() {
					$element.trigger('openExpanded');
				});

				$(this).html($output);
			});

			if (document.cookie.match(/privacy_consents=/)) {
				$element.trigger('cookiesAccepted');
			}
		});

		return this;
	};

});

// AJAX Search
+waitFor('jQuery', ($) => {

	$('.navbar-search :input').on('focus', function() {
		$(this).closest('.dropdown').addClass('open')
	})

	$('.navbar-search :input').on('blur', function() {
		$(this).closest('.dropdown').removeClass('open')
	})

	let xhrAjaxSearch
	$('.navbar-search :input').on('input', function() {

		let $navbar_search = $(this).closest('.navbar-search'),
			$dropdown = $navbar_search.find('.dropdown-menu')

		$navbar_search.find('.dropdown-menu').html('')

		if (xhrAjaxSearch) {
			xhrAjaxSearch.abort()
		}

		if (!$(this).val()) {
			$navbar_search.find('.dropdown-menu').append(
				$('<li></li>').text($navbar_search.data('hint'))
			)
			return
		}

		xhrAjaxSearch = $.ajax({
			url: _env.platform.url + 'ajax/search_results.json',
			type: 'get',
			data: { query: $(this).val() },
			cache: false,
			async: true,
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'))
			},
			success: function(result) {

				if (!result) {
					dropdown.html('<li>:(</li>')
					return
				}

				if (result.categories && result.categories.length) {
					$.each(result.products, function(i, product) {

						let $item = $([
							'<li class="dropdown-menu-item"><a class="dropdown-menu-link" href="'+ category.link.escapeAttr() +'">',
							'  <img src="'+ less-featherlight.thumbnail.escapeAttr() +'" style="height: 1em;"> ' + category.name.escapeAttr(),
							'</a></li>',
						].join('\n'))

						$dropdown.append($item)
					})
				}

				if (result.products && result.products.length) {
					$.each(result.products, function(i, product) {

						let $item = $([
							'<li class="dropdown-menu-item"><a class="dropdown-menu-link" href="'+ product.link.escapeAttr() +'">',
							'  <img src="'+ product.image.thumbnail.escapeAttr() +'" style="height: 1em;"> ' + product.name.escapeAttr(),
							'</a></li>',
						].join('\n'))

						$dropdown.append($item)
					})
				}
			}
		})
	})
})

// Wishlist
+waitFor('jQuery', ($) => {

	$('.listing .product button[name="add_to_wishlist"]').on('click', function(e) {
		e.preventDefault()

		// Get the form and button
		let $product = $(this).closest('.product')

		if (!$product.data('in-wishlist')) {
			action = 'add'
		} else {
			action = 'remove'
		}

		$.ajax({
			url: _env.platform.url + 'ajax/wishlist.json',
			type: 'post',
			data: {
				'action': action,
				'product_id': $product.data('product-id')
			},
			cache: false,
			async: true,
			dataType: 'json',
			success: function(result) {
				if (result.status == 'ok') {
					$product.data('in-wishlist', result.added ? '1' : '0')
				}
			}
		})

	})

})