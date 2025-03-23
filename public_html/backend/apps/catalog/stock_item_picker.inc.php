<?php

	document::$layout = 'ajax';

?>
<style>
#stock-item-picker tbody tr {
	cursor: pointer;
}
</style>

<div id="stock-item-picker" class="card">

	<div class="card-header">
		<h2 class="card-title">
			<?php echo language::translate('title_stock_items', 'Stock Items'); ?>
		</h2>
	</div>

	<div class="card-action">
		<?php echo functions::form_button_link(document::ilink(__APP__.'/edit_stock_item', [], ['js_callback']), language::translate('title_create_new_stock_item', 'Create New Stock Item'), 'class="btn btn-default" data-toggle="lightbox" data-seamless=true data-width="980px"', 'add'); ?>
	</div>

	<div class="card-body">
		<label class="form-group">
			<div class="form-label"><?php echo language::translate('title_search', 'Search'); ?></div>
			<?php echo functions::form_input_text('query', true, 'placeholder="'. functions::escape_attr(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
		 </label>
	</div>

	<div class="results">
		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo language::translate('title_id', 'ID'); ?></th>
					<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
					<th><?php echo language::translate('title_sku', 'SKU'); ?></th>
					<th><?php echo language::translate('title_gtin', 'GTIN'); ?></th>
					<th><?php echo language::translate('title_mpn', 'MPN'); ?></th>
					<th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
					<th><?php echo language::translate('title_date_created', 'Date Created'); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<div class="card-body">
	</div>

</div>

<script>
	var xhr_stock_item_picker = null;
	$('#stock-item-picker input[name="query"]').on('input', function() {

		/*
		if ($(this).val() == '') {
			$('#stock-item-picker tbody').html('');
			xhr_stock_item_picker = null;
			return;
		}
		*/

		xhr_stock_item_picker = $.ajax({
			type: 'get',
			async: true,
			cache: false,
			url: '<?php echo document::ilink(__APP__.'/stock_items.json'); ?>?query=' + $(this).val(),
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
			},
			success: function(result) {
				$('#stock-item-picker tbody').html('');
				$.each(result, function(i, item) {

					$row = $([
						'<tr>',
						'  <td class="id">' + item.id + '</td>',
						'  <td class="name">' + item.name + '</td>',
						'  <td class="sku">' + item.sku + '</td>',
						'  <td class="gtin">' + item.gtin + '</td>',
						'  <td class="mpn">' + item.mpn + '</td>',
						'  <td class="quantity text-end">' + item.quantity + '</td>',
						'  <td class="date-created">' + item.date_created + '</td>',
						'</tr>',
					].join('\n'));

					$.each(Object.keys(item), function(j, key) {  // Iterate Object.keys() because jQuery.each() doesn't support a property named length
						$row.data(key, item[key]);
					});

					$row.appendTo('#stock-item-picker tbody');
				});

				if ($('#stock-item-picker tbody').html() == '') {
					$('#stock-item-picker tbody').html([
						'<tr>',
						'  <td colspan="6">',
						'    <em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em>',
						'</td>',
						'</tr>',
					].join('\n'));
				}
			}
		});
	}).trigger('input').trigger('focus');

	$('#stock-item-picker tbody').on('click', 'td', function() {

		let $row = $(this).closest('tr'),
			callback = '<?php echo !empty($_GET['js_callback']) ? functions::escape_js($_GET['js_callback']) : ''; ?>',
			stock_item = $row.data();

		if (callback) {

			window[callback](stock_item);

		} else if ($.litebox.current().$currentTarget.data('callback')) {

			if (typeof callback == 'function') {
				callback(stock_item);
			} else {
				window[callback](stock_item);
			}

		} else if ($.litebox.current().$currentTarget.closest('.input-group').length) {
			let $field = $.litebox.current().$currentTarget.closest('.input-group');
			$field.find(':input').val(stock_item.id).trigger('change');
			$field.find('.id').text(stock_item.id);
			$field.find('.name').text(stock_item.name);
		}

		if ($.litebox.opened) {
			$.litebox.close();
		}
	});
</script>