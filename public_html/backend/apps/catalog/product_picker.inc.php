<?php
	document::$layout = 'ajax';
?>
<style>
#modal-product-picker tbody tr {
	cursor: pointer;
}
</style>

<div id="modal-product-picker" class="modal fade" style="max-width: 980px; display: none;">

	<h2><?php echo language::translate('title_products', 'Products'); ?></h2>

	<div class="modal-body">
		<label class="form-group">
			<div class="form-label"><?php echo language::translate('title_search', 'Search'); ?></div>
			<?php echo functions::form_input_text('query', true, 'placeholder="'. functions::escape_attr(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
		 </label>

		<table class="table data-table">
			<thead>
				<tr>
					<th><?php echo language::translate('title_id', 'ID'); ?></th>
					<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
					<th><?php echo language::translate('title_stock_options', 'Stock Options'); ?></th>
					<th><?php echo language::translate('title_price', 'Price'); ?></th>
					<th><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
					<th><?php echo language::translate('title_reserved', 'Reserved'); ?></th>
					<th><?php echo language::translate('title_date_created', 'Date Created'); ?></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>

</div>

<script>
	$('#modal-product-picker input[name="query"]').trigger('focus');

	var xhr_product_picker = null;
	$('#modal-product-picker input[name="query"]').on('input', function() {

		let $modal = $('#modal-product-picker');

		if ($(this).val() == '') {
			$modal.find('tbody').html('');
			xhr_product_picker = null;
			return;
		}

		xhr_product_picker = $.ajax({
			type: 'get',
			async: true,
			cache: false,
			url: '<?php echo document::ilink('catalog/products.json'); ?>?query=' + $(this).val(),
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
			},
			success: function(result) {

				$('tbody', $modal).html('');

				if (!result.length) {

					var $output = $([
						'<tr>',
						'  <td colspan="7">',
						'    <em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em>',
						'  </td>',
						'</tr>'
					].join('\n'));

					$('tbody', $modal).html($output);
					return;
				}

				$.each(result, function(i, product) {

					let $row = $([
						'<tr>',
						'  <td>' + product.id + '</td>',
						'  <td>' + product.name + '</td>',
						'  <td class="text-center">' + (product.num_stock_options || '-') + '</td>',
						'  <td class="text-end">' + product.price.formatted + '</td>',
						'  <td class="text-end">' + product.quantity + '</td>',
						'  <td class="text-end">' + product.reserved + '</td>',
						'  <td>' + product.date_created + '</td>',
						'</tr>'
					].join('\n'));

					$row.data(product);

					$modal.find('tbody').append($row);
				});

			},
		});
	}).trigger('input').trigger('focus');

	$('#modal-product-picker tbody').on('click', 'td', function() {

		let $row = $(this).closest('tr'),
			callback = $.litebox.current().$currentTarget.data('callback'),
			expand = <?php echo (isset($_GET['collect']) && array_intersect(['price', 'stock_option'], $_GET['collect'])) ? 'true' : 'false'; ?>,
			product = $row.data();

		if (expand || $row.data('stock_option')) {
			callback = function(product){
				$.litebox('<?php echo document::ilink(__APP__.'/product_picker_configure', ['callback' => @$_GET['callback']]);?>&product_id='+ product.id);
			};
		}

		if (callback) {

			if (typeof callback == 'function') {
				callback(product);
			} else {
				window[callback](product);
			}

		} else if ($.litebox.current().$currentTarget.closest('.input-group').length) {
			let $field = $.litebox.current().$currentTarget.closest('.input-group');
			$field.find(':input').val(product.id).trigger('change');
			$field.find('.id').text(product.id);
			$field.find('.name').text(product.name);
		}

		if ($.litebox.opened) {
			$.litebox.close();
		}
	});
</script>