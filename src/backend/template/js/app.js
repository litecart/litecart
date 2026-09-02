/*!
	LiteCart v3.0.0 - Superfast, lightweight e-commerce platform built built with for simplicity.
	Link: https://www.litecart.net/
	License: CC-BY-ND-4.0
	Author: T. Almroth, LiteCart AB
*/

// LiteCart Ask AI - chat workspace client
// Dependency-free; uses the global $ (jQuery) loaded by the backend shell.

waitFor('jQuery', ($) => {

	const app = $('.ai-chat-app');
	if (!app.length) return;

	const state = {
		conversationId: parseInt(app.attr('data-conversation-id') || $('#ai-conversation-id').val() || '0', 10),
		isSending: false,
		endpoint: document.querySelector('meta[name="admin-url"]')?.content || '',
	};

	// ---------- Helpers ----------

	function post(action, data = {}) {
		return $.ajax({
			url: window.location.pathname,
			method: 'POST',
			data: $.extend({ action }, data),
			dataType: 'json',
		});
	}

	function escapeHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function renderMessage(role, content) {
		let inner;
		if (role === 'tool_use') {
			const parts = (content || '').split(' ');
			const tool = parts[0] || '';
			const args = parts.slice(1).join(' ') || '{}';
			inner = `<details class="ai-chat-tool"><summary>${escapeHtml(tool)}</summary><pre>${escapeHtml(args)}</pre></details>`;
		} else if (role === 'tool_result') {
			inner = `<details class="ai-chat-tool"><summary>Tool result</summary><pre>${escapeHtml(content)}</pre></details>`;
		} else {
			inner = `<pre class="ai-chat-text">${escapeHtml(content)}</pre>`;
		}
		return `<div class="ai-chat-message ai-chat-message-${escapeHtml(role)}"><div class="ai-chat-message-content">${inner}</div></div>`;
	}

	function appendMessage(role, content) {
		const $messages = $('#ai-chat-messages');
		const $empty = $('.ai-chat-empty', $messages);
		if ($empty.length) $empty.remove();
		$messages.append(renderMessage(role, content));
		$messages.scrollTop($messages[0].scrollHeight);
	}

	// ---------- New chat ----------

	$('#ai-new-chat').on('click', function () {
		post('create_conversation').done((res) => {
			if (res.ok && res.conversation_id) {
				window.location.href = window.location.pathname + '?doc=agent&app=ai&conversation_id=' + res.conversation_id;
			}
		}).fail((xhr) => {
			alert(xhr.responseJSON?.error || 'Failed to create conversation');
		});
	});

	// ---------- Composer ----------

	const $input = $('#ai-chat-input');
	const $send  = $('#ai-chat-send');

	$input.on('input', function () {
		this.style.height = 'auto';
		this.style.height = Math.min(this.scrollHeight, 200) + 'px';
		this.value = this.value; // keep caret
		$send.prop('disabled', !this.value.trim() || state.isSending);
	});

	$input.on('keydown', function (e) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			$('#ai-chat-composer').trigger('submit');
		}
	});

	$('#ai-chat-composer').on('submit', function (e) {
		e.preventDefault();
		if (state.isSending) return;
		const text = $input.val().trim();
		if (!text || !state.conversationId) return;

		state.isSending = true;
		$send.prop('disabled', true);
		appendMessage('user', text);
		$input.val('').trigger('input');

		// Append a placeholder for the assistant reply
		const $placeholder = $('<div class="ai-chat-message ai-chat-message-assistant ai-chat-thinking"><div class="ai-chat-message-content"><span class="ai-chat-typing"></span><span class="ai-chat-typing"></span><span class="ai-chat-typing"></span></div></div>');
		$('#ai-chat-messages').append($placeholder);
		$('#ai-chat-messages').scrollTop($('#ai-chat-messages')[0].scrollHeight);

		post('send', {
			conversation_id: state.conversationId,
			message: text,
		}).done((res) => {
			$placeholder.remove();
			if (!res.ok) {
				appendMessage('assistant', '⚠️ ' + (res.error || 'Unknown error'));
				return;
			}
			appendMessage('assistant', res.text || '(empty reply)');

			// Update sidebar title after first exchange
			if (res.title) {
				const $link = $(`.ai-chat-conversation[data-conversation-id="${state.conversationId}"] .ai-chat-conversation-title`);
				$link.text(res.title);
				$('.ai-chat-title').first().text(res.title);
			}
		}).fail((xhr) => {
			$placeholder.remove();
			appendMessage('assistant', '⚠️ ' + (xhr.responseJSON?.error || 'Network error'));
		}).always(() => {
			state.isSending = false;
			$send.prop('disabled', !$input.val().trim());
			$input.focus();
		});
	});

	// ---------- Rename / Delete ----------

	$('#ai-conversation-list').on('click', '.ai-chat-rename', function (e) {
		e.preventDefault();
		e.stopPropagation();
		const $link = $(this).closest('.ai-chat-conversation');
		const id = $link.data('conversation-id');
		const current = $link.find('.ai-chat-conversation-title').text();
		const next = prompt('Rename conversation', current);
		if (next === null || next.trim() === '') return;
		post('rename_conversation', { conversation_id: id, title: next.trim() }).done(() => {
			$link.find('.ai-chat-conversation-title').text(next.trim());
			if (state.conversationId === id) {
				$('.ai-chat-title').first().text(next.trim());
			}
		});
	});

	$('#ai-conversation-list').on('click', '.ai-chat-delete', function (e) {
		e.preventDefault();
		e.stopPropagation();
		const $link = $(this).closest('.ai-chat-conversation');
		const id = $link.data('conversation-id');
		if (!confirm('Delete this conversation and all its messages?')) return;
		post('delete_conversation', { conversation_id: id }).done(() => {
			$link.remove();
			if (state.conversationId === id) {
				window.location.href = window.location.pathname + '?doc=agent&app=ai';
			}
		});
	});

	// ---------- Starter buttons ----------

	$('.ai-chat-starter').on('click', function () {
		const text = $(this).text();
		$input.val(text).trigger('input').focus();
		if (state.conversationId) {
			$('#ai-chat-composer').trigger('submit');
		} else {
			post('create_conversation').done((res) => {
				if (res.ok && res.conversation_id) {
					window.location.href = window.location.pathname + '?doc=agent&app=ai&conversation_id=' + res.conversation_id;
				}
			});
		}
	});

	// Focus composer on load (only if a conversation is active)
	if (state.conversationId) {
		$input.focus();
	}

});

