/*!
 * LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built without nonsense.
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
	if (_env && _env.platform && _env.platform.path) {
		let keepAlive = setInterval(function() {
			$.get({
				url: _env.platform.path + 'ajax/cart.json',
				cache: false
			});
		}, 60e3);
	}
});

/*
 * Bootstrap: carousel.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#carousel
 *
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

waitFor('jQuery', ($) => {
	'use strict';

	// CAROUSEL CLASS DEFINITION

	let Carousel = function (element, options) {
		this.$element    = $(element);
		this.$indicators = this.$element.find('.carousel-indicators');
		this.options     = options;
		this.paused      = null;
		this.sliding     = null;
		this.interval    = null;
		this.$active     = null;
		this.$items      = null;

		this.options.keyboard && this.$element.on('keydown.bs.carousel', $.proxy(this.keydown, this));

		this.options.pause == 'hover' && !('ontouchstart' in document.documentElement) && this.$element
			.on('mouseenter.bs.carousel', $.proxy(this.pause, this))
			.on('mouseleave.bs.carousel', $.proxy(this.cycle, this));
	};

	Carousel.VERSION  = '3.4.1';

	Carousel.TRANSITION_DURATION = 600;

	Carousel.DEFAULTS = {
		interval: 5000,
		pause: 'hover',
		wrap: true,
		keyboard: true
	};

	Carousel.prototype.keydown = function (e) {
		if (/input|textarea/i.test(e.target.tagName)) return;
		switch (e.which) {
			case 37: this.prev(); break;
			case 39: this.next(); break;
			default: return;
		}

		e.preventDefault();
	};

	Carousel.prototype.cycle = function (e) {
		e || (this.paused = false);

		this.interval && clearInterval(this.interval);

		this.options.interval
			&& !this.paused
			&& (this.interval = setInterval($.proxy(this.next, this), this.options.interval));

		return this;
	};

	Carousel.prototype.getItemIndex = function (item) {
		this.$items = item.parent().children('.item');
		return this.$items.index(item || this.$active);
	};

	Carousel.prototype.getItemForDirection = function (direction, active) {
		let activeIndex = this.getItemIndex(active);
		let willWrap = (direction == 'prev' && activeIndex === 0)
								|| (direction == 'next' && activeIndex == (this.$items.length - 1));
		if (willWrap && !this.options.wrap) return active;
		let delta = direction == 'prev' ? -1 : 1;
		let itemIndex = (activeIndex + delta) % this.$items.length;
		return this.$items.eq(itemIndex);
	};

	Carousel.prototype.to = function (pos) {
		let that        = this;
		let activeIndex = this.getItemIndex(this.$active = this.$element.find('.item.active'));

		if (pos > (this.$items.length - 1) || pos < 0) return;

		if (this.sliding)       return this.$element.one('slid.bs.carousel', function () { that.to(pos); }); // yes, "slid"
		if (activeIndex == pos) return this.pause().cycle();

		return this.slide(pos > activeIndex ? 'next' : 'prev', this.$items.eq(pos));
	};

	Carousel.prototype.pause = function (e) {
		e || (this.paused = true);

		if (this.$element.find('.next, .prev').length && $.support.transition) {
			this.$element.trigger($.support.transition.end);
			this.cycle(true);
		}

		this.interval = clearInterval(this.interval);

		return this;
	};

	Carousel.prototype.next = function () {
		if (this.sliding) return;
		return this.slide('next');
	};

	Carousel.prototype.prev = function () {
		if (this.sliding) return;
		return this.slide('prev');
	};

	Carousel.prototype.slide = function (type, next) {
		let $active   = this.$element.find('.item.active');
		let $next     = next || this.getItemForDirection(type, $active);
		let isCycling = this.interval;
		let direction = type == 'next' ? 'left' : 'right';
		let that      = this;

		if ($next.hasClass('active')) return (this.sliding = false);

		let relatedTarget = $next[0];
		let slideEvent = $.Event('slide.bs.carousel', {
			relatedTarget: relatedTarget,
			direction: direction
		});
		this.$element.trigger(slideEvent);
		if (slideEvent.isDefaultPrevented()) return;

		this.sliding = true;

		isCycling && this.pause();

		if (this.$indicators.length) {
			this.$indicators.find('.active').removeClass('active');
			let $nextIndicator = $(this.$indicators.children()[this.getItemIndex($next)]);
			$nextIndicator && $nextIndicator.addClass('active');
		}

		let slidEvent = $.Event('slid.bs.carousel', { relatedTarget: relatedTarget, direction: direction }); // yes, "slid"
		if ($.support.transition && this.$element.hasClass('slide')) {
			$next.addClass(type);
			if (typeof $next === 'object' && $next.length) {
				$next[0].offsetWidth; // force reflow
			}
			$active.addClass(direction);
			$next.addClass(direction);
			$active
				.one('bsTransitionEnd', function () {
					$next.removeClass([type, direction].join(' ')).addClass('active');
					$active.removeClass(['active', direction].join(' '));
					that.sliding = false;
					setTimeout(function () {
						that.$element.trigger(slidEvent);
					}, 0);
				})
				.emulateTransitionEnd(Carousel.TRANSITION_DURATION);
		} else {
			$active.removeClass('active');
			$next.addClass('active');
			this.sliding = false;
			this.$element.trigger(slidEvent);
		}

		isCycling && this.cycle();

		return this;
	};

	// CAROUSEL PLUGIN DEFINITION

	function Plugin(option) {
		return this.each(function () {
			let $this   = $(this);
			let data    = $this.data('bs.carousel');
			let options = $.extend({}, Carousel.DEFAULTS, $this.data(), typeof option == 'object' && option);
			let action  = typeof option == 'string' ? option : options.slide;

			if (!data) $this.data('bs.carousel', (data = new Carousel(this, options)));
			if (typeof option == 'number') data.to(option);
			else if (action) data[action]();
			else if (options.interval) data.pause().cycle();
		});
	}

	let old = $.fn.carousel;

	$.fn.carousel             = Plugin;
	$.fn.carousel.Constructor = Carousel;

	// CAROUSEL NO CONFLICT

	$.fn.carousel.noConflict = function () {
		$.fn.carousel = old;
		return this;
	};

	// CAROUSEL DATA-API

	let clickHandler = function (e) {
		let href;
		let $this   = $(this);
		let $target = $($this.attr('data-target') || $this.closest('.carousel'));
		if (!$target.hasClass('carousel')) return;

		let options = $.extend({}, $target.data(), $this.data());
		let slideIndex = $this.attr('data-slide-to');

		if (slideIndex) options.interval = false;

		Plugin.call($target, options);

		if (slideIndex) {
			$target.data('bs.carousel').to(slideIndex);
		}

		e.preventDefault();
	};

	$(document)
		.on('click.bs.carousel.data-api', '[data-slide]', clickHandler)
		.on('click.bs.carousel.data-api', '[data-slide-to]', clickHandler);

	$(window).on('load', function () {
		$('[data-ride="carousel"]').each(function () {
			let $carousel = $(this);
			Plugin.call($carousel, $carousel.data());
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
						let dropdownMenu = $(self).find('.dropdown-menu');

						$(dropdownMenu).html('');

						if (self.xhr) self.xhr.abort();

						if ($(this).val() == '') {

							$.getJSON(self.config.link, function(result) {

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									);
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
									$(dropdownMenu).html('<li class="dropdown-item text-center no-results"><em>:(</em></li>');
									return;
								}

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>');

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									);
								});
							},
						});
					}
			});

			$(this).on('click', '.dropdown-menu .dropdown-item a', function(e) {
				e.preventDefault();

				let dropdownMenu = $(this).closest('.dropdown-menu');

				$.getJSON($(this).data('link'), function(result) {

					$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

					if (result.id) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.back +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
							'</li>'
						);
					}

					$.each(result.subcategories, function(i, category) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.folder +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
							'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
							'</li>'
						);
					});
				});
			});

			$(this).on('click', '.dropdown-menu .dropdown-item button.add', function(e) {
				e.preventDefault();

				let category = $(this).closest('li'),
						abort = false;

				$(self).find('input[name="'+ self.config.inputName +'"]').each(function() {
					if ($(this).val() == category.data('id')) {
						abort = true;
						return;
					}
				});

				if (abort) return;

				$(self).find('.categories').append(
					'<li class="dropdown-item" style="display: flex; align-items: center;">' +
					'  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ $(category).data('name').replace(/"/, '&quote;') +'">' +
					'  <div style="flex-grow: 1;">' + self.config.icons.folder +' '+ $(category).data('name') +'</div>' +
					'  <div><button class="remove btn btn-default btn-sm" type="button">'+ self.config.translations.remove +'</button></div>' +
					'</li>'
				);

				$(self).trigger('change');

				$('.dropdown.open').removeClass('open');

				return false;
			});

			$(this).find('.categories').on('click', '.remove', function(e) {
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


/* Context Menu */

