/*!
 * LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built without nonsense.
 * @link https://www.litecart.net/
 * @license CC-BY-ND-4.0
 * @author T. Almroth
 */

// Stylesheet Loader
$.loadStylesheet = function(url, options, callback, fallback) {

	options = $.extend(options || {}, {
		rel: 'stylesheet',
		href: url,
		onload: callback,
		onerror: fallback
	})

	$('<link>', options).appendTo('head')
}

// JavaScript Loader
$.loadScript = function(url, options) {

	options = $.extend(options || {}, {
		method: 'GET',
		dataType: 'script',
		cache: true
	})

	return jQuery.ajax(url, options)
}

// Escape HTML
function escapeHTML(string) {

	let entityMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
	}

	return String(string).replace(/[&<>"'\/]/g, function (s) {
		return entityMap[s]
	})
}

// Escape HTML
function escapeAttr(string) {
	return escapeHTML(string).replace(/\r\n?|\n/g, '\\n')
}

// Money Formatting
Number.prototype.toMoney = function() {
	var n = this,
		c = _env.session.currency.decimals,
		d = _env.session.language.decimal_point,
		t = _env.session.language.thousands_separator,
		p = _env.session.currency.prefix,
		x = _env.session.currency.suffix,
		u = _env.session.currency.code,
		s = n < 0 ? '-' : '',
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
		f = n - i,
		j = (j = i.length) > 3 ? j % 3 : 0

	return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x
}

// Keep-alive
let keepAlive = setInterval(function() {
	$.get({
		url: window._env.platform.path + 'ajax/cart.json',
		cache: false
	})
}, 60e3)


/*
 * Bootstrap: carousel.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#carousel
 *
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */

+function ($) {
	'use strict'

	// CAROUSEL CLASS DEFINITION

	let Carousel = function (element, options) {
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
		let activeIndex = this.getItemIndex(active)
		let willWrap = (direction == 'prev' && activeIndex === 0)
								|| (direction == 'next' && activeIndex == (this.$items.length - 1))
		if (willWrap && !this.options.wrap) return active
		let delta = direction == 'prev' ? -1 : 1
		let itemIndex = (activeIndex + delta) % this.$items.length
		return this.$items.eq(itemIndex)
	}

	Carousel.prototype.to = function (pos) {
		let that        = this
		let activeIndex = this.getItemIndex(this.$active = this.$element.find('.item.active'))

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
		let $active   = this.$element.find('.item.active')
		let $next     = next || this.getItemForDirection(type, $active)
		let isCycling = this.interval
		let direction = type == 'next' ? 'left' : 'right'
		let that      = this

		if ($next.hasClass('active')) return (this.sliding = false)

		let relatedTarget = $next[0]
		let slideEvent = $.Event('slide.bs.carousel', {
			relatedTarget: relatedTarget,
			direction: direction
		})
		this.$element.trigger(slideEvent)
		if (slideEvent.isDefaultPrevented()) return

		this.sliding = true

		isCycling && this.pause()

		if (this.$indicators.length) {
			this.$indicators.find('.active').removeClass('active')
			let $nextIndicator = $(this.$indicators.children()[this.getItemIndex($next)])
			$nextIndicator && $nextIndicator.addClass('active')
		}

		let slidEvent = $.Event('slid.bs.carousel', { relatedTarget: relatedTarget, direction: direction }) // yes, "slid"
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
			let $this   = $(this)
			let data    = $this.data('bs.carousel')
			let options = $.extend({}, Carousel.DEFAULTS, $this.data(), typeof option == 'object' && option)
			let action  = typeof option == 'string' ? option : options.slide

			if (!data) $this.data('bs.carousel', (data = new Carousel(this, options)))
			if (typeof option == 'number') data.to(option)
			else if (action) data[action]()
			else if (options.interval) data.pause().cycle()
		})
	}

	let old = $.fn.carousel

	$.fn.carousel             = Plugin
	$.fn.carousel.Constructor = Carousel

	// CAROUSEL NO CONFLICT

	$.fn.carousel.noConflict = function () {
		$.fn.carousel = old
		return this
	}

	// CAROUSEL DATA-API

	let clickHandler = function (e) {
		let href
		let $this   = $(this)
		let $target = $($this.attr('data-target') || $this.closest('.carousel'))
		if (!$target.hasClass('carousel')) return

		let options = $.extend({}, $target.data(), $this.data())
		let slideIndex = $this.attr('data-slide-to')

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
			let $carousel = $(this)
			Plugin.call($carousel, $carousel.data())
		})
	})

}(jQuery)

