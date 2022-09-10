// Stylesheet Loader
  $.loadStylesheet = function(url, callback, fallback) {
    $('<link/>', {rel: 'stylesheet', href: url}).appendTo('head');
  }

// JavaScript Loader
  $.loadScript = function(url, options) {

    options = $.extend(options || {}, {
      mtehod: 'GET',
      dataType: 'script',
      cache: true
    });

    return jQuery.ajax(url, options);
  };

// Alerts
  $('body').on('click', '.alert .close', function(e){
    e.preventDefault();
    $(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
  });

// Form required asterix
  $(':input[required]').closest('.form-group').addClass('required');

// AJAX Search
  let timer_ajax_search = null;
  let xhr_search = null;
  $('#search input[name="query"]').on('input', function(){

    let search_field = this;

    if (xhr_search) xhr_search.abort();

    if ($(this).val() == '') {
      $('#search .results').hide().html('');
      $('#box-apps-menu').fadeIn('fast');
      return;
    }

    if (!$('#search .loader-wrapper').length) {
      $('#box-apps-menu').fadeOut('fast');
      $('#search .results').show().html('<div class="loader-wrapper text-center"><div class="loader" style="width: 48px; height: 48px;"></div></div>');
    }

    clearTimeout(timer_ajax_search);
    timer_ajax_search = setTimeout(function() {
      xhr_search = $.ajax({
        type: 'get',
        async: true,
        cache: false,
        url: window._env.backend.url + 'search_results.json?query=' + $(search_field).val(),
        dataType: 'json',

        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
        },

        error: function(jqXHR, textStatus, errorThrown) {
          $('#search .results').text(textStatus + ': ' + errorThrown);
        },

        success: function(json) {

          $('#search .results').html('');

          if (!$('#search input[name="query"]').val()) return;

          $.each(json, function(i, group){

            if (group.results.length) {
              $('#search .results').append(
                '<h4>'+ group.name +'</h4>' +
                '<ul class="list-group" data-group="'+ group.name +'"></ul>'
              );

              $.each(group.results, function(i, result){
                $('#search .results ul[data-group="'+ group.name +'"]').append(
                  '<li class="result">' +
                  '  <a class="list-group-item" href="'+ result.link +'" style="border-inline-start: 3px solid '+ group.theme.color +';">' +
                  '    <small class="id float-end">#'+ result.id +'</small>' +
                  '    <div class="title">'+ result.title +'</div>' +
                  '    <div class="description"><small>'+ result.description +'</small></div>' +
                  '  </a>' +
                  '</li>'
                );
              });
            }
          });

          if ($('#search .results').html() == '') {
            $('#search .results').html('<p class="text-center no-results"><em>:(</em></p>');
          }
        },
      });
    }, 500);
  });

