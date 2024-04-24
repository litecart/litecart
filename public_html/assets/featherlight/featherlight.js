/**
 * Featherlight - ultra slim jQuery lightbox
 * Version 1.7.14 - http://noelboss.github.io/featherlight/
 *
 * Copyright 2019, Noël Raoul Bossart (http://www.noelboss.com)
 * MIT Licensed.
**/

(function($) {
	'use strict';

	if (typeof $ === 'undefined') {
		if ('console' in window) {
			window.console.error('Featherlight requires jQuery.');
		}
		return;
	}

	if ($.fn.jquery.match(/-ajax/)) {
		if ('console' in window) {
			window.console.info('Featherlight needs regular jQuery, not the slim version.');
		}
		return;
	}

	function Featherlight($modal, config) {
		if (this instanceof Featherlight) {  // called with new
			this.id = Featherlight.id++;
			this.setup($modal, config);
			this.chainCallbacks(Featherlight._callbackChain);
		} else {
			var fl = new Featherlight($modal, config);
			fl.open();
			return fl;
		}
	}

	var opened = [],
		pruneOpened = function(remove) {
			opened = $.grep(opened, function(fl) {
				return fl !== remove && fl.$instance.closest('body').length > 0;
			});
			return opened;
		};

	// Document wide key handler
	var toggleGlobalEvents = function(newState) {
		if (Featherlight._globalHandlerInstalled !== newState) {
			Featherlight._globalHandlerInstalled = newState;

			var eventMap = {keyup: 'onKeyUp', resize: 'onResize'};
			var events = $.map(eventMap, function(_, name) { return name+'.featherlight'; } ).join(' ');

			$(window)[newState ? 'on' : 'off'](events, function(e) {
				$.each(Featherlight.opened().reverse(), function() {
					if (!e.isDefaultPrevented()) {
						if (this[eventMap[e.type]](e) === false) {
							e.preventDefault(); e.stopPropagation(); return false;
						}
					}
				});
			});
		}
	}

	Featherlight.prototype = {
		constructor: Featherlight,

		/*** Defaults ***/

		// Extend featherlight with defaults and methods
		autoBind:       '[data-toggle="featherlight"]', // Will automatically bind elements matching this selector. Clear or set before onReady

		targetAttr:     'data-target',         // Attribute of the triggered element that contains the selector to the modal
		openTrigger:    'click',               // Event that triggers the lightbox
		filter:         null,                  // Selector to filter events. Think $(...).on('click', filter, eventHandler)
		closeOnClick:   'backdrop',            // Close lightbox on click ('backdrop', 'anywhere' or false)
		closeOnEsc:     true,                  // Close lightbox when pressing esc
		loading:        '<div class="featherlight-loader"></div>', // Content to show while initial modal is loading
		persist:        false,                 // If set, the modal will persist and will be shown again when opened again. 'shared' is a special value when binding multiple elements for them to share the same modal
		beforeOpen:     $.noop,                // Called before open. can return false to prevent opening of lightbox. Gets event as parameter, this contains all data
		beforeContent:  $.noop,                // Called when modal is loaded. Gets event as parameter, this contains all data
		beforeClose:    $.noop,                // Called before close. can return false to prevent closing the lightbox. Gets event as parameter, this contains all data
		afterOpen:      $.noop,                // Called after open. Gets event as parameter, this contains all data
		afterContent:   $.noop,                // Called after modal is ready and has been set. Gets event as parameter, this contains all data
		afterClose:     $.noop,                // Called after close. Gets event as parameter, this contains all data
		onKeyUp:        $.noop,                // Called on key up for the frontmost featherlight
		onResize:       $.noop,                // Called after new modal and when a window is resized
		type:           null,                  // Specify type of lightbox. If unset, it will check for the data-type attribute value or try to identify from contentFilters.
		closeIcon:      '&#x2716;',            // Close icon
		contentFilters: ['jquery', 'image', 'html', 'ajax', 'iframe', 'text'], /* List of content filters to use to determine the modal */
		seamless:       false,                 // Enable or disable seamless mode.
		width:          '',                    // Specify width of lightbox.
		height:         '',                    // Specify width of lightbox.
		maxWidth:       '',                    // Specify max-width of lightbox.
		maxHeight:      '',                    // Specify max-height of lightbox.
		requireWindowWidth: null,              // Minimum scren width in pixels to enable the Featherlight. Otherwise bypass it.  */

		/*** Methods ***/
		// Setup iterates over a single instance of featherlight and prepares the backdrop and binds the events
		setup: function(target, config){

			// Make all arguments optional
			if (typeof target === 'object' && target instanceof $ === false && !config) {
				config = target;
				target = undefined;
			}

			var self = $.extend(this, config, {target: target});

			self.$instance = $([
				'<div class="featherlight featherlight-loading">',
        '  <div class="featherlight-modal'+ (self.seamless ? ' featherlight-seamless' : '') +'">',
				'    <div class="featherlight-inner">' + self.loading + '</div>',
				'  </div>',
				'</div>'
			].join('\n'));

			// Close when click on backdrop/anywhere/null or closebox
			self.$instance.on('click.featherlight', function(e) {

				if (e.isDefaultPrevented()) {
					return;
				}

				switch (true) {
					case (self.closeOnClick === 'backdrop' && $(e.target).is('.featherlight')):
					case (self.closeOnClick === 'anywhere'):
					case ($(e.target).is('.featherlight-close')):
						self.close(e);
						e.preventDefault();
						break;
				}
			});

			return this;
		},

		// This method prepares the modal and converts it into a jQuery object or a promise
		getContent: function(){

			if (this.persist !== false && this.$modal) {
				return this.$modal;
			}

			var self = this,
				filters = this.constructor.contentFilters,
				readTargetAttr = function(name){ return self.$currentTarget && self.$currentTarget.attr(name); },
				targetValue = readTargetAttr(self.targetAttr),
				data = self.target || targetValue || '';

			// Find which filter applies
			var filter = filters[self.type]; // check explicit type like {type: 'image'}

			// Check explicit type like data-target="image"
			if (!filter && data in filters) {
				filter = filters[data];
				data = self.target && targetValue;
			}
			data = data || readTargetAttr('href') || '';

			// Check explicity type & content like {image: 'photo.jpg'}
			if (!filter) {
				for (var filterName in filters) {
					if (self[filterName]) {
						filter = filters[filterName];
						data = self[filterName];
					}
				}
			}

			// Otherwise it's implicit, run checks
			if (!filter) {
				var target = data;
				data = null;

				$.each(self.contentFilters, function() {
					filter = filters[this];
					if (filter.test) {
						data = filter.test(target);
					}
					if (!data && filter.regex && target.match && target.match(filter.regex)) {
						data = target;
					}
					return !data;
				});

				if (!data) {
					if ('console' in window) {
						window.console.error('Featherlight: No content filter found ' + (target ? ' for "' + target + '"' : ' (no target specified)'));
					}
					return false;
				}
			}

			// Process it
			return filter.process.call(self, data);
		},

		// sets the content of $instance to $modal
		setContent: function($modal){
			var self = this;

			self.$instance.removeClass('featherlight-loading');

			self.$modal = $modal.show();
			self.$instance.find('.featherlight-modal').html(self.$modal);

			if (self.closeIcon) {
				self.$instance.find('.featherlight-modal').prepend([
					'<div class="featherlight-close-icon featherlight-close">',
				  '  ' + self.closeIcon,
					'</div>',
        ].join('\n'));
			}

			return self;
		},

		/* opens the lightbox. "this" contains $instance with the lightbox, and with the config.
			Returns a promise that is resolved after is successfully opened. */
		open: function(e){
			var self = this;

			if (e && (e.ctrlKey || e.shiftKey)) {
				return false;
			}

			if (self.requireWindowWidth && self.requireWindowWidth > $(window).width()) {
				return false;
			}

			self.$instance.hide().appendTo('body');

			if ((!e || !e.isDefaultPrevented()) && self.beforeOpen(e) !== false) {

        if (e) e.preventDefault();

				$('body').addClass('featherlight-open');

				$('.featherlight').removeClass('active');
				self.$instance.addClass('active');

				var $modal = self.getContent();

				if ($modal) {
					opened.push(self);

					toggleGlobalEvents(true);

					self.$instance.show();
					self.beforeContent(e);

					// Set modal and show
					return $.when($modal)
						.always(function($modal){
							self.setContent($modal);
							if (self.width) {
								self.$modal.parent().css('width', self.width);
							}
							if (self.height) {
								self.$modal.parent().css('height', self.height);
							}
							if (self.maxWidth) {
								self.$modal.parent().css('max-width', self.maxWidth);
							}
							if (self.maxHeight) {
								self.$modal.parent().css('max-height', self.maxHeight);
							}
							self.afterContent(e);
						})
						.then(self.$instance.promise())
						// Call afterOpen after show() is done
						.done(function(){ self.afterOpen(e); });
				}
			}
			self.$instance.detach();
			return $.Deferred().reject().promise();
		},

		/* closes the lightbox. "this" contains $instance with the lightbox, and with the config
			returns a promise, resolved after the lightbox is successfully closed. */
		close: function(e){
			var self = this,
				deferred = $.Deferred();

			if (self.beforeClose(e) === false) {
				deferred.reject();
			} else {

				if (pruneOpened(self).length === 0) {
					toggleGlobalEvents(false);
				}

				self.$instance.hide().detach();
				self.afterClose(e);
				deferred.resolve();

				$('.featherlight:not(.active)').filter(':last').addClass('active');

				if ($('.featherlight').length === 0) {
					$('body').removeClass('featherlight-open');
				}
			}
			return deferred.promise();
		},

		/* [Warning: guru-level] Utility function to chain callbacks
		 * Used be extensions that want to let users specify callbacks but also need themselves to use the callbacks.
		 * The argument 'chain' has callback names as keys and function(super, event) as values. That function is meant to call `super` at some point.
		*/
		chainCallbacks: function(chain) {
			for (var name in chain) {
				this[name] = $.proxy(chain[name], this, $.proxy(this[name], this));
			}
		}
	};

	$.extend(Featherlight, {
		id: 0, // Used to id single featherlight instances
		defaults: Featherlight.prototype, // You can access and override all defaults using $.featherlight.defaults, which is just a synonym for $.featherlight.prototype
		contentFilters: { // Contains the logic to determine content

			jquery: {
				regex: /^[#.]\w/,         // Anything that starts with a class name or identifiers
				test: function(element)    { return element instanceof $ && element; },
				process: function(element) { return this.persist !== false ? $(element) : $(element).clone(true); }
			},

			image: {
				regex: /\.(a?png|avif|bmp|gif|ico|jpe?g|jp2|svg|tiff?|webp)(\?\S*)?$/i,
				process: function(url) {

					var self = this,
            deferred = $.Deferred();

					var $img = $('<img>', {
            src: url,
            alt: '',
            complete: function(){
              deferred.resolve($(this));
            }
          });

					return deferred.promise();
				}
			},

			html: {
				regex: /^\s*<[\w!][^<]*>/,  // Anything that starts with some kind of valid tag
				process: function(html) {
          return $(html);
        }
			},

			ajax: {
				regex: /./,  // At this point, any content is assumed to be an URL
				process: function(url)  {

					var self = this,
						deferred = $.Deferred();

					// we are using load so one can specify a target with: url.html #targetelement
					var $container = $('<div></div>').load(url.replace('#', ' #'), function(response, status){
						if (status !== 'error') {
							deferred.resolve($container.contents());
						}
						deferred.reject();
					});

					return deferred.promise();
				}
			},

			iframe: {
				process: function(url) {

					var self = this,
            deferred = new $.Deferred();

					var $iframe = $('<iframe/>', {
            src: url,
						complete, function(){
              // We can't move an <iframe> and avoid reloading it, so let's put it in place ourselves right now:
              $iframe.show().appendTo(self.$instance.find('.featherlight-modal'));
              deferred.resolve($iframe);
            }
          }).hide();

					return deferred.promise();
				}
			},

			text: {
				process: function(text) {
          return $('<div>', {text: text});
        }
			}
		},

		/*** Class Methods ***/

		// Read element's data attributes
		readElementConfig: function(element) {

			if (!element) return;

			var config = $(element).data(),
				functionAttributes = ['beforeOpen', 'afterOpen', 'beforeContent', 'afterContent', 'beforeClose', 'afterClose'];

			$.each(functionAttributes, function(i, e){
				if (config[e] !== undefined) {
					config[e] = new Function(config[e]);
				}
			});

			return config;
		},

		attach: function($source, $modal, config) {

			var self = this;

			if (typeof $modal === 'object' && $modal instanceof $ === false && !config) {
				config = $modal;
				$modal = undefined;
			}

			// Make a copy
			config = $.extend({}, config);

			/* Only for openTrigger and filter ... */
			var tempConfig = $.extend({}, self.defaults, self.readElementConfig($source[0]), config),
				sharedPersist;

			var handler = function(e) {

				var $target = $(e.currentTarget);

				// ... since we might as well compute the config on the actual target
				var elementConfig = $.extend(
					{$source: $source, $currentTarget: $target},
					self.readElementConfig($source[0]),
					self.readElementConfig(this),
					config);

				var fl = sharedPersist || $target.data('featherlight-persisted') || new self($modal, elementConfig);

				if (fl.persist === 'shared') {
					sharedPersist = fl;
				} else if (fl.persist !== false) {
					$target.data('featherlight-persisted', fl);
				}

				if (elementConfig.$currentTarget.blur) {
					elementConfig.$currentTarget.blur(); // Otherwise 'enter' key might trigger the dialog again
				}

				fl.open(e);
			};

			$source.on(tempConfig.openTrigger+'.featherlight', tempConfig.filter, handler);

			return {filter: tempConfig.filter, handler: handler};
		},

		current: function() {
			var all = this.opened();
			return all[all.length - 1] || null;
		},

		opened: function() {
			var self = this;
			pruneOpened();
			return $.grep(opened, function(fl) { return fl instanceof self; } );
		},

		close: function(e) {
			var cur = this.current();
			if (cur) { return cur.close(e); }
		},

		// Featherlight uses the onKeyUp callback to intercept the escape key. Private to Featherlight.
		_callbackChain: {
			onKeyUp: function(_super, e){

				switch (e.keyCode) {
					case 27:
					if (this.closeOnEsc) {
						$.featherlight.close(e);
					}
					return false;
				}

				return _super(e);
			},

			onResize: function(_super, e){
				return _super(e);
			},

			beforeOpen: function(_super, e) {

				// Remember focus:
				this._previouslyActive = document.activeElement;

				// Disable tabbing:
				// See http://stackoverflow.com/questions/1599660/which-html-elements-can-receive-focus
				this._$previouslyTabbable = $('a, input, select, textarea, iframe, button, iframe, [contentEditable=true]')
					.not('[tabindex]')
					.not(this.$instance.find('button'));

				this._$previouslyWithTabIndex = $('[tabindex]').not('[tabindex="-1"]');
				this._previousWithTabIndices = this._$previouslyWithTabIndex.map(function(_i, element) {
					return $(element).attr('tabindex');
				});

				this._$previouslyWithTabIndex.add(this._$previouslyTabbable).attr('tabindex', -1);

				if (document.activeElement.blur) {
					document.activeElement.blur();
				}

				return _super(e);
			},

			afterContent: function(_super, e){
				this.$instance.find('[autofocus]:not([disabled])').focus();
				this.onResize(e);
				return _super(e);
			},

			afterClose: function(_super, e) {

				var self = this;

				// Restore focus
				this._$previouslyTabbable.removeAttr('tabindex');
				this._$previouslyWithTabIndex.each(function(i, element) {
					$(element).attr('tabindex', self._previousWithTabIndices[i]);
				});

				this._previouslyActive.focus();

				this.$instance.off('next.featherlight previous.featherlight');

				return _super(e);
			}
		}
	});

	$.featherlight = Featherlight;

	// Bind jQuery elements to trigger featherlight
	$.fn.featherlight = function($modal, config) {
		Featherlight.attach(this, $modal, config);
		return this;
	};

	// Bind featherlight on ready if config autoBind is set
	$(document).ready(function(){

		// Do auto binding on startup. Meant only to be used by Featherlight and its extensions
    if (Featherlight.autoBind){

      var $autobound = $(Featherlight.autoBind);

      // Bind existing elements
      $autobound.each(function(){
        Featherlight.attach($(this));
      });

      // If a click propagates to the document level, then we have an item that was added later on
      $(document).on('click', Featherlight.autoBind, function(e) {

        if (e.isDefaultPrevented()) {
          return;
        }

        var $cur = $(e.currentTarget);
        var len = $autobound.length;
        $autobound = $autobound.add($cur);
        if (len === $autobound.length) {
          return; // already bound
        }

        // Bind featherlight
        var data = Featherlight.attach($cur);

        // Dispatch event directly
        if (!data.filter || $(e.target).parentsUntil($cur, data.filter).length > 0) {
          data.handler(e);
        }
      });
    }
  });

}(jQuery));
