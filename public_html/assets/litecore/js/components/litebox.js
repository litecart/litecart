+waitFor('jQuery', ($) => {
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