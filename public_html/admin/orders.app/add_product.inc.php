<?php
  document::$layout = 'ajax';

  if (empty($_GET['product_id'])) return;
  if (empty($_GET['currency_code'])) $_GET['currency_code'] = settings::get('store_currency_code');
  if (empty($_GET['currency_value'])) $_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];

  $product = reference::product($_GET['product_id'], $_GET['language_code'], $_GET['currency_code'], $_GET['customer']['id']);
  if (empty($product->id)) return;

  if (empty($_POST)) {

    $fields = [
      'name',
      'sku',
      'gtin',
      'taric',
      'weight',
      'weight_class',
      'dim_x',
      'dim_y',
      'dim_z',
      'dim_class',
      'price',
      'tax',
    ];

    foreach ($fields as $field) {
      if (isset($product->$field)) $_POST[$field] = $product->$field;
    }

    $price = currency::format_raw($product->final_price, $_GET['currency_code'], $_GET['currency_value']);
    $tax = tax::get_tax($price, $product->tax_class_id, $_GET['customer']);
    $_POST['price'] = $price;
    $_POST['tax'] = $tax;
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
  echo '<img src="'. document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $product->image, $width, $height, settings::get('product_image_clipping'))) .'" />';
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

      <div class="options">
<?php
  if (count($product->options) > 0) {
    foreach ($product->options as $group) {

      echo '  <div class="form-group">'
         . '    <label>'. $group['name'] .'</label>';

      switch ($group['function']) {

        case 'checkbox':

          foreach ($group['values'] as $value) {

            $price_adjust = currency::format_raw($value['price_adjust'], $_GET['currency_code'], $_GET['currency_value']);
            $tax_adjust = currency::format_raw(tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']));

            $price_adjust_text = '';
            if ($value['price_adjust']) {
              $price_adjust_text = currency::format($value['price_adjust'], false, $_GET['currency_code'], $_GET['currency_value']);
              if ($value['price_adjust'] > 0) $price_adjust_text = ' +' . $price_adjust_text;
            }

            echo '<div class="checkbox">' . PHP_EOL
               . '  <label>' . functions::form_draw_checkbox('options['.$group['name'] .'][]', $value['name'], true, 'data-group="'. $group['name'] .'" data-combination="'. $group['group_id'] .'-'. (!empty($value['value_id']) ? $value['value_id'] : functions::escape_html('0:"'. $value['custom_value'] .'"')) .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required' : '')) .' '. $value['name'] . $price_adjust_text . '</label>' . PHP_EOL
               . '</div>';
          }
          break;

        case 'input':

          $value = array_shift($group['values']);

          $price_adjust = currency::format_raw($value['price_adjust'], $_GET['currency_code'], $_GET['currency_value']);
          $tax_adjust = currency::format_raw(tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']));

          $price_adjust_text = '';
          if ($value['price_adjust']) {
            $price_adjust_text = currency::format($value['price_adjust'], false, $_GET['currency_code'], $_GET['currency_value']);
            if ($value['price_adjust'] > 0) $price_adjust_text = ' +'.$price_adjust_text;
          }

          echo functions::form_draw_text_field('options['.$group['name'].']', isset($_POST['options'][$group['name']]) ? true : $value['value'], 'data-group="'. $group['name'] .'" data-combination="'. $group['group_id'] .'-'. $value['value_id'] .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required' : '')) . $price_adjust_text . PHP_EOL;

          break;

        case 'radio':

          foreach ($group['values'] as $value) {

            $price_adjust = currency::format_raw($value['price_adjust'], $_GET['currency_code'], $_GET['currency_value']);
            $tax_adjust = currency::format_raw(tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']));

            $price_adjust_text = '';
            if ($value['price_adjust']) {
              $price_adjust_text = currency::format($value['price_adjust'], false, $_GET['currency_code'], $_GET['currency_value']);
              if ($value['price_adjust'] > 0) $price_adjust_text = ' +'.$price_adjust_text;
            }

            echo '<div class="radio">' . PHP_EOL
               . '  <label>'. functions::form_draw_radio_button('options['.$group['name'].']', $value['name'], true, 'data-group="'. $group['name'] .'" data-combination="'. $group['group_id'] .'-'. (!empty($value['value_id']) ? $value['value_id'] : functions::escape_html('0:"'. $value['custom_value'] .'"')) .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required' : '')) .' '. $value['name'] . $price_adjust_text . '</label>' . PHP_EOL
               . '</div>';
          }

          break;

        case 'select':

          $options = [['-- '. language::translate('title_select', 'Select') .' --', '']];

          foreach ($group['values'] as $value) {

            $price_adjust = currency::format_raw($value['price_adjust'], $_GET['currency_code'], $_GET['currency_value']);
            $tax_adjust = currency::format_raw(tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']));

            $price_adjust_text = '';
            if ($value['price_adjust']) {
              $price_adjust_text = currency::format($value['price_adjust'], false, $_GET['currency_code'], $_GET['currency_value']);
              if ($value['price_adjust'] > 0) $price_adjust_text = ' +'.$price_adjust_text;
            }

            $options[] = [$value['name'] . $price_adjust_text, $value['name'], 'data-combination="'. $group['group_id'] .'-'. (!empty($value['value_id']) ? $value['value_id'] : functions::escape_html('0:"'. $value['custom_value'] .'"')) .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"'];
          }

          echo functions::form_draw_select_field('options['.$group['name'].']', $options, true, 'data-group="'. $group['name'] .'" ' . (!empty($group['required']) ? ' required' : ''));

          break;

        case 'textarea':

          $value = array_shift($group['values']);

          $price_adjust = currency::format_raw($value['price_adjust'], $_GET['currency_code'], $_GET['currency_value']);
          $tax_adjust = currency::format_raw(tax::get_tax($value['price_adjust'], $product->tax_class_id, $_GET['customer']), $_GET['currency_code'], $_GET['currency_value']);

          $price_adjust_text = '';
          if ($value['price_adjust']) {
            $price_adjust_text = currency::format($value['price_adjust'], false, $_GET['currency_code'], $_GET['currency_value']);
            if ($value['price_adjust'] > 0) {
              $price_adjust_text = ' <br />+'. $price_adjust;
            }
          }

          echo functions::form_draw_textarea('options['.$group['name'].']', isset($_POST['options'][$group['name']]) ? true : $value['value'], 'data-group="'. $group['name'] .'" data-combination="'. $group['group_id'] .'-'. $value['value_id'] .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required' : '')) . $price_adjust_text. PHP_EOL;

          break;
      }

      echo '</div>';
    }
  }

  echo functions::form_draw_hidden_field('option_stock_combination', '');
?>
      </div>

      <div class="row">
        <div class="form-group col-md-4">
          <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
          <?php echo functions::form_draw_text_field('sku', true); ?>
        </div>

        <div class="form-group col-md-4">
          <label><?php echo language::translate('title_gtin', 'GTIN'); ?></label>
          <?php echo functions::form_draw_text_field('gtin', true); ?>
        </div>

        <div class="form-group col-md-4">
          <label><?php echo language::translate('title_taric', 'TARIC'); ?></label>
          <?php echo functions::form_draw_text_field('taric', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-4">
          <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
          <div class="input-group">
            <?php echo functions::form_draw_decimal_field('weight', true, 2, 0); ?>
            <?php echo functions::form_draw_weight_classes_list('weight_class', true, false, 'style="width: auto;"'); ?>
          </div>
        </div>

        <div class="form-group col-md-8">
          <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
          <div class="input-group">
            <?php echo functions::form_draw_decimal_field('dim_x', true, 1, 0); ?>
            <span class="input-group-text">x</span>
            <?php echo functions::form_draw_decimal_field('dim_y', true, 1, 0); ?>
            <span class="input-group-text">x</span>
            <?php echo functions::form_draw_decimal_field('dim_z', true, 1, 0); ?>
            <?php echo functions::form_draw_length_classes_list('dim_class', true, false, 'style="width: auto;"'); ?>
          </div>
        </div>
      </div>

      <div class="row">
          <div class="form-group col-md-4">
          <label><?php echo language::translate('title_quantity', 'quantity'); ?></label>
          <?php echo functions::form_draw_decimal_field('quantity', 1); ?>
        </div>

          <div class="form-group col-md-4">
          <label><?php echo language::translate('title_price', 'Price'); ?></label>
          <?php echo functions::form_draw_currency_field($_GET['currency_code'], 'price', true); ?>
        </div>

          <div class="form-group col-md-4">
          <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
          <?php echo functions::form_draw_currency_field($_GET['currency_code'], 'tax', true); ?>
        </div>
      </div>

      <div class="form-group">
        <?php if (!empty($product->stock_options)) {?>
        <table class="table table-default table-striped data-table">
          <thead>
            <tr>
              <th><?php echo language::translate('title_stock_option', 'Stock Option'); ?></th>
              <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
              <th><?php echo language::translate('title_in_stock', 'In Stock'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($product->stock_options as $stock_option) { ?>
            <tr>
              <td><?php echo $stock_option['name']; ?></td>
              <td><?php echo $stock_option['sku']; ?></td>
              <td class="text-center"><?php echo (float)$stock_option['quantity']; ?></td>
            </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="text-end"><strong><?php echo language::translate('title_total', 'Total'); ?>: </strong><?php echo (float)$product->quantity; ?></td>
            </tr>
          </tfoot>
        </table>
        <?php } ?>
      </div>

      <div>
        <?php echo functions::form_draw_button('ok', language::translate('title_ok', 'OK'), 'button', '', 'ok'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>

</div>

<script>
  $('form[name=form_add_product]').on('input', function(e) {

    var price = <?php echo (float)$price; ?>,
      tax = <?php echo (float)$tax; ?>,
      decimals = <?php echo (int)currency::$currencies[$_GET['currency_code']]['decimals']; ?>;

    $(this).find('input[name^="options"][type="radio"]:checked, input[type="checkbox"]:checked').each(function(){
      if ($(this).data('price-adjust')) price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
    });

    $(this).find('select[name^="options"] option:checked').each(function(){
      if ($(this).data('price-adjust')) price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
    });

    $(this).find('input[name^="options"][type!="radio"][type!="checkbox"]').each(function(){
      if ($(this).val() != '') {
        if ($(this).data('price-adjust')) price += $(this).data('price-adjust');
        if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
      }
    });

    $(this).find('input[name="price"]').val( price.toFixed(decimals) );
    $(this).find('input[name="tax"]').val( tax.toFixed(decimals) );
  });

  $('form[name="form_add_product"] button[name="ok"]').off('click').click(function(e){
    e.preventDefault();

    var error = false,
        form = $(this).closest('form');

    var item = {
      id: '',
      product_id: $(form).find(':input[name="product_id"]').val(),
      option_stock_combination: $(form).find(':input[name="option_stock_combination"]').val(),
      options: {},
      name: $(form).find(':input[name="name"]').val(),
      sku: $(form).find(':input[name="sku"]').val(),
      gtin: $(form).find(':input[name="gtin"]').val(),
      taric: $(form).find(':input[name="taric"]').val(),
      weight: parseFloat($(form).find(':input[name="weight"]').val() || 0),
      weight_class: $(form).find(':input[name="weight_class"]').val(),
      dim_x: parseFloat($(form).find(':input[name="dim_x"]').val() || 0),
      dim_y: parseFloat($(form).find(':input[name="dim_y"]').val() || 0),
      dim_z: parseFloat($(form).find(':input[name="dim_z"]').val() || 0),
      dim_class: $(form).find(':input[name="dim_class"]').val(),
      quantity: parseFloat($(form).find(':input[name="quantity"]').val() || 0),
      price: parseFloat($(form).find(':input[name="price"]').val() || 0),
      tax: parseFloat($(form).find(':input[name="tax"]').val() || 0)
    };

    var selected_option_combinations = [];

    $(form).find('.options input[type="checkbox"]:checked').each(function(){
      if ($(this).val()) {
        if (!item.options[$(this).data('group')]) item.options[$(this).data('group')] = [];
        item.options[$(this).data('group')].push($(this).val());
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });

    $(form).find('.options input[type="radio"]:checked').each(function(){
      if ($(this).val()) {
        item.options[$(this).data('group')] = $(this).val();
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });

    $(form).find('.options select :selected').each(function(){
      if ($(this).val()) {
        item.options[$(this).parent().data('group')] = $(this).val();
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).parent().attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });

    $(form).find('.options input[type!="radio"][type!="checkbox"]').each(function(){
      if ($(this).val()) {
        item.options[$(this).data('group')] = $(this).val();
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });

    if (error) {
      alert("<?php echo functions::escape_html(language::translate('error_missing_required_options', 'Missing required options')); ?>");
      return false;
    }

    selected_option_combinations.sort();
    var available_stock_options = <?php echo !empty($product->id) ? json_encode($product->options_stock, JSON_UNESCAPED_SLASHES) : '[]'; ?>;

    $.each(available_stock_options, function(i, stock_option) {
      var matched = false;
      $.each(stock_option.combination.split(','), function(j, current_stock_combination){
        if ($.inArray(current_stock_combination, selected_option_combinations) != -1) matched = true;
      });

      if (matched) {
        item.option_stock_combination = stock_option.combination;
        item.sku = stock_option.sku;
        item.gtin = stock_option.gtin;
        if (stock_option.weight > 0) {
          item.weight = parseFloat(stock_option.weight || 0);
          item.weight_class = stock_option.weight_class;
        }
        if (stock_option.dim_x > 0) {
          item.dim_x = parseFloat(stock_option.dim_x || 0);
          item.dim_y = parseFloat(stock_option.dim_y || 0);
          item.dim_z = parseFloat(stock_option.dim_z || 0);
          item.dim_class = stock_option.dim_class;
        }
      }
    });

    addItem(item);
    $.featherlight.close();
  });
</script>