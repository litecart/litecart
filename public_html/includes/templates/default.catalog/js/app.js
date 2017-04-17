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
  (function($) {
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
  })(jQuery);

  /*!
   * jQuery Cookie Plugin v1.4.1
   * https://github.com/carhartl/jquery-cookie
   *
   * Copyright 2006, 2014 Klaus Hartl
   * Released under the MIT license
   */
  (function (factory) {
    if (typeof define === 'function' && define.amd) {
      // AMD (Register as an anonymous module)
      define(['jquery'], factory);
    } else if (typeof exports === 'object') {
      // Node/CommonJS
      module.exports = factory(require('jquery'));
    } else {
      // Browser globals
      factory(jQuery);
    }
  }(function ($) {

    var pluses = /\+/g;

    function encode(s) {
      return config.raw ? s : encodeURIComponent(s);
    }

    function decode(s) {
      return config.raw ? s : decodeURIComponent(s);
    }

    function stringifyCookieValue(value) {
      return encode(config.json ? JSON.stringify(value) : String(value));
    }

    function parseCookieValue(s) {
      if (s.indexOf('"') === 0) {
        // This is a quoted cookie as according to RFC2068, unescape...
        s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
      }

      try {
        // Replace server-side written pluses with spaces.
        // If we can't decode the cookie, ignore it, it's unusable.
        // If we can't parse the cookie, ignore it, it's unusable.
        s = decodeURIComponent(s.replace(pluses, ' '));
        return config.json ? JSON.parse(s) : s;
      } catch(e) {}
    }

    function read(s, converter) {
      var value = config.raw ? s : parseCookieValue(s);
      return $.isFunction(converter) ? converter(value) : value;
    }

    var config = $.cookie = function (key, value, options) {

      // Write

      if (arguments.length > 1 && !$.isFunction(value)) {
        options = $.extend({}, config.defaults, options);

        if (typeof options.expires === 'number') {
          var days = options.expires, t = options.expires = new Date();
          t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
        }

        return (document.cookie = [
          encode(key), '=', stringifyCookieValue(value),
          options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
          options.path    ? '; path=' + options.path : '',
          options.domain  ? '; domain=' + options.domain : '',
          options.secure  ? '; secure' : ''
        ].join(''));
      }

      // Read

      var result = key ? undefined : {},
        // To prevent the for loop in the first place assign an empty array
        // in case there are no cookies at all. Also prevents odd result when
        // calling $.cookie().
        cookies = document.cookie ? document.cookie.split('; ') : [],
        i = 0,
        l = cookies.length;

      for (; i < l; i++) {
        var parts = cookies[i].split('='),
          name = decode(parts.shift()),
          cookie = parts.join('=');

        if (key === name) {
          // If second argument (value) is a function it's a converter...
          result = read(cookie, value);
          break;
        }

        // Prevent storing a cookie that we couldn't decode.
        if (!key && (cookie = read(cookie)) !== undefined) {
          result[name] = cookie;
        }
      }

      return result;
    };

    config.defaults = {};

    $.removeCookie = function (key, options) {
      // Must not alter options, thus extending a fresh object...
      $.cookie(key, '', $.extend({}, options, { expires: -1 }));
      return !$.cookie(key);
    };

  }));