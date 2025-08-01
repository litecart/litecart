/*!
 * LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built built with for simplicity.
 * @link https://www.litecart.net/
 * @license CC-BY-ND-4.0
 * @author T. Almroth
 */

window.waitFor = (objectName, callback, retries=100) => {
	if (typeof(window[objectName]) !== 'undefined') {
		callback(window[objectName]);
	} else {
		if (retries) {
			setTimeout(() => {
				waitFor(objectName, callback, --retries);
			}, 50);
		} else {
			console.warn('waitFor('+ objectName +') timed out');
		}
	}
};

waitFor('jQuery', ($) => {

	// Stylesheet Loader
	$.loadStylesheet = function(url, options, callback, fallback) {

		options = $.extend(options || {}, {
			rel: 'stylesheet',
			href: url,
			cache: true,
			onload: callback,
			onerror: fallback
		});

		$('<link>', options).appendTo('head');
	};

	// JavaScript Loader
	$.loadScript = function(url, options, callback, fallback) {

		options = $.extend(options || {}, {
			method: 'GET',
			dataType: 'script',
			cache: true,
			onload: callback,
			onerror: fallback
		});

		return jQuery.ajax(url, options);
	};

	// Keep-alive
	if (typeof _env !== 'undefined' && _env?.platform?.path) {
		setInterval(function() {
			$.get({
				url: _env.platform.path + 'ajax/cart.json',
				cache: false
			});
		}, 60e3);
	}
});

/*
 * Simplified Carousel with Swipe Support
 * Lightweight carousel with essential features
 */

waitFor('jQuery', ($) => {
	'use strict';

	class Carousel {
		constructor(element, options) {
			this.$element = $(element);
			this.$indicators = this.$element.find('.carousel-indicators');
			this.options = $.extend({}, Carousel.DEFAULTS, options);

			this.paused = false;
			this.sliding = false;
			this.interval = null;
			this.touchStartX = 0;

			this.init();
		}

		init() {
			this.$items = this.$element.find('.item');
			this.$active = this.$element.find('.item.active');

			// Event listeners
			this.setupEvents();

			// Start autoplay
			if (this.options.interval) {
				this.cycle();
			}
		}

		setupEvents() {
			// Keyboard navigation
			if (this.options.keyboard) {
				this.$element.on('keydown.carousel', (e) => {
					if (/input|textarea/i.test(e.target.tagName)) return;
					if (e.which === 37) { this.prev(); e.preventDefault(); }
					if (e.which === 39) { this.next(); e.preventDefault(); }
				});
			}

			// Mouse hover
			if (this.options.pause === 'hover') {
				this.$element
					.on('mouseenter.carousel', () => this.pause())
					.on('mouseleave.carousel', () => this.cycle());
			}

			// Touch/swipe
			if (this.options.touch) {
				this.$element[0].addEventListener('touchstart', (e) => {
					if (e.touches.length === 1) {
						this.touchStartX = e.touches[0].clientX;
					}
				}, { passive: true });

				this.$element[0].addEventListener('touchend', (e) => {
					if (e.changedTouches.length === 1) {
						const deltaX = e.changedTouches[0].clientX - this.touchStartX;
						if (Math.abs(deltaX) > 50) {
							deltaX > 0 ? this.prev() : this.next();
						}
					}
				}, { passive: true });
			}
		}

		cycle() {
			this.paused = false;
			if (this.interval) clearInterval(this.interval);
			if (this.options.interval) {
				this.interval = setInterval(() => this.next(), this.options.interval);
			}
			return this;
		}

		pause() {
			this.paused = true;
			if (this.interval) {
				clearInterval(this.interval);
				this.interval = null;
			}
			return this;
		}

		next() {
			if (this.sliding) return this;
			return this.slide('next');
		}

		prev() {
			if (this.sliding) return this;
			return this.slide('prev');
		}

		to(index) {
			const activeIndex = this.$items.index(this.$active);
			if (index < 0 || index >= this.$items.length || index === activeIndex) return this;
			if (this.sliding) return this.$element.one('slid.carousel', () => this.to(index));

			const direction = index > activeIndex ? 'next' : 'prev';
			return this.slide(direction, this.$items.eq(index));
		}

		slide(type, next) {
			const $active = this.$element.find('.item.active');
			const $next = next || this.getNext(type, $active);
			const direction = type === 'next' ? 'left' : 'right';

			if ($next.hasClass('active') || this.sliding) return this;

			// Trigger slide event
			const slideEvent = $.Event('slide.carousel', {
				relatedTarget: $next[0],
				direction: direction
			});
			this.$element.trigger(slideEvent);
			if (slideEvent.isDefaultPrevented()) return this;

			this.sliding = true;
			const wasCycling = this.interval;
			wasCycling && this.pause();

			// Update indicators
			if (this.$indicators.length) {
				this.$indicators.find('.active').removeClass('active');
				const nextIndex = this.$items.index($next);
				$(this.$indicators.children()[nextIndex]).addClass('active');
			}

			// Perform slide
			const slidEvent = $.Event('slid.carousel', {
				relatedTarget: $next[0],
				direction: direction
			});

			if ($.support.transition && this.$element.hasClass('slide')) {
				$next.addClass(type);
				$next[0].offsetWidth; // Force reflow
				$active.addClass(direction);
				$next.addClass(direction);

				$active.one('bsTransitionEnd', () => {
					$next.removeClass([type, direction].join(' ')).addClass('active');
					$active.removeClass(['active', direction].join(' '));
					this.sliding = false;
					setTimeout(() => this.$element.trigger(slidEvent), 0);
				}).emulateTransitionEnd(600);
			} else {
				$active.removeClass('active');
				$next.addClass('active');
				this.sliding = false;
				this.$element.trigger(slidEvent);
			}

			wasCycling && this.cycle();
			return this;
		}

		getNext(direction, active) {
			const activeIndex = this.$items.index(active);
			const isGoingToWrap = (direction === 'prev' && activeIndex === 0) ||
								  (direction === 'next' && activeIndex === this.$items.length - 1);

			if (isGoingToWrap && !this.options.wrap) return active;

			const delta = direction === 'prev' ? -1 : 1;
			const itemIndex = (activeIndex + delta) % this.$items.length;
			return this.$items.eq(itemIndex);
		}
	}

	Carousel.DEFAULTS = {
		interval: 5000,
		pause: 'hover',
		wrap: true,
		keyboard: true,
		touch: true
	};

	// Plugin definition
	function Plugin(option) {
		return this.each(function () {
			const $this = $(this);
			let data = $this.data('carousel');
			const options = $.extend({}, Carousel.DEFAULTS, $this.data(), typeof option === 'object' && option);

			if (!data) $this.data('carousel', (data = new Carousel(this, options)));
			if (typeof option === 'number') data.to(option);
			else if (typeof option === 'string') data[option]();
			else if (options.interval) data.pause().cycle();
		});
	}

	$.fn.carousel = Plugin;
	$.fn.carousel.Constructor = Carousel;

	// Data API
	$(document).on('click.carousel.data-api', '[data-slide]', function (e) {
		const $this = $(this);
		const $target = $($this.attr('data-target') || $this.closest('.carousel'));
		if (!$target.hasClass('carousel')) return;

		const options = $.extend({}, $target.data(), $this.data());
		const slideIndex = $this.attr('data-slide-to');

		if (slideIndex) options.interval = false;
		Plugin.call($target, options);
		if (slideIndex) $target.data('carousel').to(slideIndex);
		e.preventDefault();
	});

	// Arrow controls
	$(document).on('click.carousel.data-api', '[data-slide="prev"]', function (e) {
		const $target = $($(this).attr('data-target') || $(this).closest('.carousel'));
		if (!$target.hasClass('carousel')) return;
		Plugin.call($target, 'prev');
		e.preventDefault();
	});

	$(document).on('click.carousel.data-api', '[data-slide="next"]', function (e) {
		const $target = $($(this).attr('data-target') || $(this).closest('.carousel'));
		if (!$target.hasClass('carousel')) return;
		Plugin.call($target, 'next');
		e.preventDefault();
	});

	$(window).on('load', () => {
		$('[data-ride="carousel"]').each(function () {
			Plugin.call($(this), $(this).data());
		});
	});

});