// Tabs (data-toggle="tab")
+function($) {
  'use strict';
  $.fn.Tabs = function(){
    this.each(function(){

      let self = this;

      this.$element = $(this);

      this.$element.find('[data-toggle="tab"]').each(function(){
        let $link = $(this);

        $link.on('select', function(){
          self.$element.find('.active').removeClass('active');

          if ($link.hasClass('nav-link')) {
            $link.addClass('active');
          }

          $link.closest('.nav-item').addClass('active');

          $($link.attr('href')).show().siblings().hide();
        });

        $link.on('click', function(e) {
          e.preventDefault();
          history.replaceState(null, null, this.hash);
          $link.trigger('select');
        });
      });

      if (!this.$element.find('.active').length) {
        this.$element.find('[data-toggle="tab"]').first().select();
      } else {
        this.$element.find('[data-toggle="tab"].active').select();
      }
    });
  }

  $('.nav-tabs').Tabs();

  if (document.location.hash && document.location.hash.match(/^#tab-/)) {
    $('[data-toggle="tab"][href="' + document.location.hash +'"]').trigger('select');
  }

  $(document).on('ajaxcomplete', function(){
    $('.nav-tabs').Tabs();
  });
}(jQuery);

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

// Dropdown select
  $('.dropdown .form-select + .dropdown-menu :input').on('input', function(e){
    let $dropdown = $(this).closest('.dropdown');
    let $input = $dropdown.find(':input:checked');

    if (!$dropdown.find(':input:checked').length) return;

    $dropdown.find('li.active').removeClass('active');

    if ($input.data('title')) {
      $dropdown.find('.form-select').text( $input.data('title') );
    } else if ($input.closest('.option').find('.title').length) {
      $dropdown.find('.form-select').text( $input.closest('.option').find('.title').text() );
    } else {
      $dropdown.find('.form-select').text( $input.parent().text() );
    }

    $input.closest('li').addClass('active');
    $dropdown.trigger('click.bs.dropdown');

  }).trigger('input');

// Data-Table Toggle Checkboxes
  $('body').on('click', '.data-table *[data-toggle="checkbox-toggle"]', function() {
    $(this).closest('.data-table').find('tbody td > .form-check > :checkbox').each(function() {
      $(this).prop('checked', !$(this).prop('checked'));
    });
    return false;
  });

  $('body').on('click', '.data-table tbody tr', function(e) {
    if ($(e.target).is('a') || $(e.target).closest('a').length) return;
    if ($(e.target).is('.btn, :input, th, .fa-star, .fa-star-o')) return;
    $(this).find(':checkbox, :radio').trigger('click');
  });

// Data-Table Dragable
  $('body').on('mousedown', '.table-dragable tbody .grabable', function(e){
    let tr = $(e.target).closest('tr'), sy = e.pageY, drag;
    if ($(e.target).is('tr')) tr = $(e.target);
    let index = tr.index();
    $(tr).addClass('grabbed');
    $(tr).closest('tbody').css('unser-input', 'unset');
    function move(e) {
      if (!drag && Math.abs(e.pageY - sy) < 10) return;
      drag = true;
      tr.siblings().each(function() {
        let s = $(this), i = s.index(), y = s.offset().top;
        if (e.pageY >= y && e.pageY < y + s.outerHeight()) {
          if (i < tr.index()) s.insertAfter(tr);
          else s.insertBefore(tr);
          return false;
        }
      });
    }
    function up(e) {
      if (drag && index != tr.index()) {
        drag = false;
      }
      $(document).off('mousemove', move).off('mouseup', up);
      $(tr).removeClass('grabbed');
      $(tr).closest('tbody').css('unser-input', '');
    }
    $(document).mousemove(move).mouseup(up);
  });

// Data-Table Sorting (Page Reload)
  $('.table-sortable thead th[data-sort]').click(function(){
    let params = {};

    window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
      params[key] = value;
    });

    params.sort = $(this).data('sort');

    window.location.search = $.param(params);
  });

// Data-Table Shift Check Multiple Checkboxes
  let lastTickedCheckbox = null;
  $('.data-table td > .form-check > :checkbox').click(function(e){

    let $chkboxes = $('.data-table td > .form-check > :checkbox');

    if (!lastTickedCheckbox) {
      lastTickedCheckbox = this;
      return;
    }

    if (e.shiftKey) {
      let start = $chkboxes.index(this);
      let end = $chkboxes.index(lastTickedCheckbox);
      $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastTickedCheckbox.checked);
    }

    lastTickedCheckbox = this;
  });

// Keep-alive
  let keepAlive = setInterval(function(){
    $.get({
      url: window._env.platform.path + 'ajax/cart.json',
      cache: false
    });
  }, 60e3);

/*
 * jQuery Category Picker
 * by LiteCart
 */

