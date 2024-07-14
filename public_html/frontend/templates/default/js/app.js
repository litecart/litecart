// Stylesheet Loader
  $.loadStylesheet = function(url, options) {

    options = $.extend(options || {}, {
      rel: 'stylesheet',
      href: url
      //onload: callback,
      //onerror: fallback
    });

    $('<link/>', options).appendTo('head');
  }

// JavaScript Loader
  $.loadScript = function(url, options) {

    options = $.extend(options || {}, {
      method: 'GET',
      url: url,
      dataType: 'script',
      cache: true
    });

    return jQuery.ajax(options);
  };

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

// Keep-alive
  let keepAlive = setInterval(function(){
    $.get({
      url: window._env.platform.path + 'ajax/cart.json',
      cache: false
    });
  }, 60e3);

// Form required asterix
  $(':input[required]').closest('.form-group').addClass('required');

// Sidebar parallax effect
  if (typeof(window._env) !== 'undefined' && window._env.template.settings.sidebar_parallax_effect == true) {

    let $sidebar = $('#sidebar');
    //let sidebar_max_offset = $sidebar.parent().height() - $sidebar.height() - 200; // Failsafe 30

    $(window).on('resize scroll', function(e){
      if ($(window).width() >= 768 && ($sidebar.parent().height() - $sidebar.height()) > 500) {
        let offset = $(this).scrollTop() * .6;
        if (offset > 0) $sidebar.css('margin-top', offset + 'px');
      } else {
        $sidebar.css('margin-top', 0);
      }
    }).trigger('resize');
  }


// Off-Canvas Sidebar (data-toggle="offcanvas-collapse")
  $('[data-toggle="offcanvas"]').on('click', function() {
    $(this).closest('.navbar').toggleClass('expanded');
    $('body').toggleClass('offcanvas-open', $(this).closest('.navbar').hasClass('expanded'));
    $('body').css('overflow', $(this).closest('.navbar').hasClass('expanded') ? 'hidden' : '');
  });

// Scroll Up
  $(window).scroll(function(){
    if ($(this).scrollTop() > 300) {
      $('#scroll-up').fadeIn();
    } else {
      $('#scroll-up').fadeOut();
    }
  });

  $('#scroll-up').click(function(){
    $('html, body').animate({scrollTop: 0}, 1000, 'easeOutBounce');
    return false;
  });

// Toggle Buttons (data-toggle="buttons")

  $('body').on('click', '[data-toggle="buttons"] :checkbox', function(){
    if ($(this).is(':checked')) {
      $(this).closest('.btn').addClass('active');
    } else {
      $(this).closest('.btn').removeClass('active');
    }
  });

  $('body').on('click', '[data-toggle="buttons"] :radio', function(){
    $(this).closest('.btn').addClass('active').siblings().removeClass('active');
  });

