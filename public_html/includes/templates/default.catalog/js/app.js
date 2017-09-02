$(document).ready(function(){

// Alerts
  $('body').on('click', '.alert .close', function(e){
    e.preventDefault();
    $(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
  });

// Form required asterix
  $(':input[required="required"]').closest('.form-group').addClass('required');

// Sidebar parallax effect
  if (window.config.template.settings.sidebar_parallax_effect == true) {
    var column = $('#column-left'), sidebar = $('#sidebar');
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
  window.updateCart = function(data) {
    if (data) $('*').css('cursor', 'wait');
    $.ajax({
      url: window.config.platform.url + 'ajax/cart.json',
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
        $('#cart .items').append('<li><a href="' + config.platform.url + 'checkout"><i class="fa fa-shopping-cart"></i> ' + json['text_total'] + ': <span class="formatted-value">'+ json['formatted_value'] +'</a></li>');
        $('#cart .quantity').html(json['quantity']);
        $('#cart .formatted_value').html(json['formatted_value']);
        if (json['quantity'] > 0) {
          $('#cart img').attr('src', config.template.url + 'images/cart_filled.svg');
        } else {
          $('#cart img').attr('src', config.template.url + 'images/cart.svg');
        }
      },
      complete: function() {
        if (data) $('*').css('cursor', '');
      }
    });
  }

  var timerCart = setInterval("updateCart()", 60000); // Keeps session alive

// Bootstrap Comaptible (data-toggle="tab")
  $('[data-toggle="tab"]').click(function(e) {
    e.preventDefault();
    $(this).closest('ul').find('li').removeClass('active');
    $(this).closest('li').addClass('active');
    $($(this).attr('href')).show().siblings().hide();
  });

  $('.nav-tabs').each(function(){
    if (!$(this).find('.active').length) {
      $(this).find('li:first').addClass('active');
    }
  });

  if (window.location.hash != '') {
    $('a[href="' + window.location.hash + '"]').click();
  } else {
    $('.nav-tabs .active a').trigger('click');
  }

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
      shadowImage = "<img src='" + options.initial_css.image + "' style='width: 100%; height: 100%' />";
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
 * Bootstrap: collapse.js v3.3.7
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2011-2016 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

/* jshint latedef: false */

+function ($) {
  'use strict';

  // COLLAPSE PUBLIC CLASS DEFINITION
  // ================================

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

  Collapse.VERSION  = '3.3.7'

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

    var scrollSize = $.camelCase(['scroll', dimension].join('-'))

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
    return $(this.options.parent)
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

    return $(target)
  }


  // COLLAPSE PLUGIN DEFINITION
  // ==========================

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
  // ====================

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }


  // COLLAPSE DATA-API
  // =================

  $(document).on('click.bs.collapse.data-api', '[data-toggle="collapse"]', function (e) {
    var $this   = $(this)

    if (!$this.attr('data-target')) e.preventDefault()

    var $target = getTargetFromTrigger($this)
    var data    = $target.data('bs.collapse')
    var option  = data ? 'toggle' : $this.data()

    Plugin.call($target, option)
  })

}(jQuery);