+function($) {

  $.fn.categoryPicker = function(config){
    this.each(function(){

      this.xhr = null;
      this.config = config;

      self = this;

      $(this).find('.dropdown input[type="search"]').on({

        'focus': function(e){
          $(self).find('.dropdown').addClass('open');
        },

        'input': function(e){
            let dropdownMenu = $(self).find('.dropdown-menu');

            $(dropdownMenu).html('');

            if (self.xhr) self.xhr.abort();

            if ($(this).val() == '') {

              $.getJSON(self.config.link+'?parent_id=0', function(result) {

                $(dropdownMenu).html('<li class="list-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

                $.each(result.subcategories, function(i, category) {
                  $(dropdownMenu).append(
                    '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex;">' +
                    '  ' + self.config.icons.folder +
                    '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
                    '  <button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button>' +
                    '</li>'
                  );
                });
              });

              return;
            }

            self.xhr = $.ajax({
              type: 'get',
              async: true,
              cache: true,
              url: self.config.link + '?query=' + $(this).val(),
              dataType: 'json',

              beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
              },

              error: function(jqXHR, textStatus, errorThrown) {
                if (errorThrown == 'abort') return;
                alert(errorThrown);
              },

              success: function(result) {

                if (!result.subcategories.length) {
                  $(dropdownMenu).html('<li class="list-item text-center no-results"><em>:(</em></li>');
                  return;
                }

                $(dropdownMenu).html('<li class="list-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>');

                $.each(result.subcategories, function(i, category) {
                  $(dropdownMenu).append(
                    '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex;">' +
                    '  ' + self.config.icons.folder +
                    '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
                    '  <button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button>' +
                    '</li>'
                  );
                });
              },
            });
          }
      });

      $(this).on('click', '.dropdown-menu .list-item a', function(e){
        e.preventDefault();

        let dropdownMenu = $(this).closest('.dropdown-menu');

        $.getJSON($(this).data('link'), function(result) {

          $(dropdownMenu).html('<li class="list-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

          if (result.id) {
            $(dropdownMenu).append(
              '<li class="list-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex;">' +
              '  ' + self.config.icons.back +
              '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
              '</li>'
            );
          }

          $.each(result.subcategories, function(i, category) {
            $(dropdownMenu).append(
              '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex;">' +
              '  ' + self.config.icons.folder +
              '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
              '  <button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button>' +
              '</li>'
            );
          });
        });
      });

      $(this).on('click', '.dropdown-menu .list-item button.add', function(e){
        e.preventDefault();

        let category = $(this).closest('li');

        $(self).find('.categories').append(
          '<li class="list-item" style="display: flex;">' +
          '  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ escape($(category).data('name')) +'" />' +
          '  <div style="flex-grow: 1;">' + self.config.icons.folder +' '+ $(category).data('name') +'</div>' +
          '  <button class="remove btn btn-default btn-sm" type="button">'+ self.config.translations.remove +'</button>' +
          '</li>'
        );

        $(self).trigger('change');

        $('.dropdown.open').removeClass('open');

        return false;
      });

      $(this).find('.categories').on('click', '.remove', function(e){
        $(this).closest('li').remove();
        $(self).trigger('change');
      });

      $('body').on('mousedown', function(e){
        if ($('.dropdown.open').has(e.target).length === 0) {
          $('.dropdown.open').removeClass('open');
        }
      });

      $(this).find('input[type="search"]').trigger('input');

    });
  }

}(jQuery);

/*
 * Escape HTML
 */
function escapeHTML(string) {
  let entityMap = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': '&quot;',
      "'": '&#39;',
      "/": '&#x2F;'
  };
  return String(string).replace(/[&<>"'\/]/g, function (s) {
      return entityMap[s];
  });
};

/*
 * jQuery Category Picker
 * by LiteCart
 */

