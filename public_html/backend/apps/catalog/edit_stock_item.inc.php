<?php

  if (!empty($_GET['stock_item_id'])) {
    $stock_item = new ent_stock_item($_GET['stock_item_id']);
  } else {
    $stock_item = new ent_stock_item();
  }

  if (!$_POST) {
    $_POST = $stock_item->data;
  }

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'][settings::get('store_language_code')])) {
        throw new Exception(language::translate('error_name_missing', 'You must provide a name'));
      }

      if (!empty($_POST['sku'])) {
        if (database::query(
          "select id from ". DB_TABLE_PREFIX ."stock_items
          where id != ". (int)$stock_item->data['id'] ."
          and sku = '". database::input($_POST['sku']) ."'
          limit 1;"
        )->num_rows) {
          throw new Exception(language::translate('error_sku_database_conflict', 'Another entry with the given SKU already exists in the database'));
        }
      }

      if (!empty($_POST['mpn'])) {
        if (database::query(
          "select id from ". DB_TABLE_PREFIX ."stock_items
          where id != ". (int)$stock_item->data['id'] ."
          and mpn = '". database::input($_POST['mpn']) ."'
          limit 1;"
        )->num_rows) {
          throw new Exception(language::translate('error_mpn_database_conflict', 'Another entry with the given MPN already exists in the database'));
        }
      }

      if (!empty($_POST['gtin'])) {
        if (database::query(
          "select id from ". DB_TABLE_PREFIX ."stock_items
          where id != ". (int)$stock_item->data['id'] ."
          and gtin = '". database::input($_POST['gtin']) ."'
          limit 1;"
        )->num_rows) {
          throw new Exception(language::translate('error_gtin_database_conflict', 'Another entry with the given GTIN already exists in the database'));
        }
      }

      foreach (['sku', 'mpn', 'gtin', 'taric'] as $field) {
        $_POST[$field] = trim($_POST[$field]);
      }

      foreach ([
        'name',
        'sku',
        'mpn',
        'gtin',
        'taric',
        'shelf',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'length_unit',
        'quantity',
        'quantity_adjustment',
        'backordered',
        'quantity_unit_id',
        'purchase_price',
        'purchase_price_currency_code',
        'references',
      ] as $field) {
        if (isset($_POST[$field])) {
          $stock_item->data[$field] = $_POST[$field];
        }
      }

      $stock_item->save();

      if (!empty($_POST['delete_image'])) {
        $stock_item->delete_image();
      }

      if (!empty($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $stock_item->save_file($_FILES['image']['tmp_name']);
      }

      if (!empty($_POST['delete_file'])) {
        $stock_item->delete_file();
      }

      if (!empty($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $stock_item->save_file($_FILES['file']['tmp_name'], $_FILES['file']['name'], $_FILES['file']['type']);
      }

      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json; charset='. language::$selected['code']);
        echo json_encode(['status' => 'ok', 'data' => $stock_item->data], JSON_UNESCAPED_SLASHES);
        exit;
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/stock_items'));
      exit;

    } catch (Exception $e) {

      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json; charset='. language::$selected['code']);
        echo json_encode(['status' => 'error', 'error' =>  $e->getMessage()], JSON_UNESCAPED_SLASHES);
        exit;
      }

      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete']) && $stock_item) {

    try {

      $stock_item->delete();

      notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
      header('Location: '. document::ilink(__APP__.'/stock_items'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  breadcrumbs::add(!empty($stock_item->data['id']) ? language::translate('title_edit_stock_item', 'Edit Stock Item') : language::translate('title_create_new_stock_item', 'Create New Stock Item'));

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <div class="card-title">
        <?php echo $app_icon; ?> <?php echo !empty($stock_item->data['id']) ? language::translate('title_edit_stock_item', 'Edit Stock Item') : language::translate('title_create_new_stock_item', 'Create New Stock Item'); ?>
      </div>
    </div>
  </div>

  <div class="card-body">

    <?php echo functions::form_begin('stock_item_form', 'post', false, true); ?>

      <div class="row">
        <div class="<?php echo (is_ajax_load()) ? 'col-xl-12' : 'col-xl-6'; ?>">

          <div class="form-group">
            <label><?php echo language::translate('title_name', 'Name'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_regional_text('name['. $language_code .']', $language_code, true, ''); ?>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo language::translate('title_image', 'Image'); ?></label>

                <div class="thumbnail">
                  <?php echo functions::draw_thumbnail('storage://images/' . ($stock_item->data['image'] ? $stock_item->data['image'] : 'no_image.png'), 320, 0, 'product'); ?>
                </div>

                <?php if (!empty($stock_item->data['image'])) { ?>
                <small class="float-end"><?php echo functions::form_input_checkbox('delete_image', ['1', language::translate('text_delete', 'Delete')], true); ?></small>
                <?php } ?>

                <?php echo functions::form_input_file('image', 'accept="image/*"'); ?>
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-group references">
                <label><?php echo language::translate('title_references', 'References'); ?></label>
                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_sku', 'SKU'); ?> <a href="https://en.wikipedia.org/wiki/Stock_keeping_unit" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_input_text('sku', true, 'style="text-transform: uppercase;"'); ?>
                </div>

                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_gtin', 'GTIN'); ?> <a href="https://en.wikipedia.org/wiki/Global_Trade_Item_Number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_input_text('gtin', true); ?>
                </div>

                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_mpn', 'MPN'); ?> <a href="https://en.wikipedia.org/wiki/Brand_part_number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_input_text('mpn', true); ?>
                </div>

                <div class="input-group">
                  <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_taric', 'TARIC'); ?> <a href="https://en.wikipedia.org/wiki/TARIC_code" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                  <?php echo functions::form_input_text('taric', true); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_shelf_location', 'Shelf Location'); ?></label>
                <?php echo functions::form_input_text('shelf', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></label>
                <div class="input-group">
                  <?php echo functions::form_input_decimal('purchase_price', true, 2, 'min="0"'); ?>
                  <?php echo functions::form_select_currency('purchase_price_currency_code', true); ?>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-2">
              <label><?php echo language::translate('title_reserved_quantity', 'Reserved Quantity'); ?></label>
              <div class="form-input text-end" readonly>
                <?php echo !empty($stock_item->data['id']) ? (float)$stock_item->data['quantity_reserved'] : 'n/a'; ?>
              </div>
            </div>

            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_stock_quantity', 'Stock Quantity'); ?></label>
              <div class="input-group">
                <?php echo functions::form_input_decimal('quantity', true, 2, 'data-quantity="'. (!empty($stock_item->data['id']) ? (float)$stock_item->data['quantity'] : '0') .'"'); ?>
                <?php echo functions::form_select_quantity_unit('quantity_unit_id', true); ?>
              </div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_quantity_adjustment', 'Quantity Adjustment'); ?></label>
              <div class="input-group">
                <span class="input-group-text">&plusmn;</span>
                <?php echo functions::form_input_decimal('quantity_adjustment', true, 2); ?>
              </div>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_backordered', 'Backordered'); ?></label>
              <div class="input-group">
                <?php echo functions::form_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
                <?php echo functions::form_input_decimal('backordered', true, 2, 'min="0"'); ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
              <div class="input-group">
                <?php echo functions::form_input_decimal('weight', true, 3, 'min="0"'); ?>
                <?php echo functions::form_select_weight_unit('weight_unit', true); ?>
              </div>
            </div>

            <div class="form-group col-md-8">
              <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
              <div class="input-group">
                <?php echo functions::form_input_decimal('length', true, 3, 'min="0"'); ?>
                <span class="input-group-text">x</span>
                <?php echo functions::form_input_decimal('width', true, 3, 'min="0"'); ?>
                <span class="input-group-text">x</span>
                <?php echo functions::form_input_decimal('height', true, 3, 'min="0"'); ?>
                <?php echo functions::form_select_length_unit('length_unit', true); ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-10">
              <label><?php echo language::translate('title_digital_item', 'Digital Item'); ?></label>
              <?php echo functions::form_input_file('file'); ?>
              <?php if (!empty($stock_item->data['file'])) { ?>
              <div><?php echo functions::form_input_checkbox('delete_file', ['1', language::translate('text_delete', 'Delete') .' '. $stock_item->data['filename']], true); ?></div>
              <?php } ?>
            </div>

            <div class="form-group col-md-2">
              <label><?php echo language::translate('title_downloads', 'Downloads'); ?></label>
              <?php echo functions::form_input_number('downloads', true, 'readonly'); ?>
            </div>
          </div>
        </div>

        <div class="<?php echo (is_ajax_load()) ? 'col-xl-12' : 'col-xl-6'; ?>">
          <h2><?php echo language::translate('title_references', 'References'); ?></h2>

          <div class="table-responsive">
            <table id="table-references" class="table table-striped data-table">
              <thead>
                <th style="min-width: 200px;"><?php echo language::translate('title_supplier', 'Supplier'); ?></th>
                <th class="main"><?php echo language::translate('title_code', 'Code'); ?></th>
                <th></th>
              </thead>

              <tbody>
                <?php if (!empty($_POST['references'])) foreach (array_keys($_POST['references']) as $key) { ?>
                <tr>
                  <td>
                    <?php echo functions::form_input_hidden('references['.$key.'][id]', true); ?>
                    <?php echo functions::form_select_supplier('references['.$key.'][source]', true); ?>
                  </td>
                  <td><?php echo functions::form_input_text('references['.$key.'][code]', true); ?></td>
                  <td><a class="remove btn btn-default btn-sm" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
                </tr>
              <?php } ?>
              </tbody>

              <tfoot>
                <tr>
                  <td colspan="3"><button class="btn btn-default add" type="button"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('text_create_new_reference', 'Create New Reference'); ?></button></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

      </div>

      <div class="card-action">
        <?php echo functions::form_button_predefined('save'); ?>
        <?php if ($stock_item->data['id']) echo functions::form_button_predefined('delete'); ?>
        <?php echo functions::form_button_predefined('cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<script>
  <?php if (empty($stock_item->data['id'])) { ?>
  $('form[name="stock_item_form"] input[name^="name"]').each(function() {
    if ($(this).val() == '') {
      var field = 'input[name="' + $(this).attr('name') + '"]';
      $(this).val( $(field).not(this).val() );
    }
  });
  <?php } ?>

  $('form[name="stock_item_form"] input[name="quantity"], form[name="stock_item_form"] input[name="quantity_adjustment"], form[name="stock_item_form"] input[name="backordered"]').on('blur', function(){
    $(this).val(parseFloat($(this).val()).toFixed($('select[name="quantity_unit_id"] option:selected').data('decimals')));
  });

  $('form[name="stock_item_form"] input[name="quantity"]').on('input', function(){
    var quantity = parseFloat(parseFloat($(this).val()) - parseFloat($(this).data('quantity'))).toFixed($('select[name="quantity_unit_id"] option:selected').data('decimals'));
    $('input[name="quantity_adjustment"]').val(quantity);
  });

  $('form[name="stock_item_form"] input[name="quantity_adjustment"]').on('input', function(){
    var quantity = parseFloat(parseFloat($('input[name="quantity"]').data('quantity')) + parseFloat($(this).val())).toFixed($('select[name="quantity_unit_id"] option:selected').data('decimals'));
    $('input[name="quantity"]').val(quantity);
  });

  $('form[name="stock_item_form"] select[name="quantity_unit_id"]').on('change', function(){
    if ($('input[name="quantity"]').val() != '') $('input[name="quantity"]').val(parseFloat($('input[name="quantity"]').val()).toFixed($(this).find('option:selected').data('decimals')));
    if ($('input[name="quantity_adjustment"]').val() != '') $('input[name="quantity_adjustment"]').val(parseFloat($('input[name="quantity_adjustment"]').val()).toFixed($(this).find('option:selected').data('decimals')));
    if ($('input[name="backordered"]').val() != '') $('input[name="backordered"]').val(parseFloat($('input[name="backordered"]').val()).toFixed($(this).find('option:selected').data('decimals')));
  });

  $('button[name="transfer"]').click(function(){
    var quantity_field = $(this).closest('form').find('input[name="quantity_adjustment"]');
    var backordered_field = $(this).closest('form').find('input[name="backordered"]');
    $(quantity_field).val( Number($(quantity_field).val()) + Number($(backordered_field).val()) ).trigger('input');
    $(backordered_field).val(0);
  });

  if ($.featherlight && $.featherlight.opened) {
    $('form[name="stock_item_form"]').submit(function(e){
      e.preventDefault();
      $.ajax({
        url: '<?php echo document::link(); ?>',
        type: 'post',
        cache: false,
        async: true,
        data: $(this).serialize() + '&save=true',
        dataType: 'json',
        success: function(result) {
          if (result.error) {
            alert(result.error);
            return;
          }
          <?php if (!empty($_GET['js_callback'])) { ?>
          <?php echo 'if ('. addcslashes($_GET['js_callback'], '\'') .') '. addcslashes($_GET['js_callback'], '\'') .'(result.data);' ?>
          <?php echo 'else alert("Unknown callback function'. addcslashes($_GET['js_callback'], "\"\r\n") .'");' ?>
          <?php } ?>
          $.featherlight.close();
        },
      });
    });

    $('form[name="stock_item_form"] button[name="cancel"]').attr('onclick', '$.featherlight.close();');
    $('form[name="stock_item_form"] button[name="delete"]').remove();
  }

// References

  $('#table-references').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  var new_reference_i = 1;
  $('#table-references').on('click', '.add', function(e) {
    e.preventDefault();
    let output = [
    ' <tr>',
    '  <td>',
    '     <?php echo functions::escape_js(functions::form_input_hidden('references[new_reference_i][id]', true)); ?>',
    '     <?php echo functions::escape_js(functions::form_select_supplier('references[new_reference_i][source]', true)); ?>',
    '   </td>',
    '   <td><?php echo functions::escape_js(functions::form_input_text('references[new_reference_i][code]', true)); ?></td>',
    '   <td><a class="remove btn btn-default btn-sm" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::escape_js(functions::draw_fonticon('remove')); ?></a></td>',
    ' </tr>',
    ].join('\n');
    while ($('input[name="references[new_'+new_reference_i+']"]').length) new_reference_i++;
    output = output.replace(/new_reference_i/g, 'new_' + new_reference_i);
    $('#table-references tbody').append(output);
    new_reference_i++;
  });

</script>