/*
 * jQuery Category Picker
 * by LiteCart
 */
waitFor('jQuery', ($) => {

	$.fn.categoryPicker = function(config){
		this.each(function() {

			this.xhr = null;
			this.config = config;

			self = this;

			$(this).find('.dropdown input[type="search"]').on({

				'focus': function(e) {
					$(self).find('.dropdown').addClass('open');
				},

				'input': function(e) {
						let dropdownMenu = $(self).find('.dropdown-content');

						$(dropdownMenu).html('');

						if (self.xhr) self.xhr.abort();

						if ($(this).val() == '') {

							$.getJSON(self.config.link, function(result) {

								$(dropdownMenu).html(
									'<h3 style="margin-top: 0;">'+ result.name +'</h3>'
								);

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append([
										'<div class="category flex" style="align-items: center;" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'">',
										'	' + self.config.icons.folder + '<a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
										'	<button name="add" class="btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button>',
										'</div>',
									].join('\n'));
								});
							});

							return;
						}

						self.xhr = $.ajax({
							type: 'get',
							async: true,
							cache: true,
							url: self.config.link + '&query=' + $(this).val(),
							dataType: 'json',

							beforeSend: function(jqXHR) {
								jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
							},

							error: function(jqXHR, textStatus, errorThrown) {
								if (errorThrown == 'abort') return;
								alert(errorThrown);
							},

							success: function(result) {

								if (!result.subcategories.length) {
									$(dropdownMenu).html(
										'<div class="text-center no-results"><em>:(</em></div>'
									);
									return;
								}

								$(dropdownMenu).html(
									'<h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3>'
								);

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<div class="category flex" style="align-items: center;" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'">',
										'	' + self.config.icons.folder + '<a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
										'	<button name="add" class="btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button>',
										'</div>',
									);
								});
							},
						});
					}
			});

			$(this).on('click', '.dropdown-content a', function(e) {
				e.preventDefault();

				let dropdownMenu = $(this).closest('.dropdown-content');

				$.getJSON($(this).data('link'), function(result) {

					$(dropdownMenu).html(
						'<h3 style="margin-top: 0;">'+ result.name +'</h3></li>'
					);

					if (result.id) {
						$(dropdownMenu).append([
							'<div class="flex" style="align-items: center;" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'">',
							'	' + self.config.icons.back + '<a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>',
							'</div>',
						].join('\n'));
					}

					$.each(result.subcategories, function(i, category) {
						$(dropdownMenu).append([
							'<div class="category flex" style="align-items: center;" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'">',
							'	' + self.config.icons.folder +' <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
							'	<button name="add" class="btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button>',
							'</div>',
						].join('\n'));
					});
				});
			});

			$(this).on('click', '.dropdown-content button[name="add"]', function(e) {
				e.preventDefault();

				let category = $(this).closest('.category').data(),
					abort = false;

				$(self).find('input[name="'+ self.config.inputName +'"]').each(function() {
					if ($(this).val() == category.id) {
						abort = true;
						return;
					}
				});

				if (abort) return;

				let inputField = $('<input>', {
					type: 'hidden',
					name: self.config.inputName,
					value: category.id,
					"data-name": category.name
				})[0].outerHTML;

				$(self).find('ul').append([
					'<li class="list-item flex">',
					'	<div style="flex-grow: 1;">',
					'		'+ inputField,
					'		'+ self.config.icons.folder +' '+ category.name,
					'	</div>',
					'	<button name="remove" class="btn btn-default btn-sm" type="button">',
					'		'+ self.config.translations.remove,
					'	</button>',
					'</div>',
				].join('\n'));

				$(self).trigger('change');

				$('.dropdown.open').removeClass('open');

				return false;
			});

			$(this).on('click', 'button[name="remove"]', function(e) {
				$(this).closest('li').remove();
				$(self).trigger('change');
			});

			$('body').on('mousedown', function(e) {
				if ($('.dropdown.open').has(e.target).length === 0) {
					$('.dropdown.open').removeClass('open');
				}
			});

			$(this).find('input[type="search"]').trigger('input');
		});
	};

});


