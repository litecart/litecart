/*!
 * LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built without nonsense.
 * @link https://www.litecart.net/
 * @license CC-BY-ND-4.0
 * @author T. Almroth
 */

	// Stylesheet Loader
	$.loadStylesheet = function(url, options, callback, fallback) {

		options = $.extend(options || {}, {
			rel: 'stylesheet',
			href: url,
			onload: callback,
			onerror: fallback
		})

		$('<link>', options).appendTo('head')
	}

	// JavaScript Loader
	$.loadScript = function(url, options) {

		options = $.extend(options || {}, {
			method: 'GET',
			dataType: 'script',
			cache: true
		})

		return jQuery.ajax(url, options)
	}

	// Escape HTML
	function escapeHTML(string) {

		let entityMap = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#39;',
			'/': '&#x2F;',
			'`': '&#x60;',
		}

		return String(string).replace(/[&<>"'\/]/g, function (s) {
			return entityMap[s]
		})
	}

	// Escape HTML
	function escapeAttr(string) {
		return escapeHTML(string).replace(/\r\n?|\n/g, '\\n')
	}

	// Money Formatting
	Number.prototype.toMoney = function() {
		var n = this,
			c = _env.session.currency.decimals,
			d = _env.session.language.decimal_point,
			t = _env.session.language.thousands_separator,
			p = _env.session.currency.prefix,
			x = _env.session.currency.suffix,
			u = _env.session.currency.code,
			s = n < 0 ? '-' : '',
			i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
			f = n - i,
			j = (j = i.length) > 3 ? j % 3 : 0

		return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(f).toFixed(c).slice(2) : '') + x
	}

	// Keep-alive
	let keepAlive = setInterval(function() {
		$.get({
			url: window._env.platform.path + 'ajax/cart.json',
			cache: false
		})
	}, 60e3)



/*
 * jQuery Category Picker
 * by LiteCart
 */

+function() {

	$.fn.categoryPicker = function(config){
		this.each(function() {

			this.xhr = null
			this.config = config

			self = this

			$(this).find('.dropdown input[type="search"]').on({

				'focus': function(e) {
					$(self).find('.dropdown').addClass('open')
				},

				'input': function(e) {
						let dropdownMenu = $(self).find('.dropdown-menu')

						$(dropdownMenu).html('')

						if (self.xhr) self.xhr.abort()

						if ($(this).val() == '') {

							$.getJSON(self.config.link, function(result) {

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>')

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									)
								})
							})

							return
						}

						self.xhr = $.ajax({
							type: 'get',
							async: true,
							cache: true,
							url: self.config.link + '&query=' + $(this).val(),
							dataType: 'json',

							beforeSend: function(jqXHR) {
								jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'))
							},

							error: function(jqXHR, textStatus, errorThrown) {
								if (errorThrown == 'abort') return
								alert(errorThrown)
							},

							success: function(result) {

								if (!result.subcategories.length) {
									$(dropdownMenu).html('<li class="dropdown-item text-center no-results"><em>:(</em></li>')
									return
								}

								$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ self.config.translations.search_results +'</h3></li>')

								$.each(result.subcategories, function(i, category) {
									$(dropdownMenu).append(
										'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
										'  ' + self.config.icons.folder +
										'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
										'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
										'</li>'
									)
								})
							},
						})
					}
			})

			$(this).on('click', '.dropdown-menu .dropdown-item a', function(e) {
				e.preventDefault()

				let dropdownMenu = $(this).closest('.dropdown-menu')

				$.getJSON($(this).data('link'), function(result) {

					$(dropdownMenu).html('<li class="dropdown-item"><h3 style="margin-top: 0;">'+ result.name +'</h3></li>')

					if (result.id) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.back +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>' +
							'</li>'
						)
					}

					$.each(result.subcategories, function(i, category) {
						$(dropdownMenu).append(
							'<li class="dropdown-item" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'" style="display: flex; align-items: center;">' +
							'  ' + self.config.icons.folder +
							'  <a href="#" data-link="'+ self.config.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>' +
							'  <div><button class="add btn btn-default btn-sm" type="button">'+ self.config.translations.add +'</button></div>' +
							'</li>'
						)
					})
				})
			})

			$(this).on('click', '.dropdown-menu .dropdown-item button.add', function(e) {
				e.preventDefault()

				let category = $(this).closest('li'),
						abort = false

				$(self).find('input[name="'+ self.config.inputName +'"]').each(function() {
					if ($(this).val() == category.data('id')) {
						abort = true
						return
					}
				})

				if (abort) return

				$(self).find('.categories').append(
					'<li class="dropdown-item" style="display: flex; align-items: center;">' +
					'  <input type="hidden" name="'+ self.config.inputName +'" value="'+ $(category).data('id') +'" data-name="'+ $(category).data('name').replace(/"/, '&quote;') +'" />' +
					'  <div style="flex-grow: 1;">' + self.config.icons.folder +' '+ $(category).data('name') +'</div>' +
					'  <div><button class="remove btn btn-default btn-sm" type="button">'+ self.config.translations.remove +'</button></div>' +
					'</li>'
				)

				$(self).trigger('change')

				$('.dropdown.open').removeClass('open')

				return false
			})

			$(this).find('.categories').on('click', '.remove', function(e) {
				$(this).closest('li').remove()
				$(self).trigger('change')
			})

			$('body').on('mousedown', function(e) {
				if ($('.dropdown.open').has(e.target).length === 0) {
					$('.dropdown.open').removeClass('open')
				}
			})

			$(this).find('input[type="search"]').trigger('input')

		})
	}

}()