/*
 * jQuery Category Picker
 * by LiteCart
 */

+function() {

	$.fn.categoryPicker = function(config){
		this.each(function() {

			this.xhr = null
			this.config = config

			self = this

			$(this).find('.dropdown input[type="search"]').on({

				'focus': function(e) {
					$(self).find('.dropdown').addClass('open')
				},

				'input': function(e) {
						let dropdownMenu = $(self).find('.dropdown-menu')

						$(dropdownMenu).html('')

						if (self.xhr) self.xhr.abort()

						if ($(this).val() == '') {

							$.getJSON(self.config.link, function(result) {

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>')

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									)
								})
							})

							return
						}

						self.xhr = $.ajax({
							type: 'get',
							async: true,
							cache: true,
							url: self.config.link + '&query=' + $(this).val(),
							dataType: 'json',

							beforeSend: function(jqXHR) {
								jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'))
							},

							error: function(jqXHR, textStatus, errorThrown) {
								if (errorThrown == 'abort') return
								alert(errorThrown)
							},

							success: function(result) {

								if (!result.subcategories.length) {
									$(dropdownMenu).html('<li class="dropdown-item text-center no-results"><em>:(</em></li>')
									return
								}

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>')

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									)
								})
							},
						})
					}
			})

			$(this).on('click', '.dropdown-menu .dropdown-item a', function(e) {
				e.preventDefault()

				let dropdownMenu = $(this).closest('.dropdown-menu')

				$.getJSON($(this).data('link'), function(result) {

					$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>')

					if (result.id) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.back +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
							'</li>'
						)
					}

					$.each(result.subcategories, function(i, category) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.folder +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
							'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
							'</li>'
						)
					})
				})
			})

			$(this).on('click', '.dropdown-menu .dropdown-item button.add', function(e) {
				e.preventDefault()

				let category = $(this).closest('li'),
						abort = false

				$(self).find('input[name="'+ self.config.inputName +'"]').each(function() {
					if ($(this).val() == category.data('id')) {
						abort = true
						return
					}
				})

				if (abort) return

				$(self).find('.categories').append(
					'<li class="dropdown-item" style="display: flex; align-items: center;">' +
					'  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ $(category).data('name').replace(/"/, '&quote;') +'" />' +
					'  <div style="flex-grow: 1;">' + self.config.icons.folder +' '+ $(category).data('name') +'</div>' +
					'  <div><button class="remove btn btn-default btn-sm" type="button">'+ self.config.translations.remove +'</button></div>' +
					'</li>'
				)

				$(self).trigger('change')

				$('.dropdown.open').removeClass('open')

				return false
			})

			$(this).find('.categories').on('click', '.remove', function(e) {
				$(this).closest('li').remove()
				$(self).trigger('change')
			})

			$('body').on('mousedown', function(e) {
				if ($('.dropdown.open').has(e.target).length === 0) {
					$('.dropdown.open').removeClass('open')
				}
			})

			$(this).find('input[type="search"]').trigger('input')

		})
	}

}()


/*
 * jQuery Context Menu
 * by LiteCart
 */

+function() {

	$.fn.contextMenu = function(config){
		this.each(function() {

			this.config = config

			self = this

			$(this).on('contextmenu').on({
			})
		})
	}

}()


// Dragmove

