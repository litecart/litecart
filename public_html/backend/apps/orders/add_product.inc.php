<?php
  document::$layout = 'ajax';

  if (empty($_GET['product_id'])) return;
  if (empty($_GET['currency_code'])) $_GET['currency_code'] = settings::get('site_currency_code');
  if (empty($_GET['currency_value'])) $_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];

  $product = reference::product($_GET['product_id'], $_GET['language_code'], $_GET['currency_code'], $_GET['customer']['id']);
  if (empty($product->id)) return;

  if (!$_POST) {

    $fields = [
      'name',
      'price',
      'tax',
    ];

    foreach ($fields as $field) {
      if (isset($product->$field)) $_POST[$field] = $product->$field;
    }

    $price = !empty($product->campaign['price']) ? $product->campaign['price'] : $product->price;
    $_POST['price'] = currency::format_raw($price, $_GET['currency_code'], $_GET['currency_value']);
    $_POST['tax'] = tax::get_tax($_POST['price'], $product->tax_class_id, $_GET['customer']);
  }
?>

<div id="modal-add-order-item" class="modal fade" style="max-width: 640px;">

  <h2><?php echo language::translate('title_add_product', 'Add Product'); ?></h2>

  <div class="modal-body">

    <?php echo functions::form_draw_form_begin('form_add_product', 'post'); ?>
      <?php echo functions::form_draw_hidden_field('product_id', $product->id); ?>

      <div class="form-group">
        <div class="thumbnail">
<?php
  list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));
  echo '<img src="'. document::href_rlink(functions::image_thumbnail('storage://images/' . $product->image, $width, $height)) .'" />';
?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-9">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true); ?>
        </div>

        <div class="form-group col-md-3">
          <label><?php echo language::translate('title_product_id', 'Product ID'); ?></label>
          <?php echo functions::form_draw_number_field('product_id', true, 'readonly'); ?>
        </div>
      </div>

      <div class="row">
          <div class="form-group col-md-4">
          <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
          <?php echo functions::form_draw_decimal_field('quantity', 1); ?>
        </div>

          <div class="form-group col-md-4">
          <label><?php echo language::translate('title_price', 'Price'); ?></label>
          <?php echo functions::form_draw_currency_field('price', $_GET['currency_code'], true); ?>
        </div>

          <div class="form-group col-md-4">
          <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
          <?php echo functions::form_draw_currency_field('tax', $_GET['currency_code'], true); ?>
        </div>
      </div>

      <div class="form-group">
        <?php if (!empty($product->stock_options)) {?>
        <table class="table table-default table-striped data-table">
          <thead>
            <tr>
              <th></th>
              <th><?php echo language::translate('title_stock_option', 'Stock Option'); ?></th>
              <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
              <th class="text-end"><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($product->stock_options as $stock_option) { ?>
            <tr>
              <td><?php echo functions::form_draw_radio_button('stock_item_id', $stock_option['stock_item_id'], true, 'required'); ?></td>
              <td><?php echo $stock_option['name']; ?></td>
              <td><?php echo $stock_option['sku']; ?></td>
              <td class="text-end"><?php echo (float)$stock_option['quantity']; ?></td>
            </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td class="text-end" colspan="4">
                <strong><?php echo language::translate('title_total', 'Total'); ?>: </strong><?php echo (float)$product->quantity; ?>
              </td>
            </tr>
          </tfoot>
        </table>
        <?php } ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

</div>

<script>
  $('form[name="form_add_product"] button[name="ok"]').unbind('click').click(function(e){
    e.preventDefault();

    var error = false,
        form = $(this).closest('form');

    var item = {
      id: '',
      product_id: $(form).find(':input[name="product_id"]').val(),
      stock_item_id: $(form).find(':input[name="stock_item_id"]').val(),
      name: $(form).find(':input[name="name"]').val(),
      sku: $(form).find(':input[name="sku"]').val(),
      gtin: $(form).find(':input[name="gtin"]').val(),
      taric: $(form).find(':input[name="taric"]').val(),
      weight: parseFloat($(form).find(':input[name="weight"]').val() || 0),
      weight_unit: $(form).find(':input[name="weight_unit"]').val(),
      length: parseFloat($(form).find(':input[name="length"]').val() || 0),
      width: parseFloat($(form).find(':input[name="width"]').val() || 0),
      height: parseFloat($(form).find(':input[name="height"]').val() || 0),
      length_unit: $(form).find(':input[name="length_unit"]').val(),
      quantity: parseFloat($(form).find(':input[name="quantity"]').val() || 0),
      price: parseFloat($(form).find(':input[name="price"]').val() || 0),
      tax: parseFloat($(form).find(':input[name="tax"]').val() || 0)
    };

    addItem(item);
    $.featherlight.close();
  });
</script>