waitFor('jQuery', ($) => {

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
		.html('.grabbed { opacity: 0.5; }')
		.appendTo('head');

	$.fn.draggable = function(options) {

		// Default settings
		var settings = $.extend({
			handle: null,
			cursor: 'ns-resize',
			direction: 'vertical' // Default direction
		}, options);

		return this.each(function() {
			var $self = $(this),
					$handle = settings.handle ? $self.find(settings.handle) : $self,
					dragging = false,
					startPos = null;

			// Add basic styling
			$self.css({
				'position': 'relative',
				'user-select': 'none'
			});

			$handle.css({
				'cursor': settings.cursor
			});

			// Mouse down handler
			$handle.on('mousedown', function(e) {
				e.preventDefault();
				dragging = true;
				startPos = {
					x: e.pageX,
					y: e.pageY
				};
				$self.addClass('grabbed');
				$self.parent().addClass('dragging');

				// Store original position
				$self.data('original-index', $self.index());
			});

			// Mouse move handler
			$(document).on('mousemove', function(e) {
				if (!dragging) return;
				e.preventDefault();

				var $siblings = $self.siblings().not('.grabbed'),
						selfHeight = $self.outerHeight(),
						selfWidth = $self.outerWidth(),
						selfOffset = $self.offset(),
						selfTopY = selfOffset.top,
						selfBottomY = selfOffset.top + selfHeight,
						selfLeftX = selfOffset.left,
						selfRightX = selfOffset.left + selfWidth,
						mouseX = e.pageX,
						mouseY = e.pageY;

				// Find the sibling to swap with
				$siblings.each(function() {
					var $sibling = $(this),
							siblingOffset = $sibling.offset(),
							siblingHeight = $sibling.outerHeight(),
							siblingWidth = $sibling.outerWidth(),
							siblingTop = siblingOffset.top,
							siblingBottom = siblingOffset.top + siblingHeight,
							siblingLeft = siblingOffset.left,
							siblingRight = siblingOffset.left + siblingWidth;

					if (settings.direction === 'vertical') {
						// Moving up: use self's top Y position
						if (mouseY < selfTopY && siblingBottom > selfTopY && siblingTop < selfTopY) {
							$sibling.before($self);
						}
						// Moving down: use self's bottom Y position
						else if (mouseY > selfBottomY && siblingTop < selfBottomY && siblingBottom > selfBottomY) {
							$sibling.after($self);
						}
					} else if (settings.direction === 'horizontal') {
						// Moving left: use self's left X position
						if (mouseX < selfLeftX && siblingRight > selfLeftX && siblingLeft < selfLeftX) {
							$sibling.before($self);
						}
						// Moving right: use self's right X position
						else if (mouseX > selfRightX && siblingLeft < selfRightX && siblingRight > selfRightX) {
							$sibling.after($self);
						}
					}
				});
			});

			// Mouse up handler
			$(document).on('mouseup', function(e) {
				if (!dragging) return;
				dragging = false;
				$self.removeClass('grabbed');
				$self.parent().removeClass('dragging');
			});

			// Prevent text selection while dragging
			$self.on('dragstart selectstart', function() {
				return false;
			});
		});
	};

	// Initialize draggable elements
	$('[draggable="true"]').draggable({
		handle: '.grabbable',
		cursor: 'ns-resize',
		direction: 'vertical' // Default direction
	});

});

