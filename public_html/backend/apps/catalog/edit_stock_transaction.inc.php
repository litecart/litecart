<?php

	if (!empty($_GET['transaction_id'])) {
		$stock_transaction = new ent_stock_transaction($_GET['transaction_id']);
	} else {
		$stock_transaction = new ent_stock_transaction();
	}

	if (empty($_POST)) {
		foreach ($stock_transaction->data as $key => $value) {
			$_POST[$key] = $value;
		}
	}

	breadcrumbs::add(!empty($stock_transaction->data['id']) ? language::translate('title_edit_stock_transaction', 'Edit Stock Transaction') : language::translate('title_create_new_transaction', 'Create New Transaction'));

	if (isset($_POST['save'])) {

		if (empty($_POST['contents'])) $_POST['contents'] = [];

		foreach (array_keys($_POST['contents']) as $key) {
			if (empty($_POST['contents'][$key]['quantity'])) notices::add('errors', language::translate('error_quantity_cannot_be_empty', 'Quantity cannot be empty'));
		}

		if (empty(notices::$data['errors'])) {

			$fields = [
				'name',
				'notes',
				'contents',
			];

			foreach ($fields as $field) {
				if (isset($_POST[$field])) $stock_transaction->data[$field] = $_POST[$field];
			}

			$stock_transaction->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			header('Location: '. document::link('', ['app' => $_GET['app'], 'doc' => 'stock_transactions']));
			exit;
		}
	}

	if (isset($_POST['delete']) && !empty($stock_transaction->data['id'])) {
		$stock_transaction->delete();
		notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
		header('Location: '. document::link('', ['app' => $_GET['app'], 'doc' => 'stock_transactions']));
		exit;
	}

	functions::draw_lightbox();
?>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($stock_transaction->data['id']) ? language::translate('title_edit_stock_transaction', 'Edit Stock Transaction') : language::translate('title_create_new_stock_transaction', 'Create New Stock Transaction'); ?></h1>

<?php echo functions::form_draw_form_begin('form_stock_transaction', 'post'); ?>

	<div class="row">
		<div class="form-group col-md-6">
			<label><?php echo language::translate('title_name', 'Name'); ?></label>
			<?php echo functions::form_draw_text_field('name', true, 'style="max-width: 320px;"'); ?>
		</div>
	</div>

	<div class="row">
		<div class="form-group col-md-6">
			<label><?php echo language::translate('title_notes', 'Notes'); ?></label>
			<?php echo functions::form_draw_textarea('notes', true, 'style="max-width: 320px; height: 50px;"'); ?>
		</div>
	</div>

	<h2><?php echo language::translate('title_contents', 'Contents'); ?></h2>

	<table id="transaction-contents" class="table table-striped data-table">
		<thead>
			<tr>
				<th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
				<th class="text-center" style="min-width: 50px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
				<th class="text-center" style="min-width: 150px;"><?php echo language::translate('title_qty', 'Qty'); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
<?php
	if (!empty($_POST['contents'])) {
		foreach (array_keys($_POST['contents']) as $key) {
?>
			<tr class="item">
				<td>
					<?php echo functions::form_draw_hidden_field('contents['.$key.'][id]', true); ?>
					<?php echo functions::form_draw_hidden_field('contents['.$key.'][product_id]', true); ?>
					<?php echo functions::form_draw_hidden_field('contents['.$key.'][combination]', true); ?>
					<?php echo functions::form_draw_hidden_field('contents['.$key.'][name]', true); ?>
					<a href="<?php echo document::href_ilink('product', ['product_id' => $_POST['contents'][$key]['product_id']]); ?>" target="_blank"><?php echo $_POST['contents'][$key]['name']; ?></a>
				</td>
				<td><?php echo functions::form_draw_hidden_field('contents['. $key .'][sku]', true); ?><?php echo $_POST['contents'][$key]['sku']; ?></td>
				<td class="text-center"><?php echo functions::form_draw_decimal_field('contents['. $key .'][quantity]', true, 2); ?></td>
				<td><a class="remove" href="#" title="<?php echo htmlspecialchars(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
			</tr>
<?php
		}
	}
