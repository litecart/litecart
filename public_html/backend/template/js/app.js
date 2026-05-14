/*!
	LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built built with for simplicity.
	Link: https://www.litecart.net/
	License: CC-BY-ND-4.0
	Author: T. Almroth, LiteCart AB
*/

waitFor('jQuery', ($) => {

	// CSRF token for AJAX requests
	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		if (!/^(GET|HEAD|OPTIONS)$/i.test(options.type) && window._env && window._env.csrf_token) {
			jqXHR.setRequestHeader('X-CSRF-Token', window._env.csrf_token);
		}
	});

});

waitFor('jQuery', ($) => {

	// Filter
	$('#sidebar input[name="filter"]').on({

		'input': function(){

			let query = $(this).val();

			if ($(this).val() == '') {
				$('#box-apps-menu .app').css('display', 'block');
				return;
			}

			$('#box-apps-menu .app').each(function() {
				var regex = new RegExp(''+ query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')  +'', 'ig');

				if (regex.test($(this).text())) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}
	});

});

waitFor('jQuery', ($) => {

	$.fn.categoryPicker = function(config){
		this.each(function() {

			let xhr = null;
			const cfg = config;

			const $self = $(this);

			$self.find('.dropdown input[type="search"]').on({

				'focus': function(e) {
					$self.find('.dropdown').addClass('open');
				},

				'input': function(e) {
						let $dropdownMenu = $self.find('.dropdown-content');

						$dropdownMenu.html('');

						if (xhr) {
							xhr.abort();
						}

						if ($(this).val() == '') {

							$.getJSON(cfg.link, function(result) {

								$dropdownMenu.html(
									'<h3 style="margin-top: 0;">'+ result.name +'</h3>'
								);

								$.each(result.subcategories, function(i, category) {
									$dropdownMenu.append([
										'<div class="category flex" style="align-items: center;" data-id="'+ category.id + '" data-name="'+ category.path.join(' &gt; ') + '">',
										'\t' + cfg.icons.folder + '<a href="#" data-link="'+ cfg.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
										'\t<button name="add" class="btn btn-default btn-sm" type="button">'+ cfg.translations.add +'</button>',
										'</div>',
									].join('\n'));
								});
							});

							return;
						}

						xhr = $.ajax({
							type: 'get',
							async: true,
							cache: true,
							url: cfg.link + '&query=' + $(this).val(),
							dataType: 'json',

							beforeSend: function(jqXHR) {
								jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
							},

							error: function(jqXHR, textStatus, errorThrown) {
								if (errorThrown == 'abort') return;
								alert(errorThrown);
							},

							success: function(result) {

								if (!result.subcategories?.length) {
									$dropdownMenu.html(
										'<div class="text-center no-results"><em>:(</em></div>'
										);
										return;
									}

									$dropdownMenu.html(
										'<h3 style="margin-top: 0;">'+ cfg.translations.search_results +'</h3>'
									);

									$.each(result.subcategories, function(i, category) {
										$dropdownMenu.append([
											'<div class="category flex" style="align-items: center;" data-id="'+ category.id + '" data-name="'+ category.path.join(' &gt; ') + '">',
											'\t' + cfg.icons.folder + '<a href="#" data-link="'+ cfg.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
											'\t<button name="add" class="btn btn-default btn-sm" type="button">'+ cfg.translations.add +'</button>',
											'</div>',
										].join('\n'));
									});
								},
							});
						}
			});

			$self.on('click', '.dropdown-content a', function(e) {
				e.preventDefault();

				let $dropdownMenu = $(this).closest('.dropdown-content');

				$.getJSON($(this).data('link'), function(result) {

						$dropdownMenu.html(
							'<h3 style="margin-top: 0;">'+ result.name +'</h3>'
						);

						if (result.parent) {
							$dropdownMenu.append([
								'<div class="flex" style="align-items: center;" data-id="'+ result.parent.id +'" data-name="'+ result.parent.name +'">',
								'\t' + cfg.icons.back + '<a href="#" data-link="'+ cfg.link +'?parent_id='+ result.parent.id +'" style="flex-grow: 1;">'+ result.parent.name +'</a>',
								'</div>',
							].join('\n'));
						}

						$.each(result.subcategories, function(i, category) {
							$dropdownMenu.append([
								'<div class="category flex" style="align-items: center;" data-id="'+ category.id +'" data-name="'+ category.path.join(' &gt; ') +'">',
								'\t' + cfg.icons.folder +' <a href="#" data-link="'+ cfg.link +'?parent_id='+ category.id +'" style="flex-grow: 1;">'+ category.name +'</a>',
								'\t<button name="add" class="btn btn-default btn-sm" type="button">'+ cfg.translations.add +'</button>',
								'</div>',
							].join('\n'));
						});
				});
			});

			$self.on('click', '.dropdown-content button[name="add"]', function(e) {
				e.preventDefault();

				let category = $(this).closest('.category').data(),
					abort = false;

				$self.find('input[name="'+ cfg.inputName +'"]').each(function() {
					if ($(this).val() == category.id) {
						abort = true;
						return;
					}
				});

				if (abort) return;

				let inputField = $('<input>', {
					type: 'hidden',
					name: cfg.inputName,
					value: category.id,
					"data-name": category.name
				})[0].outerHTML;

				$self.find('ul').append([
					'<li class="list-item flex">',
					'	<div style="flex-grow: 1;">',
					'		'+ inputField,
					'		'+ cfg.icons.folder +' '+ category.name,
					'	</div>',
					'	<button name="remove" class="btn btn-default btn-sm" type="button">',
					'		'+ cfg.translations.remove,
					'	</button>',
					'</li>',
				].join('\n'));

				$self.trigger('change');

				$('.dropdown.open').removeClass('open');

				return false;
			});

			$self.on('click', 'button[name="remove"]', function(e) {
				$(this).closest('li').remove();
				$self.trigger('change');
			});

			$('body').on('mousedown', function(e) {
				if ($('.dropdown.open').has(e.target).length === 0) {
					$('.dropdown.open').removeClass('open');
				}
			});

			$(this).find('input[type="search"]').trigger('input');
		});
	};

});


waitFor('jQuery', $ => {

	// CSV Input
	$('textarea[data-toggle="csv"] + table').on('click', 'button[name="remove"]', function (e) {
		e.preventDefault();
		var $parent = $(this).closest('tbody');
		$(this).closest('tr').remove();
		$parent.trigger('keyup');
	});

	$('textarea[data-toggle="csv"] + table .add-row').on('click', function (e) {
		e.preventDefault();
		var n = $(this).closest('table').find('thead th:not(:last-child)').length;
		$(this).closest('table').find('tbody').append([
			'<tr>',
			'  <td contenteditable></td>'.repeat(n),
			'  <td><button name="remove"><i class="icon-times" style="color: #d33;"></i></button></td>',
			'</tr>',
		].join('\n')).trigger('keyup');
	});

	$('textarea[data-toggle="csv"] + table .add-column').on('click', function (e) {
		e.preventDefault();
		let $table = $(this).closest('table'),
			title = prompt('Column Title');
		if (!title) return;
		$table.find('thead tr th:last-child:last-child').before('<th>' + title + '</th>');
		$table.find('tbody tr td:last-child:last-child').before('<td contenteditable></td>');
		$table.find('tfoot tr td').attr('colspan', $(this).closest('table').find('tfoot tr td').attr('colspan') + 1);
		$(this).trigger('keyup');
	});

	$('textarea[data-toggle="csv"] + table').on('keyup', function (e) {
		let $tr = $(this).find('thead tr, tbody tr').map(function (i, row) {
			return $(row).find('th:not(:last-child),td:not(:last-child)').map(function (j, col) {
				var text = $(col).text();
				if (/('|,)/.test(text)) {
					return '"' + text.replace(/"/g, '""') + '"';
				} else {
					return text;
				}
			}).get().join(',');
		}).get().join('\n');
		$(this).next('textarea').val($tr);
	});
});


waitFor('jQuery', $ => {

	// Keep-alive
	if (typeof _env !== 'undefined' && _env?.platform?.path) {
		setInterval(function() {
			$.get({
				url: _env.platform.path + 'ajax/keep_alive',
				cache: false
			});
		}, 60e3);
	}

});


waitFor('jQuery', ($) => {

	// Sidebar Filter
	$('#sidebar input[name="filter"]').on({

		'input': function(){

			let query = $(this).val();

			if ($(this).val() == '') {
				$('#box-apps-menu .app').css('display', 'block');
				return;
			}

			$('#box-apps-menu .app').each(function(){
				var regex = new RegExp(''+ query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')  +'', 'ig');
				console.log()
				if (regex.test($(this).text())) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}
	});

	// AJAX Search
	let timer_ajax_search = null;
	let xhr_search = null;

	$('#search input[name="query"]').on({

		'focus': function(){
			if ($(this).val()) {
				$('#search.dropdown').addClass('open');
			}
		},

		'blur': function(){
			if (!$('#search').filter(':hover').length) {
				$('#search.dropdown').removeClass('open');
			} else {
				$('#search.dropdown').on('blur', function() {
					$('#search.dropdown').removeClass('open');
				});
			}
		},

		'input': function(){

			if (xhr_search) {
				xhr_search.abort();
			}

			let $searchField = $(this);

			if ($searchField.val()) {

				$('#search .results').html([
					'<div class="loader-wrapper text-center">',
					'  <div class="loader" style="width: 48px; height: 48px;"></div>',
					'</div>'
				].join('\n'));

				$('#search.dropdown').addClass('open');

			} else {
				$('#search .results').html('');
				$('#search.dropdown').removeClass('open');
				return;
			}

			clearTimeout(timer_ajax_search);

			timer_ajax_search = setTimeout(function() {
				xhr_search = $.ajax({
					type: 'get',
					async: true,
					cache: false,
					url: _env.backend.url + 'search_results.json?query=' + $searchField.val(),
					dataType: 'json',

					beforeSend: function(jqXHR) {
						jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
					},

					error: function(jqXHR, textStatus, errorThrown) {
						$('#search .results').text(textStatus + ': ' + errorThrown);
					},

					success: function(json) {

						$('#search .results').html('');

						if (!$('#search input[name="query"]').val()) {
							$('#search .results').html('Search');
							return;
						}

						$.each(json, function(i, group) {

							if (group.results.length) {

								$('#search .results').append(
									'<h3>'+ group.name +'</h3>' +
									'<ul class="flex flex-rows flex-nogap" data-group="'+ group.name +'"></ul>'
								);

								$.each(group.results, function(i, result) {

									var $li = $([
										'<li class="result">',
										'  <a class="list-group-item" href="'+ result.link +'" style="border-inline-start: 3px solid '+ group.theme.color +'; background: '+ group.theme.color +'11;">',
										'    <small class="id float-end">#'+ result.id +'</small>',
										'    <div class="title">'+ result.title +'</div>',
										'    <div class="description"><small>'+ result.description +'</small></div>',
										'  </a>',
										'</li>'
									].join('\n'));

									$('#search .results ul[data-group="'+ group.name +'"]').append($li);
								});
							}
						});

						if ($('#search .results').html() == '') {
							$('#search .results').html('<p class="text-center no-results"><em>:(</em></p>');
						}
					},
				});
			}, 500);
		}
	});

});


waitFor('jQuery', function($){

	$('button[name="font_size"]').on('click', function(){
		let new_size = parseInt($(':root').css('--default-text-size').split('px')[0]) + (($(this).val() == 'increase') ? 1 : -1);
		$(':root').css('--default-text-size', new_size + 'px');
		document.cookie = `font_size=${new_size}; Path=${_env.platform.path}; Max-Age=2592000;`;
	});

	$('input[name="dark_mode"]').on('click', function(){
		if ($(this).val() == 1) {
			document.cookie = `dark_mode=1; Path=${_env.platform.path}; Max-Age=2592000;`;
			$('html').addClass('dark-mode');
		} else {
			document.cookie = `dark_mode=0; Path=${_env.platform.path}; Max-Age=2592000;`;
			$('html').removeClass('dark-mode');
		}
	});

});
