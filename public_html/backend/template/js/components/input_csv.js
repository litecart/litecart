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
