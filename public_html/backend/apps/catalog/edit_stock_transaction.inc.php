<?php

  if (!empty($_GET['transaction_id'])) {
    $stock_transaction = new ent_stock_transaction($_GET['transaction_id']);
  } else {
    $stock_transaction = new ent_stock_transaction();
  }

  if (!$_POST) {
    $_POST = $stock_transaction->data;
  }

  breadcrumbs::add(!empty($stock_transaction->data['id']) ? language::translate('title_edit_stock_transaction', 'Edit Stock Transaction') : language::translate('title_create_new_transaction', 'Create New Transaction'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['contents'])) {
        $_POST['contents'] = [];
      }

      foreach (array_keys($_POST['contents']) as $key) {
        if (empty($_POST['contents'][$key]['quantity_adjustment'])) {
          throw new Exception(language::translate('error_quantity_cannot_be_empty', 'Quantity cannot be empty'));
        }
      }

      $fields = [
        'name',
        'description',
        'contents',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $stock_transaction->data[$field] = $_POST[$field];
      }

      $stock_transaction->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/stock_transactions'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete']) && !empty($stock_transaction->data['id'])) {

    try {

      $stock_transaction->delete();

      notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
      header('Location: '. document::ilink(__APP__.'/stock_transactions'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $available_stock_items = database::query(
    "select si.id, si.sku, si.quantity, si.backordered, sii.name from ". DB_TABLE_PREFIX ."stock_items si
    left join ". DB_TABLE_PREFIX ."stock_items_info sii on (si.id = sii.stock_item_id and sii.language_code = '". database::input(language::$selected['code']) ."')
    order by sku, name;"
  )->fetch_all();

  functions::draw_lightbox();
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <div class="card-title">
        <?php echo $app_icon; ?> <?php echo !empty($stock_transaction->data['id']) ? language::translate('title_edit_stock_transaction', 'Edit Stock Transaction') : language::translate('title_create_new_stock_transaction', 'Create New Stock Transaction'); ?>
      </div>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('form_stock_transaction', 'post'); ?>

      <div class="row">
        <div class="form-group col-md-3">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_text_field('name', true); ?>
        </div>

        <?php if (!empty($stock_transaction->data['id'])) { ?>
        <div class="form-group col-md-2">
          <label><?php echo language::translate('title_updated', 'Updated'); ?></label>
          <div class="form-input" readonly><?php echo date(language::$selected['raw_datetime'], strtotime($stock_transaction->data['date_updated'])); ?></div>
        </div>

        <div class="form-group col-md-2">
          <label><?php echo language::translate('title_created', 'Created'); ?></label>
          <div class="form-input" readonly><?php echo date(language::$selected['raw_datetime'], strtotime($stock_transaction->data['date_created'])); ?></div>
        </div>
        <?php } ?>
      </div>

      <div class="row">
        <div class="form-group col-md-7">
          <label><?php echo language::translate('title_description', 'Description'); ?></label>
          <?php echo functions::form_textarea('description', true, 'style="height: 100px;"'); ?>
        </div>
      </div>

      <h2><?php echo language::translate('title_contents', 'Contents'); ?></h2>

      <table id="transaction-contents" class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th style="min-width: 225px;"><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th class="main"><?php echo language::translate('title_item', 'Item'); ?></th>
            <th class="text-end" style="min-width: 150px;"><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
            <th class="text-end" style="min-width: 150px;"><?php echo language::translate('title_quantity_adjustment', 'Quantity Adjustment'); ?></th>
            <th class="text-end" style="min-width: 175px;"><?php echo language::translate('title_backordered', 'Backordered'); ?></th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($_POST['contents'])) foreach (array_keys($_POST['contents']) as $key) { ?>
          <tr class="item">
            <td>
              <?php echo functions::form_hidden_field('contents['.$key.'][id]', true); ?>
              <?php echo functions::form_hidden_field('contents['.$key.'][stock_item_id]', true); ?>
              <?php echo functions::form_hidden_field('contents['. $key .'][sku]', true); ?>
              <?php echo functions::form_hidden_field('contents['. $key .'][name]', true); ?>
              <?php echo functions::escape_html($_POST['contents'][$key]['sku']); ?>
            </td>
            <td><?php echo functions::escape_html($_POST['contents'][$key]['name']); ?></td>
            <td><?php echo functions::form_decimal_field('contents['. $key .'][quantity]', true, 2, 'readonly'); ?></td>
            <td class="text-center">
              <div class="input-group">
                <span class="input-group-text">&plusmn;</span>
                <?php echo functions::form_decimal_field('contents['. $key .'][quantity_adjustment]', true, 2); ?>
              </div>
            </td>
            <td class="text-center">
              <div class="input-group">
                <?php echo functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                <?php echo functions::form_decimal_field('contents['. $key .'][backordered]', true, 2); ?>
              </div>
            </td>
            <td class="text-center"><a class="remove btn btn-default btn-sm" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td><?php echo functions::form_text_field('new[sku]', true, 'list="available-stock-items"'); ?></td>
            <td><?php echo functions::form_text_field('new[name]', true, 'tabindex="-1"'); ?></td>
            <td><?php echo functions::form_decimal_field('new[quantity]', true, 2, 'tabindex="-1" readonly'); ?></td>
            <td>
              <div class="input-group">
                <span class="input-group-text">&plusmn;</span>
                <?php echo functions::form_decimal_field('new[quantity_adjustment]', true, 2); ?>
              </div>
            </td>
            <td class="text-center">
              <div class="input-group">
                <?php echo functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button', 'tabindex="-1"'); ?>
                <?php echo functions::form_decimal_field('new[backordered]', true, 2); ?>
              </div>
            </td>
            <td><?php echo functions::form_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($stock_transaction->data['id']) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<datalist id="available-stock-items">
  <?php foreach ($available_stock_items as $stock_item) { ?>
  <option value="<?php echo functions::escape_html($stock_item['sku']); ?>" data-product-id="<?php echo functions::escape_html($stock_item['product_id']); ?>" data-stock-option-id="<?php echo functions::escape_html($stock_item['stock_option_id']); ?>" data-sku="<?php echo functions::escape_html($stock_item['sku']); ?>"  data-name="<?php echo functions::escape_html($stock_item['name']); ?>" data-quantity="<?php echo (float)$stock_item['quantity']; ?>" data-backordered="<?php echo (float)$stock_item['backordered']; ?>">
    <?php echo functions::escape_html($stock_item['name']); ?> &ndash; (<?php echo language::translate('title_in_stock', 'In Stock'); ?>: <?php echo (float)$stock_item['quantity']; ?>)
  </option>
  <?php } ?>
</datalist>

<script>
  $('input[name="new[sku]"]').on('input', function(e) {
    let row = $(this).closest('tr');

    if ($('datalist#available-stock-items option[value="'+ $(this).val() +'"]').length) {
      $(row).find('input[name="new[name]"]').val($('datalist#available-stock-items option[value="'+ $(this).val() +'"]:first').data('name')).prop('readonly', true);
      $(row).find('input[name="new[quantity]"]').val($('datalist#available-stock-items option[value="'+ $(this).val() +'"]:first').data('quantity') || 0);
      $(row).find('input[name="new[backordered]"]').val($('datalist#available-stock-items option[value="'+ $(this).val() +'"]:first').data('backordered') || '');
    } else {
      $(row).find('input[name="new[name]"]').prop('readonly', false);
    }
  });

  $('body').on('click', 'button[name="transfer"]', function(){
    let quantity_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]'),
      backordered_field = $(this).closest('tr').find('input[name$="[backordered]"]');

    $(quantity_field).val( Number($(quantity_field).val()) + Number($(backordered_field).val()) );
    $(backordered_field).val(0);
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

  let new_item_index = 0;
  while ($(':input[name^="contents['+new_item_index+']"]').length) new_item_index++;

  $('table tfoot button[name="add"]').click(function(e) {
    e.preventDefault();

    let row = $(this).closest('tr');

    if (!$('datalist#available-stock-items option[value="'+ $('input[name="new[sku]"]').val() +'"]').length) {
      alert('Uknown stock item');
      return;
    }

    let $option = $('datalist#available-stock-items option[value="'+ $('input[name="new[sku]"]').val() +'"]:first');

    let output = [
      '  <tr class="item">',
      '    <td>',
      '       <?php echo functions::escape_js(functions::form_hidden_field('contents[new_item_index][id]', '')); ?>',
      '       <?php echo functions::escape_js(functions::form_hidden_field('contents[new_item_index][item_id]', '')); ?>',
      '       <?php echo functions::escape_js(functions::form_hidden_field('contents[new_item_index][sku]', '')); ?>',
      '       ' + $option.attr('value'),
      '    </td>',
      '    <td><?php echo functions::escape_js(functions::form_hidden_field('contents[new_item_index][name]', '')); ?>'+ $option.data('name') +'</td>',
      '    <td><?php echo functions::escape_js(functions::form_decimal_field('contents[new_item_index][quantity]', '', 2, 'readonly')); ?></td>',
      '    <td>',
      '      <div class="input-group">',
      '        <span class="input-group-text">&plusmn;</span>',
      '        <?php echo functions::form_decimal_field('contents[new_item_index][quantity_adjustment]', true, 2, !empty($_POST['options_stock']) ? 'readonly' : ''); ?>',
      '      </div>',
      '    </td>',
      '    <td class="text-center">',
      '      <div class="input-group">',
      '        <?php echo functions::escape_js(functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button')); ?>',
      '        <?php echo functions::escape_js(functions::form_decimal_field('contents[new_item_index][backordered]', true, 2)); ?>',
      '      </div>',
      '    </td>',
      '    <td class="text-center"><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times', 'style="color: #c33;"')); ?></a></td>',
      '  </tr>'
    ].join('')
    .replace(/new_item_index/g, 'new_' + new_item_index++);

    let $output = $(output);

  // Insert values
    $output.find('[name$="[item_id]"]').val($('input[name="new[id]"]').data('id') || '');
    $output.find('[name$="[sku]"]').val($('input[name="new[sku]"]').data('sku') || '');
    $output.find('[name$="[name]"]').val($('input[name="new[name]"]').val() || '');
    $output.find('[name$="[quantity]"]').val($('input[name="new[quantity]"]').val() || 0);
    $output.find('[name$="[quantity_adjustment]"]').val($('input[name="new[quantity_adjustment]"]').val() || '');
    $output.find('[name$="[backordered]"]').val($('input[name="new[backordered]"]').val() || '');

    $('#transaction-contents tbody').append($output);

    $('input[name="new[sku]"]').val('');
    $('input[name="new[name]"]').val('');
    $('input[name="new[quantity]"]').val('');
    $('input[name="new[quantity_adjustment]"]').val('');
    $('input[name="new[sku]"]').focus();
  });

  $('button[name="save"]').click(function(){
    if ($('input[name="new[sku]"]').val() != '' && $('input[name="new[quantity_adjustment]"]').val() != '') {
      $('button[name="add"]').click();
    }
  });
</script>