waitFor('jQuery', ($) => {

	// CSRF token for AJAX requests
	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		if (!/^(GET|HEAD|OPTIONS)$/i.test(options.type) && window._env && window._env.csrf_token) {
			jqXHR.setRequestHeader('X-CSRF-Token', window._env.csrf_token);
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


// LiteCart POS (Point of Sale) client
// Loaded by src/backend/apps/pos/pos.inc.php after a small PHP bootstrap that
// injects window.__posConfig with all server-side strings and URLs.

waitFor('jQuery', ($) => {

	if (!$('#point-of-sales').length) return;

	const cfg = window.__posConfig || {};
	const POS_CURRENCY = cfg.currency || 'USD';
	const POS_CUSTOMER_DISPLAY_URL = cfg.customerDisplayUrl || '';
	const POS_BASE_URL = cfg.baseUrl || '';
	const POS_T = cfg.translations || {};
	const POS_ICONS = cfg.icons || {};
	let activeSlot = parseInt(cfg.activeSlot, 10) || 1;

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	function escapeHtml(value) {
		return $('<div>').text(value == null ? '' : value).html();
	}

	function formatMoney(value, currency) {
		try {
			return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency }).format(value);
		} catch (e) {
			return currency + ' ' + Number(value).toFixed(2);
		}
	}

	function formatPriceTag(regular, final) {

		const r = Number(regular) || 0;
		const f = Number(final) || 0;

		if (r && f && r !== f) {
			return '<del>' + formatMoney(r, POS_CURRENCY) + '</del> <strong>' + formatMoney(f, POS_CURRENCY) + '</strong>';
		}
		return formatMoney(r || f, POS_CURRENCY);
	}

	function formatTimestamp(date) {
		const pad = n => String(n).padStart(2, '0');
		return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
			+ ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
	}

	// -------------------------------------------------------------------------
	// Customer display iframe sizing
	// -------------------------------------------------------------------------

	$(window).on('resize', function() {
		$('#customer-display').css({
			'aspect-ratio': screen.width + '/' + screen.height,
			'zoom': $('#customer-display').width() / screen.width
		});
	}).trigger('resize');

	$customerDisplay = $('#customer-display')[0].contentWindow;

	$('button[name="open_customer_display"]').on('click', function() {
		window.open(POS_CUSTOMER_DISPLAY_URL, 'customer_display', 'width=640,height=480,location=no');
	});

	// -------------------------------------------------------------------------
	// Barcode scanner toggle persistence
	// -------------------------------------------------------------------------

	if (localStorage.getItem('pos_barcode_scanner_enabled') === 'false') {
		$('#barcode-scanner-toggle').prop('checked', false);
	}

	$('#barcode-scanner-toggle').on('change', function() {
		localStorage.setItem('pos_barcode_scanner_enabled', $(this).is(':checked'));
	});

	// -------------------------------------------------------------------------
	// AJAX product search
	// -------------------------------------------------------------------------

	let xhrSearch;
	$('input[name="query"]').on('input', function() {

		const query = $(this).val().trim();

		if (xhrSearch) {
			xhrSearch.abort();
		}

		if (!query) {
			// Restore the initial server-rendered list of products
			$('#results-list').html(window.__posInitialResults || '');
			return;
		}

		xhrSearch = $.ajax({
			url: window.location.href.split('?')[0],
			type: 'get',
			data: { query: query },
			dataType: 'json',
			success: function(data) {
				if (data && data.error) {
					console.error('Search error:', data.error);
					return;
				}
				renderResults(data || []);
			},
			complete: function() {
				xhrSearch = null;
			}
		});
	});

	// Cache the initial server-rendered #results-list so clearing the search restores it
	window.__posInitialResults = $('#results-list').html();

	function renderResults(products) {

		const $list = $('#results-list');
		$list.empty();

		if (!products.length) {
			$list.append(
				$('<div class="no-results">').text(POS_T.noProductsFound || 'No products found')
			);
			return;
		}

		const pinnedIds = $('#pinned-products .product').map(function() {
			return String($(this).data('product-id'));
		}).get();

		for (const product of products) {

			const id = product.id;
			const isPinned = pinnedIds.indexOf(String(id)) !== -1;

			const $card = $('<div class="product">')
				.attr('data-product-id', id)
				.attr('data-regular-price', product.regular_price || 0)
				.attr('data-final-price', product.final_price || 0)
				.append(
					$('<img>').attr('src', product.thumbnail_url || (product.image ? 'storage/images/' + product.image : 'images/no_image.png')).attr('alt', '')
				)
				.append(
					$('<div>').css('flex-grow', 1)
						.append($('<div class="name">').text(product.name || ''))
						.append($('<div class="code">').text(product.code || ''))
						.append($('<div class="price">').html(formatPriceTag(product.regular_price, product.final_price)))
						.append(
							$('<div class="actions">')
								.append(
									$('<label class="pin-toggle">').attr('title', POS_T.pin || 'Pin')
										.append(
											$('<input type="checkbox">')
												.attr('name', 'pinned[]')
												.attr('value', id)
												.attr('data-product-id', id)
												.prop('checked', !!isPinned)
										)
										.append($('<span class="pin-toggle-icon">').html(POS_ICONS.thumbtack || ''))
								)
								.append(
									$('<button class="btn btn-default">')
										.attr('data-action', 'add-to-cart')
										.attr('data-product-id', id)
										.html(POS_ICONS.cart || '')
								)
						)
				);

			$list.append($card);
		}
	}

	// -------------------------------------------------------------------------
	// Pin / unpin
	// -------------------------------------------------------------------------

	$(document).on('change', '#point-of-sales .pin-toggle input[type="checkbox"]', function() {

		const $checkbox = $(this);
		const productId = $checkbox.data('product-id');
		const pinned = $checkbox.is(':checked');

		if (!productId) return;

		const $icon = $checkbox.siblings('.pin-toggle-icon');
		$icon.addClass('is-thumping');
		setTimeout(() => $icon.removeClass('is-thumping'), 350);

		$.ajax({
			url: POS_BASE_URL.replace(/\/$/, '') + '/admin/pos/pos',
			type: 'post',
			data: {
				product_id: productId,
				pinned: pinned ? 1 : 0,
			},
			dataType: 'json',
		}).done(function(response) {

			if (!response || response.error) {
				$checkbox.prop('checked', !pinned);
				console.error('Pin update failed:', response && response.error);
				return;
			}

			if (pinned) {
				pinProduct($checkbox, productId);
			} else {
				unpinProduct(productId);
			}

		}).fail(function() {
			$checkbox.prop('checked', !pinned);
			console.error('Pin update request failed');
		});
	});

	function pinProduct($sourceCheckbox, productId) {

		const $sourceCard = $sourceCheckbox.closest('.product');

		if ($('#pinned-products .product[data-product-id="' + productId + '"]').length) {
			return;
		}

		const $newCard = $('<div class="product is-pinning">')
			.attr('data-product-id', productId)
			.html($sourceCard.html());

		$newCard.find('input[type="checkbox"]').prop('checked', true);
		$newCard.find('.pin-toggle').attr('title', POS_T.unpin || 'Unpin');

		$('#pinned-products').append($newCard);

		$newCard.one('animationend', function() {
			$(this).removeClass('is-pinning');
		});
	}

	function unpinProduct(productId) {

		const $card = $('#pinned-products .product[data-product-id="' + productId + '"]');
		if (!$card.length) return;

		$card.addClass('is-unpinning');

		$('#results-list input[type="checkbox"][data-product-id="' + productId + '"]').prop('checked', false);

		$card.one('animationend', function() {
			$(this).remove();
		});
	}

	// -------------------------------------------------------------------------
	// Cart: in-memory store, rendering, animations, qty & price editing.
	// -------------------------------------------------------------------------

	const TAX_RATE = 0.25;
	const cart = { items: [] };

	function findCartItem(productId) {
		return cart.items.find(it => String(it.id) === String(productId));
	}

	function addToCart(product) {

		const existing = findCartItem(product.id);
		if (existing) {
			existing.qty += 1;
		} else {
			cart.items.push({
				id: product.id,
				name: product.name || '',
				code: product.code || '',
				image: product.image || 'images/no_image.png',
				qty: 1,
				unitPrice: Number(product.final_price || product.regular_price) || 0,
				regularPrice: Number(product.regular_price) || 0,
			});
		}

		renderCart({ flashNew: !existing ? product.id : null });
		syncCartToServer();
	}

	function changeQty(productId, delta) {

		const item = findCartItem(productId);
		if (!item) return;

		item.qty = Math.max(0, item.qty + delta);

		if (item.qty === 0) {
			cart.items = cart.items.filter(it => String(it.id) !== String(productId));
		}

		renderCart({ flashUpdated: productId });
		syncCartToServer();
	}

	function setUnitPrice(productId, value) {

		const item = findCartItem(productId);
		if (!item) return;

		const parsed = parseFloat(value);
		item.unitPrice = isNaN(parsed) || parsed < 0 ? 0 : parsed;

		renderCart({ flashUpdated: productId });
		syncCartToServer();
	}

	function removeFromCart(productId) {
		cart.items = cart.items.filter(it => String(it.id) !== String(productId));
		renderCart();
		syncCartToServer();
	}

	// Fire-and-forget: persists the current cart to session::$data['pos'][active_slot]['cart'].
	function syncCartToServer() {

		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'post',
			data: {
				cart_sync: 1,
				slot: activeSlot,
				items: serializeCartForServer(),
			},
			dataType: 'json',
		}).done(function(response) {
			if (!response || response.error) {
				console.error('Cart sync failed:', response && response.error);
			}
		});
	}

	function getCartTotals() {
		let subtotal = 0;
		for (const it of cart.items) {
			subtotal += it.qty * it.unitPrice;
		}
		const tax = subtotal * TAX_RATE;
		const total = subtotal + tax;
		return { subtotal, tax, total };
	}

	function renderCart(opts = {}) {

		const $list = $('#cart-lines');
		const $empty = $('#cart-empty');
		const $cart = $('#cart');

		$list.empty();

		if (!cart.items.length) {
			$empty.show();
		} else {
			$empty.hide();
		}

		for (const it of cart.items) {

			const lineTotal = it.qty * it.unitPrice;

			const $row = $('<div class="line-item">')
				.attr('data-product-id', it.id)
				.append($('<img>').attr('src', it.image).attr('alt', ''))
				.append(
					$('<div class="line-info">')
						.append($('<span class="line-name">').text(it.name))
						.append(
							$('<span class="line-meta">')
								.append(
									$('<span class="line-qty">')
										.append($('<button type="button" data-action="dec">').html('-'))
										.append($('<span class="qty-value">').text(it.qty))
										.append($('<button type="button" data-action="inc">').html('+'))
								)
								.append(
									$('<span>')
										.append(' × ')
										.append(
											$('<input type="number" class="line-price-input">')
												.attr('step', '0.01')
												.attr('min', '0')
												.val(it.unitPrice.toFixed(2))
												.attr('aria-label', 'Unit price')
										)
								)
						)
				)
				.append(
					$('<div class="line-total">')
						.append($('<span class="line-total-amount">').text(formatMoney(lineTotal, POS_CURRENCY)))
						.append(
							$('<span class="line-remove" title="Remove">').html(POS_ICONS.times || '')
						)
				);

			if (opts.flashNew && String(opts.flashNew) === String(it.id)) {
				$row.addClass('is-cart-popping');
				$row.one('animationend', function() {
					$(this).removeClass('is-cart-popping');
				});
			}

			$list.append($row);
		}

		const totals = getCartTotals();
		$('#cart-subtotal').html(formatMoney(totals.subtotal, POS_CURRENCY));
		$('#cart-tax').html(formatMoney(totals.tax, POS_CURRENCY));
		$('#cart-total').html('<strong>' + formatMoney(totals.total, POS_CURRENCY) + '</strong>');
		$('#cart-timestamp').text(formatTimestamp(new Date()));

		renderReceipt();

		if (opts.flashNew) {
			$cart.addClass('is-flashing');
			setTimeout(() => $cart.removeClass('is-flashing'), 600);
		}

		if (opts.flashUpdated) {
			const $row = $list.find('.line-item[data-product-id="' + opts.flashUpdated + '"]');
			if ($row.length) {
				$row.attr('id', 'line-item-updated');
				setTimeout(() => $row.removeAttr('id'), 600);
			}
		}
	}

	// -------------------------------------------------------------------------
	// Receipt rendering (mirrors the cart)
	// -------------------------------------------------------------------------

	function renderReceipt() {

		const $lines = $('#receipt-lines');
		if (!$lines.length) return;

		$lines.empty();

		if (!cart.items.length) {
			return;
		}

		for (const it of cart.items) {
			$lines.append($('<dt>').text(it.qty + ' × ' + it.name));
			$lines.append($('<dd>').text(formatMoney(it.qty * it.unitPrice, POS_CURRENCY)));
		}

		const totals = getCartTotals();
		const taxPercent = Math.round(TAX_RATE * 100);

		$lines.append($('<dt>').html('<strong>' + escapeHtml(POS_T.total || 'Total') + '</strong>'));
		$lines.append($('<dd>').html('<strong>' + formatMoney(totals.total, POS_CURRENCY) + '</strong>'));

		$lines.append($('<dt>').text('Tax (' + taxPercent + '%)'));
		$lines.append($('<dd>').text(formatMoney(totals.tax, POS_CURRENCY)));

		$('#receipt-timestamp').text(formatTimestamp(new Date()));
	}

	// -------------------------------------------------------------------------
	// Add-to-cart interaction
	// -------------------------------------------------------------------------

	$(document).on('click', '#point-of-sales [data-action="add-to-cart"]', function(e) {
		e.preventDefault();

		const $btn = $(this);
		const productId = $btn.data('product-id');
		const $card = $btn.closest('.product');

		const product = readProductFromCard($card, productId);
		if (!product) return;

		const $fly = $card.clone();
		$fly.removeClass('is-pinning is-unpinning is-cart-popping');
		$fly.addClass('is-flying-to-cart');
		const offset = $card.offset();
		$fly.css({
			position: 'absolute',
			top: offset.top,
			left: offset.left,
			width: $card.outerWidth(),
			height: $card.outerHeight(),
			margin: 0,
			zIndex: 9999,
			pointerEvents: 'none',
		});
		$('body').append($fly);
		$fly.one('animationend', function() {
			$(this).remove();
		});

		addToCart(product);
	});

	function readProductFromCard($card, productId) {

		if (!$card.length) return null;

		return {
			id: productId,
			name: $card.find('.name').first().text().trim(),
			code: $card.find('.code').first().text().trim(),
			image: $card.find('img').first().attr('src') || 'images/no_image.png',
			regular_price: parseFloat($card.data('regular-price')) || 0,
			final_price: parseFloat($card.data('final-price')) || 0,
		};
	}

	// -------------------------------------------------------------------------
	// Cart row interactions
	// -------------------------------------------------------------------------

	$(document).on('click', '#cart-lines [data-action="inc"], #cart-lines [data-action="dec"]', function(e) {
		e.preventDefault();
		const $btn = $(this);
		const productId = $btn.closest('.line-item').data('product-id');
		const delta = $btn.data('action') === 'inc' ? 1 : -1;
		changeQty(productId, delta);
	});

	$(document).on('click', '#cart-lines .line-remove', function(e) {
		e.preventDefault();
		const productId = $(this).closest('.line-item').data('product-id');
		removeFromCart(productId);
	});

	$(document).on('change input', '#cart-lines .line-price-input', function() {
		const $input = $(this);
		const productId = $input.closest('.line-item').data('product-id');
		setUnitPrice(productId, $input.val());
	});

	// -------------------------------------------------------------------------
	// Session slots: park/recall the cart in one of 4 memory slots
	// - Short click on a slot   → save current cart there AND load that slot
	// - Right-click (or Shift+click) → clear the slot
	// -------------------------------------------------------------------------

	function serializeCartForServer() {
		return cart.items.map(it => ({
			id: it.id,
			name: it.name,
			code: it.code,
			image: it.image,
			qty: it.qty,
			unitPrice: it.unitPrice,
			regularPrice: it.regularPrice,
		}));
	}

	function loadCartFromItems(items) {
		cart.items = (items || []).map(it => ({
			id: it.id,
			name: it.name || '',
			code: it.code || '',
			image: it.image || 'images/no_image.png',
			qty: Number(it.qty) || 0,
			unitPrice: Number(it.unitPrice) || 0,
			regularPrice: Number(it.regularPrice) || 0,
		}));
		renderCart();
	}

	function updateSlotBadges() {
		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'get',
			data: { session_list: 1 },
			dataType: 'json',
		}).done(function(response) {
			if (!response || !response.slots) return;
			if (response.active_slot) {
				activeSlot = parseInt(response.active_slot, 10) || activeSlot;
			}
			for (let slot = 1; slot <= 4; slot++) {
				const count = response.slots[slot]?.count || 0;
				const $btn = $('.session-slot[data-session-slot="' + slot + '"]');
				if (!$btn.length) continue;
				$btn.toggleClass('has-data', count > 0);
				$btn.toggleClass('is-active', slot === activeSlot);
				let $badge = $btn.find('.tile-badge');
				if (count > 0) {
					if (!$badge.length) {
						$badge = $('<span class="tile-badge">').text(count);
						$btn.append($badge);
					} else {
						$badge.text(count);
					}
				} else {
					$badge.remove();
				}
			}
		});
	}

	function setActiveSlot(slot) {

		activeSlot = slot;

		// Visual: clear is-active on all, set on target
		$('.session-slot').removeClass('is-active');
		$('.session-slot[data-session-slot="' + slot + '"]').addClass('is-active');

		// Persist
		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'post',
			data: { session_set_active: 1, slot: slot },
			dataType: 'json',
		});

		// Fetch the new slot's stored cart and load it
		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'get',
			data: { cart_get: 1 },
			dataType: 'json',
		}).done(function(response) {
			if (response && response.success) {
				loadCartFromItems(response.items || []);
			}
		});
	}

	function saveToSlot(slot, $btn) {

		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'post',
			data: {
				session_save: 1,
				slot: slot,
				items: serializeCartForServer(),
			},
			dataType: 'json',
		}).done(function(response) {
			if (!response || response.error) {
				console.error('Session save failed:', response && response.error);
				return;
			}
			if ($btn) {
				$btn.addClass('is-saving');
				setTimeout(() => $btn.removeClass('is-saving'), 450);
			}
			updateSlotBadges();
		});
	}

	function loadFromSlot(slot, $btn) {

		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'post',
			data: {
				session_load: 1,
				slot: slot,
			},
			dataType: 'json',
		}).done(function(response) {
			if (!response || response.error) {
				console.error('Session load failed:', response && response.error);
				return;
			}
			loadCartFromItems(response.items || []);
			if ($btn) {
				$btn.addClass('is-loading');
				setTimeout(() => $btn.removeClass('is-loading'), 550);
			}
		});
	}

	function clearSlot(slot, $btn) {

		$.ajax({
			url: window.location.href.split('?')[0],
			type: 'post',
			data: {
				session_clear: 1,
				slot: slot,
			},
			dataType: 'json',
		}).done(function(response) {
			if (!response || response.error) {
				console.error('Session clear failed:', response && response.error);
				return;
			}
			updateSlotBadges();
		});
	}

	$(document).on('click', '.session-slot', function(e) {

		e.preventDefault();
		const $btn = $(this);
		const slot = parseInt($btn.data('session-slot'), 10);
		if (!slot || slot < 1 || slot > 4) return;

		// Shift-click → clear the slot only (don't change active slot)
		if (e.shiftKey) {
			clearSlot(slot, $btn);
			return;
		}

		// Clicking a slot makes it the active slot and loads its cart
		// into the current cart (replacing whatever was there).
		// The "cart" itself is autosaved to session::$data['pos'][active_slot]['cart']
		// on every change, so no manual save step is required.
		setActiveSlot(slot);
	});

	$(document).on('contextmenu', '.session-slot', function(e) {
		e.preventDefault();
		const $btn = $(this);
		const slot = parseInt($btn.data('session-slot'), 10);
		if (!slot) return;
		clearSlot(slot, $btn);
	});

	// Hydrate the cart from the active slot on page load so the UI
	// reflects whatever was persisted server-side.
	$.ajax({
		url: window.location.href.split('?')[0],
		type: 'get',
		data: { cart_get: 1 },
		dataType: 'json',
	}).done(function(response) {
		if (response && response.success) {
			loadCartFromItems(response.items || []);
		}
	});

	// Refresh slot badges so the active marker + counts match the server.
	updateSlotBadges();

	// -------------------------------------------------------------------------
	// Barcode scanner
	// Only intercepts keystrokes when no input/textarea/select is focused,
	// so the search field always receives normal typing.
	// -------------------------------------------------------------------------

	let barcodeBuffer = '';
	let barcodeTimeout = null;
	const BARCODE_TIMEOUT = 100;

	$(document).on('keydown', function(e) {

		if (!$('#barcode-scanner-toggle').is(':checked')) {
			return;
		}

		if ($(e.target).is('input, textarea, select, [contenteditable]')) {
			return;
		}

		if (barcodeTimeout) {
			clearTimeout(barcodeTimeout);
		}

		if (
			(e.keyCode >= 48 && e.keyCode <= 57) ||
			(e.keyCode >= 65 && e.keyCode <= 90) ||
			(e.keyCode >= 96 && e.keyCode <= 105) ||
			e.keyCode === 189 || e.keyCode === 109 ||
			e.keyCode === 190 || e.keyCode === 110 ||
			e.keyCode === 171 ||
			e.keyCode === 32
		) {
			e.preventDefault();

			let char = '';
			if (e.keyCode >= 48 && e.keyCode <= 57) {
				char = String.fromCharCode(e.keyCode);
			} else if (e.keyCode >= 65 && e.keyCode <= 90) {
				char = String.fromCharCode(e.keyCode);
			} else if (e.keyCode >= 96 && e.keyCode <= 105) {
				char = String.fromCharCode(e.keyCode - 48);
			} else if (e.keyCode === 189 || e.keyCode === 109) {
				char = '-';
			} else if (e.keyCode === 171) {
				char = '+';
			} else if (e.keyCode === 190 || e.keyCode === 110) {
				char = '.';
			} else if (e.keyCode === 32) {
				char = ' ';
			}

			barcodeBuffer += char;

			barcodeTimeout = setTimeout(function() {
				barcodeBuffer = '';
			}, BARCODE_TIMEOUT);

		} else if (e.keyCode === 13 && barcodeBuffer.length > 0) {
			e.preventDefault();
			processBarcodeSearch(barcodeBuffer);
			barcodeBuffer = '';
			if (barcodeTimeout) {
				clearTimeout(barcodeTimeout);
				barcodeTimeout = null;
			}
		} else if (e.keyCode === 27) {
			barcodeBuffer = '';
			if (barcodeTimeout) {
				clearTimeout(barcodeTimeout);
				barcodeTimeout = null;
			}
		}
	});

	function processBarcodeSearch(barcode) {
		const $search = $('#point-of-sales input[name="query"]');
		$search.val(barcode).trigger('input');
		$search.addClass('barcode-scanned');
		setTimeout(function() {
			$search.removeClass('barcode-scanned');
		}, 1000);
	}

});

