<?php
  document::$layout = 'ajax';
?>
<div id="modal-edit-order-item" class="modal fade" style="max-width: 640px; display: none;">

  <h2><?php echo language::translate('title_edit_order_item', 'Edit Order Item'); ?></h2>

  <div class="modal-body">

    <div class="row">
      <div class="form-group col-md-9">
        <label><?php echo language::translate('title_name', 'Name'); ?></label>
        <?php echo functions::form_draw_text_field('name', ''); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_product_id', 'Product ID'); ?></label>
        <?php echo functions::form_draw_number_field('product_id', ''); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
        <?php echo functions::form_draw_text_field('sku', true); ?>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_gtin', 'GTIN'); ?></label>
        <?php echo functions::form_draw_text_field('gtin', true); ?>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-md-4">
        <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('weight', true, 2, 0); ?>
          <span class="input-group-addon"><?php echo functions::form_draw_weight_classes_list('weight_class', true, false, 'style="width: auto;"'); ?></span>
        </div>
      </div>

      <div class="form-group col-md-8">
        <label><?php echo language::translate('title_dimensions', 'Dimensions'); ?></label>
        <div class="input-group">
          <?php echo functions::form_draw_decimal_field('dim_x', true, 1, 0); ?>
          <span class="input-group-addon">x</span>
          <?php echo functions::form_draw_decimal_field('dim_y', true, 1, 0); ?>
          <span class="input-group-addon">x</span>
          <?php echo functions::form_draw_decimal_field('dim_z', true, 1, 0); ?>
          <span class="input-group-addon">
            <?php echo functions::form_draw_length_classes_list('dim_class', true, false, 'style="width: auto;"'); ?>
          </span>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_quantity', 'quantity'); ?></label>
        <?php echo functions::form_draw_decimal_field('quantity', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_price', 'Price'); ?></label>
        <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'price', ''); ?>
      </div>

        <div class="form-group col-md-4">
        <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
        <?php echo functions::form_draw_currency_field($_POST['currency_code'], 'tax', ''); ?>
      </div>
    </div>

    <div class="btn-group">
      <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'button', '', 'save'); ?>
      <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="$.featherlight.close();"', 'cancel'); ?>
    </div>
  </div>

</div>

<script>
  $('#modal-edit-order-item button[name="save"]').click(function(e){

    var modal = $('.featherlight.active');
    var row = $(modal).data('row');

    var fields = [
      'name',
      'sku',
      'gtin',
      'weight',
      'weight_class',
      'dim_x',
      'dim_y',
      'dim_z',
      'dim_class',
      'price',
      'tax',
    ];

    $.each($(modal).find(':input'), function(i,element){
      var field = $(element).attr('name');
      var value = $(modal).find(':input[name="'+field+'"]').val();
      if ($(element).attr('type') == 'number') value = Number(value);
      $(row).find(':input[name$="['+field+']"]').val(value).trigger('keyup');
      $(row).find('.'+field).text(value);
    });

    $.featherlight.close();
  });
</script>
<?php
  exit;
?>