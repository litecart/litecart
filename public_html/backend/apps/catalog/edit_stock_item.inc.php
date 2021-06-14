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

      if (empty($_POST['name'][settings::get('site_language_code')])) throw new Exception(language::translate('error_name_missing', 'You must provide a name'));
      if (empty($_POST['sku'])) throw new Exception(language::translate('error_missing_sku', 'You must provide SKU'));

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where id != ". (int)$stock_item->data['id'] ." and code = '". database::input($_POST['code']) ."' limit 1;"))) {
        throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
      }

      if (!empty($_POST['sku']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where id != ". (int)$stock_item->data['id'] ." and sku = '". database::input($_POST['sku']) ."' limit 1;"))) {
        throw new Exception(language::translate('error_sku_database_conflict', 'Another entry with the given SKU already exists in the database'));
      }

      if (!empty($_POST['mpn']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where id != ". (int)$stock_item->data['id'] ." and mpn = '". database::input($_POST['mpn']) ."' limit 1;"))) {
        throw new Exception(language::translate('error_mpn_database_conflict', 'Another entry with the given MPN already exists in the database'));
      }

      if (!empty($_POST['gtin']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."stock_items where id != ". (int)$stock_item->data['id'] ." and gtin = '". database::input($_POST['gtin']) ."' limit 1;"))) {
        throw new Exception(language::translate('error_gtin_database_conflict', 'Another entry with the given GTIN already exists in the database'));
      }

      $fields = [
        'supplier_id',
        'brand_id',
        'sku',
        'mpn',
        'gtin',
        'taric',
        'name',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'length_unit',
        'quantity',
        'quantity_adjustment',
        'ordered',
        'quantity_unit_id',
        'purchase_price',
        'purchase_price_currency_code',
      ];

      foreach ($fields as $field) {
        if (in_array($field, ['sku', 'mpn', 'gtin', 'taric',])) $_POST[$field] = trim($_POST[$field]);
        if (isset($_POST[$field])) $stock_item->data[$field] = $_POST[$field];
      }

      $stock_item->save();

      if (!empty($_POST['delete_file'])) $stock_item->delete_file();

      if (!empty($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $stock_item->save_file($_FILES['file']['tmp_name'], $_FILES['file']['name'], $_FILES['file']['type']);
      }

      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json; charset='. language::$selected['code']);
        echo json_encode(['status' => 'ok', 'data' => $stock_item->data], JSON_UNESCAPED_SLASHES);
        exit;
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('catalog/stock_items'));
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
      header('Location: '. document::ilink('catalog/stock_items'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  breadcrumbs::add(!empty($stock_item->data['id']) ? language::translate('title_edit_stock_item', 'Edit Stock Item') : language::translate('title_add_new_stock_item', 'Add New Stock Item'));
?>
<div class="card card-app">
  <div class="card-heading">
    <div class="card-title">
      <div class="card-title">
        <?php echo $app_icon; ?> <?php echo !empty($stock_item->data['id']) ? language::translate('title_edit_stock_item', 'Edit Stock Item') : language::translate('title_create_new_stock_item', 'Create New Stock Item'); ?>
      </div>
    </div>
  </div>

  <div class="card-body">

    <?php echo functions::form_draw_form_begin('stock_item_form', 'post', false, true); ?>

      <div style="max-width: 640px;">

        <div class="form-group">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_text_field('name['. $language_code .']', $language_code, true, ''); ?>
        </div>

        <div class="row">
          <div class="col-md-6">

            <div class="form-group references">
              <label><?php echo language::translate('title_references', 'References'); ?></label>
              <div class="input-group">
                <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_sku', 'SKU'); ?> <a href="https://en.wikipedia.org/wiki/Stock_keeping_unit" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('sku', true, 'style="text-transform: uppercase;"'); ?>
              </div>

              <div class="input-group">
                <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_gtin', 'GTIN'); ?> <a href="https://en.wikipedia.org/wiki/Global_Trade_Item_Number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('gtin', true); ?>
              </div>

              <div class="input-group">
                <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_mpn', 'MPN'); ?> <a href="https://en.wikipedia.org/wiki/Brand_part_number" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('mpn', true); ?>
              </div>

              <div class="input-group">
                <label class="input-group-text" style="width: 125px;"><?php echo language::translate('title_taric', 'TARIC'); ?> <a href="https://en.wikipedia.org/wiki/TARIC_code" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_draw_text_field('taric', true); ?>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label><?php echo language::translate('title_brand', 'Brand'); ?></label>
              <?php echo functions::form_draw_brands_list('brand_id', true); ?>
            </div>

            <div class="form-group">
              <label><?php echo language::translate('title_supplier', 'Supplier'); ?></label>
              <?php echo functions::form_draw_suppliers_list('supplier_id', true); ?>
            </div>
          </div>
        </div>

        <div class="form-group">
          <?php if (!empty($stock_item->data['file'])) { ?>
          <small class="pull-right"><?php echo functions::form_draw_checkbox('delete', ['1', language::translate('text_delete', 'Delete') .' '. $stock_item->data['filename']], true); ?></small>
          <?php } ?>
          <label><?php echo language::translate('title_digital_item', 'Digital Item'); ?></label>
          <?php echo functions::form_draw_file_field('file', true); ?>
        </div>

        <div class="row">
          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_stock_quantity', 'Stock Quantity'); ?></label>
            <div class="input-group">
              <?php echo functions::form_draw_decimal_field('quantity', true, 2, 'data-quantity="'. (!empty($stock_item->data['id']) ? (float)$stock_item->data['quantity'] : '0') .'"'); ?>
              <?php echo functions::form_draw_quantity_units_list('quantity_unit_id', true); ?>
            </div>
          </div>

          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_quantity_adjustment', 'Quantity Adjustment'); ?></label>
            <div class="input-group">
              <span class="input-group-text">&plusmn;</span>
              <?php echo functions::form_draw_decimal_field('quantity_adjustment', true, 2); ?>
            </div>
          </div>

          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_ordered', 'Ordered'); ?></label>
            <div class="input-group">
              <?php echo functions::form_draw_button('transfer', functions::draw_fonticon('fa-arrow-left'), 'button'); ?>
              <?php echo functions::form_draw_decimal_field('ordered', true, 2, 'min="0"'); ?>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_purchase_price', 'Purchase Price'); ?></label>
            <div class="input-group">
              <?php echo functions::form_draw_decimal_field('purchase_price', true, 2, 'min="0"'); ?>
              <?php echo functions::form_draw_currencies_list('purchase_price_currency_code', true); ?>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
            <div class="input-group">
              <?php echo functions::form_draw_decimal_field('weight', true, 3, 'min="0"'); ?>
              <?php echo functions::form_draw_weight_units_list('weight_unit', true); ?>
            </div>
          </div>

          <div class="form-group col-md-8">
            <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
            <div class="input-group">
              <?php echo functions::form_draw_decimal_field('length', true, 3, 'min="0"'); ?>
              <span class="input-group-text">x</span>
              <?php echo functions::form_draw_decimal_field('width', true, 3, 'min="0"'); ?>
              <span class="input-group-text">x</span>
              <?php echo functions::form_draw_decimal_field('height', true, 3, 'min="0"'); ?>
              <?php echo functions::form_draw_length_units_list('length_unit', true); ?>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_delivery_status', 'Delivery Status'); ?></label>
            <?php echo functions::form_draw_delivery_statuses_list('delivery_status_id', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_sold_out_status', 'Sold Out Status'); ?></label>
            <?php echo functions::form_draw_sold_out_statuses_list('sold_out_status_id', true); ?>
          </div>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo (isset($stock_item->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('form[name="stock_item_form"] input[name="quantity"], form[name="stock_item_form"] input[name="quantity_adjustment"], form[name="stock_item_form"] input[name="ordered"]').on('blur', function(){
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
    if ($('input[name="ordered"]').val() != '') $('input[name="ordered"]').val(parseFloat($('input[name="ordered"]').val()).toFixed($(this).find('option:selected').data('decimals')));
  });

  $('button[name="transfer"]').click(function(){
    var quantity_field = $(this).closest('form').find('input[name="quantity_adjustment"]');
    var ordered_field = $(this).closest('form').find('input[name="ordered"]');
    $(quantity_field).val( Number($(quantity_field).val()) + Number($(ordered_field).val()) ).trigger('input');
    $(ordered_field).val(0);
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
          <?php echo 'if (window.'. addcslashes($_GET['js_callback'], '\'') .') window.'. addcslashes($_GET['js_callback'], '\'') .'(result.data);' ?>
          <?php echo 'else alert("Unknown callback function'. addcslashes($_GET['js_callback'], "\"\r\n") .'");' ?>
          <?php } ?>
          $.featherlight.close();
        },
      });
    });

    $('form[name="stock_item_form"] button[name="cancel"]').attr('onclick', '$.featherlight.close();');
    $('form[name="stock_item_form"] button[name="delete"]').remove();
  }
</script>