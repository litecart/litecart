<?php
  document::$layout = 'printable';
?>

<script>
  $(document).ready(function() {
    parent.$('#fancybox-content').height($('body').height() + parseInt(parent.$('#fancybox-content').css('border-top-width')) + parseInt(parent.$('#fancybox-content').css('border-bottom-width')));
    parent.$.fancybox.center();
  });
</script>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_add_custom_item', 'Add Custom Item'); ?></h1>

<?php echo functions::form_draw_form_begin('form_add_custom_item', 'post'); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_name', 'Name'); ?></strong></td>
      <td><?php echo functions::form_draw_text_field('name', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_sku', 'SKU'); ?></strong></td>
      <td><?php echo functions::form_draw_text_field('sku', true, 'data-size="small"'); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_price', 'Price'); ?></strong></td>
      <td><?php echo functions::form_draw_currency_field($_GET['currency_code'], 'price', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_tax', 'Tax'); ?></strong></td>
      <td><?php echo functions::form_draw_currency_field($_GET['currency_code'], 'tax', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_weight', 'weight'); ?></strong></td>
      <td><?php echo functions::form_draw_decimal_field('weight', true); ?> <?php echo functions::form_draw_weight_classes_list('weight_class', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_quantity', 'Quantity'); ?></strong></td>
      <td><?php echo functions::form_draw_decimal_field('quantity', !empty($_POST['quantity']) ? true : '1', 2); ?></td>
    </tr>
  </table>

  <p><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'submit', '', 'add'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="parent.$.fancybox.close();"', 'cancel'); ?></p>

<?php echo functions::form_draw_form_end(); ?>

<script>
  $("button[name='add']").click(function(e){
    e.preventDefault();

    var item = {
      id: '',
      product_id: $("input[name='product_id']").val(),
      option_stock_combination: null,
      options: null,
      name: $("input[name='name']").val(),
      sku: $("input[name='sku']").val(),
      weight: $("input[name='weight']").val(),
      weight_class: $("select[name='weight_class'] option:selected").val(),
      quantity: $("input[name='quantity']").val(),
      price: $("input[name='price']").val(),
      tax: $("input[name='tax']").val()
    };

    parent.<?php echo preg_replace('#([^a-zA-Z_])#', '', $_GET['return_method']); ?>(item);
    parent.$.fancybox.close();
  });
</script>