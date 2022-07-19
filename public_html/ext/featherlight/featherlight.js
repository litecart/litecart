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

	/* Featherlight is exported as $.featherlight.
	   It is a function used to open a featherlight lightbox.

	   [tech]
	   Featherlight uses prototype inheritance.
	   Each opened lightbox will have a corresponding object.
	   That object may have some attributes that override the
	   prototype's.
	   Extensions created with Featherlight.extend will have their
	   own prototype that inherits from Featherlight's prototype,
	   thus attributes can be overriden either at the object level,
	   or at the extension level.
	   To create callbacks that chain themselves instead of overriding,
	   use chainCallbacks.
	   For those familiar with CoffeeScript, this correspond to
	   Featherlight being a class and the Gallery being a class
	   extending Featherlight.
	   The chainCallbacks is used since we don't have access to
	   CoffeeScript's `super`.
	*/

	function Featherlight($content, config) {
		if (this instanceof Featherlight) {  /* called with new */
			this.id = Featherlight.id++;
			this.setup($content, config);
			this.chainCallbacks(Featherlight._callbackChain);
		} else {
			var fl = new Featherlight($content, config);
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

	/* document wide key handler */
	var toggleGlobalEvents = function(newState) {
		if (Featherlight._globalHandlerInstalled !== newState) {
			Featherlight._globalHandlerInstalled = newState;

			var eventMap = {keyup: 'onKeyUp', resize: 'onResize'};
			var events = $.map(eventMap, function(_, name) { return name+'.'+Featherlight.prototype.namespace; } ).join(' ');

			$(window)[newState ? 'on' : 'off'](events, function(event) {
				$.each(Featherlight.opened().reverse(), function() {
					if (!event.isDefaultPrevented()) {
						if (this[eventMap[event.type]](event) === false) {
							event.preventDefault(); event.stopPropagation(); return false;
						}
					}
				});
			});
		}
	}

	Featherlight.prototype = {
		constructor: Featherlight,
		/*** defaults ***/
		/* extend featherlight with defaults and methods */
		autoBind:       '[data-toggle="featherlight"]', /* Will automatically bind elements matching this selector. Clear or set before onReady */

		namespace:      'featherlight',        /* Name of the events and css class prefix */
		targetAttr:     'data-target',         /* Attribute of the triggered element that contains the selector to the lightbox content */
		openTrigger:    'click',               /* Event that triggers the lightbox */
		filter:         null,                  /* Selector to filter events. Think $(...).on('click', filter, eventHandler) */
		closeOnClick:   'backdrop',            /* Close lightbox on click ('backdrop', 'anywhere' or false) */
		closeOnEsc:     true,                  /* Close lightbox when pressing esc */
		loading:        '<div class="featherlight-loader"></div>', /* Content to show while initial content is loading */
		persist:        false,                 /* If set, the content will persist and will be shown again when opened again. 'shared' is a special value when binding multiple elements for them to share the same content */
		otherClose:     null,                  /* Selector for alternate close buttons (e.g. "a.close") */
		beforeOpen:     $.noop,                /* Called before open. can return false to prevent opening of lightbox. Gets event as parameter, this contains all data */
		beforeContent:  $.noop,                /* Called when content is loaded. Gets event as parameter, this contains all data */
		beforeClose:    $.noop,                /* Called before close. can return false to prevent closing of lightbox. Gets event as parameter, this contains all data */
		afterOpen:      $.noop,                /* Called after open. Gets event as parameter, this contains all data */
		afterContent:   $.noop,                /* Called after content is ready and has been set. Gets event as parameter, this contains all data */
		afterClose:     $.noop,                /* Called after close. Gets event as parameter, this contains all data */
		onKeyUp:        $.noop,                /* Called on key up for the frontmost featherlight */
		onResize:       $.noop,                /* Called after new content and when a window is resized */
		type:           null,                  /* Specify type of lightbox. If unset, it will check for the data-type attribute value or try to identify from contentFilters. */
		closeIcon:      '&#x2716;',            /* Close icon */
		contentFilters: ['jquery', 'image', 'html', 'ajax', 'iframe', 'text'], /* List of content filters to use to determine the content */
		width:          '',                    /* Specify width of lightbox. */
		height:         '',                    /* Specify width of lightbox. */
		maxWidth:       '',                    /* Specify max-width of lightbox. */
		maxHeight:      '',                    /* Specify max-height of lightbox. */
		requireWindowWidth: null,              /* Minimum scren width in pixels to enable the Featherlight. Otherwise bypass it.  */

		/*** methods ***/
		/* setup iterates over a single instance of featherlight and prepares the backdrop and binds the events */
		setup: function(target, config){
			/* all arguments are optional */
			if (typeof target === 'object' && target instanceof $ === false && !config) {
				config = target;
				target = undefined;
			}

			var self = $.extend(this, config, {target: target});

			self.$instance = $([
				'<div class="'+self.namespace+' '+self.namespace+'-loading">',
					'<div class="'+self.namespace+'-content">',
						'<div class="'+self.namespace+'-inner">'+self.loading+'</div>',
					'</div>',
				'</div>'
			].join(''));

			/* close when click on backdrop/anywhere/null or closebox */
			self.$instance.on('click.'+self.namespace, function(event) {
				if (event.isDefaultPrevented()) {
					return;
				}
				switch (true) {
					case (self.closeOnClick === 'backdrop' && $(event.target).is('.'+self.namespace)):
					case (self.closeOnClick === 'anywhere'):
					case ($(event.target).is('.'+self.namespace+'-close' + (self.otherClose ? ',' + self.otherClose : ''))):
					self.close(event);
					event.preventDefault();
					break;
				}
			});

			return this;
		},

		/* this method prepares the content and converts it into a jQuery object or a promise */
		getContent: function(){
			if (this.persist !== false && this.$content) {
				return this.$content;
			}

			var self = this,
				filters = this.constructor.contentFilters,
				readTargetAttr = function(name){ return self.$currentTarget && self.$currentTarget.attr(name); },
				targetValue = readTargetAttr(self.targetAttr),
				data = self.target || targetValue || '';

			/* Find which filter applies */
			var filter = filters[self.type]; /* check explicit type like {type: 'image'} */

			/* check explicit type like data-target="image" */
			if (!filter && data in filters) {
				filter = filters[data];
				data = self.target && targetValue;
			}
			data = data || readTargetAttr('href') || '';

			/* check explicity type & content like {image: 'photo.jpg'} */
			if (!filter) {
				for (var filterName in filters) {
					if (self[filterName]) {
						filter = filters[filterName];
						data = self[filterName];
					}
				}
			}

			/* otherwise it's implicit, run checks */
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
			/* Process it */
			return filter.process.call(self, data);
		},

		/* sets the content of $instance to $content */
		setContent: function($content){
			var self = this;

			self.$instance.removeClass(self.namespace+'-loading');

			self.$content = $content.show();
			self.$instance.find('.'+self.namespace+'-content').html(self.$content);

			if (self.closeIcon) {
				self.$instance.find('.'+self.namespace+'-content').prepend(
					'<div class="'+ self.namespace +'-close-icon '+ self.namespace + '-close" aria-label="Close">' +
						self.closeIcon +
					'</div>'
				);
			}

			return self;
		},

		/* opens the lightbox. "this" contains $instance with the lightbox, and with the config.
			Returns a promise that is resolved after is successfully opened. */
		open: function(event){
			var self = this;

			if (event && (event.ctrlKey || event.shiftKey)) {
				return false;
			}

			if (self.requireWindowWidth && self.requireWindowWidth > $(window).width()) {
				return false;
			}

			self.$instance.hide().appendTo('body');

			if ((!event || !event.isDefaultPrevented()) && self.beforeOpen(event) !== false) {

				$('body').addClass('featherlight-open');

				$('.featherlight').removeClass('active');
				self.$instance.addClass('active');

				if (event){
					event.preventDefault();
				}
				var $content = self.getContent();

				if ($content) {
					opened.push(self);

					toggleGlobalEvents(true);

					self.$instance.show();
					self.beforeContent(event);

					/* Set content and show */
					return $.when($content)
						.always(function($content){
							self.setContent($content);
							if (self.width) {
								self.$content.parent().css('width', self.width);
							}
							if (self.height) {
								self.$content.parent().css('height', self.height);
							}
							if (self.maxWidth) {
								self.$content.parent().css('max-width', self.maxWidth);
							}
							if (self.maxHeight) {
								self.$content.parent().css('max-height', self.maxHeight);
							}
							self.afterContent(event);
						})
						.then(self.$instance.promise())
						/* Call afterOpen after show() is done */
						.done(function(){ self.afterOpen(event); });
				}
			}
			self.$instance.detach();
			return $.Deferred().reject().promise();
		},

		/* closes the lightbox. "this" contains $instance with the lightbox, and with the config
			returns a promise, resolved after the lightbox is successfully closed. */
		close: function(event){
			var self = this,
				deferred = $.Deferred();

			if (self.beforeClose(event) === false) {
				deferred.reject();
			} else {

				if (pruneOpened(self).length === 0) {
					toggleGlobalEvents(false);
				}

				self.$instance.hide().detach();
				self.afterClose(event);
				deferred.resolve();

				$('.featherlight:not(.active)').filter(':last').addClass('active');

				if ($('.featherlight').length === 0) {
					$('body').removeClass('featherlight-open');
				}
			}
			return deferred.promise();
		},

		/* Utility function to chain callbacks
		   [Warning: guru-level]
		   Used be extensions that want to let users specify callbacks but
		   also need themselves to use the callbacks.
		   The argument 'chain' has callback names as keys and function(super, event)
		   as values. That function is meant to call `super` at some point.
		*/
		chainCallbacks: function(chain) {
			for (var name in chain) {
				this[name] = $.proxy(chain[name], this, $.proxy(this[name], this));
			}
		}
	};

	$.extend(Featherlight, {
		id: 0,                                      /* Used to id single featherlight instances */
		defaults:       Featherlight.prototype,     /* You can access and override all defaults using $.featherlight.defaults, which is just a synonym for $.featherlight.prototype */
		/* Contains the logic to determine content */
		contentFilters: {

			jquery: {
				regex: /^[#.]\w/,         /* Anything that starts with a class name or identifiers */
				test: function(element)    { return element instanceof $ && element; },
				process: function(element) { return this.persist !== false ? $(element) : $(element).clone(true); }
			},

			image: {
				regex: /\.(a?png|bmp|gif|ico|jpe?g|jp2|svg|tiff?|webp)(\?\S*)?$/i,
				process: function(url) {
					var self = this,
						deferred = $.Deferred(),
						img = new Image(),
						$img = $('<img src="'+url+'" alt="" />');
					img.onload = function() {
						/* Store naturalWidth & height for IE8 */
						$img.naturalWidth = img.width;
						$img.naturalHeight = img.height;
						deferred.resolve( $img );
					}
					img.onerror = function() { deferred.reject($img); };
					img.src = url;
					return deferred.promise();
				}
			},

			html: {
				regex: /^\s*<[\w!][^<]*>/, /* Anything that starts with some kind of valid tag */
				process: function(html) { return $(html); }
			},

			ajax: {
				regex: /./,            /* At this point, any content is assumed to be an URL */
				process: function(url)  {
					var self = this,
						deferred = $.Deferred();
					/* we are using load so one can specify a target with: url.html #targetelement */
					url = url.replace('#', ' #');
					var $container = $('<div></div>').load(url, function(response, status){
						if ( status !== "error" ) {
							deferred.resolve($container.contents());
						}
						deferred.reject();
					});
					return deferred.promise();
				}
			},

			iframe: {
				process: function(url) {
					var deferred = new $.Deferred();
					var $content = $('<iframe/>');
					$content.hide()
						.attr('src', url)
						.on('load', function() { deferred.resolve($content.show()); })
						// We can't move an <iframe> and avoid reloading it,
						// so let's put it in place ourselves right now:
						.appendTo(this.$instance.find('.' + this.namespace + '-content'));
					return deferred.promise();
				}
			},

			text: {
				process: function(text) { return $('<div>', {text: text}); }
			}
		},

		/*** class methods ***/
		/* read element's attributes starting with data- */
		readElementConfig: function(element) {

			if (!element) return;

			var config = $(element).data();

			$.each(this.functionAttributes, function(i, event){
				if (config[event] !== undefined) config[event] = new Function(config[event]);
				});

			return config;
		},

		attach: function($source, $content, config) {
			var self = this;
			if (typeof $content === 'object' && $content instanceof $ === false && !config) {
				config = $content;
				$content = undefined;
			}
			/* make a copy */
			config = $.extend({}, config);

			/* Only for openTrigger, filter & namespace... */
			var namespace = config.namespace || self.defaults.namespace,
				tempConfig = $.extend({}, self.defaults, self.readElementConfig($source[0]), config),
				sharedPersist;

			var handler = function(event) {
				var $target = $(event.currentTarget);
				/* ... since we might as well compute the config on the actual target */
				var elementConfig = $.extend(
					{$source: $source, $currentTarget: $target},
					self.readElementConfig($source[0]),
					self.readElementConfig(this),
					config);
				var fl = sharedPersist || $target.data('featherlight-persisted') || new self($content, elementConfig);
				if (fl.persist === 'shared') {
					sharedPersist = fl;
				} else if (fl.persist !== false) {
					$target.data('featherlight-persisted', fl);
				}
				if (elementConfig.$currentTarget.blur) {
					elementConfig.$currentTarget.blur(); // Otherwise 'enter' key might trigger the dialog again
				}
				fl.open(event);
			};

			$source.on(tempConfig.openTrigger+'.'+tempConfig.namespace, tempConfig.filter, handler);

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

		close: function(event) {
			var cur = this.current();
			if (cur) { return cur.close(event); }
		},

		/* Does the auto binding on startup.
		   Meant only to be used by Featherlight and its extensions
		*/
		_onReady: function() {
			var self = this;
			if (self.autoBind){
				var $autobound = $(self.autoBind);
				/* Bind existing elements */
				$autobound.each(function(){
					self.attach($(this));
				});
				/* If a click propagates to the document level, then we have an item that was added later on */
				$(document).on('click', self.autoBind, function(event) {
					if (event.isDefaultPrevented()) {
						return;
					}
					var $cur = $(event.currentTarget);
					var len = $autobound.length;
					$autobound = $autobound.add($cur);
					if(len === $autobound.length) {
						return; /* already bound */
					}
					/* Bind featherlight */
					var data = self.attach($cur);
					/* Dispatch event directly */
					if (!data.filter || $(event.target).parentsUntil($cur, data.filter).length > 0) {
						data.handler(event);
					}
				});
			}
		},

		/* Featherlight uses the onKeyUp callback to intercept the escape key.
		   Private to Featherlight.
		*/
		_callbackChain: {
			onKeyUp: function(_super, event){

				switch (event.keyCode) {
					case 27:
					if (this.closeOnEsc) {
						$.featherlight.close(event);
					}
					return false;
				}
					return _super(event);
			},

			beforeOpen: function(_super, event) {

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

				return _super(event);
			},

			onResize: function(_super, event){
				return _super(event);
			},

			afterContent: function(_super, event){
				this.$instance.find('[autofocus]:not([disabled])').focus();
				this.onResize(event);
				return _super(event);
			},

			afterClose: function(_super, event) {

				var self = this;

				// Restore focus
				this._$previouslyTabbable.removeAttr('tabindex');
				this._$previouslyWithTabIndex.each(function(i, element) {
					$(element).attr('tabindex', self._previousWithTabIndices[i]);
				});

				this._previouslyActive.focus();
				return _super(event);
			}
		}
	});

	$.featherlight = Featherlight;

	/* bind jQuery elements to trigger featherlight */
	$.fn.featherlight = function($content, config) {
		Featherlight.attach(this, $content, config);
		return this;
	};

	/* bind featherlight on ready if config autoBind is set */
	$(document).ready(function(){ Featherlight._onReady(); });
}(jQuery));
