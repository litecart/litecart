/*!
	LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built built with for simplicity.
	Link: https://www.litecart.net/
	License: CC-BY-ND-4.0
	Author: T. Almroth, LiteCart AB
*/

// Banner Click Tracking

waitFor('jQuery', ($) => {

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

});


if (!localStorage.getItem('bot_challenge_passed')) {

	(async () => {

		const report = {
			started: performance.now(),
			events: 0,
			mouseMoves: 0,
			keyEvents: 0,
			touchEvents: 0,
			scrollEvents: 0,
			visibilityChanges: 0,
			focusChanges: 0,
			timings: [],
			entropy: 0,
			webdriver: false,
			plugins: 0,
			languages: [],
			hardwareConcurrency: 0,
			deviceMemory: null,
			canvas: null,
			webgl: null,
			suspicious: [],
		};

		// -------------------------------------------------------------------------
		// Basic automation detection
		// -------------------------------------------------------------------------

		report.webdriver = !!navigator.webdriver;

		if (report.webdriver) {
			report.suspicious.push('webdriver');
		}

		if (!window.chrome && navigator.userAgent.includes('Chrome')) {
			report.suspicious.push('fake_chrome');
		}

		report.plugins = navigator.plugins.length;
		report.languages = navigator.languages || [];
		report.hardwareConcurrency = navigator.hardwareConcurrency || 0;
		report.deviceMemory = navigator.deviceMemory || null;

		// -------------------------------------------------------------------------
		// Human interaction tracking
		// -------------------------------------------------------------------------

		let lastMove = null;

		document.addEventListener('mousemove', e => {

			report.events++;
			report.mouseMoves++;

			const now = performance.now();

			if (lastMove) {
				const dx = e.clientX - lastMove.x;
				const dy = e.clientY - lastMove.y;
				const dt = now - lastMove.t;

				if (dt > 0) {
					report.timings.push(
						Math.sqrt(dx * dx + dy * dy) / dt
					);
				}
			}

			lastMove = {
				x: e.clientX,
				y: e.clientY,
				t: now,
			};

		}, { passive: true });

		document.addEventListener('keydown', () => {
			report.events++;
			report.keyEvents++;
		});

		document.addEventListener('touchstart', () => {
			report.events++;
			report.touchEvents++;
		}, { passive: true });

		document.addEventListener('scroll', () => {
			report.events++;
			report.scrollEvents++;
		}, { passive: true });

		// -------------------------------------------------------------------------
		// Focus / visibility
		// -------------------------------------------------------------------------

		document.addEventListener('visibilitychange', () => {
			report.visibilityChanges++;
		});

		window.addEventListener('focus', () => {
			report.focusChanges++;
		});

		window.addEventListener('blur', () => {
			report.focusChanges++;
		});

		// -------------------------------------------------------------------------
		// Canvas fingerprint
		// -------------------------------------------------------------------------

		try {

			const canvas = document.createElement('canvas');

			canvas.width = 200;
			canvas.height = 50;

			const ctx = canvas.getContext('2d');

			ctx.textBaseline = 'top';
			ctx.font = '14px Arial';
			ctx.fillStyle = '#f60';
			ctx.fillText('background bot challenge', 2, 2);

			report.canvas = canvas.toDataURL();

		} catch {}

		// -------------------------------------------------------------------------
		// WebGL fingerprint
		// -------------------------------------------------------------------------

		try {

			const canvas = document.createElement('canvas');

			const gl = canvas.getContext('webgl')
				|| canvas.getContext('experimental-webgl');

			if (gl) {

				const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');

				if (debugInfo) {

					report.webgl = {
						vendor: gl.getParameter(
							debugInfo.UNMASKED_VENDOR_WEBGL
						),
						renderer: gl.getParameter(
							debugInfo.UNMASKED_RENDERER_WEBGL
						),
					};
				}
			}

		} catch {}

		// -------------------------------------------------------------------------
		// Entropy scoring
		// -------------------------------------------------------------------------

		function stddev(values) {

			if (!values.length) {
				return 0;
			}

			const avg =
				values.reduce((a, b) => a + b, 0) /
				values.length;

			const variance =
				values.reduce(
					(a, b) => a + ((b - avg) ** 2),
					0
				) / values.length;

			return Math.sqrt(variance);
		}

		report.entropy = stddev(report.timings);

		// -------------------------------------------------------------------------
		// Wait in background
		// -------------------------------------------------------------------------

		await new Promise(resolve => setTimeout(resolve, 8000));

		// -------------------------------------------------------------------------
		// Simple scoring
		// -------------------------------------------------------------------------

		let score = 0;

		if (report.webdriver) {
			score += 100;
		}

		if (report.mouseMoves === 0) {
			score += 25;
		}

		if (report.entropy < 0.15) {
			score += 25;
		}

		if (report.events < 3) {
			score += 15;
		}

		if (report.plugins === 0) {
			score += 10;
		}

		if (!report.webgl) {
			score += 10;
		}

		report.botScore = score;

		// -------------------------------------------------------------------------
		// Send to backend
		// -------------------------------------------------------------------------

		navigator.sendBeacon(
			'/bot-check',
			JSON.stringify(report)
		);

		localStorage.setItem('bot_challenge_passed', '1');

	})();
}