/*
 * jQuery Context Menu
 * by LiteCart
 */

+function() {

	$.fn.contextMenu = function(config){
		this.each(function() {

			this.config = config

			self = this

			$(this).on('contextmenu').on({
			})
		})
	}

}()


	// Dragmove

	$('style').first().append([
		'.dragmove-horizontal {',
		'  cursor: e-resize;',
		'  user-select: none;',
		'}',
		'.dragmove-vertical {',
		'  cursor: n-resize;',
		'  user-select: none;',
		'}',
		'.dragmove-vertical.grabbed,',
		'.dragmove-horizontal.grabbed	{',
		'  user-input: unset;',
		'  box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);',
		'}',
	].join('\n'))

	$('body').on('click', '.dragmove', function(e) {
		e.preventDefault()
		return false
	})

	$('body').on('mousedown', '.dragmove-vertical, .dragmove-horizontal', function(e) {

		let $item = $(e.target).closest('.dragmove'),
			sy = e.pageY,
			drag

		if ($(e.target).is('.dragmove')) {
			$item = $(e.target)
		}

		let index = $item.index()

		$item.addClass('grabbed')
		$item.closest('tbody').css('user-input', 'unset')

		function move(e) {

			if (!drag && Math.abs(e.pageY - sy) < 10) return
			drag = true

			$item.siblings().each(function() {

				let s = $(this), i = s.index(), y = s.offset().top

				if (e.pageY >= y && e.pageY < y + s.outerHeight()) {
					if (i < $item.index()) s.insertAfter($item)
					else s.insertBefore($item)
					return false
				}
			})
		}

		function up(e) {

			if (drag && index != $item.index()) {
				drag = false
			}

			$(document).off('mousemove', move).off('mouseup', up)
			$item.removeClass('grabbed')
			$item.closest('tbody').css('user-input', '')
		}

		$(document).mousemove(move).mouseup(up)
	})


// Dropdown

$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
	$(this).closest('.dropdown').toggleClass('open')
})

$('.dropdown').on('focusout', function(e) {
	$(this).closest('.dropdown').removeClass('open')
})

