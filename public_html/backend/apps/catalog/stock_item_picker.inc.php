<?php
	document::$layout = 'ajax';
?>
<style>
#modal-stock-item-picker tbody tr {
	cursor: pointer;
}
</style>

<div id="modal-stock-item-picker" class="modal fade" style="max-width: 980px;">

	<h2><?php echo language::translate('title_stock_items', 'Stock Items'); ?></h2>

	<div class="modal-body">
		<div class="form-group">
			<?php echo functions::form_draw_text_field('query', true, 'placeholder="'. htmlspecialchars(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
		</div>

		<div class="form-group results table-responsive">
			<table class="table table-striped table-hover data-table">
				<thead>
					<tr>
						<th><?php echo language::translate('title_id', 'ID'); ?></th>
						<th><?php echo language::translate('title_sku', 'SKU'); ?></th>
						<th><?php echo language::translate('title_brand', 'Brand'); ?></th>
						<th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
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
	</div>

</div>

<script>
	var xhr_stock_item_picker = null;
	$('#modal-stock-item-picker input[name="query"]').bind('propertyChange input', function(){
		if ($(this).val() == '') {
			$('#modal-stock-item-picker .results tbody').html('');
			xhr_stock_item_picker = null;
			return;
		}
		xhr_stock_item_picker = $.ajax({
			type: 'get',
			async: true,
			cache: false,
			url: '<?php echo document::link('', ['app' => 'catalog', 'doc' => 'stock_items.json']); ?>&query=' + $(this).val(),
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error(textStatus + ': ' + errorThrown);
			},
			success: function(json) {
				$('#modal-stock-item-picker .results tbody').html('');
				$.each(json, function(i, row){
          $('#modal-stock-item-picker .results tbody').append(
            '<tr>' +
            '  <td class="id">' + row.id + '</td>' +
            '  <td class="sku">' + row.sku + '</td>' +
            '  <td class="brand">' + row.brand_name + '</td>' +
            '  <td class="name">' + row.name + '</td>' +
            '  <td class="gtin">' + row.gtin + '</td>' +
            '  <td class="mpn">' + row.mpn + '</td>' +
            '  <td class="quantity text-right">' + row.quantity + '</td>' +
            '  <td class="date-created">' + row.date_created + '</td>' +
            '</tr>'
          );
          $.each(row, function(key, value){
            $('#modal-stock-item-picker .results tbody tr:last').data(key, value);
          });
				});
				if ($('#modal-stock-item-picker .results tbody').html() == '') {
					$('#modal-stock-item-picker .results tbody').html('<tr><td colspan="6"><em><?php echo functions::general_escape_js(language::translate('text_no_results', 'No results')); ?></em></td></tr>');
				}
			},
		});
	}).focus();

	$('#modal-stock-item-picker tbody').on('click', 'td', function() {
		var row = $(this).closest('tr');

		var id = $(row).find('.id').text();
		if (!id) return;

    var data = $(row).data();

    <?php if (!empty($_GET['js_callback'])) { ?>
    <?php echo 'if (window[\''. addcslashes($_GET['js_callback'], '\'') .'\']) window[\''. addcslashes($_GET['js_callback'], '\'') .'\'](data);' ?>
    <?php } ?>

    if ($.featherlight.opened) {
      $.featherlight.close();
    }
	});
</script>