// Add to cart
waitFor('jQuery', ($) => {

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
							'      ' + $('<div class="price"></div>').text(item.price.toMoney()).prop('outerHTML'),
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

		window.timerCart = setInterval(updateCart, 60e3); // Keeps session alive
	}
});

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


(async () => {

	const report = {
		userAgent: navigator.userAgent,
		language: navigator.language,
		hardwareConcurrency: navigator.hardwareConcurrency || 0,
		deviceMemory: navigator.deviceMemory || 0,
		timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
		plugins: navigator.plugins?.length || 0,
		webdriver: !!navigator.webdriver,
	};

	let mouseMovements = 0,
		keyPresses = 0,
		clicks = 0,
		scrollEvents = 0,
			timings = [],
		lastTimestamp = performance.now();

	function recordTiming() {
		const now = performance.now();
		timings.push(now - lastTimestamp);
		lastTimestamp = now;
	}

	onmousemove = () => {
		mouseMovements++;
		recordTiming();
	};

	onkeydown = () => keyPresses++;
	onclick = () => clicks++;
	onscroll = () => scrollEvents++;

	document.addEventListener('visibilitychange', recordTiming);
	window.addEventListener('focus', recordTiming);
	window.addEventListener('blur', recordTiming);

	await new Promise(r => setTimeout(r, 5000));

	// ----------------------------
	// ENTROPY
	// ----------------------------

	const avg =
		timings.reduce((a, b) => a + b, 0) / (timings.length || 1);

	const timingEntropy = Math.sqrt(
		timings.reduce((a, b) => a + (b - avg) ** 2, 0) /
		(timings.length || 1)
	);

	// ----------------------------
	// CANVAS
	// ----------------------------

	let canvasFingerprint = '';
	try {
		const canvas = document.createElement('canvas');
		const ctx = canvas.getContext('2d');

		ctx.textBaseline = 'top';
		ctx.font = '14px Arial';
		ctx.fillText('fp', 2, 2);

		canvasFingerprint = canvas.toDataURL();
	} catch {}

	// ----------------------------
	// WEBGL
	// ----------------------------

	let webglRenderer = '';
	try {
		const canvas = document.createElement('canvas');
		const gl = canvas.getContext('webgl');

		if (gl) {
			webglRenderer = gl.getParameter(gl.RENDERER) || '';
		}
	} catch {}

	// ----------------------------
	// BOT SCORE (risk heuristic)
	// ----------------------------

	let botScore = 0;

	// automation signal
	if (report.webdriver) botScore += 100;

	// no interaction
	if (mouseMovements === 0) botScore += 25;
	if (keyPresses === 0) botScore += 10;
	if (clicks === 0) botScore += 10;

	// suspicious timing (too smooth)
	if (timingEntropy < 20) botScore += 25;

	// weak environment signals
	if (!report.plugins) botScore += 5;

	// ----------------------------
	// CHECKSUM FINGERPRINT
	// ----------------------------

	const raw = JSON.stringify({
		report,
		behavior: {
			mouseMovements,
			keyPresses,
			clicks,
			scrollEvents
		},
		timingEntropy,
		canvasFingerprint,
		webglRenderer
	});

	let fingerprint = '';

	try {

		const encoder = new TextEncoder();
		const data = encoder.encode(raw);
		const hashBuffer = await crypto.subtle.digest('SHA-256', data);

		fingerprint = Array.from(new Uint8Array(hashBuffer))
			.map(b => b.toString(16).padStart(2, '0'))
			.join('');

	} catch {

		// Fallback: simple hash if crypto.subtle unavailable
		let hash = 0;

		for (let i = 0; i < raw.length; i++) {
			const char = raw.charCodeAt(i);
			hash = ((hash << 5) - hash) + char;
			hash = hash & hash;
		}

		fingerprint = Math.abs(hash).toString(16);
	}

	// ----------------------------
	// FINAL PAYLOAD
	// ----------------------------

	let report_url = window._env?.platform?.path + 'ajax/event';

	navigator.sendBeacon(report_url, JSON.stringify({
		type: 'challenge',
		data: {
			fingerprint,
			botScore,
			report
		}
	}));

})();