waitFor('jQuery', ($) => {

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


waitFor('jQuery', ($) => {

	function escapeHtml(s) {
		return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	}

	function escapeRegex(s) {
		return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}

	function highlight(text, query) {
		const escaped = escapeHtml(text);
		if (!query) return escaped;
		const regex = new RegExp('(' + escapeRegex(query) + ')', 'ig');
		return escaped.replace(regex, '<u>$1</u>');
	}

	// Filter
	$('#sidebar input[name="filter"]').on({

		'input': function(){

			const query = $(this).val();
			const q = query.toLowerCase();
			const $menu = $('#sidebar-menu');

			// Restore any previously highlighted text
			$menu.find('.name').each(function() {
				const $name = $(this);
				if ($name.data('original') !== undefined) {
					$name.text($name.data('original'));
				}
			});

			// Reset visibility
			$menu.find('.app, .docs, .doc, .group').css('display', '');

			if (!query) {
				return;
			}

			$menu.find('.app').each(function() {

				const $app = $(this);
				const $appLink = $app.children('a');
				const $docsList = $app.children('.docs');
				const $docs = $docsList.children('.doc');

				const appMatches = $appLink.text().toLowerCase().includes(q);

				let hasMatchingDoc = false;
				$docs.each(function() {
					if ($(this).text().toLowerCase().includes(q)) {
						hasMatchingDoc = true;
						return false;
					}
				});

				if (appMatches) {
					// Direct app name match: show app with all its docs expanded
					$app.show();
					$docsList.show();
					$docs.show();
				} else if (hasMatchingDoc) {
					// Only doc(s) match: show app and only matching docs
					$app.show();
					$docsList.show();
					$docs.each(function() {
						if ($(this).text().toLowerCase().includes(q)) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				} else {
					$app.hide();
				}
			});

			// Hide groups that ended up with no visible apps
			$menu.find('.group').each(function() {
				const $group = $(this);
				const hasVisible = $group.find('.app').filter(':visible').length > 0;
				$group.css('display', hasVisible ? '' : 'none');
			});

			// Underscore the matching substring inside visible app and doc names
			$menu.find('.app').filter(':visible').find('.name')
				.add($menu.find('.doc').filter(':visible').find('.name'))
				.each(function() {
					const $name = $(this);
					if ($name.data('original') === undefined) {
						$name.data('original', $name.text());
					}
					$name.html(highlight($name.data('original'), query));
				});
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