waitFor('jQuery', ($) => {

	// Context Menu
	$.fn.contextMenu = function(config){
		this.each(function() {

			$(this).css({
				cursor: 'context-menu'
			});

			this.config = config;
			self = this;

			$(this).on('contextmenu').on({
			});
		});
	}

});

waitFor('jQuery', ($) => {
	"use strict";

	$('<style>', {type: 'text/css'})
		.html([
			'[draggable="true"] .grabbed { opacity: 0.5; }',
			'[draggable="true"] .grabbable { cursor: ns-resize; }',
		].join('\n'))
		.appendTo('head');

	$.fn.draggable = function(options) {
		// Default settings
		var settings = $.extend({
			handle: '.grabbable',
			cursor: 'ns-resize',
			direction: 'vertical'
		}, options);

		return this.each(function() {
			var $self = $(this);

			// Add basic styling
			$self.css({
				'position': 'relative',
				'user-select': 'none'
			});

			// Store settings
			$self.data('draggable-settings', settings);

			// Prevent text selection while dragging
			$self.on('dragstart selectstart', function() {
				return false;
			});
		});
	};

	// Global dragging state
	var isDragging = false;
	var dragElement = null;

	// Use event delegation for mouse events to handle dynamic elements
	$(document).on('mousedown', '[draggable="true"] .grabbable', function(e) {
		e.preventDefault();

		var $handle = $(this);
		var $element = $handle.closest('[draggable="true"]');

		// Initialize if needed
		if (!$element.data('draggable-settings')) {
			$element.draggable();
		}

		isDragging = true;
		dragElement = $element;

		$element.addClass('grabbed');
		$element.parent().addClass('dragging');
	});

	$(document).on('mousemove', function(e) {
		if (!isDragging || !dragElement) return;
		e.preventDefault();

		var mouseY = e.pageY;
		var $siblings = dragElement.siblings(':not(.grabbed)');

		$siblings.each(function() {
			var $sibling = $(this);
			var siblingOffset = $sibling.offset();
			var siblingHeight = $sibling.outerHeight();
			var siblingTop = siblingOffset.top;
			var siblingBottom = siblingOffset.top + siblingHeight;

			// Move as soon as mouse enters sibling bounds
			if (mouseY >= siblingTop && mouseY <= siblingBottom) {
				if (dragElement.index() > $sibling.index()) {
					// Moving up - place before sibling
					$sibling.before(dragElement);
				} else if (dragElement.index() < $sibling.index()) {
					// Moving down - place after sibling
					$sibling.after(dragElement);
				}
			}
		});
	});

	$(document).on('mouseup', function(e) {
		if (!isDragging) return;

		isDragging = false;
		if (dragElement) {
			dragElement.removeClass('grabbed');
			dragElement.parent().removeClass('dragging');
			dragElement = null;
		}
	});

	// Initialize existing elements
	$('[draggable="true"]').draggable();
});

// Dropdown
waitFor('jQuery', ($) => {

	$(document).on('click', '.dropdown [data-toggle="dropdown"]', function(e) {
		$(this).closest('.dropdown').toggleClass('open');
	});

	$(document).on('click', '.dropdown-item a,button,input[type="radio"]', function(e) {
		$(this).closest('.dropdown').removeClass('open');
	});

	$(document).on('change', '.dropdown :input', function(e) {

		let $dropdown = $(this).closest('.dropdown');

		let values = [];
		$dropdown.find(':input:checked').each(function() {
			values.push( $(this).parent().text().trim() );
		});

		if (!values.length) {
			values = [ $dropdown.find('.dropdown-toggle').data('placeholder') ];
		}
		console.log('"'+values.join(', ')+'"');

		$dropdown.find('.dropdown-toggle').text(values.join(', '));
	});

	// Listen for clicks outside the dropdown to uncheck the input
	$(document).on('click', function(e) {
		// If click is on dropdown::before psuedo element, remove open class
		if ($('.dropdown.open').length && !$(e.target).closest('.dropdown').length) {
			$('.dropdown.open').removeClass('open');
		}
	});

});

waitFor('jQuery', ($) => {

	$('form[data-track-changes]').each(function() {
		$(this).data('originalData', $(this).serialize());
	});

	$(window).on('beforeunload', function() {
		let hasChanges = false;

		$('form[data-track-changes]').each(function() {
			if ($(this).serialize() != $(this).data('originalData')) {
				hasChanges = true;
				return false; // Break out of the each loop
			}
		});

		if (hasChanges) {
			return true; // Show the confirmation dialog
		}
	});

	// Initialize input groups for number and float inputs
	$('.input-group').on('click', 'button[name="decrease"], button[name="increase"]', function() {
		const $input = $(this).siblings('input[type="number"]'),
			minValue = parseInt($input.attr('min')) || 0,
			maxValue = parseInt($input.attr('max')) || Infinity;
		if ($(this).attr('name') === 'decrease') {
			$input.val(Math.max(minValue, parseInt($input.val()) - 1)).trigger('input');
		} else {
			$input.val(Math.min(maxValue, parseInt($input.val()) + 1)).trigger('input');
		}
	});

	$('.input-group').on('click', '', function() {
		const $input = $(this).siblings('input[type="number"]');
		$input.val(parseInt($input.val()) - 1).trigger('input');
	});

});


