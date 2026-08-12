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


waitFor('jQuery', ($) => {
	'use strict';

	$.fn.inputCSV = function(config){

		const cfg = $.extend({
			text: {
				table: 'Table',
				raw: 'Raw',
				add_row: 'Add Row',
				add_column: 'Add Column',
				column_title: 'Column Title',
				remove: '<i class="icon-times" style="color: #d33;"></i>',
			},
			delimiter: 'auto',
			default_view: 'table',
		}, config);

		return this.each(function(){

			const $textarea = $(this);

			let $wrapper = $textarea.closest('.form-input-csv');

			if (!$wrapper.length) {
				$wrapper = $('<div class="form-input-csv"></div>');
				$textarea.wrap($wrapper);
			}

			const $tabs = $wrapper.find('.csv-view-tabs').length
				? $wrapper.find('.csv-view-tabs')
				: $('<div class="csv-view-tabs btn-group btn-group-sm" role="tablist"></div>').insertBefore($textarea);

			if (!$tabs.find('[data-view="table"]').length) {
				$('<button type="button" class="csv-view-tab btn btn-default btn-sm active" data-view="table">'+ cfg.text.table +'</button>').appendTo($tabs);
			}
			if (!$tabs.find('[data-view="raw"]').length) {
				$('<button type="button" class="csv-view-tab btn btn-default btn-sm" data-view="raw">'+ cfg.text.raw +'</button>').appendTo($tabs);
			}

			let $table = $wrapper.find('table.csv-table');
			if (!$table.length) {
				$table = $([
					'<table class="table csv-table" style="display: none;">',
					'	<thead><tr></tr></thead>',
					'	<tbody></tbody>',
					'	<tfoot>',
					'		<tr><td colspan="0">',
					'			<button type="button" class="add-row btn btn-default btn-sm"></button>',
					'		</td></tr>',
					'	</tfoot>',
					'</table>'
				].join('\n')).insertAfter($textarea);
				$table.find('.add-row').text(cfg.text.add_row);
			}

			function detectDelimiter(text){
				const lines = (text || '').split(/\r?\n/).filter(line => line.trim().length);
				if (!lines.length) return ',';

				const candidates = ['\t', '|', ';', ','];
				for (const delimiter of candidates) {
					if (lines[0].includes(delimiter)) return delimiter;
				}

				return ',';
			}

			function getDelimiter(text){
				return cfg.delimiter === 'auto' ? detectDelimiter(text) : cfg.delimiter;
			}

			// Parse textarea CSV → { columns, rows }
			function parse(text){
				const rows = [];
				const delimiter = getDelimiter(text);
				const lines = (text || '').split(/\r?\n/).filter(l => l.length);
				if (!lines.length) return { columns: [], rows: [] };

				const parseLine = (line) => {
					const out = []; let cur = ''; let inQ = false;
					for (let i = 0; i < line.length; i++){
						const c = line[i];
						if (inQ){
							if (c === '"' && line[i+1] === '"'){ cur += '"'; i++; }
							else if (c === '"'){ inQ = false; }
							else { cur += c; }
						} else {
							if (c === '"') inQ = true;
							else if (c === delimiter){ out.push(cur); cur = ''; }
							else cur += c;
						}
					}
					out.push(cur);
					return out;
				};

				const cells = lines.map(parseLine);
				const columns = cells.shift() || [];
				for (const row of cells){
					const obj = {};
					columns.forEach((col, i) => obj[col] = row[i] ?? '');
					rows.push(obj);
				}
				return { columns, rows };
			}

			// Serialize table → CSV text
			function serialize(){
				const delimiter = getDelimiter($textarea.val());
				const lines = [];
				const columnCount = $table.find('thead tr th').not('.csv-header-actions').length || 0;
				$table.find('thead tr, tbody tr').each(function(){
					const cells = $(this).find('th:not(.csv-header-actions),td:not(:last-child)').map(function(){
						let text = $(this).text();
						if (text.indexOf('"') !== -1 || text.indexOf(delimiter) !== -1 || text.indexOf('\n') !== -1){
							text = '"' + text.replace(/"/g, '""') + '"';
						}
						return text;
					}).get();
					if (cells.length || $(this).is('tbody tr')) {
						lines.push(cells.join(delimiter));
					}
				});
				if (!lines.length && columnCount) {
					lines.push(Array(columnCount).fill('').join(delimiter));
				}
				return lines.join('\n');
			}

			// Render table from CSV text
			function render(){
				const { columns, rows } = parse($textarea.val());
				const hasHeaders = columns.length > 0;
				$table.data('has-headers', hasHeaders);

				const $thead = $table.find('thead').empty().append('<tr></tr>');
				columns.forEach(col => {
					if (hasHeaders) {
						$thead.find('tr').append('<th>'+ col +'</th>');
					} else {
						$thead.find('tr').append('<th contenteditable>'+ col +'<button type="button" name="remove_column" class="btn btn-default btn-sm">'+ cfg.remove_icon +'</button></th>');
					}
				});
				if (hasHeaders) {
					$thead.find('tr').append('<th class="csv-header-actions" aria-hidden="true"></th>');
				} else {
					$thead.find('tr').append([
						'<th>',
						'	<button type="button" class="add-column btn btn-default btn-sm"></button>',
						'</th>'
					].join('\n'));
					$table.find('.add-column').text(cfg.text.add_column);
				}

				const $tbody = $table.find('tbody').empty();
				if (rows.length){
					rows.forEach(row => {
						const $tr = $('<tr></tr>');
						columns.forEach(col => {
							$tr.append('<td contenteditable>'+ (row[col] ?? '') +'</td>');
						});
						$tr.append([
							'<td>',
							'	<button type="button" name="remove_row" class="btn btn-default btn-sm">',
								cfg.text.remove,
							'	</button>',
							'</td>'
						].join('\n'));
						$tbody.append($tr);
					});
				}

				$table.find('tfoot tr td').attr('colspan', columns.length + (hasHeaders ? 1 : 1));
			}

			function setView(view){
				const nextView = view === 'raw' ? 'raw' : 'table';
				if (nextView === 'table') {
					render();
					$textarea.hide();
					$table.show();
				} else {
					$textarea.val(serialize()).trigger('change');
					$table.hide();
					$textarea.show();
				}

				$tabs.find('.csv-view-tab').removeClass('active');
				$tabs.find('[data-view="'+ nextView +'"]').addClass('active');
			}

			$tabs.on('click', '.csv-view-tab', function(e){
				e.preventDefault();
				setView($(this).data('view'));
			});

			setView(cfg.default_view || 'table');

			$table.on('click', '.add-row', function(e){
				e.preventDefault();
				const n = $table.find('thead th:not(:last-child)').length;
				if (!n){
					$table.find('thead tr').append([
						'<th contenteditable>',
						'	<button type="button" name="remove_column" class="btn btn-default btn-sm">',
							cfg.text.remove,
						'	</button>',
						'</th>'
					].join('\n'));
				}
				const $tr = $('<tr></tr>');
				$table.find('thead th:not(:last-child)').each(function(){
					$tr.append('<td contenteditable></td>');
				});
				$tr.append([
					'<td>',
					'	<button type="button" name="remove_row" class="btn btn-default btn-sm">',
						cfg.text.remove,
					'	</button>',
					'</td>'
				].join('\n'));
				$table.find('tbody').append($tr);
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('click', '.add-column', function(e){
				e.preventDefault();
				if ($table.data('has-headers')) return;
				const title = prompt(cfg.text.column_title);

				if (!title) return;

				$table.find('thead tr th:last-child:last-child').before([
					'<th contenteditable>',
						title,
					'	<button type="button" name="remove_column" class="btn btn-default btn-sm">'+ cfg.text.remove +'</button>',
					'</th>'
				].join('\n'));

				$table.find('tbody tr').each(function(){
					$(this).find('td:last-child:last-child').before('<td contenteditable></td>');
				});

				const colspan = (parseInt($table.find('tfoot tr td').attr('colspan'), 10) || 0) + 1;
				$table.find('tfoot tr td').attr('colspan', colspan);
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('click', 'button[name="remove_row"]', function(e){
				e.preventDefault();
				$(this).closest('tr').remove();
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('click', 'button[name="remove_column"]', function(e){
				e.preventDefault();

				if ($table.data('has-headers')) return;

				const index = $(this).closest('th').index();

				$table.find('thead tr th').eq(index).remove();
				$table.find('tbody tr').each(function(){
					$(this).find('td').eq(index).remove();
				});

				const colspan = (parseInt($table.find('tfoot tr td').attr('colspan'), 10) || 0) - 1;

				$table.find('tfoot tr td').attr('colspan', colspan);
				$textarea.val(serialize()).trigger('change');
			});

			$table.on('input blur', '[contenteditable]', function(){
				$textarea.val(serialize()).trigger('change');
			});
		});
	};

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

						// Defense-in-depth: only allow http(s), mailto, root-relative or scheme-less
						// URLs as result links. Today all providers build links via document::ilink()
						// (relative), so this guards against future providers leaking javascript:,
						// data: or protocol-relative (//) URLs.
						function safe_link(url) {
							if (typeof url !== 'string') return '#';
							if (url.startsWith('//')) return '#';                          // Protocol-relative — inherits page scheme
							if (/^(https?:\/\/|mailto:)/i.test(url)) return url;           // Allowed absolute schemes
							if (url.startsWith('/')) return url;                           // Root-relative
							if (!/^[a-z][a-z0-9+.\-]*:/i.test(url)) return url;            // Scheme-less = relative
							return '#';                                                    // Everything else (javascript:, data:, vbscript:, ...)
						}

						$.each(json, function(i, group) {

							if (!group.results.length) return;

							// Build group header and list via DOM APIs so untrusted fields
							// (group.name, result.title, result.description, result.link)
							// can't break out of text/attribute context.
							var $heading = $('<h3>').text(group.name);
							var $ul = $('<ul>').addClass('flex flex-rows flex-nogap').attr('data-group', group.name);

							$('#search .results').append($heading).append($ul);

							$.each(group.results, function(i, result) {

								var $a = $('<a>')
									.addClass('list-group-item')
									.attr('href', safe_link(result.link))
									.css({
										'border-inline-start': '3px solid ' + group.theme.color,
										'background': group.theme.color + '11'
									});

								$('<small>').addClass('id float-end').text('#' + result.id).appendTo($a);
								$('<div>').addClass('title').text(result.title).appendTo($a);
								$('<div>').addClass('description').append($('<small>').text(result.description)).appendTo($a);

								$('<li>').addClass('result').append($a).appendTo($ul);
							});
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