+function($) {

  $.fn.categoryPicker = function(config){
    this.each(function(){

      this.xhr = null;
      this.config = config;

      self = this;

      $(this).find('.dropdown input[type="search"]').on({

        'focus': function(e){
          $(self).find('.dropdown').addClass('open');
        },

        'input': function(e){
            let dropdownMenu = $(self).find('.dropdown-menu');

            $(dropdownMenu).html('');

            if (self.xhr) self.xhr.abort();

            if ($(this).val() == '') {

              $.getJSON(self.config.link, function(result) {

                $(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

                $.each(result.subcategories, function(i, category) {
                  $(dropdownMenu).append(
                    '<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
                    '  ' + self.config.icons.folder +
                    '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
                    '  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
                    '</li>'
                  );
                });
              });

              return;
            }

            self.xhr = $.ajax({
              type: 'get',
              async: true,
              cache: true,
              url: self.config.link + '&query=' + $(this).val(),
              dataType: 'json',

              beforeSend: function(jqXHR) {
                jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
              },

              error: function(jqXHR, textStatus, errorThrown) {
                if (errorThrown == 'abort') return;
                alert(errorThrown);
              },

              success: function(result) {

                if (!result.subcategories.length) {
                  $(dropdownMenu).html('<li class="dropdown-item text-center no-results"><em>:(</em></li>');
                  return;
                }

                $(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>');

                $.each(result.subcategories, function(i, category) {
                  $(dropdownMenu).append(
                    '<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
                    '  ' + self.config.icons.folder +
                    '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
                    '  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
                    '</li>'
                  );
                });
              },
            });
          }
      });

      $(this).on('click', '.dropdown-menu .dropdown-item a', function(e){
        e.preventDefault();

        let dropdownMenu = $(this).closest('.dropdown-menu');

        $.getJSON($(this).data('link'), function(result) {

          $(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

          if (result.id) {
            $(dropdownMenu).append(
              '<li class="dropdown-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex; align-items: center;">' +
              '  ' + self.config.icons.back +
              '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
              '</li>'
            );
          }

          $.each(result.subcategories, function(i, category) {
            $(dropdownMenu).append(
              '<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
              '  ' + self.config.icons.folder +
              '  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
              '  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
              '</li>'
            );
          });
        });
      });

      $(this).on('click', '.dropdown-menu .dropdown-item button.add', function(e){
        e.preventDefault();

        let category = $(this).closest('li'),
            abort = false;

        $(self).find('input[name="'+ self.config.inputName +'"]').each(function(){
          if ($(this).val() == category.data('id')) {
            abort = true;
            return;
          }
        });

        if (abort) return;

        $(self).find('.categories').append(
          '<li class="dropdown-item" style="display: flex; align-items: center;">' +
          '  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ $(category).data('name').replace(/"/, '&quote;') +'" />' +
          '  <div style="flex-grow: 1;">' + self.config.icons.folder +' '+ $(category).data('name') +'</div>' +
          '  <div><button class="remove btn btn-default btn-sm" type="button">'+ self.config.translations.remove +'</button></div>' +
          '</li>'
        );

        $(self).trigger('change');

        $('.dropdown.open').removeClass('open');

        return false;
      });

      $(this).find('.categories').on('click', '.remove', function(e){
        $(this).closest('li').remove();
        $(self).trigger('change');
      });

      $('body').on('mousedown', function(e){
        if ($('.dropdown.open').has(e.target).length === 0) {
          $('.dropdown.open').removeClass('open');
        }
      });

      $(this).find('input[type="search"]').trigger('input');

    });
  }

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

  let backdrop = '.dropdown-backdrop'
  let toggle   = '[data-toggle="dropdown"]'
  let Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle)
  }

  Dropdown.VERSION = '3.3.7'

  function getParent($this) {
    let selector = $this.attr('data-target')

    if (!selector) {
      selector = $this.attr('href')
      selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
    }

    let $parent = selector && $(selector)

    return $parent && $parent.length ? $parent : $this.closest('.dropdown')
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

      $this.attr('aria-expanded', 'false')
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
          .insertAfter($(this))
          .on('click', clearMenus)
      }

      let relatedTarget = { relatedTarget: this }
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