$('style').first().append([
	'.dragmove-horizontal {',
	'  cursor: e-resize;',
	'  user-select: none;',
	'}',
	'.dragmove-vertical {',
	'  cursor: n-resize;',
	'  user-select: none;',
	'}',
	'.dragmove-vertical.grabbed,',
	'.dragmove-horizontal.grabbed	{',
	'  user-input: unset;',
	'  box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);',
	'}',
].join('\n'))

$('body').on('click', '.dragmove', function(e) {
	e.preventDefault()
	return false
})

$('body').on('mousedown', '.dragmove-vertical, .dragmove-horizontal', function(e) {

	let $item = $(e.target).closest('.dragmove'),
		sy = e.pageY,
		drag

	if ($(e.target).is('.dragmove')) {
		$item = $(e.target)
	}

	let index = $item.index()

	$item.addClass('grabbed')
	$item.closest('tbody').css('user-input', 'unset')

	function move(e) {

		if (!drag && Math.abs(e.pageY - sy) < 10) return
		drag = true

		$item.siblings().each(function() {

			let s = $(this), i = s.index(), y = s.offset().top

			if (e.pageY >= y && e.pageY < y + s.outerHeight()) {
				if (i < $item.index()) s.insertAfter($item)
				else s.insertBefore($item)
				return false
			}
		})
	}

	function up(e) {

		if (drag && index != $item.index()) {
			drag = false
		}

		$(document).off('mousemove', move).off('mouseup', up)
		$item.removeClass('grabbed')
		$item.closest('tbody').css('user-input', '')
	}

	$(document).mousemove(move).mouseup(up)
})


// Dropdown

$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
	$(this).closest('.dropdown').toggleClass('open')
})

$('.dropdown').on('click', 'a', function(e) {
	$(this).closest('.dropdown').removeClass('open')
})

// Listen for clicks outside the dropdown to uncheck the input
$(document).on('click', function(e) {
  if (!$(e.target).closest('.dropdown').length) {
    $('[data-toggle="dropdown"]').prop('checked', false);
  }
});


/*!
 * jQuery Plugin developed by Mario Duarte
 * https://github.com/Mario-Duarte/image-zoom-plugin/
 * Simple jQuery plugin that converts an image into a click to zoom image
 */
+function ($) {

  $.fn.imageZoom = function (options) {

    // Default settings for the zoom level
    const settings = $.extend({
      zoom: 150
    }, options)

    // Main html template for the zoom in plugin
    const $imageObj = $([
			'<figure class="containerZoom">',
			'	<img id="imageZoom">',
			'</figure>',
		].join('\n'))

		$imageObj.css({
			'background-image': `url('${$(this).attr('src')}')`,
			'background-size': `${settings.zoom}%`,
			'background-position': '50% 50%',
			'position': 'relative',
			'width': '100%',
			'overflow': 'hidden',
			'cursor': 'zoom-in',
			'margin': 0,
		})

		$imageObj.find('img')
			.attr('src', $(this).attr('src'))
			.attr('alt', $(this).attr('alt'))
			.css({
				'transition':'opacity .5s',
				'display':'block',
				'width':'100%',
			})

    // Where all the magic happens, This will detect the position of your mouse
    // in relation to the image and pan the zoomed in background image in the same direction
    const zoomIn = (e) => {
      const zoomer = e.currentTarget
      let offsetX, offsetY

			switch (e.type) {
				case 'mousemove':
					offsetX = e.offsetX || e.clientX - $(zoomer).offset().left
					offsetY = e.offsetY || e.clientY - $(zoomer).offset().top
					break

				case 'touchmove':
					e.preventDefault(); // Prevent default touch behavior (scrolling)
					offsetX = Math.min(Math.max(0, e.originalEvent.touches[0].pageX - $(zoomer).offset().left), zoomer.offsetWidth)
					offsetY = Math.min(Math.max(0, e.originalEvent.touches[0].pageY - $(zoomer).offset().top), zoomer.offsetHeight)
					break
      }

      const x = offsetX / zoomer.offsetWidth * 100
      const y = offsetY / zoomer.offsetHeight * 100

      $(zoomer).css({
        'background-position': `${x}% ${y}%`,
      })
    }

    let newElm;

    if (this[0].nodeName === 'IMG') {
      newElm = $(this).replaceWith($imageObj)
      $(this).on({

				'click touchstart': function(e) {
					if (!("zoom" in $imageObj)) {
						$imageObj.zoom = false
					}
					if ($imageObj.zoom) {
						$imageObj.zoom = false
						$(this).removeClass('active')
					} else {
						$imageObj.zoom = true;
						$(this).addClass('active')
						$(this).find('img').css('opacity', 0)
						zoomIn(e)
					}
				},

				'mousemove touchmove': function(e) {
					$imageObj.zoom ? zoomIn(e) : null
				},

				'mouseleave touchend': function() {
					$imageObj.zoom = false
					$(this).removeClass('active')
				}
			})
    } else {
      newElm = $(this)
    }

    return newElm;
  };
}(jQuery);

