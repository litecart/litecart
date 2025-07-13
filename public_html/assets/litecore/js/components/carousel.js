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