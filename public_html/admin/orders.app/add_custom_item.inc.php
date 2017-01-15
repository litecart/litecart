<?php
  document::$layout = 'ajax';
?>
<div class="container">
  <h3><?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></h3>

  <?php echo functions::form_draw_form_begin('form_add_custom_item', 'post'); ?>

    <div class="row">
      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php echo functions::form_draw_text_field('name', true); ?>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
        <?php echo functions::form_draw_text_field('sku', true); ?>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_draw_currency_field($_GET['currency_code'], 'price', true); ?>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_draw_currency_field($_GET['currency_code'], 'tax', true); ?>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_weight', 'weight'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('weight', true, null, 0, null, 'style="width: 50%;"'); ?>
          <?php echo functions::form_draw_weight_classes_list('weight_class', true, false, 'style="width: 50%;"'); ?>
        </div>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
        <?php echo functions::form_draw_decimal_field('quantity', !empty($_POST['quantity']) ? true : '1', 2); ?>
      </div>
    </div>

    <p><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'submit', 'class="btn btn-success btn-block"'); ?></p>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
  $('button[name="add"]').click(function(e){
    e.preventDefault();

    var item = {
      id: '',
      product_id: $('input[name="product_id"]').val(),
      option_stock_combination: null,
      options: null,
      name: $('input[name="name"]').val(),
      sku: $('input[name="sku"]').val(),
      weight: $('input[name="weight"]').val(),
      weight_class: $('select[name="weight_class"] option:selected').val(),
      quantity: $('input[name="quantity"]').val(),
      price: $('input[name="price"]').val(),
      tax: $('input[name="tax"]').val()
    };

    addItem(item);
    $.featherlight.close();
  });
</script>