?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<a class="btn btn-default add-item-row" href="#box-add-item-row" data-toggle="lightbox"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_item_row', 'Add Item Row'); ?></a>
				</td>
			</tr>
		</tfoot>
	</table>

	<div class="btn-group">
		<?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
		<?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
		<?php echo (isset($stock_transaction->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
	</div>

<?php echo functions::form_draw_form_end(); ?>

<div id="box-add-item-row" style="display: none;">
	<h2><?php echo language::translate('title_add_row', 'Add Row'); ?></h2>

	<?php echo functions::form_draw_form_begin('form_add_item_row', 'post', null, false, 'style="max-width: 320px;"'); ?>

		<div class="form-group">
			<label><?php echo language::translate('title_stock_item', 'Stock Item'); ?></label>
			<?php echo functions::form_draw_stock_options_list('stock_option_id', true); ?>
		</div>

		<div class="row">
			<div class="form-group col-md-6">
				<label><?php echo language::translate('title_quantity_adjustment', 'Quantity Adjustment'); ?></label>
				<?php echo functions::form_draw_decimal_field('quantity', true); ?>
			</div>
		</div>

		<div class="btn-group">
			<?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'submit', '', 'add'); ?>
			<?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
		</div>

	<?php echo functions::form_draw_form_end(); ?>
</div>

<script>
	var new_item_index = 0;

	$('#box-add-item-row button[name="add"]').click(function(e) {
		e.preventDefault();

		var form = $(this).closest('form');

		new_item_index++;
		var output = '  <tr class="item">'
							 + '    <td>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][id]', '')); ?>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][product_id]', '')); ?>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][combination]', '')); ?>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][name]', '')); ?>'
							 + '       ' + $(form).find(':input[name="stock_option_id"] option:selected').data('name')
							 + '    </td>'
							 + '    <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][sku]', '')); ?>'+ $(form).find(':input[name="stock_option_id"] option:selected').data('sku') +'</td>'
							 + '    <td class="text-center"><?php echo functions::general_escape_js(functions::form_draw_decimal_field('contents[new_item_index][quantity]', '', 2)); ?></td>'
							 + '    <td>'
							 + '      <a class="copy" href="#" title="<?php echo htmlspecialchars(language::translate('title_copy', 'Copy')); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-clone')); ?></a>'
							 + '      <a class="remove" href="#" title="<?php echo htmlspecialchars(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle', 'style="color: #cc3333;"')); ?></a>'
							 + '    </td>'
							 + '  </tr>';

		output = output.replace(/new_item_index/g, 'new_' + new_item_index);
		console.log($(form).find(':input[name="stock_option_id"] option:selected').data('name'));
		$('#transaction-contents tbody').append(output);

	// Insert values
		var row = $('#transaction-contents tbody tr.item').last();
		$(row).find('*[name$="[product_id]"]').val($(form).find(':input[name="stock_option_id"] option:selected').data('product-id')).change();
		$(row).find('*[name$="[combination]"]').val($(form).find(':input[name="stock_option_id"] option:selected').data('combination')).change();
		$(row).find('*[name$="[name]"]').val($(form).find(':input[name="stock_option_id"] option:selected').data('name')).change();
		$(row).find('*[name$="[sku]"]').val($(form).find(':input[name="stock_option_id"] option:selected').data('sku')).change();
		$(row).find('*[name$="[quantity]"]').val($(form).find(':input[name="quantity"]').val()).change();
		console.log();
		$.featherlight.close();
	});

	$('body').on('click', '#transaction-contents .copy', function(e) {
		e.preventDefault();
		target = $(this).closest('tr').clone().insertAfter($(this).closest('tr'));

		$(this).closest('tr').next('tr').find('*[name$="[id]"]').val('');
		$(this).closest('tr').next('tr').find('*[name$="[quantity]"]').val(-$(this).closest('tr').next('tr').find('*[name$="[quantity]"]').val());

		new_item_index++;
		$.each($(this).closest('tr').next('tr').find(':input'), function(i){
			$(this).attr('name', $(this).attr('name').replace(/^contents\[[^\]]+\]/, 'contents[new_' + new_item_index + ']'));
		});
	});

	$('body').on('click', '#transaction-contents .remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});
</script>