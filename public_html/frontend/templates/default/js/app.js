
// JavaScript Loader
  function require_js(url, callback, fallback) {

    var script = document.createElement('script');

    script.src = url;
    script.async = true;
    script.defer = true;

    if (callback) {
      script.onload = function(){ callback(); };
    }

    if (fallback) {
      script.onerror = function() { fallback(); };
    }

    document.body.appendChild(script);
  }

// Toggle Cart
  $('[data-toggle="cart"]').click(function(e){
    e.preventDefault();
    console.log('yes');
    if ($('#shopping-cart').is(':hidden')) {
      $('body').addClass('cart-visible');
    } else {
      $('body').removeClass('cart-visible');
    }
  });

// Alerts
  $('body').on('click', '.alert .close', function(e){
    e.preventDefault();
    $(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
  });

// Form required asterix
  $(':input[required="required"]').closest('.form-group').addClass('required');

// Sidebar parallax effect
  if (typeof(window._env) !== 'undefined' && window._env.template.settings.sidebar_parallax_effect == true) {

    var column = $('#sidebar > *:first-child'), sidebar = $('#sidebar');
    var sidebar_max_offset = $(sidebar).outerHeight(true) - $(column).height() - 20; // 20 = failsafe

    $(window).bind('resize scroll', function(e){
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

// Add to cart animation
  $('body').on('submit', 'form[name="buy_now_form"]', function(e) {
    e.preventDefault();
    var form = $(this);
    $(this).find('button[name="add_cart_product"]').animate_from_to('#site-menu .cart', {
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

// Bootstrap Compatible (data-toggle="tab")
  $('body').on('click', '[data-toggle="tab"]', function(e) {
    e.preventDefault();
    $(this).closest('ul').find('li').removeClass('active');
    $(this).closest('li').addClass('active');
    $($(this).attr('href')).show().siblings().hide();
  });

  $('.nav-tabs').each(function(){
    if (!$(this).find('li.active').length) {
      $(this).find('li:first').addClass('active');
    }
  });

  $('.nav-tabs .active a').trigger('click');
  if (document.location.hash != '') {
    $('a[href="' + document.location.hash + '"]').click();
  }

// Bootstrap Compatible (data-toggle="buttons")
  $('body').on('click', '[data-toggle="buttons"] input[type="checkbox"]', function(){
    if ($(this).is(':checked')) {
      $(this).closest('.btn').addClass('active');
    } else {
      $(this).closest('.btn').removeClass('active');
    }
  });

  $('body').on('click', '[data-toggle="buttons"] input[type="radio"]', function(){
    $(this).closest('.btn').addClass('active').siblings().removeClass('active');
  });

// Data-Table Toggle Checkboxes
  $('body').on('click', '.data-table *[data-toggle="checkbox-toggle"]', function() {
    $(this).closest('.data-table').find('tbody :checkbox').each(function() {
      $(this).prop('checked', !$(this).prop('checked'));
    });
    return false;
  });

  $('.data-table tbody tr').click(function(e) {
    if ($(e.target).is(':input')) return;
    if ($(e.target).is('a, a *')) return;
    if ($(e.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });

// Scroll Up
  $(window).scroll(function(){
    if ($(this).scrollTop() > 100) {
      $('#scroll-up').fadeIn();
    } else {
      $('#scroll-up').fadeOut();
    }
  });

  $('#scroll-up').click(function(){
    $('html, body').animate({scrollTop: 0}, 1000, 'swing');
    return false;
  });

// Update cart / Keep alive
  if (typeof(window._env) !== 'undefined') {
    window.updateCart = function(data) {
      if (data) $('*').css('cursor', 'wait');
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
          console.error('Error while updating cart');
          console.debug(jqXHR.responseText);
        },
        success: function(json) {
          if (json['alert']) alert(json['alert']);
          $('#cart .items').html('');
          if (json['items']) {
            $.each(json['items'], function(i, item){
              $('#cart .items').append('<li><a href="'+ item.link +'">'+ item.quantity +' x '+ item.name +' - '+ item.formatted_price +'</a></li>');
            });
            $('#cart .items').append('<li class="divider"></li>');
          }
          $('#cart .items').append('<li><a href="' + window._env.platform.url + 'checkout"><i class="fa fa-shopping-cart"></i> ' + json['text_total'] + ': <span class="formatted-value">'+ json['formatted_value'] +'</a></li>');
          $('#cart .quantity').html(json['quantity'] ? json['quantity'] : '');
          $('#cart .formatted_value').html(json['formatted_value']);
          if (json['quantity'] > 0) {
            $('#cart img').attr('src', window._env.template.url + 'images/cart_filled.svg');
          } else {
            $('#cart img').attr('src', window._env.template.url + 'images/cart.svg');
          }
        },
        complete: function() {
          if (data) $('*').css('cursor', '');
        }
      });
    }

    var timerCart = setInterval("updateCart()", 60000); // Keeps session alive
  }
*/

/*
 * jQuery Momentum Scroll
 * by LiteCart
 */

+function($) {

  $.fn.momentumScroll = function(){
    this.each(function(){

      this.$element = $(this);
      this.clicked = null;
      this.dragging = false;
      this.clickX = 0;
      this.scrollX = 0;
      this.velX = 0;
      this.momentumID = null;

      this.$element.on({

        'click': function(e) {
          if (this.dragging) {
            e.preventDefault();
          }
          this.dragging = false;
        },

        'mousemove': function(e) {
          if (!this.clicked) return;
          this.dragging = true;
          var prevScrollLeft = this.$element.scrollLeft(); // Store the previous scroll position
          this.$element.scrollLeft(this.scrollX + (this.clickX - e.pageX));
          this.velX = this.$element.scrollLeft() - prevScrollLeft; // Compare change in position to work out drag speed
        },

        'mousedown': function(e) {
          e.preventDefault();
          this.clicked = true;
          this.scrollX = this.$element.scrollLeft();
          this.clickX = e.pageX;
          this.$element.css('cursor', 'grabbing');
        },

        'mouseup': function(e) {
          e.preventDefault();
          self = this;
          this.clicked = false;
          var momentumLoop = function() {
            self.$element.scrollLeft( self.$element.scrollLeft() + self.velX ); // Apply the velocity to the scroll position
            self.velX *= 0.90; // Slow the velocity slightly
            if (Math.abs(self.velX) > 0.5){ // Still moving?
              self.momentumID = requestAnimationFrame(momentumLoop); // Keep looping
            }
          }
          cancelAnimationFrame(self.momentumID);
          self.momentumID = requestAnimationFrame(momentumLoop);
          self.$element.css('cursor', 'grab');
        },

        'mouseleave': function(e) {
          this.clicked = false;
          this.$element.css('cursor', 'grab');
        }

      });
    });
  }

  $('[data-toggle*="momentumScroll"]').momentumScroll();

}(jQuery);

/*
 * jQuery Auto Scroll
 * by LiteCart
 */

+function($) {

  $.fn.autoScroll = function(){
    this.each(function(){

      this.$element = $(this);
      this.scrollInterval = null;
      this.scrollDirection = 'right';

      this.$element.on({

        'mouseenter': function(e) {
          this.$element.trigger('scrollStop');
        },

        'mouseleave': function(e) {
          if (!this.scrollInterval) {
            this.$element.trigger('scrollStart');
          }
        },

        'scrollStart': function(){
          var self = this;
          self.scrollInterval = setInterval(function() {
            if (self.scrollDirection != 'left') {
              self.$element.scrollLeft(self.$element.scrollLeft() + 1);
              if ((self.$element.scrollLeft() + self.$element.width()) >= self.$element[0].scrollWidth) {
                self.scrollDirection = 'left';
              }
            } else {
              self.$element.scrollLeft(self.$element.scrollLeft() - 1);
              if (self.$element.scrollLeft() == 0) {
                self.scrollDirection = 'right';
              }
            }
          }, 100);
        },

        'scrollStop': function() {
          clearInterval(this.scrollInterval);
          this.scrollInterval = null;
        }

      }).trigger('scrollStart');

    });
  }

  $('[data-toggle*="autoScroll"]').autoScroll();

}(jQuery);

/*
 * jQuery Placeholders
 * by LiteCart
 */

+function($) {

  var Placeholders = [];

  $.fn.Placeholder = function(options){
    this.each(function(){

      this.$element = $(this);

      this.settings = $.extend({
        aspectRatio: "1:1",
      }, options, this.$element.data());

      this.refresh = function(){
        var width = this.$element.width(),
          height = width / this.settings.aspectRatio.replace(/^([0-9]*):[0-9]*$/, '$1') * this.settings.aspectRatio.replace(/^[0-9]*:([0-9]*)$/, '$1');

        width = Math.round(width);
        height = Math.round(height);

        this.$element.text(width + '\u00d7' + height + ' (' +  this.settings.aspectRatio + ')')
          .css('font-size', Math.round(height/10) + 'px')
          .width('100%')
          .height(height);
      }

      this.refresh();

      Placeholders.push(this);
    });
  }

  $('.placeholder').Placeholder();

  $(window).on('resize', function(){
    $.each(Placeholders, function(i, placeholder) {
      placeholder.refresh();
    });
  });
}(jQuery);

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
        //target = $(targetElm).is(':visible').eq(0);
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
        "image": '|$|'
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

    var shadowImage = '';
    if (options.initial_css.image != '') {
      shadowImage = '<img src="' + options.initial_css.image + '" style="width: 100%; height: 100%" />';
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

/* ========================================================================
 * Bootstrap: carousel.js v3.3.7
 * http://getbootstrap.com/javascript/#carousel
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+function ($) {
  'use strict';

  // CAROUSEL CLASS DEFINITION
  // =========================

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

  Carousel.VERSION  = '3.3.7'

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
      $next[0].offsetWidth // force reflow
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
  // ==========================

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
  // ====================

  $.fn.carousel.noConflict = function () {
    $.fn.carousel = old
    return this
  }

  // CAROUSEL DATA-API
  // =================

  var clickHandler = function (e) {
    var href
    var $this   = $(this)
    var $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) // strip for ie7
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

/* ========================================================================
 * Bootstrap: dropdown.js v3.3.7
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+function ($) {
  'use strict';

  // DROPDOWN CLASS DEFINITION
  // =========================

  var backdrop = '.dropdown-backdrop'
  var toggle   = '[data-toggle="dropdown"]'
  var Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle)
  }

  Dropdown.VERSION = '3.3.7'

  function getParent($this) {
    var selector = $this.attr('data-target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
    }

    var $parent = selector && $(selector)

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
          .insertAfter($(this))
          .on('click', clearMenus)
      }

      var relatedTarget = { relatedTarget: this }
      $parent.trigger(e = $.Event('show.bs.dropdown', relatedTarget))

      if (e.isDefaultPrevented()) return

      $this.trigger('focus')

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
  // ==========================

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
  // ====================

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old
    return this
  }

  // APPLY TO STANDARD DROPDOWN ELEMENTS
  // ===================================

  $(document)
    .on('click.bs.dropdown.data-api', clearMenus)
    .on('click.bs.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
    .on('click.bs.dropdown.data-api', toggle, Dropdown.prototype.toggle)
    .on('keydown.bs.dropdown.data-api', toggle, Dropdown.prototype.keydown)
    .on('keydown.bs.dropdown.data-api', '.dropdown-menu', Dropdown.prototype.keydown)

}(jQuery);