/* ========================================================================
 * Bootstrap: carousel.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#carousel
 * ========================================================================
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+function ($) {
  'use strict';

  // CAROUSEL CLASS DEFINITION
  // =========================

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
  // ==========================

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
  // ====================

  $.fn.carousel.noConflict = function () {
    $.fn.carousel = old
    return this
  }

  // CAROUSEL DATA-API
  // =================

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

}(jQuery);

// Add to cart animation
  $('body').on('submit', 'form[name="buy_now_form"]', function(e) {
    e.preventDefault();

    let $form = $(this)
      $button = $(this).find('button[type="submit"]'),
      $target = $('#site-navigation .shopping-cart'),
      target_height = $target.innerHeight(),
      target_width = $target.innerWidth(),
      $object = $('<div id="animated-cart-item"></div>');

    updateCart($form.serialize() + '&add_cart_product=true');

    $object.css({
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
    })
    .appendTo('body')
    .animate({
      top: $target.offset().top,
      left: $target.offset().left,
      height: target_height,
      width: target_width,
      borderRadius: 0
    }, {
      duration: '1000ms',
      easing: 'easeOutCubic'
    })
    .animate({
      opacity: 0
    }, {
      duration: 100,
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
  if (typeof(window._env) !== 'undefined') {
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

          let html = '';
          $.each(result.items, function(key, item){
            html += [
              '<li class="item">',
              '  <div class="row">',
              '    <div class="col-3">',
              '      ' + $('<img class="image img-responsive">').attr('src', item.thumbnail).attr('alt', item.name).prop('outerHTML'),
              '    </div>',
              '    <div class="col-8">',
              '      <div>' + $('<a class="name"></a>').attr('href', item.link).text(item.name).prop('outerHTML') + '</div>',
              '      ' + $('<div class="price"></div>').text(item.formatted_price).prop('outerHTML'),
              '    </div>',
              '    <div class="col-1 text-end">',
              '      ' + $('<button class="btn btn-danger btn-sm" name="remove_cart_item" type="submit"><i class="fa fa-trash"></i></button>').val(item.key).prop('outerHTML'),
              '    </div>',
              '  </div>',
              '</li>'
            ].join('\n');
          });

          $('#site-navigation .shopping-cart ul').prepend(html);
        }
      });
    }

    let timerCart = setInterval('updateCart()', 60e3); // Keeps session alive
  }

/* ========================================================================
 * Bootstrap: dropdown.js v3.4.1
 * https://getbootstrap.com/docs/3.4/javascript/#dropdowns
 * ========================================================================
 * Copyright 2011-2019 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+function ($) {
  'use strict';

  // DROPDOWN CLASS DEFINITION
  // =========================

  let backdrop = '.dropdown-backdrop'
  let toggle   = '[data-toggle="dropdown"]'
  let Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle)
  }

  Dropdown.VERSION = '3.4.1'

  function getParent($this) {
    let selector = $this.attr('data-target')

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
      let $this         = $(this)
      let $parent       = getParent($this)
      let relatedTarget = { relatedTarget: this }

      if (!$parent.hasClass('open')) return

      if (e && e.type == 'click' && /input|textarea/i.test(e.target.tagName) && $.contains($parent[0], e.target)) return

      $parent.trigger(e = $.Event('hide.bs.dropdown', relatedTarget))

      if (e.isDefaultPrevented()) return

      $parent.removeClass('open').trigger($.Event('hidden.bs.dropdown', relatedTarget))
    })
  }

  Dropdown.prototype.toggle = function (e) {
    let $this = $(this)

    if ($this.is('.disabled, :disabled')) return

    let $parent  = getParent($this)
    let isActive = $parent.hasClass('open')

    clearMenus()

    if (!isActive) {
      if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
        // if mobile we use a backdrop because click events don't delegate
        $(document.createElement('div'))
          .addClass('dropdown-backdrop')
          .insertAfter($parent)
          .on('click', clearMenus)
      }

      let relatedTarget = { relatedTarget: this }
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

    let $this = $(this)

    e.preventDefault()
    e.stopPropagation()

    if ($this.is('.disabled, :disabled')) return

    let $parent  = getParent($this)
    let isActive = $parent.hasClass('open')

    if (!isActive && e.which != 27 || isActive && e.which == 27) {
      if (e.which == 27) $parent.find(toggle).trigger('focus')
      return $this.trigger('click')
    }

    let desc = ' li:not(.disabled):visible a'
    let $items = $parent.find('.dropdown-menu' + desc)

    if (!$items.length) return

    let index = $items.index(e.target)

    if (e.which == 38 && index > 0)                 index--         // up
    if (e.which == 40 && index < $items.length - 1) index++         // down
    if (!~index)                                    index = 0

    $items.eq(index).trigger('focus')
  }

  // DROPDOWN PLUGIN DEFINITION
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      let $this = $(this)
      let data  = $this.data('bs.dropdown')

      if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }

  let old = $.fn.dropdown

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


// Dropdown select
  $('.dropdown .form-select + .dropdown-menu :input').on('input', function(e){
    let dropdown = $(this).closest('.dropdown');
    let input = $(dropdown).find(':input:checked');
    $(dropdown).find('li.active').removeClass('active');
    if ($(dropdown).find(':input:checked').data('set-title')) {
      $(dropdown).find('.form-select').text($(input).data('set-title'));
      $(input).closest('li').addClass('active');
    }
    $(dropdown).trigger('click.bs.dropdown');
  });

  $('.data-table tbody tr').click(function(e) {
    if ($(e.target).is(':input')) return;
    if ($(e.target).is('a, a *')) return;
    if ($(e.target).is('th')) return;
    $(this).find(':checkbox').trigger('click');
  });

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
      }

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
            currentDrag = (clickX - e.pageX);
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

      $(window).on('resize', function(){

        if ($content.prop('scrollWidth') > ($self.outerWidth() + 20)) {

          if (!$self.find('button[name="left"], button[name="right"]').length) {

            $self.append(
              '<button name="left" class="btn btn-default" type="button"><i class="fa fa-chevron-left"></i></button>' +
              '<button name="right" class="btn btn-default" type="button"><i class="fa fa-chevron-right"></i></button>'
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

        if ($(window).width() > ($self.outerWidth() + 45)) {
          $self.find('button[name="left"]').css('left', '');
          $self.find('button[name="right"]').css('right', '');
        } else {
          $self.find('button[name="left"]').css('left', 0);
          $self.find('button[name="right"]').css('right', 0);
        }

      }).trigger('resize');
    });
  }

  $('[data-toggle*="momentumScroll"]').momentumScroll();

}(jQuery);

// Alerts
  $('body').on('click', '.alert .close', function(e){
    e.preventDefault();
    $(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
  });

// Password Strength
  $('form').on('input', 'input[type="password"][data-toggle="password-strength"]', function(){

    $(this).siblings('meter').remove();

    if ($(this).val() == '') return;

    let numbers = ($(this).val().match(/[0-9]/g) || []).length,
     lowercases = ($(this).val().match(/[a-z]/g) || []).length,
     uppercases = ($(this).val().match(/[A-Z]/g) || []).length,
     symbols =   ($(this).val().match(/[^\w]/g) || []).length,

     score = (numbers * 9) + (lowercases * 11.25) + (uppercases * 11.25) + (symbols * 15)
           + (numbers ? 10 : 0) + (lowercases ? 10 : 0) + (uppercases ? 10 : 0) + (symbols ? 10 : 0);

    let meter = $('<meter min="0" low="80" high="120" optimum="150" max="150" value="'+ score +'"></meter>').css({
      position: 'absolute',
      bottom: '-1em',
      width: '100%',
      height: '1em'
    });

    $(this).after(meter);
  });

/*
 * jQuery Placeholders
 * by LiteCart
 */