waitFor('jQuery', ($) => {

	// Form required asterix
	$(':input[required]').closest('.form-group').addClass('required');

	// Dropdown Select
	$('.dropdown .form-select + .dropdown-menu :input').on('input', function(e) {

		const $dropdown = $(this).closest('.dropdown');

		values = [];

		$dropdown.find(':input:checked').each(function() {

			let name;

			if ($(this).data('name')) {
				name = $(this).data('name');
			} else {
				name = $(this).parent().text();
			}

			if ($(this).is(':checkbox')) {
				values.push(name);
			} else {
				values = [name];
			}
		});

		if (values.length === 0) {
			values = [$dropdown.data('placeholder')];
		}

		$dropdown.find('.form-select').text( values.join(', ') );
		$dropdown.removeClass('open');

	}).trigger('input');

	// Input Number Decimals
	$('body').on('change', 'input[type="number"][data-decimals]', function() {
		var value = parseFloat($(this).val()),
			decimals = $(this).data('decimals');
		if (decimals != '') {
			$(this).val(value.toFixed(decimals));
		}
	});

});

waitFor('jQuery', ($) => {

	// CSV Input
	$('textarea[data-toggle="csv"] + table').on('click', '.remove', function(e) {
		e.preventDefault();
		var parent = $(this).closest('tbody');
		$(this).closest('tr').remove();
		$(parent).trigger('keyup');
	});

	$('textarea[data-toggle="csv"] + table .add-row').on('click', function(e) {
		e.preventDefault();
		var n = $(this).closest('table').find('thead th:not(:last-child)').length;
		$(this).closest('table').find('tbody').append(
			'<tr>' + ('<td contenteditable></td>'.repeat(n)) + '<td><a class="remove" href="#"><i class="icon-times" style="color: #d33;"></i></a></td>' +'</tr>'
		).trigger('keyup');
	});

	$('textarea[data-toggle="csv"] + table .add-column').on('click', function(e) {
		e.preventDefault();
		var table = $(this).closest('table');
		var title = prompt("Column Title");
		if (!title) return;
		$(table).find('thead tr th:last-child:last-child').before('<th>'+ title +'</th>');
		$(table).find('tbody tr td:last-child:last-child').before('<td contenteditable></td>');
		$(table).find('tfoot tr td').attr('colspan', $(this).closest('table').find('tfoot tr td').attr('colspan') + 1);
		$(this).trigger('keyup');
	});

	$('textarea[data-toggle="csv"] + table').on('keyup', function(e) {
		var csv = $(this).find('thead tr, tbody tr').map(function (i, row) {
				return $(row).find('th:not(:last-child),td:not(:last-child)').map(function (j, col) {
					var text = $(col).text();
					if (/('|,)/.test(text)) {
						return '"'+ text.replace(/"/g, '""') +'"';
					} else {
						return text;
					}
				}).get().join(',');
			}).get().join('\r\n');
		$(this).next('textarea').val(csv);
	});

});

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

		let tags = $.grep($originalInput.val().split(/\s*,\s*/), function(value) {
			return value;
		});

		$.each(tags, function() {
			$tagField.add(this);
		});

		$tagField.on('keypress', '.input', function(e) {
			if (e.which == 44 || e.which == 13) { // Comma or enter
				e.preventDefault();
				$tagField.add($(this).text());
				$(this).text('');
			}
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
		});

		$(this).hide().after($tagField);
	});

});