// Dropdown
waitFor('jQuery', ($) => {

	$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
		$(this).closest('.dropdown').toggleClass('open');
	});

	$('.dropdown-item').on('click', 'a', function(e) {
		$(this).closest('.dropdown').removeClass('open');
	});

	// Listen for clicks outside the dropdown to uncheck the input
	$(document).on('click', function(e) {

		if ($('.dropdown.open').length === 0) {
			return;
		}

		// If click is on dropdown::before psuedo element, remove open class
		if ($(e.target).closest('.dropdown').length === 0) {
			$('.dropdown.open').removeClass('open');
		}
	});

});

/*!
 * jQuery Plugin developed by Mario Duarte
 * https://github.com/Mario-Duarte/image-zoom-plugin/
 * Simple jQuery plugin that converts an image into a click to zoom image
 */
waitFor('jQuery', ($) => {

	$.fn.imageZoom = function (options) {

		// Default settings for the zoom level
		const settings = $.extend({
			zoom: 150
		}, options);

		// Main html template for the zoom in plugin
		const $imageObj = $([
			'<figure class="containerZoom">',
			'	<img id="imageZoom">',
			'</figure>',
		].join('\n'));

		$imageObj.css({
			'background-image': `url('${$(this).attr('src')}')`,
			'background-size': `${settings.zoom}%`,
			'background-position': '50% 50%',
			'position': 'relative',
			'width': '100%',
			'overflow': 'hidden',
			'cursor': 'zoom-in',
			'margin': 0,
		});

		$imageObj.find('img')
			.attr('src', $(this).attr('src'))
			.attr('alt', $(this).attr('alt'))
			.css({
				'transition':'opacity .5s',
				'display':'block',
				'width':'100%',
			});

		// Where all the magic happens, This will detect the position of your mouse
		// in relation to the image and pan the zoomed in background image in the same direction
		const zoomIn = (e) => {
			const zoomer = e.currentTarget;
			let offsetX, offsetY;

			switch (e.type) {
				case 'mousemove':
					offsetX = e.offsetX || e.clientX - $(zoomer).offset().left;
					offsetY = e.offsetY || e.clientY - $(zoomer).offset().top;
					break;

				case 'touchmove':
					e.preventDefault(); // Prevent default touch behavior (scrolling)
					offsetX = Math.min(Math.max(0, e.originalEvent.touches[0].pageX - $(zoomer).offset().left), zoomer.offsetWidth);
					offsetY = Math.min(Math.max(0, e.originalEvent.touches[0].pageY - $(zoomer).offset().top), zoomer.offsetHeight);
					break;
			}

			const x = offsetX / zoomer.offsetWidth * 100;
			const y = offsetY / zoomer.offsetHeight * 100;

			$(zoomer).css({
				'background-position': `${x}% ${y}%`,
			});
		};

		let newElm;

		if (this[0].nodeName === 'IMG') {
			newElm = $(this).replaceWith($imageObj);
			$(this).on({

				'click touchstart': function(e) {
					if (!("zoom" in $imageObj)) {
						$imageObj.zoom = false;
					}
					if ($imageObj.zoom) {
						$imageObj.zoom = false;
						$(this).removeClass('active');
					} else {
						$imageObj.zoom = true;
						$(this).addClass('active');
						$(this).find('img').css('opacity', 0);
						zoomIn(e);
					}
				},

				'mousemove touchmove': function(e) {
					$imageObj.zoom ? zoomIn(e) : null;
				},

				'mouseleave touchend': function() {
					$imageObj.zoom = false;
					$(this).removeClass('active');
				}
			});
		} else {
			newElm = $(this);
		}

		return newElm;
	};
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

// CSV Input
waitFor('jQuery', ($) => {

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
			'<tr>' + ('<td contenteditable></td>'.repeat(n)) + '<td><a class="remove" href="#"><i class="fa fa-times" style="color: #d33;"></i></a></td>' +'</tr>'
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

// Form Input Tags
waitFor('jQuery', ($) => {

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
				`		<div class="litebox-inner">${this.loading}</div>`,
				'	</div>',
				'</div>'
			].join('\n'));

			this.$instance.on('click.litebox', (e) => {
				if (e.isDefaultPrevented() || !(
						(this.closeOnClick === 'backdrop' && $(e.target).is('.litebox')) ||
						this.closeOnClick === 'anywhere' ||
						$(e.target).is('.litebox-close')
					)
				) return;
				this.close(e);
				e.preventDefault();
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
						$img.on('error', () => deferred.reject());
						return deferred.promise();
					}
				},
				html: {
					regex: /^\s*<[\w!][^<]*>/,
					process: (html) => $(html)
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
				iframe: {
					process: function (url) {
						const deferred = $.Deferred();
						const $iframe = $('<iframe/>', { src: url });
						$iframe.on('load', () => { $iframe.show().appendTo(this.$instance.find('.litebox-modal')); deferred.resolve($iframe); });
						return deferred.promise();
					}
				},
				raw: {
					regex: /\.(log|md|txt)(\?\S*)?$/i,
					process: function(url) {
						const deferred = $.Deferred();
						const $content = $('<div>').css({ "white-space": 'pre-wrap', "max-width": '90vw' });
						$.get(url, raw => $content.text(raw)).done(() => deferred.resolve($content));
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
			if (e.isDefaultPrevented()) return;
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
				$inner.fadeTo(this.galleryFadeOut, 0.2)
			).always(($newContent) => {
				this.setContent($newContent);
				this.afterContent();
				$newContent.fadeTo(this.galleryFadeIn, 1);
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

	$('[data-toggle*="momentumScroll"]').momentumScroll();
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

// Password Strength
waitFor('jQuery', ($) => {

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


// jQuery Placeholders by LiteCart
waitFor('jQuery', ($) => {

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