// Form required asterix
$(':input[required]').closest('.form-group').addClass('required')

// Dropdown Select
$('.dropdown .form-select + .dropdown-menu :input').on('input', function(e) {

	let $dropdown = $(this).closest('.dropdown')
	let $input = $dropdown.find(':input:checked')

	if (!$dropdown.find(':input:checked').length) return

	$dropdown.find('li.active').removeClass('active')

	if ($input.data('title')) {
		$dropdown.find('.form-select').text( $input.data('title') )
	} else if ($input.closest('.option').find('.title').length) {
		$dropdown.find('.form-select').text( $input.closest('.option').find('.title').text() )
	} else {
		$dropdown.find('.form-select').text( $input.parent().text() )
	}

	$input.closest('li').addClass('active')
	$dropdown.trigger('click.bs.dropdown')

}).trigger('input')

// Input Number Decimals
$('body').on('change', 'input[type="number"][data-decimals]', function() {
	var value = parseFloat($(this).val()),
		decimals = $(this).data('decimals')
	if (decimals != '') {
		$(this).val(value.toFixed(decimals))
	}
})



// CSV Input

$('textarea[data-toggle="csv"] + table').on('click', '.remove', function(e) {
	e.preventDefault()
	var parent = $(this).closest('tbody')
	$(this).closest('tr').remove()
	$(parent).trigger('keyup')
})

$('textarea[data-toggle="csv"] + table .add-row').on('click', function(e) {
	e.preventDefault()
	var n = $(this).closest('table').find('thead th:not(:last-child)').length
	$(this).closest('table').find('tbody').append(
		'<tr>' + ('<td contenteditable></td>'.repeat(n)) + '<td><a class="remove" href="#"><i class="fa fa-times" style="color: #d33;"></i></a></td>' +'</tr>'
	).trigger('keyup')
})

$('textarea[data-toggle="csv"] + table .add-column').on('click', function(e) {
	e.preventDefault()
	var table = $(this).closest('table')
	var title = prompt("Column Title")
	if (!title) return
	$(table).find('thead tr th:last-child:last-child').before('<th>'+ title +'</th>')
	$(table).find('tbody tr td:last-child:last-child').before('<td contenteditable></td>')
	$(table).find('tfoot tr td').attr('colspan', $(this).closest('table').find('tfoot tr td').attr('colspan') + 1)
	$(this).trigger('keyup')
})

