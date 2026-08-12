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