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
		<div class="form-group">
			<?php echo functions::form_draw_text_field('query', true, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
		</div>

		<div class="form-group results table-responsive">
			<table class="table table-striped table-hover data-table">
				<thead>
					<tr>
						<th><?php echo language::translate('title_id', 'ID'); ?></th>
						<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
						<th><?php echo language::translate('title_sku', 'SKU'); ?></th>
						<th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
						<th><?php echo language::translate('title_price', 'Price'); ?></th>
						<th><?php echo language::translate('title_date_created', 'Date Created'); ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>

</div>

<script>
  $('#modal-product-picker input[name="query"]').focus();

	var xhr_product_picker = null;
	$('#modal-product-picker input[name="query"]').on('input', function(){
		if ($(this).val() == '') {
			$('#modal-product-picker .results tbody').html('');
			xhr_product_picker = null;
			return;
		}
		xhr_product_picker = $.ajax({
			type: 'get',
			async: true,
			cache: false,
			url: '<?php echo document::link('', ['app' => 'catalog', 'doc' => 'products.json']); ?>&query=' + $(this).val(),
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error(textStatus + ': ' + errorThrown);
			},
			success: function(json) {
				$('#modal-product-picker .results tbody').html('');
				$.each(json, function(i, row){
					if (row) {
						$('#modal-product-picker .results tbody').append(
							'<tr data-id="' + row.id + '" data-sku="' + row.name + '" data-name="' + row.name + '" data-price="' + row.price.value + '" data-price-formatted="' + row.price.formatted + '" data-quantity="' + row.quantity + '">' +
							'  <td class="id">' + row.id + '</td>' +
							'  <td class="name">' + row.name + '</td>' +
							'  <td class="sku">' + row.sku + '</td>' +
							'  <td class="text-right">' + row.quantity + '</td>' +
							'  <td class="text-right">' + row.price.formatted + '</td>' +
							'  <td>' + row.date_created + '</td>' +
							'</tr>'
						);
					}
				});
				if ($('#modal-product-picker .results tbody').html() == '') {
					$('#modal-product-picker .results tbody').html('<tr><td colspan="6"><em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em></td></tr>');
				}
			},
		});
	}).focus();

  $('#modal-product-picker tbody').on('click', 'td', function() {
    var $input_group = $.featherlight.current().$currentTarget.closest('.input-group'),
      $field = $input_group.find(':input');

    var product = $(this).closest('tr').data();

    $field.val(product.id || 0)
      .data('sku', product.sku || '')
      .data('price', product.price.amount || 0)
      .data('price-formatted', product.priceFormatted || '')
      .trigger('change');

    $input_group.find('.id').text(product.id || 0);
    $input_group.find('.name').text(product.name || '(<?php echo functions::escape_js(language::translate('title_no_product', 'No Product')); ?>)');
    $.featherlight.close();
  });
</script>