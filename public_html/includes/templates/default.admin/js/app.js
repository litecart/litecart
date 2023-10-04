// Alerts
  $('body').on('click', '.alert .close', function(e){
    e.preventDefault();
    $(this).closest('.alert').fadeOut('fast', function(){$(this).remove()});
  });

// Form required asterix
  $(':input[required]').closest('.form-group').addClass('required');

// AJAX Search
  var timer_ajax_search = null;
  var xhr_search = null;
  $('#search input[name="query"]').on('input', function(){
    if (xhr_search) xhr_search.abort();

    if ($(this).val() != '') {
      if (!$('#search .loader-wrapper').length) {
        $('#box-apps-menu').fadeOut('fast');
        $('#search .results').show().html('<div class="loader-wrapper text-center"><div class="loader" style="width: 48px; height: 48px;"></div></div>');
      }
      var query = $(this).val();

      clearTimeout(timer_ajax_search);
      timer_ajax_search = setTimeout(function() {
        xhr_search = $.ajax({
          type: 'get',
          async: true,
          cache: false,
          url: 'search_results.json.php?query=' + encodeURIComponent(query),
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
                    '  <a class="list-group-item" href="'+ result.url +'" style="border-left: 3px solid '+ group.theme.color +';">' +
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

    } else {
      $('#search .results').hide().html('');
      $('#box-apps-menu').fadeIn('fast');
    }
  });

// Tabs (data-toggle="tab")
  $('.nav-tabs').each(function(){
    if (!$(this).find('.active').length) {
      $(this).find('[data-toggle="tab"]:first').addClass('active');
    }

    $(this).on('select', '[data-toggle="tab"]', function() {
      $(this).siblings().removeClass('active');
      $(this).addClass('active');
      $($(this).attr('href')).show().siblings().hide();
    });

    $(this).on('click', '[data-toggle="tab"]', function(e) {
      e.preventDefault();
      $(this).trigger('select');
      history.replaceState({}, '', location.toString().replace(/#.*$/, '') + $(this).attr('href'));
    });

    $(this).find('.active').trigger('select');
  });

  if (document.location.hash != '') {
    $('a[data-toggle="tab"][href="' + document.location.hash + '"]').click();
  }

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
    $(this).find(':checkbox, :radio').trigger('click');
  });

// Data-Table Dragable
  $('body').on('mousedown', '.table-dragable tbody .grabable', function(e){
    var tr = $(e.target).closest('tr'), sy = e.pageY, drag;
    if ($(e.target).is('tr')) tr = $(e.target);
    var index = tr.index();
    $(tr).addClass('grabbed');
    $(tr).closest('tbody').css('unser-input', 'unset');
    function move(e) {
      if (!drag && Math.abs(e.pageY - sy) < 10) return;
      drag = true;
      tr.siblings().each(function() {
        var s = $(this), i = s.index(), y = s.offset().top;
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
    var params = {};

    window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
      params[key] = value;
    });

    params.sort = $(this).data('sort');

    window.location.search = $.param(params);
  });

// Data-Table Shift Check Multiple Checkboxes
  var lastTickedCheckbox = null;
  $('.data-table input[type="checkbox"]').click(function(e){

    var $chkboxes = $('.data-table input[type="checkbox"]');

    if (!lastTickedCheckbox) {
      lastTickedCheckbox = this;
      return;
    }

    if (e.shiftKey) {
      var start = $chkboxes.index(this);
      var end = $chkboxes.index(lastTickedCheckbox);
      $chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastTickedCheckbox.checked);
    }

    lastTickedCheckbox = this;
  });

// Keep-alive
  var keepAlive = setInterval(function(){
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
          '  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ escape($(category).data('name')) +'">' +
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
 * jQuery Context Menu
 * by LiteCart
 */

+function($) {

  $.fn.contextMenu = function(config){
    this.each(function(){

      this.config = config;

      self = this;

      $(this).on('contextmenu').on({

      });

    });
  }

}(jQuery);

/*
 * Escape HTML
 */
function escapeHTML(string) {
  var entityMap = {
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
            var dropdownMenu = $(self).find('.dropdown-menu');

            $(dropdownMenu).html('');

            if (self.xhr) self.xhr.abort();

            if ($(this).val() == '') {

              $.getJSON(self.config.link, function(result) {

                $(dropdownMenu).html('<li class="list-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

                $.each(result.subcategories, function(i, category) {
                  $(dropdownMenu).append(
                    '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
                    '  ' + self.config.icons.folder +
                    '  <a href="#" data-link="'+ self.config.link +'&parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
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
                  $(dropdownMenu).html('<li class="list-item text-center no-results"><em>:(</em></li>');
                  return;
                }

                $(dropdownMenu).html('<li class="list-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>');

                $.each(result.subcategories, function(i, category) {
                  $(dropdownMenu).append(
                    '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
                    '  ' + self.config.icons.folder +
                    '  <a href="#" data-link="'+ self.config.link +'&parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
                    '  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
                    '</li>'
                  );
                });
              },
            });
          }
      });

      $(this).on('click', '.dropdown-menu .list-item a', function(e){
        e.preventDefault();

        var dropdownMenu = $(this).closest('.dropdown-menu');

        $.getJSON($(this).data('link'), function(result) {

          $(dropdownMenu).html('<li class="list-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>');

          if (result.id) {
            $(dropdownMenu).append(
              '<li class="list-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex; align-items: center;">' +
              '  ' + self.config.icons.back +
              '  <a href="#" data-link="'+ self.config.link +'&parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
              '</li>'
            );
          }

          $.each(result.subcategories, function(i, category) {
            $(dropdownMenu).append(
              '<li class="list-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
              '  ' + self.config.icons.folder +
              '  <a href="#" data-link="'+ self.config.link +'&parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
              '  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
              '</li>'
            );
          });
        });
      });

      $(this).on('click', '.dropdown-menu .list-item button.add', function(e){
        e.preventDefault();

        var category = $(this).closest('li'),
            abort = false;

        $(self).find('input[name="'+ self.config.inputName +'"]').each(function(){
          if ($(this).val() == category.data('id')) {
            abort = true;
            return;
          }
        });

        if (abort) return;

        $(self).find('.categories').append(
          '<li class="list-item" style="display: flex; align-items: center;">' +
          '  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ $(category).data('name').replace(/"/, '&quote;') +'">' +
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

      $this.attr('aria-expanded', 'false')
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