$('textarea[data-toggle="csv"] + table').on('keyup', function(e) {
	var csv = $(this).find('thead tr, tbody tr').map(function (i, row) {
			return $(row).find('th:not(:last-child),td:not(:last-child)').map(function (j, col) {
				var text = $(col).text()
				if (/('|,)/.test(text)) {
					return '"'+ text.replace(/"/g, '""') +'"'
				} else {
					return text
				}
			}).get().join(',')
		}).get().join('\r\n')
	$(this).next('textarea').val(csv)
})


// Form Input Tags

$('input[data-toggle="tags"]').each(function() {

	let $originalInput = $(this)

	let $tagField = $(
		'<div class="form-input">\
			<ul class="tokens">\
				<span class="input" contenteditable></span>\
			</ul>\
		</div>'
	)

	$tagField.tags = []

	$tagField.add = function(input){

		input = input.trim()

		if (!input) return

		$tagField.tags.push(input)

		let $tag = $(
			'<li class="tag">\
				<span class="value"></span>\
				<span class="remove">x</span>\
			</li>')

		$('.value', $tag).text(input)
		$('.input', $tagField).before($tag)

		$tagField.trigger('change')
	}

	$tagField.remove = function(input){

		$tagField.tags = $.grep($tagField.tags, function(value) {
			return value != input
		})

		$('.tag .value', $tagField).each(function() {
			if ($(this).text() == input) {
				$(this).parent('.tag').remove()
			}
		})

		$tagField.trigger('change')
	}

	let tags = $.grep($originalInput.val().split(/\s*,\s*/), function(value) {
		return value
	})

	$.each(tags, function() {
		$tagField.add(this)
	})

	$tagField.on('keypress', '.input', function(e) {
		if (e.which == 44 || e.which == 13) { // Comma or enter
			e.preventDefault()
			$tagField.add($(this).text())
			$(this).text('')
		}
	})

	$tagField.on('blur', '.input', function() {
		$tagField.add($(this).text())
		$(this).text('')
	})

	$tagField.on('click', '.remove', function(e) {
		$tagField.remove($(this).siblings('.value').text())
	})

	$tagField.on('change', function() {
		$originalInput.val($tagField.tags.join(','))
	})

	$(this).hide().after($tagField)
})


/*
 * Momentum Scroll
 * by LiteCart
 */

+function($) {

	$.fn.momentumScroll = function() {
		this.each(function() {

			let $self = $(this),
				$content = $self.find('.scroll-content')
				direction = '',
				velX = 0,
				clickX = 0,
				scrollX = 0,
				clicked = false,
				dragging = false,
				momentumID = null

			if ($(this).width() <= 768) {
				$content.css('overflow', 'auto')
			}

			let momentumLoop = function() {

				if (direction == 'left') {
					$content.scrollLeft($content.scrollLeft() - velX); // Apply the velocity to the scroll position
				} else {
					$content.scrollLeft($content.scrollLeft() + velX)
				}

				velX *= 1 - 5 / 100; // Slow down the velocity 5%

				if (Math.abs(velX) > 0.5) { // Still moving?
					momentumID = requestAnimationFrame(momentumLoop); // Keep looping
				}
			}

			$content.on({

				'click': function(e) {
					if (dragging) {
						e.preventDefault()
					}
					dragging = false
				},

				'mousemove': function(e) {
					if (!clicked) return

					dragging = true

					let prevScrollLeft = $content.scrollLeft(); // Store the previous scroll position
						currentDrag = (clickX - e.pageX)

					$content.scrollLeft(scrollX + (clickX - e.pageX))

					if (currentDrag > 0) {
						direction = 'right'
					} else {
						direction = 'left'
					}

					velX = Math.abs($content.scrollLeft() - prevScrollLeft); // Compare change in position to work out drag speed
				},

				'mousedown': function(e) {
					e.preventDefault()
					clicked = true
					scrollX = $content.scrollLeft()
					clickX = e.pageX
					$content.css('cursor', 'grabbing')
				},

				'mouseup': function(e) {
					e.preventDefault()
					self = this
					clicked = false
					cancelAnimationFrame(momentumID)
					momentumID = requestAnimationFrame(momentumLoop)
					$content.css('cursor', '')
				},

				'mouseleave': function(e) {
					clicked = false
					$content.css('cursor', '')
				}
			})

			$(window).on('resize', function() {

				if ($content.prop('scrollWidth') > ($self.outerWidth() + 20)) {

					if (!$self.find('button[name="left"], button[name="right"]').length) {

						$self.append(
							'<button name="left" class="btn btn-default" type="button"><i class="icon-chevron-left"></i></button>' +
							'<button name="right" class="btn btn-default" type="button"><i class="icon-chevron-right"></i></button>'
						)

						$self.on('click', 'button[name="left"], button[name="right"]', function(e) {
							if (direction != $(this).attr('name')) {
								velX = 0
							}
							cancelAnimationFrame(momentumID)
							velX += Math.round($self.outerWidth() * 0.03)
							direction = $(this).attr('name')
							momentumID = requestAnimationFrame(momentumLoop)

						})
					}

				} else {
					$self.find('button[name="left"], button[name="right"]').remove()
				}

				/*
				if ($(window).width() > ($self.outerWidth() + 45)) {
					$self.find('button[name="left"]').css('left', '')
					$self.find('button[name="right"]').css('right', '')
				} else {
					$self.find('button[name="left"]').css('left', 0)
					$self.find('button[name="right"]').css('right', 0)
				}
				*/

			}).trigger('resize')
		})
	}

	$('[data-toggle*="momentumScroll"]').momentumScroll()

}(jQuery)


// Alerts

$('body').on('click', '.alert .close', function(e) {
	e.preventDefault()
	$(this).closest('.alert').fadeOut('fast', function() {
		$(this).remove()
	})
})


// Off-Canvas Sidebar (data-toggle="offcanvas-collapse")
$('[data-toggle="offcanvas"]').on('click', function() {
	$(this).closest('.navbar').toggleClass('expanded')
	$('body').toggleClass('offcanvas-open', $(this).closest('.navbar').hasClass('expanded'))
	$('body').css('overflow', $(this).closest('.navbar').hasClass('expanded') ? 'hidden' : '')
})


// Password Strength

$('form').on('input', 'input[type="password"][data-toggle="password-strength"]', function() {

	$(this).siblings('meter').remove()

	if ($(this).val() == '') return

	let numbers = ($(this).val().match(/[0-9]/g) || []).length,
		lowercases = ($(this).val().match(/[a-z]/g) || []).length,
		uppercases = ($(this).val().match(/[A-Z]/g) || []).length,
		symbols =   ($(this).val().match(/[^\w]/g) || []).length,

		score = (numbers * 9) + (lowercases * 11.25) + (uppercases * 11.25) + (symbols * 15)
					+ (numbers ? 10 : 0) + (lowercases ? 10 : 0) + (uppercases ? 10 : 0) + (symbols ? 10 : 0)

	let meter = $('<meter min="0" low="80" high="120" optimum="150" max="150" value="'+ score +'"></meter>').css({
		position: 'absolute',
		bottom: '-1em',
		width: '100%',
		height: '1em'
	})

	$(this).after(meter)
})


// jQuery Placeholders by LiteCart

+function($) {

	let Placeholders = []

	$.fn.Placeholder = function(options){
		this.each(function() {

			this.$element = $(this)

			this.settings = $.extend({
				aspectRatio: "1:1",
			}, options, this.$element.data())

			this.refresh = function(){
				let width = this.$element.width(),
					height = width / this.settings.aspectRatio.replace(/^([0-9]*):[0-9]*$/, '$1') * this.settings.aspectRatio.replace(/^[0-9]*:([0-9]*)$/, '$1')

				width = Math.round(width)
				height = Math.round(height)

				this.$element.text(width + '\u00d7' + height + ' (' +  this.settings.aspectRatio + ')')
					.css('font-size', Math.round(height/10) + 'px')
					.width('100%')
					.height(height)
			}

			this.refresh()

			Placeholders.push(this)
		})
	}

	$('.placeholder').Placeholder()

	$(window).on('resize', function() {
		$.each(Placeholders, function(i, placeholder) {
			placeholder.refresh()
		})
	})

}(jQuery)


// Number Formatting

Number.prototype.toText = function(decimals = 0) {
	var n = this,
		c = decimals,
		d = '.',
		t = ',',
		s = n < 0 ? '-' : '',
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
		f = n - i,
		j = (j = i.length) > 3 ? j % 3 : 0

	return s + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + ((c && f) ? d + Math.abs(f).toFixed(c).slice(2) : '')
}

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
		j = (j = i.length) > 3 ? j % 3 : 0

	return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x
}


// Scroll Up

$(window).scroll(function() {
	if ($(this).scrollTop() > 300) {
		$('#scroll-up').fadeIn()
	} else {
		$('#scroll-up').fadeOut()
	}
})

$('#scroll-up').on('click', function() {
	$('html, body').animate({scrollTop: 0}, 1000, 'easeOutBounce')
	return false
})


// Data-Table Toggle Checkboxes
$('body').on('click', '.data-table *[data-toggle="checkbox-toggle"], .data-table .checkbox-toggle', function() {
	$(this).closest('.data-table').find('tbody td:first-child :checkbox').each(function() {
		$(this).prop('checked', !$(this).prop('checked')).trigger('change')
	})
	return false
})

$('body').on('click', '.data-table tbody tr', function(e) {
	if ($(e.target).is('a') || $(e.target).closest('a').length) return
	if ($(e.target).is('.btn, :input, th, .icon-star, .icon-star-o')) return
	$(this).find(':checkbox, :radio').first().trigger('click')
})

// Data-Table Shift Check Multiple Checkboxes
let lastTickedCheckbox = null
$('.data-table td:first-child :checkbox').on('click', function(e) {

	let $chkboxes = $('.data-table td:first-child :checkbox')

	if (!lastTickedCheckbox) {
		lastTickedCheckbox = this
		return
	}

	if (e.shiftKey) {
		let start = $chkboxes.index(this)
		let end = $chkboxes.index(lastTickedCheckbox)
		$chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastTickedCheckbox.checked)
	}

	lastTickedCheckbox = this
})

