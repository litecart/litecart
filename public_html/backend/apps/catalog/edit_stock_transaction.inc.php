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
			if (empty($_POST['contents'][$key]['quantity_adjustment'])) notices::add('errors', language::translate('error_quantity_cannot_be_empty', 'Quantity cannot be empty'));
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

  $available_stock_items = [];

  $stock_items_query = database::query(
    "select si.*, sii.name from ". DB_TABLE_PREFIX ."stock_items si
    left join ". DB_TABLE_PREFIX ."stock_items_info sii on (si.id = sii.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
    order by sku, name;"
  );

  while ($stock_item = database::fetch($stock_items_query)) {
    $available_stock_items[] = $stock_item;
  }

	functions::draw_lightbox();
?>

<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title"><?php echo $app_icon; ?> <?php echo !empty($stock_transaction->data['id']) ? language::translate('title_edit_stock_transaction', 'Edit Stock Transaction') : language::translate('title_create_new_stock_transaction', 'Create New Stock Transaction'); ?></div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('form_stock_transaction', 'post'); ?>

      <div class="row" style="max-width: 980px;">
        <div class="form-group col-md-4">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-8">
          <label><?php echo language::translate('title_notes', 'Notes'); ?></label>
          <?php echo functions::form_draw_textarea('notes', true, 'style="height: 100px;"'); ?>
        </div>
      </div>

      <h2><?php echo language::translate('title_contents', 'Contents'); ?></h2>

      <table id="transaction-contents" class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th style="min-width: 150px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
            <th class="text-right" style="min-width: 150px;"><?php echo language::translate('title_quantity_adjustment', 'Quantity Adjustment'); ?></th>
            <th class="text-right" style="min-width: 150px;"><?php echo language::translate('title_ordered', 'Ordered'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($_POST['contents'])) foreach (array_keys($_POST['contents']) as $key) { ?>
          <tr class="item">
            <td>
              <?php echo functions::form_draw_hidden_field('contents['.$key.'][id]', true); ?>
              <?php echo functions::form_draw_hidden_field('contents['.$key.'][stock_item_id]', true); ?>
              <?php echo functions::form_draw_hidden_field('contents['. $key .'][sku]', true); ?><?php echo $_POST['contents'][$key]['sku']; ?>
            </td>
            <td><?php echo $_POST['contents'][$key]['name']; ?></td>
            <td class="text-center">
              <div class="input-group">
                <span class="input-group-addon">&plusmn;</span>
                <?php echo functions::form_draw_decimal_field('contents['. $key .'][quantity_adjustment]', true, 2); ?>
              </div>
            </td>
            <td class="text-center">
              <div class="input-group">
                <span class="input-group-btn">
                  <?php echo functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                </span>
                <?php echo functions::form_draw_decimal_field('contents['. $key .'][ordered]', true, 2); ?>
              </div>
            </td>
            <td><a class="remove" href="#" title="<?php echo htmlspecialchars(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td><?php echo functions::form_draw_text_field('new[sku]', true, 'list="available-stock-items"'); ?></td>
            <td><?php echo functions::form_draw_text_field('new[name]', true, 'tabindex="-1"'); ?></td>
            <td>
              <div class="input-group">
                <span class="input-group-addon">&plusmn;</span>
                <?php echo functions::form_draw_decimal_field('new[quantity_adjustment]', true, 2); ?>
              </div>
            </td>
            <td class="text-center">
              <?php echo functions::form_draw_decimal_field('new[ordered]', true, 2); ?>
            </td>
            <td><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
          </tr>
        </tfoot>
      </table>

      <datalist id="available-stock-items">
        <?php foreach ($available_stock_items as $stock_item) { ?>
        <option value="<?php echo htmlspecialchars($stock_item['sku']); ?>" data-name="<?php echo htmlspecialchars($stock_item['name']); ?>">
        <?php } ?>
      </datalist>

      <div class="panel-action">
        <div class="btn-group">
          <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
          <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
          <?php echo (isset($stock_transaction->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        </div>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

  <div class="panel-footer">
  </div>
</div>

<script>
  $('input[name="new[sku]"]').on('input', function(e) {
    var row = $(this).closest('tr');
    if ($('datalist#available-stock-items option[value="'+ $(this).val() +'"]').length) {
      $(row).find('input[name="new[name]"]').val($('datalist#available-stock-items option[value="'+ $(this).val() +'"]:first').data('name')).prop('readonly', true);
    } else {
      $(row).find('input[name="new[name]"]').prop('readonly', false);
    }
  });

  $('body').on('click', 'button[name="transfer"]', function(){
    var quantity_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
    var ordered_field = $(this).closest('tr').find('input[name$="[ordered]"]');
    $(quantity_field).val( Number($(quantity_field).val()) + Number($(ordered_field).val()) );
    $(ordered_field).val(0);
  });

  $('table tfoot').keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
      $('table tfoot button[name="add"]').trigger('click');
    }
  });

	$('body').on('click', '#transaction-contents .remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	var new_item_index = 0;
  $('table tfoot button[name="add"]').click(function(e) {
		e.preventDefault();

    var row = $(this).closest('tr');

    if (!$('datalist#available-stock-items option[value="'+ $('input[name="new[sku]"]').val() +'"]').length) {
      alert('Uknown stock item');
      return;
    }

    var option = $('datalist#available-stock-items option[value="'+ $('input[name="new[sku]"]').val() +'"]:first');

		new_item_index++;
		var output = '  <tr class="item">'
							 + '    <td>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][id]', '')); ?>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][item_id]', '')); ?>'
							 + '       <?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][sku]', '')); ?>'
							 + '       ' + $(option).attr('value')
							 + '    </td>'
							 + '    <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('contents[new_item_index][name]', '')); ?>'+ $(option).data('name') +'</td>'
							 + '    <td>'
							 + '      <div class="input-group">'
               + '        <span class="input-group-addon">&plusmn;</span>'
               + '        <?php echo functions::form_draw_decimal_field('contents[new_item_index][quantity_adjustment]', true, 2, !empty($_POST['options_stock']) ? 'readonly' : ''); ?>'
               + '      </div>'
               + '    </td>'
               + '    <td class="text-center">'
               + '      <div class="input-group">'
               + '        <span class="input-group-btn">'
               + '          <?php echo functions::general_escape_js(functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button')); ?>'
               + '        </span>'
               + '        <?php echo functions::general_escape_js(functions::form_draw_decimal_field('contents[new_item_index][ordered]', true, 2)); ?>'
               + '      </div>'
               + '    </td>'
							 + '    <td><a class="remove" href="#" title="<?php echo htmlspecialchars(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle', 'style="color: #c33;"')); ?></a></td>'
							 + '  </tr>';

		output = output.replace(/new_item_index/g, 'new_' + new_item_index);
		$('#transaction-contents tbody').append(output);

	// Insert values
		var inserted = $('#transaction-contents tbody tr.item').last();
		$(inserted).find('[name$="[item_id]"]').val($('input[name="new[id]"]').data('id'));
		$(inserted).find('[name$="[sku]"]').val($('input[name="new[sku]"]').data('sku'));
		$(inserted).find('[name$="[name]"]').val($('input[name="new[name]"]').val());
		$(inserted).find('[name$="[quantity_adjustment]"]').val($('input[name="new[quantity_adjustment]"]').val());
		$(inserted).find('[name$="[ordered]"]').val($('input[name="new[ordered]"]').val());

    $('input[name="new[sku]"]').val('');
    $('input[name="new[name]"]').val('');
    $('input[name="new[quantity_adjustment]"]').val('');
    $('input[name="new[sku]"]').focus();
	});
</script>