waitFor('jQuery', ($) => {

	/*Momentum Scroll by LiteCart */
	$.fn.momentumScroll = function() {
		this.each(function() {

			let $self = $(this),
				$content = $self.find('.scroll-content'),
				direction = '',
				velX = 0,
				clickX = 0,
				scrollX = 0,
				clicked = false,
				dragging = false,
				momentumID = null;

			if ($(this).width() <= 768) {
				$content.css('overflow', 'auto');
			}

			let momentumLoop = function() {

				if (direction == 'left') {
					$content.scrollLeft($content.scrollLeft() - velX); // Apply the velocity to the scroll position
				} else {
					$content.scrollLeft($content.scrollLeft() + velX);
				}

				velX *= 1 - 5 / 100; // Slow down the velocity 5%

				if (Math.abs(velX) > 0.5) { // Still moving?
					momentumID = requestAnimationFrame(momentumLoop); // Keep looping
				}
			};

			$content.on({

				'click': function(e) {
					if (dragging) {
						e.preventDefault();
					}
					dragging = false;
				},

				'mousemove': function(e) {
					if (!clicked) return;

					// Require at least 10px movement before starting a drag to avoid tiny accidental shifts
					let delta = Math.abs(clickX - e.pageX);
					if (delta < 10) return;

					dragging = true;

					let prevScrollLeft = $content.scrollLeft(); // Store the previous scroll position
					let currentDrag = (clickX - e.pageX);

					$content.scrollLeft(scrollX + currentDrag);

					if (currentDrag > 0) {
						direction = 'right';
					} else {
						direction = 'left';
					}

					velX = Math.abs($content.scrollLeft() - prevScrollLeft); // Compare change in position to work out drag speed
				},

				'mousedown': function(e) {
					e.preventDefault();
					clicked = true;
					scrollX = $content.scrollLeft();
					clickX = e.pageX;
					$content.css('cursor', 'grabbing');
				},

				'mouseup': function(e) {
					// Only prevent click when a drag actually occurred
					if (dragging) {
						e.preventDefault();
					}
					clicked = false;
					cancelAnimationFrame(momentumID);
					momentumID = requestAnimationFrame(momentumLoop);
					$content.css('cursor', '');
				},

				'mouseleave': function(e) {
					clicked = false;
					$content.css('cursor', '');
				}
			});

			$(window).on('resize', function() {

				if ($content.prop('scrollWidth') > ($self.outerWidth() + 20)) {

					if (!$self.find('button[name="left"], button[name="right"]').length) {

						$self.append([
							'<button name="left" class="btn btn-default" type="button"><i class="icon-chevron-left"></i></button>',
							'<button name="right" class="btn btn-default" type="button"><i class="icon-chevron-right"></i></button>'
						].join('\n'));

						$self.on('click', 'button[name="left"], button[name="right"]', function(e) {
							if (direction != $(this).attr('name')) {
								velX = 0;
							}
							cancelAnimationFrame(momentumID);
							velX += Math.round($self.outerWidth() * 0.03);
							direction = $(this).attr('name');
							momentumID = requestAnimationFrame(momentumLoop);

						});
					}

				} else {
					$self.find('button[name="left"], button[name="right"]').remove();
				}

				/*
				if ($(window).width() > ($self.outerWidth() + 45)) {
					$self.find('button[name="left"]').css('left', '');
					$self.find('button[name="right"]').css('right', '');
				} else {
					$self.find('button[name="left"]').css('left', 0);
					$self.find('button[name="right"]').css('right', 0);
				}
				*/

			}).trigger('resize');
		});
	};

	$('[data-toggle*="momentum-scroll"]').momentumScroll();
});