// Data-Table Sorting (Page Reload)
$('.table-sortable thead th[data-sort]').on('click', function() {
	let params = {}

	window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
		params[key] = value
	})

	params.sort = $(this).data('sort')

	window.location.search = $.param(params)
})


// Tabs (data-toggle="tab")

+function($) {
	'use strict'

	$.fn.Tabs = function(){
		this.each(function() {

			const self = this
			this.$element = $(this)

			this.$element.find('[data-toggle="tab"]').each(function() {
				const $link = $(this)

				$link.on('select', function() {
					self.$element.find('.active').removeClass('active')

					if ($link.hasClass('nav-link')) {
						$link.addClass('active')
					}

					$link.closest('.nav-item').addClass('active')
					$($link.attr('href')).show().siblings().hide()
				})

				$link.on('click', function(e) {
					e.preventDefault()
					history.replaceState(null, null, $link[0].hash)
					$link.trigger('select')
				})
			})

			const activeTab = this.$element.find('.active')
			if (!activeTab.length) {
				this.$element.find('[data-toggle="tab"]').first().trigger('select')
			} else {
				activeTab.trigger('select')
	}
		})
	}

	$('.tabs').Tabs()

	if (document.location.hash && document.location.hash.match(/^#tab-/)) {
		$('[data-toggle="tab"][href="' + document.location.hash +'"]').trigger('select')
	}

	$(document).on('ajaxcomplete', function() {
		$('.tabs').Tabs()
	})

}(jQuery)


// Polyfill for easeOutBounce

$.extend($.easing, {
	easeOutCubic: function (x) {
		return 1 - Math.pow( 1 - x, 3 )
	},
	easeInCubic: function (x) {
		return Math.pow(x, 3)
	},
	easeOutBounce: function (x, t, b, c, d) {
		if ((t/=d) < (1/2.75)) {
			return c*(7.5625*t*t) + b
		} else if (t < (2/2.75)) {
			return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b
		} else if (t < (2.5/2.75)) {
			return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b
		} else {
			return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b
		}
	},
})