+function($) {

  let Placeholders = [];

  $.fn.Placeholder = function(options){
    this.each(function(){

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

// AJAX Search
  $('.navbar-search :input').focus(function(){
    $(this).closest('.dropdown').addClass('open');
  });

  $('.navbar-search :input').blur(function(){
    $(this).closest('.dropdown').removeClass('open');
  });

  let xhrAjaxSearch;
  $('.navbar-search :input').on('input', function(){

    let $navbar_search = $(this).closest('.navbar-search');

    if (xhrAjaxSearch) {
      xhrAjaxSearch.abort();
    }

    $navbar_search.find('.dropdown-menu').html('');

    if (!$(this).val()) {
      $navbar_search.find('.dropdown-menu').append(
        $('<li></li>').text($navbar_search.data('hint'))
      );
      return;
    }

    xhrAjaxSearch = $.ajax({
      url: window._env.platform.url + 'ajax/search_results.json',
      type: 'get',
      data: {query: $(this).val()},
      cache: false,
      async: true,
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
      },
      success: function(results) {
        if (results) {
          $.each(results, function(i, result){
            $.each(result, function(i, row){
              $navbar_search.find('.dropdown-menu').append([
                '<li><a href="'+ row.url +'">',
                '  <img src="'+ row.thumbnail +'" style="height: 1em;"> ' + row.name,
                '</a></li>',
              ].join('\n'));
            });
          });
        } else {
          $navbar_search.find('.dropdown-menu').html('<li>:(</li>');
        }
      }
    });
  }).trigger('input');

// Data-Table Toggle Checkboxes
$('body').on('click', '.data-table *[data-toggle="checkbox-toggle"], .data-table .checkbox-toggle', function() {
  $(this).closest('.data-table').find('tbody td:first-child :checkbox').each(function() {
    $(this).prop('checked', !$(this).prop('checked')).trigger('change');
  });
  return false;
});

$('body').on('click', '.data-table tbody tr', function(e) {
  if ($(e.target).is('a') || $(e.target).closest('a').length) return;
  if ($(e.target).is('.btn, :input, th, .fa-star, .fa-star-o')) return;
  $(this).find(':checkbox, :radio').first().trigger('click');
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

// Polyfill for easeOutBounce
  $.extend($.easing, {
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
    easeOutCubic: function (x) {
      return 1 - Math.pow( 1 - x, 3 );
    },
  });