$('.dropdown').on('click', 'a', function(e) {
	$(this).closest('.dropdown').removeClass('open')
})


	// Form required asterix
	$(':input[required]').closest('.form-group').addClass('required')

	// Dropdown select
	$('.dropdown .form-select + .dropdown-menu :input').on('input', function(e) {
		let $dropdown = $(this).closest('.dropdown')
		let $input = $dropdown.find(':input:checked')

		if (!$dropdown.find(':input:checked').length) return

		$dropdown.find('li.active').removeClass('active')

		if ($input.data('title')) {
			$dropdown.find('.form-select').text( $input.data('title') )
		} else if ($input.closest('.option').find('.title').length) {
			$dropdown.find('.form-select').text( $input.closest('.option').find('.title').text() )
		} else {
			$dropdown.find('.form-select').text( $input.parent().text() )
		}

		$input.closest('li').addClass('active')
		$dropdown.trigger('click.bs.dropdown')

	}).trigger('input')

	// Input Number Decimals
	$('body').on('change', 'input[type="number"][data-decimals]', function() {
		 var value = parseFloat($(this).val()),
			 decimals = $(this).data('decimals')
		if (decimals != '') {
			$(this).val(value.toFixed(decimals))
		}
	})


	$('textarea[data-toggle="csv"] + table').on('click', '.remove', function(e) {
		e.preventDefault()
		var parent = $(this).closest('tbody')
		$(this).closest('tr').remove()
		$(parent).trigger('keyup')
	})

	$('textarea[data-toggle="csv"] + table .add-row').on('click', function(e) {
		e.preventDefault()
		var n = $(this).closest('table').find('thead th:not(:last-child)').length
		$(this).closest('table').find('tbody').append(
			'<tr>' + ('<td contenteditable></td>'.repeat(n)) + '<td><a class="remove" href="#"><i class="fa fa-times" style="color: #d33;"></i></a></td>' +'</tr>'
		).trigger('keyup')
	})

	$('textarea[data-toggle="csv"] + table .add-column').on('click', function(e) {
		e.preventDefault()
		var table = $(this).closest('table')
		var title = prompt("Column Title")
		if (!title) return
		$(table).find('thead tr th:last-child:last-child').before('<th>'+ title +'</th>')
		$(table).find('tbody tr td:last-child:last-child').before('<td contenteditable></td>')
		$(table).find('tfoot tr td').attr('colspan', $(this).closest('table').find('tfoot tr td').attr('colspan') + 1)
		$(this).trigger('keyup')
	})

	$('textarea[data-toggle="csv"] + table').on('keyup', function(e) {
		var csv = $(this).find('thead tr, tbody tr').map(function (i, row) {
				return $(row).find('th:not(:last-child),td:not(:last-child)').map(function (j, col) {
					var text = $(col).text()
					if (/('|,)/.test(text)) {
						return '"'+ text.replace(/"/g, '""') +'"'
					} else {
						return text
					}
				}).get().join(',')
			}).get().join('\r\n')
		$(this).next('textarea').val(csv)
	})


	/* Form Input Tags */

	$('input[data-toggle="tags"]').each(function() {

		let $originalInput = $(this)

		let $tagField = $(
			'<div class="form-input">\
				<ul class="tokens">\
					<span class="input" contenteditable></span>\
				</ul>\
			</div>'
		)

		$tagField.tags = []

		$tagField.add = function(input){

			input = input.trim()

			if (!input) return

			$tagField.tags.push(input)

			let $tag = $(
				'<li class="tag">\
					<span class="value"></span>\
					<span class="remove">x</span>\
				</li>')

			$('.value', $tag).text(input)
			$('.input', $tagField).before($tag)

			$tagField.trigger('change')
		}

		$tagField.remove = function(input){

			$tagField.tags = $.grep($tagField.tags, function(value) {
				return value != input
			})

			$('.tag .value', $tagField).each(function() {
				if ($(this).text() == input) {
					$(this).parent('.tag').remove()
				}
			})

			$tagField.trigger('change')
		}

		let tags = $.grep($originalInput.val().split(/\s*,\s*/), function(value) {
			return value
		})

		$.each(tags, function() {
			$tagField.add(this)
		})

		$tagField.on('keypress', '.input', function(e) {
			if (e.which == 44 || e.which == 13) { // Comma or enter
				e.preventDefault()
				$tagField.add($(this).text())
				$(this).text('')
			}
		})

		$tagField.on('blur', '.input', function() {
			$tagField.add($(this).text())
			$(this).text('')
		})

		$tagField.on('click', '.remove', function(e) {
			$tagField.remove($(this).siblings('.value').text())
		})

		$tagField.on('change', function() {
			$originalInput.val($tagField.tags.join(','))
		})

		$(this).hide().after($tagField)
	})


// Alerts
$('body').on('click', '.alert .close', function(e) {
	e.preventDefault()
	$(this).closest('.alert').fadeOut('fast', function() {
		$(this).remove()
	})
})


// Filter
$('#sidebar input[name="filter"]').on({

	'input': function(){

		let query = $(this).val()

		if ($(this).val() == '') {
			$('#box-apps-menu .app').css('display', 'block')
			return
		}

		$('#box-apps-menu .app').each(function() {
			var regex = new RegExp(''+ query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')  +'', 'ig')
			console.log()
			if (regex.test($(this).text())) {
				$(this).show()
			} else {
				$(this).hide()
			}
		})
	}
})

// AJAX Search
let timer_ajax_search = null
let xhr_search = null

$('#search input[name="query"]').on({

	'focus': function(){
		if ($(this).val()) {
			$('#search.dropdown').addClass('open')
		}
	},

	'blur': function(){
		if (!$('#search').filter(':hover').length) {
			$('#search.dropdown').removeClass('open')
		} else {
			$('#search.dropdown').on('blur', function() {
				$('#search.dropdown').removeClass('open')
			})
		}
	},

	'input': function(){

		if (xhr_search) {
			xhr_search.abort()
		}

		let $searchField = $(this)

		if ($searchField.val()) {

			$('#search .results').html([
				'<div class="loader-wrapper text-center">',
				'  <div class="loader" style="width: 48px; height: 48px;"></div>',
				'</div>'
			].join('\n'))

			$('#search.dropdown').addClass('open')

		} else {
			$('#search .results').html('')
			$('#search.dropdown').removeClass('open')
			return
		}

		clearTimeout(timer_ajax_search)

		timer_ajax_search = setTimeout(function() {
			xhr_search = $.ajax({
				type: 'get',
				async: true,
				cache: false,
				url: window._env.backend.url + 'search_results.json?query=' + $searchField.val(),
				dataType: 'json',

				beforeSend: function(jqXHR) {
					jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'))
				},

				error: function(jqXHR, textStatus, errorThrown) {
					$('#search .results').text(textStatus + ': ' + errorThrown)
				},

				success: function(json) {

					$('#search .results').html('')

					if (!$('#search input[name="query"]').val()) {
						$('#search .results').html('Search')
						return
					}

					$.each(json, function(i, group) {

						if (group.results.length) {

							$('#search .results').append(
								'<h4>'+ group.name +'</h4>' +
								'<ul class="flex flex-rows" data-group="'+ group.name +'"></ul>'
							)

							$.each(group.results, function(i, result) {

								var $li = $([
									'<li class="result">',
									'  <a class="list-group-item" href="'+ result.link +'" style="border-inline-start: 3px solid '+ group.theme.color +'; background: '+ group.theme.color +'11;">',
									'    <small class="id float-end">#'+ result.id +'</small>',
									'    <div class="title">'+ result.title +'</div>',
									'    <div class="description"><small>'+ result.description +'</small></div>',
									'  </a>',
									'</li>'
								].join('\n'))

								$('#search .results ul[data-group="'+ group.name +'"]').append($li)
							})
						}
					})

					if ($('#search .results').html() == '') {
						$('#search .results').html('<p class="text-center no-results"><em>:(</em></p>')
					}
				},
			})
		}, 500)
	}
})


// Tabs (data-toggle="tab")
+function($) {
	'use strict'
	$.fn.Tabs = function(){
		this.each(function() {

			let self = this

			this.$element = $(this)

			this.$element.find('[data-toggle="tab"]').each(function() {
				let $link = $(this)

				$link.on('select', function() {
					self.$element.find('.active').removeClass('active')

					if ($link.hasClass('nav-link')) {
						$link.addClass('active')
					}

					$link.closest('.nav-item').addClass('active')

					$($link.attr('href')).show().siblings().hide()
				})

				$link.on('click', function(e) {
					e.preventDefault()
					history.replaceState(null, null, this.hash)
					$link.trigger('select')
				})
			})

			if (!this.$element.find('.active').length) {
				this.$element.find('[data-toggle="tab"]').first().select()
			} else {
				this.$element.find('[data-toggle="tab"].active').select()
			}
		})
	}

	$('.nav-tabs').Tabs()

	if (document.location.hash && document.location.hash.match(/^#tab-/)) {
		$('[data-toggle="tab"][href="' + document.location.hash +'"]').trigger('select')
	}

	$(document).on('ajaxcomplete', function() {
		$('.nav-tabs').Tabs()
	})
}(jQuery)


	// Data-Table Toggle Checkboxes
	$('body').on('click', '.data-table *[data-toggle="checkbox-toggle"], .data-table .checkbox-toggle', function() {
		$(this).closest('.data-table').find('tbody td:first-child :checkbox').each(function() {
			$(this).prop('checked', !$(this).prop('checked')).trigger('change')
		})
		return false
	})

	$('body').on('click', '.data-table tbody tr', function(e) {
		if ($(e.target).is('a') || $(e.target).closest('a').length) return
		if ($(e.target).is('.btn, :input, th, .icon-star, .icon-star-o')) return
		$(this).find(':checkbox, :radio').first().trigger('click')
	})

	// Data-Table Shift Check Multiple Checkboxes
	let lastTickedCheckbox = null
	$('.data-table td:first-child :checkbox').on('click', function(e) {

		let $chkboxes = $('.data-table td:first-child :checkbox')

		if (!lastTickedCheckbox) {
			lastTickedCheckbox = this
			return
		}

		if (e.shiftKey) {
			let start = $chkboxes.index(this)
			let end = $chkboxes.index(lastTickedCheckbox)
			$chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', lastTickedCheckbox.checked)
		}

		lastTickedCheckbox = this
	})

	// Data-Table Sorting (Page Reload)
	$('.table-sortable thead th[data-sort]').on('click', function() {
		let params = {}

		window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
			params[key] = value
		})

		params.sort = $(this).data('sort')

		window.location.search = $.param(params)
	})
