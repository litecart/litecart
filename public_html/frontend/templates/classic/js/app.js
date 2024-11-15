	// Alerts
	$('body').on('click', '.alert .close', function(e){
		e.preventDefault();
		$(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
	});

	// Form required asterix
	$(':input[required]').closest('.form-group').addClass('required');

	// Money Formatting
	Number.prototype.toMoney = function() {
		var n = this,
			c = _env.session.currency.decimals,
			d = _env.session.language.decimal_point,
			t = _env.session.language.thousands_separator,
			p = _env.session.currency.prefix,
			x = _env.session.currency.suffix,
			s = n < 0 ? '-' : '',
			i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
			f = n - i,
			j = (j = i.length) > 3 ? j % 3 : 0;

		return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x;
	}

	// Detect scroll direction
	let lastScrollTop = 0;
	$(document).on('scroll', function(){
		 var scrollTop = $(this).scrollTop();
		 if (scrollTop > lastScrollTop) {
			 $('body').addClass('scrolling-down');
		 } else {
			 $('body').removeClass('scrolling-down');
		 }
		 lastScrollTop = (scrollTop < 0) ? 0 : scrollTop;
	});

	// Sidebar parallax effect
	if (typeof window._env != 'undefined') {
		if (window._env.template.settings.sidebar_parallax_effect == true) {

			var column = $('#sidebar > *:first-child'), sidebar = $('#sidebar');
			var sidebar_max_offset = $(sidebar).outerHeight(true) - $(column).height() - 20; // 20 = failsafe

			$(window).on('resize scroll', function(e){
				if (sidebar_max_offset) {
					var parallax_rate = 0.4;

					if ($(window).width() >= 768 && ($(column).outerHeight(true) < $(sidebar).height())) {
						var offset = $(this).scrollTop() * parallax_rate;
						if (offset > sidebar_max_offset) offset = sidebar_max_offset;
						if (offset > 0) $(column).css('margin-top', offset + 'px');
					} else {
						$(column).css('margin', 0);
					}
				}
			}).trigger('resize');
		}
	}

	// Tabs (data-toggle="tab")
	$('.nav-tabs').each(function(){
		if (!$(this).find('.active').length) {
			$(this).find('[data-toggle="tab"]:first').addClass('active');
		}

		$(this).on('select', '[data-toggle="tab"]', function() {
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
			$($(this).attr('href')).show().siblings().hide();
		});

		$(this).on('click', '[data-toggle="tab"]', function(e) {
			e.preventDefault();
			$(this).trigger('select');
			history.replaceState({}, '', location.toString().replace(/#.*$/, '') + $(this).attr('href'));
		});

		$(this).find('.active').trigger('select');
	});

	if (document.location.hash != '') {
		$('a[data-toggle="tab"][href="' + document.location.hash + '"]').trigger('click');
	}

	// Data-Table Toggle Checkboxes
	$('body').on('click', '.data-table *[data-toggle="checkbox-toggle"]', function() {
		$(this).closest('.data-table').find('tbody :checkbox').each(function() {
			$(this).prop('checked', !$(this).prop('checked'));
		});
		return false;
	});

	$('.data-table tbody tr').on('click', function(e) {
		if ($(e.target).is(':input')) return;
		if ($(e.target).is('a, a *')) return;
		if ($(e.target).is('th')) return;
		$(this).find('input:checkbox').trigger('click');
	});

	// Offcanvas
	$('[data-toggle="offcanvas"]').on('click', function(e){
		e.preventDefault();
		var target = $(this).data('target');
		if ($(target).hasClass('show')) {
			$(target).removeClass('show');
			$(this).removeClass('toggled');
			$('body').removeClass('has-offcanvas');
		} else {
			$(target).addClass('show');
			$(this).addClass('toggled');
			$('body').addClass('has-offcanvas');
		}
	});

	$('.offcanvas [data-toggle="dismiss"]').on('click', function(e){
		$('.offcanvas').removeClass('show');
		$('[data-toggle="offcanvas"]').removeClass('toggled');
		$('body').removeClass('has-offcanvas');
	});

	// Password Strength
	$('form').on('input', 'input[type="password"][data-toggle="password-strength"]', function(){

		$(this).siblings('meter').remove();

		if ($(this).val() == '') return;

		var numbers = ($(this).val().match(/[0-9]/g) || []).length,
		 lowercases = ($(this).val().match(/[a-z]/g) || []).length,
		 uppercases = ($(this).val().match(/[A-Z]/g) || []).length,
		 symbols    = ($(this).val().match(/[^\w]/g) || []).length,

		 score = (numbers * 9) + (lowercases * 11.25) + (uppercases * 11.25) + (symbols * 15)
					 + (numbers ? 10 : 0) + (lowercases ? 10 : 0) + (uppercases ? 10 : 0) + (symbols ? 10 : 0);

		var meter = $('<meter min="0" low="80" high="120" optimum="150" max="150" value="'+ score +'"></meter>').css({
			position: 'absolute',
			bottom: '-1em',
			width: '100%',
			height: '1em'
		});

		$(this).after(meter);
	});

	// Scroll Up
	$(window).scroll(function(){
		if ($(this).scrollTop() > 100) {
			$('#scroll-up').fadeIn();
		} else {
			$('#scroll-up').fadeOut();
		}
	});

	$('#scroll-up').on('click', function(){
		$('html, body').animate({scrollTop: 0}, 1000, 'swing');
		return false;
	});

	// Update cart / Keep alive
	var num_cart_updates = 0;
	window.updateCart = function(data) {

		$.ajax({
			url: window._env.platform.url + 'ajax/cart.json',
			type: data ? 'post' : 'get',
			data: data,
			cache: false,
			async: true,
			dataType: 'json',

			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
			},

			error: function(jqXHR, textStatus, errorThrown) {
				if (data) alert('Error while updating cart');
			},

			success: function(json) {

				if (json['alert']) alert(json['alert']);

				$('#cart .items').html('');

				if (json['items']) {
					$.each(json['items'], function(i, item){
						$('#cart .items').append('<li><a href="'+ item.link +'">'+ item.quantity +' x '+ item.name +' - '+ item.formatted_price +'</a></li>');
					});

					$('#cart .items').append('<li class="dropdown-divider"></li>');
				}

				$('#cart .items').append('<li><a href="' + window._env.platform.url + 'checkout"><i class="fa fa-shopping-cart"></i> ' + json['text_total'] + ': <span class="formatted-value">'+ json['formatted_value'] +'</a></li>');
				$('#cart .quantity').html(json['quantity'] ? json['quantity'] : '');
				$('#cart .formatted_value').html(json['formatted_value']);
			}
		});

		if (++num_cart_updates < 60) { // Continue refreshing up to 60 cycles
			setTimeout('updateCart', 60e3);
		}
	}

	setTimeout('updateCart', 60e3); // Keeps session alive

	// Add to cart animation
	$('body').on('submit', 'form[name="buy_now_form"]', function(e) {
		e.preventDefault();
		var form = $(this);
		$(this).find('button[name="add_cart_product"]').animate_from_to('#cart', {
			pixels_per_second: 2000,
			initial_css: {
				'border': '1px rgba(0,136,204,1) solid',
				'background-color': 'rgba(0,136,204,0.5)',
				'z-index': '999999',
				'border-radius': '3px',
				'padding': '5px'
			},
			callback: function() {
				updateCart($(form).serialize() + '&add_cart_product=true');
			}
		});
	});

/*
 * jQuery Animate From To plugin 1.0
 *
 * Copyright (c) 2011 Emil Stenstrom <http://friendlybit.com>
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
+function ($) {
	$.fn.animate_from_to = function(targetElm, options){
		return this.each(function(){
			animate_from_to(this, targetElm, options);
		});
	};

	$.extend({
		animate_from_to: animate_from_to
	});

	function animate_from_to(sourceElm, targetElm, options) {
		var source = $(sourceElm).eq(0),
			target = $(targetElm).eq(0);

		var defaults = {
			pixels_per_second: 1000,
			initial_css: {
				"background": "#dddddd",
				"opacity": 0.8,
				"position": "absolute",
				"top": source.offset().top,
				"left": source.offset().left,
				"height": source.height(),
				"width": source.width(),
				"z-index": 100000,
				"image": ""
			},
			square: '',
			callback: function(){ return; }
		}
		if (options && options.initial_css) {
			options.initial_css = $.extend({}, defaults.initial_css, options.initial_css);
		}
		options = $.extend({}, defaults, options);

		var target_height = target.innerHeight(),
			target_width = target.innerWidth();

		if (options.square.toLowerCase() == 'height') {
			target_width = target_height;
		} else if (options.square.toLowerCase() == 'width') {
			target_height = target_width;
		}

		var shadowImage = "";
		if (options.initial_css.image != "") {
			shadowImage = "<img src='" + options.initial_css.image + "' style='width: 100%; height: 100%'>";
		}

		var dy = source.offset().top + source.width()/2 - target.offset().top,
			dx = source.offset().left + source.height()/2 - target.offset().left,
			pixel_distance = Math.floor(Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2))),
			duration = (pixel_distance/options.pixels_per_second)*1000,

			shadow = $('<div id="animated-cart-item">' + shadowImage + '</div>')
				.css(options.initial_css)
				.appendTo('body')
				.animate({
					top: target.offset().top,
					left: target.offset().left,
					height: target_height,
					width: target_width
				}, {
					duration: duration
				})
				.animate({
					opacity: 0
				}, {
					duration: 100,
					complete: function(){
						shadow.remove();
						return options.callback();
					}
				});
	}
}(jQuery);

/*
 * Bootstrap: carousel.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#carousel
 *
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

+function ($) {
	'use strict';

	// CAROUSEL CLASS DEFINITION

	var Carousel = function (element, options) {
		this.$element    = $(element)
		this.$indicators = this.$element.find('.carousel-indicators')
		this.options     = options
		this.paused      = null
		this.sliding     = null
		this.interval    = null
		this.$active     = null
		this.$items      = null

		this.options.keyboard && this.$element.on('keydown.bs.carousel', $.proxy(this.keydown, this))

		this.options.pause == 'hover' && !('ontouchstart' in document.documentElement) && this.$element
			.on('mouseenter.bs.carousel', $.proxy(this.pause, this))
			.on('mouseleave.bs.carousel', $.proxy(this.cycle, this))
	}

	Carousel.VERSION  = '3.4.1'

	Carousel.TRANSITION_DURATION = 600

	Carousel.DEFAULTS = {
		interval: 5000,
		pause: 'hover',
		wrap: true,
		keyboard: true
	}

	Carousel.prototype.keydown = function (e) {
		if (/input|textarea/i.test(e.target.tagName)) return
		switch (e.which) {
			case 37: this.prev(); break
			case 39: this.next(); break
			default: return
		}

		e.preventDefault()
	}

	Carousel.prototype.cycle = function (e) {
		e || (this.paused = false)

		this.interval && clearInterval(this.interval)

		this.options.interval
			&& !this.paused
			&& (this.interval = setInterval($.proxy(this.next, this), this.options.interval))

		return this
	}

	Carousel.prototype.getItemIndex = function (item) {
		this.$items = item.parent().children('.item')
		return this.$items.index(item || this.$active)
	}

	Carousel.prototype.getItemForDirection = function (direction, active) {
		var activeIndex = this.getItemIndex(active)
		var willWrap = (direction == 'prev' && activeIndex === 0)
								|| (direction == 'next' && activeIndex == (this.$items.length - 1))
		if (willWrap && !this.options.wrap) return active
		var delta = direction == 'prev' ? -1 : 1
		var itemIndex = (activeIndex + delta) % this.$items.length
		return this.$items.eq(itemIndex)
	}

	Carousel.prototype.to = function (pos) {
		var that        = this
		var activeIndex = this.getItemIndex(this.$active = this.$element.find('.item.active'))

		if (pos > (this.$items.length - 1) || pos < 0) return

		if (this.sliding)       return this.$element.one('slid.bs.carousel', function () { that.to(pos) }) // yes, "slid"
		if (activeIndex == pos) return this.pause().cycle()

		return this.slide(pos > activeIndex ? 'next' : 'prev', this.$items.eq(pos))
	}

	Carousel.prototype.pause = function (e) {
		e || (this.paused = true)

		if (this.$element.find('.next, .prev').length && $.support.transition) {
			this.$element.trigger($.support.transition.end)
			this.cycle(true)
		}

		this.interval = clearInterval(this.interval)

		return this
	}

	Carousel.prototype.next = function () {
		if (this.sliding) return
		return this.slide('next')
	}

	Carousel.prototype.prev = function () {
		if (this.sliding) return
		return this.slide('prev')
	}

	Carousel.prototype.slide = function (type, next) {
		var $active   = this.$element.find('.item.active')
		var $next     = next || this.getItemForDirection(type, $active)
		var isCycling = this.interval
		var direction = type == 'next' ? 'left' : 'right'
		var that      = this

		if ($next.hasClass('active')) return (this.sliding = false)

		var relatedTarget = $next[0]
		var slideEvent = $.Event('slide.bs.carousel', {
			relatedTarget: relatedTarget,
			direction: direction
		})
		this.$element.trigger(slideEvent)
		if (slideEvent.isDefaultPrevented()) return

		this.sliding = true

		isCycling && this.pause()

		if (this.$indicators.length) {
			this.$indicators.find('.active').removeClass('active')
			var $nextIndicator = $(this.$indicators.children()[this.getItemIndex($next)])
			$nextIndicator && $nextIndicator.addClass('active')
		}

		var slidEvent = $.Event('slid.bs.carousel', { relatedTarget: relatedTarget, direction: direction }) // yes, "slid"
		if ($.support.transition && this.$element.hasClass('slide')) {
			$next.addClass(type)
			if (typeof $next === 'object' && $next.length) {
				$next[0].offsetWidth // force reflow
			}
			$active.addClass(direction)
			$next.addClass(direction)
			$active
				.one('bsTransitionEnd', function () {
					$next.removeClass([type, direction].join(' ')).addClass('active')
					$active.removeClass(['active', direction].join(' '))
					that.sliding = false
					setTimeout(function () {
						that.$element.trigger(slidEvent)
					}, 0)
				})
				.emulateTransitionEnd(Carousel.TRANSITION_DURATION)
		} else {
			$active.removeClass('active')
			$next.addClass('active')
			this.sliding = false
			this.$element.trigger(slidEvent)
		}

		isCycling && this.cycle()

		return this
	}

	// CAROUSEL PLUGIN DEFINITION

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.carousel')
			var options = $.extend({}, Carousel.DEFAULTS, $this.data(), typeof option == 'object' && option)
			var action  = typeof option == 'string' ? option : options.slide

			if (!data) $this.data('bs.carousel', (data = new Carousel(this, options)))
			if (typeof option == 'number') data.to(option)
			else if (action) data[action]()
			else if (options.interval) data.pause().cycle()
		})
	}

	var old = $.fn.carousel

	$.fn.carousel             = Plugin
	$.fn.carousel.Constructor = Carousel

	// CAROUSEL NO CONFLICT

	$.fn.carousel.noConflict = function () {
		$.fn.carousel = old
		return this
	}

	// CAROUSEL DATA-API

	var clickHandler = function (e) {
		var $this   = $(this)
		var href    = $this.attr('href')
		var target  = $this.attr('data-target') || href
		var $target = $(document).find(target)

		if (!$target.hasClass('carousel')) return

		var options = $.extend({}, $target.data(), $this.data())
		var slideIndex = $this.attr('data-slide-to')
		if (slideIndex) options.interval = false

		Plugin.call($target, options)

		if (slideIndex) {
			$target.data('bs.carousel').to(slideIndex)
		}

		e.preventDefault()
	}

	$(document)
		.on('click.bs.carousel.data-api', '[data-slide]', clickHandler)
		.on('click.bs.carousel.data-api', '[data-slide-to]', clickHandler)

	$(window).on('load', function () {
		$('[data-ride="carousel"]').each(function () {
			var $carousel = $(this)
			Plugin.call($carousel, $carousel.data())
		})
	})

}(jQuery);

/*
 * Bootstrap: collapse.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#collapse
 *
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

+function ($) {
	'use strict';

	// COLLAPSE PUBLIC CLASS DEFINITION

	var Collapse = function (element, options) {
		this.$element      = $(element)
		this.options       = $.extend({}, Collapse.DEFAULTS, options)
		this.$trigger      = $('[data-toggle="collapse"][href="#' + element.id + '"],' +
													 '[data-toggle="collapse"][data-target="#' + element.id + '"]')
		this.transitioning = null

		if (this.options.parent) {
			this.$parent = this.getParent()
		} else {
			this.addAriaAndCollapsedClass(this.$element, this.$trigger)
		}

		if (this.options.toggle) this.toggle()
	}

	Collapse.VERSION  = '3.4.1'

	Collapse.TRANSITION_DURATION = 350

	Collapse.DEFAULTS = {
		toggle: true
	}

	Collapse.prototype.dimension = function () {
		var hasWidth = this.$element.hasClass('width')
		return hasWidth ? 'width' : 'height'
	}

	Collapse.prototype.show = function () {
		if (this.transitioning || this.$element.hasClass('in')) return

		var activesData
		var actives = this.$parent && this.$parent.children('.panel').children('.in, .collapsing')

		if (actives && actives.length) {
			activesData = actives.data('bs.collapse')
			if (activesData && activesData.transitioning) return
		}

		var startEvent = $.Event('show.bs.collapse')
		this.$element.trigger(startEvent)
		if (startEvent.isDefaultPrevented()) return

		if (actives && actives.length) {
			Plugin.call(actives, 'hide')
			activesData || actives.data('bs.collapse', null)
		}

		var dimension = this.dimension()

		this.$element
			.removeClass('collapse')
			.addClass('collapsing')[dimension](0)
			.attr('aria-expanded', true)

		this.$trigger
			.removeClass('collapsed')
			.attr('aria-expanded', true)

		this.transitioning = 1

		var complete = function () {
			this.$element
				.removeClass('collapsing')
				.addClass('collapse in')[dimension]('')
			this.transitioning = 0
			this.$element
				.trigger('shown.bs.collapse')
		}

		if (!$.support.transition) return complete.call(this)

		var scrollSize = ['scroll', dimension.toLowerCase()].join('-')

		this.$element
			.one('bsTransitionEnd', $.proxy(complete, this))
			.emulateTransitionEnd(Collapse.TRANSITION_DURATION)[dimension](this.$element[0][scrollSize])
	}

	Collapse.prototype.hide = function () {
		if (this.transitioning || !this.$element.hasClass('in')) return

		var startEvent = $.Event('hide.bs.collapse')
		this.$element.trigger(startEvent)
		if (startEvent.isDefaultPrevented()) return

		var dimension = this.dimension()

		this.$element[dimension](this.$element[dimension]())[0].offsetHeight

		this.$element
			.addClass('collapsing')
			.removeClass('collapse in')
			.attr('aria-expanded', false)

		this.$trigger
			.addClass('collapsed')
			.attr('aria-expanded', false)

		this.transitioning = 1

		var complete = function () {
			this.transitioning = 0
			this.$element
				.removeClass('collapsing')
				.addClass('collapse')
				.trigger('hidden.bs.collapse')
		}

		if (!$.support.transition) return complete.call(this)

		this.$element
			[dimension](0)
			.one('bsTransitionEnd', $.proxy(complete, this))
			.emulateTransitionEnd(Collapse.TRANSITION_DURATION)
	}

	Collapse.prototype.toggle = function () {
		this[this.$element.hasClass('in') ? 'hide' : 'show']()
	}

	Collapse.prototype.getParent = function () {
		return $(document).find(this.options.parent)
			.find('[data-toggle="collapse"][data-parent="' + this.options.parent + '"]')
			.each($.proxy(function (i, element) {
				var $element = $(element)
				this.addAriaAndCollapsedClass(getTargetFromTrigger($element), $element)
			}, this))
			.end()
	}

	Collapse.prototype.addAriaAndCollapsedClass = function ($element, $trigger) {
		var isOpen = $element.hasClass('in')

		$element.attr('aria-expanded', isOpen)
		$trigger
			.toggleClass('collapsed', !isOpen)
			.attr('aria-expanded', isOpen)
	}

	function getTargetFromTrigger($trigger) {
		var href
		var target = $trigger.attr('data-target')
			|| (href = $trigger.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') // strip for ie7

		return $(document).find(target)
	}

	// COLLAPSE PLUGIN DEFINITION

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.collapse')
			var options = $.extend({}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option)

			if (!data && options.toggle && /show|hide/.test(option)) options.toggle = false
			if (!data) $this.data('bs.collapse', (data = new Collapse(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.collapse

	$.fn.collapse             = Plugin
	$.fn.collapse.Constructor = Collapse

	// COLLAPSE NO CONFLICT

	$.fn.collapse.noConflict = function () {
		$.fn.collapse = old
		return this
	}

	// COLLAPSE DATA-API

	$(document).on('click.bs.collapse.data-api', '[data-toggle="collapse"]', function (e) {
		var $this   = $(this)

		if (!$this.attr('data-target')) e.preventDefault()

		var $target = getTargetFromTrigger($this)
		var data    = $target.data('bs.collapse')
		var option  = data ? 'toggle' : $this.data()

		Plugin.call($target, option)
	})

}(jQuery);

/*
 * Bootstrap: dropdown.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#dropdowns
 *
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

+function ($) {
	'use strict';

	// DROPDOWN CLASS DEFINITION

	var backdrop = '.dropdown-backdrop'
	var toggle   = '[data-toggle="dropdown"]'
	var Dropdown = function (element) {
		$(element).on('click.bs.dropdown', this.toggle)
	}

	Dropdown.VERSION = '3.4.1'

	function getParent($this) {
		var selector = $this.attr('data-target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
		}

		var $parent = selector !== '#' ? $(document).find(selector) : null

		return $parent && $parent.length ? $parent : $this.parent()
	}

	function clearMenus(e) {
		if (e && e.which === 3) return
		$(backdrop).remove()
		$(toggle).each(function () {
			var $this         = $(this)
			var $parent       = getParent($this)
			var relatedTarget = { relatedTarget: this }

			if (!$parent.hasClass('open')) return

			if (e && e.type == 'click' && /input|textarea/i.test(e.target.tagName) && $.contains($parent[0], e.target)) return

			$parent.trigger(e = $.Event('hide.bs.dropdown', relatedTarget))

			if (e.isDefaultPrevented()) return

			$this.attr('aria-expanded', 'false')
			$parent.removeClass('open').trigger($.Event('hidden.bs.dropdown', relatedTarget))
		})
	}

	Dropdown.prototype.toggle = function (e) {
		var $this = $(this)

		if ($this.is('.disabled, :disabled')) return

		var $parent  = getParent($this)
		var isActive = $parent.hasClass('open')

		clearMenus()

		if (!isActive) {
			if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
				// if mobile we use a backdrop because click events don't delegate
				$(document.createElement('div'))
					.addClass('dropdown-backdrop')
					.insertAfter($parent)
					.on('click', clearMenus)
			}

			var relatedTarget = { relatedTarget: this }
			$parent.trigger(e = $.Event('show.bs.dropdown', relatedTarget))

			if (e.isDefaultPrevented()) return

			$this
				.trigger('focus')
				.attr('aria-expanded', 'true')

			$parent
				.toggleClass('open')
				.trigger($.Event('shown.bs.dropdown', relatedTarget))
		}

		return false
	}

	Dropdown.prototype.keydown = function (e) {
		if (!/(38|40|27|32)/.test(e.which) || /input|textarea/i.test(e.target.tagName)) return

		var $this = $(this)

		e.preventDefault()
		e.stopPropagation()

		if ($this.is('.disabled, :disabled')) return

		var $parent  = getParent($this)
		var isActive = $parent.hasClass('open')

		if (!isActive && e.which != 27 || isActive && e.which == 27) {
			if (e.which == 27) $parent.find(toggle).trigger('focus')
			return $this.trigger('click')
		}

		var desc = ' li:not(.disabled):visible a'
		var $items = $parent.find('.dropdown-menu' + desc)

		if (!$items.length) return

		var index = $items.index(e.target)

		if (e.which == 38 && index > 0)                 index--         // up
		if (e.which == 40 && index < $items.length - 1) index++         // down
		if (!~index)                                    index = 0

		$items.eq(index).trigger('focus')
	}

	// DROPDOWN PLUGIN DEFINITION

	function Plugin(option) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.dropdown')

			if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
			if (typeof option == 'string') data[option].call($this)
		})
	}

	var old = $.fn.dropdown

	$.fn.dropdown             = Plugin
	$.fn.dropdown.Constructor = Dropdown

	// DROPDOWN NO CONFLICT

	$.fn.dropdown.noConflict = function () {
		$.fn.dropdown = old
		return this
	}

	// APPLY TO STANDARD DROPDOWN ELEMENTS

	$(document)
		.on('click.bs.dropdown.data-api', clearMenus)
		.on('click.bs.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
		.on('click.bs.dropdown.data-api', toggle, Dropdown.prototype.toggle)
		.on('keydown.bs.dropdown.data-api', toggle, Dropdown.prototype.keydown)
		.on('keydown.bs.dropdown.data-api', '.dropdown-menu', Dropdown.prototype.keydown)

}(jQuery);

/*
 * Bootstrap: transition.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#transitions
 *
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

+function ($) {
	'use strict';

	// CSS TRANSITION SUPPORT (Shoutout: https://modernizr.com/)

	function transitionEnd() {
		var el = document.createElement('bootstrap')

		var transEndEventNames = {
			WebkitTransition : 'webkitTransitionEnd',
			MozTransition    : 'transitionend',
			OTransition      : 'oTransitionEnd otransitionend',
			transition       : 'transitionend'
		}

		for (var name in transEndEventNames) {
			if (el.style[name] !== undefined) {
				return { end: transEndEventNames[name] }
			}
		}

		return false // explicit for ie8 (  ._.)
	}

		// https://blog.alexmaccaw.com/css-transitions
	$.fn.emulateTransitionEnd = function (duration) {
		var called = false
		var $el = this
		$(this).one('bsTransitionEnd', function () { called = true })
		var callback = function () { if (!called) $($el).trigger($.support.transition.end) }
		setTimeout(callback, duration)
		return this
	}

	$(function () {
		$.support.transition = transitionEnd()

		if (!$.support.transition) return

		$.event.special.bsTransitionEnd = {
			bindType: $.support.transition.end,
			delegateType: $.support.transition.end,
			handle: function (e) {
				if ($(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
			}
		}
	})

}(jQuery);