waitFor('jQuery', ($) => {
	'use strict';

	// Check if jQuery is available
	if (typeof $ === 'undefined') {
		console.error('Litebox cannot load without jQuery');
		return;
	}

	class Litebox {
		static id = 0;
		static opened = [];

		static defaults = {
			closeOnClick: 'backdrop',
			enableKeyboard: true,
			//loading: '<div class="litebox-loader"></div>',
			loading: '<div class="loader" style="width: 128px; height: 128px; opacity: 0.5;"></div>',
			persist: false,
			closeIcon: '\u2716', // Unicode for ✖
			seamless: false,
			width: '',
			height: '',
			maxWidth: '',
			maxHeight: '',
			requireWindowWidth: null,
			previousIcon: '\u25C0', // Unicode for ◀
			nextIcon: '\u25B6', // Unicode for ▶
			galleryFadeIn: 100,
			galleryFadeOut: 300
		};

		// Constructor
		constructor($modal, options = {}) {
			this.id = Litebox.id++;

			Object.assign(this, Litebox.defaults, options, { target: $modal });

			this.$instance = $([
				'<div class="litebox litebox-loading">',
				`	<div class="litebox-modal${this.seamless ? ' litebox-seamless' : ''}">`,
				`		${this.loading}`,
				'	</div>',
				'</div>'
			].join('\n'));

			// Track mousedown and mouseup to ensure both happen outside modal before closing
			let mousedownOutsideModal = false;

			const isOutsideModal = (e) => {
				const $modal = this.$instance.find('.litebox-modal');
				if (!$modal.length) return true;
				const rect = $modal[0].getBoundingClientRect();
				return e.clientX < rect.left || e.clientX > rect.right ||
					e.clientY < rect.top || e.clientY > rect.bottom;
			};

			this.$instance.on('mousedown.litebox', (e) => {
				mousedownOutsideModal = isOutsideModal(e);
			});

			this.$instance.on('mouseup.litebox', (e) => {
				if (mousedownOutsideModal && isOutsideModal(e) && this.closeOnClick === 'backdrop') {
					if ($(e.target).closest('.litebox-previous, .litebox-next').length) return;
					this.close(e);
					e.preventDefault();
				}
			});

			this.$instance.on('click.litebox', (e) => {
				if (e.isDefaultPrevented()) return;
				if (this.closeOnClick === 'anywhere' || $(e.target).is('.litebox-close')) {
					this.close(e);
					e.preventDefault();
				}
			});
		}

		// Attach Litebox to elements
		static attach($source, $modal, options = {}) {

			const tempOptions = { ...this.defaults, ...$source.data(), ...options };
			const handler = (e) => {
				const $target = $(e.currentTarget);
				const gallery = $(e.currentTarget).data('gallery');
				const $gallerySource = gallery ? $(`[data-gallery="${gallery}"]`) : $source;
				const elementOptions = {
					$source: $gallerySource,
					$currentTarget: $(e.currentTarget),
					...$gallerySource.data(),
					...$(e.currentTarget).data(),
					...options
				};
				const instance = $(e.currentTarget).data('litebox-persisted') || new Litebox($modal, elementOptions);
				if (instance.persist !== false) $(e.currentTarget).data('litebox-persisted', instance);
				$(e.currentTarget).blur();
				instance.open(e);
			};

			$source.on('click', null, handler);

			return handler;
		}

		// Open the Litebox
		open(e) {

			if ((e?.ctrlKey || e?.shiftKey) || (this.requireWindowWidth && this.requireWindowWidth > $(window).width())) {
				return false;
			}
			this.beforeOpen(e);
			this.$instance.hide().appendTo('body');
			if ((e && e.isDefaultPrevented()) || this.beforeOpen(e) === false) {
				this.$instance.detach();
				return $.Deferred().reject().promise();
			}

			if (e) e.preventDefault();
			$('body').addClass('litebox-open');
			$('.litebox').removeClass('active');
			this.$instance.addClass('active');

			const $modal = this.getContent();
			if (!$modal) return $.Deferred().reject().promise();

			Litebox.opened.push(this);

			this.$instance.show();

			return $.when($modal)
				.always(($m) => {
					this.setContent($m);
					const { width, height, maxWidth, maxHeight } = this;
					if (width) this.$modal.parent().css('width', width);
					if (height) this.$modal.parent().css('height', height);
					if (maxWidth) this.$modal.parent().css('max-width', maxWidth);
					if (maxHeight) this.$modal.parent().css('max-height', maxHeight);
					this.afterContent(e);
					this.afterOpen(e);
				});
		}

		// Retrieve content based on the target
		getContent() {

			if (this.persist && this.$modal) {
				return this.$modal;
			}

			const parsers = {
				jquery: {
					regex: /^[#.]\w/,
					test: function (el) { return (el instanceof $ && el); },
					process: function (el) { return this.persist ? $(el) : $(el).clone(true); }
				},
				image: {
					regex: /\.(a?png|avif|bmp|gif|ico|jpe?g|jp2|svg|tiff?|webp)(\?\S*)?$/i,
					process: function (url) {
						const deferred = $.Deferred();
						const $img = $('<img>', { src: url, alt: '' });
						$img.on('load', () => deferred.resolve($img));
						$img.on('error', () => deferred.resolve($('<div>Failed to load image</div>')));
						return deferred.promise();
					}
				},
				html: {
					regex: /^\s*<[\w!][^<]*>/,
					process: (html) => $(html)
				},
				iframe: {
					process: function (url) {
						const deferred = $.Deferred();
						const $iframe = $('<iframe/>', { src: url });
						$iframe.on('load', () => {
							$iframe.show().appendTo(this.$instance.find('.litebox-modal'));
							deferred.resolve($iframe);
						});
						return deferred.promise();
					}
				},
				video: {
					regex: /\.(mp4|webm)(\?\S*)?(\?|$)/i,
					process: function (url) {
						const deferred = $.Deferred();
						const ext = url.match(/\.(mp4|webm)(\?|$)/i)?.[1] || 'mp4';
						const $video = $('<video controls>');
						$video.append($('<source>', { src: url, type: `video/${ext}` }));
						$video.on('loadeddata', () => deferred.resolve($video));
						$video.on('error', () => deferred.resolve($('<div>Failed to load video</div>')));
						return deferred.promise();
					}
				},
			   youtube: {
				   regex: /^(https?:\/\/)?(www\.)?(youtube\.com|youtube-nocookie\.com|youtu\.?be)\//,
				   process: function (url) {
							// Improved videoId extraction for various YouTube URL formats
							let videoId = null;
							// youtu.be/VIDEOID
							let match = url.match(/youtu\.be\/([\w-]{11})/);
							if (match) videoId = match[1];
							// youtube.com/watch?v=VIDEOID
							if (!videoId) {
								match = url.match(/[?&]v=([\w-]{11})/);
								if (match) videoId = match[1];
							}
							// youtube.com/embed/VIDEOID
							if (!videoId) {
								match = url.match(/embed\/([\w-]{11})/);
								if (match) videoId = match[1];
							}
							// youtube.com/v/VIDEOID
							if (!videoId) {
								match = url.match(/\/v\/([\w-]{11})/);
								if (match) videoId = match[1];
							}
							// fallback: try to extract last 11-char id
							if (!videoId) {
								match = url.match(/([\w-]{11})/);
								if (match) videoId = match[1];
							}
							const deferred = $.Deferred();
							let $iframe;
							if (videoId) {
								$iframe = $('<iframe/>', {
									src: `https://www.youtube-nocookie.com/embed/${videoId}`,
									allowfullscreen: true,
									style: 'display: none; height: 50vh; aspect-ratio: 16/9;'
								});
							} else {
								$iframe = $('<div>Failed to extract YouTube video ID</div>');
							}
							// Always append to modal before resolving
							this.$instance.find('.litebox-modal').append($iframe);
							if ($iframe.is('iframe')) {
								$iframe.on('load', () => {
									$iframe.show();
									deferred.resolve($iframe);
								});
							} else {
								$iframe.show();
								deferred.resolve($iframe);
							}
							return deferred.promise();
				   }
			   },
				raw: {
					regex: /\.(log|md|txt)(\?\S*)?(\?|$)/i,
					process: function(url) {
						const deferred = $.Deferred();
						const $content = $('<div>').css({ "white-space": 'pre-wrap', "max-width": '90vw' });
						$.get(url, raw => $content.text(raw)).done(() => deferred.resolve($content)).fail(() => deferred.resolve($('<div>Failed to load file</div>')));
						return deferred.promise();
					}
				},
				ajax: {
					regex: /./,
					process: function (url) {
						const deferred = $.Deferred();
						const $container = $('<div>');
						$container.load(url.replace('#', ' #'), (_, status) => {
							if (status === 'error') deferred.reject();
							else deferred.resolve($container.contents());
						});
						return deferred.promise();
					}
				},
				text: {
					process: function (text) {
						return $('<div>', { text });
					}
				}
			}

			const data = this.target || (this.$currentTarget?.data('target') || this.$currentTarget?.attr('href')) || '';
			let parser = parsers[this.type] || '';

			if (!parser) {
				const target = data;
				for (const name in parsers) {
					parser = parsers[name];
					if (parser.test?.(target) || (parser.regex && target.match?.(parser.regex))) {
						return parser.process.call(this, target);
					}
				}
				console.error(`No content parser found for "${target}"`);
				return false;
			}

			return parser.process.call(this, data);
		}

		// Set the content in the modal
		setContent($modal) {
			this.$instance.removeClass('litebox-loading');
			this.$modal = $modal instanceof $ ? $modal : $($modal);
			this.$modal.show();
			this.$instance.find('.litebox-modal').html(this.$modal);
			if (this.closeIcon) {
				this.$instance.find('.litebox-modal').prepend(
					`<div class="litebox-close">${this.closeIcon}</div>`
				);
			}
		}

		// Before opening the Litebox
		beforeOpen(e) {
			this._previouslyActive = document.activeElement;
			this._$previouslyTabbable = $('a, input, select, textarea, iframe, button, [contentEditable=true]')
				.not('[tabindex]').not(this.$instance.find('button'));
			this._$previouslyWithTabIndex = $('[tabindex]').not('[tabindex="-1"]');
			this._previousWithTabIndices = this._$previouslyWithTabIndex.map((_, el) => $(el).attr('tabindex'));
			this._$previouslyWithTabIndex.add(this._$previouslyTabbable).attr('tabindex', -1);
			document.activeElement?.blur();
			return true;
		}

		// After opening the Litebox
		afterOpen(e) {

			this.$instance.on('next previous', (event) => {
				const offset = event.type === 'next' ? 1 : -1;
				this.navigateTo(this.currentIndex() + offset);
			});

			const isTouchAware = 'PointerEvent' in window;

			if (isTouchAware) {
				let startX = 0;
				this.$instance.on('pointerdown', (e) => {
					startX = e.clientX;
				});

				this.$instance.on('pointerup', (e) => {
					const endX = e.clientX;
					const diffX = startX - endX;
					if (Math.abs(diffX) > 30) {
						if (diffX > 0) {
							this.$instance.trigger('next');
						} else {
							this.$instance.trigger('previous');
						}
					}
				});

				this.$instance.addClass('litebox-swipe-aware');
			}

			return true;
		}

		// After setting content
		afterContent(e) {

			this.$instance.find('[autofocus]:not([disabled])').trigger('focus');

			// If the gallery is enabled, and current index is not first, add navigation
			if (this.$source && this.currentIndex() > 0) {
				$(`<div class="litebox-previous"><span>${this.previousIcon}</span></div>`).on('click', (e) => {
					this.$instance.trigger('previous');
					e.preventDefault();
				}).appendTo(this.$instance.find('.litebox-modal'));
			}

			// If the gallery is enabled, and current index is not last, add navigation
			if (this.$source && this.currentIndex() < this.$source.length - 1) {
				$(`<div class="litebox-next"><span>${this.nextIcon}</span></div>`).on('click', (e) => {
					this.$instance.trigger('next');
					e.preventDefault();
				}).appendTo(this.$instance.find('.litebox-modal'));
			}

			return true;
		}

		// Remove closed instances from the opened array
		static pruneOpened(remove) {
			this.opened = this.opened.filter(lb => lb !== remove && lb.$instance.closest('body').length > 0);
		}

		// Get the currently open Litebox
		static current() {
			return this.opened[this.opened.length - 1] || null;
		}

		close(e) {

			const deferred = $.Deferred();

			if (this.beforeClose(e) === false) {
				deferred.reject();
			} else {
				Litebox.pruneOpened(this);
				this.$instance.hide().detach();
				this.afterClose(e);
				deferred.resolve();
				$('.litebox:not(.active)').last().addClass('active');
				if (!$('.litebox').length) $('body').removeClass('litebox-open');
			}

			return deferred.promise();
		}

		//static close(e) {
		//	return this.current()?.close(e);
		//}

		onKeyUp(e) {

			if (!this.enableKeyboard) return;

			switch (e.keyCode) {
				case 27: e.preventDefault(); this.close(e); break;
				case 37: e.preventDefault(); this.$instance.trigger('previous'); break;
				case 39: e.preventDefault(); this.$instance.trigger('next'); break;
			}
		}

		beforeClose(e) {
			return true;
		}

		afterClose(e) {
			if (e?.isDefaultPrevented()) return;
			this._$previouslyTabbable.removeAttr('tabindex');
			this._$previouslyWithTabIndex.each((i, el) => $(el).attr('tabindex', this._previousWithTabIndices[i]));
			if (this._previouslyActive instanceof $) {
				this._previouslyActive.trigger('focus');
			} else {
				$(this._previouslyActive).trigger('focus');
			}
			this.$instance.off('next previous');
		}

		// Get the current slide index
		currentIndex() {
			return this.$source.index(this.$currentTarget);
		}

		// Navigate to a specific slide
		navigateTo(index) {

			if (!this.$source) {
				console.warn('Gallery navigation is not available');
				return;
			}

			const source = this.$source;
			const len = source.length;
			const $inner = this.$instance.find('.litebox-inner');
			index = ((index % len) + len) % len;

			this.$instance.addClass('litebox-loading');
			this.$currentTarget = source.eq(index);

			return $.when(
				this.getContent(),
			).always(($newContent) => {
				this.setContent($newContent);
				this.afterContent();
			});
		}
	}

	// jQuery plugin integration
	$.litebox = function (url, options = {}) {
		if (typeof url === 'string') {
			const instance = new Litebox(url, options);
			instance.open();
			return instance;
		}
		console.error('Invalid argument passed to $.litebox. Expected a URL string.');
	};

	// Expose the Litebox.current method
	$.litebox.current = Litebox.current.bind(Litebox);

	// Expose the Litebox.attach method
	$.litebox.opened = Litebox.opened;

	// Expose the Litebox.close method
	$.litebox.close = function(){
		this.current()?.close();
	}

	$.fn.litebox = function ($modal, options) {
		Litebox.attach(this, $modal, options);
		return this;
	};

	$(() => {

		// Early binding for static elements
		//$('[data-toggle="litebox"], [data-toggle="modal"], [data-toggle="lightbox"]').each(() => {
		//	Litebox.attach(this);
		//});

		// Late binding for dynamically added elements
		$(document).on('click', '[data-toggle="litebox"], [data-toggle="modal"], [data-toggle="lightbox"]', (e) => {
			if (e.isDefaultPrevented()) return;
			const $cur = $(e.currentTarget);
			const handler = Litebox.attach($cur);
			handler(e);
		});
	});

});

/*
 * Momentum Scroll
 * by LiteCart
 */
waitFor('jQuery', ($) => {

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

					dragging = true;

					let prevScrollLeft = $content.scrollLeft(); // Store the previous scroll position
					let currentDrag = (clickX - e.pageX);

					$content.scrollLeft(scrollX + (clickX - e.pageX));

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
					e.preventDefault();
					self = this;
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

						$self.append(
							'<button name="left" class="btn btn-default" type="button"><i class="icon-chevron-left"></i></button>' +
							'<button name="right" class="btn btn-default" type="button"><i class="icon-chevron-right"></i></button>'
						);

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

waitFor('jQuery', ($) => {

	// Alerts
	$('body').on('click', '.alert .close', function(e) {
		e.preventDefault();
		$(this).closest('.alert').fadeOut('fast', function() {
			$(this).remove();
		});
	});

});

// Off-Canvas Sidebar (data-toggle="offcanvas-collapse")
waitFor('jQuery', ($) => {

	$('[data-toggle="offcanvas"]').on('click', function() {
		$(this).closest('.navbar').toggleClass('expanded');
		$('body').toggleClass('offcanvas-open', $(this).closest('.navbar').hasClass('expanded'));
		$('body').css('overflow', $(this).closest('.navbar').hasClass('expanded') ? 'hidden' : '');
	});

});

waitFor('jQuery', ($) => {

	// Password Strength
	$('form').on('input', 'input[type="password"][data-toggle="password-strength"]', function() {

		$(this).siblings('meter').remove();

		if (!$(this).val()) return;

		let numbers = ($(this).val().match(/[0-9]/g) || []).length,
			lowercases = ($(this).val().match(/[a-z]/g) || []).length,
			uppercases = ($(this).val().match(/[A-Z]/g) || []).length,
			symbols =   ($(this).val().match(/[^\w]/g) || []).length,
			score = (numbers * 9) + (lowercases * 11.25) + (uppercases * 11.25) + (symbols * 15)
						+ (numbers ? 10 : 0) + (lowercases ? 10 : 0) + (uppercases ? 10 : 0) + (symbols ? 10 : 0),
			meter = $('<meter min="0" low="80" high="120" optimum="150" max="150" value="'+ score +'"></meter>').css({
			position: 'absolute',
			bottom: '-1em',
			width: '100%',
			height: '1em'
		});

		$(this).after(meter);
	});

});


waitFor('jQuery', ($) => {
	
	// jQuery Placeholders by LiteCart
	let Placeholders = [];

	$.fn.Placeholder = function(options){
		this.each(function() {

			this.$element = $(this);

			this.settings = $.extend({
				aspectRatio: "1:1",
			}, options, this.$element.data());

			this.refresh = function(){
				let width = this.$element.width(),
					height = width / this.settings.aspectRatio.replace(/^([0-9]*):[0-9]*$/, '$1') * this.settings.aspectRatio.replace(/^[0-9]*:([0-9]*)$/, '$1');

				width = Math.round(width);
				height = Math.round(height);

				this.$element.text(width + '\u00d7' + height + ' (' +  this.settings.aspectRatio + ')')
					.css('font-size', Math.round(height/10) + 'px')
					.width('100%')
					.height(height);
			};

			this.refresh();

			Placeholders.push(this);
		});
	};

	$('.placeholder').Placeholder();

	$(window).on('resize', function() {
		$.each(Placeholders, function(i, placeholder) {
			placeholder.refresh();
		});
	});

});


// Number Formatting
Number.prototype.toText = function(decimals = 0) {
	var n = this,
		c = decimals,
		d = '.',
		t = ',',
		s = n < 0 ? '-' : '',
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
		f = n - i,
		j = (j = i.length) > 3 ? j % 3 : 0;

	return s + (j ? i.substring(0, j) + t : '') + i.substring(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + ((c && f) ? d + Math.abs(f).toFixed(c).slice(2) : '');
};

// Money Formatting
Number.prototype.toMoney = function() {
	var n = this,
		c = _env.currency.decimals || 2,
		d = _env.language.decimal_point || '.',
		t = _env.language.thousands_separator || ',',
		p = _env.currency.prefix || '',
		x = _env.currency.suffix || '',
		s = n < 0 ? '-' : '',
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
		f = n - i,
		j = (j = i.length) > 3 ? j % 3 : 0;

	return s + p + (j ? i.substring(0, j) + t : '') + i.substring(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x;
};

// Escape HTML
String.prototype.escapeHTML = function() {

	let entityMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
	};

	return this.replace(/[&<>"'\/]/g, function (s) {
		return entityMap[s];
	});
};

// Escape Attribute
String.prototype.escapeAttr = function() {
	return this.escapeHTML().replace(/\r\n?|\n/g, '\\n');
};

// Scroll Up
waitFor('jQuery', ($) => {

	$(window).scroll(function() {
		if ($(this).scrollTop() > 300) {
			$('#scroll-up').fadeIn();
		} else {
			$('#scroll-up').fadeOut();
		}
	});

	$('#scroll-up').on('click', function() {
		$('html, body').animate({scrollTop: 0}, 1000, 'easeOutBounce');
		return false;
	});

});

// Data-Table Toggle Checkboxes
waitFor('jQuery', ($) => {

	// Data-Table Toggle Checkboxes
	$('body').on('click', '.data-table *[data-toggle="checkbox-toggle"], .data-table .checkbox-toggle', function() {
		$(this).closest('.data-table').find('tbody td:first-child :checkbox').each(function() {
			$(this).prop('checked', !$(this).prop('checked')).trigger('change');
		});
		return false;
	});

	$('body').on('click', '.data-table tbody tr', function(e) {
		if ($(e.target).is('a') || $(e.target).closest('a').length) return;
		if ($(e.target).is('.btn, :input, th, .icon-star, .icon-star-o')) return;
		$(this).find(':checkbox, :radio').first().trigger('click');
	});

	// Data-Table Shift Check Multiple Checkboxes
	let lastTickedCheckbox = null;
	$('.data-table td:first-child :checkbox').on('click', function(e) {

		let $chkboxes = $('.data-table td:first-child :checkbox');

		if (!lastTickedCheckbox) {
			lastTickedCheckbox = this;
			return;
		}

		if (e.shiftKey) {
			let start = $chkboxes.index(this);
			let end = $chkboxes.index(lastTickedCheckbox);
			$chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastTickedCheckbox.checked);
		}

		lastTickedCheckbox = this;
	});

	// Data-Table Sorting (Page Reload)
	$('.table-sortable thead th[data-sort]').on('click', function() {
		let params = {};

		window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
			params[key] = value;
		});

		params.sort = $(this).data('sort');

		window.location.search = $.param(params);
	});

});

// Tabs (data-toggle="tab")

waitFor('jQuery', ($) => {
	'use strict';

	$.fn.tabs = function(){
		this.each(function() {

			const self = this;
			this.$element = $(this);

			this.$element.find('[data-toggle="tab"]').each(function() {
				const $link = $(this);

				$link.on('select', function() {
					self.$element.find('.active').removeClass('active');

					if ($link.hasClass('tab-item')) {
						$link.addClass('active');
					}

					$link.closest('.tab-item').addClass('active');
					$($link.attr('href')).show().siblings().hide();
				});

				$link.on('click', function(e) {
					e.preventDefault();
					history.replaceState(null, null, $link[0].hash);
					$link.trigger('select');
				});
			});

			const activeTab = this.$element.find('.active');

			if (!activeTab.length) {
				this.$element.find('[data-toggle="tab"]').first().trigger('select');
			} else {
				activeTab.trigger('select');
			}
		});
	};

	$('.tabs').tabs();

	if (document.location.hash && document.location.hash.match(/^#tab-/)) {
		$('[data-toggle="tab"][href="' + document.location.hash +'"]').trigger('select');
	}

	$(document).on('ajaxcomplete', function() {
		$('.tabs').tabs();
	});

});


// Polyfill for easeOutBounce
waitFor('jQuery', ($) => {

	$.extend($.easing, {
		easeOutCubic: function (x) {
			return 1 - Math.pow( 1 - x, 3 );
		},
		easeInCubic: function (x) {
			return Math.pow(x, 3);
		},
		easeOutBounce: function (x, t, b, c, d) {
			if ((t/=d) < (1/2.75)) {
				return c*(7.5625*t*t) + b;
			} else if (t < (2/2.75)) {
				return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
			} else if (t < (2.5/2.75)) {
				return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
			} else {
				return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
			}
		},
	});

});