// Sidebar parallax effect
waitFor('jQuery', ($) => {

	if (_env && _env.template.settings.sidebar_parallax_effect == true) {

		let $sidebar = $('#sidebar');
			//let sidebar_max_offset = $sidebar.parent().height() - $sidebar.height() - 200; // Failsafe 30

		$(window).on('resize scroll', function(e) {
			if ($(window).width() >= 768 && ($sidebar.parent().height() - $sidebar.height()) > 500) {
				let offset = $(this).scrollTop() * .6;
				if (offset > 0) $sidebar.css('margin-top', offset + 'px');
			} else {
				$sidebar.css('margin-top', 0);
			}
		}).trigger('resize');
	}

});

/* Privacy Consent Manager */
waitFor('jQuery', ($) => {

	$.fn.privacyConsent = function(privacyClasses, consents) {
		var $element = $(this);

		$element.privacyClasses = privacyClasses || [];
		$element.consents = consents || [];

		$element.on('open', function(e) {
			e.preventDefault();
			$element.hide();
			$element.fadeIn();
		});

		$element.on('openExpanded', function(e) {
			e.preventDefault();
			$element.hide().find('.privacy-classes').addClass('expanded');
			$element.fadeIn();
		});

		$element.on('close', function(e) {
			e.preventDefault();
			$element.fadeOut(function(){
				$element.find('.privacy-classes').removeClass('expanded');
			});
		});

		$element.on('cookiesAccepted', function(e) {
			$('.require-consent').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$(this).replaceWith($(this).data('content'));
				}
			});

			$('script[type="application/x-privacy-script"]').each(function() {
				if (hasPrivacyConsent($(this).data('privacy-class'), $(this).data('third-party-id'))) {
					$newElement = $('<script>').attr('src', $(this).attr('src')).html($(this).prop('innerHTML'));
					$(this).replaceWith($newElement);
				}
			});

			$('script[type="application/x-privacy-content"]').each(function() {
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

		$element.find('button[name="customize"]').on('click', function() {
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

				$('button', $output).text("Click here to manage your cookie consents");

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
waitFor('jQuery', ($) => {

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
					$.each(result.categories, function(i, category) {

						let $item = $([
							'<li class="dropdown-item"><a class="dropdown-menu-link" href="'+ category.link.escapeAttr() +'">',
							'  <img src="'+ category.image.thumbnail.escapeAttr() +'" style="height: 1em;"> ' + category.name.escapeAttr(),
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
